<?php
session_start();
$page_title = 'Overview';

require_once __DIR__ . '/php/auth.php';
hh_require_admin('dashboard.php');

include "php/header.php";
include "php/navigation.php";

include 'php/db-connect.php';

$databaseName = $database ?? '';
$serverHost = $server ?? '';
$mysqlInfo = mysqli_get_server_info($con);
$effectiveTodayLabel = hh_effective_today_label('j F Y');
$effectiveNowUtc = hh_effective_now(new DateTimeZone('UTC'));

if (!function_exists('hh_overview_table_exists')) {
    function hh_overview_table_exists(mysqli $con, string $tableName): bool
    {
        $safeTable = mysqli_real_escape_string($con, $tableName);
        $result = mysqli_query($con, "SHOW TABLES LIKE '{$safeTable}'");
        if (!$result instanceof mysqli_result) {
            return false;
        }

        $exists = mysqli_num_rows($result) > 0;
        mysqli_free_result($result);
        return $exists;
    }
}

if (!function_exists('hh_overview_format_datetime')) {
    function hh_overview_format_datetime(?string $dateValue, ?string $timeValue = null, string $fallback = 'Not available'): string
    {
        $dateValue = trim((string) $dateValue);
        $timeValue = trim((string) $timeValue);

        if ($dateValue === '') {
            return $fallback;
        }

        $format = $timeValue !== '' ? 'Y-m-d H:i' : 'Y-m-d';
        $input = $timeValue !== '' ? $dateValue . ' ' . $timeValue : $dateValue;
        $date = DateTimeImmutable::createFromFormat($format, $input, new DateTimeZone('UTC'));

        if (!$date instanceof DateTimeImmutable) {
            return $fallback;
        }

        return $timeValue !== ''
            ? $date->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('D j M Y, g:ia')
            : $date->format('D j M Y');
    }
}

$tableExists = [
    'users' => hh_overview_table_exists($con, 'live_user_information'),
    'schedule' => hh_overview_table_exists($con, 'live_match_schedule'),
    'results' => hh_overview_table_exists($con, 'live_match_results'),
];

$playerCount = 0;
$paidCount = 0;
$latestPlayerLabel = 'No players yet';
$latestPlayerDate = '';
$fixturesLoaded = 0;
$resultsRecorded = 0;
$scheduleStart = '';
$scheduleEnd = '';
$nextKickoffLabel = 'No fixtures loaded';
$groupCount = 0;
$rowsByMatch = [];
$stageWindows = [];
$stageRows = [];

if ($tableExists['users']) {
    $playerSummaryResult = mysqli_query(
        $con,
        "SELECT COUNT(*) AS total_players, SUM(CASE WHEN haspaid = 'Yes' THEN 1 ELSE 0 END) AS paid_players FROM live_user_information"
    );
    if ($playerSummaryResult instanceof mysqli_result) {
        $playerSummary = mysqli_fetch_assoc($playerSummaryResult) ?: [];
        $playerCount = (int) ($playerSummary['total_players'] ?? 0);
        $paidCount = (int) ($playerSummary['paid_players'] ?? 0);
        mysqli_free_result($playerSummaryResult);
    }

    $latestPlayerResult = mysqli_query(
        $con,
        "SELECT firstname, surname, signupdate FROM live_user_information ORDER BY signupdate DESC, id DESC LIMIT 1"
    );
    if ($latestPlayerResult instanceof mysqli_result) {
        $latestPlayer = mysqli_fetch_assoc($latestPlayerResult) ?: null;
        if ($latestPlayer) {
            $latestPlayerLabel = trim((string) ($latestPlayer['firstname'] ?? '') . ' ' . (string) ($latestPlayer['surname'] ?? ''));
            $latestPlayerDate = !empty($latestPlayer['signupdate'])
                ? date('j M Y', strtotime((string) $latestPlayer['signupdate']))
                : '';
        }
        mysqli_free_result($latestPlayerResult);
    }
}

