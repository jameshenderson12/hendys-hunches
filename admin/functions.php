<?php
session_start();
$page_title = 'Admin Functions';

require_once dirname(__DIR__) . '/php/auth.php';
require_once dirname(__DIR__) . '/php/config.php';
require_once dirname(__DIR__) . '/php/process.php';
require_once dirname(__DIR__) . '/php/badges.php';

hh_require_admin('../dashboard.php');

include '../php/db-connect.php';

function hh_admin_table_exists(mysqli $con, string $table): bool
{
    $escaped = mysqli_real_escape_string($con, $table);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$escaped}'");

    if (!$result) {
        return false;
    }

    $exists = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);

    return $exists;
}

function hh_admin_fetch_all(mysqli $con, string $sql): array
{
    $result = mysqli_query($con, $sql);
    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_free_result($result);

    return $rows;
}

function hh_admin_existing_prediction_backup_tables(mysqli $con): array
{
    $tables = [
        'backup_user_predictions_groups',
        'backup_user_predictions_ro32',
        'backup_user_predictions_ro16',
        'backup_user_predictions_qf',
        'backup_user_predictions_sf',
        'backup_user_predictions_final',
    ];

    return array_values(array_filter(
        $tables,
        static fn(string $table): bool => hh_admin_table_exists($con, $table)
    ));
}

function hh_admin_restore_prediction_backup_with_connection(mysqli $con, int $userId, string $stageKey): array
{
    if ($userId <= 0) {
        throw new RuntimeException('Choose a valid user to restore.');
    }

    $contexts = hh_prediction_stage_contexts();
    $context = $contexts[$stageKey] ?? null;
    if (!$context) {
        throw new RuntimeException('Choose a valid prediction stage to restore.');
    }

    $liveTable = (string) ($context['table'] ?? '');
    if ($liveTable === '') {
        throw new RuntimeException('That stage is not configured correctly.');
    }

    $backupTable = hh_prediction_backup_table_name($liveTable);
    if (!hh_admin_table_exists($con, $backupTable)) {
        throw new RuntimeException('No backup table exists for that stage yet.');
    }

    $userStatement = mysqli_prepare(
        $con,
        "SELECT id, username, firstname, surname
         FROM live_user_information
         WHERE id = ?
         LIMIT 1"
    );

    if (!$userStatement) {
        throw new RuntimeException(mysqli_error($con));
    }

    mysqli_stmt_bind_param($userStatement, 'i', $userId);
    mysqli_stmt_execute($userStatement);
    $userResult = mysqli_stmt_get_result($userStatement);
    $user = $userResult instanceof mysqli_result ? (mysqli_fetch_assoc($userResult) ?: null) : null;
    if ($userResult instanceof mysqli_result) {
        mysqli_free_result($userResult);
    }
    mysqli_stmt_close($userStatement);

    if (!$user) {
        throw new RuntimeException('That user could not be found.');
    }

    $backupExists = hh_prediction_backup_row_exists($con, $backupTable, $userId);
    if (!$backupExists) {
        throw new RuntimeException('There is no preserved backup for that user and stage.');
    }

    mysqli_begin_transaction($con);

    try {
        if (!mysqli_query($con, "DELETE FROM {$liveTable} WHERE id = " . (int) $userId . " LIMIT 1")) {
            throw new RuntimeException(mysqli_error($con));
        }

        $restoreStatement = mysqli_prepare(
            $con,
            "INSERT INTO {$liveTable} SELECT * FROM {$backupTable} WHERE id = ? LIMIT 1"
        );

        if (!$restoreStatement) {
            throw new RuntimeException(mysqli_error($con));
        }

        mysqli_stmt_bind_param($restoreStatement, 'i', $userId);
        if (!mysqli_stmt_execute($restoreStatement)) {
            $error = mysqli_stmt_error($restoreStatement);
            mysqli_stmt_close($restoreStatement);
            throw new RuntimeException($error);
        }
        mysqli_stmt_close($restoreStatement);

        hh_recalculate_all_prediction_points($con);
        mysqli_commit($con);
    } catch (Throwable $exception) {
        mysqli_rollback($con);
        throw $exception;
    }

    $displayName = trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? ''));
    if ($displayName === '') {
        $displayName = (string) ($user['username'] ?? 'Player');
    }

    return [
        'display_name' => $displayName,
        'username' => (string) ($user['username'] ?? ''),
        'stage_label' => (string) ($context['label'] ?? $stageKey),
    ];
}

function hh_admin_random_prediction_score(): int
{
    $rand = mt_rand(1, 1000) / 1000;

    if ($rand < 0.37) {
        return 0;
    }
    if ($rand < 0.67) {
        return 1;
    }
    if ($rand < 0.88) {
        return 2;
    }
    if ($rand < 0.95) {
        return 3;
    }
    if ($rand < 0.99) {
        return 4;
    }

    return 5;
}

function hh_admin_populate_prediction_stage_for_user_with_connection(mysqli $con, int $userId, string $stageKey): array
{
    if ($userId <= 0) {
        throw new RuntimeException('Choose a valid player to populate.');
    }

    $contexts = hh_prediction_stage_contexts();
    $context = $contexts[$stageKey] ?? null;
    if (!$context) {
        throw new RuntimeException('Choose a valid prediction stage.');
    }

    $userStatement = mysqli_prepare(
        $con,
        "SELECT id, username, firstname, surname
         FROM live_user_information
         WHERE id = ?
         LIMIT 1"
    );

    if (!$userStatement) {
        throw new RuntimeException(mysqli_error($con));
    }

    mysqli_stmt_bind_param($userStatement, 'i', $userId);
    mysqli_stmt_execute($userStatement);
    $userResult = mysqli_stmt_get_result($userStatement);
    $user = $userResult instanceof mysqli_result ? (mysqli_fetch_assoc($userResult) ?: null) : null;
    if ($userResult instanceof mysqli_result) {
        mysqli_free_result($userResult);
    }
    mysqli_stmt_close($userStatement);

    if (!$user) {
        throw new RuntimeException('That player could not be found.');
    }

    $scoreValuesByIndex = [];
    $fixtureStatement = mysqli_prepare(
        $con,
        "SELECT match_number, homescore, awayscore
         FROM live_match_schedule
         WHERE match_number BETWEEN ? AND ?
         ORDER BY match_number ASC"
    );

    if (!$fixtureStatement) {
        throw new RuntimeException(mysqli_error($con));
    }

    $fixtureStart = (int) ($context['fixture_start'] ?? 0);
    $fixtureEnd = (int) ($context['fixture_end'] ?? 0);
    mysqli_stmt_bind_param($fixtureStatement, 'ii', $fixtureStart, $fixtureEnd);
    mysqli_stmt_execute($fixtureStatement);
    $fixtureResult = mysqli_stmt_get_result($fixtureStatement);

    if ($fixtureResult instanceof mysqli_result) {
        while ($fixture = mysqli_fetch_assoc($fixtureResult)) {
            $matchNumber = (int) ($fixture['match_number'] ?? 0);
            if ($matchNumber <= 0) {
                continue;
            }

            $homeIndex = ($matchNumber * 2) - 1;
            $awayIndex = $matchNumber * 2;
            $isRecorded = $fixture['homescore'] !== null && $fixture['awayscore'] !== null;

            if ($isRecorded) {
                $scoreValuesByIndex[$homeIndex] = null;
                $scoreValuesByIndex[$awayIndex] = null;
                continue;
            }

            $scoreValuesByIndex[$homeIndex] = hh_admin_random_prediction_score();
            $scoreValuesByIndex[$awayIndex] = hh_admin_random_prediction_score();
        }
        mysqli_free_result($fixtureResult);
    }

    mysqli_stmt_close($fixtureStatement);

    hh_upsert_prediction_stage_row_for_user_with_connection(
        $con,
        $stageKey,
        (int) ($user['id'] ?? 0),
        (string) ($user['username'] ?? ''),
        (string) ($user['firstname'] ?? ''),
        (string) ($user['surname'] ?? ''),
        $scoreValuesByIndex
    );

    return [
        'id' => (int) ($user['id'] ?? 0),
        'username' => (string) ($user['username'] ?? ''),
        'display_name' => trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? '')) ?: ('@' . (string) ($user['username'] ?? 'player')),
        'stage_label' => (string) ($context['label'] ?? $stageKey),
    ];
}

