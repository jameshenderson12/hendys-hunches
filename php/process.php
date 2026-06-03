<?php

function setInitialRanking() {
	// Connect to the database
	include 'db-connect.php';

	// Create a query to return the rankings information
	$sql_getrankings = "SELECT * FROM live_user_information";

	// Execute the query and return the results or display an appropriate error message
	$rankings = mysqli_query($con, $sql_getrankings) or die(mysqli_error());

	// Carry out the following actions for each row in the returned results
    while ($row = mysqli_fetch_assoc($rankings)) {
		// Capture last position and current position before updating
		$sql_setinitialrank = "UPDATE live_user_information SET lastpos = startpos, currpos = startpos WHERE NOT EXISTS (SELECT * FROM live_match_results)";
		mysqli_query($con, $sql_setinitialrank) or die(mysql_error());
	}
    // Close the database connection
    mysqli_close($con);
}

function hh_prediction_stage_definitions(): array {
	$definitions = [];
	foreach (hh_prediction_stage_contexts() as $key => $context) {
		$definitions[$key] = [
			'table' => $context['table'],
			'start' => $context['score_start'],
			'end' => $context['score_end'],
		];
	}

	return $definitions;
}

function hh_prediction_backup_table_name(string $liveTable): string {
	if (str_starts_with($liveTable, 'live_')) {
		return 'backup_' . substr($liveTable, 5);
	}

	return 'backup_' . $liveTable;
}

function hh_prediction_backup_row_exists(mysqli $con, string $tableName, int $userId): bool {
	$statement = mysqli_prepare($con, "SELECT id FROM {$tableName} WHERE id = ? LIMIT 1");
	if (!$statement) {
		throw new RuntimeException(mysqli_error($con));
	}

	mysqli_stmt_bind_param($statement, 'i', $userId);
	mysqli_stmt_execute($statement);
	$result = mysqli_stmt_get_result($statement);
	$exists = $result instanceof mysqli_result && mysqli_num_rows($result) > 0;
	if ($result instanceof mysqli_result) {
		mysqli_free_result($result);
	}
	mysqli_stmt_close($statement);

	return $exists;
}

function hh_ensure_prediction_backup_table_with_connection(mysqli $con, string $liveTable): string {
	$backupTable = hh_prediction_backup_table_name($liveTable);
	$escapedBackupTable = mysqli_real_escape_string($con, $backupTable);
	$checkResult = mysqli_query($con, "SHOW TABLES LIKE '{$escapedBackupTable}'");
	if ($checkResult === false) {
		throw new RuntimeException(mysqli_error($con));
	}

	$tableExists = mysqli_num_rows($checkResult) > 0;
	mysqli_free_result($checkResult);

	if (!$tableExists) {
		if (!mysqli_query($con, "CREATE TABLE {$backupTable} LIKE {$liveTable}")) {
			throw new RuntimeException(mysqli_error($con));
		}
	}

	return $backupTable;
}

function hh_copy_prediction_row_to_backup_with_connection(mysqli $con, string $liveTable, string $backupTable, int $userId): void {
	$insertSql = "INSERT INTO {$backupTable} SELECT * FROM {$liveTable} WHERE id = ? LIMIT 1";
	$statement = mysqli_prepare($con, $insertSql);
	if (!$statement) {
		throw new RuntimeException(mysqli_error($con));
	}

	mysqli_stmt_bind_param($statement, 'i', $userId);
	if (!mysqli_stmt_execute($statement)) {
		$error = mysqli_stmt_error($statement);
		mysqli_stmt_close($statement);
		throw new RuntimeException($error);
	}

	mysqli_stmt_close($statement);
}

function hh_prediction_stage_context_for_match_number(int $matchNumber): ?array {
	foreach (hh_prediction_stage_contexts() as $context) {
		$fixtureStart = (int) ($context['fixture_start'] ?? 0);
		$fixtureEnd = (int) ($context['fixture_end'] ?? 0);
		if ($matchNumber >= $fixtureStart && $matchNumber <= $fixtureEnd) {
			return $context;
		}
	}

	return null;
}

function hh_fixture_score_indexes(int $matchNumber): array {
	$homeIndex = max(1, ($matchNumber * 2) - 1);

	return [
		'home' => $homeIndex,
		'away' => $homeIndex + 1,
	];
}

function hh_prediction_fixture_score_detail($predHome, $predAway, $actualHome, $actualAway): array {
	$submitted = is_numeric($predHome) && is_numeric($predAway);
	$recorded = is_numeric($actualHome) && is_numeric($actualAway);

	if (!$recorded) {
		return [
			'points' => null,
			'category' => 'pending',
			'label' => 'Awaiting result',
			'submitted' => $submitted,
			'recorded' => false,
		];
	}

	if (!$submitted) {
		return [
			'points' => 0,
			'category' => 'missing',
			'label' => 'No prediction submitted',
			'submitted' => false,
			'recorded' => true,
		];
	}

	$predHome = (int) $predHome;
	$predAway = (int) $predAway;
	$actualHome = (int) $actualHome;
	$actualAway = (int) $actualAway;

	$homeHit = $predHome === $actualHome;
	$awayHit = $predAway === $actualAway;
	$exact = $homeHit && $awayHit;
	$sameOutcome =
		(($predHome > $predAway) && ($actualHome > $actualAway))
		|| (($predHome < $predAway) && ($actualHome < $actualAway))
		|| (($predHome === $predAway) && ($actualHome === $actualAway));

	$points = 0;
	if ($homeHit) {
		$points += 1;
	}
	if ($awayHit) {
		$points += 1;
	}
	if ($sameOutcome) {
		$points += 2;
	}
	if ($exact) {
		$points += 3;
	}

	$label = match (true) {
		$exact => 'Exact scoreline',
		$sameOutcome && ($homeHit || $awayHit) => 'Correct outcome + one team score',
		$sameOutcome => 'Correct outcome',
		$homeHit || $awayHit => 'One team score right',
		default => 'Miss',
	};

	$category = match ($points) {
		7 => 'perfect',
		3 => 'strong',
		2 => 'outcome',
		1 => 'single',
		default => 'miss',
	};

	return [
		'points' => $points,
		'category' => $category,
		'label' => $label,
		'submitted' => true,
		'recorded' => true,
	];
}

function hh_reset_initial_rankings_with_connection(mysqli $con): void {
	$userResult = mysqli_query(
		$con,
		"SELECT id FROM live_user_information ORDER BY signupdate ASC, id ASC"
	);

	if (!$userResult) {
		throw new RuntimeException(mysqli_error($con));
	}

	$updateStatement = mysqli_prepare(
		$con,
		"UPDATE live_user_information SET startpos = ?, lastpos = ?, currpos = ? WHERE id = ? LIMIT 1"
	);

	if (!$updateStatement) {
		mysqli_free_result($userResult);
		throw new RuntimeException(mysqli_error($con));
	}

	$position = 1;
	while ($row = mysqli_fetch_assoc($userResult)) {
		$userId = (int) ($row['id'] ?? 0);
		mysqli_stmt_bind_param($updateStatement, 'iiii', $position, $position, $position, $userId);
		if (!mysqli_stmt_execute($updateStatement)) {
			$error = mysqli_stmt_error($updateStatement);
			mysqli_stmt_close($updateStatement);
			mysqli_free_result($userResult);
			throw new RuntimeException($error);
		}
		$position++;
	}

	mysqli_stmt_close($updateStatement);
	mysqli_free_result($userResult);
}

