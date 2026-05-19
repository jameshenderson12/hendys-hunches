<?php
session_start();
$page_title = 'Dashboard';

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/flags.php';
hh_require_login('index.php');

include 'php/db-connect.php';
include "php/header.php";
include "php/navigation.php";

if (!function_exists('hh_dashboard_ordinal')) {
    function hh_dashboard_ordinal(int $number): string
    {
        $abs = abs($number);
        $suffix = 'th';
        if (($abs % 100) < 11 || ($abs % 100) > 13) {
            $suffix = match ($abs % 10) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
        }

        return $number . $suffix;
    }
}

if (!function_exists('hh_dashboard_move_meta')) {
    function hh_dashboard_move_meta(int $lastPos, int $currentPos): array
    {
        if ($lastPos > $currentPos) {
            $diff = $lastPos - $currentPos;
            return [
                'diff' => $diff,
                'label' => '+' . $diff,
                'class' => 'dashboard-up',
                'icon' => 'bi bi-caret-up-fill',
            ];
        }

        if ($lastPos < $currentPos) {
            $diff = $currentPos - $lastPos;
            return [
                'diff' => -$diff,
                'label' => '-' . $diff,
                'class' => 'dashboard-down',
                'icon' => 'bi bi-caret-down-fill',
            ];
        }

        return [
            'diff' => 0,
            'label' => '0',
            'class' => 'dashboard-neutral',
            'icon' => 'bi bi-dash',
        ];
    }
}

if (!function_exists('hh_dashboard_score_columns')) {
    function hh_dashboard_score_columns(mysqli $con, string $tableName, string $suffix): array
    {
        $columns = [];
        $result = mysqli_query($con, "SHOW COLUMNS FROM {$tableName}");
        if (!($result instanceof mysqli_result)) {
            return $columns;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $field = (string) ($row['Field'] ?? '');
            if (preg_match('/^score(\d+)_' . preg_quote($suffix, '/') . '$/', $field, $matches)) {
                $columns[(int) $matches[1]] = $field;
            }
        }

        mysqli_free_result($result);
        ksort($columns);

        return $columns;
    }
}

if (!function_exists('hh_dashboard_table_exists')) {
    function hh_dashboard_table_exists(mysqli $con, string $tableName): bool
    {
        $safeName = mysqli_real_escape_string($con, $tableName);
        $result = mysqli_query($con, "SHOW TABLES LIKE '{$safeName}'");
        if (!($result instanceof mysqli_result)) {
            return false;
        }

        $exists = mysqli_num_rows($result) > 0;
        mysqli_free_result($result);
        return $exists;
    }
}

if (!function_exists('hh_dashboard_capture')) {
    function hh_dashboard_capture(callable $renderer): string
    {
        ob_start();
        $renderer();
        return (string) ob_get_clean();
    }
}

if (!function_exists('hh_dashboard_layout_defaults')) {
    function hh_dashboard_layout_defaults(): array
    {
        return [
            'player_card' => ['label' => 'Player Card', 'width' => 'normal', 'visible' => 1],
            'todays_fixtures' => ['label' => "Today's Fixtures", 'width' => 'wide', 'visible' => 1],
            'form' => ['label' => 'Form', 'width' => 'wide', 'visible' => 1],
            'points_by_stage' => ['label' => 'Points by Stage', 'width' => 'normal', 'visible' => 1],
            'accuracy_breakdown' => ['label' => 'Accuracy Breakdown', 'width' => 'normal', 'visible' => 1],
            'winner_picks' => ['label' => 'Who Everyone Backed', 'width' => 'normal', 'visible' => 1],
            'biggest_movers' => ['label' => 'Biggest Movers', 'width' => 'normal', 'visible' => 1],
            'mini_league' => ['label' => 'Mini-League', 'width' => 'wide', 'visible' => 1],
            'game_pulse' => ['label' => 'Game Pulse', 'width' => 'wide', 'visible' => 1],
        ];
    }
}

if (!function_exists('hh_dashboard_ensure_layout_table')) {
    function hh_dashboard_ensure_layout_table(mysqli $con): bool
    {
        if (hh_dashboard_table_exists($con, 'live_dashboard_layout')) {
            return true;
        }

        $sql = @file_get_contents(__DIR__ . '/sql/setup-dashboard-layout-table.sql');
        if ($sql === false || trim($sql) === '') {
            return false;
        }

        return mysqli_multi_query($con, $sql) ? (function () use ($con): bool {
            while (mysqli_more_results($con) && mysqli_next_result($con)) {
                // consume remaining results
            }
            return hh_dashboard_table_exists($con, 'live_dashboard_layout');
        })() : false;
    }
}

if (!function_exists('hh_dashboard_load_layout')) {
    function hh_dashboard_load_layout(mysqli $con): array
    {
        $defaults = hh_dashboard_layout_defaults();
        $layout = [];

        foreach ($defaults as $cardKey => $definition) {
            $layout[$cardKey] = [
                'card_key' => $cardKey,
                'label' => $definition['label'],
                'width' => $definition['width'],
                'visible' => (int) $definition['visible'],
                'sort_order' => count($layout) + 1,
            ];
        }

        if (!hh_dashboard_table_exists($con, 'live_dashboard_layout')) {
            return array_values($layout);
        }

        $result = mysqli_query($con, "SELECT card_key, sort_order, card_width, is_visible FROM live_dashboard_layout WHERE layout_key = 'main' ORDER BY sort_order ASC, id ASC");
        if (!($result instanceof mysqli_result)) {
            return array_values($layout);
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $cardKey = (string) ($row['card_key'] ?? '');
            if (!isset($layout[$cardKey])) {
                continue;
            }

            $layout[$cardKey]['width'] = in_array(($row['card_width'] ?? ''), ['normal', 'wide'], true) ? (string) $row['card_width'] : $layout[$cardKey]['width'];
            $layout[$cardKey]['visible'] = (int) ($row['is_visible'] ?? 1) === 1 ? 1 : 0;
            $layout[$cardKey]['sort_order'] = max(1, (int) ($row['sort_order'] ?? $layout[$cardKey]['sort_order']));
        }
        mysqli_free_result($result);

        uasort(
            $layout,
            static fn(array $left, array $right): int =>
                (($left['sort_order'] ?? 0) <=> ($right['sort_order'] ?? 0))
                ?: strcmp((string) ($left['card_key'] ?? ''), (string) ($right['card_key'] ?? ''))
        );

        return array_values($layout);
    }
}

if (!function_exists('hh_dashboard_save_layout')) {
    function hh_dashboard_save_layout(mysqli $con, int $updatedBy, array $order, array $widths, array $visibleMap): void
    {
        if (!hh_dashboard_ensure_layout_table($con)) {
            throw new RuntimeException('The dashboard layout table could not be prepared.');
        }

        $defaults = hh_dashboard_layout_defaults();
        $resolvedOrder = [];

        foreach ($order as $cardKey) {
            if (isset($defaults[$cardKey]) && !in_array($cardKey, $resolvedOrder, true)) {
                $resolvedOrder[] = $cardKey;
            }
        }

        foreach (array_keys($defaults) as $cardKey) {
            if (!in_array($cardKey, $resolvedOrder, true)) {
                $resolvedOrder[] = $cardKey;
            }
        }

        mysqli_begin_transaction($con);

        try {
            mysqli_query($con, "DELETE FROM live_dashboard_layout WHERE layout_key = 'main'");
            $statement = mysqli_prepare(
                $con,
                "INSERT INTO live_dashboard_layout (layout_key, card_key, sort_order, card_width, is_visible, updated_by) VALUES ('main', ?, ?, ?, ?, ?)"
            );

            if (!$statement) {
                throw new RuntimeException(mysqli_error($con));
            }

            foreach ($resolvedOrder as $index => $cardKey) {
                $sortOrder = $index + 1;
                $width = in_array(($widths[$cardKey] ?? ''), ['normal', 'wide'], true) ? (string) $widths[$cardKey] : (string) $defaults[$cardKey]['width'];
                $isVisible = !empty($visibleMap[$cardKey]) ? 1 : 0;
                mysqli_stmt_bind_param($statement, 'sisii', $cardKey, $sortOrder, $width, $isVisible, $updatedBy);
                mysqli_stmt_execute($statement);
            }

            mysqli_stmt_close($statement);
            mysqli_commit($con);
        } catch (Throwable $exception) {
            mysqli_rollback($con);
            throw $exception;
        }
    }
}