function hh_admin_preview_table(mysqli $con, string $table, int $limit = 30): array
{
    $rows = [];
    $columns = [];

    $result = mysqli_query($con, "SELECT * FROM {$table} LIMIT {$limit}");
    if (!$result) {
        return ['columns' => $columns, 'rows' => $rows];
    }

    $fieldInfo = mysqli_fetch_fields($result);
    foreach ($fieldInfo as $field) {
        $columns[] = $field->name;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_free_result($result);

    return ['columns' => $columns, 'rows' => $rows];
}

function hh_admin_fetch_table_names(mysqli $con): array
{
    $result = mysqli_query($con, "SHOW TABLES");
    if (!($result instanceof mysqli_result)) {
        return [];
    }

    $tables = [];
    while ($row = mysqli_fetch_row($result)) {
        $tableName = trim((string) ($row[0] ?? ''));
        if ($tableName !== '') {
            $tables[] = $tableName;
        }
    }
    mysqli_free_result($result);

    sort($tables);

    return $tables;
}

function hh_admin_humanize_table_name(string $tableName): string
{
    $label = preg_replace('/^(live_|backup_)/', '', $tableName);
    $label = str_replace('_', ' ', (string) $label);
    $label = ucwords($label);

    if (str_starts_with($tableName, 'backup_')) {
        return 'Backup · ' . $label;
    }

    return $label;
}

function hh_admin_preview_table_paged(mysqli $con, string $table, string $rowMode = '30', int $page = 1, int $allPageSize = 250): array
{
    $page = max(1, $page);
    $allowedModes = ['10', '30', '50', '100', '250', '500', 'all'];
    if (!in_array($rowMode, $allowedModes, true)) {
        $rowMode = '30';
    }

    $countResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM {$table}");
    $totalRows = 0;
    if ($countResult instanceof mysqli_result) {
        $countRow = mysqli_fetch_assoc($countResult) ?: [];
        $totalRows = (int) ($countRow['total'] ?? 0);
        mysqli_free_result($countResult);
    }

    $limit = $rowMode === 'all' ? $allPageSize : (int) $rowMode;
    $offset = $rowMode === 'all' ? (($page - 1) * $limit) : 0;
    $maxPage = $rowMode === 'all' && $limit > 0 ? max(1, (int) ceil($totalRows / $limit)) : 1;
    if ($page > $maxPage) {
        $page = $maxPage;
        $offset = $rowMode === 'all' ? (($page - 1) * $limit) : 0;
    }

    $result = mysqli_query($con, "SELECT * FROM {$table} LIMIT {$limit} OFFSET {$offset}");
    if (!$result) {
        return [
            'columns' => [],
            'rows' => [],
            'total_rows' => $totalRows,
            'row_mode' => $rowMode,
            'page' => $page,
            'page_size' => $limit,
            'page_count' => $maxPage,
            'showing_from' => 0,
            'showing_to' => 0,
            'is_paginated' => $rowMode === 'all' && $totalRows > $limit,
        ];
    }

    $columns = [];
    foreach (mysqli_fetch_fields($result) as $field) {
        $columns[] = $field->name;
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_free_result($result);

    $showingFrom = $totalRows > 0 ? ($offset + 1) : 0;
    $showingTo = $offset + count($rows);

    return [
        'columns' => $columns,
        'rows' => $rows,
        'total_rows' => $totalRows,
        'row_mode' => $rowMode,
        'page' => $page,
        'page_size' => $limit,
        'page_count' => $maxPage,
        'showing_from' => $showingFrom,
        'showing_to' => $showingTo,
        'is_paginated' => $rowMode === 'all' && $totalRows > $limit,
    ];
}

function hh_admin_badge_holders(mysqli $con, string $badgeToken): array
{
    $badgeToken = trim($badgeToken);
    if ($badgeToken === '' || !hh_badge_table_exists($con)) {
        return [];
    }

    $statement = mysqli_prepare(
        $con,
        "SELECT u.id, u.username, u.firstname, u.surname, b.awarded_at
         FROM " . hh_badge_table_name() . " b
         INNER JOIN live_user_information u ON u.id = b.user_id
         WHERE b.badge_token = ?
         ORDER BY u.surname ASC, u.firstname ASC, u.username ASC"
    );

    if (!$statement) {
        return [];
    }

    mysqli_stmt_bind_param($statement, 's', $badgeToken);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $rows = [];
    if ($result instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);
    }
    mysqli_stmt_close($statement);

    return $rows;
}

function hh_admin_prediction_score_columns(array $context): array
{
    $columns = [];
    $start = (int) ($context['score_start'] ?? 0);
    $end = (int) ($context['score_end'] ?? -1);

    for ($scoreIndex = $start; $scoreIndex <= $end; $scoreIndex++) {
        if ($scoreIndex > 0) {
            $columns[] = 'score' . $scoreIndex . '_p';
        }
    }

    return $columns;
}

function hh_admin_prediction_integrity_audit(mysqli $con, array $users): array
{
    $audit = [];
    $expectedUsers = count($users);
    $contexts = hh_prediction_stage_contexts();

    foreach ($contexts as $stageKey => $context) {
        $table = (string) ($context['table'] ?? '');
        $scoreColumns = hh_admin_prediction_score_columns($context);
        $scoreChecks = [];
        foreach ($scoreColumns as $column) {
            $scoreChecks[] = "{$column} IS NOT NULL";
        }

        $summary = [
            'key' => $stageKey,
            'label' => (string) ($context['label'] ?? $stageKey),
            'table' => $table,
            'expected_users' => $expectedUsers,
            'rows' => 0,
            'complete_rows' => 0,
            'duplicate_ids' => 0,
            'duplicate_usernames' => 0,
            'missing_users' => [],
            'incomplete_rows' => [],
            'orphan_rows' => [],
            'status' => 'missing-table',
        ];

        if ($table === '' || !hh_admin_table_exists($con, $table)) {
            $audit[] = $summary;
            continue;
        }

        $countResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM {$table}");
        if ($countResult instanceof mysqli_result) {
            $countRow = mysqli_fetch_assoc($countResult);
            $summary['rows'] = (int) ($countRow['total'] ?? 0);
            mysqli_free_result($countResult);
        }

        $completeWhere = !empty($scoreChecks) ? implode(' AND ', $scoreChecks) : '1 = 1';
        $completeResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM {$table} WHERE {$completeWhere}");
        if ($completeResult instanceof mysqli_result) {
            $completeRow = mysqli_fetch_assoc($completeResult);
            $summary['complete_rows'] = (int) ($completeRow['total'] ?? 0);
            mysqli_free_result($completeResult);
        }

        $duplicateIdResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM (SELECT id FROM {$table} GROUP BY id HAVING COUNT(*) > 1) duplicates");
        if ($duplicateIdResult instanceof mysqli_result) {
            $duplicateIdRow = mysqli_fetch_assoc($duplicateIdResult);
            $summary['duplicate_ids'] = (int) ($duplicateIdRow['total'] ?? 0);
            mysqli_free_result($duplicateIdResult);
        }

        $duplicateUsernameResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM (SELECT username FROM {$table} GROUP BY username HAVING COUNT(*) > 1) duplicates");
        if ($duplicateUsernameResult instanceof mysqli_result) {
            $duplicateUsernameRow = mysqli_fetch_assoc($duplicateUsernameResult);
            $summary['duplicate_usernames'] = (int) ($duplicateUsernameRow['total'] ?? 0);
            mysqli_free_result($duplicateUsernameResult);
        }

        $missingUsers = hh_admin_fetch_all(
            $con,
            "SELECT lui.id, lui.username, lui.firstname, lui.surname
             FROM live_user_information lui
             LEFT JOIN {$table} stage ON stage.id = lui.id
             WHERE stage.id IS NULL
             ORDER BY lui.surname ASC, lui.firstname ASC"
        );
        $summary['missing_users'] = $missingUsers;

        $incompleteWhere = !empty($scoreChecks)
            ? 'id IS NOT NULL AND NOT (' . implode(' AND ', $scoreChecks) . ')'
            : '0 = 1';
        $summary['incomplete_rows'] = hh_admin_fetch_all(
            $con,
            "SELECT id, username, firstname, surname, lastupdate
             FROM {$table}
             WHERE {$incompleteWhere}
             ORDER BY surname ASC, firstname ASC"
        );

        $summary['orphan_rows'] = hh_admin_fetch_all(
            $con,
            "SELECT stage.id, stage.username, stage.firstname, stage.surname, stage.lastupdate
             FROM {$table} stage
             LEFT JOIN live_user_information lui ON lui.id = stage.id
             WHERE lui.id IS NULL
             ORDER BY stage.surname ASC, stage.firstname ASC"
        );

        $hasIssues =
            $summary['rows'] !== $expectedUsers
            || !empty($summary['missing_users'])
            || !empty($summary['incomplete_rows'])
            || !empty($summary['orphan_rows'])
            || $summary['duplicate_ids'] > 0
            || $summary['duplicate_usernames'] > 0;

        $summary['status'] = $hasIssues ? 'review' : 'ready';
        $audit[] = $summary;
    }

    return $audit;
}

function hh_admin_stage_badge_class(string $stageKey): string
{
    return match ($stageKey) {
        'groups' => 'bg-success-subtle text-success-emphasis',
        'ro32' => 'bg-primary-subtle text-primary-emphasis',
        'ro16' => 'bg-info-subtle text-info-emphasis',
        'qf' => 'bg-warning-subtle text-warning-emphasis',
        'sf' => 'bg-danger-subtle text-danger-emphasis',
        'final' => 'bg-dark-subtle text-dark-emphasis',
        default => 'bg-secondary-subtle text-secondary-emphasis',
    };
}

function hh_admin_scoring_audit(mysqli $con, int $matchNumber): array
{
    $context = hh_prediction_stage_context_for_match_number($matchNumber);
    $audit = [
        'status' => 'unavailable',
        'match_number' => $matchNumber,
        'context' => $context,
        'fixture' => null,
        'actual_home' => null,
        'actual_away' => null,
        'rows' => [],
        'summary' => [
            'players' => 0,
            'submitted' => 0,
            'perfect' => 0,
            'strong' => 0,
            'outcome' => 0,
            'single' => 0,
            'miss' => 0,
        ],
        'message' => '',
    ];

    if (!$context || empty($context['table'])) {
        $audit['message'] = 'This fixture does not map to a prediction stage.';
        return $audit;
    }

    $fixtureStatement = mysqli_prepare(
        $con,
        "SELECT match_number, stage, round_number, date, kotime, venue, hometeam, awayteam, hometeamimg, awayteamimg, homescore, awayscore
         FROM live_match_schedule
         WHERE match_number = ?
         LIMIT 1"
    );

    if (!$fixtureStatement) {
        $audit['message'] = 'The fixture could not be loaded.';
        return $audit;
    }

    mysqli_stmt_bind_param($fixtureStatement, 'i', $matchNumber);
    mysqli_stmt_execute($fixtureStatement);
    $fixtureResult = mysqli_stmt_get_result($fixtureStatement);
    $fixture = $fixtureResult instanceof mysqli_result ? mysqli_fetch_assoc($fixtureResult) : null;
    if ($fixtureResult instanceof mysqli_result) {
        mysqli_free_result($fixtureResult);
    }
    mysqli_stmt_close($fixtureStatement);

    if (!$fixture) {
        $audit['message'] = 'That fixture could not be found in the schedule.';
        return $audit;
    }

    $audit['fixture'] = $fixture;
    $indexes = hh_fixture_score_indexes($matchNumber);
    $scoreFields = [
        'home' => 'score' . $indexes['home'] . '_r',
        'away' => 'score' . $indexes['away'] . '_r',
    ];

    $latestResult = mysqli_query(
        $con,
        "SELECT {$scoreFields['home']} AS actual_home, {$scoreFields['away']} AS actual_away
         FROM live_match_results
         ORDER BY match_id DESC
         LIMIT 1"
    );

    if ($latestResult instanceof mysqli_result) {
        $snapshot = mysqli_fetch_assoc($latestResult) ?: [];
        mysqli_free_result($latestResult);
        $audit['actual_home'] = $snapshot['actual_home'] ?? null;
        $audit['actual_away'] = $snapshot['actual_away'] ?? null;
    }

    if ($audit['actual_home'] === null || $audit['actual_away'] === null) {
        $audit['actual_home'] = $fixture['homescore'] ?? null;
        $audit['actual_away'] = $fixture['awayscore'] ?? null;
    }

    $table = (string) $context['table'];
    if (!hh_admin_table_exists($con, $table)) {
        $audit['message'] = 'The stage table for this fixture has not been created yet.';
        return $audit;
    }

    $predictionSql = "
        SELECT lui.id, lui.username, lui.firstname, lui.surname,
               stage.score{$indexes['home']}_p AS pred_home,
               stage.score{$indexes['away']}_p AS pred_away
        FROM live_user_information lui
        LEFT JOIN {$table} stage ON stage.id = lui.id
        ORDER BY lui.surname ASC, lui.firstname ASC
    ";
    $predictionRows = hh_admin_fetch_all($con, $predictionSql);

    foreach ($predictionRows as $row) {
        $detail = hh_prediction_fixture_score_detail(
            $row['pred_home'] ?? null,
            $row['pred_away'] ?? null,
            $audit['actual_home'],
            $audit['actual_away']
        );

        $audit['rows'][] = [
            'id' => (int) ($row['id'] ?? 0),
            'username' => (string) ($row['username'] ?? ''),
            'firstname' => (string) ($row['firstname'] ?? ''),
            'surname' => (string) ($row['surname'] ?? ''),
            'pred_home' => $row['pred_home'] ?? null,
            'pred_away' => $row['pred_away'] ?? null,
            'detail' => $detail,
        ];

        $audit['summary']['players']++;
        if (!empty($detail['submitted'])) {
            $audit['summary']['submitted']++;
        }

        switch ($detail['category']) {
            case 'perfect':
                $audit['summary']['perfect']++;
                break;
            case 'strong':
                $audit['summary']['strong']++;
                break;
            case 'outcome':
                $audit['summary']['outcome']++;
                break;
            case 'single':
                $audit['summary']['single']++;
                break;
            case 'miss':
            case 'missing':
                $audit['summary']['miss']++;
                break;
        }
    }

    $audit['status'] = is_numeric($audit['actual_home']) && is_numeric($audit['actual_away']) ? 'ready' : 'awaiting-result';
    $audit['message'] = $audit['status'] === 'ready'
        ? 'This view follows the latest saved result snapshot, so it should match the live scoring engine.'
        : 'No saved result exists for this fixture yet, so the audit is waiting for a score.';

    return $audit;
}

function hh_admin_table_editor_config(): array
{
    return [
        'live_user_information' => [
            'primary_key' => 'id',
            'label' => 'Player profile',
            'row_sql' => "SELECT id AS row_id, CONCAT(firstname, ' ', surname, ' (@', username, ')') AS row_label
                          FROM live_user_information
                          ORDER BY surname ASC, firstname ASC",
            'columns' => [
                'firstname' => ['label' => 'First name', 'type' => 'text'],
                'surname' => ['label' => 'Surname', 'type' => 'text'],
                'email' => ['label' => 'Email', 'type' => 'email'],
                'avatar' => ['label' => 'Avatar path', 'type' => 'text'],
                'fieldofwork' => ['label' => 'Field of expertise', 'type' => 'text'],
                'location' => ['label' => 'Location', 'type' => 'text'],
                'faveteam' => ['label' => 'Favourite team', 'type' => 'text'],
                'tournwinner' => ['label' => 'Tournament winner pick', 'type' => 'text'],
                'haspaid' => ['label' => 'Entry paid', 'type' => 'select', 'options' => ['Yes', 'No']],
            ],
        ],
        'live_match_schedule' => [
            'primary_key' => 'id',
            'label' => 'Fixture record',
            'row_sql' => "SELECT id AS row_id, CONCAT('Match ', COALESCE(match_number, id), ' · ', hometeam, ' v ', awayteam) AS row_label
                          FROM live_match_schedule
                          ORDER BY COALESCE(match_number, id) ASC",
            'columns' => [
                'date' => ['label' => 'Date', 'type' => 'date', 'nullable' => true],
                'kotime' => ['label' => 'Kick-off', 'type' => 'time'],
                'stage' => ['label' => 'Stage', 'type' => 'text'],
                'round_number' => ['label' => 'Round number', 'type' => 'number', 'nullable' => true],
                'match_number' => ['label' => 'Match number', 'type' => 'number', 'nullable' => true],
                'venue' => ['label' => 'Venue', 'type' => 'text'],
                'hometeam' => ['label' => 'Home team', 'type' => 'text'],
                'hometeamimg' => ['label' => 'Home flag path', 'type' => 'text'],
                'awayteam' => ['label' => 'Away team', 'type' => 'text'],
                'awayteamimg' => ['label' => 'Away flag path', 'type' => 'text'],
                'homescore' => ['label' => 'Home score', 'type' => 'number', 'nullable' => true],
                'awayscore' => ['label' => 'Away score', 'type' => 'number', 'nullable' => true],
            ],
        ],
        'live_fanzone_posts' => [
            'primary_key' => 'id',
            'label' => 'Fan Zone post',
            'row_sql' => "SELECT id AS row_id,
                                 CONCAT('#', id, ' · ', display_name, ' · ', LEFT(REPLACE(message_body, '\n', ' '), 60)) AS row_label
                          FROM live_fanzone_posts
                          ORDER BY created_at DESC, id DESC
                          LIMIT 200",
            'columns' => [
                'display_name' => ['label' => 'Display name', 'type' => 'text'],
                'message_body' => ['label' => 'Message', 'type' => 'textarea'],
                'is_deleted' => ['label' => 'Deleted', 'type' => 'select', 'options' => ['0', '1']],
                'is_pinned' => ['label' => 'Pinned', 'type' => 'select', 'options' => ['0', '1']],
                'is_announcement' => ['label' => 'Announcement', 'type' => 'select', 'options' => ['0', '1']],
            ],
        ],
    ];
}

function hh_admin_editor_row_options(mysqli $con, array $config): array
{
    if (empty($config['row_sql'])) {
        return [];
    }

    return hh_admin_fetch_all($con, (string) $config['row_sql']);
}

function hh_admin_editor_fetch_row(mysqli $con, string $table, array $config, int $rowId): ?array
{
    if ($rowId <= 0 || empty($config['primary_key'])) {
        return null;
    }

    $primaryKey = (string) $config['primary_key'];
    $statement = mysqli_prepare($con, "SELECT * FROM {$table} WHERE {$primaryKey} = ? LIMIT 1");
    if (!$statement) {
        return null;
    }

    mysqli_stmt_bind_param($statement, 'i', $rowId);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $row = $result instanceof mysqli_result ? (mysqli_fetch_assoc($result) ?: null) : null;
    if ($result instanceof mysqli_result) {
        mysqli_free_result($result);
    }
    mysqli_stmt_close($statement);

    return $row;
}

function hh_admin_editor_normalize_value(string $rawValue, array $meta)
{
    $type = (string) ($meta['type'] ?? 'text');
    $nullable = !empty($meta['nullable']);
    $value = trim($rawValue);

    if ($value === '' && $nullable) {
        return null;
    }

    return $type === 'number'
        ? ($value === '' ? 0 : (int) $value)
        : $value;
}

function hh_admin_bind_dynamic(mysqli_stmt $statement, string $types, array $values): void
{
    $params = [$types];
    foreach ($values as $index => $value) {
        $params[] = &$values[$index];
    }
    call_user_func_array([$statement, 'bind_param'], $params);
}

function hh_admin_quote_strings(array $values): array
{
    return array_map(static fn($value): string => "'" . str_replace("'", "\\'", (string) $value) . "'", $values);
}

function hh_admin_preserved_usernames(mysqli $con, array $userIds): array
{
    if (empty($userIds)) {
        return [];
    }

    $ids = array_values(array_unique(array_map('intval', $userIds)));
    $ids = array_filter($ids, static fn(int $id): bool => $id > 0);
    if (empty($ids)) {
        return [];
    }

    $idList = implode(',', $ids);
    $rows = hh_admin_fetch_all(
        $con,
        "SELECT username
         FROM live_user_information
         WHERE id IN ({$idList})"
    );

    return array_values(array_filter(array_map(static fn(array $row): string => trim((string) ($row['username'] ?? '')), $rows)));
}

function hh_admin_reset_preserving_users_with_connection(mysqli $con, array $userIds): array
{
    $userIds = array_values(array_unique(array_map('intval', $userIds)));
    $userIds = array_filter($userIds, static fn(int $id): bool => $id > 0);

    if (empty($userIds)) {
        throw new RuntimeException('Choose at least one registered user to preserve.');
    }

    $idList = implode(',', $userIds);
    $preservedUsers = hh_admin_fetch_all(
        $con,
        "SELECT id, username, firstname, surname
         FROM live_user_information
         WHERE id IN ({$idList})
         ORDER BY surname ASC, firstname ASC"
    );

    if (count($preservedUsers) !== count($userIds)) {
        throw new RuntimeException('One or more selected users could not be found.');
    }

    $preservedUsernames = array_values(array_filter(array_map(static fn(array $row): string => trim((string) ($row['username'] ?? '')), $preservedUsers)));
    if (empty($preservedUsernames)) {
        throw new RuntimeException('The selected users do not have valid usernames.');
    }

    $quotedUsernames = implode(',', hh_admin_quote_strings($preservedUsernames));
    $preservedSummary = implode(', ', array_map(
        static fn(array $row): string => trim(((string) ($row['firstname'] ?? '')) . ' ' . ((string) ($row['surname'] ?? ''))) ?: (string) ($row['username'] ?? ''),
        $preservedUsers
    ));

    $deleteQueries = [
        "DELETE FROM live_match_results",
        "UPDATE live_match_schedule SET homescore = NULL, awayscore = NULL",
        "DELETE FROM live_group_standings",
        "DELETE FROM live_user_predictions_groups",
        "DELETE FROM live_user_predictions_ro32",
        "DELETE FROM live_user_predictions_ro16",
        "DELETE FROM live_user_predictions_qf",
        "DELETE FROM live_user_predictions_sf",
        "DELETE FROM live_user_predictions_final",
        "DELETE FROM live_user_minileague",
        "DELETE FROM live_poll_votes",
        "DELETE FROM live_poll_options",
        "DELETE FROM live_polls",
        "DELETE FROM live_fanzone_posts",
        "DELETE FROM live_user_logins",
        "DELETE FROM live_temp_information WHERE username NOT IN ({$quotedUsernames})",
        "UPDATE live_temp_information SET temp_pass = '' WHERE username IN ({$quotedUsernames})",
        "DELETE FROM live_user_information WHERE id NOT IN ({$idList})",
        "UPDATE live_user_information SET lastlogin = NULL, login_count = 0",
    ];

    if (hh_runtime_table_exists($con, hh_registration_invite_table_name())) {
        $deleteQueries[] = "DELETE FROM " . hh_registration_invite_table_name();
    }

    if (hh_runtime_table_exists($con, hh_prediction_access_override_table_name())) {
        $deleteQueries[] = "DELETE FROM " . hh_prediction_access_override_table_name();
    }

    foreach (hh_admin_existing_prediction_backup_tables($con) as $backupTable) {
        $deleteQueries[] = "DELETE FROM {$backupTable}";
    }

    if (hh_badge_table_exists($con)) {
        $deleteQueries[] = "DELETE FROM " . hh_badge_table_name();
    }

    mysqli_begin_transaction($con);

    try {
        foreach ($deleteQueries as $query) {
            if (!mysqli_query($con, $query)) {
                throw new RuntimeException(mysqli_error($con));
            }
        }

        hh_reset_initial_rankings_with_connection($con);
        mysqli_commit($con);
    } catch (Throwable $exception) {
        mysqli_rollback($con);
        throw $exception;
    }

    return [
        'count' => count($preservedUsers),
        'summary' => $preservedSummary,
    ];
}

function hh_admin_delete_user_with_connection(mysqli $con, int $userId): array
{
    if ($userId <= 0) {
        throw new RuntimeException('Choose a valid user to delete.');
    }

    $statement = mysqli_prepare(
        $con,
        "SELECT id, username, firstname, surname
         FROM live_user_information
         WHERE id = ?
         LIMIT 1"
    );

    if (!$statement) {
        throw new RuntimeException(mysqli_error($con));
    }

    mysqli_stmt_bind_param($statement, 'i', $userId);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $user = $result instanceof mysqli_result ? (mysqli_fetch_assoc($result) ?: null) : null;
    if ($result instanceof mysqli_result) {
        mysqli_free_result($result);
    }
    mysqli_stmt_close($statement);

    if (!$user) {
        throw new RuntimeException('That user could not be found.');
    }

    $username = trim((string) ($user['username'] ?? ''));
    if ($username === '') {
        throw new RuntimeException('That user record does not have a valid username.');
    }

    $displayName = trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? ''));
    if ($displayName === '') {
        $displayName = $username;
    }

    $quotedUsername = implode(',', hh_admin_quote_strings([$username]));
    $quotedUserId = (int) ($user['id'] ?? 0);

    $queries = [
        "DELETE FROM live_temp_information WHERE username IN ({$quotedUsername})",
        "DELETE FROM live_user_predictions_groups WHERE id = {$quotedUserId} OR username IN ({$quotedUsername})",
        "DELETE FROM live_user_predictions_ro32 WHERE id = {$quotedUserId} OR username IN ({$quotedUsername})",
        "DELETE FROM live_user_predictions_ro16 WHERE id = {$quotedUserId} OR username IN ({$quotedUsername})",
        "DELETE FROM live_user_predictions_qf WHERE id = {$quotedUserId} OR username IN ({$quotedUsername})",
        "DELETE FROM live_user_predictions_sf WHERE id = {$quotedUserId} OR username IN ({$quotedUsername})",
        "DELETE FROM live_user_predictions_final WHERE id = {$quotedUserId} OR username IN ({$quotedUsername})",
        "DELETE FROM live_fanzone_posts WHERE username IN ({$quotedUsername})",
        "DELETE FROM live_user_information WHERE id = {$quotedUserId} LIMIT 1",
    ];

    if (hh_runtime_table_exists($con, hh_prediction_access_override_table_name())) {
        $queries[] = "DELETE FROM " . hh_prediction_access_override_table_name() . " WHERE user_id = {$quotedUserId}";
    }

    if (hh_runtime_table_exists($con, hh_registration_invite_table_name())) {
        $queries[] = "DELETE FROM " . hh_registration_invite_table_name() . " WHERE used_by_user_id = {$quotedUserId}";
    }

    foreach (hh_admin_existing_prediction_backup_tables($con) as $backupTable) {
        $queries[] = "DELETE FROM {$backupTable} WHERE id = {$quotedUserId} OR username IN ({$quotedUsername})";
    }

    if (hh_badge_table_exists($con)) {
        $queries[] = "DELETE FROM " . hh_badge_table_name() . " WHERE user_id = {$quotedUserId}";
    }

    mysqli_begin_transaction($con);

    try {
        foreach ($queries as $query) {
            if (!mysqli_query($con, $query)) {
                throw new RuntimeException(mysqli_error($con));
            }
        }

        hh_reset_initial_rankings_with_connection($con);
        mysqli_commit($con);
    } catch (Throwable $exception) {
        mysqli_rollback($con);
        throw $exception;
    }

    return [
        'id' => $quotedUserId,
        'username' => $username,
        'display_name' => $displayName,
    ];
}

