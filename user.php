<?php
session_start();
$page_title = 'View Predictions';

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/flags.php';
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/process.php';

hh_require_login('index.php');

include 'php/db-connect.php';

function hh_prediction_stage_contexts_for_user(): array
{
    $contexts = [];
    $fixtureStart = 1;
    $definitions = hh_prediction_stage_definitions();

    foreach (hh_stage_blueprint() as $stage) {
        $key = (string) ($stage['key'] ?? '');
        $fixtureCount = (int) ($stage['fixtures'] ?? 0);
        $definition = $definitions[$key] ?? null;

        if ($key === '' || $fixtureCount <= 0 || !$definition) {
            continue;
        }

        $contexts[$key] = [
            'key' => $key,
            'label' => (string) ($stage['label'] ?? ucfirst($key)),
            'table' => (string) ($stage['table'] ?? $definition['table']),
            'fixture_start' => $fixtureStart,
            'fixture_end' => $fixtureStart + $fixtureCount - 1,
            'score_start' => (int) $definition['start'],
            'score_end' => (int) $definition['end'],
        ];

        $fixtureStart += $fixtureCount;
    }

    return $contexts;
}

function hh_ordinal_position($number): string
{
    $number = (int) $number;
    if ($number <= 0) {
        return 'N/A';
    }

    $ends = ['th','st','nd','rd','th','th','th','th','th','th'];
    if (($number % 100) >= 11 && ($number % 100) <= 13) {
        return $number . 'th';
    }

    return $number . $ends[$number % 10];
}

function hh_score_value(array $row, int $scoreIndex, string $suffix): ?int
{
    $column = 'score' . $scoreIndex . '_' . $suffix;
    if (!array_key_exists($column, $row) || $row[$column] === null || $row[$column] === '') {
        return null;
    }

    return (int) $row[$column];
}

function hh_calculate_prediction_points(?int $predictedHome, ?int $predictedAway, ?int $actualHome, ?int $actualAway): ?int
{
    if ($predictedHome === null || $predictedAway === null || $actualHome === null || $actualAway === null) {
        return null;
    }

    if ($predictedHome === $actualHome && $predictedAway === $actualAway) {
        return 7;
    }

    $predictedOutcome = $predictedHome > $predictedAway ? 'home' : ($predictedHome < $predictedAway ? 'away' : 'draw');
    $actualOutcome = $actualHome > $actualAway ? 'home' : ($actualHome < $actualAway ? 'away' : 'draw');

    if ($predictedOutcome === $actualOutcome) {
        if ($predictedHome === $actualHome || $predictedAway === $actualAway) {
            return 3;
        }

        return 2;
    }

    if ($predictedHome === $actualHome || $predictedAway === $actualAway) {
        return 1;
    }

    return 0;
}

function hh_stage_context_for_match(array $stageContexts, int $matchNumber): ?array
{
    foreach ($stageContexts as $context) {
        if ($matchNumber >= $context['fixture_start'] && $matchNumber <= $context['fixture_end']) {
            return $context;
        }
    }

    return null;
}

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stageContexts = hh_prediction_stage_contexts_for_user();

$profile = null;
$predictionRows = [];
$fixtures = [];
$matchResults = [];
$totalPoints = 0;

if ($userId > 0) {
    $profileStatement = mysqli_prepare(
        $con,
        "SELECT id, username, firstname, surname, avatar, faveteam, fieldofwork, location, tournwinner, currpos, haspaid
         FROM live_user_information
         WHERE id = ?
         LIMIT 1"
    );

    if ($profileStatement) {
        mysqli_stmt_bind_param($profileStatement, 'i', $userId);
        mysqli_stmt_execute($profileStatement);
        $profileResult = mysqli_stmt_get_result($profileStatement);
        if ($profileResult instanceof mysqli_result) {
            $profile = mysqli_fetch_assoc($profileResult) ?: null;
            mysqli_free_result($profileResult);
        }
        mysqli_stmt_close($profileStatement);
    }

    foreach ($stageContexts as $stageKey => $context) {
        $statement = mysqli_prepare($con, "SELECT * FROM {$context['table']} WHERE id = ? LIMIT 1");
        if (!$statement) {
            continue;
        }

        mysqli_stmt_bind_param($statement, 'i', $userId);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        if ($result instanceof mysqli_result) {
            $predictionRows[$stageKey] = mysqli_fetch_assoc($result) ?: [];
            mysqli_free_result($result);
        } else {
            $predictionRows[$stageKey] = [];
        }
        mysqli_stmt_close($statement);

        $totalPoints += (int) ($predictionRows[$stageKey]['points_total'] ?? 0);
    }

    $fixtureResult = mysqli_query(
        $con,
        "SELECT match_number, stage, round_number, date, kotime, venue, hometeam, awayteam, hometeamimg, awayteamimg, homescore, awayscore
         FROM live_match_schedule
         ORDER BY match_number ASC"
    );

    if ($fixtureResult instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($fixtureResult)) {
            $fixtures[] = $row;
        }
        mysqli_free_result($fixtureResult);
    }
}