if (!function_exists('hh_dashboard_normalize_team_name')) {
    function hh_dashboard_normalize_team_name(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
        return $value;
    }
}

if (!function_exists('hh_dashboard_team_alias_key')) {
    function hh_dashboard_team_alias_key(string $value): string
    {
        $normalized = hh_dashboard_normalize_team_name($value);

        $aliases = [
            'usa' => 'unitedstates',
            'us' => 'unitedstates',
            'unitedstatesofamerica' => 'unitedstates',
            'england' => 'england',
            'scotland' => 'scotland',
            'wales' => 'wales',
            'southkorea' => 'korearepublic',
            'koreasouth' => 'korearepublic',
            'ivorycoast' => 'cotedivoire',
            'czechrepublic' => 'czechia',
            'drcongo' => 'congodr',
            'democraticrepublicofcongo' => 'congodr',
            'iran' => 'iran',
        ];

        return $aliases[$normalized] ?? $normalized;
    }
}

if (!function_exists('hh_dashboard_flag_for_team')) {
    function hh_dashboard_flag_for_team(array $teamFlagMap, string $teamName): string
    {
        $normalizedName = hh_dashboard_team_alias_key($teamName);
        if ($normalizedName === '') {
            return '';
        }

        if (isset($teamFlagMap[$normalizedName])) {
            return (string) $teamFlagMap[$normalizedName];
        }

        $knownFlagPath = hh_get_team_flag_path($teamName);
        if ($knownFlagPath !== '') {
            return hh_normalize_flag_src($knownFlagPath);
        }

        foreach ($teamFlagMap as $candidateName => $flagPath) {
            if ($candidateName === '') {
                continue;
            }

            if (str_contains($candidateName, $normalizedName) || str_contains($normalizedName, $candidateName)) {
                return (string) $flagPath;
            }
        }

        return '';
    }
}

$stageContexts = hh_prediction_stage_contexts();
$sessionUserId = (int) ($_SESSION['id'] ?? 0);
$isDashboardAdmin = hh_is_admin_user();
$dashboardEditMode = $isDashboardAdmin && (($_GET['dashboard_edit'] ?? '0') === '1');
$messages = [];
$errors = [];
$todayFixtures = [];
$effectiveToday = hh_effective_today_sql();
$effectiveTodayLabel = hh_effective_today_label('D j M Y');
$stageWindows = hh_prediction_stage_windows($con);
$hasRecordedResults = false;
$dashboardReminder = null;
$stagePoints = [];
$winnerPicks = [];
$teamFlagMap = [];
$miniLeagueSelectionIds = [];
$miniLeagueIsConfigured = false;
$miniLeagueTableExists = hh_dashboard_table_exists($con, 'live_user_minileague');
$availableMiniLeaguePlayers = [];
$recentForm = [];
$accuracy = [
    ['label' => '7-pointers', 'value' => 0, 'color' => '#0c5a43'],
    ['label' => '3-pointers', 'value' => 0, 'color' => '#8f66d8'],
    ['label' => '2-pointers', 'value' => 0, 'color' => '#118ab2'],
    ['label' => '1-pointer', 'value' => 0, 'color' => '#f3c742'],
    ['label' => '0-pointers', 'value' => 0, 'color' => '#d7dde1'],
];
$rankingRows = [];
$currentUser = null;
$miniLeagueRows = [];
$momentumRows = [];
$prizeRace = null;
$pulseStats = [
    'players' => 0,
    'paid' => 0,
    'top_pick' => 'No picks yet',
    'prize_fund' => 0.0,
    'charity_total' => 0.0,
];
$dashboardLayoutCards = [];
$dashboardLayout = [];
$closestRivalSummary = [
    'ahead' => null,
    'behind' => null,
    'count' => 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['dashboard_action'] ?? '') === 'save_mini_league') {
    if (!$miniLeagueTableExists) {
        $errors[] = 'The mini-league table is not available yet. Create the remaining tables in the installation manager first.';
    } else {
        $postedMembers = $_POST['mini_league_members'] ?? [];
        $postedMembers = is_array($postedMembers) ? $postedMembers : [];
        $postedMembers = array_values(array_unique(array_map('intval', $postedMembers)));
        $postedMembers = array_values(array_filter($postedMembers, static fn (int $memberId): bool => $memberId > 0 && $memberId !== (int) $_SESSION['id']));

        if (count($postedMembers) > 8) {
            $errors[] = 'You can choose up to 8 mini-league players.';
        } else {
            $validMemberIds = [];
            if (!empty($postedMembers)) {
                $idList = implode(',', array_map('intval', $postedMembers));
                $validResult = mysqli_query(
                    $con,
                    "SELECT id FROM live_user_information WHERE id IN ({$idList}) AND id <> " . (int) $_SESSION['id']
                );

                if ($validResult instanceof mysqli_result) {
                    while ($row = mysqli_fetch_assoc($validResult)) {
                        $validMemberIds[] = (int) ($row['id'] ?? 0);
                    }
                    mysqli_free_result($validResult);
                }
            }

            sort($validMemberIds);
            sort($postedMembers);

            if ($validMemberIds !== $postedMembers) {
                $errors[] = 'One or more selected mini-league players could not be saved.';
            } else {
                mysqli_begin_transaction($con);

                try {
                    mysqli_query($con, "DELETE FROM live_user_minileague WHERE owner_id = " . (int) $_SESSION['id']);

                    if (!empty($validMemberIds)) {
                        $insertStatement = mysqli_prepare(
                            $con,
                            "INSERT INTO live_user_minileague (owner_id, member_id) VALUES (?, ?)"
                        );

                        if (!$insertStatement) {
                            throw new RuntimeException('Mini-league selections could not be prepared.');
                        }

                        foreach ($validMemberIds as $memberId) {
                            $ownerId = (int) $_SESSION['id'];
                            mysqli_stmt_bind_param($insertStatement, 'ii', $ownerId, $memberId);
                            mysqli_stmt_execute($insertStatement);
                        }

                        mysqli_stmt_close($insertStatement);
                    }

                    mysqli_commit($con);
                    $messages[] = empty($validMemberIds)
                        ? 'Your mini-league has been cleared.'
                        : 'Your mini-league players have been saved.';
                } catch (Throwable $exception) {
                    mysqli_rollback($con);
                    $errors[] = 'Mini-league selections could not be saved: ' . $exception->getMessage();
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['dashboard_action'] ?? '') === 'save_dashboard_layout') {
    if (!$isDashboardAdmin) {
        $errors[] = 'Only admins can edit the dashboard layout.';
    } else {
        $order = array_values(array_filter((array) ($_POST['layout_order'] ?? []), static fn($value): bool => is_string($value) && $value !== ''));
        $widths = [];
        foreach ((array) ($_POST['layout_width'] ?? []) as $cardKey => $widthValue) {
            if (is_string($cardKey) && is_string($widthValue)) {
                $widths[$cardKey] = $widthValue;
            }
        }

        $visibleMap = [];
        foreach ((array) ($_POST['layout_visible'] ?? []) as $cardKey => $visibleValue) {
            if (is_string($cardKey)) {
                $visibleMap[$cardKey] = ($visibleValue === '1');
            }
        }

        try {
            hh_dashboard_save_layout($con, $sessionUserId, $order, $widths, $visibleMap);
            $messages[] = 'Dashboard layout saved.';
        } catch (Throwable $exception) {
            $errors[] = 'Dashboard layout could not be saved: ' . $exception->getMessage();
        }
    }
}

$resultStateQuery = mysqli_query($con, "SELECT COUNT(*) AS total FROM live_match_schedule WHERE homescore IS NOT NULL AND awayscore IS NOT NULL");
if ($resultStateQuery instanceof mysqli_result) {
    $resultStateRow = mysqli_fetch_assoc($resultStateQuery) ?: [];
    $hasRecordedResults = ((int) ($resultStateRow['total'] ?? 0)) > 0;
    mysqli_free_result($resultStateQuery);
}

$rankingSelectParts = [
    'lui.id',
    'lui.username',
    'lui.firstname',
    'lui.surname',
    'lui.avatar',
    'lui.fieldofwork',
    'lui.location',
    'lui.faveteam',
    'lui.tournwinner',
    'lui.signupdate',
    'lui.haspaid',
    'lui.startpos',
    'lui.lastpos',
    'lui.currpos',
];
$rankingJoinParts = [];
$rankingTotalParts = [];

foreach ($stageContexts as $stageKey => $context) {
    $alias = 'pred_' . $stageKey;
    $rankingSelectParts[] = "COALESCE({$alias}.points_total, 0) AS {$stageKey}_points";
    $rankingJoinParts[] = "LEFT JOIN {$context['table']} {$alias} ON lui.id = {$alias}.id";
    $rankingTotalParts[] = "COALESCE({$alias}.points_total, 0)";
}

if (!empty($rankingTotalParts)) {
    $rankingSelectParts[] = '(' . implode(' + ', $rankingTotalParts) . ') AS total_points';
}

$rankingsQuery = 'SELECT ' . implode(",\n       ", $rankingSelectParts) . '
    FROM live_user_information lui
    ' . implode("\n    ", $rankingJoinParts) . '
    ORDER BY lui.currpos ASC, lui.surname ASC, lui.firstname ASC';

$rankingsResult = mysqli_query($con, $rankingsQuery);
if ($rankingsResult instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($rankingsResult)) {
        $currentPos = max(1, (int) ($row['currpos'] ?? $row['startpos'] ?? 1));
        $lastPos = max(1, (int) ($row['lastpos'] ?? $currentPos));
        $moveMeta = hh_dashboard_move_meta($lastPos, $currentPos);
        if (!$hasRecordedResults) {
            $moveMeta = [
                'diff' => 0,
                'label' => '-',
                'class' => 'dashboard-neutral',
                'icon' => 'bi bi-dash',
            ];
        }
        $player = [
            'id' => (int) ($row['id'] ?? 0),
            'username' => (string) ($row['username'] ?? ''),
            'firstname' => ucfirst((string) ($row['firstname'] ?? '')),
            'surname' => ucfirst((string) ($row['surname'] ?? '')),
            'name' => trim(ucfirst((string) ($row['firstname'] ?? '')) . ' ' . ucfirst((string) ($row['surname'] ?? ''))),
            'avatar' => (string) ($row['avatar'] ?? 'img/hh-icon-2024.png'),
            'fieldofwork' => trim((string) ($row['fieldofwork'] ?? '')),
            'location' => trim((string) ($row['location'] ?? '')),
            'faveteam' => trim((string) ($row['faveteam'] ?? '')),
            'tournwinner' => trim((string) ($row['tournwinner'] ?? '')),
            'signupdate' => (string) ($row['signupdate'] ?? ''),
            'haspaid' => trim((string) ($row['haspaid'] ?? 'No')),
            'rank' => $currentPos,
            'rank_label' => $hasRecordedResults ? hh_dashboard_ordinal($currentPos) : '-',
            'lastpos' => $lastPos,
            'points_total' => (int) ($row['total_points'] ?? 0),
            'move' => $moveMeta,
            'is_me' => (int) ($row['id'] ?? 0) === $sessionUserId,
            'stage_points' => [],
        ];

        foreach ($stageContexts as $stageKey => $context) {
            $player['stage_points'][$stageKey] = (int) ($row[$stageKey . '_points'] ?? 0);
        }

        $rankingRows[] = $player;

        if ($player['is_me']) {
            $currentUser = $player;
        }
    }

    mysqli_free_result($rankingsResult);
}

if ($miniLeagueTableExists && $sessionUserId > 0) {
    $miniLeagueSelectionResult = mysqli_query(
        $con,
        "SELECT member_id FROM live_user_minileague WHERE owner_id = " . (int) $sessionUserId . " ORDER BY created_at ASC, id ASC"
    );

    if ($miniLeagueSelectionResult instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($miniLeagueSelectionResult)) {
            $memberId = (int) ($row['member_id'] ?? 0);
            if ($memberId > 0) {
                $miniLeagueSelectionIds[] = $memberId;
            }
        }
        mysqli_free_result($miniLeagueSelectionResult);
    }
}

$miniLeagueSelectionIds = array_values(array_unique($miniLeagueSelectionIds));
$miniLeagueIsConfigured = !empty($miniLeagueSelectionIds);

if ($sessionUserId > 0) {
    $availablePlayersResult = mysqli_query(
        $con,
        "SELECT id, firstname, surname, avatar
         FROM live_user_information
         WHERE id <> " . (int) $sessionUserId . "
         ORDER BY surname ASC, firstname ASC"
    );
    if ($availablePlayersResult instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($availablePlayersResult)) {
            $availableMiniLeaguePlayers[] = $row;
        }
        mysqli_free_result($availablePlayersResult);
    }
}