function hh_admin_local_datetime_to_utc_string(string $value): ?string
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }

    $local = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value, new DateTimeZone('Europe/London'));
    if (!($local instanceof DateTimeImmutable)) {
        return null;
    }

    return $local->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
}

function hh_admin_format_utc_for_local_display(?string $value, string $fallback = 'No expiry'): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return $fallback;
    }

    $utc = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone('UTC'));
    if (!($utc instanceof DateTimeImmutable)) {
        return $fallback;
    }

    return $utc->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('D j M Y, g:ia');
}

function hh_admin_issue_registration_invite_with_connection(mysqli $con, string $baseUrl, int $adminId, string $emailHint, string $notes, ?string $expiresAtUtc): array
{
    if (!hh_ensure_registration_invite_table($con)) {
        throw new RuntimeException('The registration invite table could not be prepared.');
    }

    $token = bin2hex(random_bytes(24));
    $statement = mysqli_prepare(
        $con,
        "INSERT INTO " . hh_registration_invite_table_name() . " (invite_token, email_hint, notes, expires_at, created_by)
         VALUES (?, ?, ?, ?, ?)"
    );

    if (!$statement) {
        throw new RuntimeException(mysqli_error($con));
    }

    $emailHintValue = $emailHint !== '' ? $emailHint : null;
    $notesValue = $notes !== '' ? $notes : null;
    $expiresValue = $expiresAtUtc !== '' ? $expiresAtUtc : null;
    mysqli_stmt_bind_param($statement, 'ssssi', $token, $emailHintValue, $notesValue, $expiresValue, $adminId);

    if (!mysqli_stmt_execute($statement)) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException($error);
    }

    $inviteId = (int) mysqli_insert_id($con);
    mysqli_stmt_close($statement);

    $baseUrl = rtrim(trim($baseUrl), '/');
    if ($baseUrl === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $baseUrl = $host !== '' ? $scheme . '://' . $host : '';
    }

    $registrationUrl = ($baseUrl !== '' ? $baseUrl : '') . '/registration.php?invite=' . urlencode($token);

    return [
        'id' => $inviteId,
        'token' => $token,
        'url' => $registrationUrl,
    ];
}

function hh_admin_revoke_registration_invite_with_connection(mysqli $con, int $inviteId): void
{
    if ($inviteId <= 0 || !hh_runtime_table_exists($con, hh_registration_invite_table_name())) {
        throw new RuntimeException('Choose a valid invite to revoke.');
    }

    $statement = mysqli_prepare($con, "DELETE FROM " . hh_registration_invite_table_name() . " WHERE id = ? LIMIT 1");
    if (!$statement) {
        throw new RuntimeException(mysqli_error($con));
    }

    mysqli_stmt_bind_param($statement, 'i', $inviteId);
    mysqli_stmt_execute($statement);
    $affected = mysqli_stmt_affected_rows($statement);
    mysqli_stmt_close($statement);

    if ($affected <= 0) {
        throw new RuntimeException('That invite could not be found.');
    }
}

function hh_admin_list_registration_invites(mysqli $con): array
{
    if (!hh_runtime_table_exists($con, hh_registration_invite_table_name())) {
        return [];
    }

    return hh_admin_fetch_all(
        $con,
        "SELECT invite.id, invite.email_hint, invite.notes, invite.expires_at, invite.used_at, invite.created_at,
                used.username AS used_username, used.firstname AS used_firstname, used.surname AS used_surname
         FROM " . hh_registration_invite_table_name() . " invite
         LEFT JOIN live_user_information used ON used.id = invite.used_by_user_id
         ORDER BY invite.created_at DESC, invite.id DESC
         LIMIT 20"
    );
}

function hh_admin_save_prediction_override_with_connection(mysqli $con, int $userId, string $stageKey, string $grantedUntilUtc, int $adminId, string $reason): array
{
    if ($userId <= 0) {
        throw new RuntimeException('Choose a valid player.');
    }

    $contexts = hh_prediction_stage_contexts();
    if (!isset($contexts[$stageKey])) {
        throw new RuntimeException('Choose a valid stage.');
    }

    if ($grantedUntilUtc === '') {
        throw new RuntimeException('Choose when the override should end.');
    }

    if (!hh_ensure_prediction_access_override_table($con)) {
        throw new RuntimeException('The prediction override table could not be prepared.');
    }

    $userStatement = mysqli_prepare(
        $con,
        "SELECT username, firstname, surname
         FROM live_user_information
         WHERE id = ?
         LIMIT 1"
    );

    if (!$userStatement) {
        throw new RuntimeException(mysqli_error($con));
    }

    mysqli_stmt_bind_param($userStatement, 'i', $userId);
    mysqli_stmt_execute($userStatement);
    $userResult = mysqli_stmt_get_result($userStatement);
    $user = $userResult instanceof mysqli_result ? (mysqli_fetch_assoc($userResult) ?: null) : null;
    if ($userResult instanceof mysqli_result) {
        mysqli_free_result($userResult);
    }
    mysqli_stmt_close($userStatement);

    if (!$user) {
        throw new RuntimeException('That player could not be found.');
    }

    $reasonValue = $reason !== '' ? $reason : null;
    $statement = mysqli_prepare(
        $con,
        "INSERT INTO " . hh_prediction_access_override_table_name() . " (user_id, stage_key, granted_until, reason, granted_by)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE granted_until = VALUES(granted_until), reason = VALUES(reason), granted_by = VALUES(granted_by), created_at = CURRENT_TIMESTAMP"
    );

    if (!$statement) {
        throw new RuntimeException(mysqli_error($con));
    }

    mysqli_stmt_bind_param($statement, 'isssi', $userId, $stageKey, $grantedUntilUtc, $reasonValue, $adminId);
    if (!mysqli_stmt_execute($statement)) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException($error);
    }
    mysqli_stmt_close($statement);

    $displayName = trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? ''));
    if ($displayName === '') {
        $displayName = (string) ($user['username'] ?? 'Player');
    }

    return [
        'display_name' => $displayName,
        'username' => (string) ($user['username'] ?? ''),
        'stage_label' => (string) ($contexts[$stageKey]['label'] ?? $stageKey),
    ];
}

function hh_admin_remove_prediction_override_with_connection(mysqli $con, int $overrideId): void
{
    if ($overrideId <= 0 || !hh_runtime_table_exists($con, hh_prediction_access_override_table_name())) {
        throw new RuntimeException('Choose a valid override to remove.');
    }

    $statement = mysqli_prepare($con, "DELETE FROM " . hh_prediction_access_override_table_name() . " WHERE id = ? LIMIT 1");
    if (!$statement) {
        throw new RuntimeException(mysqli_error($con));
    }

    mysqli_stmt_bind_param($statement, 'i', $overrideId);
    mysqli_stmt_execute($statement);
    $affected = mysqli_stmt_affected_rows($statement);
    mysqli_stmt_close($statement);

    if ($affected <= 0) {
        throw new RuntimeException('That override could not be found.');
    }
}

function hh_admin_list_prediction_overrides(mysqli $con): array
{
    if (!hh_runtime_table_exists($con, hh_prediction_access_override_table_name())) {
        return [];
    }

    return hh_admin_fetch_all(
        $con,
        "SELECT override_row.id, override_row.stage_key, override_row.granted_until, override_row.reason, override_row.created_at,
                player.id AS user_id, player.username, player.firstname, player.surname
         FROM " . hh_prediction_access_override_table_name() . " override_row
         INNER JOIN live_user_information player ON player.id = override_row.user_id
         ORDER BY override_row.granted_until DESC, override_row.id DESC"
    );
}

function hh_admin_execute_sql(mysqli $con, string $sql): array
{
    $trimmed = trim($sql);
    if ($trimmed === '') {
        throw new RuntimeException('Enter a SQL statement to run.');
    }

    $trimmed = rtrim($trimmed, " \t\n\r\0\x0B;");
    if ($trimmed === '') {
        throw new RuntimeException('Enter a SQL statement to run.');
    }

    if (strpos($trimmed, ';') !== false) {
        throw new RuntimeException('Please run one SQL statement at a time.');
    }

    $keyword = strtoupper((string) strtok($trimmed, " \t\n\r("));
    $allowed = ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN', 'UPDATE', 'DELETE', 'INSERT', 'REPLACE', 'TRUNCATE'];
    if (!in_array($keyword, $allowed, true)) {
        throw new RuntimeException('That SQL command is not enabled in the admin runner.');
    }

    $result = mysqli_query($con, $trimmed);
    if ($result === false) {
        throw new RuntimeException(mysqli_error($con));
    }

    if ($result instanceof mysqli_result) {
        $columns = [];
        foreach (mysqli_fetch_fields($result) as $field) {
            $columns[] = $field->name;
        }

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);

        return [
            'type' => 'result-set',
            'message' => count($rows) . ' row(s) returned.',
            'columns' => $columns,
            'rows' => $rows,
            'sql' => $trimmed,
        ];
    }

    return [
        'type' => 'write',
        'message' => mysqli_affected_rows($con) . ' row(s) affected.',
        'columns' => [],
        'rows' => [],
        'sql' => $trimmed,
    ];
}

$messages = [];
$errors = [];

$knownTableLabels = [
    'live_user_information' => 'User information',
    'live_user_logins' => 'Login events',
    'live_registration_invites' => 'Registration invites',
    'live_prediction_access_overrides' => 'Prediction overrides',
    'live_match_schedule' => 'Match schedule',
    'live_match_results' => 'Match results',
    'live_user_predictions_groups' => 'Group predictions',
    'live_user_predictions_ro32' => 'Round of 32 predictions',
    'live_user_predictions_ro16' => 'Round of 16 predictions',
    'live_user_predictions_qf' => 'Quarter-final predictions',
    'live_user_predictions_sf' => 'Semi-final predictions',
    'live_user_predictions_final' => 'Final predictions',
    'live_user_badges' => 'Badge awards',
    'live_fanzone_posts' => 'Fan Zone posts',
];

$tableOptions = [];
foreach (hh_admin_fetch_table_names($con) as $tableName) {
    $tableOptions[$tableName] = $knownTableLabels[$tableName] ?? hh_admin_humanize_table_name($tableName);
}

$tableEditorConfig = hh_admin_table_editor_config();

$stageOptions = [
    'live_user_predictions_groups' => 'Group stage points',
    'live_user_predictions_ro32' => 'Round of 32 points',
    'live_user_predictions_ro16' => 'Round of 16 points',
    'live_user_predictions_qf' => 'Quarter-final points',
    'live_user_predictions_sf' => 'Semi-final points',
    'live_user_predictions_final' => 'Final points',
];

