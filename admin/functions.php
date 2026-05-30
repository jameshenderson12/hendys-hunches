<?php
session_start();
$page_title = 'Admin Functions';

require_once dirname(__DIR__) . '/php/auth.php';
require_once dirname(__DIR__) . '/php/config.php';
require_once dirname(__DIR__) . '/php/process.php';

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
        "DELETE FROM live_temp_information WHERE username NOT IN ({$quotedUsernames})",
        "UPDATE live_temp_information SET temp_pass = '' WHERE username IN ({$quotedUsernames})",
        "DELETE FROM live_user_information WHERE id NOT IN ({$idList})",
        "UPDATE live_user_information SET lastlogin = NULL",
    ];

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

$tableOptions = [
    'live_user_information' => 'User information',
    'live_match_schedule' => 'Match schedule',
    'live_match_results' => 'Match results',
    'live_user_predictions_groups' => 'Group predictions',
    'live_user_predictions_ro32' => 'Round of 32 predictions',
    'live_user_predictions_ro16' => 'Round of 16 predictions',
    'live_user_predictions_qf' => 'Quarter-final predictions',
    'live_user_predictions_sf' => 'Semi-final predictions',
    'live_user_predictions_final' => 'Final predictions',
    'live_fanzone_posts' => 'Fan Zone posts',
];

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
    $selectedTable = 'live_user_information';
}

$users = hh_admin_fetch_all(
    $con,
    "SELECT id, username, firstname, surname, haspaid FROM live_user_information ORDER BY surname ASC, firstname ASC"
);

$snapshot = [
    'users' => 0,
    'fixtures' => 0,
    'results' => 0,
    'result_snapshots' => 0,
    'fanzone' => 0,
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

$tablePreview = hh_admin_preview_table($con, $selectedTable, 30);
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
    </style>

<div class="admin-shell">
    <div class="page-hero page-hero--admin">
        <div>
            <p class="eyebrow" style="color: #FF0000 !important">Admin control room</p>
            <h1>Game Functions</h1>
            <p class="lead mb-0">A central place to run the core admin actions that steer the game and keep the data in shape.</p>
        </div>
        <div class="page-hero__actions">
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

    <section class="admin-grid">
        <div class="admin-card">
            <h2>System Snapshot</h2>
            <div class="admin-kpi">
                <div class="admin-kpi__item"><strong><?= $snapshot['users'] ?></strong><span>registered players</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['fixtures'] ?></strong><span>scheduled fixtures</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['results'] ?></strong><span>fixtures with results</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['result_snapshots'] ?></strong><span>result snapshots saved</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['fanzone'] ?></strong><span>live Fan Zone posts</span></div>
            </div>
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
                    <div>
                        <label class="form-label" for="table">Database table</label>
                        <select class="form-select" id="table" name="table" onchange="this.form.submit()">
                            <?php foreach ($tableOptions as $tableName => $label) : ?>
                                <option value="<?= htmlspecialchars($tableName, ENT_QUOTES) ?>"<?= $selectedTable === $tableName ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($label, ENT_QUOTES) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
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