function hh_update_move_status_with_connection(mysqli $con): void {
	$sql_getrankings = "SELECT lui.id, lui.firstname, lui.surname, lui.avatar, lui.faveteam, lui.startpos, lui.currpos, lui.lastpos,
						(lup_groups.points_total +
						IFNULL(lup_ro32.points_total, 0) +
						IFNULL(lup_ro16.points_total, 0) +
						IFNULL(lup_qf.points_total, 0) +
						IFNULL(lup_sf.points_total, 0) +
						IFNULL(lup_fi.points_total, 0)) AS points_total,
						FIND_IN_SET(
							(lup_groups.points_total +
							IFNULL(lup_ro32.points_total, 0) +
							IFNULL(lup_ro16.points_total, 0) +
							IFNULL(lup_qf.points_total, 0) +
							IFNULL(lup_sf.points_total, 0) +
							IFNULL(lup_fi.points_total, 0)),
							(
								SELECT GROUP_CONCAT(
									DISTINCT (lup_groups.points_total +
											IFNULL(lup_ro32.points_total, 0) +
											IFNULL(lup_ro16.points_total, 0) +
											IFNULL(lup_qf.points_total, 0) +
											IFNULL(lup_sf.points_total, 0) +
											IFNULL(lup_fi.points_total, 0))
									ORDER BY (lup_groups.points_total +
											IFNULL(lup_ro32.points_total, 0) +
											IFNULL(lup_ro16.points_total, 0) +
											IFNULL(lup_qf.points_total, 0) +
											IFNULL(lup_sf.points_total, 0) +
											IFNULL(lup_fi.points_total, 0)) DESC
								)
								FROM live_user_predictions_groups lup_groups
								LEFT JOIN live_user_predictions_ro32 lup_ro32 ON lup_groups.id = lup_ro32.id
								LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lup_groups.id = lup_ro16.id
								LEFT JOIN live_user_predictions_qf lup_qf ON lup_groups.id = lup_qf.id
								LEFT JOIN live_user_predictions_sf lup_sf ON lup_groups.id = lup_sf.id
								LEFT JOIN live_user_predictions_final lup_fi ON lup_groups.id = lup_fi.id
							)
						) AS rank
					FROM live_user_information lui
					INNER JOIN live_user_predictions_groups lup_groups ON lui.id = lup_groups.id
					LEFT JOIN live_user_predictions_ro32 lup_ro32 ON lui.id = lup_ro32.id
					LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lui.id = lup_ro16.id
					LEFT JOIN live_user_predictions_qf lup_qf ON lui.id = lup_qf.id
					LEFT JOIN live_user_predictions_sf lup_sf ON lui.id = lup_sf.id
					LEFT JOIN live_user_predictions_final lup_fi ON lui.id = lup_fi.id
					ORDER BY rank ASC, surname ASC";

	$rankings = mysqli_query($con, $sql_getrankings) or die(mysqli_error($con));

	while ($row = mysqli_fetch_assoc($rankings)) {
		$id = (int) $row["id"];
		$currpos = (int) $row["currpos"];
		$rank = (int) $row["rank"];

		mysqli_query($con, "UPDATE live_user_information SET lastpos = $currpos WHERE id=$id") or die(mysqli_error($con));
		mysqli_query($con, "UPDATE live_user_information SET currpos = $rank WHERE id=$id") or die(mysqli_error($con));
	}

	mysqli_free_result($rankings);
}

function updateMoveStatus() {
	include 'db-connect.php';
	hh_update_move_status_with_connection($con);
	mysqli_close($con);
}

function console_log($data){
  echo '<script>';
  echo 'console.log('. json_encode($data) .')';
  echo '</script>';
}

function hh_compare_prediction_stage(mysqli $con, string $predictionTable, int $startIndex, int $endIndex): void {
	if ($endIndex < $startIndex) {
		return;
	}

	$identical_points = 3;
	$outcome_points = 2;
	$score_points = 1;
	$resultFields = [];
	$predictionFields = ['username', 'firstname', 'surname'];

	for ($i = $startIndex; $i <= $endIndex; $i++) {
		$resultFields[] = "score{$i}_r";
		$predictionFields[] = "score{$i}_p";
	}

	$resultQuery = mysqli_query(
		$con,
		"SELECT " . implode(', ', $resultFields) . " FROM live_match_results ORDER BY match_id DESC LIMIT 1"
	);
	$rvalue = $resultQuery ? mysqli_fetch_assoc($resultQuery) : null;
	if ($resultQuery) {
		mysqli_free_result($resultQuery);
	}

	if (!is_array($rvalue)) {
		return;
	}

	mysqli_query($con, "UPDATE {$predictionTable} SET points_total = 0");
	$list_of_usernames = mysqli_query($con, "SELECT username FROM {$predictionTable}");
	if (!$list_of_usernames) {
		return;
	}

	$usernames = [];
	while ($row = mysqli_fetch_assoc($list_of_usernames)) {
		if (!empty($row['username'])) {
			$usernames[] = $row['username'];
		}
	}
	mysqli_free_result($list_of_usernames);

	$updateStatement = mysqli_prepare($con, "UPDATE {$predictionTable} SET points_total = ? WHERE username = ? LIMIT 1");
	if (!$updateStatement) {
		return;
	}

	foreach ($usernames as $usernamevalue) {
		$escapedUsername = mysqli_real_escape_string($con, $usernamevalue);
		$userResult = mysqli_query(
			$con,
			"SELECT " . implode(', ', $predictionFields) . " FROM {$predictionTable} WHERE username='{$escapedUsername}' LIMIT 1"
		);
		$pvalue = $userResult ? mysqli_fetch_assoc($userResult) : null;
		if ($userResult) {
			mysqli_free_result($userResult);
		}

		if (!is_array($pvalue)) {
			continue;
		}

		$pointsTotal = 0;

		for ($i = $startIndex; $i <= $endIndex; $i++) {
			$prediction = $pvalue["score{$i}_p"] ?? null;
			$result = $rvalue["score{$i}_r"] ?? null;

			if ($prediction !== null && $result !== null && (string) $prediction === (string) $result) {
				$pointsTotal += $score_points;
			}
		}

		for ($home = $startIndex, $away = $startIndex + 1; $home <= $endIndex && $away <= $endIndex; $home += 2, $away += 2) {
			$predHome = $pvalue["score{$home}_p"] ?? null;
			$predAway = $pvalue["score{$away}_p"] ?? null;
			$resHome = $rvalue["score{$home}_r"] ?? null;
			$resAway = $rvalue["score{$away}_r"] ?? null;

			if (!is_numeric($predHome) || !is_numeric($predAway) || !is_numeric($resHome) || !is_numeric($resAway)) {
				continue;
			}

			$predHome = (int) $predHome;
			$predAway = (int) $predAway;
			$resHome = (int) $resHome;
			$resAway = (int) $resAway;

			$sameOutcome =
				(($predHome > $predAway) && ($resHome > $resAway))
				|| (($predHome < $predAway) && ($resHome < $resAway))
				|| (($predHome === $predAway) && ($resHome === $resAway));

			if ($sameOutcome) {
				$pointsTotal += $outcome_points;
			}

			if ($predHome === $resHome && $predAway === $resAway) {
				$pointsTotal += $identical_points;
			}
		}

		mysqli_stmt_bind_param($updateStatement, 'is', $pointsTotal, $usernamevalue);
		mysqli_stmt_execute($updateStatement);
	}

	mysqli_stmt_close($updateStatement);
}

function hh_recalculate_all_prediction_points(mysqli $con): void {
	foreach (hh_prediction_stage_definitions() as $definition) {
		hh_compare_prediction_stage($con, $definition['table'], $definition['start'], $definition['end']);
	}

	hh_update_move_status_with_connection($con);
}