mysqli_close($con);

if (!$profile) {
    header('Location: rankings.php');
    exit;
}

$fullName = ucfirst((string) $profile['firstname']) . ' ' . ucfirst((string) $profile['surname']);
$tournwinner = (string) ($profile['tournwinner'] ?? '');
$tournwinnerFlag = $tournwinner !== '' ? hh_get_team_flag_path($tournwinner) : '';
$currentPosition = hh_ordinal_position((int) ($profile['currpos'] ?? 0));

include 'php/header.php';
include 'php/navigation.php';
?>

<style>
.user-layout {
  display: grid;
  align-items: start;
  grid-template-columns: minmax(270px, 320px) minmax(0, 1fr);
  gap: 18px;
}

.user-page .concept-profile-card {
  align-self: start;
  position: sticky;
  top: 104px;
}

.user-table .team-line {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 800;
}

.user-table .team-line--away {
  justify-content: flex-end;
  text-align: right;
}

.user-table .team-line--away img {
  margin-left: 8px;
  margin-right: 0;
}

/* .user-table .team-line img {
  height: 24px;
  margin-right: 8px;
  border: 1px solid lightgray;
  background: #ffffff;
} */

.user-table .fixture-meta {
  color: var(--hh-muted);
  font-size: 0.84rem;
  line-height: 1.35;
}

.user-table .fixture-meta strong {
  display: block;
  color: var(--hh-green-dark);
  font-size: 0.85rem;
}

.user-table .fixture-meta span {
  display: block;
}

.user-table .fixture-meta span:last-child {
  font-size: 0.68rem;
}

.user-table .prediction,
.user-table .result,
.user-table .points {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 52px;
  min-height: 34px;
  padding: 4px 9px;
  border-radius: 8px;
  background: rgba(12, 90, 67, 0.09);
  font-weight: 900;
}

.user-table .result {
  background: rgba(243, 199, 66, 0.2);
}

.user-table .points {
  background: rgba(143, 102, 216, 0.16);
  color: var(--hh-purple-dark);
}

@media (max-width: 991.98px) {
  .user-layout {
    grid-template-columns: 1fr;
  }

  .user-page .concept-profile-card {
    position: static;
  }
}
</style>