if ($tableExists['schedule']) {
    $scheduleSummaryResult = mysqli_query(
        $con,
        "SELECT COUNT(*) AS fixtures_loaded, MIN(date) AS start_date, MAX(date) AS end_date, COUNT(DISTINCT stage) AS stage_count FROM live_match_schedule"
    );
    if ($scheduleSummaryResult instanceof mysqli_result) {
        $scheduleSummary = mysqli_fetch_assoc($scheduleSummaryResult) ?: [];
        $fixturesLoaded = (int) ($scheduleSummary['fixtures_loaded'] ?? 0);
        $scheduleStart = (string) ($scheduleSummary['start_date'] ?? '');
        $scheduleEnd = (string) ($scheduleSummary['end_date'] ?? '');
        $groupCount = (int) ($scheduleSummary['stage_count'] ?? 0);
        mysqli_free_result($scheduleSummaryResult);
    }

    $nextKickoffResult = mysqli_query(
        $con,
        "SELECT date, kotime, hometeam, awayteam
         FROM live_match_schedule
         WHERE CONCAT(date, ' ', kotime) >= '" . mysqli_real_escape_string($con, $effectiveNowUtc->format('Y-m-d H:i:s')) . "'
         ORDER BY date ASC, kotime ASC, match_number ASC
         LIMIT 1"
    );
    if ($nextKickoffResult instanceof mysqli_result) {
        $nextKickoff = mysqli_fetch_assoc($nextKickoffResult) ?: null;
        if ($nextKickoff) {
            $nextKickoffLabel = trim(
                hh_overview_format_datetime((string) ($nextKickoff['date'] ?? ''), (string) ($nextKickoff['kotime'] ?? ''))
                . ' · '
                . (string) ($nextKickoff['hometeam'] ?? '')
                . ' v '
                . (string) ($nextKickoff['awayteam'] ?? '')
            );
        }
        mysqli_free_result($nextKickoffResult);
    }

    $groupCountResult = mysqli_query(
        $con,
        "SELECT COUNT(DISTINCT stage) AS group_count FROM live_match_schedule WHERE stage LIKE 'Group %'"
    );
    if ($groupCountResult instanceof mysqli_result) {
        $groupCountRow = mysqli_fetch_assoc($groupCountResult) ?: [];
        $groupCount = (int) ($groupCountRow['group_count'] ?? $groupCount);
        mysqli_free_result($groupCountResult);
    }

    $scheduleRowsResult = mysqli_query(
        $con,
        "SELECT match_number, stage, date, kotime FROM live_match_schedule ORDER BY match_number ASC"
    );
    if ($scheduleRowsResult instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($scheduleRowsResult)) {
            $rowsByMatch[(int) ($row['match_number'] ?? 0)] = $row;
        }
        mysqli_free_result($scheduleRowsResult);
    }

    $stageWindows = hh_prediction_stage_windows($con);
}

if ($tableExists['results']) {
    $resultsResult = mysqli_query($con, "SELECT * FROM live_match_results ORDER BY match_id DESC LIMIT 1");
    if ($resultsResult instanceof mysqli_result) {
        $resultsRow = mysqli_fetch_assoc($resultsResult) ?: [];
        $nonNullScoreColumns = 0;
        foreach ($resultsRow as $column => $value) {
            if (preg_match('/^score\d+_r$/', (string) $column) && $value !== null && $value !== '') {
                $nonNullScoreColumns++;
            }
        }
        $resultsRecorded = (int) floor($nonNullScoreColumns / 2);
        mysqli_free_result($resultsResult);
    }
}

