<?php
session_start();
$page_title = 'Tournament Groups';

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/flags.php';
hh_require_login('index.php');

include 'php/db-connect.php';
include "php/header.php";
include "php/navigation.php";

if (!function_exists('hh_group_stage_rank_sort')) {
    function hh_group_stage_rank_sort(array $left, array $right): int
    {
        if ($left['points'] !== $right['points']) {
            return $right['points'] <=> $left['points'];
        }

        if ($left['goal_difference'] !== $right['goal_difference']) {
            return $right['goal_difference'] <=> $left['goal_difference'];
        }

        if ($left['goals_for'] !== $right['goals_for']) {
            return $right['goals_for'] <=> $left['goals_for'];
        }

        return strcasecmp($left['name'], $right['name']);
    }
}

if (!function_exists('hh_group_stage_sort_key')) {
    function hh_group_stage_sort_key(string $stageLabel): array
    {
        if (preg_match('/^group\s+([a-z0-9]+)/i', $stageLabel, $matches)) {
            return [0, strtoupper($matches[1])];
        }

        return [1, strtoupper($stageLabel)];
    }
}

$groupFixtures = [];
$groupStandings = [];
$groupMeta = [];
$groupRows = [];
$totalGroupFixtures = 0;
$completedGroupFixtures = 0;
$teamsPerGroup = 0;

$groupFixtureQuery = mysqli_query(
    $con,
    "SELECT stage, match_number, date, kotime, venue, hometeam, awayteam, homescore, awayscore, hometeamimg, awayteamimg
     FROM live_match_schedule
     WHERE stage LIKE 'Group %'
     ORDER BY stage ASC, match_number ASC"
);

if ($groupFixtureQuery instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($groupFixtureQuery)) {
        $stage = trim((string) ($row['stage'] ?? ''));
        if ($stage === '') {
            continue;
        }

        $homeTeam = trim((string) ($row['hometeam'] ?? ''));
        $awayTeam = trim((string) ($row['awayteam'] ?? ''));
        if ($homeTeam === '' || $awayTeam === '') {
            continue;
        }

        $totalGroupFixtures++;
        $hasResult = $row['homescore'] !== null && $row['awayscore'] !== null;
        if ($hasResult) {
            $completedGroupFixtures++;
        }

        $groupFixtures[$stage][] = [
            'match_number' => (int) ($row['match_number'] ?? 0),
            'date' => (string) ($row['date'] ?? ''),
            'kotime' => (string) ($row['kotime'] ?? ''),
            'venue' => trim((string) ($row['venue'] ?? '')),
            'home' => $homeTeam,
            'away' => $awayTeam,
            'homescore' => $row['homescore'],
            'awayscore' => $row['awayscore'],
            'has_result' => $hasResult,
        ];

        if (!isset($groupStandings[$stage])) {
            $groupStandings[$stage] = [];
        }

        if (!isset($groupStandings[$stage][$homeTeam])) {
            $groupStandings[$stage][$homeTeam] = [
                'name' => $homeTeam,
                'img' => (string) ($row['hometeamimg'] ?? ''),
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ];
        }

        if (!isset($groupStandings[$stage][$awayTeam])) {
            $groupStandings[$stage][$awayTeam] = [
                'name' => $awayTeam,
                'img' => (string) ($row['awayteamimg'] ?? ''),
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ];
        }

        if (!$hasResult) {
            continue;
        }

        $homeScore = (int) $row['homescore'];
        $awayScore = (int) $row['awayscore'];

        $groupStandings[$stage][$homeTeam]['played']++;
        $groupStandings[$stage][$awayTeam]['played']++;
        $groupStandings[$stage][$homeTeam]['goals_for'] += $homeScore;
        $groupStandings[$stage][$homeTeam]['goals_against'] += $awayScore;
        $groupStandings[$stage][$awayTeam]['goals_for'] += $awayScore;
        $groupStandings[$stage][$awayTeam]['goals_against'] += $homeScore;
        $groupStandings[$stage][$homeTeam]['goal_difference'] = $groupStandings[$stage][$homeTeam]['goals_for'] - $groupStandings[$stage][$homeTeam]['goals_against'];
        $groupStandings[$stage][$awayTeam]['goal_difference'] = $groupStandings[$stage][$awayTeam]['goals_for'] - $groupStandings[$stage][$awayTeam]['goals_against'];

        if ($homeScore > $awayScore) {
            $groupStandings[$stage][$homeTeam]['won']++;
            $groupStandings[$stage][$awayTeam]['lost']++;
            $groupStandings[$stage][$homeTeam]['points'] += 3;
        } elseif ($homeScore < $awayScore) {
            $groupStandings[$stage][$awayTeam]['won']++;
            $groupStandings[$stage][$homeTeam]['lost']++;
            $groupStandings[$stage][$awayTeam]['points'] += 3;
        } else {
            $groupStandings[$stage][$homeTeam]['drawn']++;
            $groupStandings[$stage][$awayTeam]['drawn']++;
            $groupStandings[$stage][$homeTeam]['points']++;
            $groupStandings[$stage][$awayTeam]['points']++;
        }
    }

    mysqli_free_result($groupFixtureQuery);
}

