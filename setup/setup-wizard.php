<?php
session_start();
require_once dirname(__DIR__) . '/php/auth.php';
require_once dirname(__DIR__) . '/php/flags.php';
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/process.php';

hh_require_login('../index.php');

$page_title = 'Installation Manager';

$errors = [];
$messages = [];
$sql_output = [];
$fixture_preview = [];
$group_preview = [];
$tournament_summary = [];
$config_preview = [];
$database_preview = [];
$setup_summary = [];
$permission_checks = [];
$db_connect_template = '';
$wizard_action = $_POST['wizard_action'] ?? '';
$inline_mysql_feedback = null;
$table_install_context = [];
$runtime_audit = [];
$table_audit = [];
$database_contents = [];

$mysql_diagnostics = [
    'status' => 'Not tested',
    'message' => '',
    'server' => '',
    'target_database' => '',
    'database_exists' => false,
    'table_count' => 0,
    'tables' => [],
];

function resolve_directory_path(string $root, string $relative): string {
    $relative = ltrim($relative, '/');
    return rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relative;
}

function slugify_value(string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';
    return trim($value, '_');
}

function mysql_quote_identifier(string $identifier): string {
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function mysql_quote_string(string $value): string {
    return "'" . addslashes($value) . "'";
}

function infer_tournament_name(string $explicitName, string $sourceReference): string {
    if ($explicitName !== '') {
        return $explicitName;
    }

    $path = parse_url($sourceReference, PHP_URL_PATH) ?: $sourceReference;
    $basename = pathinfo($path, PATHINFO_FILENAME);
    $basename = preg_replace('/[-_]+/', ' ', $basename) ?? $basename;
    $basename = trim($basename);

    return $basename === '' ? 'Imported Tournament' : ucwords($basename);
}

function format_config_date(?string $date): string {
    $date = trim((string)$date);
    if ($date === '') {
        return '';
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    if ($dt instanceof DateTimeImmutable) {
        return $dt->format('d/m/Y');
    }

    return $date;
}

function read_fixture_source_payload(string $fixturesUrl): array {
    $payload = '';
    $sourceReference = '';
    $sourceType = '';
    $detectedFormat = '';
    $errors = [];

    if ($fixturesUrl !== '') {
        $payload = @file_get_contents($fixturesUrl);
        if ($payload === false) {
            $errors[] = 'Unable to fetch fixture data from the provided feed URL.';
        } else {
            $sourceReference = $fixturesUrl;
            $sourceType = 'Feed URL';
        }
    } elseif (!empty($_FILES['fixtures_file']['tmp_name'])) {
        $payload = file_get_contents($_FILES['fixtures_file']['tmp_name']);
        $sourceReference = $_FILES['fixtures_file']['name'] ?? 'uploaded-file';
        $sourceType = 'Uploaded file';
    } else {
        $errors[] = 'Provide either a feed URL or upload a local CSV/JSON fixture file.';
    }

    if ($payload !== '') {
        $trimmed = ltrim($payload);
        $extension = strtolower(pathinfo($sourceReference, PATHINFO_EXTENSION));
        $isJson = $extension === 'json' || str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[');
        $detectedFormat = $isJson ? 'JSON' : 'CSV';
    }

    return [$payload, $sourceReference, $sourceType, $detectedFormat, $errors];
}

function parse_fixture_datetime(?string $raw): array {
    $raw = trim((string)$raw);
    if ($raw === '') {
        return ['date' => null, 'time' => '', 'datetime' => null];
    }

    $displayTimezone = new DateTimeZone('Europe/London');

    try {
        $dt = new DateTimeImmutable($raw);
    } catch (Throwable $exception) {
        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return ['date' => null, 'time' => '', 'datetime' => null];
        }
        $dt = (new DateTimeImmutable('@' . $timestamp))->setTimezone(new DateTimeZone('UTC'));
    }

    $utc = $dt->setTimezone(new DateTimeZone('UTC'));
    $local = $utc->setTimezone($displayTimezone);

    return [
        'date' => $local->format('Y-m-d'),
        'time' => $local->format('H:i'),
        'datetime' => $utc->format(DateTimeInterface::ATOM),
    ];
}

function resolve_knockout_stage(?int $roundNumber): string {
    return match ($roundNumber) {
        4 => 'Round of 32',
        5 => 'Round of 16',
        6 => 'Quarter-Finals',
        7 => 'Semi-Finals',
        8 => 'Final Stage',
        default => 'Knockout Stage',
    };
}

function normalize_fixture_row(array $row): array {
    $home = trim((string)($row['hometeam'] ?? $row['home'] ?? $row['HomeTeam'] ?? ''));
    $away = trim((string)($row['awayteam'] ?? $row['away'] ?? $row['AwayTeam'] ?? ''));

    if ($home === '' || $away === '') {
        return [];
    }

    $group = trim((string)($row['group'] ?? $row['Group'] ?? ''));
    $roundNumber = isset($row['round_number']) ? (int)$row['round_number'] : (isset($row['RoundNumber']) ? (int)$row['RoundNumber'] : null);
    $matchNumber = isset($row['match_number']) ? (int)$row['match_number'] : (isset($row['MatchNumber']) ? (int)$row['MatchNumber'] : null);
    $venue = trim((string)($row['venue'] ?? $row['Venue'] ?? $row['Location'] ?? 'TBD'));
    $homeFlag = trim((string)($row['hometeamimg'] ?? $row['home_flag'] ?? $row['HomeFlag'] ?? $row['HomeTeamFlag'] ?? ''));
    $awayFlag = trim((string)($row['awayteamimg'] ?? $row['away_flag'] ?? $row['AwayFlag'] ?? $row['AwayTeamFlag'] ?? ''));
    $datetime = parse_fixture_datetime($row['DateUtc'] ?? $row['date_utc'] ?? $row['date'] ?? $row['Date'] ?? '');
    $stage = $group !== '' ? $group : resolve_knockout_stage($roundNumber);

    if ($homeFlag === '') {
        $homeFlag = hh_get_team_flag_path($home);
    }
    if ($awayFlag === '') {
        $awayFlag = hh_get_team_flag_path($away);
    }

    return [
        'match_number' => $matchNumber,
        'round_number' => $roundNumber,
        'stage' => $stage,
        'group' => $group,
        'hometeamimg' => $homeFlag,
        'hometeam' => $home,
        'homescore' => null,
        'awayscore' => null,
        'awayteam' => $away,
        'awayteamimg' => $awayFlag,
        'venue' => $venue,
        'kotime' => $datetime['time'],
        'date' => $datetime['date'],
        'datetime_utc' => $datetime['datetime'],
    ];
}

function parse_fixture_csv(string $payload): array {
    $rows = [];
    $lines = preg_split('/\r\n|\r|\n/', trim($payload));
    if (!$lines || count($lines) === 0) {
        return $rows;
    }

    $header = str_getcsv(array_shift($lines));
    foreach ($lines as $line) {
        if (trim($line) === '') {
            continue;
        }

        $values = str_getcsv($line);
        $row = [];
        foreach ($header as $index => $column) {
            $row[trim((string)$column)] = $values[$index] ?? '';
        }

        $normalized = normalize_fixture_row($row);
        if (!empty($normalized)) {
            $rows[] = $normalized;
        }
    }

    return $rows;
}

function parse_fixture_json(string $payload): array {
    $rows = [];
    $data = json_decode($payload, true);
    if (!is_array($data)) {
        return $rows;
    }

    $items = isset($data['fixtures']) && is_array($data['fixtures']) ? $data['fixtures'] : $data;
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $normalized = normalize_fixture_row($item);
        if (!empty($normalized)) {
            $rows[] = $normalized;
        }
    }

    return $rows;
}

function enrich_fixture_stages(array $fixtures): array {
    $finalStageIndexes = [];

    foreach ($fixtures as $index => $fixture) {
        if (($fixture['round_number'] ?? null) === 8) {
            $finalStageIndexes[] = $index;
        }
    }

    if (count($finalStageIndexes) === 2) {
        usort($finalStageIndexes, function (int $left, int $right) use ($fixtures): int {
            return strcmp((string)$fixtures[$left]['datetime_utc'], (string)$fixtures[$right]['datetime_utc']);
        });

        $fixtures[$finalStageIndexes[0]]['stage'] = 'Third Place Play-Off';
        $fixtures[$finalStageIndexes[1]]['stage'] = 'Final';
    } elseif (count($finalStageIndexes) === 1) {
        $fixtures[$finalStageIndexes[0]]['stage'] = 'Final';
    }

    return $fixtures;
}

function build_tournament_metadata(array $fixtures, string $tournamentName, string $sourceReference, string $sourceType, string $detectedFormat): array {
    $groupStages = [];
    $groupFixtures = [];
    $knockoutFixtures = [];
    $groupTeams = [];
    $allRealTeams = [];
    $allDates = [];
    $groupDates = [];
    $knockoutDates = [];
    $stageCounts = [];
    $stageDates = [];

    foreach ($fixtures as $fixture) {
        if ($fixture['date'] !== null) {
            $allDates[] = $fixture['date'];
        }

        foreach ([$fixture['hometeam'], $fixture['awayteam']] as $team) {
            if (!hh_is_placeholder_team($team)) {
                $allRealTeams[$team] = true;
            }
        }

        if (($fixture['group'] ?? '') !== '') {
            $groupStages[$fixture['group']] = true;
            $groupFixtures[] = $fixture;
            $groupTeams[$fixture['group']][$fixture['hometeam']] = true;
            $groupTeams[$fixture['group']][$fixture['awayteam']] = true;
            if ($fixture['date'] !== null) {
                $groupDates[] = $fixture['date'];
            }
        } else {
            $knockoutFixtures[] = $fixture;
            if ($fixture['date'] !== null) {
                $knockoutDates[] = $fixture['date'];
            }
        }

        $stage = trim((string)($fixture['stage'] ?? ''));
        if ($stage !== '') {
            $stageCounts[$stage] = ($stageCounts[$stage] ?? 0) + 1;
            if ($fixture['date'] !== null) {
                $stageDates[$stage][] = $fixture['date'];
            }
        }
    }

    sort($allDates);
    sort($groupDates);
    sort($knockoutDates);

    $groupSizes = [];
    foreach ($groupTeams as $group => $teams) {
        $groupSizes[$group] = count($teams);
    }

    $teamsPerGroup = '';
    if (!empty($groupSizes)) {
        $uniqueSizes = array_values(array_unique(array_values($groupSizes)));
        $teamsPerGroup = count($uniqueSizes) === 1 ? (string)$uniqueSizes[0] : 'Mixed';
    }

    $finalDate = '';
    foreach ($fixtures as $fixture) {
        if ($fixture['stage'] === 'Final' && $fixture['date'] !== null) {
            $finalDate = $fixture['date'];
        }
    }
    if ($finalDate === '' && !empty($allDates)) {
        $finalDate = end($allDates);
    }

    foreach ($stageDates as $stage => $dates) {
        sort($dates);
        $stageDates[$stage] = $dates;
    }

    return [
        'tournament_name' => $tournamentName,
        'source_reference' => $sourceReference,
        'source_type' => $sourceType,
        'detected_format' => $detectedFormat,
        'total_matches' => count($fixtures),
        'group_count' => count($groupStages),
        'teams_per_group' => $teamsPerGroup,
        'confirmed_teams' => count($allRealTeams),
        'group_fixture_count' => count($groupFixtures),
        'knockout_fixture_count' => count($knockoutFixtures),
        'competition_start_date' => $allDates[0] ?? '',
        'competition_end_date' => $allDates[count($allDates) - 1] ?? '',
        'group_fixtures_start_date' => $groupDates[0] ?? '',
        'group_fixtures_end_date' => $groupDates[count($groupDates) - 1] ?? '',
        'knockout_fixtures_start_date' => $knockoutDates[0] ?? '',
        'round_of_32_start_date' => $stageDates['Round of 32'][0] ?? '',
        'round_of_32_end_date' => $stageDates['Round of 32'][count($stageDates['Round of 32'] ?? []) - 1] ?? '',
        'round_of_16_start_date' => $stageDates['Round of 16'][0] ?? '',
        'round_of_16_end_date' => $stageDates['Round of 16'][count($stageDates['Round of 16'] ?? []) - 1] ?? '',
        'quarter_final_start_date' => $stageDates['Quarter-Finals'][0] ?? '',
        'quarter_final_end_date' => $stageDates['Quarter-Finals'][count($stageDates['Quarter-Finals'] ?? []) - 1] ?? '',
        'semi_final_start_date' => $stageDates['Semi-Finals'][0] ?? '',
        'semi_final_end_date' => $stageDates['Semi-Finals'][count($stageDates['Semi-Finals'] ?? []) - 1] ?? '',
        'final_date' => $finalDate,
        'round_of_32_count' => $stageCounts['Round of 32'] ?? 0,
        'round_of_16_count' => $stageCounts['Round of 16'] ?? 0,
        'quarter_final_count' => $stageCounts['Quarter-Finals'] ?? 0,
        'semi_final_count' => $stageCounts['Semi-Finals'] ?? 0,
        'final_count' => ($stageCounts['Final'] ?? 0) + ($stageCounts['Final Stage'] ?? 0) + ($stageCounts['Third Place Play-Off'] ?? 0),
    ];
}

function build_group_preview(array $fixtures): array {
    $groups = [];

    foreach ($fixtures as $fixture) {
        $group = trim((string)($fixture['group'] ?? ''));
        if ($group === '') {
            continue;
        }

        foreach ([
            ['name' => $fixture['hometeam'], 'flag' => $fixture['hometeamimg']],
            ['name' => $fixture['awayteam'], 'flag' => $fixture['awayteamimg']],
        ] as $team) {
            if ($team['name'] === '' || hh_is_placeholder_team($team['name'])) {
                continue;
            }

            $groups[$group][$team['name']] = $team['flag'];
        }
    }

    ksort($groups);

    foreach ($groups as $group => $teams) {
        ksort($teams);
        $normalized = [];
        foreach ($teams as $name => $flag) {
            $normalized[] = ['name' => $name, 'flag' => $flag];
        }
        $groups[$group] = $normalized;
    }

    return $groups;
}

function build_config_preview(array $metadata): array {
    return [
        '$competition' => $metadata['tournament_name'],
        '$competition_start_date' => format_config_date($metadata['competition_start_date']),
        '$competition_end_date' => format_config_date($metadata['competition_end_date']),
        '$group_fixtures_start_date' => format_config_date($metadata['group_fixtures_start_date']),
        '$group_fixtures_end_date' => format_config_date($metadata['group_fixtures_end_date']),
        '$knockout_fixtures_start_date' => format_config_date($metadata['knockout_fixtures_start_date']),
        '$round_of_32_start_date' => format_config_date($metadata['round_of_32_start_date']),
        '$round_of_32_end_date' => format_config_date($metadata['round_of_32_end_date']),
        '$round_of_16_start_date' => format_config_date($metadata['round_of_16_start_date']),
        '$round_of_16_end_date' => format_config_date($metadata['round_of_16_end_date']),
        '$quarter_final_start_date' => format_config_date($metadata['quarter_final_start_date']),
        '$quarter_final_end_date' => format_config_date($metadata['quarter_final_end_date']),
        '$semi_final_start_date' => format_config_date($metadata['semi_final_start_date']),
        '$semi_final_end_date' => format_config_date($metadata['semi_final_end_date']),
        '$final_date' => format_config_date($metadata['final_date']),
        '$no_of_competition_groups' => $metadata['group_count'],
        '$no_of_competition_teams' => $metadata['confirmed_teams'],
        '$no_of_group_fixtures' => $metadata['group_fixture_count'],
        '$no_of_knockout_fixtures' => $metadata['knockout_fixture_count'],
        '$no_of_ro32_fixtures' => $metadata['round_of_32_count'],
        '$no_of_ro16_fixtures' => $metadata['round_of_16_count'],
        '$no_of_qf_fixtures' => $metadata['quarter_final_count'],
        '$no_of_sf_fixtures' => $metadata['semi_final_count'],
        '$no_of_final_fixtures' => $metadata['final_count'],
        '$no_of_total_fixtures' => $metadata['total_matches'],
    ];
}

function get_recommended_schedule_definition(): array {
    return [
        'id SMALLINT(6) NOT NULL AUTO_INCREMENT PRIMARY KEY',
        'hometeamimg VARCHAR(255) NOT NULL',
        'hometeam CHAR(50) NOT NULL',
        'homescore SMALLINT(6) NULL',
        'awayscore SMALLINT(6) NULL',
        'awayteam CHAR(50) NOT NULL',
        'awayteamimg VARCHAR(255) NOT NULL',
        'venue CHAR(70) NOT NULL',
        'kotime CHAR(5) NOT NULL',
        'date DATE NULL',
        'stage CHAR(50) NOT NULL',
        'match_number SMALLINT(6) NULL',
        'round_number SMALLINT(6) NULL',
    ];
}

function get_recommended_schedule_columns(): array {
    return [
        'hometeamimg',
        'hometeam',
        'homescore',
        'awayscore',
        'awayteam',
        'awayteamimg',
        'venue',
        'kotime',
        'date',
        'stage',
        'match_number',
        'round_number',
    ];
}

function build_schedule_create_sql(): string {
    return "CREATE TABLE IF NOT EXISTS live_match_schedule (\n  " . implode(",\n  ", get_recommended_schedule_definition()) . "\n);";
}

function build_tournament_config_create_sql(): string {
    return "CREATE TABLE IF NOT EXISTS tournament_config (\n"
        . "  id INT AUTO_INCREMENT PRIMARY KEY,\n"
        . "  tournament_name VARCHAR(255) NOT NULL,\n"
        . "  source_type VARCHAR(50) NOT NULL,\n"
        . "  source_reference VARCHAR(255) NOT NULL,\n"
        . "  total_matches INT NOT NULL,\n"
        . "  group_count INT NOT NULL,\n"
        . "  teams_per_group VARCHAR(20) NOT NULL,\n"
        . "  confirmed_teams INT NOT NULL,\n"
        . "  start_date DATE NOT NULL,\n"
        . "  end_date DATE NOT NULL,\n"
        . "  group_start_date DATE NULL,\n"
        . "  group_end_date DATE NULL,\n"
        . "  knockout_start_date DATE NULL,\n"
        . "  final_date DATE NULL,\n"
        . "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n"
        . ");";
}

function build_tournament_config_insert_sql(array $metadata): string {
    return sprintf(
        "INSERT INTO tournament_config (tournament_name, source_type, source_reference, total_matches, group_count, teams_per_group, confirmed_teams, start_date, end_date, group_start_date, group_end_date, knockout_start_date, final_date) VALUES (%s, %s, %s, %d, %d, %s, %d, %s, %s, %s, %s, %s, %s);",
        mysql_quote_string($metadata['tournament_name']),
        mysql_quote_string($metadata['source_type']),
        mysql_quote_string($metadata['source_reference']),
        $metadata['total_matches'],
        $metadata['group_count'],
        mysql_quote_string((string)$metadata['teams_per_group']),
        $metadata['confirmed_teams'],
        mysql_quote_string($metadata['competition_start_date']),
        mysql_quote_string($metadata['competition_end_date']),
        $metadata['group_fixtures_start_date'] === '' ? 'NULL' : mysql_quote_string($metadata['group_fixtures_start_date']),
        $metadata['group_fixtures_end_date'] === '' ? 'NULL' : mysql_quote_string($metadata['group_fixtures_end_date']),
        $metadata['knockout_fixtures_start_date'] === '' ? 'NULL' : mysql_quote_string($metadata['knockout_fixtures_start_date']),
        $metadata['final_date'] === '' ? 'NULL' : mysql_quote_string($metadata['final_date'])
    );
}

function build_schedule_insert_sql(array $fixture): string {
    $values = [
        mysql_quote_string((string)$fixture['hometeamimg']),
        mysql_quote_string((string)$fixture['hometeam']),
        'NULL',
        'NULL',
        mysql_quote_string((string)$fixture['awayteam']),
        mysql_quote_string((string)$fixture['awayteamimg']),
        mysql_quote_string((string)$fixture['venue']),
        mysql_quote_string((string)$fixture['kotime']),
        $fixture['date'] === null ? 'NULL' : mysql_quote_string((string)$fixture['date']),
        mysql_quote_string((string)$fixture['stage']),
        $fixture['match_number'] === null ? 'NULL' : (string)$fixture['match_number'],
        $fixture['round_number'] === null ? 'NULL' : (string)$fixture['round_number'],
    ];

    return sprintf(
        'INSERT INTO live_match_schedule (%s) VALUES (%s);',
        implode(', ', get_recommended_schedule_columns()),
        implode(', ', $values)
    );
}

function build_match_results_create_sql(int $totalScoreColumns): string {
    $columns = [
        'match_id SMALLINT(6) PRIMARY KEY NOT NULL AUTO_INCREMENT',
    ];

    for ($match = 1; $match <= $totalScoreColumns; $match++) {
        $columns[] = 'score' . $match . '_r TINYINT(4) DEFAULT NULL';
    }

    return "CREATE TABLE IF NOT EXISTS live_match_results (\n    " . implode(",\n    ", $columns) . "\n);";
}

function build_prediction_table_create_sql(string $tableName, int $startScoreIndex, int $scoreColumnCount): string {
    $columns = [
        'id SMALLINT(6) PRIMARY KEY NOT NULL',
        'username CHAR(50) UNIQUE NOT NULL',
        'firstname CHAR(50) NOT NULL',
        'surname CHAR(50) NOT NULL',
    ];

    for ($match = $startScoreIndex; $match < $startScoreIndex + $scoreColumnCount; $match++) {
        $columns[] = 'score' . $match . '_p TINYINT(4) DEFAULT NULL';
    }

    $columns[] = 'lastupdate TIMESTAMP NULL';
    $columns[] = "points_total SMALLINT(6) DEFAULT '0'";

    return "CREATE TABLE IF NOT EXISTS {$tableName} (\n    " . implode(",\n    ", $columns) . "\n);";
}

function read_table_install_context_from_request(): array {
    $keys = [
        'group_fixture_count',
        'round_of_32_count',
        'round_of_16_count',
        'quarter_final_count',
        'semi_final_count',
        'final_count',
        'total_matches',
    ];

    $context = [];
    foreach ($keys as $key) {
        if (!isset($_POST[$key]) || $_POST[$key] === '') {
            return [];
        }
        $context[$key] = max(0, (int) $_POST[$key]);
    }

    return $context;
}

function build_installable_table_definitions(array $context): array {
    $groupCount = $context['group_fixture_count'] ?? 0;
    $ro32Count = $context['round_of_32_count'] ?? 0;
    $ro16Count = $context['round_of_16_count'] ?? 0;
    $qfCount = $context['quarter_final_count'] ?? 0;
    $sfCount = $context['semi_final_count'] ?? 0;
    $finalCount = $context['final_count'] ?? 0;
    $totalMatches = $context['total_matches'] ?? 0;

    $groupScoreCount = $groupCount * 2;
    $ro32ScoreCount = $ro32Count * 2;
    $ro16ScoreCount = $ro16Count * 2;
    $qfScoreCount = $qfCount * 2;
    $sfScoreCount = $sfCount * 2;
    $finalScoreCount = $finalCount * 2;
    $totalScoreColumns = $totalMatches * 2;

    $ro32Start = $groupScoreCount + 1;
    $ro16Start = $ro32Start + $ro32ScoreCount;
    $qfStart = $ro16Start + $ro16ScoreCount;
    $sfStart = $qfStart + $qfScoreCount;
    $finalStart = $sfStart + $sfScoreCount;

    return [
        'user_info' => [
            'label' => 'User Information',
            'file' => 'sql/setup-user-info-table.sql',
            'table' => 'live_user_information',
            'sql' => file_get_contents(__DIR__ . '/../sql/setup-user-info-table.sql') ?: '',
            'description' => 'Player accounts, profile fields, payment status and ranking positions.',
        ],
        'temp_information' => [
            'label' => 'Temporary Passwords',
            'file' => 'sql/setup-temp-information.sql',
            'table' => 'live_temp_information',
            'sql' => file_get_contents(__DIR__ . '/../sql/setup-temp-information.sql') ?: '',
            'description' => 'Temporary password reset records for account recovery.',
        ],
        'group_standings' => [
            'label' => 'Group Standings',
            'file' => 'sql/setup-group-standings-table.sql',
            'table' => 'live_group_standings',
            'sql' => file_get_contents(__DIR__ . '/../sql/setup-group-standings-table.sql') ?: '',
            'description' => 'Cached standings table for the group stage.',
        ],
        'user_minileague' => [
            'label' => 'Mini-League Links',
            'file' => 'sql/setup-user-minileague-table.sql',
            'table' => 'live_user_minileague',
            'sql' => file_get_contents(__DIR__ . '/../sql/setup-user-minileague-table.sql') ?: '',
            'description' => 'Stores each player’s chosen mini-league opponents for personalised dashboard standings.',
        ],
        'fanzone_posts' => [
            'label' => 'Fan Zone Message Board',
            'file' => 'sql/setup-fanzone-board-table.sql',
            'table' => 'live_fanzone_posts',
            'sql' => file_get_contents(__DIR__ . '/../sql/setup-fanzone-board-table.sql') ?: '',
            'description' => 'Threaded community posts, replies, pinned updates and announcements.',
        ],
        'polls' => [
            'label' => 'Poll Questions',
            'file' => 'sql/setup-poll-table.sql',
            'table' => 'live_polls',
            'sql' => file_get_contents(__DIR__ . '/../sql/setup-poll-table.sql') ?: '',
            'description' => 'Stores the live Fan Zone poll question and whether it is currently active.',
        ],
        'poll_options' => [
            'label' => 'Poll Options',
            'file' => 'sql/setup-poll-options-table.sql',
            'table' => 'live_poll_options',
            'sql' => file_get_contents(__DIR__ . '/../sql/setup-poll-options-table.sql') ?: '',
            'description' => 'Stores the answer options for each Fan Zone poll.',
        ],
        'poll_votes' => [
            'label' => 'Poll Votes',
            'file' => 'sql/setup-poll-votes-table.sql',
            'table' => 'live_poll_votes',
            'sql' => file_get_contents(__DIR__ . '/../sql/setup-poll-votes-table.sql') ?: '',
            'description' => 'Stores one vote per player for each Fan Zone poll.',
        ],
        'user_badges' => [
            'label' => 'Badge Awards',
            'file' => 'sql/setup-user-badges-table.sql',
            'table' => 'live_user_badges',
            'sql' => file_get_contents(__DIR__ . '/../sql/setup-user-badges-table.sql') ?: '',
            'description' => 'Stores permanently awarded player badges and notification state.',
        ],
        'match_results' => [
            'label' => 'Match Results',
            'file' => 'sql/setup-match-results-table.sql',
            'table' => 'live_match_results',
            'sql' => build_match_results_create_sql($totalScoreColumns),
            'description' => 'Stores home and away result values across all ' . $totalMatches . ' fixtures (' . $totalScoreColumns . ' score columns).',
        ],
        'predictions_groups' => [
            'label' => 'Group Predictions',
            'file' => 'sql/setup-user-predicitions-table-groups.sql',
            'table' => 'live_user_predictions_groups',
            'sql' => build_prediction_table_create_sql('live_user_predictions_groups', 1, $groupScoreCount),
            'description' => 'Prediction score slots for group fixtures 1-' . $groupCount . ' (' . $groupScoreCount . ' values).',
        ],
        'backup_predictions_groups' => [
            'label' => 'Backup Group Predictions',
            'file' => '(generated)',
            'table' => 'backup_user_predictions_groups',
            'sql' => build_prediction_table_create_sql('backup_user_predictions_groups', 1, $groupScoreCount),
            'description' => 'Preserves each player\'s original group-stage submission before later edits.',
        ],
        'predictions_ro32' => [
            'label' => 'Round of 32 Predictions',
            'file' => 'sql/setup-user-predicitions-table-ro32.sql',
            'table' => 'live_user_predictions_ro32',
            'sql' => build_prediction_table_create_sql('live_user_predictions_ro32', $ro32Start, $ro32ScoreCount),
            'description' => 'Prediction score slots for Round of 32 match scores ' . $ro32Start . '-' . ($ro32Start + $ro32ScoreCount - 1) . '.',
        ],
        'backup_predictions_ro32' => [
            'label' => 'Backup Round of 32 Predictions',
            'file' => '(generated)',
            'table' => 'backup_user_predictions_ro32',
            'sql' => build_prediction_table_create_sql('backup_user_predictions_ro32', $ro32Start, $ro32ScoreCount),
            'description' => 'Preserves each player\'s original Round of 32 submission before later edits.',
        ],
        'predictions_ro16' => [
            'label' => 'Round of 16 Predictions',
            'file' => 'sql/setup-user-predicitions-table-ro16.sql',
            'table' => 'live_user_predictions_ro16',
            'sql' => build_prediction_table_create_sql('live_user_predictions_ro16', $ro16Start, $ro16ScoreCount),
            'description' => 'Prediction score slots for Round of 16 match scores ' . $ro16Start . '-' . ($ro16Start + $ro16ScoreCount - 1) . '.',
        ],
        'backup_predictions_ro16' => [
            'label' => 'Backup Round of 16 Predictions',
            'file' => '(generated)',
            'table' => 'backup_user_predictions_ro16',
            'sql' => build_prediction_table_create_sql('backup_user_predictions_ro16', $ro16Start, $ro16ScoreCount),
            'description' => 'Preserves each player\'s original Round of 16 submission before later edits.',
        ],
        'predictions_qf' => [
            'label' => 'Quarter-Final Predictions',
            'file' => 'sql/setup-user-predicitions-table-qf.sql',
            'table' => 'live_user_predictions_qf',
            'sql' => build_prediction_table_create_sql('live_user_predictions_qf', $qfStart, $qfScoreCount),
            'description' => 'Prediction score slots for quarter-final match scores ' . $qfStart . '-' . ($qfStart + $qfScoreCount - 1) . '.',
        ],
        'backup_predictions_qf' => [
            'label' => 'Backup Quarter-Final Predictions',
            'file' => '(generated)',
            'table' => 'backup_user_predictions_qf',
            'sql' => build_prediction_table_create_sql('backup_user_predictions_qf', $qfStart, $qfScoreCount),
            'description' => 'Preserves each player\'s original quarter-final submission before later edits.',
        ],
        'predictions_sf' => [
            'label' => 'Semi-Final Predictions',
            'file' => 'sql/setup-user-predicitions-table-sf.sql',
            'table' => 'live_user_predictions_sf',
            'sql' => build_prediction_table_create_sql('live_user_predictions_sf', $sfStart, $sfScoreCount),
            'description' => 'Prediction score slots for semi-final match scores ' . $sfStart . '-' . ($sfStart + $sfScoreCount - 1) . '.',
        ],
        'backup_predictions_sf' => [
            'label' => 'Backup Semi-Final Predictions',
            'file' => '(generated)',
            'table' => 'backup_user_predictions_sf',
            'sql' => build_prediction_table_create_sql('backup_user_predictions_sf', $sfStart, $sfScoreCount),
            'description' => 'Preserves each player\'s original semi-final submission before later edits.',
        ],
        'predictions_final' => [
            'label' => 'Final Stage Predictions',
            'file' => 'sql/setup-user-predicitions-table-final.sql',
            'table' => 'live_user_predictions_final',
            'sql' => build_prediction_table_create_sql('live_user_predictions_final', $finalStart, $finalScoreCount),
            'description' => 'Prediction score slots for final-stage match scores ' . $finalStart . '-' . ($finalStart + $finalScoreCount - 1) . '.',
        ],
        'backup_predictions_final' => [
            'label' => 'Backup Final Stage Predictions',
            'file' => '(generated)',
            'table' => 'backup_user_predictions_final',
            'sql' => build_prediction_table_create_sql('backup_user_predictions_final', $finalStart, $finalScoreCount),
            'description' => 'Preserves each player\'s original final-stage submission before later edits.',
        ],
    ];
}

function build_prediction_stage_contexts(array $context): array {
    $groupCount = max(0, (int) ($context['group_fixture_count'] ?? 0));
    $ro32Count = max(0, (int) ($context['round_of_32_count'] ?? 0));
    $ro16Count = max(0, (int) ($context['round_of_16_count'] ?? 0));
    $qfCount = max(0, (int) ($context['quarter_final_count'] ?? 0));
    $sfCount = max(0, (int) ($context['semi_final_count'] ?? 0));
    $finalCount = max(0, (int) ($context['final_count'] ?? 0));

    $groupScores = $groupCount * 2;
    $ro32Scores = $ro32Count * 2;
    $ro16Scores = $ro16Count * 2;
    $qfScores = $qfCount * 2;
    $sfScores = $sfCount * 2;
    $finalScores = $finalCount * 2;

    $ro32Start = $groupScores + 1;
    $ro16Start = $ro32Start + $ro32Scores;
    $qfStart = $ro16Start + $ro16Scores;
    $sfStart = $qfStart + $qfScores;
    $finalStart = $sfStart + $sfScores;

    return [
        ['table' => 'live_user_predictions_groups', 'start' => 1, 'count' => $groupScores],
        ['table' => 'live_user_predictions_ro32', 'start' => $ro32Start, 'count' => $ro32Scores],
        ['table' => 'live_user_predictions_ro16', 'start' => $ro16Start, 'count' => $ro16Scores],
        ['table' => 'live_user_predictions_qf', 'start' => $qfStart, 'count' => $qfScores],
        ['table' => 'live_user_predictions_sf', 'start' => $sfStart, 'count' => $sfScores],
        ['table' => 'live_user_predictions_final', 'start' => $finalStart, 'count' => $finalScores],
    ];
}

function build_dummy_users(array $footballKits): array {
    $users = [
        ['username' => 'james', 'firstname' => 'James', 'surname' => 'Henderson', 'email' => 'james@example.com', 'faveteam' => 'England', 'winner' => 'Brazil', 'paid' => 'Yes', 'work' => 'Admin', 'location' => 'Nottingham'],
        ['username' => 'amy', 'firstname' => 'Amy', 'surname' => 'Carter', 'email' => 'amy@example.com', 'faveteam' => 'United States', 'winner' => 'Spain', 'paid' => 'Yes', 'work' => 'Teacher', 'location' => 'Leeds'],
        ['username' => 'matt', 'firstname' => 'Matt', 'surname' => 'Shaw', 'email' => 'matt@example.com', 'faveteam' => 'Mexico', 'winner' => 'Argentina', 'paid' => 'No', 'work' => 'Engineer', 'location' => 'Derby'],
        ['username' => 'sarah', 'firstname' => 'Sarah', 'surname' => 'Bell', 'email' => 'sarah@example.com', 'faveteam' => 'Canada', 'winner' => 'France', 'paid' => 'Yes', 'work' => 'Designer', 'location' => 'Sheffield'],
        ['username' => 'lee', 'firstname' => 'Lee', 'surname' => 'Mitchell', 'email' => 'lee@example.com', 'faveteam' => 'Germany', 'winner' => 'England', 'paid' => 'Yes', 'work' => 'Analyst', 'location' => 'York'],
        ['username' => 'nina', 'firstname' => 'Nina', 'surname' => 'Patel', 'email' => 'nina@example.com', 'faveteam' => 'Portugal', 'winner' => 'Portugal', 'paid' => 'No', 'work' => 'Doctor', 'location' => 'London'],
        ['username' => 'tom', 'firstname' => 'Tom', 'surname' => 'Evans', 'email' => 'tom@example.com', 'faveteam' => 'Mexico', 'winner' => 'Germany', 'paid' => 'Yes', 'work' => 'Consultant', 'location' => 'Bristol'],
        ['username' => 'zoe', 'firstname' => 'Zoe', 'surname' => 'Morgan', 'email' => 'zoe@example.com', 'faveteam' => 'Brazil', 'winner' => 'Brazil', 'paid' => 'Yes', 'work' => 'Nurse', 'location' => 'Manchester'],
    ];

    foreach ($users as $index => $user) {
        $users[$index]['avatar'] = $footballKits[$index % max(1, count($footballKits))] ?? 'img/hh-icon-2024.png';
    }

    return $users;
}

function execute_multi_sql_statements(mysqli $connection, string $sql): void {
    $statements = array_filter(array_map('trim', preg_split('/;\s*(?:\R|$)/', $sql) ?: []));
    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }
        if (!mysqli_query($connection, $statement)) {
            throw new RuntimeException(mysqli_error($connection));
        }
    }
}