foreach (hh_stage_blueprint() as $stage) {
    $context = hh_prediction_stage_contexts()[$stage['key']] ?? null;
    $window = $stageWindows[$stage['key']] ?? null;
    $status = $window['status'] ?? 'pending';
    $stageRows[] = [
        'label' => (string) ($stage['label'] ?? ''),
        'fixtures' => (int) ($stage['fixtures'] ?? 0),
        'match_range' => $context ? $context['fixture_start'] . '-' . $context['fixture_end'] : 'Not available',
        'status' => $status,
        'opens_at' => $window && $window['opens_at'] instanceof DateTimeImmutable
            ? $window['opens_at']->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('D j M, g:ia')
            : ($stage['key'] === 'groups' ? 'Open from setup' : 'Awaiting schedule'),
        'closes_at' => $window && $window['closes_at'] instanceof DateTimeImmutable
            ? $window['closes_at']->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('D j M, g:ia')
            : 'Awaiting schedule',
    ];
}

$competitionWindowLabel = ($scheduleStart !== '' || $scheduleEnd !== '')
    ? hh_overview_format_datetime($scheduleStart, null) . ' to ' . hh_overview_format_datetime($scheduleEnd, null)
    : 'Waiting for schedule import';

$previewModeLabel = hh_is_preview_mode()
    ? 'Preview mode on · system date treated as ' . $effectiveTodayLabel
    : 'Live mode · using the current server date';

mysqli_close($con);
?>