if (!empty($groupStandings)) {
    uksort(
        $groupStandings,
        static function (string $left, string $right): int {
            [$leftBucket, $leftLabel] = hh_group_stage_sort_key($left);
            [$rightBucket, $rightLabel] = hh_group_stage_sort_key($right);

            if ($leftBucket !== $rightBucket) {
                return $leftBucket <=> $rightBucket;
            }

            return strcasecmp($leftLabel, $rightLabel);
        }
    );

    foreach ($groupStandings as $groupName => $teams) {
        $teams = array_values($teams);
        usort($teams, 'hh_group_stage_rank_sort');
        $groupStandings[$groupName] = $teams;

        $teamsPerGroup = max($teamsPerGroup, count($teams));
        $groupMeta[$groupName] = [
            'fixtures' => count($groupFixtures[$groupName] ?? []),
            'completed' => count(array_filter($groupFixtures[$groupName] ?? [], static fn(array $fixture): bool => $fixture['has_result'])),
        ];
    }
}

$groupCount = count($groupStandings);
$nextKnockoutFixtures = 0;
foreach (hh_stage_blueprint() as $stage) {
    if (($stage['key'] ?? '') === 'groups') {
        continue;
    }

    $nextKnockoutFixtures = (int) ($stage['fixtures'] ?? 0);
    if ($nextKnockoutFixtures > 0) {
        break;
    }
}

$nextKnockoutEntrants = $nextKnockoutFixtures > 0 ? $nextKnockoutFixtures * 2 : 0;
$automaticQualifiersPerGroup = ($groupCount > 0 && $nextKnockoutEntrants > 0) ? intdiv($nextKnockoutEntrants, $groupCount) : 0;
$wildcardSlots = ($groupCount > 0 && $nextKnockoutEntrants > 0) ? ($nextKnockoutEntrants % $groupCount) : 0;
$groupSummary = [
    'group_count' => $groupCount,
    'teams_per_group' => $teamsPerGroup,
    'fixtures_total' => $totalGroupFixtures,
    'fixtures_completed' => $completedGroupFixtures,
    'automatic_qualifiers' => $automaticQualifiersPerGroup,
    'wildcard_slots' => $wildcardSlots,
];

mysqli_close($con);
?>