$sqlRunnerOutput = null;
$sqlRunnerStatement = '';
$generatedInviteLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['admin_action'] ?? '');

    if ($action === 'recalculate_all') {
        compareValues();
        compareRO32Values();
        compareRO16Values();
        compareQFValues();
        compareSFValues();
        compareFinalValues();
        $messages[] = 'All prediction points and ranking movement have been recalculated.';
    } elseif ($action === 'reset_game_data') {
        $queries = [
            "TRUNCATE TABLE live_match_results",
            "UPDATE live_match_schedule SET homescore = NULL, awayscore = NULL",
            "UPDATE live_user_predictions_groups SET points_total = 0",
            "UPDATE live_user_predictions_ro32 SET points_total = 0",
            "UPDATE live_user_predictions_ro16 SET points_total = 0",
            "UPDATE live_user_predictions_qf SET points_total = 0",
            "UPDATE live_user_predictions_sf SET points_total = 0",
            "UPDATE live_user_predictions_final SET points_total = 0",
            "UPDATE live_user_information SET lastpos = startpos, currpos = startpos",
        ];

        mysqli_begin_transaction($con);
        try {
            foreach ($queries as $query) {
                if (!mysqli_query($con, $query)) {
                    throw new RuntimeException(mysqli_error($con));
                }
            }
            hh_reset_initial_rankings_with_connection($con);
            mysqli_commit($con);
            $messages[] = 'Match results, schedule scores and player points have been reset. Starting rankings now follow signup order again.';
        } catch (Throwable $exception) {
            mysqli_rollback($con);
            $errors[] = 'Reset failed: ' . $exception->getMessage();
        }
    } elseif ($action === 'adjust_points') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $stageTable = (string) ($_POST['stage_table'] ?? '');
        $pointsDelta = (int) ($_POST['points_delta'] ?? 0);

        if ($username === '' || !isset($stageOptions[$stageTable])) {
            $errors[] = 'Please choose a player and a valid scoring stage.';
        } elseif ($pointsDelta === 0) {
            $errors[] = 'Please enter a points adjustment other than zero.';
        } else {
            $stmt = mysqli_prepare($con, "UPDATE {$stageTable} SET points_total = points_total + ? WHERE username = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'is', $pointsDelta, $username);
                mysqli_stmt_execute($stmt);
                $affected = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);

                if ($affected > 0) {
                    updateMoveStatus();
                    $messages[] = 'Points updated for ' . htmlspecialchars($username, ENT_QUOTES) . '.';
                } else {
                    $errors[] = 'No matching player row was found in that stage table.';
                }
            } else {
                $errors[] = 'Could not prepare the points adjustment.';
            }
        }
    } elseif ($action === 'set_payment_status') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $paymentStatus = (string) ($_POST['haspaid'] ?? '');

        if ($userId <= 0 || !in_array($paymentStatus, ['Yes', 'No'], true)) {
            $errors[] = 'Please choose a valid user and payment status.';
        } else {
            $stmt = mysqli_prepare($con, "UPDATE live_user_information SET haspaid = ? WHERE id = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $paymentStatus, $userId);
                mysqli_stmt_execute($stmt);
                $affected = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);

                if ($affected >= 0) {
                    $messages[] = 'Payment status updated.';
                } else {
                    $errors[] = 'Payment status could not be updated.';
                }
            } else {
                $errors[] = 'Could not prepare the payment status update.';
            }
        }
    } elseif ($action === 'clear_fanzone') {
        if (!hh_admin_table_exists($con, 'live_fanzone_posts')) {
            $errors[] = 'The Fan Zone table does not exist in this database.';
        } elseif (mysqli_query($con, "DELETE FROM live_fanzone_posts")) {
            $messages[] = 'The Fan Zone board has been cleared.';
        } else {
            $errors[] = 'The Fan Zone board could not be cleared.';
        }
    } elseif ($action === 'reset_preserving_users') {
        $preserveUserIds = array_map('intval', (array) ($_POST['preserve_user_ids'] ?? []));

        try {
            $summary = hh_admin_reset_preserving_users_with_connection($con, $preserveUserIds);
            $messages[] = 'All live game activity was cleared while preserving ' . $summary['count'] . ' user registration(s): ' . $summary['summary'] . '.';
        } catch (Throwable $exception) {
            $errors[] = 'Preserve-users reset failed: ' . $exception->getMessage();
        }
    } elseif ($action === 'run_sql') {
        $sqlRunnerStatement = (string) ($_POST['sql_statement'] ?? '');
        $sqlUnlock = (string) ($_POST['sql_unlock'] ?? '') === '1';
        $sqlConfirm = strtoupper(trim((string) ($_POST['sql_confirm'] ?? '')));

        if (!$sqlUnlock || $sqlConfirm !== 'RUN') {
            $errors[] = 'Unlock the SQL runner and type RUN before executing a statement.';
        } else {
            try {
                $sqlRunnerOutput = hh_admin_execute_sql($con, $sqlRunnerStatement);
                $messages[] = 'SQL statement executed successfully.';
            } catch (Throwable $exception) {
                $errors[] = 'SQL runner failed: ' . $exception->getMessage();
            }
        }
    } elseif ($action === 'delete_user') {
        $deleteUserId = (int) ($_POST['delete_user_id'] ?? 0);

        if ($deleteUserId <= 0) {
            $errors[] = 'Choose a user to delete.';
        } elseif ($deleteUserId === (int) ($_SESSION['id'] ?? 0)) {
            $errors[] = 'You cannot delete the admin account you are currently using.';
        } else {
            try {
                $deleted = hh_admin_delete_user_with_connection($con, $deleteUserId);
                $messages[] = 'Deleted user ' . $deleted['display_name'] . ' (@' . $deleted['username'] . ') and their linked game data.';
            } catch (Throwable $exception) {
                $errors[] = 'User deletion failed: ' . $exception->getMessage();
            }
        }
    } elseif ($action === 'restore_prediction_backup') {
        $restoreUserId = (int) ($_POST['restore_user_id'] ?? 0);
        $restoreStageKey = trim((string) ($_POST['restore_stage_key'] ?? ''));

        try {
            $restored = hh_admin_restore_prediction_backup_with_connection($con, $restoreUserId, $restoreStageKey);
            $messages[] = 'Restored the preserved ' . $restored['stage_label'] . ' snapshot for ' . $restored['display_name'] . ' (@' . $restored['username'] . ').';
        } catch (Throwable $exception) {
            $errors[] = 'Backup restore failed: ' . $exception->getMessage();
        }
    } elseif ($action === 'refresh_badges') {
        try {
            $badgeRefresh = hh_sync_badges_for_all_with_connection($con);
            $messages[] = 'Badge awards refreshed for ' . (int) ($badgeRefresh['users'] ?? 0) . ' player(s). Seeded ' . (int) ($badgeRefresh['seeded'] ?? 0) . ' existing badge(s) quietly and queued ' . (int) ($badgeRefresh['new'] ?? 0) . ' new badge notification(s).';
        } catch (Throwable $exception) {
            $errors[] = 'Badge refresh failed: ' . $exception->getMessage();
        }
    } elseif ($action === 'create_registration_invite') {
        $emailHint = trim((string) ($_POST['invite_email_hint'] ?? ''));
        $notes = trim((string) ($_POST['invite_notes'] ?? ''));
        $expiresAtUtc = hh_admin_local_datetime_to_utc_string((string) ($_POST['invite_expires_at'] ?? ''));

        if ((string) ($_POST['invite_expires_at'] ?? '') !== '' && $expiresAtUtc === null) {
            $errors[] = 'Please choose a valid invite expiry time.';
        } else {
            try {
                $invite = hh_admin_issue_registration_invite_with_connection($con, (string) ($base_url ?? ''), (int) ($_SESSION['id'] ?? 0), $emailHint, $notes, $expiresAtUtc);
                $generatedInviteLink = (string) ($invite['url'] ?? '');
                $messages[] = 'A one-time registration invite link has been created.';
            } catch (Throwable $exception) {
                $errors[] = 'Invite creation failed: ' . $exception->getMessage();
            }
        }
    } elseif ($action === 'revoke_registration_invite') {
        $inviteId = (int) ($_POST['invite_id'] ?? 0);

        try {
            hh_admin_revoke_registration_invite_with_connection($con, $inviteId);
            $messages[] = 'The registration invite has been revoked.';
        } catch (Throwable $exception) {
            $errors[] = 'Invite revoke failed: ' . $exception->getMessage();
        }
    } elseif ($action === 'save_prediction_override') {
        $overrideUserId = (int) ($_POST['override_user_id'] ?? 0);
        $overrideStageKey = trim((string) ($_POST['override_stage_key'] ?? ''));
        $overrideReason = trim((string) ($_POST['override_reason'] ?? ''));
        $grantedUntilRaw = (string) ($_POST['override_granted_until'] ?? '');
        $grantedUntilUtc = hh_admin_local_datetime_to_utc_string($grantedUntilRaw);

        if ($grantedUntilUtc === null) {
            $errors[] = 'Choose a valid override end time.';
        } else {
            try {
                $override = hh_admin_save_prediction_override_with_connection($con, $overrideUserId, $overrideStageKey, $grantedUntilUtc, (int) ($_SESSION['id'] ?? 0), $overrideReason);
                $messages[] = 'Prediction access override saved for ' . $override['display_name'] . ' (@' . $override['username'] . ') on ' . $override['stage_label'] . '.';
            } catch (Throwable $exception) {
                $errors[] = 'Prediction override failed: ' . $exception->getMessage();
            }
        }
    } elseif ($action === 'populate_player_predictions') {
        $populateUserId = (int) ($_POST['populate_user_id'] ?? 0);
        $populateStageKey = trim((string) ($_POST['populate_stage_key'] ?? ''));

        try {
            $populated = hh_admin_populate_prediction_stage_for_user_with_connection($con, $populateUserId, $populateStageKey);
            $messages[] = 'Populated ' . $populated['stage_label'] . ' predictions for ' . $populated['display_name'] . ' (@' . $populated['username'] . '). Recorded fixtures in that stage were left blank, so they will stay on zero points.';
        } catch (Throwable $exception) {
            $errors[] = 'Prediction populate failed: ' . $exception->getMessage();
        }
    } elseif ($action === 'remove_prediction_override') {
        $overrideId = (int) ($_POST['override_id'] ?? 0);

        try {
            hh_admin_remove_prediction_override_with_connection($con, $overrideId);
            $messages[] = 'The prediction access override has been removed.';
        } catch (Throwable $exception) {
            $errors[] = 'Override removal failed: ' . $exception->getMessage();
        }
    } elseif ($action === 'save_table_row') {
        $tableName = (string) ($_POST['table_name'] ?? '');
        $rowId = (int) ($_POST['row_id'] ?? 0);
        $isUnlocked = (string) ($_POST['editor_unlock'] ?? '') === '1';
        $config = $tableEditorConfig[$tableName] ?? null;

        if (!$config) {
            $errors[] = 'That table is not enabled for direct editing.';
        } elseif (!$isUnlocked) {
            $errors[] = 'Please unlock the editor before saving changes.';
        } elseif ($rowId <= 0) {
            $errors[] = 'Please choose a valid row to update.';
        } else {
            $assignments = [];
            $types = '';
            $values = [];

            foreach (($config['columns'] ?? []) as $columnName => $meta) {
                $rawValue = (string) ($_POST['editor'][$columnName] ?? '');
                $normalized = hh_admin_editor_normalize_value($rawValue, $meta);

                if ($normalized === null) {
                    $assignments[] = "{$columnName} = NULL";
                    continue;
                }

                $assignments[] = "{$columnName} = ?";
                $types .= (string) ($meta['type'] ?? '') === 'number' ? 'i' : 's';
                $values[] = $normalized;
            }

            $primaryKey = (string) ($config['primary_key'] ?? 'id');
            $sql = "UPDATE {$tableName} SET " . implode(', ', $assignments) . " WHERE {$primaryKey} = ? LIMIT 1";
            $types .= 'i';
            $values[] = $rowId;

            $statement = mysqli_prepare($con, $sql);
            if (!$statement) {
                $errors[] = 'The editor could not prepare that update.';
            } else {
                hh_admin_bind_dynamic($statement, $types, $values);
                mysqli_stmt_execute($statement);
                mysqli_stmt_close($statement);
                $messages[] = 'The selected row has been updated.';
            }
        }
    }
}

$selectedTable = (string) (($_REQUEST['table'] ?? $_POST['table_name'] ?? 'live_user_information'));
if (!isset($tableOptions[$selectedTable])) {
    $selectedTable = isset($tableOptions['live_user_information'])
        ? 'live_user_information'
        : (array_key_first($tableOptions) ?? 'live_user_information');
}

$users = hh_admin_fetch_all(
    $con,
    "SELECT id, username, firstname, surname, haspaid FROM live_user_information ORDER BY surname ASC, firstname ASC"
);

$restoreStageOptions = [];
foreach (hh_prediction_stage_contexts() as $stageKey => $context) {
    $restoreStageOptions[$stageKey] = (string) ($context['label'] ?? $stageKey);
}
$registrationInvites = hh_admin_list_registration_invites($con);
$predictionOverrides = hh_admin_list_prediction_overrides($con);

$snapshot = [
    'users' => 0,
    'fixtures' => 0,
    'results' => 0,
    'result_snapshots' => 0,
    'fanzone' => 0,
    'badges' => 0,
    'logins' => 0,
    'quiz_players' => 0,
    'quiz_sessions' => 0,
    'spot_players' => 0,
    'spot_sessions' => 0,
];

$countQueries = [
    'users' => "SELECT COUNT(*) AS total FROM live_user_information",
    'fixtures' => "SELECT COUNT(*) AS total FROM live_match_schedule",
    'results' => "SELECT COUNT(*) AS total FROM live_match_schedule WHERE homescore IS NOT NULL AND awayscore IS NOT NULL",
    'result_snapshots' => "SELECT COUNT(*) AS total FROM live_match_results",
];

foreach ($countQueries as $key => $query) {
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $snapshot[$key] = (int) ($row['total'] ?? 0);
        mysqli_free_result($result);
    }
}

if (hh_admin_table_exists($con, 'live_fanzone_posts')) {
    $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM live_fanzone_posts WHERE is_deleted = 0");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $snapshot['fanzone'] = (int) ($row['total'] ?? 0);
        mysqli_free_result($result);
    }
}

if (hh_badge_table_exists($con)) {
    $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM " . hh_badge_table_name());
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $snapshot['badges'] = (int) ($row['total'] ?? 0);
        mysqli_free_result($result);
    }
}

if (hh_admin_table_exists($con, 'live_user_logins')) {
    $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM live_user_logins");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $snapshot['logins'] = (int) ($row['total'] ?? 0);
        mysqli_free_result($result);
    }
}

if (hh_admin_table_exists($con, 'live_fanzone_quiz_activity')) {
    $result = mysqli_query(
        $con,
        "SELECT
            COUNT(DISTINCT user_id) AS player_total,
            COUNT(*) AS event_total,
            SUM(CASE WHEN event_type = 'session_start' THEN 1 ELSE 0 END) AS session_total
         FROM live_fanzone_quiz_activity"
    );
    if ($result) {
        $row = mysqli_fetch_assoc($result) ?: [];
        $snapshot['quiz_players'] = (int) ($row['player_total'] ?? 0);
        $snapshot['quiz_sessions'] = (int) ($row['session_total'] ?? 0);
        mysqli_free_result($result);
    }
}

if (hh_admin_table_exists($con, 'live_fanzone_spot_activity')) {
    $result = mysqli_query(
        $con,
        "SELECT
            COUNT(DISTINCT user_id) AS player_total,
            COUNT(*) AS event_total,
            SUM(CASE WHEN event_type = 'session_start' THEN 1 ELSE 0 END) AS session_total
         FROM live_fanzone_spot_activity"
    );
    if ($result) {
        $row = mysqli_fetch_assoc($result) ?: [];
        $snapshot['spot_players'] = (int) ($row['player_total'] ?? 0);
        $snapshot['spot_sessions'] = (int) ($row['session_total'] ?? 0);
        mysqli_free_result($result);
    }
}

$fixturePreview = hh_admin_fetch_all(
    $con,
    "SELECT id, hometeam, awayteam, homescore, awayscore, date, kotime, stage FROM live_match_schedule ORDER BY date ASC, kotime ASC LIMIT 8"
);

$auditFixtures = hh_admin_fetch_all(
    $con,
    "SELECT match_number, stage, date, kotime, hometeam, awayteam, homescore, awayscore
     FROM live_match_schedule
     ORDER BY match_number ASC"
);