function ensure_fanzone_posts_schema(mysqli $connection): void {
    $createSql = file_get_contents(__DIR__ . '/../sql/setup-fanzone-board-table.sql') ?: '';
    if ($createSql === '') {
        throw new RuntimeException('The Fan Zone setup SQL could not be loaded.');
    }

    execute_multi_sql_statements($connection, $createSql);

    $existingColumns = [];
    $columnResult = mysqli_query($connection, "SHOW COLUMNS FROM live_fanzone_posts");
    if ($columnResult instanceof mysqli_result) {
        while ($column = mysqli_fetch_assoc($columnResult)) {
            $existingColumns[] = (string) ($column['Field'] ?? '');
        }
        mysqli_free_result($columnResult);
    }

    $alterStatements = [];
    if (!in_array('is_pinned', $existingColumns, true)) {
        $alterStatements[] = "ADD COLUMN is_pinned TINYINT(1) NOT NULL DEFAULT 0 AFTER is_deleted";
    }
    if (!in_array('is_announcement', $existingColumns, true)) {
        $alterStatements[] = "ADD COLUMN is_announcement TINYINT(1) NOT NULL DEFAULT 0 AFTER is_pinned";
    }

    if (!empty($alterStatements)) {
        $alterSql = "ALTER TABLE live_fanzone_posts\n    " . implode(",\n    ", $alterStatements);
        if (!mysqli_query($connection, $alterSql)) {
            throw new RuntimeException(mysqli_error($connection));
        }
    }
}