function hh_save_match_results_with_connection(mysqli $con, array $scoresByMatch): void {
	global $no_of_total_fixtures;

	$totalScoreColumns = max(0, $no_of_total_fixtures * 2);
	if ($totalScoreColumns <= 0) {
		return;
	}

	$resultColumns = [];
	$resultValues = [];
	$resultTypes = '';

	for ($scoreIndex = 1; $scoreIndex <= $totalScoreColumns; $scoreIndex++) {
		$resultColumns[] = "score{$scoreIndex}_r";
		$resultTypes .= 'i';
		$resultValues[] = array_key_exists($scoreIndex, $scoresByMatch) ? $scoresByMatch[$scoreIndex] : null;
	}

	mysqli_begin_transaction($con);

	try {
		$insertSql = "INSERT INTO live_match_results (" . implode(', ', $resultColumns) . ") VALUES (" . implode(', ', array_fill(0, count($resultColumns), '?')) . ")";
		$insertStatement = mysqli_prepare($con, $insertSql);
		if (!$insertStatement) {
			throw new RuntimeException(mysqli_error($con));
		}

		hh_bind_stmt_values($insertStatement, $resultTypes, $resultValues);
		if (!mysqli_stmt_execute($insertStatement)) {
			$error = mysqli_stmt_error($insertStatement);
			mysqli_stmt_close($insertStatement);
			throw new RuntimeException($error);
		}
		mysqli_stmt_close($insertStatement);

		$scheduleStatement = mysqli_prepare($con, "UPDATE live_match_schedule SET homescore = ?, awayscore = ? WHERE match_number = ? LIMIT 1");
		if (!$scheduleStatement) {
			throw new RuntimeException(mysqli_error($con));
		}

		for ($matchNumber = 1; $matchNumber <= $no_of_total_fixtures; $matchNumber++) {
			$homeIndex = ($matchNumber * 2) - 1;
			$awayIndex = $matchNumber * 2;
			$homeScore = array_key_exists($homeIndex, $scoresByMatch) ? $scoresByMatch[$homeIndex] : null;
			$awayScore = array_key_exists($awayIndex, $scoresByMatch) ? $scoresByMatch[$awayIndex] : null;

			mysqli_stmt_bind_param($scheduleStatement, 'iii', $homeScore, $awayScore, $matchNumber);
			if (!mysqli_stmt_execute($scheduleStatement)) {
				$error = mysqli_stmt_error($scheduleStatement);
				mysqli_stmt_close($scheduleStatement);
				throw new RuntimeException($error);
			}
		}

		mysqli_stmt_close($scheduleStatement);
		hh_recalculate_all_prediction_points($con);
		mysqli_commit($con);
	} catch (Throwable $exception) {
		mysqli_rollback($con);
		throw $exception;
	}
}

function hh_bind_stmt_values(mysqli_stmt $statement, string $types, array $values): void {
	$refs = [];
	foreach ($values as $index => $value) {
		$refs[$index] = &$values[$index];
	}

	array_unshift($refs, $types);
	call_user_func_array([$statement, 'bind_param'], $refs);
}

function hh_insert_prediction_stage_row(string $stageKey): void {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	include 'php/db-connect.php';
	$definitions = hh_prediction_stage_definitions();
	$definition = $definitions[$stageKey] ?? null;
	if (!$definition) {
		mysqli_close($con);
		return;
	}

	$columns = ['id', 'username', 'firstname', 'surname'];
	$placeholders = ['?', '?', '?', '?'];
	$types = 'isss';
	$values = [
		(int) ($_SESSION['id'] ?? 0),
		(string) ($_SESSION['username'] ?? ''),
		(string) ($_SESSION['firstname'] ?? ''),
		(string) ($_SESSION['surname'] ?? ''),
	];

	for ($scoreIndex = $definition['start']; $scoreIndex <= $definition['end']; $scoreIndex++) {
		$columns[] = "score{$scoreIndex}_p";
		$placeholders[] = '?';
		$types .= 'i';
		$value = trim((string) ($_POST["score{$scoreIndex}_p"] ?? ''));
		$values[] = $value === '' ? null : (int) $value;
	}

	$columns[] = 'lastupdate';
	$placeholders[] = 'NOW()';

	$sql = "INSERT INTO {$definition['table']} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
	$stmt = mysqli_prepare($con, $sql);
	if (!$stmt) {
		die('Error: ' . mysqli_error($con));
	}

	hh_bind_stmt_values($stmt, $types, $values);
	if (!mysqli_stmt_execute($stmt)) {
		die('Error: ' . mysqli_stmt_error($stmt));
	}

	mysqli_stmt_close($stmt);
	mysqli_close($con);
}

function hh_stage_has_posted_scores(array $definition): bool {
	for ($scoreIndex = $definition['start']; $scoreIndex <= $definition['end']; $scoreIndex++) {
		if (array_key_exists("score{$scoreIndex}_p", $_POST)) {
			return true;
		}
	}

	return false;
}

function hh_stage_posted_score_stats(array $definition): array {
	$present = 0;
	$filled = 0;

	for ($scoreIndex = $definition['start']; $scoreIndex <= $definition['end']; $scoreIndex++) {
		$field = "score{$scoreIndex}_p";
		if (!array_key_exists($field, $_POST)) {
			continue;
		}

		$present++;
		$value = trim((string) $_POST[$field]);
		if ($value !== '') {
			$filled++;
		}
	}

	$required = max(0, $definition['end'] - $definition['start'] + 1);

	return [
		'present' => $present,
		'filled' => $filled,
		'required' => $required,
		'has_any' => $present > 0,
		'is_complete' => $required > 0 && $filled === $required,
	];
}

function hh_upsert_prediction_stage_row_with_connection(mysqli $con, string $stageKey): void {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$definitions = hh_prediction_stage_definitions();
	$definition = $definitions[$stageKey] ?? null;
	if (!$definition || $definition['end'] < $definition['start']) {
		return;
	}

	$userId = (int) ($_SESSION['id'] ?? 0);
	$username = (string) ($_SESSION['username'] ?? '');
	$firstname = (string) ($_SESSION['firstname'] ?? '');
	$surname = (string) ($_SESSION['surname'] ?? '');
	$backupTable = hh_ensure_prediction_backup_table_with_connection($con, $definition['table']);

	$scoreValues = [];
	for ($scoreIndex = $definition['start']; $scoreIndex <= $definition['end']; $scoreIndex++) {
		$value = trim((string) ($_POST["score{$scoreIndex}_p"] ?? ''));
		$scoreValues[] = $value === '' ? null : (int) $value;
	}

	$existsStatement = mysqli_prepare($con, "SELECT id FROM {$definition['table']} WHERE id = ? LIMIT 1");
	if (!$existsStatement) {
		die('Error: ' . mysqli_error($con));
	}

	mysqli_stmt_bind_param($existsStatement, 'i', $userId);
	mysqli_stmt_execute($existsStatement);
	$existingResult = mysqli_stmt_get_result($existsStatement);
	$rowExists = $existingResult instanceof mysqli_result && mysqli_num_rows($existingResult) > 0;
	if ($existingResult instanceof mysqli_result) {
		mysqli_free_result($existingResult);
	}
	mysqli_stmt_close($existsStatement);

	$backupExists = hh_prediction_backup_row_exists($con, $backupTable, $userId);

	if ($rowExists && !$backupExists) {
		hh_copy_prediction_row_to_backup_with_connection($con, $definition['table'], $backupTable, $userId);
		$backupExists = true;
	}

	if ($rowExists) {
		$setClauses = ['username = ?', 'firstname = ?', 'surname = ?'];
		$types = 'sss';
		$values = [$username, $firstname, $surname];

		for ($scoreIndex = $definition['start']; $scoreIndex <= $definition['end']; $scoreIndex++) {
			$setClauses[] = "score{$scoreIndex}_p = ?";
			$types .= 'i';
		}

		$values = array_merge($values, $scoreValues);
		$types .= 'i';
		$values[] = $userId;

		$sql = "UPDATE {$definition['table']} SET " . implode(', ', $setClauses) . ", lastupdate = NOW() WHERE id = ?";
		$stmt = mysqli_prepare($con, $sql);
		if (!$stmt) {
			die('Error: ' . mysqli_error($con));
		}

		hh_bind_stmt_values($stmt, $types, $values);
		if (!mysqli_stmt_execute($stmt)) {
			die('Error: ' . mysqli_stmt_error($stmt));
		}

		mysqli_stmt_close($stmt);
		return;
	}

	$columns = ['id', 'username', 'firstname', 'surname'];
	$placeholders = ['?', '?', '?', '?'];
	$types = 'isss';
	$values = [$userId, $username, $firstname, $surname];

	for ($scoreIndex = $definition['start']; $scoreIndex <= $definition['end']; $scoreIndex++) {
		$columns[] = "score{$scoreIndex}_p";
		$placeholders[] = '?';
		$types .= 'i';
	}

	$values = array_merge($values, $scoreValues);
	$columns[] = 'lastupdate';
	$placeholders[] = 'NOW()';

	$sql = "INSERT INTO {$definition['table']} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
	$stmt = mysqli_prepare($con, $sql);
	if (!$stmt) {
		die('Error: ' . mysqli_error($con));
	}

	hh_bind_stmt_values($stmt, $types, $values);
	if (!mysqli_stmt_execute($stmt)) {
		die('Error: ' . mysqli_stmt_error($stmt));
	}

	mysqli_stmt_close($stmt);

	if (!$backupExists) {
		hh_copy_prediction_row_to_backup_with_connection($con, $definition['table'], $backupTable, $userId);
	}
}