<main id="main" class="main">

    <div class="page-hero page-hero--competition">
        <div>
            <p class="eyebrow">Competition</p>
            <h1>Tournament Groups</h1>
            <p class="lead mb-0">Live group tables generated from <?= htmlspecialchars($GLOBALS['competition']) ?> recorded results.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="tournament-knockouts.php"><i class="bi bi-diagram-3"></i> Knockouts</a>
            <a class="btn btn-outline-dark" href="rankings.php"><i class="bi bi-list-ol"></i> Rankings</a>
        </div>
    </div>

    <section class="section competition-page">
        <article class="competition-panel competition-overview-panel">
            <div class="competition-overview-grid">
                <div class="competition-overview-stat">
                    <span>Groups</span>
                    <strong><?= (int) $groupSummary['group_count'] ?></strong>
                </div>
                <div class="competition-overview-stat">
                    <span>Teams per group</span>
                    <strong><?= (int) $groupSummary['teams_per_group'] ?></strong>
                </div>
                <div class="competition-overview-stat">
                    <span>Fixtures played</span>
                    <strong><?= (int) $groupSummary['fixtures_completed'] ?> / <?= (int) $groupSummary['fixtures_total'] ?></strong>
                </div>
                <div class="competition-overview-stat">
                    <span>Next knockout field</span>
                    <strong><?= $nextKnockoutEntrants > 0 ? (int) $nextKnockoutEntrants . ' teams' : 'TBC' ?></strong>
                </div>
            </div>
            <div class="competition-legend">
                <?php if ($automaticQualifiersPerGroup > 0) : ?>
                    <span class="competition-legend__chip competition-legend__chip--qualified">Top <?= (int) $automaticQualifiersPerGroup ?> qualify automatically</span>
                <?php endif; ?>
                <?php if ($wildcardSlots > 0) : ?>
                    <span class="competition-legend__chip competition-legend__chip--wildcard"><?= (int) $wildcardSlots ?> extra places for best next-ranked teams</span>
                <?php endif; ?>
                <span class="competition-legend__chip competition-legend__chip--neutral">Tables sort by points, goal difference, then goals scored</span>
            </div>
        </article>

        <?php if (empty($groupStandings)) : ?>
            <article class="competition-panel">
                <p class="mb-0">No group-stage fixtures were found in the current schedule.</p>
            </article>
        <?php else : ?>
            <div class="competition-groups-grid">
                <?php foreach ($groupStandings as $groupName => $teams) : ?>
                    <?php
                    $meta = $groupMeta[$groupName] ?? ['fixtures' => 0, 'completed' => 0];
                    ?>
                    <article class="competition-panel">
                        <div class="competition-panel__header">
                            <div>
                                <h2 class="competition-panel__title"><?= htmlspecialchars($groupName) ?></h2>
                                <p class="competition-panel__subtitle mb-0"><?= (int) $meta['completed'] ?> of <?= (int) $meta['fixtures'] ?> fixtures completed</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped competition-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="40%">Team</th>
                                        <th>P</th>
                                        <th>W</th>
                                        <th>D</th>
                                        <th>L</th>
                                        <th>F</th>
                                        <th>A</th>
                                        <th>GD</th>
                                        <th>Pts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teams as $index => $team) : ?>
                                        <?php
                                        $position = $index + 1;
                                        $rowClass = '';
                                        if ($automaticQualifiersPerGroup > 0 && $position <= $automaticQualifiersPerGroup) {
                                            $rowClass = 'competition-row--qualified';
                                        } elseif ($wildcardSlots > 0 && $position === ($automaticQualifiersPerGroup + 1)) {
                                            $rowClass = 'competition-row--wildcard';
                                        }
                                        ?>
                                        <tr class="<?= $rowClass ?>">
                                            <td>
                                                <img class="competition-flag" src="<?= htmlspecialchars((string) $team['img']) ?>" alt="<?= htmlspecialchars((string) $team['name']) ?>">
                                                <?= hh_render_team_name_responsive((string) $team['name']) ?>
                                            </td>
                                            <td><?= (int) $team['played'] ?></td>
                                            <td><?= (int) $team['won'] ?></td>
                                            <td><?= (int) $team['drawn'] ?></td>
                                            <td><?= (int) $team['lost'] ?></td>
                                            <td><?= (int) $team['goals_for'] ?></td>
                                            <td><?= (int) $team['goals_against'] ?></td>
                                            <td><?= (int) $team['goal_difference'] ?></td>
                                            <td><strong><?= (int) $team['points'] ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php include "php/footer.php" ?>
