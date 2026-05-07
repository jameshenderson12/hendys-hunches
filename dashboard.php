<?php
session_start();
$page_title = 'Dashboard';

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/config.php';
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
                'class' => 'concept-up',
                'icon' => 'bi bi-caret-up-fill',
            ];
        }

        if ($lastPos < $currentPos) {
            $diff = $currentPos - $lastPos;
            return [
                'diff' => -$diff,
                'label' => '-' . $diff,
                'class' => 'concept-down',
                'icon' => 'bi bi-caret-down-fill',
            ];
        }

        return [
            'diff' => 0,
            'label' => '0',
            'class' => 'concept-neutral',
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

$stageContexts = hh_prediction_stage_contexts();
$sessionUserId = (int) ($_SESSION['id'] ?? 0);
$todayFixtures = [];
$effectiveToday = hh_effective_today_sql();
$effectiveTodayLabel = hh_effective_today_label('D j M Y');
$stageWindows = hh_prediction_stage_windows($con);
$dashboardReminder = null;
$stagePoints = [];
$winnerPicks = [];
$accuracy = [
    ['label' => 'Exact scores', 'value' => 0],
    ['label' => 'Right outcome', 'value' => 0],
    ['label' => 'One-score hits', 'value' => 0],
    ['label' => 'Misses', 'value' => 0],
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
$closestRivalSummary = [
    'ahead' => null,
    'behind' => null,
    'count' => 0,
];

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
            'rank_label' => hh_dashboard_ordinal($currentPos),
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

$winnerPicksResult = mysqli_query(
    $con,
    "SELECT tournwinner, COUNT(*) AS pick_count
     FROM live_user_information
     WHERE tournwinner <> ''
     GROUP BY tournwinner
     ORDER BY pick_count DESC, tournwinner ASC
     LIMIT 5"
);
if ($winnerPicksResult instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($winnerPicksResult)) {
        $count = (int) ($row['pick_count'] ?? 0);
        $winnerPicks[] = [
            'team' => trim((string) ($row['tournwinner'] ?? '')),
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
    foreach ($rankingRows as $index => $player) {
        if ($player['id'] === $currentUser['id']) {
            $sliceStart = max(0, $index - 2);
            $miniLeagueRows = array_slice($rankingRows, $sliceStart, 5);
            $closestRivalSummary['ahead'] = $rankingRows[$index - 1] ?? null;
            $closestRivalSummary['behind'] = $rankingRows[$index + 1] ?? null;
            break;
        }
    }

    if (empty($miniLeagueRows)) {
        $miniLeagueRows = array_slice($rankingRows, 0, 5);
    }

    $closestRivalSummary['count'] = count($miniLeagueRows);
}

$momentumRows = $rankingRows;
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

if ($currentUser && !empty($rankingRows)) {
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
    $allResultFields = [];
    $predictionByScore = [];

    foreach ($resultScoreColumns as $scoreIndex => $fieldName) {
        $allResultFields[] = "SUM({$fieldName}) AS {$fieldName}";
    }

    $resultScores = null;
    if (!empty($allResultFields)) {
        $resultsQuery = mysqli_query($con, "SELECT " . implode(', ', $allResultFields) . " FROM live_match_results");
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

                if ($predHome === $resHome && $predAway === $resAway) {
                    $accuracy[0]['value']++;
                    continue;
                }

                $sameOutcome =
                    (($predHome > $predAway) && ($resHome > $resAway))
                    || (($predHome < $predAway) && ($resHome < $resAway))
                    || (($predHome === $predAway) && ($resHome === $resAway));

                if ($sameOutcome) {
                    $accuracy[1]['value']++;
                    continue;
                }

                if ($predHome === $resHome || $predAway === $resAway) {
                    $accuracy[2]['value']++;
                    continue;
                }

                $accuracy[3]['value']++;
            }
        }
    }
}

mysqli_close($con);

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
        <?php if ($dashboardReminder) : ?>
            <div class="alert alert-<?= htmlspecialchars($dashboardReminder['type']) ?> d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3" role="alert">
                <div>
                    <strong><?= htmlspecialchars($dashboardReminder['title']) ?></strong><br>
                    <span><?= htmlspecialchars($dashboardReminder['body']) ?></span>
                </div>
                <a class="btn btn-sm btn-outline-dark" href="<?= htmlspecialchars($dashboardReminder['href']) ?>"><?= htmlspecialchars($dashboardReminder['cta']) ?></a>
            </div>
        <?php endif; ?>

        <div class="concept-grid concept-grid--top">
            <article class="concept-profile-card">
                <div class="concept-profile-card__kit">
                    <img src="<?= htmlspecialchars((string) ($currentUser['avatar'] ?? 'img/hh-icon-2024.png')) ?>" alt="<?= htmlspecialchars((string) ($currentUser['name'] ?? 'Player')) ?> football strip avatar">
                </div>
                <div class="concept-profile-card__body">
                    <p class="eyebrow">Player card</p>
                    <h2><?= htmlspecialchars((string) ($currentUser['name'] ?? 'Preview player')) ?></h2>
                    <p class="concept-subtle">
                        <?= htmlspecialchars((string) (($currentUser['haspaid'] ?? 'No') === 'Yes' ? 'Entry fee paid' : 'Entry fee pending')) ?>
                        <?php if (!empty($currentUser['signupdate'])) : ?>
                            · signed up <?= htmlspecialchars(date('j F Y', strtotime((string) $currentUser['signupdate']))) ?>
                        <?php endif; ?>
                    </p>
                    <div class="concept-profile-stats">
                        <span><strong><?= htmlspecialchars((string) ($currentUser['rank_label'] ?? 'N/A')) ?></strong>Rank</span>
                        <span><strong><?= htmlspecialchars((string) ($currentUser['points_total'] ?? 0)) ?></strong>Points</span>
                        <span><strong><?= htmlspecialchars((string) ($currentUser['move']['label'] ?? '0')) ?></strong>Move</span>
                    </div>
                    <dl class="concept-profile-details">
                        <div>
                            <dt>Favourite club</dt>
                            <dd><?= htmlspecialchars((string) ($currentUser['faveteam'] ?? 'Not set')) ?></dd>
                        </div>
                        <div>
                            <dt>Tournament winner</dt>
                            <dd><?= htmlspecialchars((string) ($currentUser['tournwinner'] ?? 'Not set')) ?></dd>
                        </div>
                        <div>
                            <dt>Location</dt>
                            <dd><?= htmlspecialchars((string) ($currentUser['location'] ?? 'Not set')) ?></dd>
                        </div>
                        <div>
                            <dt>Field</dt>
                            <dd><?= htmlspecialchars((string) ($currentUser['fieldofwork'] ?? 'Not set')) ?></dd>
                        </div>
                    </dl>
                </div>
            </article>

            <article class="concept-panel concept-panel--wide">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Matchday card</p>
                        <h2>Today's Fixtures</h2>
                        <p class="dashboard-subtle mb-0"><?= htmlspecialchars($effectiveTodayLabel) ?></p>
                    </div>
                    <span class="concept-pill"><?= count($todayFixtures) ?> games</span>
                </div>
                <div class="fixture-stack">
                    <?php if (!empty($todayFixtures)) : ?>
                        <?php foreach ($todayFixtures as $fixture) : ?>
                            <article class="fixture-card-row">
                                <div class="fixture-card-row__meta">
                                    <span class="fixture-card-row__time"><?= htmlspecialchars($fixture['time']) ?></span>
                                </div>
                                <div class="fixture-card-row__match">
                                    <div class="fixture-card-row__team">
                                        <img src="<?= htmlspecialchars($fixture['home_flag']) ?>" alt="<?= htmlspecialchars($fixture['home']) ?> flag">
                                        <div>
                                            <strong><?= htmlspecialchars($fixture['home']) ?></strong>
                                            <span><?= htmlspecialchars($fixture['home_avg']) ?></span>
                                        </div>
                                    </div>
                                    <div class="fixture-card-row__divider">vs</div>
                                    <div class="fixture-card-row__team fixture-card-row__team--away">
                                        <img src="<?= htmlspecialchars($fixture['away_flag']) ?>" alt="<?= htmlspecialchars($fixture['away']) ?> flag">
                                        <div>
                                            <strong><?= htmlspecialchars($fixture['away']) ?></strong>
                                            <span><?= htmlspecialchars($fixture['away_avg']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <p class="fixture-card-row__pick"><?= htmlspecialchars($fixture['pick']) ?> · <?= htmlspecialchars($fixture['away_avg']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="concept-subtle mb-0">No fixtures are scheduled for the selected day.</p>
                    <?php endif; ?>
                </div>
                <p class="concept-subtle mb-0">In preview mode, changing the configured preview day will change this card to show that date’s fixtures.</p>
            </article>
        </div>

        <div class="concept-grid concept-grid--insights">
            <article class="concept-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Prize race</p>
                        <h2><?= htmlspecialchars((string) ($prizeRace['title'] ?? 'Prize places')) ?></h2>
                    </div>
                    <span class="concept-pill concept-pill--gold"><?= htmlspecialchars((string) (($prizeRace['gap'] ?? 0) . ' pts')) ?></span>
                </div>
                <div class="race-card">
                    <div>
                        <span><?= htmlspecialchars((string) ($prizeRace['top']['label'] ?? 'Target')) ?></span>
                        <strong><?= htmlspecialchars((string) ($prizeRace['top']['name'] ?? 'Waiting for standings')) ?></strong>
                        <small><?= htmlspecialchars((string) (($prizeRace['top']['points'] ?? 0) . ' pts')) ?></small>
                    </div>
                    <i class="bi bi-arrow-down"></i>
                    <div>
                        <span><?= htmlspecialchars((string) ($prizeRace['bottom']['label'] ?? 'You')) ?></span>
                        <strong><?= htmlspecialchars((string) ($prizeRace['bottom']['name'] ?? ($currentUser['rank_label'] ?? 'N/A'))) ?></strong>
                        <small><?= htmlspecialchars((string) (($prizeRace['bottom']['points'] ?? ($currentUser['points_total'] ?? 0)) . ' pts')) ?></small>
                    </div>
                </div>
            </article>

            <article class="concept-panel concept-panel--wide">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Personal form</p>
                        <h2>Points by Stage</h2>
                    </div>
                    <span class="concept-pill"><?= htmlspecialchars((string) ($currentUser['points_total'] ?? 0)) ?> pts</span>
                </div>
                <div class="stage-bars">
                    <?php foreach ($stagePoints as $stage) : ?>
                        <?php $width = $stage['max'] > 0 ? round(($stage['points'] / $stage['max']) * 100) : 0; ?>
                        <div class="stage-bar">
                            <div class="stage-bar__meta">
                                <span><?= $stage['label'] ?></span>
                                <strong><?= $stage['points'] ?> / <?= $stage['max'] ?></strong>
                            </div>
                            <div class="stage-bar__track">
                                <span style="width: <?= $width ?>%"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="concept-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Prediction quality</p>
                        <h2>Accuracy Breakdown</h2>
                    </div>
                </div>
                <div class="accuracy-donut" aria-label="Accuracy breakdown">
                    <div class="accuracy-donut__ring">
                        <div>
                            <strong><?= (int) $accuracy[0]['value'] + (int) $accuracy[1]['value'] + (int) $accuracy[2]['value'] ?></strong>
                            <span>scoring picks</span>
                        </div>
                    </div>
                    <ul class="accuracy-list">
                        <?php foreach ($accuracy as $item) : ?>
                            <li><span><?= $item['label'] ?></span><strong><?= $item['value'] ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </article>

            <article class="concept-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Crowd read</p>
                        <h2>Who Everyone Backed</h2>
                    </div>
                </div>
                <div class="crowd-bars">
                    <?php if (!empty($winnerPicks)) : ?>
                        <?php foreach ($winnerPicks as $pick) : ?>
                            <div class="crowd-bar">
                                <div class="crowd-bar__meta">
                                    <span><?= htmlspecialchars((string) $pick['team']) ?></span>
                                    <strong><?= htmlspecialchars((string) $pick['count']) ?></strong>
                                </div>
                                <div class="crowd-bar__track"><span style="width: <?= (int) $pick['percent'] ?>%"></span></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="concept-subtle mb-0">Winner picks will appear once players have chosen their champions.</p>
                    <?php endif; ?>
                </div>
            </article>

            <article class="concept-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Momentum</p>
                        <h2>Biggest Movers</h2>
                    </div>
                </div>
                <ol class="movement-list">
                    <?php if (!empty($momentumRows)) : ?>
                        <?php foreach ($momentumRows as $player) : ?>
                            <li>
                                <span class="<?= htmlspecialchars((string) $player['move']['class']) ?>">
                                    <i class="<?= htmlspecialchars((string) $player['move']['icon']) ?>"></i> <?= htmlspecialchars((string) $player['name']) ?>
                                </span>
                                <strong><?= htmlspecialchars((string) $player['move']['label']) ?></strong>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li><span class="concept-neutral"><i class="bi bi-dash"></i> No movement yet</span><strong>0</strong></li>
                    <?php endif; ?>
                </ol>
            </article>
        </div>

        <div class="concept-grid concept-grid--mini-league">
            <article class="concept-panel concept-panel--wide">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Closest rivals</p>
                        <h2>Mini-League Snapshot</h2>
                    </div>
                    <a class="btn btn-sm btn-outline-success" href="rankings.php"><i class="bi bi-list-ol"></i> Full table</a>
                </div>
                <div class="mini-league-table">
                    <?php if (!empty($miniLeagueRows)) : ?>
                        <?php foreach ($miniLeagueRows as $player) : ?>
                            <div class="mini-league-row<?= !empty($player['is_me']) ? ' mini-league-row--me' : '' ?>">
                                <span class="mini-league-rank"><?= htmlspecialchars((string) $player['rank']) ?></span>
                                <img class="mini-league-avatar" src="<?= htmlspecialchars((string) $player['avatar']) ?>" alt="<?= htmlspecialchars((string) $player['name']) ?> kit avatar">
                                <span class="mini-league-player">
                                    <strong><?= htmlspecialchars((string) $player['name']) ?></strong>
                                    <small><?= htmlspecialchars((string) ($player['location'] !== '' ? $player['location'] : ($player['faveteam'] !== '' ? $player['faveteam'] : 'Overall standings'))) ?></small>
                                </span>
                                <span class="mini-league-points"><?= htmlspecialchars((string) $player['points_total']) ?> pts</span>
                                <span class="mini-league-move <?= htmlspecialchars((string) $player['move']['class']) ?>"><?= htmlspecialchars((string) $player['move']['label']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="concept-subtle mb-0">Mini-league standings will appear as soon as player records are available.</p>
                    <?php endif; ?>
                </div>
            </article>

            <article class="concept-panel mini-league-summary">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Rival watch</p>
                        <h2>Close Calls</h2>
                    </div>
                </div>
                <div class="rival-watch-card">
                    <p>
                        <strong><?= $closestRivalSummary['ahead'] && $currentUser ? max(0, (int) $closestRivalSummary['ahead']['points_total'] - (int) $currentUser['points_total']) : 0 ?> pts</strong>
                        <span><?= $closestRivalSummary['ahead'] ? 'behind ' . htmlspecialchars((string) $closestRivalSummary['ahead']['name']) : 'to the next player' ?></span>
                    </p>
                    <p>
                        <strong><?= $closestRivalSummary['behind'] && $currentUser ? max(0, (int) $currentUser['points_total'] - (int) $closestRivalSummary['behind']['points_total']) : 0 ?> pts</strong>
                        <span><?= $closestRivalSummary['behind'] ? 'ahead of ' . htmlspecialchars((string) $closestRivalSummary['behind']['name']) : 'clear of the next player' ?></span>
                    </p>
                    <p><strong><?= (int) $closestRivalSummary['count'] ?></strong><span>players in view</span></p>
                </div>
                <p class="concept-subtle mb-0">Until custom opponent lists arrive, this shows the players nearest to you in the live standings.</p>
            </article>
        </div>

        <div class="concept-grid concept-grid--single">
            <article class="concept-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Community</p>
                        <h2>Game Pulse</h2>
                    </div>
                </div>
                <div class="pulse-list">
                    <p><strong><?= (int) $pulseStats['players'] ?></strong><span>players registered</span></p>
                    <p><strong><?= (int) $pulseStats['paid'] ?></strong><span>paid entries</span></p>
                    <p><strong><?= htmlspecialchars((string) $pulseStats['top_pick']) ?></strong><span>most backed winner</span></p>
                    <p><strong>£<?= number_format((float) $pulseStats['prize_fund'], 2) ?></strong><span>prize fund</span></p>
                </div>
                <div class="dashboard-charity-card">
                    <div class="dashboard-charity-card__logo">
                        <img src="img/charity-logos/In-Aid-Of-CALM.png" alt="<?= htmlspecialchars($charity) ?> logo">
                    </div>
                    <div class="dashboard-charity-card__content">
                        <p class="eyebrow">Charity support</p>
                        <h3>Supporting <a href="<?= htmlspecialchars($charity_url) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($charity) ?></a></h3>
                        <p class="concept-subtle mb-0">Based on the paid entries recorded so far, this year’s game has earmarked £<?= number_format((float) $pulseStats['charity_total'], 2) ?> for <?= htmlspecialchars($charity) ?>.</p>
                    </div>
                </div>
            </article>
        </div>
    </section>
</main>

<?php include "php/footer.php" ?>