$selectedAuditMatch = 0;
foreach ($auditFixtures as $fixture) {
    if (($fixture['homescore'] ?? null) !== null && ($fixture['awayscore'] ?? null) !== null) {
        $selectedAuditMatch = (int) ($fixture['match_number'] ?? 0);
    }
}
if ($selectedAuditMatch <= 0 && !empty($auditFixtures)) {
    $selectedAuditMatch = (int) ($auditFixtures[0]['match_number'] ?? 0);
}

$requestedAuditMatch = (int) ($_GET['audit_match'] ?? 0);
if ($requestedAuditMatch > 0) {
    $selectedAuditMatch = $requestedAuditMatch;
}

$predictionIntegrityAudit = hh_admin_prediction_integrity_audit($con, $users);
$scoringAudit = hh_admin_scoring_audit($con, $selectedAuditMatch);
$badgeSummary = hh_badge_admin_summary($con);
$selectedBadgeUserId = (int) ($_GET['badge_user_id'] ?? 0);
$selectedBadgeToken = trim((string) ($_GET['badge_token'] ?? ''));
$selectedBadgeTokens = [];
$selectedBadgeUserLabel = '';
$selectedBadgeTitle = '';
$selectedBadgeHolders = [];

foreach ($badgeSummary as $badge) {
    if ((string) ($badge['token'] ?? '') === $selectedBadgeToken) {
        $selectedBadgeTitle = (string) ($badge['title'] ?? $selectedBadgeToken);
        break;
    }
}

if ($selectedBadgeUserId > 0) {
    foreach ($users as $user) {
        $userId = (int) ($user['id'] ?? 0);
        if ($userId !== $selectedBadgeUserId) {
            continue;
        }

        $displayName = trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? ''));
        if ($displayName === '') {
            $displayName = (string) ($user['username'] ?? 'Player ' . $userId);
        }

        $selectedBadgeUserLabel = $displayName . ' (@' . (string) ($user['username'] ?? '') . ')';
        break;
    }

    if ($selectedBadgeUserLabel !== '' && hh_badge_table_exists($con)) {
        $selectedBadgeTokens = hh_badge_fetch_awarded_tokens_with_connection($con, $selectedBadgeUserId);
    } else {
        $selectedBadgeUserId = 0;
    }
}

$selectedRowMode = trim((string) ($_GET['table_rows'] ?? '30'));
$selectedTablePage = max(1, (int) ($_GET['table_page'] ?? 1));
$tablePreview = hh_admin_preview_table_paged($con, $selectedTable, $selectedRowMode, $selectedTablePage);
$selectedRowMode = (string) ($tablePreview['row_mode'] ?? '30');
$selectedTablePage = (int) ($tablePreview['page'] ?? 1);

if ($selectedBadgeTitle !== '') {
    $selectedBadgeHolders = hh_admin_badge_holders($con, $selectedBadgeToken);
}

$editableTable = $tableEditorConfig[$selectedTable] ?? null;
$editorRowOptions = $editableTable ? hh_admin_editor_row_options($con, $editableTable) : [];
$selectedEditRowId = (int) ($_REQUEST['edit_row'] ?? $_POST['row_id'] ?? 0);
if ($selectedEditRowId <= 0 && !empty($editorRowOptions)) {
    $selectedEditRowId = (int) ($editorRowOptions[0]['row_id'] ?? 0);
}
$editableRow = ($editableTable && $selectedEditRowId > 0)
    ? hh_admin_editor_fetch_row($con, $selectedTable, $editableTable, $selectedEditRowId)
    : null;

mysqli_close($con);

$app_path_prefix = '../';
$app_logout_path = '../php/logout.php';
include '../php/header.php';
include '../php/navigation.php';
?>
<style>
      .admin-shell {
        width: min(1320px, calc(100% - 32px));
        margin: 18px auto 28px;
      }
      .admin-grid {
        display: grid;
        gap: 18px;
      }
      .admin-shell > *,
      .admin-grid > *,
      .admin-grid--two > *,
      .admin-grid--three > *,
      .admin-kpi > *,
      .admin-audit-metrics > *,
      .admin-score-summary > *,
      .admin-editor-grid > *,
      .page-hero > * {
        min-width: 0;
      }
      .admin-grid--two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
      .admin-grid--three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
      .admin-card {
        padding: 22px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(251, 252, 248, 0.96);
        box-shadow: 0 18px 38px rgba(0, 0, 0, 0.14);
      }
      .admin-card h2,
      .admin-card h3 {
        margin: 0 0 12px;
        font-weight: 900;
      }
      .admin-checkbox-list {
        display: grid;
        gap: 10px;
        max-height: 220px;
        overflow: auto;
        padding: 12px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.72);
      }
      .admin-checkbox-list label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
      }
      .admin-checkbox-list small {
        color: var(--hh-muted);
        font-weight: 600;
      }
      .admin-sql-result {
        margin-top: 16px;
        padding: 14px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.78);
      }
      .admin-sql-result code {
        display: block;
        white-space: pre-wrap;
        word-break: break-word;
      }
      .admin-kpi {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }
      .admin-kpi__item {
        padding: 16px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(143, 102, 216, 0.08);
      }
      .admin-kpi__item strong,
      .admin-kpi__item span {
        display: block;
      }
      .admin-kpi__item strong {
        font-size: 1.6rem;
        line-height: 1;
      }
      .admin-kpi__item span {
        margin-top: 6px;
        color: var(--hh-muted);
        font-weight: 700;
      }
      .admin-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
      }
      .admin-card form {
        display: grid;
        gap: 14px;
      }
      .admin-danger {
        border-color: rgba(214, 64, 69, 0.18);        
        /* background: rgba(214, 64, 69, 0.04); */
      }
      .admin-note {
        margin: 0;
        color: var(--hh-muted);
        font-size: 0.92rem;
      }
      .admin-table-preview {
        overflow-x: auto;
      }
      .admin-table-preview table {
        margin-bottom: 0;
        white-space: nowrap;
      }
      .admin-editor-toggle {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
      }
      .admin-editor-toggle input[type="checkbox"] {
        width: 46px;
        height: 26px;
        appearance: none;
        border-radius: 999px;
        background: #d6d8d1;
        position: relative;
        border: 1px solid var(--hh-line);
        transition: background 0.2s ease;
      }
      .admin-editor-toggle input[type="checkbox"]::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
        transition: transform 0.2s ease;
      }
      .admin-editor-toggle input[type="checkbox"]:checked {
        background: rgba(143, 102, 216, 0.6);
      }
      .admin-editor-toggle input[type="checkbox"]:checked::after {
        transform: translateX(20px);
      }
      .admin-editor-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
      .admin-editor-grid .is-changed {
        border-color: rgba(143, 102, 216, 0.5);
        background: rgba(143, 102, 216, 0.08);
      }
      .admin-editor-grid textarea.form-control {
        min-height: 110px;
      }
      .admin-audit-grid {
        display: grid;
        gap: 16px;
      }
      .admin-audit-card {
        padding: 16px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: #ffffff;
      }
      .admin-audit-card.is-ready {
        border-color: rgba(25, 135, 84, 0.22);
      }
      .admin-audit-card.is-review {
        border-color: rgba(255, 193, 7, 0.28);
      }
      .admin-audit-card.is-missing-table {
        border-color: rgba(214, 64, 69, 0.22);
      }
      .admin-audit-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
      }
      .admin-audit-card__top h4 {
        margin: 0 0 4px;
        font-size: 1.02rem;
        font-weight: 900;
      }
      .admin-audit-card__top p {
        margin: 0;
        color: var(--hh-muted);
        font-size: 0.82rem;
      }
      .admin-audit-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
      }
      .admin-audit-status--ready {
        background: rgba(25, 135, 84, 0.12);
        color: #146c43;
      }
      .admin-audit-status--review {
        background: rgba(255, 193, 7, 0.16);
        color: #8a6d03;
      }
      .admin-audit-status--missing-table {
        background: rgba(214, 64, 69, 0.12);
        color: #a52f33;
      }
      .admin-audit-metrics {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 12px;
      }
      .admin-audit-metrics div {
        padding: 12px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(12, 90, 67, 0.04);
      }
      .admin-audit-metrics strong,
      .admin-audit-metrics span {
        display: block;
      }
      .admin-audit-metrics strong {
        font-size: 1.15rem;
        line-height: 1;
      }
      .admin-audit-metrics span {
        margin-top: 6px;
        color: var(--hh-muted);
        font-size: 0.78rem;
        font-weight: 700;
      }
      .admin-audit-list {
        margin: 0;
        padding-left: 18px;
        color: var(--hh-muted);
      }
      .admin-audit-list li + li {
        margin-top: 4px;
      }
      .admin-score-summary {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(7, minmax(0, 1fr));
      }
      .admin-score-summary div {
        padding: 12px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: #ffffff;
      }
      .admin-score-summary strong,
      .admin-score-summary span {
        display: block;
      }
      .admin-score-summary strong {
        font-size: 1.2rem;
        line-height: 1;
      }
      .admin-score-summary span {
        margin-top: 6px;
        color: var(--hh-muted);
        font-size: 0.78rem;
        font-weight: 700;
      }
      .admin-score-result {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(12, 90, 67, 0.08);
        color: var(--hh-green-dark);
        font-size: 0.86rem;
        font-weight: 800;
      }
      .admin-score-table td,
      .admin-score-table th {
        vertical-align: middle;
      }
      .admin-score-player strong,
      .admin-score-player span {
        display: block;
      }
      .admin-score-player span {
        color: var(--hh-muted);
        font-size: 0.8rem;
      }
      .admin-score-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 900;
      }
      .admin-score-pill--perfect {
        background: rgba(25, 135, 84, 0.14);
        color: #146c43;
      }
      .admin-score-pill--strong {
        background: rgba(143, 102, 216, 0.14);
        color: #5a2db1;
      }
      .admin-score-pill--outcome {
        background: rgba(13, 110, 253, 0.12);
        color: #0a58ca;
      }
      .admin-score-pill--single {
        background: rgba(255, 193, 7, 0.16);
        color: #8a6d03;
      }
      .admin-score-pill--miss,
      .admin-score-pill--missing {
        background: rgba(214, 64, 69, 0.12);
        color: #a52f33;
      }
      .admin-score-pill--pending {
        background: rgba(108, 117, 125, 0.12);
        color: #5c636a;
      }
      .admin-score-meta {
        color: var(--hh-muted);
        font-size: 0.84rem;
      }
      .admin-score-meta strong {
        color: var(--hh-green-dark);
      }
      .admin-badge-list {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
      }
      .admin-badge-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 14px;
      }
      .admin-badge-filter {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 10px;
      }
      .admin-badge-tile {
        display: grid;
        gap: 10px;
        padding: 16px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.84);
      }
      .admin-badge-tile.is-earned {
        border-color: rgba(25, 135, 84, 0.26);
        background: rgba(25, 135, 84, 0.06);
      }
      .admin-badge-tile__top {
        display: grid;
        gap: 10px;
        justify-items: center;
        text-align: center;
      }
      .admin-badge-tile__img {
        width: 68px;
        height: 68px;
        border-radius: 8px;
        object-fit: cover;
      }
      .admin-badge-tile__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
      }
      .admin-badge-tile__desc {
        margin: 0;
        color: var(--hh-muted);
        font-size: 0.86rem;
        line-height: 1.35;
      }
      .admin-badge-tile__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
      }
      .admin-badge-tile__footer.is-summary {
        justify-content: center;
      }
      .admin-badge-tile__count {
        font-size: 0.95rem;
        font-weight: 900;
        text-align: center;
      }
      .admin-badge-tile__status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.88rem;
        font-weight: 800;
        color: var(--hh-muted);
      }
      .admin-badge-tile__status.is-earned {
        color: #198754;
      }
      .admin-badge-tile__status .bi {
        font-size: 1rem;
      }
      .admin-inline-output {
        margin-top: 12px;
      }
      .admin-inline-output .form-control {
        font-weight: 700;
      }
      .admin-stack-list {
        display: grid;
        gap: 10px;
        margin-top: 16px;
      }
      .admin-stack-item {
        display: grid;
        gap: 10px;
        padding: 14px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.84);
      }
      .admin-stack-item__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
      }
      .admin-stack-item__top strong,
      .admin-stack-item__top span {
        display: block;
      }
      .admin-stack-item__top span {
        color: var(--hh-muted);
        font-size: 0.82rem;
      }
      .admin-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
      }
      .admin-status-pill--active {
        background: rgba(25, 135, 84, 0.12);
        color: #146c43;
      }
      .admin-status-pill--used {
        background: rgba(13, 110, 253, 0.12);
        color: #0a58ca;
      }
      .admin-status-pill--expired {
        background: rgba(214, 64, 69, 0.12);
        color: #a52f33;
      }
      .admin-status-pill--pending {
        background: rgba(255, 193, 7, 0.18);
        color: #8a6d03;
      }
      .admin-stack-item__meta {
        display: grid;
        gap: 4px;
        color: var(--hh-muted);
        font-size: 0.84rem;
      }
      .admin-stack-item__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
      }
      @media (max-width: 991px) {
        .admin-grid--two,
        .admin-grid--three,
        .admin-kpi,
        .admin-audit-metrics,
        .admin-score-summary,
        .admin-editor-grid {
          grid-template-columns: 1fr;
        }
      }
      @media (max-width: 575.98px) {
        .admin-shell {
          width: min(100%, calc(100% - 16px));
          margin: 12px auto 22px;
        }
        .admin-card {
          padding: 16px;
        }
        .admin-actions .btn,
        .page-hero__actions .btn {
          width: 100%;
          justify-content: center;
        }
      }
    </style>