function create_installable_tables(mysqli $connection, array $installableTables): void {
    foreach ($installableTables as $definition) {
        execute_multi_sql_statements($connection, $definition['sql']);
    }
}

function rebuild_installable_tables(mysqli $connection, array $installableTables): void {
    if (empty($installableTables)) {
        return;
    }

    mysqli_query($connection, 'SET FOREIGN_KEY_CHECKS = 0');

    try {
        $dropOrder = array_reverse(array_values($installableTables));
        foreach ($dropOrder as $definition) {
            $tableName = $definition['table'] ?? '';
            if ($tableName === '') {
                continue;
            }

            if (!mysqli_query($connection, 'DROP TABLE IF EXISTS ' . mysql_quote_identifier($tableName))) {
                throw new RuntimeException('Failed to rebuild ' . $tableName . ': ' . mysqli_error($connection));
            }
        }
    } finally {
        mysqli_query($connection, 'SET FOREIGN_KEY_CHECKS = 1');
    }

    create_installable_tables($connection, $installableTables);
}

function hh_setup_bind_stmt_values(mysqli_stmt $statement, string $types, array $values): void {
    $refs = [];
    foreach ($values as $index => $value) {
        $refs[$index] = &$values[$index];
    }

    array_unshift($refs, $types);
    call_user_func_array([$statement, 'bind_param'], $refs);
}

function seed_dummy_user_information(mysqli $connection, array $users): void {
    mysqli_query($connection, "DELETE FROM live_temp_information");
    mysqli_query($connection, "DELETE FROM live_user_information");

    $statement = mysqli_prepare(
        $connection,
        "INSERT INTO live_user_information (id, username, password, firstname, surname, email, avatar, fieldofwork, location, faveteam, tournwinner, startpos, lastpos, currpos, haspaid)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$statement) {
        throw new RuntimeException(mysqli_error($connection));
    }

    foreach ($users as $index => $user) {
        $id = $index + 1;
        $password = md5('demo123');
        $startPos = $id;
        $lastPos = $id;
        $currPos = $id;
        mysqli_stmt_bind_param(
            $statement,
            'issssssssssiiis',
            $id,
            $user['username'],
            $password,
            $user['firstname'],
            $user['surname'],
            $user['email'],
            $user['avatar'],
            $user['work'],
            $user['location'],
            $user['faveteam'],
            $user['winner'],
            $startPos,
            $lastPos,
            $currPos,
            $user['paid']
        );
        mysqli_stmt_execute($statement);
    }

    mysqli_stmt_close($statement);
}

function seed_dummy_prediction_table(mysqli $connection, string $tableName, int $startScoreIndex, int $scoreColumnCount, array $users, array $resultsByMatch): void {
    mysqli_query($connection, "DELETE FROM {$tableName}");

    if ($scoreColumnCount <= 0) {
        return;
    }

    $columns = ['id', 'username', 'firstname', 'surname'];
    $placeholders = ['?', '?', '?', '?'];
    $types = 'isss';

    for ($scoreIndex = $startScoreIndex; $scoreIndex < $startScoreIndex + $scoreColumnCount; $scoreIndex++) {
        $columns[] = "score{$scoreIndex}_p";
        $placeholders[] = '?';
        $types .= 'i';
    }

    $columns[] = 'lastupdate';
    $placeholders[] = 'NOW()';

    $sql = "INSERT INTO {$tableName} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $statement = mysqli_prepare($connection, $sql);
    if (!$statement) {
        throw new RuntimeException(mysqli_error($connection));
    }

    foreach ($users as $index => $user) {
        $rowValues = [$index + 1, $user['username'], $user['firstname'], $user['surname']];
        for ($scoreIndex = $startScoreIndex; $scoreIndex < $startScoreIndex + $scoreColumnCount; $scoreIndex++) {
            $matchNumber = (int) ceil($scoreIndex / 2);
            $baseScore = ($scoreIndex % 2 === 1)
                ? ($resultsByMatch[$matchNumber]['home'] ?? 0)
                : ($resultsByMatch[$matchNumber]['away'] ?? 0);

            $adjustment = (($index + $scoreIndex) % 3) - 1;
            $rowValues[] = max(0, min(6, $baseScore + $adjustment));
        }

        hh_setup_bind_stmt_values($statement, $types, $rowValues);
        mysqli_stmt_execute($statement);
    }

    mysqli_stmt_close($statement);
}

function seed_dummy_match_results(mysqli $connection, int $totalMatches): array {
    mysqli_query($connection, "DELETE FROM live_match_results");

    $resultsByMatch = [];
    $columns = [];
    $placeholders = [];
    $types = '';
    $values = [];

    for ($matchNumber = 1; $matchNumber <= $totalMatches; $matchNumber++) {
        $home = ($matchNumber + 1) % 4;
        $away = ($matchNumber * 2) % 3;
        $resultsByMatch[$matchNumber] = ['home' => $home, 'away' => $away];

        $homeIndex = ($matchNumber * 2) - 1;
        $awayIndex = $matchNumber * 2;
        $columns[] = "score{$homeIndex}_r";
        $columns[] = "score{$awayIndex}_r";
        $placeholders[] = '?';
        $placeholders[] = '?';
        $types .= 'ii';
        $values[] = $home;
        $values[] = $away;
    }

    $statement = mysqli_prepare(
        $connection,
        "INSERT INTO live_match_results (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")"
    );
    if (!$statement) {
        throw new RuntimeException(mysqli_error($connection));
    }

    hh_setup_bind_stmt_values($statement, $types, $values);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);

    return $resultsByMatch;
}

function seed_dummy_schedule_scores(mysqli $connection, array $resultsByMatch): void {
    $statement = mysqli_prepare($connection, "UPDATE live_match_schedule SET homescore = ?, awayscore = ? WHERE match_number = ?");
    if (!$statement) {
        throw new RuntimeException(mysqli_error($connection));
    }

    foreach ($resultsByMatch as $matchNumber => $scores) {
        $home = (int) $scores['home'];
        $away = (int) $scores['away'];
        $match = (int) $matchNumber;
        mysqli_stmt_bind_param($statement, 'iii', $home, $away, $match);
        mysqli_stmt_execute($statement);
    }

    mysqli_stmt_close($statement);
}

function seed_dummy_group_standings(mysqli $connection, array $groupPreview): void {
    mysqli_query($connection, "DELETE FROM live_group_standings");

    $statement = mysqli_prepare(
        $connection,
        "INSERT INTO live_group_standings (group_name, team_name, team_img, played, won, drawn, lost, goals_for, goals_against, points, goal_difference)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$statement) {
        throw new RuntimeException(mysqli_error($connection));
    }

    foreach ($groupPreview as $groupName => $teams) {
        foreach (array_values($teams) as $index => $team) {
            $played = 3;
            $won = max(0, 3 - $index);
            $drawn = $index % 2;
            $lost = max(0, $played - $won - $drawn);
            $goalsFor = max(0, 7 - $index);
            $goalsAgainst = max(0, 2 + $index);
            $points = ($won * 3) + $drawn;
            $goalDifference = $goalsFor - $goalsAgainst;

            mysqli_stmt_bind_param(
                $statement,
                'sssiiiiiiii',
                $groupName,
                $team['name'],
                $team['flag'],
                $played,
                $won,
                $drawn,
                $lost,
                $goalsFor,
                $goalsAgainst,
                $points,
                $goalDifference
            );
            mysqli_stmt_execute($statement);
        }
    }

    mysqli_stmt_close($statement);
}

function seed_dummy_fanzone_posts(mysqli $connection, array $users): void {
    mysqli_query($connection, "DELETE FROM live_fanzone_posts");

    $announcement = mysqli_prepare(
        $connection,
        "INSERT INTO live_fanzone_posts (parent_id, username, display_name, message_body, is_deleted, is_pinned, is_announcement)
         VALUES (NULL, ?, ?, ?, 0, 1, 1)"
    );
    $thread = mysqli_prepare(
        $connection,
        "INSERT INTO live_fanzone_posts (parent_id, username, display_name, message_body, is_deleted, is_pinned, is_announcement)
         VALUES (NULL, ?, ?, ?, 0, 0, 0)"
    );
    $reply = mysqli_prepare(
        $connection,
        "INSERT INTO live_fanzone_posts (parent_id, username, display_name, message_body, is_deleted, is_pinned, is_announcement)
         VALUES (?, ?, ?, ?, 0, 0, 0)"
    );

    if (!$announcement || !$thread || !$reply) {
        throw new RuntimeException(mysqli_error($connection));
    }

    $adminUser = $users[0];
    $adminName = $adminUser['firstname'] . ' ' . $adminUser['surname'];
    $adminMessage = 'Welcome to the Fan Zone. This is a demo announcement so you can see pinned admin updates and how replies sit underneath them.';
    mysqli_stmt_bind_param($announcement, 'sss', $adminUser['username'], $adminName, $adminMessage);
    mysqli_stmt_execute($announcement);

    $threadBodies = [
        ['body' => 'Opening night prediction: chaos, drama, and at least one wild 3-2 somewhere.', 'reply' => 'I am backing two draws and one absolute shock result.'],
        ['body' => 'Which host nation is everyone secretly adopting for the summer?', 'reply' => 'Mexico at home for the opener feels hard to ignore.'],
        ['body' => 'This is the sort of message board thread that makes the site feel alive before a ball is kicked.', 'reply' => 'Exactly. Even a small bit of chat changes the mood of the whole game.'],
    ];

    foreach ($threadBodies as $index => $threadBody) {
        $author = $users[($index + 1) % count($users)];
        $authorName = $author['firstname'] . ' ' . $author['surname'];
        mysqli_stmt_bind_param($thread, 'sss', $author['username'], $authorName, $threadBody['body']);
        mysqli_stmt_execute($thread);
        $threadId = (int) mysqli_insert_id($connection);

        $replyAuthor = $users[($index + 2) % count($users)];
        $replyName = $replyAuthor['firstname'] . ' ' . $replyAuthor['surname'];
        $replyParent = $threadId;
        mysqli_stmt_bind_param($reply, 'isss', $replyParent, $replyAuthor['username'], $replyName, $threadBody['reply']);
        mysqli_stmt_execute($reply);
    }

    mysqli_stmt_close($announcement);
    mysqli_stmt_close($thread);
    mysqli_stmt_close($reply);
}