<main id="main" class="main">
    <div class="page-hero page-hero--user">
        <div>
            <p class="eyebrow">Player predictions</p>
            <h1 class="page-header">Predictions by <?= htmlspecialchars($fullName) ?></h1>
            <p class="lead mb-0">A live fixture-by-fixture view of this player’s picks, results and points.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="rankings.php"><i class="bi bi-list-ol"></i> Rankings</a>
            <a class="btn btn-outline-dark" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </div>
    </div>

    <section class="section user-page">
        <div class="user-layout">
            <article class="concept-profile-card">
                <div class="concept-profile-card__kit">
                    <img src="<?= htmlspecialchars((string) $profile['avatar']) ?>" alt="<?= htmlspecialchars($fullName) ?> football strip avatar">
                </div>
                <div class="concept-profile-card__body">
                    <div>
                        <p class="eyebrow mb-2">Player card</p>
                        <h2><?= htmlspecialchars($fullName) ?></h2>
                        <p class="concept-subtle mb-0"><?= htmlspecialchars((string) $profile['username']) ?> · <?= htmlspecialchars($currentPosition) ?> in the rankings</p>
                    </div>
                    <div class="concept-profile-stats">
                        <span><strong><?= htmlspecialchars((string) $totalPoints) ?></strong>Total points</span>
                        <span><strong><?= htmlspecialchars((string) count($fixtures)) ?></strong>Fixtures tracked</span>
                        <span><strong><?= htmlspecialchars((string) strtoupper((string) $profile['haspaid'])) ?></strong>Entry paid</span>
                    </div>
                    <dl class="concept-profile-details">
                        <div>
                            <dt>Tournament winner</dt>
                            <dd>
                                <?php if ($tournwinnerFlag !== '') : ?>
                                    <img src="<?= htmlspecialchars($tournwinnerFlag) ?>" alt="<?= htmlspecialchars($tournwinner) ?> flag" width="24" height="24" style="border-radius:50%;margin-right:8px;">
                                <?php endif; ?>
                                <?= htmlspecialchars($tournwinner !== '' ? $tournwinner : 'Not chosen') ?>
                            </dd>
                        </div>
                        <div>
                            <dt>Favourite team</dt>
                            <dd><?= htmlspecialchars((string) ($profile['faveteam'] ?? 'Not set')) ?></dd>
                        </div>
                        <div>
                            <dt>Location</dt>
                            <dd><?= htmlspecialchars((string) ($profile['location'] ?? 'Not set')) ?></dd>
                        </div>
                        <div>
                            <dt>Field of expertise</dt>
                            <dd><?= htmlspecialchars((string) ($profile['fieldofwork'] ?? 'Not set')) ?></dd>
                        </div>
                    </dl>
                </div>
            </article>

            <div class="content-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow mb-2">Fixture list</p>
                        <h2 class="mb-1">Predictions and results</h2>
                        <p class="dashboard-subtle mb-0">Every fixture in the schedule, built from the live tournament data.</p>
                    </div>
                </div>

                <div class="table-responsive user-table">
                    <table id="table" class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th class="d-none d-lg-table-cell">Fixture</th>
                                <th>Home</th>
                                <th></th>
                                <th class="text-center">Pred.</th>
                                <th class="text-center">Res.</th>
                                <th class="text-center">Pts</th>
                                <th></th>
                                <th class="text-end">Away</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fixtures as $fixture) : ?>
                                <?php
                                $matchNumber = (int) ($fixture['match_number'] ?? 0);
                                $stageContext = hh_stage_context_for_match($stageContexts, $matchNumber);
                                $stageKey = $stageContext['key'] ?? '';
                                $predictionRow = $stageKey !== '' ? ($predictionRows[$stageKey] ?? []) : [];
                                $homeScoreIndex = ($matchNumber * 2) - 1;
                                $awayScoreIndex = $matchNumber * 2;
                                $predictedHome = hh_score_value($predictionRow, $homeScoreIndex, 'p');
                                $predictedAway = hh_score_value($predictionRow, $awayScoreIndex, 'p');
                                $actualHome = hh_score_value($fixture, $homeScoreIndex, 'r');
                                $actualAway = hh_score_value($fixture, $awayScoreIndex, 'r');

                                if ($actualHome === null && isset($fixture['homescore']) && $fixture['homescore'] !== null && $fixture['homescore'] !== '') {
                                    $actualHome = (int) $fixture['homescore'];
                                }
                                if ($actualAway === null && isset($fixture['awayscore']) && $fixture['awayscore'] !== null && $fixture['awayscore'] !== '') {
                                    $actualAway = (int) $fixture['awayscore'];
                                }

                                $points = hh_calculate_prediction_points($predictedHome, $predictedAway, $actualHome, $actualAway);
                                $stageLabel = trim((string) ($fixture['stage'] ?? '')) ?: (string) ($stageContext['label'] ?? '');
                                $kickoffDate = !empty($fixture['date']) ? date('D j M', strtotime((string) $fixture['date'])) : '';
                                $kickoffTime = trim((string) ($fixture['kotime'] ?? ''));
                                $fixtureDateTime = trim($kickoffDate . ($kickoffTime !== '' ? ' ' . $kickoffTime : ''));
                                $fixtureVenue = trim((string) ($fixture['venue'] ?? ''));
                                ?>
                                <tr>
                                    <td class="d-none d-lg-table-cell">
                                        <div class="fixture-meta">
                                            <strong>Match <?= htmlspecialchars((string) $matchNumber) ?> · <?= htmlspecialchars($stageLabel !== '' ? $stageLabel : 'Fixture') ?></strong>
                                            <span><?= htmlspecialchars($fixtureDateTime !== '' ? $fixtureDateTime : 'Kick-off TBC') ?></span>
                                            <span><?= htmlspecialchars($fixtureVenue !== '' ? $fixtureVenue : 'Venue TBC') ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="team-line">
                                            <img src="<?= htmlspecialchars((string) $fixture['hometeamimg']) ?>" alt="<?= htmlspecialchars((string) $fixture['hometeam']) ?> flag">
                                            <span><?= htmlspecialchars((string) $fixture['hometeam']) ?></span>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td class="text-center"><span class="prediction"><?= $predictedHome !== null && $predictedAway !== null ? htmlspecialchars($predictedHome . ' - ' . $predictedAway) : '&mdash;' ?></span></td>
                                    <td class="text-center"><span class="result"><?= $actualHome !== null && $actualAway !== null ? htmlspecialchars($actualHome . ' - ' . $actualAway) : '&mdash;' ?></span></td>
                                    <td class="text-center"><span class="points"><?= $points !== null ? htmlspecialchars((string) $points) : '&mdash;' ?></span></td>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td>
                                        <div class="team-line team-line--away">
                                            <span><?= htmlspecialchars((string) $fixture['awayteam']) ?></span>
                                            <img src="<?= htmlspecialchars((string) $fixture['awayteamimg']) ?>" alt="<?= htmlspecialchars((string) $fixture['awayteam']) ?> flag">
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'php/footer.php'; ?>