<!-- Main Content Section -->
<main id="main" class="main">

    <div class="page-hero page-hero--overview">
		<div>
			<p class="eyebrow">Application overview</p>
			<h1>Game Overview</h1>
			<p class="lead mb-0">A live snapshot of the tournament setup, database state and what players can currently do.</p>
		</div>
    </div><!-- End Page Title -->

    <section class="section overview-page">
        <div class="overview-stat-grid">
            <article class="overview-stat-card">
                <span class="overview-stat-card__label">Players registered</span>
                <strong><?= htmlspecialchars((string) $playerCount) ?></strong>
                <p><?= htmlspecialchars((string) $paidCount) ?> paid and ready to play.</p>
            </article>
            <article class="overview-stat-card">
                <span class="overview-stat-card__label">Fixtures loaded</span>
                <strong><?= htmlspecialchars((string) $fixturesLoaded) ?></strong>
                <p><?= htmlspecialchars((string) $resultsRecorded) ?> fixtures now have recorded results.</p>
            </article>
            <article class="overview-stat-card">
                <span class="overview-stat-card__label">Next kickoff</span>
                <strong><?= $nextKickoffLabel === 'No fixtures loaded' ? 'Waiting' : 'Scheduled' ?></strong>
                <p><?= htmlspecialchars($nextKickoffLabel) ?></p>
            </article>
            <article class="overview-stat-card">
                <span class="overview-stat-card__label">System date</span>
                <strong><?= htmlspecialchars($effectiveTodayLabel) ?></strong>
                <p><?= htmlspecialchars($previewModeLabel) ?></p>
            </article>
        </div>

        <div class="overview-grid">
            <article class="overview-panel">
                <div class="overview-panel__header">
                    <p class="eyebrow">Tournament</p>
                    <h2>Competition Snapshot</h2>
                </div>
                <table class="table overview-table">
                    <tr>
                        <th scope="row">Competition</th>
                        <td><a href="<?= htmlspecialchars($competition_url, ENT_QUOTES) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($competition) ?></a></td>
                    </tr>
                    <tr>
                        <th scope="row">Host nations</th>
                        <td><?= htmlspecialchars($competition_location) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Schedule window</th>
                        <td><?= htmlspecialchars($competitionWindowLabel) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Fixtures breakdown</th>
                        <td>
                            <?= htmlspecialchars((string) $no_of_total_fixtures) ?> total:
                            <?= htmlspecialchars((string) $no_of_group_fixtures) ?> group,
                            <?= htmlspecialchars((string) $no_of_ro32_fixtures) ?> Round of 32,
                            <?= htmlspecialchars((string) $no_of_ro16_fixtures) ?> Round of 16,
                            <?= htmlspecialchars((string) $no_of_qf_fixtures) ?> quarter-finals,
                            <?= htmlspecialchars((string) $no_of_sf_fixtures) ?> semi-finals,
                            <?= htmlspecialchars((string) $no_of_final_fixtures) ?> final stage.
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Groups in schedule</th>
                        <td><?= htmlspecialchars((string) $groupCount) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Entry fee</th>
                        <td>&pound;<?= htmlspecialchars($signup_fee_formatted) ?> per player</td>
                    </tr>
                    <tr>
                        <th scope="row">Signup closes</th>
                        <td><?= htmlspecialchars($signup_close_date) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Charity support</th>
                        <td><a href="<?= htmlspecialchars($charity_url, ENT_QUOTES) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($charity) ?></a> receives &pound;<?= htmlspecialchars($charity_fee_formatted) ?> from each entry.</td>
                    </tr>
                </table>
            </article>

            <article class="overview-panel">
                <div class="overview-panel__header">
                    <p class="eyebrow">Platform</p>
                    <h2>System Snapshot</h2>
                </div>
                <table class="table overview-table">
                    <tr>
                        <th scope="row">Title</th>
                        <td><?= htmlspecialchars($title) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Version</th>
                        <td><?= htmlspecialchars($version) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Developer</th>
                        <td><?= htmlspecialchars($developer) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Base URL</th>
                        <td><?= htmlspecialchars($base_url) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Database</th>
                        <td><?= htmlspecialchars($databaseName !== '' ? $databaseName : 'Not configured') ?></td>
                    </tr>
                    <tr>
                        <th scope="row">MySQL host</th>
                        <td><?= htmlspecialchars($serverHost !== '' ? $serverHost : 'Not configured') ?></td>
                    </tr>
                    <tr>
                        <th scope="row">MySQL version</th>
                        <td><?= htmlspecialchars($mysqlInfo) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Latest player</th>
                        <td><?= htmlspecialchars($latestPlayerLabel . ($latestPlayerDate !== '' ? ' · joined ' . $latestPlayerDate : '')) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Key tables</th>
                        <td>
                            Users <?= $tableExists['users'] ? 'ready' : 'missing' ?>,
                            schedule <?= $tableExists['schedule'] ? 'ready' : 'missing' ?>,
                            results <?= $tableExists['results'] ? 'ready' : 'missing' ?>.
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Preview mode</th>
                        <td><?= htmlspecialchars($previewModeLabel) ?></td>
                    </tr>
                </table>
            </article>
        </div>

        <article class="overview-panel">
            <div class="overview-panel__header">
                <p class="eyebrow">Prediction windows</p>
                <h2>Stage Access</h2>
                <p class="overview-panel__intro mb-0">Players can only submit predictions while a stage is open. Each stage closes two hours before its first kickoff.</p>
            </div>
            <div class="overview-stage-grid">
                <?php foreach ($stageRows as $stageRow) : ?>
                    <div class="overview-stage-card overview-stage-card--<?= htmlspecialchars($stageRow['status']) ?>">
                        <div class="overview-stage-card__top">
                            <h3><?= htmlspecialchars($stageRow['label']) ?></h3>
                            <span class="overview-stage-pill overview-stage-pill--<?= htmlspecialchars($stageRow['status']) ?>">
                                <?= htmlspecialchars(hh_stage_status_label((string) $stageRow['status'])) ?>
                            </span>
                        </div>
                        <p><?= htmlspecialchars((string) $stageRow['fixtures']) ?> fixtures · matches <?= htmlspecialchars($stageRow['match_range']) ?></p>
                        <dl>
                            <div>
                                <dt>Opens</dt>
                                <dd><?= htmlspecialchars($stageRow['opens_at']) ?></dd>
                            </div>
                            <div>
                                <dt>Closes</dt>
                                <dd><?= htmlspecialchars($stageRow['closes_at']) ?></dd>
                            </div>
                        </dl>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

</main>

<!-- Footer -->
<?php include "php/footer.php" ?>