function seed_dummy_poll_data(mysqli $connection, array $users): void {
    mysqli_query($connection, "DELETE FROM live_poll_votes");
    mysqli_query($connection, "DELETE FROM live_poll_options");
    mysqli_query($connection, "DELETE FROM live_polls");

    $creatorId = (int) ($users[0]['id'] ?? 1);
    $pollStmt = mysqli_prepare($connection, "INSERT INTO live_polls (question, created_by, is_active) VALUES (?, ?, 1)");
    if (!$pollStmt) {
        throw new RuntimeException(mysqli_error($connection));
    }

    $question = 'Which host nation do you think will go the furthest?';
    mysqli_stmt_bind_param($pollStmt, 'si', $question, $creatorId);
    mysqli_stmt_execute($pollStmt);
    $pollId = (int) mysqli_insert_id($connection);
    mysqli_stmt_close($pollStmt);

    if ($pollId <= 0) {
        return;
    }

    $optionStmt = mysqli_prepare($connection, "INSERT INTO live_poll_options (poll_id, option_label, sort_order) VALUES (?, ?, ?)");
    if (!$optionStmt) {
        throw new RuntimeException(mysqli_error($connection));
    }

    $options = [
        1 => 'Canada',
        2 => 'Mexico',
        3 => 'United States',
    ];
    $optionIds = [];

    foreach ($options as $sortOrder => $label) {
        mysqli_stmt_bind_param($optionStmt, 'isi', $pollId, $label, $sortOrder);
        mysqli_stmt_execute($optionStmt);
        $optionIds[$sortOrder] = (int) mysqli_insert_id($connection);
    }
    mysqli_stmt_close($optionStmt);

    $voteStmt = mysqli_prepare($connection, "INSERT INTO live_poll_votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
    if (!$voteStmt) {
        throw new RuntimeException(mysqli_error($connection));
    }

    $voteMap = [
        1 => $optionIds[3] ?? null,
        2 => $optionIds[3] ?? null,
        3 => $optionIds[2] ?? null,
        4 => $optionIds[1] ?? null,
        5 => $optionIds[3] ?? null,
        6 => $optionIds[2] ?? null,
    ];

    foreach ($voteMap as $userId => $optionId) {
        if ($optionId === null) {
            continue;
        }
        mysqli_stmt_bind_param($voteStmt, 'iii', $pollId, $optionId, $userId);
        mysqli_stmt_execute($voteStmt);
    }

    mysqli_stmt_close($voteStmt);
}

function seed_dummy_game(mysqli $connection, array $tableInstallContext, array $groupPreview, array $footballKits): void {
    $users = build_dummy_users($footballKits);
    $totalMatches = max(0, (int) ($tableInstallContext['total_matches'] ?? 0));

    seed_dummy_user_information($connection, $users);
    $resultsByMatch = seed_dummy_match_results($connection, $totalMatches);
    seed_dummy_schedule_scores($connection, $resultsByMatch);
    seed_dummy_group_standings($connection, $groupPreview);
    seed_dummy_fanzone_posts($connection, $users);
    seed_dummy_poll_data($connection, $users);

    foreach (build_prediction_stage_contexts($tableInstallContext) as $stageDefinition) {
        seed_dummy_prediction_table(
            $connection,
            $stageDefinition['table'],
            $stageDefinition['start'],
            $stageDefinition['count'],
            $users,
            $resultsByMatch
        );
    }

    hh_recalculate_all_prediction_points($connection);
}

function render_flag_preview_cell(string $flagPath, string $teamName): string {
    if ($flagPath === '') {
        return '<span class="text-muted small">No flag mapped</span>';
    }

    $safePath = htmlspecialchars(hh_normalize_flag_src($flagPath, '../'), ENT_QUOTES);
    $safeLabel = htmlspecialchars($teamName, ENT_QUOTES);
    $safeCode = htmlspecialchars($flagPath, ENT_QUOTES);

    return '<div class="d-flex align-items-center gap-2">'
        . '<span class="flag-square"><img src="' . $safePath . '" alt="' . $safeLabel . ' flag" width="24" height="24"></span>'
        . '<span class="d-flex flex-column"><code class="small mb-0">' . $safeCode . '</code></span>'
        . '</div>';
}

function mysql_try_connect(string $server, string $username, string $password, ?string $database = null): array {
    mysqli_report(MYSQLI_REPORT_OFF);

    $connection = @mysqli_connect($server, $username, $password, $database ?? '');
    if (!$connection) {
        return ['ok' => false, 'connection' => null, 'message' => mysqli_connect_error()];
    }

    return ['ok' => true, 'connection' => $connection, 'message' => 'Connected'];
}

function inspect_target_database(mysqli $connection, string $databaseName): array {
    $exists = false;
    $tables = [];

    $safeDatabase = mysqli_real_escape_string($connection, $databaseName);
    $databaseResult = mysqli_query($connection, "SHOW DATABASES LIKE '{$safeDatabase}'");
    if ($databaseResult) {
        $exists = mysqli_num_rows($databaseResult) > 0;
        mysqli_free_result($databaseResult);
    }

    if ($exists) {
        $tableResult = mysqli_query($connection, 'SHOW TABLES FROM ' . mysql_quote_identifier($databaseName));
        if ($tableResult) {
            while ($row = mysqli_fetch_row($tableResult)) {
                if (!empty($row[0])) {
                    $tables[] = $row[0];
                }
            }
            mysqli_free_result($tableResult);
        }
    }

    return [
        'exists' => $exists,
        'tables' => $tables,
    ];
}

function fetch_table_row_count(mysqli $connection, string $tableName): ?int {
    $result = mysqli_query($connection, 'SELECT COUNT(*) AS total FROM ' . mysql_quote_identifier($tableName));
    if (!($result instanceof mysqli_result)) {
        return null;
    }

    $row = mysqli_fetch_assoc($result) ?: [];
    mysqli_free_result($result);

    return isset($row['total']) ? (int) $row['total'] : null;
}

function build_database_contents_snapshot(mysqli $connection, array $installableTables = []): array {
    $snapshot = [];
    $knownTables = [
        'tournament_config' => 'Tournament Config',
        'live_match_schedule' => 'Match Schedule',
        'live_user_information' => 'User Information',
        'live_temp_information' => 'Temporary Passwords',
        'live_group_standings' => 'Group Standings',
        'live_user_minileague' => 'Mini-League Links',
        'live_fanzone_posts' => 'Fan Zone Message Board',
        'live_polls' => 'Poll Questions',
        'live_poll_options' => 'Poll Options',
        'live_poll_votes' => 'Poll Votes',
        'live_user_badges' => 'Badge Awards',
        'live_match_results' => 'Match Results',
        'live_user_predictions_groups' => 'Group Predictions',
        'backup_user_predictions_groups' => 'Backup Group Predictions',
        'live_user_predictions_ro32' => 'Round of 32 Predictions',
        'backup_user_predictions_ro32' => 'Backup Round of 32 Predictions',
        'live_user_predictions_ro16' => 'Round of 16 Predictions',
        'backup_user_predictions_ro16' => 'Backup Round of 16 Predictions',
        'live_user_predictions_qf' => 'Quarter-Final Predictions',
        'backup_user_predictions_qf' => 'Backup Quarter-Final Predictions',
        'live_user_predictions_sf' => 'Semi-Final Predictions',
        'backup_user_predictions_sf' => 'Backup Semi-Final Predictions',
        'live_user_predictions_final' => 'Final Stage Predictions',
        'backup_user_predictions_final' => 'Backup Final Stage Predictions',
    ];

    foreach ($installableTables as $definition) {
        if (!empty($definition['table']) && !empty($definition['label'])) {
            $knownTables[$definition['table']] = $definition['label'];
        }
    }

    foreach ($knownTables as $tableName => $label) {
        $columns = fetch_table_columns($connection, $tableName);
        if (empty($columns)) {
            $snapshot[] = [
                'table' => $tableName,
                'label' => $label,
                'exists' => false,
                'rows' => null,
            ];
            continue;
        }

        $snapshot[] = [
            'table' => $tableName,
            'label' => $label,
            'exists' => true,
            'rows' => fetch_table_row_count($connection, $tableName),
        ];
    }

    return $snapshot;
}

function clear_current_tournament_setup(mysqli $connection, array $installableTables): void {
    $tablesToDrop = [
        'tournament_config',
        'live_match_schedule',
        'live_user_information',
        'live_temp_information',
        'live_group_standings',
        'live_user_minileague',
        'live_fanzone_posts',
        'live_polls',
        'live_poll_options',
        'live_poll_votes',
        'live_user_badges',
        'live_match_results',
        'live_user_predictions_groups',
        'backup_user_predictions_groups',
        'live_user_predictions_ro32',
        'backup_user_predictions_ro32',
        'live_user_predictions_ro16',
        'backup_user_predictions_ro16',
        'live_user_predictions_qf',
        'backup_user_predictions_qf',
        'live_user_predictions_sf',
        'backup_user_predictions_sf',
        'live_user_predictions_final',
        'backup_user_predictions_final',
    ];
    foreach ($installableTables as $definition) {
        if (!empty($definition['table'])) {
            $tablesToDrop[] = $definition['table'];
        }
    }

    $tablesToDrop = array_values(array_unique($tablesToDrop));

    mysqli_query($connection, 'SET FOREIGN_KEY_CHECKS = 0');
    try {
        foreach ($tablesToDrop as $tableName) {
            if (!mysqli_query($connection, 'DROP TABLE IF EXISTS ' . mysql_quote_identifier($tableName))) {
                throw new RuntimeException('Failed to drop ' . $tableName . ': ' . mysqli_error($connection));
            }
        }
    } finally {
        mysqli_query($connection, 'SET FOREIGN_KEY_CHECKS = 1');
    }
}

function fetch_table_columns(mysqli $connection, string $tableName): array {
    $columns = [];
    $result = mysqli_query($connection, 'SHOW COLUMNS FROM ' . mysql_quote_identifier($tableName));
    if (!$result) {
        return $columns;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['Field'])) {
            $columns[] = $row['Field'];
        }
    }

    mysqli_free_result($result);
    return $columns;
}

function build_runtime_audit(array $tableInstallContext): array {
    global $title, $version, $base_url, $competition, $competition_location, $developer;
    global $no_of_group_fixtures, $no_of_ro32_fixtures, $no_of_ro16_fixtures, $no_of_qf_fixtures, $no_of_sf_fixtures, $no_of_final_fixtures, $no_of_total_fixtures;

    $rows = [
        ['label' => '$title', 'actual' => (string) $title, 'expected' => 'Hendy\'s Hunches branding', 'status' => 'info'],
        ['label' => '$version', 'actual' => (string) $version, 'expected' => 'Current release identifier', 'status' => 'info'],
        ['label' => '$base_url', 'actual' => (string) $base_url, 'expected' => 'Live site URL', 'status' => 'info'],
        ['label' => '$competition', 'actual' => (string) $competition, 'expected' => 'Tournament title for the current run', 'status' => 'info'],
        ['label' => '$competition_location', 'actual' => (string) $competition_location, 'expected' => 'Tournament hosts / location', 'status' => 'info'],
        ['label' => '$developer', 'actual' => (string) $developer, 'expected' => 'Admin / owner display name', 'status' => 'info'],
    ];

    $checks = [
        '$no_of_group_fixtures' => [$no_of_group_fixtures ?? null, $tableInstallContext['group_fixture_count'] ?? null],
        '$no_of_ro32_fixtures' => [$no_of_ro32_fixtures ?? null, $tableInstallContext['round_of_32_count'] ?? null],
        '$no_of_ro16_fixtures' => [$no_of_ro16_fixtures ?? null, $tableInstallContext['round_of_16_count'] ?? null],
        '$no_of_qf_fixtures' => [$no_of_qf_fixtures ?? null, $tableInstallContext['quarter_final_count'] ?? null],
        '$no_of_sf_fixtures' => [$no_of_sf_fixtures ?? null, $tableInstallContext['semi_final_count'] ?? null],
        '$no_of_final_fixtures' => [$no_of_final_fixtures ?? null, $tableInstallContext['final_count'] ?? null],
        '$no_of_total_fixtures' => [$no_of_total_fixtures ?? null, $tableInstallContext['total_matches'] ?? null],
    ];

    foreach ($checks as $label => [$actual, $expected]) {
        $rows[] = [
            'label' => $label,
            'actual' => $actual === null ? 'Not set' : (string) $actual,
            'expected' => $expected === null ? 'Not inferred yet' : (string) $expected,
            'status' => ($expected !== null && (int) $actual === (int) $expected) ? 'match' : (($expected === null) ? 'info' : 'mismatch'),
        ];
    }

    return $rows;
}

function build_table_audit(mysqli $connection, array $installableTables): array {
    $rows = [];

    $expectedTables = [
        'tournament_config' => [
            'label' => 'Tournament Config',
            'expected_columns' => 15,
            'score_columns' => 0,
        ],
        'live_match_schedule' => [
            'label' => 'Match Schedule',
            'expected_columns' => count(get_recommended_schedule_columns()) + 1,
            'score_columns' => 0,
        ],
    ];

    foreach ($installableTables as $definition) {
        $expectedSql = $definition['sql'];
        if (($definition['table'] ?? '') === 'live_fanzone_posts') {
            $expectedTables[$definition['table']] = [
                'label' => $definition['label'],
                'expected_columns' => 10,
                'score_columns' => 0,
            ];
            continue;
        }
        if (($definition['table'] ?? '') === 'live_polls') {
            $expectedTables[$definition['table']] = [
                'label' => $definition['label'],
                'expected_columns' => 6,
                'score_columns' => 0,
            ];
            continue;
        }
        if (($definition['table'] ?? '') === 'live_poll_options') {
            $expectedTables[$definition['table']] = [
                'label' => $definition['label'],
                'expected_columns' => 4,
                'score_columns' => 0,
            ];
            continue;
        }
        if (($definition['table'] ?? '') === 'live_poll_votes') {
            $expectedTables[$definition['table']] = [
                'label' => $definition['label'],
                'expected_columns' => 5,
                'score_columns' => 0,
            ];
            continue;
        }
        if (($definition['table'] ?? '') === 'live_user_minileague') {
            $expectedTables[$definition['table']] = [
                'label' => $definition['label'],
                'expected_columns' => 4,
                'score_columns' => 0,
            ];
            continue;
        }

        preg_match_all('/\n\s+(?!PRIMARY\b|KEY\b|CONSTRAINT\b)[a-zA-Z0-9_]+\s+[a-zA-Z]+/m', $expectedSql, $matches);
        preg_match_all('/score\d+_[pr]/', $expectedSql, $scoreMatches);
        $expectedTables[$definition['table']] = [
            'label' => $definition['label'],
            'expected_columns' => count($matches[0]),
            'score_columns' => count($scoreMatches[0]),
        ];
    }

    foreach ($expectedTables as $tableName => $definition) {
        $actualColumns = fetch_table_columns($connection, $tableName);
        $actualScoreColumns = array_values(array_filter($actualColumns, static fn(string $column): bool => preg_match('/^score\d+_[pr]$/', $column) === 1));
        $exists = !empty($actualColumns);
        $status = !$exists ? 'missing' : ((count($actualColumns) === $definition['expected_columns'] && count($actualScoreColumns) === $definition['score_columns']) ? 'ready' : 'mismatch');

        $rows[] = [
            'table' => $tableName,
            'label' => $definition['label'],
            'exists' => $exists,
            'expected_columns' => $definition['expected_columns'],
            'actual_columns' => count($actualColumns),
            'expected_score_columns' => $definition['score_columns'],
            'actual_score_columns' => count($actualScoreColumns),
            'status' => $status,
        ];
    }

    return $rows;
}

function build_db_connect_template(string $server, string $database, string $username, string $password): string {
    return "<?php\n"
        . "\t// Setup global variables\n"
        . "\t\$dbusername = " . mysql_quote_string($username) . ";\n"
        . "\t\$dbpassword = " . mysql_quote_string($password) . ";\n"
        . "\t\$database = " . mysql_quote_string($database) . ";\n"
        . "\t\$server = " . mysql_quote_string($server) . ";\n\n"
        . "\t// Create DB connection\n"
        . "\t\$con = mysqli_connect(\$server, \$dbusername, \$dbpassword, \$database);\n\n"
        . "\t// Check connection\n"
        . "\tif (mysqli_connect_errno()) {\n"
        . "\t\techo \"Failed to connect to MySQL: \" . mysqli_connect_error();\n"
        . "\t}\n"
        . "?>\n";
}

function ensure_mysql_user_access(mysqli $connection, string $databaseName, string $username, string $host, string $password): ?string {
    $createUser = 'CREATE USER IF NOT EXISTS ' . mysql_quote_string($username) . '@' . mysql_quote_string($host)
        . ' IDENTIFIED BY ' . mysql_quote_string($password);

    if (!mysqli_query($connection, $createUser)) {
        $errorMessage = mysqli_error($connection);
        if (stripos($errorMessage, 'syntax') !== false) {
            $legacyCreate = 'CREATE USER ' . mysql_quote_string($username) . '@' . mysql_quote_string($host)
                . ' IDENTIFIED BY ' . mysql_quote_string($password);
            if (!mysqli_query($connection, $legacyCreate)) {
                $legacyError = mysqli_error($connection);
                if (stripos($legacyError, 'exists') === false) {
                    return $legacyError;
                }
            }
        } elseif (stripos($errorMessage, 'exists') === false) {
            return $errorMessage;
        }
    }

    $grantSql = 'GRANT ALL PRIVILEGES ON ' . mysql_quote_identifier($databaseName) . '.* TO '
        . mysql_quote_string($username) . '@' . mysql_quote_string($host);

    if (!mysqli_query($connection, $grantSql)) {
        return mysqli_error($connection);
    }

    if (!mysqli_query($connection, 'FLUSH PRIVILEGES')) {
        return mysqli_error($connection);
    }

    return null;
}