<div class="admin-shell">
    <div class="page-hero page-hero--admin">
        <div>
            <p class="eyebrow" style="color: #FF0000 !important">Admin control room</p>
            <h1>Game Functions</h1>
            <p class="lead mb-0">A central place to run the core admin actions that steer the game and keep the data in shape.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="communications.php"><i class="bi bi-envelope-paper"></i> Communications</a>
            <a class="btn btn-primary" href="results.php"><i class="bi bi-trophy"></i> Record results</a>
            <a class="btn btn-outline-dark" href="../dashboard.php"><i class="bi bi-grid"></i> Back to dashboard</a>
        </div>
    </div>

    <?php foreach ($messages as $message) : ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($message, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $error) : ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <?php if ($generatedInviteLink !== '') : ?>
        <div class="alert alert-info" role="alert">
            <strong>New invite link ready.</strong>
            <div class="admin-inline-output">
                <input class="form-control" type="text" value="<?= htmlspecialchars($generatedInviteLink, ENT_QUOTES) ?>" readonly onclick="this.select()">
            </div>
        </div>
    <?php endif; ?>

    <section class="admin-grid">
        <div class="admin-card">
            <h2>System Snapshot</h2>
            <div class="admin-kpi">
                <div class="admin-kpi__item"><strong><?= $snapshot['users'] ?></strong><span>registered players</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['fixtures'] ?></strong><span>scheduled fixtures</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['results'] ?></strong><span>fixtures with results</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['result_snapshots'] ?></strong><span>result snapshots saved</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['fanzone'] ?></strong><span>live Fan Zone posts</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['badges'] ?></strong><span>badge awards stored</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['logins'] ?></strong><span>successful logins recorded</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['quiz_players'] ?></strong><span>quiz players</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['quiz_sessions'] ?></strong><span>quiz sessions started</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['spot_players'] ?></strong><span>spot-the-ball players</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['spot_sessions'] ?></strong><span>spot-the-ball sessions started</span></div>
            </div>
        </div>

        <div class="admin-grid admin-grid--two">
            <div class="admin-card">
                <h2>Registration Invites</h2>
                <p class="admin-note">Generate a one-time signup link for a late joiner without reopening registration globally. The link stays private, can expire, and is consumed after a successful registration.</p>
                <form method="post" class="mt-3">
                    <input type="hidden" name="admin_action" value="create_registration_invite">
                    <div>
                        <label class="form-label" for="invite_email_hint">Email hint</label>
                        <input class="form-control" type="text" id="invite_email_hint" name="invite_email_hint" placeholder="Optional: player email or name">
                    </div>
                    <div>
                        <label class="form-label" for="invite_notes">Notes</label>
                        <input class="form-control" type="text" id="invite_notes" name="invite_notes" placeholder="Optional: why this invite was issued">
                    </div>
                    <div>
                        <label class="form-label" for="invite_expires_at">Expires at</label>
                        <input class="form-control" type="datetime-local" id="invite_expires_at" name="invite_expires_at">
                    </div>
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-link-45deg"></i> Create one-time invite</button>
                </form>

                <div class="admin-stack-list">
                    <?php if ($registrationInvites === []) : ?>
                        <p class="admin-note mb-0">No registration invites have been created yet.</p>
                    <?php else : ?>
                        <?php foreach ($registrationInvites as $invite) : ?>
                            <?php
                            $inviteUsedAt = trim((string) ($invite['used_at'] ?? ''));
                            $inviteExpiresAt = trim((string) ($invite['expires_at'] ?? ''));
                            $inviteStatus = 'pending';
                            $inviteStatusLabel = 'Unused';
                            if ($inviteUsedAt !== '') {
                                $inviteStatus = 'used';
                                $inviteStatusLabel = 'Used';
                            } elseif ($inviteExpiresAt !== '') {
                                $expiryUtc = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $inviteExpiresAt, new DateTimeZone('UTC'));
                                if ($expiryUtc instanceof DateTimeImmutable && $expiryUtc < hh_effective_now(new DateTimeZone('UTC'))) {
                                    $inviteStatus = 'expired';
                                    $inviteStatusLabel = 'Expired';
                                } else {
                                    $inviteStatus = 'active';
                                    $inviteStatusLabel = 'Active';
                                }
                            } else {
                                $inviteStatus = 'active';
                                $inviteStatusLabel = 'Active';
                            }
                            $usedByName = trim((string) ($invite['used_firstname'] ?? '') . ' ' . (string) ($invite['used_surname'] ?? ''));
                            if ($usedByName === '' && trim((string) ($invite['used_username'] ?? '')) !== '') {
                                $usedByName = '@' . trim((string) ($invite['used_username'] ?? ''));
                            }
                            ?>
                            <article class="admin-stack-item">
                                <div class="admin-stack-item__top">
                                    <div>
                                        <strong><?= htmlspecialchars(trim((string) ($invite['email_hint'] ?? '')) !== '' ? (string) $invite['email_hint'] : 'Unnamed invite', ENT_QUOTES) ?></strong>
                                        <span><?= htmlspecialchars(trim((string) ($invite['notes'] ?? '')) !== '' ? (string) $invite['notes'] : 'No notes added.', ENT_QUOTES) ?></span>
                                    </div>
                                    <span class="admin-status-pill admin-status-pill--<?= htmlspecialchars($inviteStatus, ENT_QUOTES) ?>"><?= htmlspecialchars($inviteStatusLabel, ENT_QUOTES) ?></span>
                                </div>
                                <div class="admin-stack-item__meta">
                                    <span>Created <?= htmlspecialchars(hh_admin_format_utc_for_local_display((string) ($invite['created_at'] ?? ''), 'Unknown'), ENT_QUOTES) ?></span>
                                    <span>Expires <?= htmlspecialchars(hh_admin_format_utc_for_local_display($inviteExpiresAt), ENT_QUOTES) ?></span>
                                    <?php if ($inviteUsedAt !== '') : ?>
                                        <span>Used <?= htmlspecialchars(hh_admin_format_utc_for_local_display($inviteUsedAt, 'Used'), ENT_QUOTES) ?><?= $usedByName !== '' ? ' by ' . htmlspecialchars($usedByName, ENT_QUOTES) : '' ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="admin-stack-item__actions">
                                    <span class="admin-note">Invite #<?= (int) ($invite['id'] ?? 0) ?></span>
                                    <form method="post" onsubmit="return confirm('Remove this registration invite?');">
                                        <input type="hidden" name="admin_action" value="revoke_registration_invite">
                                        <input type="hidden" name="invite_id" value="<?= (int) ($invite['id'] ?? 0) ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Remove</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-card">
                <h2>Prediction Access Overrides</h2>
                <p class="admin-note">Grant a specific player extra time on one prediction stage without reopening that stage for everyone else. The override ends automatically at the time you set here.</p>
                <form method="post" class="mt-3">
                    <input type="hidden" name="admin_action" value="save_prediction_override">
                    <div>
                        <label class="form-label" for="override_user_id">Player</label>
                        <select class="form-select" id="override_user_id" name="override_user_id" required>
                            <option value="">Choose a player</option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?= (int) ($user['id'] ?? 0) ?>">
                                    <?= htmlspecialchars(trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? '')) . ' (@' . (string) ($user['username'] ?? '') . ')', ENT_QUOTES) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="override_stage_key">Stage</label>
                        <select class="form-select" id="override_stage_key" name="override_stage_key" required>
                            <option value="">Choose a stage</option>
                            <?php foreach ($restoreStageOptions as $stageKey => $stageLabel) : ?>
                                <option value="<?= htmlspecialchars($stageKey, ENT_QUOTES) ?>"><?= htmlspecialchars($stageLabel, ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="override_granted_until">Override ends at</label>
                        <input class="form-control" type="datetime-local" id="override_granted_until" name="override_granted_until" required>
                    </div>
                    <div>
                        <label class="form-label" for="override_reason">Reason</label>
                        <input class="form-control" type="text" id="override_reason" name="override_reason" placeholder="Optional: late payer, known issue, manual exception">
                    </div>
                    <button type="submit" class="btn btn-outline-success"><i class="bi bi-unlock"></i> Save override</button>
                </form>

                <div class="admin-stack-list">
                    <?php if ($predictionOverrides === []) : ?>
                        <p class="admin-note mb-0">No prediction access overrides are currently stored.</p>
                    <?php else : ?>
                        <?php foreach ($predictionOverrides as $override) : ?>
                            <?php
                            $overrideName = trim((string) ($override['firstname'] ?? '') . ' ' . (string) ($override['surname'] ?? ''));
                            if ($overrideName === '') {
                                $overrideName = '@' . trim((string) ($override['username'] ?? 'player'));
                            }
                            $overrideIsActive = false;
                            $overrideUtc = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) ($override['granted_until'] ?? ''), new DateTimeZone('UTC'));
                            if ($overrideUtc instanceof DateTimeImmutable) {
                                $overrideIsActive = $overrideUtc >= hh_effective_now(new DateTimeZone('UTC'));
                            }
                            ?>
                            <article class="admin-stack-item">
                                <div class="admin-stack-item__top">
                                    <div>
                                        <strong><?= htmlspecialchars($overrideName, ENT_QUOTES) ?></strong>
                                        <span><?= htmlspecialchars((string) ($restoreStageOptions[(string) ($override['stage_key'] ?? '')] ?? (string) ($override['stage_key'] ?? 'Stage')), ENT_QUOTES) ?></span>
                                    </div>
                                    <span class="admin-status-pill admin-status-pill--<?= $overrideIsActive ? 'active' : 'expired' ?>"><?= $overrideIsActive ? 'Active' : 'Expired' ?></span>
                                </div>
                                <div class="admin-stack-item__meta">
                                    <span>Open until <?= htmlspecialchars(hh_admin_format_utc_for_local_display((string) ($override['granted_until'] ?? '')), ENT_QUOTES) ?></span>
                                    <span><?= htmlspecialchars(trim((string) ($override['reason'] ?? '')) !== '' ? (string) $override['reason'] : 'No reason added.', ENT_QUOTES) ?></span>
                                </div>
                                <div class="admin-stack-item__actions">
                                    <span class="admin-note">Override #<?= (int) ($override['id'] ?? 0) ?></span>
                                    <form method="post" onsubmit="return confirm('Remove this prediction access override?');">
                                        <input type="hidden" name="admin_action" value="remove_prediction_override">
                                        <input type="hidden" name="override_id" value="<?= (int) ($override['id'] ?? 0) ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-lock"></i> Remove</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-card">
                <h2>Populate Predictions For Player</h2>
                <p class="admin-note">Use the same weighted “Populate for me” idea for a chosen player without logging in as them. Any fixtures in that stage that already have recorded results are left blank automatically, so they stay on zero points rather than gaining anything retrospectively.</p>
                <form method="post" class="mt-3" onsubmit="return confirm('Populate this player\\'s stage predictions now? Already recorded fixtures in that stage will be left blank.');">
                    <input type="hidden" name="admin_action" value="populate_player_predictions">
                    <div>
                        <label class="form-label" for="populate_user_id">Player</label>
                        <select class="form-select" id="populate_user_id" name="populate_user_id" required>
                            <option value="">Choose a player</option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?= (int) ($user['id'] ?? 0) ?>">
                                    <?= htmlspecialchars(trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? '')) . ' (@' . (string) ($user['username'] ?? '') . ')', ENT_QUOTES) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="populate_stage_key">Stage</label>
                        <select class="form-select" id="populate_stage_key" name="populate_stage_key" required>
                            <option value="">Choose a stage</option>
                            <?php foreach ($restoreStageOptions as $stageKey => $stageLabel) : ?>
                                <option value="<?= htmlspecialchars($stageKey, ENT_QUOTES) ?>"><?= htmlspecialchars($stageLabel, ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-magic"></i> Populate stage for player</button>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-badge-toolbar">
                <div>
                    <h2>Badge Awards</h2>
                    <p class="admin-note mb-0">
                        <?php if ($selectedBadgeUserId > 0 && $selectedBadgeUserLabel !== '') : ?>
                            Viewing badge progress for <?= htmlspecialchars($selectedBadgeUserLabel, ENT_QUOTES) ?>. Green ticks mark the badges they currently hold.
                        <?php else : ?>
                            Refresh badge awards from the current game data, then review how many players have earned each badge across the whole player base.
                        <?php endif; ?>
                    </p>
                </div>
                <div class="d-flex flex-wrap align-items-end gap-2">
                    <form method="get" class="admin-badge-filter">
                        <div>
                            <label class="form-label" for="badge_user_id">Player view</label>
                            <select class="form-select" id="badge_user_id" name="badge_user_id">
                                <option value="">All players</option>
                                <?php foreach ($users as $user) : ?>
                                    <?php
                                    $userId = (int) ($user['id'] ?? 0);
                                    $displayName = trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? ''));
                                    if ($displayName === '') {
                                        $displayName = (string) ($user['username'] ?? 'Player ' . $userId);
                                    }
                                    ?>
                                    <option value="<?= $userId ?>"<?= $selectedBadgeUserId === $userId ? ' selected' : '' ?>>
                                        <?= htmlspecialchars($displayName . ' (@' . (string) ($user['username'] ?? '') . ')', ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="badge_token">Badge view</label>
                            <select class="form-select" id="badge_token" name="badge_token">
                                <option value="">All badges</option>
                                <?php foreach ($badgeSummary as $badge) : ?>
                                    <?php $badgeToken = (string) ($badge['token'] ?? ''); ?>
                                    <option value="<?= htmlspecialchars($badgeToken, ENT_QUOTES) ?>"<?= $selectedBadgeToken === $badgeToken ? ' selected' : '' ?>>
                                        <?= htmlspecialchars((string) ($badge['title'] ?? $badgeToken), ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="table" value="<?= htmlspecialchars($selectedTable, ENT_QUOTES) ?>">
                        <input type="hidden" name="table_rows" value="<?= htmlspecialchars($selectedRowMode, ENT_QUOTES) ?>">
                        <input type="hidden" name="table_page" value="<?= (int) $selectedTablePage ?>">
                        <input type="hidden" name="audit_match" value="<?= (int) $selectedAuditMatch ?>">
                        <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-person-check"></i> View badges</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="admin_action" value="refresh_badges">
                        <button type="submit" class="btn btn-outline-dark"><i class="bi bi-award"></i> Refresh badge awards</button>
                    </form>
                </div>
            </div>
            <div class="admin-badge-list mt-3">
                <?php foreach ($badgeSummary as $badge) : ?>
                    <?php
                    $badgeToken = (string) ($badge['token'] ?? '');
                    $isEarnedForSelectedUser = $selectedBadgeUserId > 0 && in_array($badgeToken, $selectedBadgeTokens, true);
                    ?>
                    <article class="admin-badge-tile<?= $isEarnedForSelectedUser ? ' is-earned' : '' ?>">
                        <div class="admin-badge-tile__top">
                            <img class="admin-badge-tile__img" src="../<?= htmlspecialchars((string) ($badge['image'] ?? ''), ENT_QUOTES) ?>" alt="<?= htmlspecialchars((string) ($badge['title'] ?? 'Badge artwork'), ENT_QUOTES) ?>">
                            <div>
                                <h3 class="admin-badge-tile__title"><?= htmlspecialchars((string) ($badge['title'] ?? 'Badge'), ENT_QUOTES) ?></h3>
                                <p class="admin-badge-tile__desc"><?= htmlspecialchars((string) ($badge['description'] ?? ''), ENT_QUOTES) ?></p>
                            </div>
                        </div>
                        <div class="admin-badge-tile__footer<?= $selectedBadgeUserId > 0 ? '' : ' is-summary' ?>">
                            <?php if ($selectedBadgeUserId > 0) : ?>
                                <span class="admin-badge-tile__status<?= $isEarnedForSelectedUser ? ' is-earned' : '' ?>">
                                    <i class="bi <?= $isEarnedForSelectedUser ? 'bi-check-circle-fill' : 'bi-circle' ?>"></i>
                                    <?= $isEarnedForSelectedUser ? 'Earned' : 'Locked' ?>
                                </span>
                            <?php else : ?>
                                <span class="admin-badge-tile__count"><?= (int) ($badge['count'] ?? 0) ?> player<?= (int) ($badge['count'] ?? 0) === 1 ? '' : 's' ?></span>
                            <?php endif; ?>
                            <?php if ($selectedBadgeUserId > 0) : ?>
                                <span class="admin-note"><?= (int) ($badge['count'] ?? 0) ?> player<?= (int) ($badge['count'] ?? 0) === 1 ? '' : 's' ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if ($selectedBadgeTitle !== '') : ?>
                <div class="admin-sql-result mt-3">
                    <p class="mb-2"><strong><?= htmlspecialchars($selectedBadgeTitle, ENT_QUOTES) ?></strong> has been awarded to <strong><?= count($selectedBadgeHolders) ?></strong> player<?= count($selectedBadgeHolders) === 1 ? '' : 's' ?>.</p>
                    <?php if ($selectedBadgeHolders === []) : ?>
                        <p class="admin-note mb-0">Nobody has earned this badge yet.</p>
                    <?php else : ?>
                        <div class="admin-checkbox-list" style="max-height: 280px;">
                            <?php foreach ($selectedBadgeHolders as $holder) : ?>
                                <?php
                                $holderName = trim((string) ($holder['firstname'] ?? '') . ' ' . (string) ($holder['surname'] ?? ''));
                                if ($holderName === '') {
                                    $holderName = '@' . trim((string) ($holder['username'] ?? 'player'));
                                }
                                ?>
                                <label>
                                    <span><?= htmlspecialchars($holderName, ENT_QUOTES) ?> <small>@<?= htmlspecialchars((string) ($holder['username'] ?? ''), ENT_QUOTES) ?></small></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-grid admin-grid--three">
            <div class="admin-card">
                <h3>Record Football Scores</h3>
                <p class="admin-note">Open the existing results capture page and record the actual match results against the fixture list.</p>
                <div class="admin-actions mt-3">
                    <a class="btn btn-primary" href="results.php"><i class="bi bi-plus-circle"></i> Open results page</a>
                </div>
            </div>

            <div class="admin-card">
                <h3>Recalculate Game</h3>
                <p class="admin-note">Re-run the scoring logic across all prediction stages and refresh ranking movement.</p>
                <form method="post" class="mt-3">
                    <input type="hidden" name="admin_action" value="recalculate_all">
                    <button type="submit" class="btn btn-outline-success"><i class="bi bi-arrow-repeat"></i> Recalculate all points</button>
                </form>
            </div>

            <div class="admin-card admin-danger">
                <h3>Reset Game Data</h3>
                <p class="admin-note">Clear recorded results and fixture scores, zero all stage points and reset all positions back to their starting rank.</p>
                <form method="post" class="mt-3" onsubmit="return confirm('Reset all recorded results and points? This cannot be undone easily.');">
                    <input type="hidden" name="admin_action" value="reset_game_data">
                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-exclamation-triangle"></i> Reset game data</button>
                </form>
            </div>
        </div>

        <div class="admin-grid admin-grid--two">
            <div class="admin-card admin-danger">
                <h3>Reset But Keep Users</h3>
                <p class="admin-note">Clear results, predictions, mini-leagues, polls, group standings, Fan Zone posts, and ranking movement while keeping selected registrations intact.</p>
                <form method="post" class="mt-3" onsubmit="return confirm('Clear all live game activity and preserve only the selected user registrations?');">
                    <input type="hidden" name="admin_action" value="reset_preserving_users">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button type="button" class="btn btn-outline-dark btn-sm" id="preserveUsersSelectAll">Select all</button>
                        <button type="button" class="btn btn-outline-dark btn-sm" id="preserveUsersClearAll">Clear all</button>
                    </div>
                    <div class="admin-checkbox-list">
                        <?php foreach ($users as $user) : ?>
                            <?php
                            $userId = (int) ($user['id'] ?? 0);
                            $displayName = trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? ''));
                            if ($displayName === '') {
                                $displayName = (string) ($user['username'] ?? 'User ' . $userId);
                            }
                            ?>
                            <label>
                                <input class="form-check-input preserve-user-checkbox" type="checkbox" name="preserve_user_ids[]" value="<?= $userId ?>"<?= in_array((string) ($user['username'] ?? ''), ['James', 'Oliver'], true) ? ' checked' : '' ?>>
                                <span><?= htmlspecialchars($displayName, ENT_QUOTES) ?> <small>@<?= htmlspecialchars((string) ($user['username'] ?? ''), ENT_QUOTES) ?></small></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-outline-danger mt-3"><i class="bi bi-person-x"></i> Reset and preserve selected users</button>
                </form>
            </div>

            <div class="admin-card">
                <h3>Run SQL</h3>
                <p class="admin-note">Run one SQL statement directly against the live database when you need a precise fix. This runner only allows one statement at a time and requires an explicit unlock.</p>
                <form method="post" class="mt-3">
                    <input type="hidden" name="admin_action" value="run_sql">
                    <div class="mb-3">
                        <label class="form-label" for="sql_statement">SQL statement</label>
                        <textarea class="form-control" id="sql_statement" name="sql_statement" rows="8" placeholder="SELECT id, username FROM live_user_information ORDER BY id DESC LIMIT 10;"><?= htmlspecialchars($sqlRunnerStatement, ENT_QUOTES) ?></textarea>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label" for="sql_confirm">Type RUN to confirm</label>
                            <input class="form-control" type="text" id="sql_confirm" name="sql_confirm" placeholder="RUN">
                        </div>
                        <div class="col-md-5">
                            <div class="form-check form-switch pt-md-4">
                                <input class="form-check-input" type="checkbox" id="sql_unlock" name="sql_unlock" value="1">
                                <label class="form-check-label" for="sql_unlock">Unlock SQL runner</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-terminal"></i> Run SQL</button>
                </form>

                <?php if (is_array($sqlRunnerOutput)) : ?>
                    <div class="admin-sql-result">
                        <p class="mb-2"><strong><?= htmlspecialchars((string) ($sqlRunnerOutput['message'] ?? ''), ENT_QUOTES) ?></strong></p>
                        <code><?= htmlspecialchars((string) ($sqlRunnerOutput['sql'] ?? ''), ENT_QUOTES) ?></code>
                        <?php if (!empty($sqlRunnerOutput['columns']) && !empty($sqlRunnerOutput['rows'])) : ?>
                            <div class="admin-table-preview mt-3">
                                <table class="table table-sm table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <?php foreach ($sqlRunnerOutput['columns'] as $column) : ?>
                                                <th><?= htmlspecialchars((string) $column, ENT_QUOTES) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sqlRunnerOutput['rows'] as $row) : ?>
                                            <tr>
                                                <?php foreach ($sqlRunnerOutput['columns'] as $column) : ?>
                                                    <td><?= htmlspecialchars((string) ($row[$column] ?? ''), ENT_QUOTES) ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-card admin-danger">
            <h3>Delete User</h3>
            <p class="admin-note">Remove one player entirely, including their predictions, temporary password row, Fan Zone posts, and any linked records that cascade from the user account. Useful for unpaid or withdrawn players after the opening window closes.</p>
            <form method="post" class="mt-3" onsubmit="return confirm('Delete this user and all linked data? This cannot be undone easily.');">
                <input type="hidden" name="admin_action" value="delete_user">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label" for="delete_user_id">User to delete</label>
                        <select class="form-select" id="delete_user_id" name="delete_user_id" required>
                            <option value="">Choose a user…</option>
                            <?php foreach ($users as $user) : ?>
                                <?php
                                $userId = (int) ($user['id'] ?? 0);
                                $displayName = trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? ''));
                                if ($displayName === '') {
                                    $displayName = (string) ($user['username'] ?? 'User ' . $userId);
                                }
                                $label = $displayName . ' (@' . (string) ($user['username'] ?? '') . ')';
                                if ((string) ($user['haspaid'] ?? '') === 'No') {
                                    $label .= ' · unpaid';
                                }
                                if ($userId === (int) ($_SESSION['id'] ?? 0)) {
                                    $label .= ' · current admin';
                                }
                                ?>
                                <option value="<?= $userId ?>"<?= $userId === (int) ($_SESSION['id'] ?? 0) ? ' disabled' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-trash3"></i> Delete user</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <h3>Restore Prediction Backup</h3>
            <p class="admin-note">Restore a player’s preserved first-submission snapshot for one stage back into the live predictions table, then recalculate the points and rankings.</p>
            <form method="post" class="mt-3" onsubmit="return confirm('Restore this preserved prediction snapshot back into the live stage table? This will overwrite the player\\'s current live predictions for that stage.');">
                <input type="hidden" name="admin_action" value="restore_prediction_backup">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="restore_user_id">Player</label>
                        <select class="form-select" id="restore_user_id" name="restore_user_id" required>
                            <option value="">Choose a player…</option>
                            <?php foreach ($users as $user) : ?>
                                <?php
                                $userId = (int) ($user['id'] ?? 0);
                                $displayName = trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? ''));
                                if ($displayName === '') {
                                    $displayName = (string) ($user['username'] ?? 'User ' . $userId);
                                }
                                ?>
                                <option value="<?= $userId ?>"><?= htmlspecialchars($displayName . ' (@' . (string) ($user['username'] ?? '') . ')', ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="restore_stage_key">Stage</label>
                        <select class="form-select" id="restore_stage_key" name="restore_stage_key" required>
                            <option value="">Choose a stage…</option>
                            <?php foreach ($restoreStageOptions as $stageKey => $stageLabel) : ?>
                                <option value="<?= htmlspecialchars($stageKey, ENT_QUOTES) ?>"><?= htmlspecialchars($stageLabel, ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-outline-dark w-100"><i class="bi bi-clock-history"></i> Restore backup</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <h3>Scoring Audit</h3>
                    <p class="admin-note">Pick one fixture and we’ll show the exact result, every player prediction, and why the scoring engine awarded those points.</p>
                </div>
                <form method="get" class="d-flex flex-wrap align-items-end gap-2">
                    <div>
                        <label class="form-label" for="audit_match">Fixture</label>
                        <select class="form-select" id="audit_match" name="audit_match">
                            <?php foreach ($auditFixtures as $fixture) : ?>
                                <?php
                                $matchNumber = (int) ($fixture['match_number'] ?? 0);
                                $fixtureLabel = 'Match ' . $matchNumber . ' · ' . trim((string) ($fixture['stage'] ?? ''));
                                $fixtureLabel .= ' · ' . trim((string) ($fixture['hometeam'] ?? '')) . ' v ' . trim((string) ($fixture['awayteam'] ?? ''));
                                ?>
                                <option value="<?= $matchNumber ?>"<?= $selectedAuditMatch === $matchNumber ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($fixtureLabel, ENT_QUOTES) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" name="table" value="<?= htmlspecialchars($selectedTable, ENT_QUOTES) ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Inspect fixture</button>
                </form>
            </div>

            <?php if ($scoringAudit['fixture']) : ?>
                <?php
                $fixture = $scoringAudit['fixture'];
                $stageKey = (string) ($scoringAudit['context']['key'] ?? '');
                $stageBadgeClass = hh_admin_stage_badge_class($stageKey);
                $actualHome = $scoringAudit['actual_home'];
                $actualAway = $scoringAudit['actual_away'];
                $hasActual = is_numeric($actualHome) && is_numeric($actualAway);
                ?>
                <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                    <span class="badge rounded-pill <?= htmlspecialchars($stageBadgeClass, ENT_QUOTES) ?>">
                        <?= htmlspecialchars((string) ($scoringAudit['context']['label'] ?? ''), ENT_QUOTES) ?>
                    </span>
                    <span class="admin-score-result">
                        <strong><?= htmlspecialchars((string) ($fixture['hometeam'] ?? ''), ENT_QUOTES) ?></strong>
                        <span><?= $hasActual ? (int) $actualHome . ' - ' . (int) $actualAway : 'vs' ?></span>
                        <strong><?= htmlspecialchars((string) ($fixture['awayteam'] ?? ''), ENT_QUOTES) ?></strong>
                    </span>
                    <span class="admin-score-meta">
                        <strong>Match <?= (int) ($fixture['match_number'] ?? 0) ?></strong>
                        · <?= htmlspecialchars((string) ($fixture['date'] ?? ''), ENT_QUOTES) ?>
                        <?= htmlspecialchars((string) ($fixture['kotime'] ?? ''), ENT_QUOTES) ?>
                    </span>
                </div>

                <p class="admin-note mt-3"><?= htmlspecialchars((string) ($scoringAudit['message'] ?? ''), ENT_QUOTES) ?></p>

                <div class="admin-score-summary mt-3">
                    <div><strong><?= (int) ($scoringAudit['summary']['players'] ?? 0) ?></strong><span>players checked</span></div>
                    <div><strong><?= (int) ($scoringAudit['summary']['submitted'] ?? 0) ?></strong><span>predictions submitted</span></div>
                    <div><strong><?= (int) ($scoringAudit['summary']['perfect'] ?? 0) ?></strong><span>7-point perfects</span></div>
                    <div><strong><?= (int) ($scoringAudit['summary']['strong'] ?? 0) ?></strong><span>3-point reads</span></div>
                    <div><strong><?= (int) ($scoringAudit['summary']['outcome'] ?? 0) ?></strong><span>2-point outcomes</span></div>
                    <div><strong><?= (int) ($scoringAudit['summary']['single'] ?? 0) ?></strong><span>1-point hits</span></div>
                    <div><strong><?= (int) ($scoringAudit['summary']['miss'] ?? 0) ?></strong><span>0-point misses</span></div>
                </div>

                <div class="admin-table-preview mt-3">
                    <table class="table table-sm table-striped align-middle admin-score-table">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Prediction</th>
                                <th>Actual</th>
                                <th>Points</th>
                                <th>Why</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scoringAudit['rows'] as $row) : ?>
                                <?php
                                $detail = $row['detail'] ?? [];
                                $category = (string) ($detail['category'] ?? 'pending');
                                $points = $detail['points'];
                                $predictionLabel = is_numeric($row['pred_home']) && is_numeric($row['pred_away'])
                                    ? (int) $row['pred_home'] . ' - ' . (int) $row['pred_away']
                                    : '—';
                                $actualLabel = $hasActual ? (int) $actualHome . ' - ' . (int) $actualAway : '—';
                                ?>
                                <tr>
                                    <td>
                                        <div class="admin-score-player">
                                            <strong><?= htmlspecialchars(trim(($row['firstname'] ?? '') . ' ' . ($row['surname'] ?? '')), ENT_QUOTES) ?></strong>
                                            <span>@<?= htmlspecialchars((string) ($row['username'] ?? ''), ENT_QUOTES) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($predictionLabel, ENT_QUOTES) ?></td>
                                    <td><?= htmlspecialchars($actualLabel, ENT_QUOTES) ?></td>
                                    <td>
                                        <span class="admin-score-pill admin-score-pill--<?= htmlspecialchars($category, ENT_QUOTES) ?>">
                                            <?= $points === null ? '—' : (int) $points ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars((string) ($detail['label'] ?? ''), ENT_QUOTES) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p class="admin-note mt-3">No fixture is available to audit yet.</p>
            <?php endif; ?>
        </div>

        <div class="admin-grid admin-grid--two">
            <div class="admin-card">
                <h3>Add or Deduct Points</h3>
                <form method="post">
                    <input type="hidden" name="admin_action" value="adjust_points">
                    <div>
                        <label class="form-label" for="username">Player</label>
                        <select class="form-select" id="username" name="username" required>
                            <option value="">Choose a player</option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>">
                                    <?= htmlspecialchars($user['firstname'] . ' ' . $user['surname'] . ' (' . $user['username'] . ')', ENT_QUOTES) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="stage_table">Scoring stage</label>
                        <select class="form-select" id="stage_table" name="stage_table" required>
                            <?php foreach ($stageOptions as $tableName => $label) : ?>
                                <option value="<?= htmlspecialchars($tableName, ENT_QUOTES) ?>"><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="points_delta">Points adjustment</label>
                        <input class="form-control" type="number" id="points_delta" name="points_delta" min="-50" max="50" step="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-slash-minus"></i> Apply points change</button>
                </form>
            </div>

            <div class="admin-card">
                <h3>Update Player Status</h3>
                <form method="post">
                    <input type="hidden" name="admin_action" value="set_payment_status">
                    <div>
                        <label class="form-label" for="user_id">Player</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Choose a player</option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?= (int) $user['id'] ?>">
                                    <?= htmlspecialchars($user['firstname'] . ' ' . $user['surname'] . ' - paid: ' . $user['haspaid'], ENT_QUOTES) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="haspaid">Payment status</label>
                        <select class="form-select" id="haspaid" name="haspaid" required>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-success"><i class="bi bi-cash-coin"></i> Save payment status</button>
                </form>
            </div>
        </div>

        <div class="admin-grid admin-grid--two">
            <div class="admin-card">
                <h3>Quick Table Browser</h3>
                <form method="get" class="mb-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label" for="table">Database table</label>
                            <select class="form-select" id="table" name="table">
                                <?php foreach ($tableOptions as $tableName => $label) : ?>
                                    <option value="<?= htmlspecialchars($tableName, ENT_QUOTES) ?>"<?= $selectedTable === $tableName ? ' selected' : '' ?>>
                                        <?= htmlspecialchars($label, ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="table_rows">Rows to show</label>
                            <select class="form-select" id="table_rows" name="table_rows">
                                <?php foreach (['10', '30', '50', '100', '250', '500', 'all'] as $rowMode) : ?>
                                    <option value="<?= htmlspecialchars($rowMode, ENT_QUOTES) ?>"<?= $selectedRowMode === $rowMode ? ' selected' : '' ?>>
                                        <?= $rowMode === 'all' ? 'All rows' : htmlspecialchars($rowMode, ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="hidden" name="table_page" value="1">
                            <button type="submit" class="btn btn-outline-dark w-100"><i class="bi bi-table"></i> Browse table</button>
                        </div>
                    </div>
                </form>
                <p class="admin-note">
                    Showing
                    <strong><?= (int) ($tablePreview['showing_from'] ?? 0) ?></strong>
                    to
                    <strong><?= (int) ($tablePreview['showing_to'] ?? 0) ?></strong>
                    of
                    <strong><?= (int) ($tablePreview['total_rows'] ?? 0) ?></strong>
                    row<?= (int) ($tablePreview['total_rows'] ?? 0) === 1 ? '' : 's' ?>.
                    <?php if (!empty($tablePreview['is_paginated'])) : ?>
                        Page <strong><?= (int) ($tablePreview['page'] ?? 1) ?></strong> of <strong><?= (int) ($tablePreview['page_count'] ?? 1) ?></strong>.
                    <?php endif; ?>
                </p>
                <div class="admin-table-preview">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <?php foreach ($tablePreview['columns'] as $column) : ?>
                                    <th><?= htmlspecialchars($column, ENT_QUOTES) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($tablePreview['rows'] === []) : ?>
                                <tr><td colspan="<?= max(1, count($tablePreview['columns'])) ?>">No rows found.</td></tr>
                            <?php else : ?>
                                <?php foreach ($tablePreview['rows'] as $row) : ?>
                                    <tr>
                                        <?php foreach ($tablePreview['columns'] as $column) : ?>
                                            <td><?= htmlspecialchars((string) ($row[$column] ?? ''), ENT_QUOTES) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($tablePreview['is_paginated'])) : ?>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <form method="get" class="d-inline">
                            <input type="hidden" name="table" value="<?= htmlspecialchars($selectedTable, ENT_QUOTES) ?>">
                            <input type="hidden" name="table_rows" value="<?= htmlspecialchars($selectedRowMode, ENT_QUOTES) ?>">
                            <input type="hidden" name="table_page" value="<?= max(1, $selectedTablePage - 1) ?>">
                            <button type="submit" class="btn btn-outline-secondary btn-sm"<?= $selectedTablePage <= 1 ? ' disabled' : '' ?>><i class="bi bi-chevron-left"></i> Previous</button>
                        </form>
                        <span class="admin-note mb-0">Browsing all rows in manageable pages.</span>
                        <form method="get" class="d-inline">
                            <input type="hidden" name="table" value="<?= htmlspecialchars($selectedTable, ENT_QUOTES) ?>">
                            <input type="hidden" name="table_rows" value="<?= htmlspecialchars($selectedRowMode, ENT_QUOTES) ?>">
                            <input type="hidden" name="table_page" value="<?= min((int) ($tablePreview['page_count'] ?? 1), $selectedTablePage + 1) ?>">
                            <button type="submit" class="btn btn-outline-secondary btn-sm"<?= $selectedTablePage >= (int) ($tablePreview['page_count'] ?? 1) ? ' disabled' : '' ?>>Next <i class="bi bi-chevron-right"></i></button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <div class="admin-card">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div>
                        <h3>Direct Row Editor</h3>
                        <p class="admin-note">A deliberately narrow editor for quick, admin-only fixes. Choose a row, unlock the fields, and save one record at a time.</p>
                    </div>
                    <?php if ($editableTable) : ?>
                        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis"><?= htmlspecialchars((string) ($editableTable['label'] ?? 'Editable'), ENT_QUOTES) ?></span>
                    <?php else : ?>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Read-only table</span>
                    <?php endif; ?>
                </div>

                <?php if (!$editableTable) : ?>
                    <p class="admin-note mt-3">The currently selected table can still be previewed, but it is not enabled for direct editing here. That keeps the sharper edges away from the emergency tool.</p>
                <?php elseif (empty($editorRowOptions)) : ?>
                    <p class="admin-note mt-3">There are no rows available to edit in this table yet.</p>
                <?php else : ?>
                    <form method="get" class="mt-3">
                        <input type="hidden" name="table" value="<?= htmlspecialchars($selectedTable, ENT_QUOTES) ?>">
                        <div>
                            <label class="form-label" for="edit_row">Row to edit</label>
                            <select class="form-select" id="edit_row" name="edit_row" onchange="this.form.submit()">
                                <?php foreach ($editorRowOptions as $rowOption) : ?>
                                    <option value="<?= (int) ($rowOption['row_id'] ?? 0) ?>"<?= $selectedEditRowId === (int) ($rowOption['row_id'] ?? 0) ? ' selected' : '' ?>>
                                        <?= htmlspecialchars((string) ($rowOption['row_label'] ?? ''), ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <?php if ($editableRow) : ?>
                        <form method="post" class="mt-3" id="adminRowEditor">
                            <input type="hidden" name="admin_action" value="save_table_row">
                            <input type="hidden" name="table_name" value="<?= htmlspecialchars($selectedTable, ENT_QUOTES) ?>">
                            <input type="hidden" name="row_id" value="<?= (int) $selectedEditRowId ?>">
                            <input type="hidden" name="editor_unlock" id="editorUnlockValue" value="0">

                            <label class="admin-editor-toggle">
                                <input type="checkbox" id="editorUnlockToggle">
                                <span>Unlock this row for editing</span>
                            </label>

                            <div class="admin-editor-grid mt-3">
                                <?php foreach (($editableTable['columns'] ?? []) as $columnName => $meta) : ?>
                                    <?php
                                    $fieldId = 'editor_' . $columnName;
                                    $fieldType = (string) ($meta['type'] ?? 'text');
                                    $fieldValue = (string) ($editableRow[$columnName] ?? '');
                                    ?>
                                    <div>
                                        <label class="form-label" for="<?= htmlspecialchars($fieldId, ENT_QUOTES) ?>"><?= htmlspecialchars((string) ($meta['label'] ?? $columnName), ENT_QUOTES) ?></label>
                                        <?php if ($fieldType === 'textarea') : ?>
                                            <textarea class="form-control admin-editor-field" id="<?= htmlspecialchars($fieldId, ENT_QUOTES) ?>" name="editor[<?= htmlspecialchars($columnName, ENT_QUOTES) ?>]" data-original="<?= htmlspecialchars($fieldValue, ENT_QUOTES) ?>" readonly><?= htmlspecialchars($fieldValue, ENT_QUOTES) ?></textarea>
                                        <?php elseif ($fieldType === 'select') : ?>
                                            <select class="form-select admin-editor-field" id="<?= htmlspecialchars($fieldId, ENT_QUOTES) ?>" name="editor[<?= htmlspecialchars($columnName, ENT_QUOTES) ?>]" data-original="<?= htmlspecialchars($fieldValue, ENT_QUOTES) ?>" disabled>
                                                <?php foreach ((array) ($meta['options'] ?? []) as $optionValue) : ?>
                                                    <option value="<?= htmlspecialchars((string) $optionValue, ENT_QUOTES) ?>"<?= $fieldValue === (string) $optionValue ? ' selected' : '' ?>><?= htmlspecialchars((string) $optionValue, ENT_QUOTES) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else : ?>
                                            <input class="form-control admin-editor-field" type="<?= htmlspecialchars($fieldType === 'number' ? 'number' : ($fieldType === 'date' ? 'date' : ($fieldType === 'time' ? 'time' : $fieldType)), ENT_QUOTES) ?>" id="<?= htmlspecialchars($fieldId, ENT_QUOTES) ?>" name="editor[<?= htmlspecialchars($columnName, ENT_QUOTES) ?>]" value="<?= htmlspecialchars($fieldValue, ENT_QUOTES) ?>" data-original="<?= htmlspecialchars($fieldValue, ENT_QUOTES) ?>"<?= $fieldType === 'number' ? ' step="1"' : '' ?> readonly>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="submit" class="btn btn-primary" id="adminRowEditorSave" disabled><i class="bi bi-save"></i> Save row</button>
                                <button type="button" class="btn btn-outline-dark" id="adminRowEditorReset" disabled><i class="bi bi-arrow-counterclockwise"></i> Reset edits</button>
                            </div>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

                <form method="post" class="mt-4 pt-3 border-top" onsubmit="return confirm('Clear every Fan Zone post?');">
                    <input type="hidden" name="admin_action" value="clear_fanzone">
                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash3"></i> Clear Fan Zone board</button>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <h3>Prediction Integrity Audit</h3>
            <p class="admin-note">A pre-flight check for stage tables, player rows, and suspicious prediction data before you trust the game to run itself.</p>
            <div class="admin-audit-grid mt-3">
                <?php foreach ($predictionIntegrityAudit as $audit) : ?>
                    <?php
                    $statusClass = 'admin-audit-status--' . $audit['status'];
                    $cardClass = 'is-' . $audit['status'];
                    ?>
                    <article class="admin-audit-card <?= htmlspecialchars($cardClass, ENT_QUOTES) ?>">
                        <div class="admin-audit-card__top">
                            <div>
                                <h4><?= htmlspecialchars($audit['label'], ENT_QUOTES) ?></h4>
                                <p><?= htmlspecialchars($audit['table'], ENT_QUOTES) ?></p>
                            </div>
                            <span class="admin-audit-status <?= htmlspecialchars($statusClass, ENT_QUOTES) ?>">
                                <?php if ($audit['status'] === 'ready') : ?>
                                    <i class="bi bi-check-circle-fill"></i> Ready
                                <?php elseif ($audit['status'] === 'missing-table') : ?>
                                    <i class="bi bi-x-octagon-fill"></i> Missing table
                                <?php else : ?>
                                    <i class="bi bi-exclamation-triangle-fill"></i> Review
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="admin-audit-metrics">
                            <div><strong><?= (int) $audit['rows'] ?></strong><span>rows present</span></div>
                            <div><strong><?= (int) $audit['expected_users'] ?></strong><span>users expected</span></div>
                            <div><strong><?= (int) $audit['complete_rows'] ?></strong><span>complete rows</span></div>
                            <div><strong><?= (int) count($audit['missing_users']) + (int) count($audit['incomplete_rows']) + (int) count($audit['orphan_rows']) + (int) $audit['duplicate_ids'] + (int) $audit['duplicate_usernames'] ?></strong><span>issues found</span></div>
                        </div>

                        <?php if ($audit['status'] === 'ready') : ?>
                            <p class="mb-0 text-success-emphasis">Every registered player has one complete row in this stage table.</p>
                        <?php else : ?>
                            <ul class="admin-audit-list">
                                <?php if ($audit['rows'] !== $audit['expected_users']) : ?>
                                    <li><?= (int) $audit['rows'] ?> rows found for <?= (int) $audit['expected_users'] ?> registered players.</li>
                                <?php endif; ?>
                                <?php if ($audit['duplicate_ids'] > 0) : ?>
                                    <li><?= (int) $audit['duplicate_ids'] ?> duplicate player ID group(s) detected.</li>
                                <?php endif; ?>
                                <?php if ($audit['duplicate_usernames'] > 0) : ?>
                                    <li><?= (int) $audit['duplicate_usernames'] ?> duplicate username group(s) detected.</li>
                                <?php endif; ?>
                                <?php if (!empty($audit['missing_users'])) : ?>
                                    <li>Missing rows for: <?= htmlspecialchars(implode(', ', array_map(static fn(array $row): string => trim(($row['firstname'] ?? '') . ' ' . ($row['surname'] ?? '')) ?: (string) ($row['username'] ?? ''), array_slice($audit['missing_users'], 0, 5))), ENT_QUOTES) ?><?= count($audit['missing_users']) > 5 ? '…' : '' ?></li>
                                <?php endif; ?>
                                <?php if (!empty($audit['incomplete_rows'])) : ?>
                                    <li>Incomplete rows for: <?= htmlspecialchars(implode(', ', array_map(static fn(array $row): string => trim(($row['firstname'] ?? '') . ' ' . ($row['surname'] ?? '')) ?: (string) ($row['username'] ?? ''), array_slice($audit['incomplete_rows'], 0, 5))), ENT_QUOTES) ?><?= count($audit['incomplete_rows']) > 5 ? '…' : '' ?></li>
                                <?php endif; ?>
                                <?php if (!empty($audit['orphan_rows'])) : ?>
                                    <li>Orphan rows exist for usernames no longer linked to a live user record.</li>
                                <?php endif; ?>
                                <?php if ($audit['status'] === 'missing-table') : ?>
                                    <li>This stage table has not been created in the current database.</li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="admin-card">
            <h3>Upcoming Fixtures</h3>
            <div class="admin-table-preview">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Home</th>
                            <th>Away</th>
                            <th>Score</th>
                            <th>Date</th>
                            <th>KO</th>
                            <th>Stage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fixturePreview as $fixture) : ?>
                            <tr>
                                <td><?= (int) $fixture['id'] ?></td>
                                <td><?= htmlspecialchars($fixture['hometeam'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($fixture['awayteam'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars((string) ($fixture['homescore'] ?? '-'), ENT_QUOTES) ?> - <?= htmlspecialchars((string) ($fixture['awayscore'] ?? '-'), ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars((string) $fixture['date'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars((string) $fixture['kotime'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars((string) $fixture['stage'], ENT_QUOTES) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
  (() => {
    const selectAllButton = document.getElementById('preserveUsersSelectAll');
    const clearAllButton = document.getElementById('preserveUsersClearAll');
    const preserveCheckboxes = Array.from(document.querySelectorAll('.preserve-user-checkbox'));

    if (!selectAllButton || !clearAllButton || preserveCheckboxes.length === 0) {
      return;
    }

    selectAllButton.addEventListener('click', () => {
      preserveCheckboxes.forEach((checkbox) => {
        checkbox.checked = true;
      });
    });

    clearAllButton.addEventListener('click', () => {
      preserveCheckboxes.forEach((checkbox) => {
        checkbox.checked = false;
      });
    });
  })();

  (() => {
    const unlockToggle = document.getElementById('editorUnlockToggle');
    const unlockValue = document.getElementById('editorUnlockValue');
    const saveButton = document.getElementById('adminRowEditorSave');
    const resetButton = document.getElementById('adminRowEditorReset');
    const fields = Array.from(document.querySelectorAll('.admin-editor-field'));

    if (!unlockToggle || !unlockValue || fields.length === 0) {
      return;
    }

    const syncUnlockState = () => {
      const unlocked = unlockToggle.checked;
      unlockValue.value = unlocked ? '1' : '0';
      saveButton.disabled = !unlocked;
      resetButton.disabled = !unlocked;

      fields.forEach((field) => {
        if (field.tagName === 'SELECT') {
          field.disabled = !unlocked;
        } else {
          field.readOnly = !unlocked;
        }
      });
    };

    const refreshChangedState = () => {
      fields.forEach((field) => {
        const original = field.dataset.original ?? '';
        field.classList.toggle('is-changed', (field.value ?? '') !== original);
      });
    };

    unlockToggle.addEventListener('change', syncUnlockState);
    resetButton.addEventListener('click', () => {
      fields.forEach((field) => {
        field.value = field.dataset.original ?? '';
      });
      refreshChangedState();
    });

    fields.forEach((field) => {
      field.addEventListener('input', refreshChangedState);
      field.addEventListener('change', refreshChangedState);
    });

    syncUnlockState();
    refreshChangedState();
  })();
</script>

<?php include "../php/footer.php"; ?>