function compareValues() {
	include 'db-connect.php';
	$definition = hh_prediction_stage_definitions()['groups'];
	hh_compare_prediction_stage($con, $definition['table'], $definition['start'], $definition['end']);
	hh_update_move_status_with_connection($con);
	mysqli_close($con);
}

function compareRO32Values() {
	include 'db-connect.php';
	$definition = hh_prediction_stage_definitions()['ro32'];
	hh_compare_prediction_stage($con, $definition['table'], $definition['start'], $definition['end']);
	hh_update_move_status_with_connection($con);
	mysqli_close($con);
}

function compareRO16Values() {
	include 'db-connect.php';
	$definition = hh_prediction_stage_definitions()['ro16'];
	hh_compare_prediction_stage($con, $definition['table'], $definition['start'], $definition['end']);
	hh_update_move_status_with_connection($con);
	mysqli_close($con);
}

function compareQFValues() {
	include 'db-connect.php';
	$definition = hh_prediction_stage_definitions()['qf'];
	hh_compare_prediction_stage($con, $definition['table'], $definition['start'], $definition['end']);
	hh_update_move_status_with_connection($con);
	mysqli_close($con);
}

function compareSFValues() {
	include 'db-connect.php';
	$definition = hh_prediction_stage_definitions()['sf'];
	hh_compare_prediction_stage($con, $definition['table'], $definition['start'], $definition['end']);
	hh_update_move_status_with_connection($con);
	mysqli_close($con);
}

function compareFinalValues() {
	include 'db-connect.php';
	$definition = hh_prediction_stage_definitions()['final'];
	hh_compare_prediction_stage($con, $definition['table'], $definition['start'], $definition['end']);
	hh_update_move_status_with_connection($con);
	mysqli_close($con);
}

function insertMatchResult() {
	include 'db-connect.php';
	global $no_of_total_fixtures;

	$totalScoreColumns = max(0, $no_of_total_fixtures * 2);
	$scoresByMatch = [];
	for ($i = 1; $i <= $totalScoreColumns; $i++) {
		$scoresByMatch[$i] = isset($_POST["score{$i}_r"]) && $_POST["score{$i}_r"] !== '' ? (int) $_POST["score{$i}_r"] : null;
	}

	hh_save_match_results_with_connection($con, $scoresByMatch);
	mysqli_close($con);
}

function insertMatchResultZZ() {
	// Connect to the database
	include '../php/db-connect.php';

	// Write results data to the database
	$sql = "INSERT INTO live_match_results (score1_r, score2_r, score3_r, score4_r, score5_r, score6_r, score7_r, score8_r, score9_r, score10_r, score11_r, score12_r, score13_r, score14_r, score15_r, score16_r, score17_r, score18_r, score19_r, score20_r, score21_r, score22_r, score23_r, score24_r, score25_r, score26_r, score27_r, score28_r, score29_r, score30_r, score31_r, score32_r, score33_r, score34_r, score35_r, score36_r, score37_r, score38_r, score39_r, score40_r, score41_r, score42_r, score43_r, score44_r, score45_r, score46_r, score47_r, score48_r, score49_r, score50_r, score51_r, score52_r, score53_r, score54_r, score55_r, score56_r, score57_r, score58_r, score59_r, score60_r, score61_r, score62_r, score63_r, score64_r, score65_r, score66_r, score67_r, score68_r, score69_r, score70_r, score71_r, score72_r, score73_r, score74_r, score75_r, score76_r, score77_r, score78_r, score79_r, score80_r, score81_r, score82_r, score83_r, score84_r, score85_r, score86_r, score87_r, score88_r, score89_r, score90_r, score91_r, score92_r, score93_r, score94_r, score95_r, score96_r, score97_r, score98_r, score99_r, score100_r, score101_r, score102_r)
	VALUES ('$_POST[score1_r]','$_POST[score2_r]','$_POST[score3_r]','$_POST[score4_r]','$_POST[score5_r]','$_POST[score6_r]','$_POST[score7_r]','$_POST[score8_r]','$_POST[score9_r]','$_POST[score10_r]','$_POST[score11_r]','$_POST[score12_r]','$_POST[score13_r]','$_POST[score14_r]','$_POST[score15_r]','$_POST[score16_r]','$_POST[score17_r]','$_POST[score18_r]','$_POST[score19_r]','$_POST[score20_r]','$_POST[score21_r]','$_POST[score22_r]','$_POST[score23_r]',
		'$_POST[score24_r]','$_POST[score25_r]','$_POST[score26_r]','$_POST[score27_r]','$_POST[score28_r]','$_POST[score29_r]','$_POST[score30_r]','$_POST[score31_r]','$_POST[score32_r]','$_POST[score33_r]','$_POST[score34_r]','$_POST[score35_r]','$_POST[score36_r]','$_POST[score37_r]','$_POST[score38_r]','$_POST[score39_r]','$_POST[score40_r]','$_POST[score41_r]','$_POST[score42_r]','$_POST[score43_r]','$_POST[score44_r]','$_POST[score45_r]','$_POST[score46_r]',
		'$_POST[score47_r]','$_POST[score48_r]','$_POST[score49_r]','$_POST[score50_r]','$_POST[score51_r]','$_POST[score52_r]','$_POST[score53_r]','$_POST[score54_r]','$_POST[score55_r]','$_POST[score56_r]','$_POST[score57_r]','$_POST[score58_r]','$_POST[score59_r]','$_POST[score60_r]','$_POST[score61_r]','$_POST[score62_r]','$_POST[score63_r]','$_POST[score64_r]','$_POST[score65_r]','$_POST[score66_r]','$_POST[score67_r]','$_POST[score68_r]','$_POST[score69_r]',
		'$_POST[score70_r]','$_POST[score71_r]','$_POST[score72_r]','$_POST[score73_r]','$_POST[score74_r]','$_POST[score75_r]','$_POST[score76_r]','$_POST[score77_r]','$_POST[score78_r]','$_POST[score79_r]','$_POST[score80_r]','$_POST[score81_r]','$_POST[score82_r]','$_POST[score83_r]','$_POST[score84_r]','$_POST[score85_r]','$_POST[score86_r]','$_POST[score87_r]','$_POST[score88_r]','$_POST[score89_r]','$_POST[score90_r]','$_POST[score91_r]','$_POST[score92_r]',
		'$_POST[score93_r]','$_POST[score94_r]','$_POST[score95_r]','$_POST[score96_r]','$_POST[score97_r]','$_POST[score98_r]','$_POST[score99_r]','$_POST[score100_r]','$_POST[score101_r]','$_POST[score102_r]')";

	// If the SQL query fails, produce related error message
	if (!mysqli_query($con, $sql)) {
		die('Error: ' . mysqli_error($con));
	}
	// Close the DB connection
	mysqli_close($con);
	// Now update table by comparing match results against user predictions
	//compareValues();
	compareRO16Values();
	//compareQFValues();
	// compareSFValues();
}

function updateTotalUsers() {
	// Connect to the database
	include 'php/db-connect.php';
	// Create a query to return the total number of users
	$sql_countusers = "SELECT count(*) AS totalusers FROM live_user_information";
	// Execute the query and return the result or display appropriate error message
	$totalusers = mysqli_query($con, $sql_countusers) or die(mysqli_error());
	// For each instance of the returned result
	while ($row = mysqli_fetch_assoc($totalusers)) {
		$countoftotalusers = $row["totalusers"];
		$sql_updatestartpos = "UPDATE live_user_information SET startpos=$countoftotalusers";
		mysqli_query($con, $sql_updatestartpos) or die(mysqli_error());
	}
    // Close the database connection
    mysqli_close($con);
}