function write_generated_db_connect(string $content): array {
    $path = dirname(__DIR__) . '/php/db-connect.php';
    $written = @file_put_contents($path, $content);

    if ($written === false) {
        return ['ok' => false, 'path' => $path, 'message' => 'The generated db-connect.php file could not be written.'];
    }

    return ['ok' => true, 'path' => $path, 'message' => 'The generated db-connect.php file was written successfully.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $wizard_action === 'install_setup_table') {
    $mysqlAdminServer = trim($_POST['mysql_admin_server'] ?? 'localhost');
    $mysqlAdminUser = trim($_POST['mysql_admin_user'] ?? 'hh_admin');
    $mysqlAdminPassword = trim($_POST['mysql_admin_password'] ?? '');
    $targetDbName = trim($_POST['target_db_name'] ?? '');
    $tableKey = trim($_POST['install_table_key'] ?? '');

    $table_install_context = read_table_install_context_from_request();
    $installableTables = build_installable_table_definitions($table_install_context);

    if ($mysqlAdminServer === '' || $mysqlAdminUser === '' || $targetDbName === '') {
        $errors[] = 'MySQL server, admin user and target database are required before installing tables.';
    } elseif (!isset($installableTables[$tableKey])) {
        $errors[] = 'The selected setup table definition is not available.';
    } else {
        $adminConnection = populate_mysql_diagnostics($mysql_diagnostics, $mysqlAdminServer, $mysqlAdminUser, $mysqlAdminPassword, $targetDbName);

        if (!$adminConnection instanceof mysqli) {
            $errors[] = 'The MySQL admin connection failed, so the table could not be created.';
        } elseif (!mysqli_select_db($adminConnection, $targetDbName)) {
            $errors[] = 'The target database could not be selected: ' . mysqli_error($adminConnection);
        } else {
            $tableDefinition = $installableTables[$tableKey];
            try {
                if ($tableKey === 'fanzone_posts') {
                    ensure_fanzone_posts_schema($adminConnection);
                } else {
                    execute_multi_sql_statements($adminConnection, $tableDefinition['sql']);
                }
                $messages[] = $tableDefinition['table'] . ' is ready in ' . $targetDbName . '.';
                $inspection = inspect_target_database($adminConnection, $targetDbName);
                $mysql_diagnostics['database_exists'] = $inspection['exists'];
                $mysql_diagnostics['tables'] = $inspection['tables'];
                $mysql_diagnostics['table_count'] = count($inspection['tables']);
            } catch (Throwable $exception) {
                $errors[] = 'Failed to create ' . $tableDefinition['table'] . ': ' . $exception->getMessage();
            }
        }

        if ($adminConnection instanceof mysqli) {
            mysqli_close($adminConnection);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $wizard_action === 'install_all_setup_tables') {
    $mysqlAdminServer = trim($_POST['mysql_admin_server'] ?? 'localhost');
    $mysqlAdminUser = trim($_POST['mysql_admin_user'] ?? 'hh_admin');
    $mysqlAdminPassword = trim($_POST['mysql_admin_password'] ?? '');
    $targetDbName = trim($_POST['target_db_name'] ?? '');

    $table_install_context = read_table_install_context_from_request();
    $installableTables = build_installable_table_definitions($table_install_context);

    if ($mysqlAdminServer === '' || $mysqlAdminUser === '' || $targetDbName === '') {
        $errors[] = 'MySQL server, admin user and target database are required before installing tables.';
    } elseif (empty($installableTables)) {
        $errors[] = 'Build the installation plan first so the wizard knows which table structures to create.';
    } else {
        $adminConnection = populate_mysql_diagnostics($mysql_diagnostics, $mysqlAdminServer, $mysqlAdminUser, $mysqlAdminPassword, $targetDbName);

        if (!$adminConnection instanceof mysqli) {
            $errors[] = 'The MySQL admin connection failed, so the remaining tables could not be created.';
        } elseif (!mysqli_select_db($adminConnection, $targetDbName)) {
            $errors[] = 'The target database could not be selected: ' . mysqli_error($adminConnection);
        } else {
            foreach ($installableTables as $tableKey => $tableDefinition) {
                try {
                    if ($tableKey === 'fanzone_posts') {
                        ensure_fanzone_posts_schema($adminConnection);
                    } else {
                        execute_multi_sql_statements($adminConnection, $tableDefinition['sql']);
                    }
                } catch (Throwable $exception) {
                    $errors[] = 'Failed to create ' . $tableDefinition['table'] . ': ' . $exception->getMessage();
                    break;
                }
            }

            if (empty($errors)) {
                $messages[] = 'All remaining setup tables are now ready in ' . $targetDbName . '.';
                $inspection = inspect_target_database($adminConnection, $targetDbName);
                $mysql_diagnostics['database_exists'] = $inspection['exists'];
                $mysql_diagnostics['tables'] = $inspection['tables'];
                $mysql_diagnostics['table_count'] = count($inspection['tables']);
            }
        }

        if ($adminConnection instanceof mysqli) {
            mysqli_close($adminConnection);
        }
    }
}

function populate_mysql_diagnostics(array &$mysql_diagnostics, string $mysqlAdminServer, string $mysqlAdminUser, string $mysqlAdminPassword, string $targetDbName): ?mysqli {
    if ($mysqlAdminServer === '' || $mysqlAdminUser === '') {
        return null;
    }

    $adminConnectionAttempt = mysql_try_connect($mysqlAdminServer, $mysqlAdminUser, $mysqlAdminPassword, null);
    if (!$adminConnectionAttempt['ok']) {
        $mysql_diagnostics['status'] = 'Failed';
        $mysql_diagnostics['message'] = $adminConnectionAttempt['message'];
        $mysql_diagnostics['server'] = $mysqlAdminServer;
        $mysql_diagnostics['target_database'] = $targetDbName;
        return null;
    }

    /** @var mysqli $connection */
    $connection = $adminConnectionAttempt['connection'];
    $mysql_diagnostics['status'] = 'Connected';
    $mysql_diagnostics['message'] = 'Admin connection successful.';
    $mysql_diagnostics['server'] = $mysqlAdminServer;
    $mysql_diagnostics['target_database'] = $targetDbName;

    if ($targetDbName !== '') {
        $inspection = inspect_target_database($connection, $targetDbName);
        $mysql_diagnostics['database_exists'] = $inspection['exists'];
        $mysql_diagnostics['tables'] = $inspection['tables'];
        $mysql_diagnostics['table_count'] = count($inspection['tables']);
        $mysql_diagnostics['message'] = $inspection['exists']
            ? 'Admin connection successful. Existing tables for the target database are listed below.'
            : 'Admin connection successful. The target database does not exist yet.';
    }

    return $connection;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $wizard_action === 'test_db_connection') {
    $mysqlAdminServer = trim($_POST['mysql_admin_server'] ?? 'localhost');
    $mysqlAdminUser = trim($_POST['mysql_admin_user'] ?? 'hh_admin');
    $mysqlAdminPassword = trim($_POST['mysql_admin_password'] ?? '');
    $targetDbName = trim($_POST['target_db_name'] ?? '');

    if ($mysqlAdminServer === '') {
        $errors[] = 'MySQL server address is required.';
    }
    if ($mysqlAdminUser === '') {
        $errors[] = 'MySQL admin account is required.';
    }

    if (empty($errors)) {
        $adminConnection = populate_mysql_diagnostics($mysql_diagnostics, $mysqlAdminServer, $mysqlAdminUser, $mysqlAdminPassword, $targetDbName);
        if ($adminConnection instanceof mysqli) {
            $inline_mysql_feedback = [
                'type' => 'success',
                'message' => 'MySQL admin connection test passed. Step 2 is ready.',
            ];
            mysqli_close($adminConnection);
        } else {
            $inline_mysql_feedback = [
                'type' => 'danger',
                'message' => 'MySQL admin connection test failed. Please check the credentials and try again.',
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($wizard_action, ['inspect_current_setup', 'reset_current_setup'], true)) {
    $mysqlAdminServer = trim($_POST['mysql_admin_server'] ?? 'localhost');
    $mysqlAdminUser = trim($_POST['mysql_admin_user'] ?? 'hh_admin');
    $mysqlAdminPassword = trim($_POST['mysql_admin_password'] ?? '');
    $targetDbName = trim($_POST['target_db_name'] ?? '');

    $table_install_context = read_table_install_context_from_request();
    $installableTables = !empty($table_install_context) ? build_installable_table_definitions($table_install_context) : [];

    if ($mysqlAdminServer === '') {
        $errors[] = 'MySQL server address is required.';
    }
    if ($mysqlAdminUser === '') {
        $errors[] = 'MySQL admin account is required.';
    }
    if ($targetDbName === '') {
        $errors[] = 'Target database name is required.';
    }

    if (empty($errors)) {
        $adminConnection = populate_mysql_diagnostics($mysql_diagnostics, $mysqlAdminServer, $mysqlAdminUser, $mysqlAdminPassword, $targetDbName);

        if (!$adminConnection instanceof mysqli) {
            $errors[] = 'The MySQL admin connection failed, so the current database could not be inspected.';
        } elseif (!$mysql_diagnostics['database_exists']) {
            $errors[] = 'The target database does not exist yet.';
        } elseif (!mysqli_select_db($adminConnection, $targetDbName)) {
            $errors[] = 'The target database could not be selected: ' . mysqli_error($adminConnection);
        } else {
            if ($wizard_action === 'reset_current_setup') {
                try {
                    clear_current_tournament_setup($adminConnection, $installableTables);
                    $messages[] = 'The current tournament setup tables were removed. The database itself and its user were left in place.';
                    $mysql_diagnostics['message'] = 'Admin connection successful. The current tournament setup was cleared.';
                } catch (Throwable $exception) {
                    $errors[] = 'The current setup could not be cleared: ' . $exception->getMessage();
                }
            }

            $inspection = inspect_target_database($adminConnection, $targetDbName);
            $mysql_diagnostics['database_exists'] = $inspection['exists'];
            $mysql_diagnostics['tables'] = $inspection['tables'];
            $mysql_diagnostics['table_count'] = count($inspection['tables']);
            $database_contents = build_database_contents_snapshot($adminConnection, $installableTables);
        }

        if ($adminConnection instanceof mysqli) {
            mysqli_close($adminConnection);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($wizard_action, ['setup_preview', 'setup_apply', 'setup_apply_and_connect', 'setup_dummy_game', 'setup_dummy_game_and_connect'], true)) {
    $siteTitle = trim($_POST['site_title'] ?? ($title ?? 'Hendy\'s Hunches'));
    $siteUrl = trim($_POST['site_url'] ?? ($base_url ?? ''));
    $storageDir = trim($_POST['storage_dir'] ?? ($backup_dir ?? '/bak'));

    $mysqlAdminServer = trim($_POST['mysql_admin_server'] ?? 'localhost');
    $mysqlAdminUser = trim($_POST['mysql_admin_user'] ?? 'hh_admin');
    $mysqlAdminPassword = trim($_POST['mysql_admin_password'] ?? '');

    $targetDbName = trim($_POST['target_db_name'] ?? '');
    $targetDbUser = trim($_POST['target_db_user'] ?? '');
    $targetDbPassword = trim($_POST['target_db_password'] ?? '');
    $targetDbUserHost = trim($_POST['target_db_user_host'] ?? 'localhost');
    $writeDbConnect = in_array($wizard_action, ['setup_apply_and_connect', 'setup_dummy_game_and_connect'], true);
    $seedDummyGame = in_array($wizard_action, ['setup_dummy_game', 'setup_dummy_game_and_connect'], true);

    $tournamentName = trim($_POST['tournament_name'] ?? '');
    $fixturesUrl = trim($_POST['fixtures_url'] ?? '');
    $truncateSchedule = isset($_POST['truncate_schedule']);
    $applyChanges = $wizard_action !== 'setup_preview';

    if ($siteTitle === '') {
        $errors[] = 'Site name is required.';
    }
    if ($siteUrl === '' || !filter_var($siteUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'A valid base URL is required.';
    }
    if ($mysqlAdminServer === '') {
        $errors[] = 'MySQL server address is required.';
    }
    if ($mysqlAdminUser === '') {
        $errors[] = 'MySQL admin account is required.';
    }
    if ($targetDbName === '') {
        $errors[] = 'Target database name is required.';
    }
    if ($targetDbUser === '') {
        $errors[] = 'Target database user is required.';
    }
    if ($targetDbUserHost === '') {
        $errors[] = 'Target database user host is required.';
    }

    [$payload, $sourceReference, $sourceType, $detectedFormat, $sourceErrors] = read_fixture_source_payload($fixturesUrl);
    $errors = array_merge($errors, $sourceErrors);

    $tournamentName = infer_tournament_name($tournamentName, $sourceReference);

    $rootPath = realpath(__DIR__ . '/..');
    $permissionTargets = [
        'Backups' => $storageDir,
        'Text lists' => $datalists_dir ?? '/text',
        'SQL exports' => $sql_dir ?? '/sql',
        'JSON data' => '/json',
        'Images' => '/img',
    ];
    foreach ($permissionTargets as $label => $relativePath) {
        $resolved = $rootPath ? resolve_directory_path($rootPath, $relativePath) : $relativePath;
        $permission_checks[] = [
            'label' => $label,
            'path' => $resolved,
            'exists' => is_dir($resolved),
            'writable' => is_writable($resolved),
        ];
    }

    $db_connect_template = build_db_connect_template($mysqlAdminServer, $targetDbName, $targetDbUser, $targetDbPassword);

    $setup_summary = [
        'Site title' => $siteTitle,
        'Base URL' => $siteUrl,
        'Backups directory' => $storageDir,
        'MySQL server' => $mysqlAdminServer,
        'MySQL admin account' => $mysqlAdminUser,
        'Target database' => $targetDbName,
        'Target app user' => $targetDbUser . '@' . $targetDbUserHost,
        'Requested action' => $seedDummyGame
            ? ($writeDbConnect ? 'Set up dummy game and connect the site' : 'Set up dummy game')
            : ($writeDbConnect ? 'Apply setup and connect the site' : ($applyChanges ? 'Apply setup to the database' : 'Preview only')),
    ];

    $fixtures = [];
    if (empty($errors)) {
        $fixtures = $detectedFormat === 'JSON' ? parse_fixture_json($payload) : parse_fixture_csv($payload);
        $fixtures = enrich_fixture_stages($fixtures);
        if (empty($fixtures)) {
            $errors[] = 'Fixture data could not be parsed. JSON should include keys like HomeTeam, AwayTeam, DateUtc, Location, Group and RoundNumber. CSV should include equivalent headings.';
        }
    }

    $adminConnection = null;
    if ($mysqlAdminServer !== '' && $mysqlAdminUser !== '') {
        $adminConnection = populate_mysql_diagnostics($mysql_diagnostics, $mysqlAdminServer, $mysqlAdminUser, $mysqlAdminPassword, $targetDbName);
        if (!$adminConnection instanceof mysqli && $applyChanges) {
            $errors[] = 'The MySQL admin connection failed, so setup changes cannot be applied.';
        }
    }

    if (empty($errors)) {
        usort($fixtures, function (array $left, array $right): int {
            return strcmp((string)$left['datetime_utc'], (string)$right['datetime_utc']);
        });

        $metadata = build_tournament_metadata($fixtures, $tournamentName, $sourceReference, $sourceType, $detectedFormat);
        $fixture_preview = array_slice($fixtures, 0, 10);
        $group_preview = build_group_preview($fixtures);
        $config_preview = build_config_preview($metadata);

        $tournament_summary = [
            'Tournament name' => $metadata['tournament_name'],
            'Source type' => $metadata['source_type'],
            'Detected format' => $metadata['detected_format'],
            'Source reference' => $metadata['source_reference'],
            'Competition start date' => $metadata['competition_start_date'],
            'Competition end date' => $metadata['competition_end_date'],
            'Total fixtures' => (string)$metadata['total_matches'],
            'Group fixtures' => (string)$metadata['group_fixture_count'],
            'Knockout fixtures' => (string)$metadata['knockout_fixture_count'],
            'Round of 32 fixtures' => (string)$metadata['round_of_32_count'],
            'Round of 16 fixtures' => (string)$metadata['round_of_16_count'],
            'Quarter-final fixtures' => (string)$metadata['quarter_final_count'],
            'Semi-final fixtures' => (string)$metadata['semi_final_count'],
            'Final stage fixtures' => (string)$metadata['final_count'],
            'Groups detected' => (string)$metadata['group_count'],
            'Teams per group' => $metadata['teams_per_group'] === '' ? 'Not detected' : $metadata['teams_per_group'],
            'Confirmed teams' => (string)$metadata['confirmed_teams'],
        ];

        $database_preview = [
            'database_name' => $targetDbName,
            'app_user' => $targetDbUser . '@' . $targetDbUserHost,
            'tables' => [
                [
                    'name' => 'tournament_config',
                    'columns' => 'tournament_name, source_type, source_reference, total_matches, group_count, teams_per_group, confirmed_teams, start_date, end_date, group_start_date, group_end_date, knockout_start_date, final_date',
                ],
                [
                    'name' => 'live_match_schedule',
                    'columns' => implode(', ', get_recommended_schedule_columns()),
                ],
            ],
            'sample_rows' => array_slice($fixtures, 0, 5),
        ];

        $table_install_context = [
            'group_fixture_count' => (int) $metadata['group_fixture_count'],
            'round_of_32_count' => (int) $metadata['round_of_32_count'],
            'round_of_16_count' => (int) $metadata['round_of_16_count'],
            'quarter_final_count' => (int) $metadata['quarter_final_count'],
            'semi_final_count' => (int) $metadata['semi_final_count'],
            'final_count' => (int) $metadata['final_count'],
            'total_matches' => (int) $metadata['total_matches'],
        ];

        $sql_output[] = 'CREATE DATABASE IF NOT EXISTS ' . mysql_quote_identifier($targetDbName) . ';';
        $sql_output[] = 'CREATE USER IF NOT EXISTS ' . mysql_quote_string($targetDbUser) . '@' . mysql_quote_string($targetDbUserHost) . ' IDENTIFIED BY ' . mysql_quote_string($targetDbPassword) . ';';
        $sql_output[] = 'GRANT ALL PRIVILEGES ON ' . mysql_quote_identifier($targetDbName) . '.* TO ' . mysql_quote_string($targetDbUser) . '@' . mysql_quote_string($targetDbUserHost) . ';';
        $sql_output[] = 'FLUSH PRIVILEGES;';
        $sql_output[] = 'USE ' . mysql_quote_identifier($targetDbName) . ';';
        $sql_output[] = build_tournament_config_create_sql();
        $sql_output[] = build_schedule_create_sql();

        if ($truncateSchedule) {
            $sql_output[] = 'TRUNCATE TABLE live_match_schedule;';
        }

        $sql_output[] = build_tournament_config_insert_sql($metadata);

        foreach ($fixtures as $fixture) {
            $sql_output[] = build_schedule_insert_sql($fixture);
        }

        if ($applyChanges) {
            if (!$adminConnection instanceof mysqli) {
                $errors[] = 'Cannot apply changes because the MySQL admin connection is unavailable.';
            } else {
                if (!mysqli_query($adminConnection, 'CREATE DATABASE IF NOT EXISTS ' . mysql_quote_identifier($targetDbName))) {
                    $errors[] = 'Failed while creating the target database: ' . mysqli_error($adminConnection);
                }

                if (empty($errors)) {
                    $userAccessError = ensure_mysql_user_access($adminConnection, $targetDbName, $targetDbUser, $targetDbUserHost, $targetDbPassword);
                    if ($userAccessError !== null) {
                        $errors[] = 'Failed while preparing the app database user: ' . $userAccessError;
                    }
                }

                if (empty($errors) && !mysqli_select_db($adminConnection, $targetDbName)) {
                    $errors[] = 'The target database could not be selected after creation: ' . mysqli_error($adminConnection);
                }

                if (empty($errors) && !mysqli_query($adminConnection, build_tournament_config_create_sql())) {
                    $errors[] = 'Failed to create tournament_config: ' . mysqli_error($adminConnection);
                }

                if (empty($errors) && !mysqli_query($adminConnection, build_schedule_create_sql())) {
                    $errors[] = 'Failed to create live_match_schedule: ' . mysqli_error($adminConnection);
                }

                if (empty($errors) && $truncateSchedule && !mysqli_query($adminConnection, 'TRUNCATE TABLE live_match_schedule')) {
                    $errors[] = 'Failed to clear live_match_schedule: ' . mysqli_error($adminConnection);
                }

                if (empty($errors)) {
                    $configInsert = rtrim(build_tournament_config_insert_sql($metadata), ';');

                    if (!mysqli_query($adminConnection, $configInsert)) {
                        $errors[] = 'Failed to insert tournament_config: ' . mysqli_error($adminConnection);
                    }
                }

                if (empty($errors)) {
                    foreach ($fixtures as $fixture) {
                        if (!mysqli_query($adminConnection, build_schedule_insert_sql($fixture))) {
                            $errors[] = 'Failed to insert fixture rows: ' . mysqli_error($adminConnection);
                            break;
                        }
                    }
                }

                if (empty($errors) && $seedDummyGame) {
                    try {
                        rebuild_installable_tables($adminConnection, build_installable_table_definitions($table_install_context));
                        seed_dummy_game($adminConnection, $table_install_context, $group_preview, $football_kits ?? []);
                        $messages[] = 'Dummy game data was created, including player accounts, predictions, results, group standings and Fan Zone posts.';
                    } catch (Throwable $exception) {
                        $errors[] = 'Dummy game setup failed: ' . $exception->getMessage();
                    }
                }

                if (empty($errors)) {
                    if ($writeDbConnect) {
                        $writeResult = write_generated_db_connect($db_connect_template);
                        if (!$writeResult['ok']) {
                            $errors[] = $writeResult['message'];
                        } else {
                            $messages[] = $writeResult['message'] . ' (' . $writeResult['path'] . ')';
                        }
                    }
                }

                if (empty($errors)) {
                    $messages[] = 'Setup SQL executed successfully against the target database.';
                    $inspection = inspect_target_database($adminConnection, $targetDbName);
                    $mysql_diagnostics['database_exists'] = $inspection['exists'];
                    $mysql_diagnostics['tables'] = $inspection['tables'];
                    $mysql_diagnostics['table_count'] = count($inspection['tables']);
                    $mysql_diagnostics['message'] = 'Admin connection successful. Setup changes were applied and the updated tables are listed below.';
                }
            }
        }
    }

    if ($adminConnection instanceof mysqli) {
        mysqli_close($adminConnection);
    }
}

$site_settings_ready = trim((string)($_POST['site_title'] ?? ($title ?? ''))) !== ''
    && trim((string)($_POST['site_url'] ?? ($base_url ?? ''))) !== '';
$database_ready = trim((string)($_POST['target_db_name'] ?? '')) !== ''
    && trim((string)($_POST['target_db_user'] ?? '')) !== ''
    && trim((string)($_POST['target_db_user_host'] ?? '')) !== '';
$source_ready = trim((string)($_POST['fixtures_url'] ?? '')) !== '' || !empty($_FILES['fixtures_file']['tmp_name']);
$review_ready = !empty($setup_summary) || !empty($tournament_summary) || !empty($sql_output);
$installable_tables = !empty($table_install_context) ? build_installable_table_definitions($table_install_context) : [];

if (!empty($table_install_context)) {
    $runtime_audit = build_runtime_audit($table_install_context);
}

if (
    $mysql_diagnostics['status'] === 'Connected'
    && ($mysql_diagnostics['target_database'] ?? '') !== ''
) {
    $auditConnection = mysql_try_connect(
        trim($_POST['mysql_admin_server'] ?? 'localhost'),
        trim($_POST['mysql_admin_user'] ?? 'hh_admin'),
        trim($_POST['mysql_admin_password'] ?? ''),
        $mysql_diagnostics['target_database']
    );

    if (!empty($auditConnection['ok']) && $auditConnection['connection'] instanceof mysqli) {
        if (!empty($installable_tables)) {
            $table_audit = build_table_audit($auditConnection['connection'], $installable_tables);
        }
        $database_contents = build_database_contents_snapshot($auditConnection['connection'], $installable_tables);
        mysqli_close($auditConnection['connection']);
    }
}

$show_site_step = $wizard_action === '' || (!$site_settings_ready && !$review_ready);
$show_mysql_step = $wizard_action === 'test_db_connection';
$show_database_step = $wizard_action === 'install_setup_table' || $wizard_action === 'install_all_setup_tables' || $review_ready;
$show_source_step = $review_ready || $source_ready || in_array($wizard_action, ['setup_preview', 'setup_apply', 'setup_apply_and_connect', 'setup_dummy_game', 'setup_dummy_game_and_connect', 'install_setup_table', 'install_all_setup_tables'], true);
$mysql_test_success = $wizard_action === 'test_db_connection' && $inline_mysql_feedback !== null && $inline_mysql_feedback['type'] === 'success';

$app_path_prefix = '../';
$app_logout_path = '../php/logout.php';
include '../php/header.php';
include '../php/navigation.php';
?>

<main class="container py-4 setup-page">
    <section class="page-hero page-hero--setup">
        <div>
            <p class="eyebrow" style="color: #FF0000 !important">System setup</p>
            <h1>Installation Manager</h1>
            <p class="lead mb-0">Test MySQL access and app database, import fixtures, and prepare the tournament.</p>
        </div>
        <div class="page-hero__actions">
            <!-- <a class="btn btn-light" href="../admin/results.php">Record Results</a> -->
            <!-- <a class="btn btn-outline-dark" href="../admin/functions.php">Open Admin Functions</a> -->
        </div>
    </section>

    <?php if (!empty($errors) && $wizard_action !== 'test_db_connection') : ?>
        <div class="alert alert-danger setup-alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error) : ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($messages) && $wizard_action !== 'test_db_connection') : ?>
        <div class="alert alert-success setup-alert">
            <ul class="mb-0">
                <?php foreach ($messages as $message) : ?>
                    <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="card setup-form-card" enctype="multipart/form-data">
        <div class="card-body">
            <div class="setup-section">
                <div class="setup-section__header">
                    <div>
                        <p class="eyebrow mb-2">Foundations</p>
                        <h2 class="h4 mb-2">Site and database setup</h2>
                        <p class="text-muted mb-0">Define the site details and use a MySQL admin account so the wizard can inspect the target environment before anything is applied.</p>
                    </div>
                </div>
            </div>

            <div class="setup-mode-bar mb-4">
                <div>
                    <p class="eyebrow mb-2">Run mode</p>
                    <h2 class="h5 mb-1">Choose what to do with this setup</h2>
                    <p class="text-muted mb-0">These actions use the values from the steps below. “Connect site” means the wizard also writes <code>php/db-connect.php</code> so the live site starts using this database straight away. Preview stays read-only.</p>
                </div>
                <div class="setup-mode-list" role="list" aria-label="Installer run modes">
                    <div class="setup-mode-item" role="listitem">
                        <div class="setup-mode-item__body">
                            <h3>Preview plan only</h3>
                            <p>Read-only check of the fixture source, inferred stage counts, SQL output, and audit panels. Nothing is written to the database or the site connection file.</p>
                        </div>
                        <div class="setup-mode-item__action">
                            <button type="submit" name="wizard_action" value="setup_preview" class="btn btn-outline-secondary">Preview only</button>
                        </div>
                    </div>
                    <div class="setup-mode-item" role="listitem">
                        <div class="setup-mode-item__body">
                            <h3>Apply setup and connect site</h3>
                            <p>Runs the setup and also writes <code>php/db-connect.php</code>, so the live site immediately starts using this target database.</p>
                        </div>
                        <div class="setup-mode-item__action">
                            <button type="submit" name="wizard_action" value="setup_apply_and_connect" class="btn btn-primary">Apply and connect</button>
                        </div>
                    </div>
                    <div class="setup-mode-item" role="listitem">
                        <div class="setup-mode-item__body">
                            <h3>Set up dummy game and connect site</h3>
                            <p>Builds the installable tables, seeds sample users, results, predictions, standings, and Fan Zone posts, then points the site at that dummy database straight away.</p>
                        </div>
                        <div class="setup-mode-item__action">
                            <button type="submit" name="wizard_action" value="setup_dummy_game_and_connect" class="btn btn-success">Dummy game and connect</button>
                        </div>
                    </div>
                    <div class="setup-mode-item setup-mode-item--muted" role="listitem">
                        <div class="setup-mode-item__body">
                            <h3>Inspect current database</h3>
                            <p>Shows which setup tables already exist and how many rows are currently in each one, without changing anything.</p>
                        </div>
                        <div class="setup-mode-item__action">
                            <button type="submit" name="wizard_action" value="inspect_current_setup" class="btn btn-outline-secondary" formnovalidate>Inspect current DB</button>
                        </div>
                    </div>
                    <div class="setup-mode-item setup-mode-item--danger" role="listitem">
                        <div class="setup-mode-item__body">
                            <h3>Clear current tournament setup</h3>
                            <p>Removes the current tournament config, schedule, standings, results, predictions, Fan Zone posts, and user info tables from this database, but leaves the database itself and its user in place.</p>
                        </div>
                        <div class="setup-mode-item__action">
                            <button type="submit" name="wizard_action" value="reset_current_setup" class="btn btn-outline-danger" formnovalidate onclick="return confirm('Clear the current tournament setup tables from this database?');">Clear current setup</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 align-items-start">
                <div class="col-xl-8">
                    <div class="accordion setup-accordion" id="setupWizardAccordion">
                        <div class="accordion-item setup-accordion__item">
                            <h2 class="accordion-header" id="setup-heading-site">
                                <button class="accordion-button<?= $review_ready ? ' collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#setup-collapse-site" aria-expanded="<?= $review_ready ? 'false' : 'true' ?>" aria-controls="setup-collapse-site">
                                    <span class="setup-step">
                                        <span class="setup-step__number">1</span>
                                        <span class="setup-step__body">
                                            <span class="setup-step__title">Site settings</span>
                                            <span id="setup-meta-site" class="setup-step__meta"><?= $site_settings_ready ? 'Ready to use' : 'Add the core site details' ?></span>
                                        </span>
                                        <span id="setup-state-site" class="setup-step__state <?= $site_settings_ready ? 'is-complete' : 'is-pending' ?>"><?= $site_settings_ready ? 'Ready' : 'Pending' ?></span>
                                    </span>
                                </button>
                            </h2>
                            <div id="setup-collapse-site" class="accordion-collapse collapse<?= $show_site_step ? ' show' : '' ?>" aria-labelledby="setup-heading-site" data-bs-parent="#setupWizardAccordion">
                                <div class="accordion-body">
                                    <div class="setup-panel">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label" for="site_title">Site name</label>
                                                <input type="text" class="form-control" id="site_title" name="site_title" value="<?= htmlspecialchars($_POST['site_title'] ?? ($title ?? 'Hendy\'s Hunches')) ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="site_url">Base URL</label>
                                                <input type="url" class="form-control" id="site_url" name="site_url" value="<?= htmlspecialchars($_POST['site_url'] ?? ($base_url ?? '')) ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="storage_dir">Backups directory</label>
                                                <input type="text" class="form-control" id="storage_dir" name="storage_dir" value="<?= htmlspecialchars($_POST['storage_dir'] ?? ($backup_dir ?? '/bak')) ?>">
                                                <p class="setup-help mb-0">e.g. <code>/bak</code></p>
                                            </div>
                                        </div>
                                        <div class="setup-panel__actions">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#setup-collapse-mysql" aria-controls="setup-collapse-mysql">Continue to MySQL</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item setup-accordion__item">
                            <h2 class="accordion-header" id="setup-heading-mysql">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#setup-collapse-mysql" aria-expanded="false" aria-controls="setup-collapse-mysql">
                                    <span class="setup-step">
                                        <span class="setup-step__number">2</span>
                                        <span class="setup-step__body">
                                            <span class="setup-step__title">MySQL admin connection</span>
                                            <span id="setup-meta-mysql" class="setup-step__meta"><?= $mysql_diagnostics['status'] === 'Connected' ? 'Connection test passed' : 'Connect and inspect the target server' ?></span>
                                        </span>
                                        <span id="setup-state-mysql" class="setup-step__state <?= $mysql_diagnostics['status'] === 'Connected' ? 'is-complete' : 'is-pending' ?>"><?= $mysql_diagnostics['status'] === 'Connected' ? 'Ready' : 'Pending' ?></span>
                                    </span>
                                </button>
                            </h2>
                            <div id="setup-collapse-mysql" class="accordion-collapse collapse<?= $show_mysql_step ? ' show' : '' ?>" aria-labelledby="setup-heading-mysql" data-bs-parent="#setupWizardAccordion">
                                <div class="accordion-body">
                                    <div class="setup-panel">
                                        <p class="text-muted small">Use your MySQL admin account here so the wizard can test the server, inspect the target database, create the app user, and confirm what already exists.</p>
                                        <?php if ($inline_mysql_feedback !== null) : ?>
                                            <div class="alert alert-<?= htmlspecialchars($inline_mysql_feedback['type']) ?> setup-inline-feedback mb-3" id="setup-inline-mysql-feedback" role="status" aria-live="polite">
                                                <?= htmlspecialchars($inline_mysql_feedback['message']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label" for="mysql_admin_server">MySQL server address</label>
                                                <input type="text" class="form-control" id="mysql_admin_server" name="mysql_admin_server" value="<?= htmlspecialchars($_POST['mysql_admin_server'] ?? 'localhost') ?>" required>
                                                <p class="setup-help mb-0">e.g. <code>localhost</code> or your hosting provider’s MySQL server name</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="mysql_admin_user">MySQL admin user</label>
                                                <input type="text" class="form-control" id="mysql_admin_user" name="mysql_admin_user" value="<?= htmlspecialchars($_POST['mysql_admin_user'] ?? 'hh_admin') ?>" required>
                                                <p class="setup-help mb-0">e.g. <code>root</code> or the admin user from your hosting panel</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="mysql_admin_password">MySQL admin password</label>
                                                <input type="password" class="form-control" id="mysql_admin_password" name="mysql_admin_password" value="<?= htmlspecialchars($_POST['mysql_admin_password'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="setup-panel__actions">
                                            <button class="btn btn-outline-secondary btn-sm" type="submit" name="wizard_action" value="test_db_connection" formnovalidate>Test DB connection</button>
                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#setup-collapse-database" aria-controls="setup-collapse-database">Continue to app database</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item setup-accordion__item">
                            <h2 class="accordion-header" id="setup-heading-database">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#setup-collapse-database" aria-expanded="false" aria-controls="setup-collapse-database">
                                    <span class="setup-step">
                                        <span class="setup-step__number">3</span>
                                        <span class="setup-step__body">
                                            <span class="setup-step__title">App database and user</span>
                                            <span id="setup-meta-database" class="setup-step__meta"><?= $database_ready ? 'Target database details captured' : 'Set the database name and app credentials' ?></span>
                                        </span>
                                        <span id="setup-state-database" class="setup-step__state <?= $database_ready ? 'is-complete' : 'is-pending' ?>"><?= $database_ready ? 'Ready' : 'Pending' ?></span>
                                    </span>
                                </button>
                            </h2>
                            <div id="setup-collapse-database" class="accordion-collapse collapse<?= $show_database_step ? ' show' : '' ?>" aria-labelledby="setup-heading-database" data-bs-parent="#setupWizardAccordion">
                                <div class="accordion-body">
                                    <div class="setup-panel">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label" for="target_db_name">Target database name</label>
                                                <input type="text" class="form-control" id="target_db_name" name="target_db_name" value="<?= htmlspecialchars($_POST['target_db_name'] ?? '') ?>" required>
                                                <p class="setup-help mb-0">e.g. <code>hh_wc2026</code></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="target_db_user">App database user</label>
                                                <input type="text" class="form-control" id="target_db_user" name="target_db_user" value="<?= htmlspecialchars($_POST['target_db_user'] ?? '') ?>" required>
                                                <p class="setup-help mb-0">e.g. <code>hh_user</code></p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="target_db_password">App database password</label>
                                                <input type="password" class="form-control" id="target_db_password" name="target_db_password" value="<?= htmlspecialchars($_POST['target_db_password'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label" for="target_db_user_host">App user host</label>
                                                <input type="text" class="form-control" id="target_db_user_host" name="target_db_user_host" value="<?= htmlspecialchars($_POST['target_db_user_host'] ?? 'localhost') ?>" required>
                                                <p class="setup-help mb-0">Usually <code>localhost</code> unless your host tells you otherwise</p>
                                            </div>
                                        </div>
                                        <div class="setup-panel__actions">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#setup-collapse-source" aria-controls="setup-collapse-source">Continue to source and build</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item setup-accordion__item">
                            <h2 class="accordion-header" id="setup-heading-source">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#setup-collapse-source" aria-expanded="false" aria-controls="setup-collapse-source">
                                    <span class="setup-step">
                                        <span class="setup-step__number">4</span>
                                        <span class="setup-step__body">
                                            <span class="setup-step__title">Tournament source and build</span>
                                            <span id="setup-meta-source" class="setup-step__meta"><?= $source_ready ? 'Fixture source supplied' : 'Choose a feed or upload a local file, then build the plan' ?></span>
                                        </span>
                                        <span id="setup-state-source" class="setup-step__state <?= $source_ready ? 'is-complete' : 'is-pending' ?>"><?= $source_ready ? 'Ready' : 'Pending' ?></span>
                                    </span>
                                </button>
                            </h2>
                            <div id="setup-collapse-source" class="accordion-collapse collapse<?= $show_source_step ? ' show' : '' ?>" aria-labelledby="setup-heading-source" data-bs-parent="#setupWizardAccordion">
                                <div class="accordion-body">
                                    <div class="setup-panel">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label" for="tournament_name">Tournament name</label>
                                                <input type="text" class="form-control" id="tournament_name" name="tournament_name" value="<?= htmlspecialchars($_POST['tournament_name'] ?? '') ?>">
                                                <p class="setup-help mb-0">Optional. Leave blank to infer it from the feed or uploaded file name.</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="fixtures_url">Feed URL</label>
                                                <input type="url" class="form-control" id="fixtures_url" name="fixtures_url" value="<?= htmlspecialchars($_POST['fixtures_url'] ?? '') ?>">
                                                <p class="setup-help mb-0">e.g. <code>https://fixturedownload.com/feed/json/fifa-world-cup-2026</code></p>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label" for="fixtures_file">Import local file</label>
                                                <input type="file" class="form-control" id="fixtures_file" name="fixtures_file" accept=".csv,.json">
                                                <p class="setup-help mb-0">Use either a feed URL or a local CSV/JSON file. You do not need both.</p>
                                            </div>
                                            <div class="col-lg-6">
                                                <h3 class="h6 mb-2">Expected JSON Shape</h3>
                                                <pre class="setup-code-block setup-code-block--compact small mb-0">[
  {
    "MatchNumber": 1,
    "RoundNumber": 1,
    "DateUtc": "2026-06-11 19:00:00Z",
    "Location": "Mexico City Stadium",
    "HomeTeam": "Mexico",
    "AwayTeam": "South Africa",
    "Group": "Group A"
  }
]</pre>
                                            </div>
                                            <div class="col-lg-6">
                                                <h3 class="h6 mb-2">Expected CSV Headings</h3>
                                                <pre class="setup-code-block setup-code-block--compact small mb-0">MatchNumber,RoundNumber,DateUtc,Location,HomeTeam,AwayTeam,Group
1,1,2026-06-11 19:00:00Z,Mexico City Stadium,Mexico,South Africa,Group A</pre>
                                            </div>
                                        </div>

                                        <div class="setup-sidebar__checks mt-4">
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" id="truncate_schedule" name="truncate_schedule" <?= isset($_POST['truncate_schedule']) ? 'checked' : '' ?>>
                                                <span class="form-check-label">Replace existing fixture schedule during import</span>
                                            </label>
                                        </div>

                                        <div class="setup-panel__actions">
                                            <p class="setup-help mb-0">When the source looks right, use the run-mode buttons above to preview, apply the setup, or spin up a seeded dummy game.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <aside class="setup-sidebar">
                        <div class="setup-sidebar__panel">
                            <p class="eyebrow mb-2">At a glance</p>
                            <h2 class="h5 mb-3">Live setup state</h2>
                            <div class="setup-review-list">
                                <div class="setup-review-item">
                                    <span>Site</span>
                                    <strong data-review="site"><?= htmlspecialchars($_POST['site_title'] ?? ($title ?? 'Hendy\'s Hunches')) ?></strong>
                                </div>
                                <div class="setup-review-item">
                                    <span>MySQL server</span>
                                    <strong data-review="mysql-server"><?= htmlspecialchars($_POST['mysql_admin_server'] ?? 'localhost') ?></strong>
                                </div>
                                <div class="setup-review-item">
                                    <span>Target database</span>
                                    <strong data-review="target-db"><?= htmlspecialchars($_POST['target_db_name'] ?? 'Not set') ?></strong>
                                </div>
                                <div class="setup-review-item">
                                    <span>App user</span>
                                    <strong data-review="app-user"><?= htmlspecialchars($_POST['target_db_user'] ?? 'Not set') ?></strong>
                                </div>
                                <div class="setup-review-item">
                                    <span>Tournament</span>
                                    <strong data-review="tournament"><?= htmlspecialchars($_POST['tournament_name'] ?? 'Will infer from source') ?></strong>
                                </div>
                                <div class="setup-review-item">
                                    <span>Fixture source</span>
                                    <strong data-review="fixture-source"><?= htmlspecialchars($_POST['fixtures_url'] ?? (!empty($_FILES['fixtures_file']['name']) ? $_FILES['fixtures_file']['name'] : 'Not supplied')) ?></strong>
                                </div>
                            </div>

                            <div class="setup-review-list">
                                <div class="setup-review-item">
                                    <span>Database status from last check</span>
                                    <strong><?= $mysql_diagnostics['database_exists'] ? 'Database found' : 'Not checked yet' ?></strong>
                                </div>
                                <div class="setup-review-item">
                                    <span>Tables already present</span>
                                    <strong><?= htmlspecialchars((string) ($mysql_diagnostics['table_count'] ?? 0)) ?></strong>
                                </div>
                                <div class="setup-review-item">
                                    <span>Connection state</span>
                                    <strong><?= htmlspecialchars($mysql_diagnostics['status']) ?></strong>
                                </div>
                                <?php if (!empty($mysql_diagnostics['tables'])) : ?>
                                    <div class="setup-review-item">
                                        <span>Existing tables</span>
                                        <strong class="setup-review-item__stack"><?= htmlspecialchars(implode(', ', $mysql_diagnostics['tables'])) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <p class="setup-help mb-0">These values update as you type. The database findings update when you test the MySQL connection or build the installation plan.</p>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">MySQL Diagnostics</h2>
                    <p class="text-muted mb-0">These diagnostics are based on the admin credentials entered above, not the site’s currently checked-in <code>php/db-connect.php</code>.</p>
                </div>
                <div>
                    <?php if ($mysql_diagnostics['status'] === 'Connected') : ?>
                        <span class="badge bg-success">Connected</span>
                    <?php elseif ($mysql_diagnostics['status'] === 'Failed') : ?>
                        <span class="badge bg-danger">Failed</span>
                    <?php else : ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars($mysql_diagnostics['status']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="setup-stat">
                        <div class="small text-muted">Server</div>
                        <div class="fw-semibold"><?= htmlspecialchars($mysql_diagnostics['server'] !== '' ? $mysql_diagnostics['server'] : 'Not tested') ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="setup-stat">
                        <div class="small text-muted">Target database</div>
                        <div class="fw-semibold"><?= htmlspecialchars($mysql_diagnostics['target_database'] !== '' ? $mysql_diagnostics['target_database'] : 'Not provided') ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="setup-stat">
                        <div class="small text-muted">Tables found</div>
                        <div class="fw-semibold"><?= htmlspecialchars((string)$mysql_diagnostics['table_count']) ?></div>
                    </div>
                </div>
            </div>

            <?php if ($mysql_diagnostics['message'] !== '') : ?>
                <div class="alert <?= $mysql_diagnostics['status'] === 'Connected' ? 'alert-success' : 'alert-secondary' ?> py-2 setup-alert setup-alert--inline">
                    <?= htmlspecialchars($mysql_diagnostics['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($mysql_diagnostics['tables'])) : ?>
                <h3 class="h6">Existing Tables In Target Database</h3>
                <div class="row g-2">
                    <?php foreach ($mysql_diagnostics['tables'] as $tableName) : ?>
                        <div class="col-sm-6 col-lg-4">
                            <div class="setup-table-pill"><code><?= htmlspecialchars($tableName) ?></code></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($setup_summary)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Setup Summary</h2>
                <div class="row g-3">
                    <?php foreach ($setup_summary as $label => $value) : ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="setup-stat">
                                <div class="small text-muted"><?= htmlspecialchars($label) ?></div>
                                <div class="fw-semibold"><?= htmlspecialchars($value) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3 class="h6 mt-4">Filesystem & Permissions</h3>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Folder</th>
                                <th>Path</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($permission_checks as $check) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($check['label']) ?></td>
                                    <td><code><?= htmlspecialchars($check['path']) ?></code></td>
                                    <td>
                                        <?php if (!$check['exists']) : ?>
                                            <span class="badge bg-danger">Missing</span>
                                        <?php elseif (!$check['writable']) : ?>
                                            <span class="badge bg-warning text-dark">Not writable</span>
                                        <?php else : ?>
                                            <span class="badge bg-success">Ready</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h3 class="h6 mt-4">db-connect.php Preview</h3>
                <p class="text-muted small">This generated file becomes the app’s live database connection if you choose to write it during apply.</p>
                <pre class="setup-code-block mb-0"><?= htmlspecialchars($db_connect_template) ?></pre>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($tournament_summary)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Tournament Summary</h2>
                <div class="row g-3">
                    <?php foreach ($tournament_summary as $label => $value) : ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="setup-stat">
                                <div class="small text-muted"><?= htmlspecialchars($label) ?></div>
                                <div class="fw-semibold"><?= htmlspecialchars($value) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($table_install_context)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Knockout Breakdown</h2>
                <p class="text-muted">This is the stage structure the installer will use for prediction tables, runtime totals and scoring ranges.</p>
                <div class="row g-3">
                    <div class="col-md-6 col-xl-4">
                        <div class="setup-stat">
                            <div class="small text-muted">Group Stage</div>
                            <div class="fw-semibold"><?= htmlspecialchars((string) ($table_install_context['group_fixture_count'] ?? 0)) ?> fixtures</div>
                        </div>
                    </div>
                    <?php foreach ([
                        'round_of_32_count' => false,
                        'round_of_16_count' => false,
                        'quarter_final_count' => false,
                        'semi_final_count' => false,
                        'final_count' => true,
                    ] as $countKey => $isFinalStage) : ?>
                        <?php $fixtureCount = (int) ($table_install_context[$countKey] ?? 0); ?>
                        <?php if ($fixtureCount > 0) : ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="setup-stat">
                                    <div class="small text-muted"><?= htmlspecialchars(hh_knockout_label_from_fixture_count($fixtureCount, $isFinalStage)) ?></div>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) $fixtureCount) ?> fixtures</div>
                                    <div class="small text-muted mt-1"><?= htmlspecialchars((string) ($fixtureCount * 2)) ?> score values</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($config_preview)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">config.php Preview</h2>
                <pre class="setup-code-block mb-0"><?php foreach ($config_preview as $key => $value) { echo htmlspecialchars($key . ' = "' . $value . '";') . "\n"; } ?></pre>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($database_preview)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Database Preview</h2>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="setup-stat">
                            <div class="small text-muted">Target database</div>
                            <div class="fw-semibold"><?= htmlspecialchars($database_preview['database_name']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="setup-stat">
                            <div class="small text-muted">App user</div>
                            <div class="fw-semibold"><?= htmlspecialchars($database_preview['app_user']) ?></div>
                        </div>
                    </div>
                    <?php foreach ($database_preview['tables'] as $table) : ?>
                        <div class="col-md-4">
                            <div class="setup-stat">
                                <div class="small text-muted">Table</div>
                                <div class="fw-semibold"><?= htmlspecialchars($table['name']) ?></div>
                                <div class="small text-muted mt-2">Columns</div>
                                <div class="small"><?= htmlspecialchars($table['columns']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3 class="h6">Sample Schedule Rows</h3>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Stage</th>
                                <th>Home Flag</th>
                                <th>Home</th>
                                <th>Away Flag</th>
                                <th>Away</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($database_preview['sample_rows'] as $fixture) : ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)$fixture['match_number']) ?></td>
                                    <td><?= htmlspecialchars($fixture['stage']) ?></td>
                                    <td><?= render_flag_preview_cell($fixture['hometeamimg'], $fixture['hometeam']) ?></td>
                                    <td><?= htmlspecialchars($fixture['hometeam']) ?></td>
                                    <td><?= render_flag_preview_cell($fixture['awayteamimg'], $fixture['awayteam']) ?></td>
                                    <td><?= htmlspecialchars($fixture['awayteam']) ?></td>
                                    <td><?= htmlspecialchars((string)$fixture['date']) ?></td>
                                    <td><?= htmlspecialchars($fixture['kotime']) ?></td>
                                    <td><?= htmlspecialchars($fixture['venue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($database_contents)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Current Database Contents</h2>
                <p class="text-muted">This shows what is already in <code><?= htmlspecialchars($mysql_diagnostics['target_database'] ?? ($_POST['target_db_name'] ?? '')) ?></code> right now.</p>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Table</th>
                                <th>Exists</th>
                                <th>Rows</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($database_contents as $row) : ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['label']) ?></div>
                                        <code class="small"><?= htmlspecialchars($row['table']) ?></code>
                                    </td>
                                    <td><?= !empty($row['exists']) ? 'Yes' : 'No' ?></td>
                                    <td><?= $row['rows'] === null ? 'N/A' : htmlspecialchars((string) $row['rows']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($runtime_audit) || !empty($table_audit)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Runtime And Table Audit</h2>
                <p class="text-muted">Use this audit to compare the loaded runtime configuration against the inferred tournament values, and to confirm the live table schemas match what the application expects.</p>

                <?php if (!empty($runtime_audit)) : ?>
                    <h3 class="h6 mt-3">Runtime Variables</h3>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Variable</th>
                                    <th>Actual runtime value</th>
                                    <th>Expected / inferred</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($runtime_audit as $row) : ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($row['label']) ?></code></td>
                                        <td><?= htmlspecialchars($row['actual']) ?></td>
                                        <td><?= htmlspecialchars($row['expected']) ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'match') : ?>
                                                <span class="badge bg-success">Match</span>
                                            <?php elseif ($row['status'] === 'mismatch') : ?>
                                                <span class="badge bg-danger">Mismatch</span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary">Review</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (!empty($table_audit)) : ?>
                    <h3 class="h6 mt-4">Table Review</h3>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Exists</th>
                                    <th>Columns</th>
                                    <th>Score columns</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($table_audit as $row) : ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($row['label']) ?></div>
                                            <code class="small"><?= htmlspecialchars($row['table']) ?></code>
                                        </td>
                                        <td><?= $row['exists'] ? 'Yes' : 'No' ?></td>
                                        <td><?= htmlspecialchars((string) $row['actual_columns']) ?> / <?= htmlspecialchars((string) $row['expected_columns']) ?></td>
                                        <td><?= htmlspecialchars((string) $row['actual_score_columns']) ?> / <?= htmlspecialchars((string) $row['expected_score_columns']) ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'ready') : ?>
                                                <span class="badge bg-success">Ready</span>
                                            <?php elseif ($row['status'] === 'missing') : ?>
                                                <span class="badge bg-danger">Missing</span>
                                            <?php else : ?>
                                                <span class="badge bg-warning text-dark">Mismatch</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($installable_tables)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Install Remaining Tables</h2>
                        <p class="text-muted mb-0">Create the remaining game tables one at a time in <code><?= htmlspecialchars($_POST['target_db_name'] ?? 'hh_wc2026') ?></code>. Prediction and results tables use the fixture counts inferred from this tournament setup.</p>
                    </div>
                    <form method="POST" class="d-flex">
                        <input type="hidden" name="wizard_action" value="install_all_setup_tables">
                        <input type="hidden" name="site_title" value="<?= htmlspecialchars($_POST['site_title'] ?? ($title ?? 'Hendy\'s Hunches')) ?>">
                        <input type="hidden" name="site_url" value="<?= htmlspecialchars($_POST['site_url'] ?? ($base_url ?? '')) ?>">
                        <input type="hidden" name="storage_dir" value="<?= htmlspecialchars($_POST['storage_dir'] ?? ($backup_dir ?? '/bak')) ?>">
                        <input type="hidden" name="mysql_admin_server" value="<?= htmlspecialchars($_POST['mysql_admin_server'] ?? 'localhost') ?>">
                        <input type="hidden" name="mysql_admin_user" value="<?= htmlspecialchars($_POST['mysql_admin_user'] ?? 'hh_admin') ?>">
                        <input type="hidden" name="mysql_admin_password" value="<?= htmlspecialchars($_POST['mysql_admin_password'] ?? '') ?>">
                        <input type="hidden" name="target_db_name" value="<?= htmlspecialchars($_POST['target_db_name'] ?? 'hh_wc2026') ?>">
                        <input type="hidden" name="target_db_user" value="<?= htmlspecialchars($_POST['target_db_user'] ?? '') ?>">
                        <input type="hidden" name="target_db_password" value="<?= htmlspecialchars($_POST['target_db_password'] ?? '') ?>">
                        <input type="hidden" name="target_db_user_host" value="<?= htmlspecialchars($_POST['target_db_user_host'] ?? 'localhost') ?>">
                        <input type="hidden" name="tournament_name" value="<?= htmlspecialchars($_POST['tournament_name'] ?? '') ?>">
                        <input type="hidden" name="fixtures_url" value="<?= htmlspecialchars($_POST['fixtures_url'] ?? '') ?>">
                        <?php if (isset($_POST['truncate_schedule'])) : ?><input type="hidden" name="truncate_schedule" value="1"><?php endif; ?>
                        <?php foreach ($table_install_context as $contextKey => $contextValue) : ?>
                            <input type="hidden" name="<?= htmlspecialchars($contextKey) ?>" value="<?= htmlspecialchars((string) $contextValue) ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary btn-sm">Create all remaining tables</button>
                    </form>
                </div>
                <div class="row g-3">
                    <?php foreach ($installable_tables as $tableKey => $definition) : ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="setup-stat h-100 d-flex flex-column gap-3">
                                <div>
                                    <h3 class="h6 mb-1"><?= htmlspecialchars($definition['label']) ?></h3>
                                    <p class="small text-muted mb-2"><?= htmlspecialchars($definition['description']) ?></p>
                                    <code class="small"><?= htmlspecialchars($definition['file']) ?></code>
                                </div>
                                <form method="POST" class="mt-auto">
                                    <input type="hidden" name="wizard_action" value="install_setup_table">
                                    <input type="hidden" name="install_table_key" value="<?= htmlspecialchars($tableKey) ?>">
                                    <input type="hidden" name="site_title" value="<?= htmlspecialchars($_POST['site_title'] ?? ($title ?? 'Hendy\'s Hunches')) ?>">
                                    <input type="hidden" name="site_url" value="<?= htmlspecialchars($_POST['site_url'] ?? ($base_url ?? '')) ?>">
                                    <input type="hidden" name="storage_dir" value="<?= htmlspecialchars($_POST['storage_dir'] ?? ($backup_dir ?? '/bak')) ?>">
                                    <input type="hidden" name="mysql_admin_server" value="<?= htmlspecialchars($_POST['mysql_admin_server'] ?? 'localhost') ?>">
                                    <input type="hidden" name="mysql_admin_user" value="<?= htmlspecialchars($_POST['mysql_admin_user'] ?? 'hh_admin') ?>">
                                    <input type="hidden" name="mysql_admin_password" value="<?= htmlspecialchars($_POST['mysql_admin_password'] ?? '') ?>">
                                    <input type="hidden" name="target_db_name" value="<?= htmlspecialchars($_POST['target_db_name'] ?? 'hh_wc2026') ?>">
                                    <input type="hidden" name="target_db_user" value="<?= htmlspecialchars($_POST['target_db_user'] ?? '') ?>">
                                    <input type="hidden" name="target_db_password" value="<?= htmlspecialchars($_POST['target_db_password'] ?? '') ?>">
                                    <input type="hidden" name="target_db_user_host" value="<?= htmlspecialchars($_POST['target_db_user_host'] ?? 'localhost') ?>">
                                    <input type="hidden" name="tournament_name" value="<?= htmlspecialchars($_POST['tournament_name'] ?? '') ?>">
                                    <input type="hidden" name="fixtures_url" value="<?= htmlspecialchars($_POST['fixtures_url'] ?? '') ?>">
                                    <?php if (isset($_POST['truncate_schedule'])) : ?><input type="hidden" name="truncate_schedule" value="1"><?php endif; ?>
                                    <?php foreach ($table_install_context as $contextKey => $contextValue) : ?>
                                        <input type="hidden" name="<?= htmlspecialchars($contextKey) ?>" value="<?= htmlspecialchars((string) $contextValue) ?>">
                                    <?php endforeach; ?>
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">Create <?= htmlspecialchars($definition['table']) ?></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($fixture_preview)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Fixture Preview (first 10 matches)</h2>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Stage</th>
                                <th>Home Flag</th>
                                <th>Home</th>
                                <th>Away Flag</th>
                                <th>Away</th>
                                <th>Date</th>
                                <th>Time (UTC)</th>
                                <th>Venue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fixture_preview as $fixture) : ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)$fixture['match_number']) ?></td>
                                    <td><?= htmlspecialchars($fixture['stage']) ?></td>
                                    <td><?= render_flag_preview_cell($fixture['hometeamimg'], $fixture['hometeam']) ?></td>
                                    <td><?= htmlspecialchars($fixture['hometeam']) ?></td>
                                    <td><?= render_flag_preview_cell($fixture['awayteamimg'], $fixture['awayteam']) ?></td>
                                    <td><?= htmlspecialchars($fixture['awayteam']) ?></td>
                                    <td><?= htmlspecialchars((string)$fixture['date']) ?></td>
                                    <td><?= htmlspecialchars($fixture['kotime']) ?></td>
                                    <td><?= htmlspecialchars($fixture['venue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($group_preview)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Group Preview</h2>
                <p class="text-muted">Use this to confirm that each group contains the right teams and that the flag mapping looks correct before applying the import.</p>
                <div class="row g-3">
                    <?php foreach ($group_preview as $groupName => $teams) : ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="setup-stat h-100">
                                <h3 class="h6 mb-3"><?= htmlspecialchars($groupName) ?></h3>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($teams as $team) : ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="flag-square">
                                                <?php if ($team['flag'] !== '') : ?>
                                                    <img src="<?= htmlspecialchars(hh_normalize_flag_src($team['flag'], '../'), ENT_QUOTES) ?>" alt="<?= htmlspecialchars($team['name'], ENT_QUOTES) ?> flag" width="24" height="24">
                                                <?php endif; ?>
                                            </span>
                                            <span><?= htmlspecialchars($team['name']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($sql_output)) : ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Generated SQL</h2>
                <p class="text-muted">This is the setup SQL that can create the target database, create the app user, build the required tables, and insert the imported fixtures.</p>
                <pre class="setup-code-block mb-0"><?= htmlspecialchars(implode("\n", $sql_output)) ?></pre>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var siteTitle = document.getElementById('site_title');
  var siteUrl = document.getElementById('site_url');
  var mysqlServer = document.getElementById('mysql_admin_server');
  var mysqlUser = document.getElementById('mysql_admin_user');
  var targetDb = document.getElementById('target_db_name');
  var targetDbUser = document.getElementById('target_db_user');
  var targetDbHost = document.getElementById('target_db_user_host');
  var tournamentName = document.getElementById('tournament_name');
  var fixturesUrl = document.getElementById('fixtures_url');
  var fixturesFile = document.getElementById('fixtures_file');
  var mysqlPanel = document.getElementById('setup-collapse-mysql');
  var databasePanel = document.getElementById('setup-collapse-database');
  var mysqlHeading = document.getElementById('setup-heading-mysql');
  var siteState = document.getElementById('setup-state-site');
  var siteMeta = document.getElementById('setup-meta-site');
  var mysqlState = document.getElementById('setup-state-mysql');
  var mysqlMeta = document.getElementById('setup-meta-mysql');
  var databaseState = document.getElementById('setup-state-database');
  var databaseMeta = document.getElementById('setup-meta-database');
  var sourceState = document.getElementById('setup-state-source');
  var sourceMeta = document.getElementById('setup-meta-source');
  var reviewSite = document.querySelector('[data-review="site"]');
  var reviewMysqlServer = document.querySelector('[data-review="mysql-server"]');
  var reviewTargetDb = document.querySelector('[data-review="target-db"]');
  var reviewAppUser = document.querySelector('[data-review="app-user"]');
  var reviewTournament = document.querySelector('[data-review="tournament"]');
  var reviewFixtureSource = document.querySelector('[data-review="fixture-source"]');
  var mysqlTestPassed = <?= json_encode($mysql_diagnostics['status'] === 'Connected') ?>;
  var initialMysqlServer = <?= json_encode((string) ($_POST['mysql_admin_server'] ?? 'localhost')) ?>;
  var initialMysqlUser = <?= json_encode((string) ($_POST['mysql_admin_user'] ?? 'hh_admin')) ?>;

  function readValue(input) {
    return input ? input.value.trim() : '';
  }

  function setReviewText(target, value) {
    if (target) {
      target.textContent = value;
    }
  }

  function setStepState(stateNode, metaNode, ready, readyLabel, pendingLabel, readyMeta, pendingMeta) {
    if (!stateNode) {
      return;
    }

    stateNode.textContent = ready ? readyLabel : pendingLabel;
    stateNode.classList.toggle('is-complete', ready);
    stateNode.classList.toggle('is-pending', !ready);

    if (metaNode) {
      metaNode.textContent = ready ? readyMeta : pendingMeta;
    }
  }

  function getFixtureSourceText() {
    var urlValue = readValue(fixturesUrl);
    if (urlValue !== '') {
      return urlValue;
    }

    if (fixturesFile && fixturesFile.files && fixturesFile.files[0]) {
      return fixturesFile.files[0].name;
    }

    return 'Not supplied';
  }

  function refreshLiveState() {
    var siteReady = readValue(siteTitle) !== '' && readValue(siteUrl) !== '';
    var mysqlReady = readValue(mysqlServer) !== '' && readValue(mysqlUser) !== '';
    var databaseReady = readValue(targetDb) !== '' && readValue(targetDbUser) !== '' && readValue(targetDbHost) !== '';
    var sourceReady = readValue(fixturesUrl) !== '' || (fixturesFile && fixturesFile.files && fixturesFile.files.length > 0);
    var mysqlStillMatchesTestedValues = readValue(mysqlServer) === initialMysqlServer && readValue(mysqlUser) === initialMysqlUser;

    setReviewText(reviewSite, readValue(siteTitle) || 'Not set');
    setReviewText(reviewMysqlServer, readValue(mysqlServer) || 'Not set');
    setReviewText(reviewTargetDb, readValue(targetDb) || 'Not set');
    setReviewText(reviewAppUser, readValue(targetDbUser) !== '' ? readValue(targetDbUser) + '@' + (readValue(targetDbHost) || 'localhost') : 'Not set');
    setReviewText(reviewTournament, readValue(tournamentName) || 'Will infer from source');
    setReviewText(reviewFixtureSource, getFixtureSourceText());

    setStepState(siteState, siteMeta, siteReady, 'Ready', 'Pending', 'Ready to use', 'Add the core site details');
    if (mysqlTestPassed && mysqlStillMatchesTestedValues) {
      setStepState(mysqlState, mysqlMeta, true, 'Ready', 'Pending', 'Connection test passed', 'Connect and inspect the target server');
    } else {
      setStepState(mysqlState, mysqlMeta, mysqlReady, 'Check', 'Pending', 'Ready to test the server', 'Connect and inspect the target server');
    }
    setStepState(databaseState, databaseMeta, databaseReady, 'Ready', 'Pending', 'Target database details captured', 'Set the database name and app credentials');
    setStepState(sourceState, sourceMeta, sourceReady, 'Ready', 'Pending', 'Fixture source supplied', 'Choose a feed or upload a local file, then build the plan');
  }

  [siteTitle, siteUrl, mysqlServer, mysqlUser, targetDb, targetDbUser, targetDbHost, tournamentName, fixturesUrl, fixturesFile].forEach(function (input) {
    if (!input) {
      return;
    }

    input.addEventListener('input', refreshLiveState);
    input.addEventListener('change', refreshLiveState);
  });

  refreshLiveState();

  <?php if ($wizard_action === 'test_db_connection') : ?>

  if (mysqlHeading) {
    mysqlHeading.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  <?php if ($mysql_test_success) : ?>
  window.setTimeout(function () {
    if (!mysqlPanel || !databasePanel || typeof bootstrap === 'undefined' || !bootstrap.Collapse) {
      return;
    }

    var mysqlCollapse = bootstrap.Collapse.getOrCreateInstance(mysqlPanel, { toggle: false });
    var databaseCollapse = bootstrap.Collapse.getOrCreateInstance(databasePanel, { toggle: false });

    mysqlCollapse.hide();
    databaseCollapse.show();

    window.setTimeout(function () {
      databasePanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 250);
  }, 2200);
  <?php endif; ?>
  <?php endif; ?>
});
</script>
<?php include "../php/footer.php"; ?>
