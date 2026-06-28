<?php
session_start();
$page_title = 'Tournament Knockouts';

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/flags.php';
hh_require_login('index.php');

include 'php/header.php';
include 'php/navigation.php';
include 'php/db-connect.php';

$stages = [];
$stageOrder = [];

$sql_stages = "SELECT stage, MIN(date) AS stage_date
               FROM live_match_schedule
               WHERE stage IN ('Round of 32', 'Round of 16', 'Quarter-Finals', 'Semi-Finals', 'Third Place Play-Off', 'Final', 'Final Stage')
               GROUP BY stage
               ORDER BY stage_date ASC, MIN(kotime) ASC";
$stageResult = mysqli_query($con, $sql_stages) or die(mysqli_error($con));

while ($row = mysqli_fetch_assoc($stageResult)) {
    $stage = trim((string) ($row['stage'] ?? ''));
    if ($stage !== '') {
        $stageOrder[] = $stage;
    }
}
mysqli_free_result($stageResult);

foreach ($stageOrder as $stage) {
    $safeStage = mysqli_real_escape_string($con, $stage);
    $fixtureResult = mysqli_query(
        $con,
        "SELECT hometeamimg, hometeam, homescore, awayscore, awayteam, awayteamimg, venue, kotime,
                DATE_FORMAT(date, '%a, %D %b') AS formatted_date
         FROM live_match_schedule
         WHERE stage = '{$safeStage}'
         ORDER BY date, kotime"
    ) or die(mysqli_error($con));

    $stages[$stage] = [];
    while ($fixture = mysqli_fetch_assoc($fixtureResult)) {
        $stages[$stage][] = $fixture;
    }
    mysqli_free_result($fixtureResult);
}

mysqli_close($con);
?>

<main id="main" class="main">
    <div class="page-hero page-hero--competition">
        <div>
            <p class="eyebrow">Competition</p>
            <h1>Tournament Knockouts</h1>
            <p class="lead mb-0">The knockout path for <?= htmlspecialchars($GLOBALS['competition']) ?>, updated after each fixture.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="tournament-groups.php"><i class="bi bi-grid-3x3-gap"></i> Groups</a>
            <a class="btn btn-outline-dark" href="rankings.php"><i class="bi bi-list-ol"></i> Rankings</a>
        </div>
    </div>

    <section class="section competition-page">
        <?php if (empty($stages)) : ?>
            <div class="competition-panel">
                <div class="row">
                    <div class="col-12">
                        <p class="mb-0 text-muted">No knockout fixtures have been loaded yet.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($stages as $stageLabel => $fixtures) : ?>
            <div class="competition-panel">
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="competition-panel__title"><?= htmlspecialchars($stageLabel) ?></h4>
                    </div>
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="d-none d-sm-table-cell">Kick-Off</th>
                                        <th>Home</th>
                                        <th class="text-center">H</th>
                                        <th class="text-center">A</th>
                                        <th class="text-end">Away</th>
                                        <th class="d-none d-sm-table-cell">Venue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fixtures as $fixture) : ?>
                                        <?php
                                        $kickoff = htmlspecialchars((string) $fixture['formatted_date']) . '<br>' . htmlspecialchars((string) $fixture['kotime']);
                                        $homeImage = htmlspecialchars((string) $fixture['hometeamimg']);
                                        $awayImage = htmlspecialchars((string) $fixture['awayteamimg']);
                                        $homeTeam = htmlspecialchars((string) $fixture['hometeam']);
                                        $awayTeam = htmlspecialchars((string) $fixture['awayteam']);
                                        $venue = htmlspecialchars((string) $fixture['venue']);
                                        $homeScore = $fixture['homescore'] !== null ? htmlspecialchars((string) $fixture['homescore']) : '&ndash;';
                                        $awayScore = $fixture['awayscore'] !== null ? htmlspecialchars((string) $fixture['awayscore']) : '&ndash;';
                                        ?>
                                        <tr>
                                            <td class="small d-none d-sm-table-cell"><?= $kickoff ?></td>
                                            <td>
                                                <img class="competition-flag" src="<?= $homeImage ?>" alt="<?= $homeTeam ?> flag">
                                                <?= hh_render_team_name_responsive((string) ($fixture['hometeam'] ?? '')) ?>
                                            </td>
                                            <td class="text-center"><strong><?= $homeScore ?></strong></td>
                                            <td class="text-center"><strong><?= $awayScore ?></strong></td>
                                            <td class="text-end">
                                                <?= hh_render_team_name_responsive((string) ($fixture['awayteam'] ?? '')) ?>
                                                <img class="competition-flag" src="<?= $awayImage ?>" alt="<?= $awayTeam ?> flag">
                                            </td>
                                            <td class="small d-none d-sm-table-cell"><?= $venue ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<?php include 'php/footer.php'; ?>