function retrieveScorePrediction($num) {
	// Connect to the database
	include 'php/db-connect.php';
	// Create a query to return a user's specific predictions
	$sql_getscore = "SELECT * FROM live_user_predictions_groups WHERE id='{$_SESSION['id']}'";
	// Execute the query and return the results or display an appropriate error message
	$userpred = mysqli_query($con, $sql_getscore) or die(mysqli_error());
	// Output score value
    while($row = mysqli_fetch_assoc($userpred)) {
        echo $row["score".$num."_p"];
   	}
}

function retrieveHomeResult($num) {
	// Connect to the database
	include '../php/db-connect.php';
	// Create a query to return a user's specific predictions
	$sql_getresults = "SELECT * FROM live_match_results WHERE match_id = '$num'";
	// Execute the query and return the results or display an appropriate error message
	$result = mysqli_query($con, $sql_getresults) or die(mysqli_error());
	// Output score value
    while($row = mysqli_fetch_assoc($result)) {
		// Create a query to return a user's specific predictions
		$home = ($num * 2) - 1;
		//$away = $num * 2;
		echo $row["score".$home."_r"];
		//echo $row["score".$away."_r"];
   	}
}

function retrieveAwayResult($num) {
	// Connect to the database
	include '../php/db-connect.php';
	// Create a query to return a user's specific predictions
	$sql_getresults = "SELECT * FROM live_match_results WHERE match_id = '$num'";
	// Execute the query and return the results or display an appropriate error message
	$result = mysqli_query($con, $sql_getresults) or die(mysqli_error());
	// Output score value
    while($row = mysqli_fetch_assoc($result)) {
		// Create a query to return a user's specific predictions
		$away = $num * 2;
		echo $row["score".$away."_r"];
   	}
}
/*
function updatePredictions() {
	// Connect to the database
	include 'php/db-connect.php';
	// SQL query to update predictions once they exist
	$sql_update = "UPDATE live_user_predictions_groups SET score1_p = '$_POST[score1_p]', score2_p = '$_POST[score2_p]', score3_p = '$_POST[score3_p]', score4_p = '$_POST[score4_p]', score5_p = '$_POST[score5_p]', score6_p = '$_POST[score6_p]', score7_p = '$_POST[score7_p]', score8_p = '$_POST[score8_p]', score9_p = '$_POST[score9_p]', score10_p = '$_POST[score10_p]',
	score11_p = '$_POST[score11_p]', score12_p = '$_POST[score12_p]', score13_p = '$_POST[score13_p]', score14_p = '$_POST[score14_p]', score15_p = '$_POST[score15_p]', score16_p = '$_POST[score16_p]', score17_p = '$_POST[score17_p]', score18_p = '$_POST[score18_p]', score19_p = '$_POST[score19_p]', score20_p = '$_POST[score20_p]', score21_p = '$_POST[score21_p]',
	score22_p = '$_POST[score22_p]', score23_p = '$_POST[score23_p]', score24_p = '$_POST[score24_p]', score25_p = '$_POST[score25_p]', score26_p = '$_POST[score26_p]', score27_p = '$_POST[score27_p]', score28_p = '$_POST[score28_p]', score29_p = '$_POST[score29_p]', score30_p = '$_POST[score30_p]', score31_p = '$_POST[score31_p]', score32_p = '$_POST[score32_p]',
	score33_p = '$_POST[score33_p]', score34_p = '$_POST[score34_p]', score35_p = '$_POST[score35_p]', score36_p = '$_POST[score36_p]', score37_p = '$_POST[score37_p]', score38_p = '$_POST[score38_p]', score39_p = '$_POST[score39_p]', score40_p = '$_POST[score40_p]', score41_p = '$_POST[score41_p]', score42_p = '$_POST[score42_p]', score43_p = '$_POST[score43_p]',
	score44_p = '$_POST[score44_p]', score45_p = '$_POST[score45_p]', score46_p = '$_POST[score46_p]', score47_p = '$_POST[score47_p]', score48_p = '$_POST[score48_p]', score49_p = '$_POST[score49_p]', score50_p = '$_POST[score50_p]', score51_p = '$_POST[score51_p]', score52_p = '$_POST[score52_p]', score53_p = '$_POST[score53_p]', score54_p = '$_POST[score54_p]',
	score55_p = '$_POST[score55_p]', score56_p = '$_POST[score56_p]', score57_p = '$_POST[score57_p]', score58_p = '$_POST[score58_p]', score59_p = '$_POST[score59_p]', score60_p = '$_POST[score60_p]', score61_p = '$_POST[score61_p]', score62_p = '$_POST[score62_p]', score63_p = '$_POST[score63_p]', score64_p = '$_POST[score64_p]', score65_p = '$_POST[score65_p]',
	score66_p = '$_POST[score66_p]', score67_p = '$_POST[score67_p]', score68_p = '$_POST[score68_p]', score69_p = '$_POST[score69_p]', score70_p = '$_POST[score70_p]', score71_p = '$_POST[score71_p]', score72_p = '$_POST[score72_p]', score73_p = '$_POST[score73_p]', score74_p = '$_POST[score74_p]', score75_p = '$_POST[score75_p]', score76_p = '$_POST[score76_p]',
	score77_p = '$_POST[score77_p]', score78_p = '$_POST[score78_p]', score79_p = '$_POST[score79_p]', score80_p = '$_POST[score80_p]', score81_p = '$_POST[score81_p]', score82_p = '$_POST[score82_p]', score83_p = '$_POST[score83_p]', score84_p = '$_POST[score84_p]', score85_p = '$_POST[score85_p]', score86_p = '$_POST[score86_p]', score87_p = '$_POST[score87_p]',
	score88_p = '$_POST[score88_p]', score89_p = '$_POST[score89_p]', score90_p = '$_POST[score90_p]', score91_p = '$_POST[score91_p]', score92_p = '$_POST[score92_p]', score93_p = '$_POST[score93_p]', score94_p = '$_POST[score94_p]', score95_p = '$_POST[score95_p]', score96_p = '$_POST[score96_p]', lastupdate = NOW() WHERE id='{$_SESSION['id']}'";

	mysqli_query($con, $sql_update) or die('Error: ' . mysqli_error($con));
	mysqli_close($con);
}
*/
function insertGroupPredictions() {
	hh_insert_prediction_stage_row('groups');
}

function insertRO32Predictions() {
	hh_insert_prediction_stage_row('ro32');
}

function insertRO16Predictions() {
	hh_insert_prediction_stage_row('ro16');
}

function insertQFPredictions() {
	hh_insert_prediction_stage_row('qf');
}

function insertSFPredictions() {
	hh_insert_prediction_stage_row('sf');
}

function insertFiPredictions() {
	hh_insert_prediction_stage_row('final');
}