foreach ($stageContexts as $stageKey => $context) {
    $stagePoints[] = [
        'label' => $context['label'],
        'points' => (int) ($currentUser['stage_points'][$stageKey] ?? 0),
        'max' => $context['fixtures'] * 7,
    ];
}

$playerCountResult = mysqli_query($con, "SELECT COUNT(*) AS total_players, SUM(CASE WHEN haspaid = 'Yes' THEN 1 ELSE 0 END) AS paid_players FROM live_user_information");
if ($playerCountResult instanceof mysqli_result) {
    $playerCountRow = mysqli_fetch_assoc($playerCountResult) ?: [];
    $pulseStats['players'] = (int) ($playerCountRow['total_players'] ?? 0);
    $pulseStats['paid'] = (int) ($playerCountRow['paid_players'] ?? 0);
    $pulseStats['prize_fund'] = $pulseStats['paid'] * $prize_fee;
    $pulseStats['charity_total'] = $pulseStats['paid'] * $charity_fee;
    mysqli_free_result($playerCountResult);
}

$teamFlagResult = mysqli_query(
    $con,
    "SELECT hometeam AS team_name, hometeamimg AS team_flag FROM live_match_schedule WHERE hometeam <> '' AND hometeamimg <> ''
     UNION
     SELECT awayteam AS team_name, awayteamimg AS team_flag FROM live_match_schedule WHERE awayteam <> '' AND awayteamimg <> ''"
);
if ($teamFlagResult instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($teamFlagResult)) {
        $teamName = trim((string) ($row['team_name'] ?? ''));
        $teamFlag = trim((string) ($row['team_flag'] ?? ''));
        $teamKey = hh_dashboard_team_alias_key($teamName);
        if ($teamKey !== '' && $teamFlag !== '' && !isset($teamFlagMap[$teamKey])) {
            $teamFlagMap[$teamKey] = $teamFlag;
        }
    }
    mysqli_free_result($teamFlagResult);
}

$winnerPicksResult = mysqli_query(
    $con,
    "SELECT tournwinner, COUNT(*) AS pick_count
     FROM live_user_information
     WHERE TRIM(tournwinner) <> ''
       AND LOWER(TRIM(tournwinner)) NOT IN ('prefer not to say', 'not set')
     GROUP BY tournwinner
     ORDER BY pick_count DESC, tournwinner ASC
     LIMIT 5"
);
if ($winnerPicksResult instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($winnerPicksResult)) {
        $count = (int) ($row['pick_count'] ?? 0);
        $winnerTeam = trim((string) ($row['tournwinner'] ?? ''));
        $winnerPicks[] = [
            'team' => $winnerTeam,
            'flag' => hh_dashboard_flag_for_team($teamFlagMap, $winnerTeam),
            'count' => $count,
            'percent' => $pulseStats['players'] > 0 ? (int) round(($count / $pulseStats['players']) * 100) : 0,
        ];
    }

    mysqli_free_result($winnerPicksResult);
}

if (!empty($winnerPicks)) {
    $pulseStats['top_pick'] = $winnerPicks[0]['team'] . ' (' . $winnerPicks[0]['count'] . ')';
}

$fixtureStatement = mysqli_prepare(
    $con,
    "SELECT match_number, stage, date, kotime, venue, hometeam, awayteam, hometeamimg, awayteamimg
     FROM live_match_schedule
     WHERE date = ?
     ORDER BY kotime ASC, match_number ASC"
);

if ($fixtureStatement) {
    mysqli_stmt_bind_param($fixtureStatement, 's', $effectiveToday);
    mysqli_stmt_execute($fixtureStatement);
    $fixtureResult = mysqli_stmt_get_result($fixtureStatement);

    if ($fixtureResult instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($fixtureResult)) {
            $todayFixtures[] = [
                'time' => (string) ($row['kotime'] ?? ''),
                'home' => (string) ($row['hometeam'] ?? ''),
                'home_flag' => (string) ($row['hometeamimg'] ?? ''),
                'home_avg' => trim((string) ($row['stage'] ?? '')),
                'away' => (string) ($row['awayteam'] ?? ''),
                'away_flag' => (string) ($row['awayteamimg'] ?? ''),
                'away_avg' => (string) ($row['venue'] ?? ''),
                'pick' => 'Match ' . (int) ($row['match_number'] ?? 0),
            ];
        }

        mysqli_free_result($fixtureResult);
    }

    mysqli_stmt_close($fixtureStatement);
}