function submitPredictions(): array {
	// Connect to the database
	include 'php/db-connect.php';

	$submittedAnyStage = false;
	$selectedStage = trim((string) ($_POST['stage'] ?? ''));
	$stageDefinitions = hh_prediction_stage_definitions();

	if ($selectedStage !== '' && isset($stageDefinitions[$selectedStage])) {
		$stageWindows = hh_prediction_stage_windows($con);
		$selectedWindow = $stageWindows[$selectedStage] ?? null;
		$selectedStats = hh_stage_posted_score_stats($stageDefinitions[$selectedStage]);

		if (!$selectedWindow || !$selectedWindow['is_open']) {
			mysqli_close($con);
			return [
				'ok' => false,
				'stage' => $selectedStage,
				'message' => 'This prediction window is not currently open.',
			];
		}

		if ($selectedStats['has_any'] && !$selectedStats['is_complete']) {
			mysqli_close($con);
			return [
				'ok' => false,
				'stage' => $selectedStage,
				'message' => 'Please complete every score in this stage before saving your predictions.',
			];
		}

		if (hh_stage_has_posted_scores($stageDefinitions[$selectedStage])) {
			mysqli_begin_transaction($con);
			try {
				hh_upsert_prediction_stage_row_with_connection($con, $selectedStage);
				hh_recalculate_all_prediction_points($con);
				mysqli_commit($con);
				$submittedAnyStage = true;
			} catch (Throwable $exception) {
				mysqli_rollback($con);
				mysqli_close($con);
				return [
					'ok' => false,
					'stage' => $selectedStage,
					'message' => 'Predictions could not be saved: ' . $exception->getMessage(),
				];
			}
		}
	} else {
		mysqli_begin_transaction($con);
		try {
			foreach ($stageDefinitions as $stageKey => $definition) {
				$stats = hh_stage_posted_score_stats($definition);
				if (!$stats['has_any']) {
					continue;
				}

				if (!$stats['is_complete']) {
					throw new RuntimeException('Please complete every score in each stage before saving your predictions.');
				}

				hh_upsert_prediction_stage_row_with_connection($con, $stageKey);
				$submittedAnyStage = true;
			}

			if ($submittedAnyStage) {
				hh_recalculate_all_prediction_points($con);
			}
			mysqli_commit($con);
		} catch (Throwable $exception) {
			mysqli_rollback($con);
			mysqli_close($con);
			return [
				'ok' => false,
				'stage' => $selectedStage,
				'message' => $exception->getMessage(),
			];
		}
	}

	// Close the DB connection
	mysqli_close($con);

	return [
		'ok' => $submittedAnyStage,
		'stage' => $selectedStage,
		'message' => $submittedAnyStage ? 'Predictions recorded.' : 'No stage scores were submitted.',
	];
}

function displayRankings() {
    // Connect to the database
    include 'php/db-connect.php';

    // Set up SQL query to retrieve data from database tables
    $sql_maketable = "SELECT live_user_information.id, live_user_information.firstname, live_user_information.surname, live_user_information.avatar, live_user_information.faveteam, live_user_information.startpos, live_user_information.currpos, live_user_information.lastpos, live_user_predictions_groups.points_total,
                        FIND_IN_SET(points_total, (
                            SELECT GROUP_CONCAT(DISTINCT points_total ORDER BY points_total DESC)
                            FROM live_user_predictions_groups )
                        ) AS rank
                        FROM live_user_information
                        INNER JOIN live_user_predictions_groups ON live_user_information.id = live_user_predictions_groups.id
                        ORDER BY rank ASC, surname ASC";

    $sql_matchresults = "SELECT * FROM live_match_results";

    // Execute the query and return the results or display an appropriate error message
    $table = mysqli_query($con, $sql_maketable) or die(mysqli_error($con));
    $result = mysqli_query($con, $sql_matchresults) or die(mysqli_error($con));

    echo "<div class='table-responsive'>";
    echo "<table id='rankingsTable' class='table table-striped'>";
    echo "<thead><tr><th>Rank</th><th>Player</th><th>Points</th></tr></thead>";
    echo "<tbody>";

    while ($row = mysqli_fetch_assoc($table)) {
        // Check if match results table contains any data
        if (mysqli_num_rows($result) == 0) {
            $rank = $row["startpos"];
        } else {
            $rank = $row["rank"];
        }

        // Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
        if ($row["lastpos"] > $row["currpos"]) {
            $diff = $row["lastpos"] - $row["currpos"];
            $move = "<i class='bi bi-arrow-up-circle-fill text-success'></i>";
        } elseif ($row["lastpos"] < $row["currpos"]) {
            $diff = $row["currpos"] - $row["lastpos"];
            $move = "<i class='bi bi-arrow-down-circle-fill text-danger'></i>";
        } else {
            $diff = 0;
            $move = "<i class='bi bi-arrow-right-circle-fill text-secondary'></i>";
        }

        // Ensure both name variables begin with upper case letters
        $uppCaseFN = ucfirst($row["firstname"]);
        $uppCaseSN = ucfirst($row["surname"]);

        // Display the table complete with all data variables
        echo "<tr>";
        echo "<td>".$row["rank"]."</span><span style='margin-left: 5px'>".$move."</span></td>";        
        echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;<a href='user.php?id=".$row["id"]."'>".$uppCaseFN." ".$uppCaseSN."</a></td>";
        echo "<td>".$row["points_total"]."</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    mysqli_close($con);
}


function displayRankingsEq3() {
    // Connect to the database
    include 'php/db-connect.php';

    // Set up SQL query to retrieve data from database tables
    $sql_maketable = "SELECT live_user_information.id, live_user_information.firstname, live_user_information.surname, live_user_information.avatar, live_user_information.faveteam, live_user_information.startpos, live_user_information.currpos, live_user_information.lastpos, test_user_predictions_groups.points_total,
                        FIND_IN_SET(points_total, (
                            SELECT GROUP_CONCAT(DISTINCT points_total ORDER BY points_total DESC)
                            FROM test_user_predictions_groups )
                        ) AS rank
                        FROM live_user_information
                        INNER JOIN test_user_predictions_groups ON live_user_information.id = test_user_predictions_groups.id
                        ORDER BY rank ASC, surname ASC";

    $sql_matchresults = "SELECT * FROM live_match_results";

    // Execute the query and return the results or display an appropriate error message
    $table = mysqli_query($con, $sql_maketable) or die(mysqli_error($con));
    $result = mysqli_query($con, $sql_matchresults) or die(mysqli_error($con));

    echo "<div class='table-responsive'>";
    echo "<table id='rankingsTable' class='table table-striped'>";
    echo "<thead><tr><th>Rank</th><th>Move</th><th>Player</th><th>Points</th></tr></thead>";
    echo "<tbody>";

    // Keep track of the previous rank to identify non-unique ranks
    $prevRank = null;
    while ($row = mysqli_fetch_assoc($table)) {
        // Check if match results table contains any data
        if (mysqli_num_rows($result) == 0) {
            $rank = $row["startpos"];
        } else {
            $rank = $row["rank"];
        }

        // Append '=' if the rank is not unique
        if ($rank == $prevRank) {
            $displayRank = '<strong>'. $rank . '</strong>'."=";
        } else {
            $displayRank = '<strong>'. $rank . '</strong>';
        }

        // Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
        if ($row["lastpos"] > $row["currpos"]) {
            $diff = $row["lastpos"] - $row["currpos"];
            $move = "<span class='text-success'><i class='bi bi-caret-up-fill'></i>" . $diff . "</span>";
        } elseif ($row["lastpos"] < $row["currpos"]) {
            $diff = $row["currpos"] - $row["lastpos"];
            $move = "<span class='text-danger'><i class='bi bi-caret-down-fill'></i>" . $diff . "</span>";
        } else {
            $diff = 0;
            $move = "<span class='text-secondary'><i class='bi bi-caret-right-fill'></i>" . $diff . "</span>";
        }

        // Ensure both name variables begin with upper case letters
        $uppCaseFN = ucfirst($row["firstname"]);
        $uppCaseSN = ucfirst($row["surname"]);

        // Display the table complete with all data variables
        echo "<tr>";		
        echo "<td><span class=''>" . $displayRank . "</span></td>";
		echo "<td><span class=''>" . $move . "</span></td>";		
        echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;<a href='user.php?id=".$row["id"]."'>".$uppCaseFN." ".$uppCaseSN."</a></td>";
        echo "<td>".$row["points_total"]."</td>";		
        echo "</tr>";

        // Update the previous rank
        $prevRank = $rank;
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    mysqli_close($con);
}

function displayRankingsEq4() {
    // Connect to the database
    include 'php/db-connect.php';

    // Set up SQL query to retrieve data from database tables
    $sql_maketable = "SELECT lui.id, lui.firstname, lui.surname, lui.avatar, lui.faveteam, lui.startpos, lui.currpos, lui.lastpos, 
						(lup_groups.points_total + IFNULL(lup_ro32.points_total, 0) + IFNULL(lup_ro16.points_total, 0)) AS points_total,
						FIND_IN_SET((lup_groups.points_total + IFNULL(lup_ro32.points_total, 0) + IFNULL(lup_ro16.points_total, 0)), (
							SELECT GROUP_CONCAT(DISTINCT (lup_groups.points_total + IFNULL(lup_ro32.points_total, 0) + IFNULL(lup_ro16.points_total, 0)) ORDER BY (lup_groups.points_total + IFNULL(lup_ro32.points_total, 0) + IFNULL(lup_ro16.points_total, 0)) DESC)
							FROM live_user_predictions_groups lup_groups
							LEFT JOIN live_user_predictions_ro32 lup_ro32 ON lup_groups.id = lup_ro32.id
							LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lup_groups.id = lup_ro16.id)
						) AS rank
					FROM live_user_information lui
					INNER JOIN live_user_predictions_groups lup_groups ON lui.id = lup_groups.id
					LEFT JOIN live_user_predictions_ro32 lup_ro32 ON lui.id = lup_ro32.id
					LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lui.id = lup_ro16.id
					ORDER BY rank ASC, surname ASC";

    $sql_matchresults = "SELECT * FROM live_match_results";

    // Execute the query and return the results or display an appropriate error message
    $table = mysqli_query($con, $sql_maketable) or die(mysqli_error($con));
    $result = mysqli_query($con, $sql_matchresults) or die(mysqli_error($con));

    echo "<div class='table-responsive'>";
    echo "<table id='rankingsTable' class='table table-striped'>";
    echo "<thead><tr><th>Rank</th><th>Move</th><th>Player</th><th>Points</th></tr></thead>";
    echo "<tbody>";

    // Keep track of the previous rank to identify non-unique ranks
    $prevRank = null;
    while ($row = mysqli_fetch_assoc($table)) {
        // Check if match results table contains any data
        if (mysqli_num_rows($result) == 0) {
            $rank = $row["startpos"];
        } else {
            $rank = $row["rank"];
        }

        // Append '=' if the rank is not unique
        if ($rank == $prevRank) {
            $displayRank = '<strong>'. $rank . '</strong>'."=";
        } else {
            $displayRank = '<strong>'. $rank . '</strong>';
        }

        // Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
        if ($row["lastpos"] > $row["currpos"]) {
            $diff = $row["lastpos"] - $row["currpos"];
            $move = "<span class='text-success'><i class='bi bi-caret-up-fill'></i>" . $diff . "</span>";
        } elseif ($row["lastpos"] < $row["currpos"]) {
            $diff = $row["currpos"] - $row["lastpos"];
            $move = "<span class='text-danger'><i class='bi bi-caret-down-fill'></i>" . $diff . "</span>";
        } else {
            $diff = 0;
            $move = "<span class='text-secondary'><i class='bi bi-caret-right-fill'></i>" . $diff . "</span>";
        }

        // Ensure both name variables begin with upper case letters
        $uppCaseFN = ucfirst($row["firstname"]);
        $uppCaseSN = ucfirst($row["surname"]);

        // Display the table complete with all data variables
        echo "<tr>";		
        echo "<td><span class=''>" . $displayRank . "</span></td>";
		echo "<td><span class=''>" . $move . "</span></td>";		
        echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;<a href='user.php?id=".$row["id"]."'>".$uppCaseFN." ".$uppCaseSN."</a></td>";
        echo "<td>".$row["points_total"]."</td>";		
        echo "</tr>";

        // Update the previous rank
        $prevRank = $rank;
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    mysqli_close($con);
}

function displayRankingsEq5() {
    // Connect to the database
    include 'php/db-connect.php';

    // Set up SQL query to retrieve data from database tables
		$sql_maketable = "SELECT lui.id, lui.firstname, lui.surname, lui.avatar, lui.faveteam, lui.location, lui.startpos, lui.currpos, lui.lastpos, lui.signupdate,
						(lup_groups.points_total +
						IFNULL(lup_ro32.points_total, 0) +
						IFNULL(lup_ro16.points_total, 0) +
						IFNULL(lup_qf.points_total, 0) +
						IFNULL(lup_sf.points_total, 0) +
						IFNULL(lup_fi.points_total, 0)) AS points_total,
						FIND_IN_SET(
							(lup_groups.points_total +
							IFNULL(lup_ro32.points_total, 0) +
							IFNULL(lup_ro16.points_total, 0) +
							IFNULL(lup_qf.points_total, 0) +
							IFNULL(lup_sf.points_total, 0) +
							IFNULL(lup_fi.points_total, 0)),
							(
								SELECT GROUP_CONCAT(
									DISTINCT (lup_groups.points_total +
											IFNULL(lup_ro32.points_total, 0) +
											IFNULL(lup_ro16.points_total, 0) +
											IFNULL(lup_qf.points_total, 0) +
											IFNULL(lup_sf.points_total, 0) +
											IFNULL(lup_fi.points_total, 0))
									ORDER BY (lup_groups.points_total +
											IFNULL(lup_ro32.points_total, 0) +
											IFNULL(lup_ro16.points_total, 0) +
											IFNULL(lup_qf.points_total, 0) +
											IFNULL(lup_sf.points_total, 0) +
											IFNULL(lup_fi.points_total, 0)) DESC
								)
								FROM live_user_predictions_groups lup_groups
								LEFT JOIN live_user_predictions_ro32 lup_ro32 ON lup_groups.id = lup_ro32.id
								LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lup_groups.id = lup_ro16.id
								LEFT JOIN live_user_predictions_qf lup_qf ON lup_groups.id = lup_qf.id
								LEFT JOIN live_user_predictions_sf lup_sf ON lup_groups.id = lup_sf.id
								LEFT JOIN live_user_predictions_final lup_fi ON lup_groups.id = lup_fi.id
							)
						) AS rank
					FROM live_user_information lui
					INNER JOIN live_user_predictions_groups lup_groups ON lui.id = lup_groups.id
					LEFT JOIN live_user_predictions_ro32 lup_ro32 ON lui.id = lup_ro32.id
					LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lui.id = lup_ro16.id
					LEFT JOIN live_user_predictions_qf lup_qf ON lui.id = lup_qf.id
					LEFT JOIN live_user_predictions_sf lup_sf ON lui.id = lup_sf.id
					LEFT JOIN live_user_predictions_final lup_fi ON lui.id = lup_fi.id
					ORDER BY rank ASC, surname ASC";

    $sql_matchresults = "SELECT COUNT(*) AS total FROM live_match_schedule WHERE homescore IS NOT NULL AND awayscore IS NOT NULL";

    // Execute the query and return the results or display an appropriate error message
    $table = mysqli_query($con, $sql_maketable) or die(mysqli_error($con));
    $result = mysqli_query($con, $sql_matchresults) or die(mysqli_error($con));
    $resultMeta = mysqli_fetch_assoc($result);
    $hasRecordedResults = ((int) ($resultMeta['total'] ?? 0)) > 0;
    mysqli_free_result($result);

    $rows = [];
    while ($row = mysqli_fetch_assoc($table)) {
        $rows[] = $row;
    }
    mysqli_free_result($table);

    if (!$hasRecordedResults) {
        usort($rows, static function (array $left, array $right): int {
            $leftSignedUp = strtotime((string) ($left['signupdate'] ?? '')) ?: 0;
            $rightSignedUp = strtotime((string) ($right['signupdate'] ?? '')) ?: 0;

            if ($leftSignedUp === $rightSignedUp) {
                return ((int) ($left['id'] ?? 0)) <=> ((int) ($right['id'] ?? 0));
            }

            return $leftSignedUp <=> $rightSignedUp;
        });
    }

    echo "<div class='table-responsive'>";
    echo "<table id='rankingsTable' class='table table-striped'>";
    echo "<thead><tr><th>Rank</th><th>Move</th><th>Player</th><th>Points</th></tr></thead>";
    echo "<tbody>";

    // Keep track of the previous rank to identify non-unique ranks
    $prevRank = null;
    foreach ($rows as $index => $row) {
        if (!$hasRecordedResults) {
            $rank = $index + 1;
        } else {
            $rank = $row["rank"];
        }

        // Append '=' if the rank is not unique
        if ($rank == $prevRank) {
            $displayRank = '<strong>'. $rank . '</strong>'."=";
        } else {
            $displayRank = '<strong>'. $rank . '</strong>';
        }

        // Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
        if (!$hasRecordedResults) {
            $move = "<span class='text-secondary'>-</span>";
        } elseif ($row["lastpos"] > $row["currpos"]) {
            $diff = $row["lastpos"] - $row["currpos"];
            $move = "<span class='text-success'><i class='bi bi-caret-up-fill'></i>" . $diff . "</span>";
        } elseif ($row["lastpos"] < $row["currpos"]) {
            $diff = $row["currpos"] - $row["lastpos"];
            $move = "<span class='text-danger'><i class='bi bi-caret-down-fill'></i>" . $diff . "</span>";
        } else {
            $diff = 0;
            $move = "<span class='text-secondary'><i class='bi bi-caret-right-fill'></i>" . $diff . "</span>";
        }

        // Ensure both name variables begin with upper case letters
        $uppCaseFN = ucfirst($row["firstname"]);
        $uppCaseSN = ucfirst($row["surname"]);

        $isCurrentUser = isset($_SESSION['id']) && (string) $_SESSION['id'] === (string) $row["id"];
        $rowClass = $isCurrentUser ? " class='rankings-row--me'" : "";
        $youBadge = $isCurrentUser ? "<span class='rankings-you-badge'>You</span>" : "";

        // Display the table complete with all data variables
        echo "<tr" . $rowClass . ">";
        echo "<td><span class=''>" . $displayRank . "</span></td>";
		echo "<td><span class=''>" . $move . "</span></td>";		
        $playerMeta = trim((string) ($row["location"] ?? ''));
        if ($playerMeta === '' || strcasecmp($playerMeta, 'Prefer Not To Say') === 0) {
            $playerMeta = trim((string) ($row["faveteam"] ?? ''));
        }
        if ($playerMeta === '' || strcasecmp($playerMeta, 'Prefer Not To Say') === 0) {
            $playerMeta = '-';
        }

        echo "<td>"
            . "<div class='rankings-player-cell'>"
            . "<img src='" . $row["avatar"] . "' class='img-responsive pull-left' width='20px'>"
            . "<div class='rankings-player-cell__text'>"
            . "<div><a href='user.php?id=" . $row["id"] . "'>" . $uppCaseFN . " " . $uppCaseSN . "</a>" . $youBadge . "</div>"
            . ($playerMeta !== '' ? "<small>" . htmlspecialchars($playerMeta, ENT_QUOTES) . "</small>" : "")
            . "</div>"
            . "</div>"
            . "</td>";
        //echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;".$uppCaseFN." ".$uppCaseSN."</td>";
        echo "<td><span class='rankings-points-pill'>" . $row["points_total"] . "</span></td>";
        echo "</tr>";

        // Update the previous rank
        $prevRank = $rank;
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    mysqli_close($con);
}

function displayRankingsEq2() {
    // Connect to the database
    include 'php/db-connect.php';

    // Set up SQL query to retrieve data from database tables
    $sql_maketable = "SELECT lui.id, lui.firstname, lui.surname, lui.avatar, lui.faveteam, lui.startpos, lui.currpos, lui.lastpos, (lup_groups.points_total + IFNULL(lup_ro32.points_total, 0) + lup_ro16.points_total) AS points_total,
						FIND_IN_SET((lup_groups.points_total + IFNULL(lup_ro32.points_total, 0) + lup_ro16.points_total), (
							SELECT GROUP_CONCAT(DISTINCT (lup_groups.points_total + IFNULL(lup_ro32.points_total, 0) + lup_ro16.points_total) ORDER BY (lup_groups.points_total + IFNULL(lup_ro32.points_total, 0) + lup_ro16.points_total) DESC)
							FROM live_user_predictions_groups lup_groups
							LEFT JOIN live_user_predictions_ro32 lup_ro32 ON lup_groups.id = lup_ro32.id
							JOIN live_user_predictions_ro16 lup_ro16 ON lup_groups.id = lup_ro16.id)
						) AS rank
						FROM live_user_information lui
						INNER JOIN live_user_predictions_groups lup_groups ON lui.id = lup_groups.id
						LEFT JOIN live_user_predictions_ro32 lup_ro32 ON lui.id = lup_ro32.id
						INNER JOIN live_user_predictions_ro16 lup_ro16 ON lui.id = lup_ro16.id
						ORDER BY rank ASC, surname ASC";

    $sql_matchresults = "SELECT * FROM live_match_results";

    // Execute the query and return the results or display an appropriate error message
    $table = mysqli_query($con, $sql_maketable) or die(mysqli_error($con));
    $result = mysqli_query($con, $sql_matchresults) or die(mysqli_error($con));

    echo "<div class='table-responsive'>";
    echo "<table id='rankingsTable' class='table table-striped'>";
    echo "<thead><tr><th>Rank</th><th>Move</th><th>Player</th><th>Points</th></tr></thead>";
    echo "<tbody>";

    // Keep track of the previous rank to identify non-unique ranks
    $prevRank = null;
    while ($row = mysqli_fetch_assoc($table)) {
        // Check if match results table contains any data
        if (mysqli_num_rows($result) == 0) {
            $rank = $row["startpos"];
        } else {
            $rank = $row["rank"];
        }

        // Append '=' if the rank is not unique
        if ($rank == $prevRank) {
            $displayRank = '<strong>'. $rank . '</strong>'."=";
        } else {
            $displayRank = '<strong>'. $rank . '</strong>';
        }

        // Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
        if ($row["lastpos"] > $row["currpos"]) {
            $diff = $row["lastpos"] - $row["currpos"];
            $move = "<span class='text-success'><i class='bi bi-caret-up-fill'></i>" . $diff . "</span>";
        } elseif ($row["lastpos"] < $row["currpos"]) {
            $diff = $row["currpos"] - $row["lastpos"];
            $move = "<span class='text-danger'><i class='bi bi-caret-down-fill'></i>" . $diff . "</span>";
        } else {
            $diff = 0;
            $move = "<span class='text-secondary'><i class='bi bi-caret-right-fill'></i>" . $diff . "</span>";
        }

        // Ensure both name variables begin with upper case letters
        $uppCaseFN = ucfirst($row["firstname"]);
        $uppCaseSN = ucfirst($row["surname"]);

        // Display the table complete with all data variables
        echo "<tr>";		
        echo "<td><span class=''>" . $displayRank . "</span></td>";
		echo "<td><span class=''>" . $move . "</span></td>";		
        echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;<a href='user.php?id=".$row["id"]."'>".$uppCaseFN." ".$uppCaseSN."</a></td>";
        echo "<td>".$row["points_total"]."</td>";		
        echo "</tr>";

        // Update the previous rank
        $prevRank = $rank;
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    mysqli_close($con);
}

function displayInfo() {
	// Connect to the database
	include 'php/db-connect.php';

	$sql_countusers = "SELECT count(*) AS totalusers FROM live_user_information";

	// Execute the query and return the result or display appropriate error message
	$totalusers = mysqli_query($con, $sql_countusers) or die(mysqli_error());
	// For each instance of the returned result
	
	while ($row = mysqli_fetch_assoc($totalusers)) {
		$countoftotalusers = $row["totalusers"];
		//$prizefund = ($countoftotalusers * 3);
		print "<p class='text-center' style='margin: 0px 15px;'>";
		printf("Players: %d", $countoftotalusers-1);
		printf("<span style='margin: 0px 15px;'>");
		//printf("Prize Fund: £%d.00", $prizefund);
		print "Prizes TBC</span></p>";	
/*
		print "<p class='text-center' style='margin: 0px 10px;'>";
		print("Players: 92");
		print("<span style='margin: 0px 15px;'>");
		print("Prizes TBC");
		print "</span></p>";
*/
	}
}

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

function abbrTeam($team) {
	// Include the configuration file
	include 'php/config.php';

	// If user is on a small mobile device, do...
	if(isMobile()){
		$teamupper = strtoupper($team);
		$teamabb = substr($teamupper,0,3);
		echo $teamabb;
	}
	// Else users on desktop get...
	else {
		echo $team;
	}
}
?>