foreach ($stageWindows as $stageKey => $window) {
    $submitted = false;
    $lastUpdate = '';

    $submissionStatement = mysqli_prepare($con, "SELECT lastupdate FROM {$window['table']} WHERE id = ? LIMIT 1");
    if ($submissionStatement) {
        $sessionId = (int) ($_SESSION['id'] ?? 0);
        mysqli_stmt_bind_param($submissionStatement, 'i', $sessionId);
        mysqli_stmt_execute($submissionStatement);
        $submissionResult = mysqli_stmt_get_result($submissionStatement);

        if ($submissionResult instanceof mysqli_result) {
            $submissionRow = mysqli_fetch_assoc($submissionResult) ?: null;
            $submitted = is_array($submissionRow);
            $lastUpdate = !empty($submissionRow['lastupdate']) ? (string) $submissionRow['lastupdate'] : '';
            mysqli_free_result($submissionResult);
        }

        mysqli_stmt_close($submissionStatement);
    }

    $window['submitted'] = $submitted;
    $window['lastupdate'] = $lastUpdate;
    $stageWindows[$stageKey] = $window;
}

foreach ($stageWindows as $window) {
    if ($window['status'] === 'open') {
        $deadlineLabel = $window['closes_at'] instanceof DateTimeImmutable
            ? $window['closes_at']->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('D j M H:i')
            : '';

        $dashboardReminder = [
            'type' => $window['submitted'] ? 'info' : 'warning',
            'stage' => $window['label'],
            'href' => 'predictions.php?stage=' . urlencode($window['key']),
            'title' => $window['submitted']
                ? $window['label'] . ' predictions are in'
                : 'Submit your ' . strtolower($window['label']) . ' predictions',
            'body' => $window['submitted']
                ? 'You can still review or update this stage before the window closes' . ($deadlineLabel !== '' ? ' at ' . $deadlineLabel : '') . '.'
                : 'This stage is currently open' . ($deadlineLabel !== '' ? ' and will lock at ' . $deadlineLabel : '') . '.',
            'cta' => $window['submitted'] ? 'Review predictions' : 'Submit predictions',
        ];
        break;
    }
}

if ($dashboardReminder === null) {
    foreach ($stageWindows as $window) {
        if ($window['status'] === 'upcoming') {
            $openLabel = $window['opens_at'] instanceof DateTimeImmutable
                ? $window['opens_at']->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('D j M H:i')
                : '';

            $dashboardReminder = [
                'type' => 'secondary',
                'stage' => $window['label'],
                'href' => 'predictions.php?stage=' . urlencode($window['key']),
                'title' => $window['label'] . ' predictions are next',
                'body' => 'This stage will open 5 hours after the previous stage\'s last kick-off' . ($openLabel !== '' ? ', which works out as ' . $openLabel : '') . '.',
                'cta' => 'Preview stage',
            ];
            break;
        }
    }
}

if ($currentUser) {
    if ($miniLeagueIsConfigured) {
        $allowedIds = array_fill_keys($miniLeagueSelectionIds, true);
        $allowedIds[(int) $currentUser['id']] = true;

        foreach ($rankingRows as $player) {
            if (isset($allowedIds[(int) ($player['id'] ?? 0)])) {
                $miniLeagueRows[] = $player;
            }
        }

        foreach ($miniLeagueRows as $index => $player) {
            if ($player['id'] === $currentUser['id']) {
                $closestRivalSummary['ahead'] = $miniLeagueRows[$index - 1] ?? null;
                $closestRivalSummary['behind'] = $miniLeagueRows[$index + 1] ?? null;
                break;
            }
        }
    } else {
        $miniLeagueRows = [];
    }

    $closestRivalSummary['count'] = count($miniLeagueRows);
}

$momentumRows = $rankingRows;
if ($hasRecordedResults) {
    usort(
        $momentumRows,
        static function (array $left, array $right): int {
            $leftAbs = abs((int) ($left['move']['diff'] ?? 0));
            $rightAbs = abs((int) ($right['move']['diff'] ?? 0));

            if ($leftAbs === $rightAbs) {
                return ($left['rank'] ?? 9999) <=> ($right['rank'] ?? 9999);
            }

            return $rightAbs <=> $leftAbs;
        }
    );
    $momentumRows = array_values(array_filter($momentumRows, static fn(array $player): bool => (int) ($player['move']['diff'] ?? 0) !== 0));
    $momentumRows = array_slice($momentumRows, 0, 4);
} else {
    $momentumRows = [];
}

if ($hasRecordedResults && $currentUser && !empty($rankingRows)) {
    $prizePlace = min(5, count($rankingRows));
    $currentRank = (int) $currentUser['rank'];
    $currentPoints = (int) $currentUser['points_total'];

    if ($currentRank > $prizePlace && isset($rankingRows[$prizePlace - 1])) {
        $target = $rankingRows[$prizePlace - 1];
        $prizeRace = [
            'eyebrow' => 'Prize race',
            'title' => 'Chasing ' . hh_dashboard_ordinal($prizePlace),
            'gap' => max(0, (int) $target['points_total'] - $currentPoints),
            'top' => ['label' => hh_dashboard_ordinal($prizePlace) . ' place', 'name' => $target['name'], 'points' => (int) $target['points_total']],
            'bottom' => ['label' => 'You', 'name' => $currentUser['rank_label'], 'points' => $currentPoints],
        ];
    } elseif ($currentRank > 1 && isset($rankingRows[$currentRank - 2])) {
        $target = $rankingRows[$currentRank - 2];
        $prizeRace = [
            'eyebrow' => 'Prize race',
            'title' => 'Chasing ' . $target['rank_label'],
            'gap' => max(0, (int) $target['points_total'] - $currentPoints),
            'top' => ['label' => $target['rank_label'], 'name' => $target['name'], 'points' => (int) $target['points_total']],
            'bottom' => ['label' => 'You', 'name' => $currentUser['rank_label'], 'points' => $currentPoints],
        ];
    } elseif (isset($rankingRows[1])) {
        $challenger = $rankingRows[1];
        $prizeRace = [
            'eyebrow' => 'Prize race',
            'title' => 'Holding 1st',
            'gap' => max(0, $currentPoints - (int) $challenger['points_total']),
            'top' => ['label' => 'You', 'name' => $currentUser['rank_label'], 'points' => $currentPoints],
            'bottom' => ['label' => '2nd place', 'name' => $challenger['name'], 'points' => (int) $challenger['points_total']],
        ];
    }
}

if ($currentUser && !empty($stageContexts)) {
    $resultScoreColumns = hh_dashboard_score_columns($con, 'live_match_results', 'r');
    $predictionByScore = [];

    $resultScores = null;
    if (!empty($resultScoreColumns)) {
        $resultsQuery = mysqli_query(
            $con,
            "SELECT " . implode(', ', array_values($resultScoreColumns)) . " FROM live_match_results ORDER BY match_id DESC LIMIT 1"
        );
        $resultScores = $resultsQuery ? mysqli_fetch_assoc($resultsQuery) : null;
        if ($resultsQuery instanceof mysqli_result) {
            mysqli_free_result($resultsQuery);
        }
    }

    foreach ($stageContexts as $stageKey => $context) {
        $predictionFields = [];
        $predictionScoreColumns = hh_dashboard_score_columns($con, $context['table'], 'p');
        for ($scoreIndex = $context['score_start']; $scoreIndex <= $context['score_end']; $scoreIndex++) {
            if (isset($predictionScoreColumns[$scoreIndex])) {
                $predictionFields[] = $predictionScoreColumns[$scoreIndex];
            }
        }

        if (empty($predictionFields)) {
            continue;
        }

        $predictionResult = mysqli_query(
            $con,
            "SELECT " . implode(', ', $predictionFields) . " FROM {$context['table']} WHERE id = " . $sessionUserId . " LIMIT 1"
        );
        $predictionRow = $predictionResult ? mysqli_fetch_assoc($predictionResult) : null;
        if ($predictionResult instanceof mysqli_result) {
            mysqli_free_result($predictionResult);
        }

        if (!is_array($predictionRow)) {
            continue;
        }

        foreach ($predictionRow as $field => $value) {
            if (preg_match('/^score(\d+)_p$/', (string) $field, $matches)) {
                $predictionByScore[(int) $matches[1]] = $value;
            }
        }
    }

    if (is_array($resultScores)) {
        $completedFixtures = [];
        foreach ($stageContexts as $context) {
            for ($home = $context['score_start'], $away = $context['score_start'] + 1; $home <= $context['score_end'] && $away <= $context['score_end']; $home += 2, $away += 2) {
                $resHome = $resultScores["score{$home}_r"] ?? null;
                $resAway = $resultScores["score{$away}_r"] ?? null;
                $predHome = $predictionByScore[$home] ?? null;
                $predAway = $predictionByScore[$away] ?? null;

                if (!is_numeric($resHome) || !is_numeric($resAway) || !is_numeric($predHome) || !is_numeric($predAway)) {
                    continue;
                }

                $resHome = (int) $resHome;
                $resAway = (int) $resAway;
                $predHome = (int) $predHome;
                $predAway = (int) $predAway;
                $fixturePoints = 0;
                $homeScoreHit = $predHome === $resHome;
                $awayScoreHit = $predAway === $resAway;

                if ($homeScoreHit && $awayScoreHit) {
                    $accuracy[0]['value']++;
                    $fixturePoints = 7;
                    $completedFixtures[] = [
                        'match_number' => (int) (($home + 1) / 2),
                        'points' => $fixturePoints,
                    ];
                    continue;
                }

                $sameOutcome =
                    (($predHome > $predAway) && ($resHome > $resAway))
                    || (($predHome < $predAway) && ($resHome < $resAway))
                    || (($predHome === $predAway) && ($resHome === $resAway));

                if ($sameOutcome) {
                    if ($homeScoreHit || $awayScoreHit) {
                        $accuracy[1]['value']++;
                        $fixturePoints = 3;
                    } else {
                        $accuracy[2]['value']++;
                        $fixturePoints = 2;
                    }
                    $completedFixtures[] = [
                        'match_number' => (int) (($home + 1) / 2),
                        'points' => $fixturePoints,
                    ];
                    continue;
                }

                if ($homeScoreHit || $awayScoreHit) {
                    $accuracy[3]['value']++;
                    $fixturePoints = 1;
                    $completedFixtures[] = [
                        'match_number' => (int) (($home + 1) / 2),
                        'points' => $fixturePoints,
                    ];
                    continue;
                }

                $accuracy[4]['value']++;
                $completedFixtures[] = [
                    'match_number' => (int) (($home + 1) / 2),
                    'points' => 0,
                ];
            }
        }

        if (!empty($completedFixtures)) {
            usort(
                $completedFixtures,
                static fn(array $left, array $right): int => ($left['match_number'] ?? 0) <=> ($right['match_number'] ?? 0)
            );
            $recentForm = array_slice($completedFixtures, -6);
        }
    }
}

$accuracyOverallTotal = 0;

foreach ($accuracy as $index => $item) {
    $accuracyOverallTotal += (int) $item['value'];
}

$accuracyChartLabels = array_map(static fn(array $item): string => (string) ($item['label'] ?? ''), $accuracy);
$accuracyChartValues = array_map(static fn(array $item): int => (int) ($item['value'] ?? 0), $accuracy);
$accuracyChartColors = array_map(static fn(array $item): string => (string) ($item['color'] ?? '#cccccc'), $accuracy);

$formChartLabels = [];
$formChartValues = [];
$formPointMeta = [];

if (!empty($recentForm)) {
    foreach ($recentForm as $formItem) {
        $points = (int) ($formItem['points'] ?? 0);
        $matchNumber = (int) ($formItem['match_number'] ?? 0);
        $formChartLabels[] = (string) $matchNumber;
        $formChartValues[] = $points;
        $pointClass = 'is-mid';
        if ($points >= 5) {
            $pointClass = 'is-strong';
        } elseif ($points <= 1) {
            $pointClass = 'is-weak';
        }
        $formPointMeta[] = [
            'match_number' => $matchNumber,
            'points' => $points,
            'class' => $pointClass,
        ];
    }
}

$dashboardLayoutCards = [
    'player_card' => [
        'label' => 'Player Card',
        'markup' => hh_dashboard_capture(function () use ($currentUser) { ?>
            <article class="dashboard-player-card">
                <div class="dashboard-player-card__kit">
                    <img src="<?= htmlspecialchars((string) ($currentUser['avatar'] ?? 'img/hh-icon-2024.png')) ?>" alt="<?= htmlspecialchars((string) ($currentUser['name'] ?? 'Player')) ?> football strip avatar">
                </div>
                <div class="dashboard-player-card__body">
                    <p class="eyebrow">Player card</p>
                    <h2><?= htmlspecialchars((string) ($currentUser['name'] ?? 'Preview player')) ?></h2>
                    <p class="dashboard-note">
                        <?= htmlspecialchars((string) (($currentUser['haspaid'] ?? 'No') === 'Yes' ? 'Entry fee paid' : 'Entry fee pending')) ?>
                        <?php if (!empty($currentUser['signupdate'])) : ?>
                            · signed up <?= htmlspecialchars(date('j F Y', strtotime((string) $currentUser['signupdate']))) ?>
                        <?php endif; ?>
                    </p>
                    <div class="dashboard-player-stats">
                        <span><strong><?= htmlspecialchars((string) ($currentUser['rank_label'] ?? 'N/A')) ?></strong>Rank</span>
                        <span><strong><?= htmlspecialchars((string) ($currentUser['points_total'] ?? 0)) ?></strong>Points</span>
                        <span><strong><?= htmlspecialchars((string) ($currentUser['move']['label'] ?? '0')) ?></strong>Move</span>
                    </div>
                    <dl class="dashboard-player-details">
                        <div><dt>Favourite club</dt><dd><?= htmlspecialchars((string) ($currentUser['faveteam'] ?? 'Not set')) ?></dd></div>
                        <div><dt>Tournament winner</dt><dd><?= htmlspecialchars((string) ($currentUser['tournwinner'] ?? 'Not set')) ?></dd></div>
                        <div><dt>Location</dt><dd><?= htmlspecialchars((string) ($currentUser['location'] ?? 'Not set')) ?></dd></div>
                        <div><dt>Field</dt><dd><?= htmlspecialchars((string) ($currentUser['fieldofwork'] ?? 'Not set')) ?></dd></div>
                    </dl>
                </div>
            </article>
        <?php }),
    ],
    'todays_fixtures' => [
        'label' => "Today's Fixtures",
        'markup' => hh_dashboard_capture(function () use ($todayFixtures, $effectiveTodayLabel) { ?>
            <article class="dashboard-panel">
                <div class="dashboard-panel__header">
                    <div>
                        <p class="eyebrow">Matchday card</p>
                        <h2>Today's Fixtures</h2>
                        <p class="dashboard-subtle mb-0"><?= htmlspecialchars($effectiveTodayLabel) ?></p>
                    </div>
                    <span class="dashboard-pill"><?= count($todayFixtures) ?> games</span>
                </div>
                <div class="fixture-stack">
                    <?php if (!empty($todayFixtures)) : ?>
                        <?php foreach ($todayFixtures as $fixture) : ?>
                            <article class="fixture-card-row">
                                <div class="fixture-card-row__meta"><span class="fixture-card-row__time"><?= htmlspecialchars($fixture['time']) ?></span></div>
                                <div class="fixture-card-row__match">
                                    <div class="fixture-card-row__team">
                                        <img src="<?= htmlspecialchars($fixture['home_flag']) ?>" alt="<?= htmlspecialchars($fixture['home']) ?> flag">
                                        <div><strong><?= htmlspecialchars($fixture['home']) ?></strong></div>
                                    </div>
                                    <div class="fixture-card-row__divider">vs</div>
                                    <div class="fixture-card-row__team fixture-card-row__team--away">
                                        <div><strong><?= htmlspecialchars($fixture['away']) ?></strong></div>
                                        <img src="<?= htmlspecialchars($fixture['away_flag']) ?>" alt="<?= htmlspecialchars($fixture['away']) ?> flag">
                                    </div>
                                </div>
                                <p class="fixture-card-row__pick"><?= htmlspecialchars($fixture['pick']) ?> · <?= htmlspecialchars($fixture['away_avg']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="dashboard-note mb-0">No fixtures are scheduled for the selected day.</p>
                    <?php endif; ?>
                </div>
                <p class="dashboard-note mb-0">In preview mode, changing the configured preview day will change this card to show that date’s fixtures.</p>
            </article>
        <?php }),
    ],
    'form' => [
        'label' => 'Form',
        'markup' => hh_dashboard_capture(function () use ($formPointMeta) { ?>
            <article class="dashboard-panel">
                <div class="dashboard-panel__header">
                    <div><p class="eyebrow">Recent returns</p><h2>Form</h2></div>
                </div>
                <?php if (!empty($formPointMeta)) : ?>
                    <div class="form-chart">
                        <div class="form-chart__canvas-wrap">
                            <canvas id="dashboardFormChart" aria-label="Last six scored fixtures form chart" role="img"></canvas>
                        </div>
                    </div>
                <?php else : ?>
                    <p class="dashboard-note mb-0">Form will appear once enough results have been recorded to build a recent scoring run.</p>
                <?php endif; ?>
            </article>
        <?php }),
    ],
    'points_by_stage' => [
        'label' => 'Points by Stage',
        'markup' => hh_dashboard_capture(function () use ($stagePoints, $currentUser) { ?>
            <article class="dashboard-panel">
                <div class="dashboard-panel__header">
                    <div><p class="eyebrow">Personal form</p><h2>Points by Stage</h2></div>
                    <span class="dashboard-pill"><?= htmlspecialchars((string) ($currentUser['points_total'] ?? 0)) ?> pts</span>
                </div>
                <div class="stage-bars">
                    <?php foreach ($stagePoints as $stage) : $width = $stage['max'] > 0 ? round(($stage['points'] / $stage['max']) * 100) : 0; ?>
                        <div class="stage-bar">
                            <div class="stage-bar__meta"><span><?= htmlspecialchars((string) $stage['label']) ?></span><strong><?= (int) $stage['points'] ?> / <?= (int) $stage['max'] ?></strong></div>
                            <div class="stage-bar__track"><span style="width: <?= $width ?>%"></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php }),
    ],
    'accuracy_breakdown' => [
        'label' => 'Accuracy Breakdown',
        'markup' => hh_dashboard_capture(function () use ($accuracy) { ?>
            <article class="dashboard-panel">
                <div class="dashboard-panel__header">
                    <div><p class="eyebrow">Prediction quality</p><h2>Accuracy Breakdown</h2></div>
                </div>
                <div class="accuracy-donut" aria-label="Accuracy breakdown">
                    <div class="accuracy-donut__chart">
                        <canvas id="dashboardAccuracyChart" aria-label="Accuracy breakdown donut chart" role="img"></canvas>
                    </div>
                    <ul class="accuracy-list">
                        <?php foreach ($accuracy as $item) : ?>
                            <li><span class="accuracy-list__label"><span class="accuracy-list__swatch" style="background: <?= htmlspecialchars($item['color'], ENT_QUOTES) ?>"></span><?= htmlspecialchars($item['label']) ?></span><strong><?= (int) $item['value'] ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </article>
        <?php }),
    ],
    'winner_picks' => [
        'label' => 'Who Everyone Backed',
        'markup' => hh_dashboard_capture(function () use ($winnerPicks) { ?>
            <article class="dashboard-panel">
                <div class="dashboard-panel__header">
                    <div><p class="eyebrow">Crowd read</p><h2>Who Everyone Backed</h2></div>
                </div>
                <div class="crowd-bars">
                    <?php if (!empty($winnerPicks)) : ?>
                        <?php foreach ($winnerPicks as $pick) : ?>
                            <div class="crowd-bar">
                                <div class="crowd-bar__meta">
                                    <span class="crowd-bar__team"><?php if (!empty($pick['flag'])) : ?><span class="crowd-bar__flag"><img src="<?= htmlspecialchars((string) $pick['flag']) ?>" alt="<?= htmlspecialchars((string) $pick['team']) ?> flag"></span><?php endif; ?><?= htmlspecialchars((string) $pick['team']) ?></span>
                                    <strong><?= htmlspecialchars((string) $pick['count']) ?></strong>
                                </div>
                                <div class="crowd-bar__track"><span style="width: <?= (int) $pick['percent'] ?>%"></span></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="dashboard-note mb-0">Winner picks will appear once players have chosen their champions.</p>
                    <?php endif; ?>
                </div>
            </article>
        <?php }),
    ],
    'biggest_movers' => [
        'label' => 'Biggest Movers',
        'markup' => hh_dashboard_capture(function () use ($momentumRows) { ?>
            <article class="dashboard-panel">
                <div class="dashboard-panel__header">
                    <div><p class="eyebrow">Momentum</p><h2>Biggest Movers</h2></div>
                </div>
                <ol class="movement-list">
                    <?php if (!empty($momentumRows)) : ?>
                        <?php foreach ($momentumRows as $player) : ?>
                            <li><span class="<?= htmlspecialchars((string) $player['move']['class']) ?>"><i class="<?= htmlspecialchars((string) $player['move']['icon']) ?>"></i> <?= htmlspecialchars((string) $player['name']) ?></span><strong><?= htmlspecialchars((string) $player['move']['label']) ?></strong></li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li><span class="dashboard-neutral"><i class="bi bi-dash"></i> No movement yet</span><strong>0</strong></li>
                    <?php endif; ?>
                </ol>
            </article>
        <?php }),
    ],
    'mini_league' => [
        'label' => 'Mini-League',
        'markup' => hh_dashboard_capture(function () use ($miniLeagueIsConfigured, $miniLeagueRows, $hasRecordedResults, $miniLeagueSelectionIds, $availableMiniLeaguePlayers, $miniLeagueTableExists, $errors) { ?>
            <article class="dashboard-panel">
                <div class="dashboard-panel__header">
                    <div><p class="eyebrow">Mini-league</p><h2><?= $miniLeagueIsConfigured ? 'Your Mini-League' : 'Set Up Your Mini-League' ?></h2></div>
                    <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardMiniLeagueManager" aria-expanded="false" aria-controls="dashboardMiniLeagueManager"><i class="bi bi-people"></i> <?= $miniLeagueIsConfigured ? 'Manage mini-league' : 'Choose players' ?></button>
                </div>
                <?php if ($miniLeagueIsConfigured && !empty($miniLeagueRows)) : ?>
                    <div class="mini-league-table">
                        <?php foreach ($miniLeagueRows as $player) : ?>
                            <div class="mini-league-row<?= !empty($player['is_me']) ? ' mini-league-row--me' : '' ?>">
                                <span class="mini-league-rank"><?= htmlspecialchars((string) ($hasRecordedResults ? $player['rank'] : '-')) ?></span>
                                <img class="mini-league-avatar" src="<?= htmlspecialchars((string) $player['avatar']) ?>" alt="<?= htmlspecialchars((string) $player['name']) ?> kit avatar">
                                <span class="mini-league-player"><strong><?= htmlspecialchars((string) $player['name']) ?></strong><small><?= htmlspecialchars((string) ($player['location'] !== '' ? $player['location'] : ($player['faveteam'] !== '' ? $player['faveteam'] : 'Mini-league player'))) ?></small></span>
                                <span class="mini-league-points"><?= htmlspecialchars((string) $player['points_total']) ?> pts</span>
                                <span class="mini-league-move <?= htmlspecialchars((string) $player['move']['class']) ?>"><?= htmlspecialchars((string) $player['move']['label']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="dashboard-note mb-0">This table is built from the players you chose for your personal mini-league.</p>
                <?php else : ?>
                    <div class="mini-league-empty-state">
                        <p class="dashboard-note mb-0">Choose up to 8 other players and your personalised mini-league will appear here on the dashboard.</p>
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardMiniLeagueManager" aria-expanded="false" aria-controls="dashboardMiniLeagueManager"><i class="bi bi-plus-circle"></i> Choose players</button>
                    </div>
                <?php endif; ?>
                <div class="collapse mt-3<?= !empty($errors) ? ' show' : '' ?>" id="dashboardMiniLeagueManager">
                    <div class="mini-league-manager__summary">
                        <span><strong><?= count($miniLeagueSelectionIds) ?></strong> chosen</span>
                        <span><strong>8</strong> max</span>
                        <span><strong><?= count($availableMiniLeaguePlayers) ?></strong> available</span>
                    </div>
                    <?php if ($miniLeagueTableExists) : ?>
                        <form method="post" class="mini-league-manager" id="dashboardMiniLeagueForm">
                            <input type="hidden" name="dashboard_action" value="save_mini_league">
                            <div class="mini-league-manager__search">
                                <label class="form-label" for="miniLeagueSearch">Find players</label>
                                <input class="form-control" id="miniLeagueSearch" type="search" placeholder="Search by name">
                            </div>
                            <div class="mini-league-manager__list" id="miniLeagueOptions">
                                <?php foreach ($availableMiniLeaguePlayers as $playerOption) : $optionId = (int) ($playerOption['id'] ?? 0); $optionName = trim((string) ($playerOption['firstname'] ?? '') . ' ' . (string) ($playerOption['surname'] ?? '')); ?>
                                    <label class="mini-league-option" data-player-name="<?= htmlspecialchars(strtolower($optionName), ENT_QUOTES) ?>">
                                        <input class="form-check-input mini-league-option__checkbox" type="checkbox" name="mini_league_members[]" value="<?= $optionId ?>" <?= in_array($optionId, $miniLeagueSelectionIds, true) ? 'checked' : '' ?>>
                                        <img src="<?= htmlspecialchars((string) ($playerOption['avatar'] ?? 'img/hh-icon-2024.png')) ?>" alt="" width="24" height="24">
                                        <span><?= htmlspecialchars($optionName) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mt-3">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-floppy"></i> Save mini-league</button>
                                <span class="dashboard-note" id="miniLeagueCount"><?= count($miniLeagueSelectionIds) ?> of 8 chosen</span>
                            </div>
                        </form>
                    <?php else : ?>
                        <p class="dashboard-note mb-0">The mini-league table has not been created yet. Create the remaining tables in the installation manager and this selector will come to life.</p>
                    <?php endif; ?>
                </div>
            </article>
        <?php }),
    ],
    'game_pulse' => [
        'label' => 'Game Pulse',
        'markup' => hh_dashboard_capture(function () use ($pulseStats, $charity, $charity_url) { ?>
            <article class="dashboard-panel">
                <div class="dashboard-panel__header">
                    <div><p class="eyebrow">Community</p><h2>Game Pulse</h2></div>
                </div>
                <div class="pulse-list">
                    <p><strong><?= (int) $pulseStats['players'] ?></strong><span>players registered</span></p>
                    <p><strong><?= (int) $pulseStats['paid'] ?></strong><span>paid entries</span></p>
                    <p><strong><?= htmlspecialchars((string) $pulseStats['top_pick']) ?></strong><span>most backed winner</span></p>
                    <p><strong>£<?= number_format((float) $pulseStats['prize_fund'], 2) ?></strong><span>prize fund</span></p>
                </div>
                <div class="dashboard-charity-card">
                    <div class="dashboard-charity-card__logo"><img src="img/charity-logos/In-Aid-Of-CALM.png" alt="<?= htmlspecialchars($charity) ?> logo"></div>
                    <div class="dashboard-charity-card__content">
                        <p class="eyebrow">Charity support</p>
                        <h3>Supporting <a href="<?= htmlspecialchars($charity_url) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($charity) ?></a></h3>
                        <p class="dashboard-note mb-0">Based on the paid entries recorded so far, this year’s game has earmarked £<?= number_format((float) $pulseStats['charity_total'], 2) ?> for <?= htmlspecialchars($charity) ?>.</p>
                    </div>
                </div>
            </article>
        <?php }),
    ],
];

$dashboardLayout = hh_dashboard_load_layout($con);

mysqli_close($con);

$dashboardChartScriptPath = htmlspecialchars(($app_path_prefix ?? '') . 'vendor/chart.js/chart.umd.js', ENT_QUOTES);
$accuracyChartJson = json_encode([
    'labels' => $accuracyChartLabels,
    'values' => $accuracyChartValues,
    'colors' => $accuracyChartColors,
], JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$formChartJson = json_encode([
    'labels' => $formChartLabels,
    'values' => $formChartValues,
    'classes' => array_map(static fn(array $point): string => (string) ($point['class'] ?? 'is-mid'), $formPointMeta),
], JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

?>

<main id="main" class="main">
    <div class="page-hero page-hero--dashboard">
        <div>
            <p class="eyebrow">Matchday control room</p>
            <h1>Dashboard</h1>
            <p class="lead mb-0">Track your position, scan today’s fixtures and keep an eye on the crowd around you.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="user.php?id=<?= $_SESSION['id'] ?>"><i class="bi bi-person-lines-fill"></i> My predictions</a>
            <a class="btn btn-outline-dark" href="rankings.php"><i class="bi bi-list-ol"></i> Rankings</a>
        </div>
    </div>

    <section class="section dashboard-board" id="dashboardBoard">
        <?php foreach ($messages as $message) : ?>
            <p class="alert alert-success mb-0"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($message) ?></p>
        <?php endforeach; ?>
        <?php foreach ($errors as $error) : ?>
            <p class="alert alert-danger mb-0"><i class="bi bi-exclamation-octagon-fill"></i> <?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <?php if ($dashboardReminder) : ?>
            <div class="alert alert-<?= htmlspecialchars($dashboardReminder['type']) ?> d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3" role="alert">
                <div>
                    <strong><?= htmlspecialchars($dashboardReminder['title']) ?></strong><br>
                    <span><?= htmlspecialchars($dashboardReminder['body']) ?></span>
                </div>
                <a class="btn btn-sm btn-outline-dark" href="<?= htmlspecialchars($dashboardReminder['href']) ?>"><?= htmlspecialchars($dashboardReminder['cta']) ?></a>
            </div>
        <?php endif; ?>

        <?php if ($isDashboardAdmin) : ?>
            <div class="dashboard-layout-toolbar<?= $dashboardEditMode ? ' is-active' : '' ?>">
                <div>
                    <strong>Dashboard layout</strong>
                    <span class="dashboard-note"><?= $dashboardEditMode ? 'Drag cards, open the cog for width/visibility, then save.' : 'Edit mode is admin-only and changes the standard layout for everyone.' ?></span>
                </div>
                <div class="dashboard-layout-toolbar__actions">
                    <?php if ($dashboardEditMode) : ?>
                        <form method="post" id="dashboardLayoutSaveForm">
                            <input type="hidden" name="dashboard_action" value="save_dashboard_layout">
                            <div id="dashboardLayoutInputs"></div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Save layout</button>
                        </form>
                        <a class="btn btn-outline-dark" href="dashboard.php"><i class="bi bi-x-circle"></i> Exit edit mode</a>
                    <?php else : ?>
                        <a class="btn btn-outline-dark" href="dashboard.php?dashboard_edit=1"><i class="bi bi-grid-3x3-gap"></i> Edit layout</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="dashboard-layout" id="dashboardLayout" data-edit-mode="<?= $dashboardEditMode ? '1' : '0' ?>">
            <?php foreach ($dashboardLayout as $layoutCard) : ?>
                <?php
                $cardKey = (string) ($layoutCard['card_key'] ?? '');
                if (!isset($dashboardLayoutCards[$cardKey])) {
                    continue;
                }
                $cardWidth = (string) ($layoutCard['width'] ?? 'normal');
                $cardVisible = (int) ($layoutCard['visible'] ?? 1) === 1;
                if (!$dashboardEditMode && !$cardVisible) {
                    continue;
                }
                ?>
                <section
                    class="dashboard-layout-card<?= $cardWidth === 'wide' ? ' dashboard-layout-card--wide' : '' ?><?= !$cardVisible ? ' is-hidden-card' : '' ?><?= $dashboardEditMode ? ' is-editable' : '' ?>"
                    data-card-key="<?= htmlspecialchars($cardKey, ENT_QUOTES) ?>"
                    data-card-width="<?= htmlspecialchars($cardWidth, ENT_QUOTES) ?>"
                    data-card-visible="<?= $cardVisible ? '1' : '0' ?>"
                >
                    <?php if ($dashboardEditMode) : ?>
                        <div class="dashboard-layout-card__chrome">
                            <button class="dashboard-layout-card__handle" type="button" draggable="true" aria-label="Drag <?= htmlspecialchars($dashboardLayoutCards[$cardKey]['label'], ENT_QUOTES) ?>"><i class="bi bi-grip-vertical"></i></button>
                            <button class="dashboard-layout-card__settings-toggle" type="button" data-layout-settings-toggle aria-expanded="false" aria-label="Edit <?= htmlspecialchars($dashboardLayoutCards[$cardKey]['label'], ENT_QUOTES) ?> settings"><i class="bi bi-gear"></i></button>
                        </div>
                        <div class="dashboard-layout-card__settings" hidden>
                            <label>
                                <span>Width</span>
                                <select data-layout-width>
                                    <option value="normal"<?= $cardWidth === 'normal' ? ' selected' : '' ?>>Normal</option>
                                    <option value="wide"<?= $cardWidth === 'wide' ? ' selected' : '' ?>>Wide</option>
                                </select>
                            </label>
                            <label class="dashboard-layout-card__visibility">
                                <input type="checkbox" data-layout-visible value="1"<?= $cardVisible ? ' checked' : '' ?>>
                                <span>Visible</span>
                            </label>
                        </div>
                    <?php endif; ?>
                    <?= $dashboardLayoutCards[$cardKey]['markup'] ?>
                </section>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<script src="<?= $dashboardChartScriptPath ?>"></script>
<script>
  (function () {
    const searchInput = document.getElementById('miniLeagueSearch');
    const optionsWrap = document.getElementById('miniLeagueOptions');
    const countNode = document.getElementById('miniLeagueCount');
    const form = document.getElementById('dashboardMiniLeagueForm');

    if (!searchInput || !optionsWrap || !countNode || !form) {
      return;
    }

    const checkboxes = Array.from(optionsWrap.querySelectorAll('.mini-league-option__checkbox'));

    function refreshCount() {
      const checked = checkboxes.filter((checkbox) => checkbox.checked).length;
      countNode.textContent = `${checked} of 8 chosen`;
    }

    function filterOptions() {
      const query = searchInput.value.trim().toLowerCase();
      const options = Array.from(optionsWrap.querySelectorAll('.mini-league-option'));

      options.forEach((option) => {
        const playerName = option.getAttribute('data-player-name') || '';
        option.hidden = query !== '' && !playerName.includes(query);
      });
    }

    checkboxes.forEach((checkbox) => {
      checkbox.addEventListener('change', function () {
        const checked = checkboxes.filter((item) => item.checked);
        if (checked.length > 8) {
          this.checked = false;
          refreshCount();
          return;
        }
        refreshCount();
      });
    });

    searchInput.addEventListener('input', filterOptions);
    refreshCount();
  })();

  (function () {
    if (typeof Chart === 'undefined') {
      return;
    }

    const accuracyCanvas = document.getElementById('dashboardAccuracyChart');
    const formCanvas = document.getElementById('dashboardFormChart');
    const accuracyData = <?= $accuracyChartJson ?>;
    const formData = <?= $formChartJson ?>;

    if (accuracyCanvas && Array.isArray(accuracyData.values)) {
      new Chart(accuracyCanvas, {
        type: 'doughnut',
        data: {
          labels: accuracyData.labels,
          datasets: [{
            data: accuracyData.values,
            backgroundColor: accuracyData.colors,
            borderWidth: 0,
            hoverOffset: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '68%',
          animation: {
            duration: 700,
            easing: 'easeOutQuart'
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#16231d',
              titleColor: '#ffffff',
              bodyColor: '#ffffff',
              padding: 10,
              displayColors: true
            }
          }
        }
      });
    }

    if (formCanvas && Array.isArray(formData.values) && formData.values.length > 0) {
      const pointColors = formData.classes.map((pointClass) => {
        if (pointClass === 'is-strong') {
          return '#0c5a43';
        }
        if (pointClass === 'is-weak') {
          return '#d64045';
        }
        return '#8f66d8';
      });

      new Chart(formCanvas, {
        type: 'line',
        data: {
          labels: formData.labels,
          datasets: [{
            data: formData.values,
            borderColor: '#8f66d8',
            borderWidth: 3,
            tension: 0.32,
            fill: false,
            pointRadius: 5,
            pointHoverRadius: 6,
            pointBorderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: pointColors
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            duration: 800,
            easing: 'easeOutQuart'
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#16231d',
              titleColor: '#ffffff',
              bodyColor: '#ffffff',
              padding: 10,
              callbacks: {
                title(items) {
                  const item = items[0];
                  return `Match ${item.label}`;
                },
                label(context) {
                  const value = Number(context.parsed.y || 0);
                  return `${value} point${value === 1 ? '' : 's'}`;
                }
              }
            }
          },
          scales: {
            x: {
              title: {
                display: true,
                text: 'Match Number',
                color: '#59635f',
                font: {
                  size: 11,
                  weight: '700'
                }
              },
              grid: {
                display: false
              },
              ticks: {
                color: '#16231d',
                font: {
                  size: 11,
                  weight: '800'
                }
              },
              border: {
                color: 'rgba(22, 35, 29, 0.16)'
              }
            },
            y: {
              min: 0,
              max: 8,
              ticks: {
                stepSize: 1,
                color: '#59635f',
                font: {
                  size: 10,
                  weight: '700'
                }
              },
              title: {
                display: true,
                text: 'Points',
                color: '#59635f',
                font: {
                  size: 11,
                  weight: '700'
                }
              },
              grid: {
                color: 'rgba(22, 35, 29, 0.10)'
              },
              border: {
                color: 'rgba(22, 35, 29, 0.16)'
              }
            }
          }
        }
      });
    }
  })();

  (function () {
    const layout = document.getElementById('dashboardLayout');
    const saveForm = document.getElementById('dashboardLayoutSaveForm');
    const inputsWrap = document.getElementById('dashboardLayoutInputs');
    const editMode = layout ? layout.getAttribute('data-edit-mode') === '1' : false;

    if (!layout || !editMode) {
      return;
    }

    const cards = () => Array.from(layout.querySelectorAll('.dashboard-layout-card'));
    let draggedCard = null;

    cards().forEach((card) => {
      const handle = card.querySelector('.dashboard-layout-card__handle');
      const settingsToggle = card.querySelector('[data-layout-settings-toggle]');
      const settingsPanel = card.querySelector('.dashboard-layout-card__settings');
      const widthSelect = card.querySelector('[data-layout-width]');
      const visibleInput = card.querySelector('[data-layout-visible]');

      if (handle) {
        handle.addEventListener('dragstart', () => {
          draggedCard = card;
          card.classList.add('is-dragging');
        });

        handle.addEventListener('dragend', () => {
          card.classList.remove('is-dragging');
          draggedCard = null;
        });
      }

      card.addEventListener('dragover', (event) => {
        event.preventDefault();
        if (!draggedCard || draggedCard === card) {
          return;
        }
        const bounds = card.getBoundingClientRect();
        const insertAfter = event.clientY > bounds.top + (bounds.height / 2);
        layout.insertBefore(draggedCard, insertAfter ? card.nextElementSibling : card);
      });

      if (settingsToggle && settingsPanel) {
        settingsToggle.addEventListener('click', () => {
          const isOpen = !settingsPanel.hidden;
          settingsPanel.hidden = isOpen;
          settingsToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        });
      }

      if (widthSelect) {
        widthSelect.addEventListener('change', () => {
          card.dataset.cardWidth = widthSelect.value;
          card.classList.toggle('dashboard-layout-card--wide', widthSelect.value === 'wide');
        });
      }

      if (visibleInput) {
        visibleInput.addEventListener('change', () => {
          card.dataset.cardVisible = visibleInput.checked ? '1' : '0';
          card.classList.toggle('is-hidden-card', !visibleInput.checked);
        });
      }
    });

    if (saveForm && inputsWrap) {
      saveForm.addEventListener('submit', () => {
        inputsWrap.innerHTML = '';
        cards().forEach((card) => {
          const key = card.getAttribute('data-card-key');
          const width = card.dataset.cardWidth || 'normal';
          const visible = card.dataset.cardVisible === '1';

          const orderInput = document.createElement('input');
          orderInput.type = 'hidden';
          orderInput.name = 'layout_order[]';
          orderInput.value = key || '';
          inputsWrap.appendChild(orderInput);

          const widthInput = document.createElement('input');
          widthInput.type = 'hidden';
          widthInput.name = `layout_width[${key}]`;
          widthInput.value = width;
          inputsWrap.appendChild(widthInput);

          if (visible) {
            const visibleInput = document.createElement('input');
            visibleInput.type = 'hidden';
            visibleInput.name = `layout_visible[${key}]`;
            visibleInput.value = '1';
            inputsWrap.appendChild(visibleInput);
          }
        });
      });
    }
  })();
</script>

<?php include "php/footer.php" ?>
