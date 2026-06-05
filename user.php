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

function hh_stage_short_label(string $stageKey, string $fallbackLabel): string
{
    return match ($stageKey) {
        'groups' => 'Groups',
        'ro32' => 'Round of 32',
        'ro16' => 'Round of 16',
        'qf' => 'Quarter-Finals',
        'sf' => 'Semi-Finals',
        'final' => 'Final',
        default => $fallbackLabel,
    };
}

function hh_user_move_meta(int $lastPos, int $currentPos): array
{
    if ($lastPos > $currentPos) {
        $diff = $lastPos - $currentPos;
        return ['label' => '+' . $diff];
    }

    if ($lastPos < $currentPos) {
        $diff = $currentPos - $lastPos;
        return ['label' => '-' . $diff];
    }

    return ['label' => '0'];
}

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stageContexts = hh_prediction_stage_contexts_for_user();
$selectedStageKey = isset($_GET['stage']) ? trim((string) $_GET['stage']) : '';
if ($selectedStageKey === '' || !isset($stageContexts[$selectedStageKey])) {
    $selectedStageKey = array_key_first($stageContexts) ?: 'groups';
}

$profile = null;
$predictionRows = [];
$fixtures = [];
$matchResults = [];
$totalPoints = 0;
$hasRecordedResults = false;

if ($userId > 0) {
    $profileStatement = mysqli_prepare(
        $con,
        "SELECT id, username, firstname, surname, avatar, faveteam, fieldofwork, location, tournwinner, currpos, lastpos, haspaid, signupdate
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
         ORDER BY date ASC, kotime ASC, match_number ASC"
    );

    if ($fixtureResult instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($fixtureResult)) {
            $fixtures[] = $row;
        }
        mysqli_free_result($fixtureResult);
    }

    $resultStateResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM live_match_schedule WHERE homescore IS NOT NULL AND awayscore IS NOT NULL");
    if ($resultStateResult instanceof mysqli_result) {
        $resultStateRow = mysqli_fetch_assoc($resultStateResult) ?: [];
        $hasRecordedResults = ((int) ($resultStateRow['total'] ?? 0)) > 0;
        mysqli_free_result($resultStateResult);
    }
}

mysqli_close($con);

if (!$profile) {
    header('Location: rankings.php');
    exit;
}

$fullName = ucfirst((string) $profile['firstname']) . ' ' . ucfirst((string) $profile['surname']);
$tournwinner = (string) ($profile['tournwinner'] ?? '');
$currentPosition = $hasRecordedResults ? hh_ordinal_position((int) ($profile['currpos'] ?? 0)) : '-';
$moveMeta = hh_user_move_meta((int) ($profile['lastpos'] ?? 0), (int) ($profile['currpos'] ?? 0));
if (!$hasRecordedResults) {
    $moveMeta = ['label' => '-'];
}
$selectedStage = $stageContexts[$selectedStageKey] ?? null;
$visibleFixtures = [];
$selectedStagePoints = (int) (($predictionRows[$selectedStageKey]['points_total'] ?? 0));

if ($selectedStage) {
    foreach ($fixtures as $fixture) {
        $matchNumber = (int) ($fixture['match_number'] ?? 0);
        if ($matchNumber >= (int) $selectedStage['fixture_start'] && $matchNumber <= (int) $selectedStage['fixture_end']) {
            $visibleFixtures[] = $fixture;
        }
    }
}

include 'php/header.php';
include 'php/navigation.php';
?>

<style>
.user-stage-nav {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 0 0 16px;
}

.user-stage-nav__link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.65rem 0.9rem;
  border: 1px solid var(--hh-line);
  border-radius: 8px;
  background: #f4f5f2;
  color: var(--hh-ink);
  font-size: 0.92rem;
  font-weight: 800;
  text-decoration: none;
}

.user-stage-nav__link:hover {
  background: #ffffff;
  color: var(--hh-purple-dark);
  text-decoration: none;
}

.user-stage-nav__link.is-active {
  border-color: rgba(143, 102, 216, 0.34);
  background: rgba(143, 102, 216, 0.12);
  color: var(--hh-purple-dark);
  box-shadow: inset 0 0 0 1px rgba(143, 102, 216, 0.1);
}

.user-layout {
  display: grid;
  align-items: start;
  grid-template-columns: minmax(270px, 320px) minmax(0, 1fr);
  gap: 18px;
}

.user-layout > * {
  min-width: 0;
}

.user-page .dashboard-player-card {
  align-self: start;
  position: sticky;
  top: 104px;
}

.user-table .team-line {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 800;
  min-width: 0;
}

.user-table td,
.user-table th {
  min-width: 0;
}

.user-table .team-line span {
  min-width: 0;
}

.user-table .team-line--away {
  justify-content: flex-end;
  text-align: right;
}

.user-table .team-line--away img {
  margin-left: 8px;
  margin-right: 0;
}

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

.user-table__pts-head {
  color: var(--hh-purple-dark);
  /* background: rgba(143, 102, 216, 0.08); */
}

.user-table__pts-cell {
  background: rgba(143, 102, 216, 0.10) !important;
}

.user-table tfoot td {
  border-top: 2px solid var(--hh-line);
  font-weight: 800;
}

@media (max-width: 991.98px) {
  .user-layout {
    grid-template-columns: 1fr;
  }

  .user-page .dashboard-player-card {
    position: static;
  }
}

@media (max-width: 767.98px) {
  .user-table .team-line {
    gap: 4px;
    font-size: 0.82rem;
  }

  .user-table .team-line img {
    height: 20px;
    margin-right: 4px;
  }

  .user-table .team-line--away img {
    margin-left: 4px;
    margin-right: 0;
  }

  .user-table .prediction,
  .user-table .result {
    min-width: 44px;
    padding: 4px 6px;
    font-size: 0.82rem;
  }

  .user-table .points {
    min-width: 38px;
    padding: 4px 6px;
    font-size: 0.82rem;
  }

  .user-table__pts-head {
    background: rgba(143, 102, 216, 0.1);
  }

  .user-table tfoot td {
    padding-left: 0.35rem;
    padding-right: 0.35rem;
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
            <article class="dashboard-player-card">
                <div class="dashboard-player-card__kit">
                    <img src="<?= htmlspecialchars((string) $profile['avatar']) ?>" alt="<?= htmlspecialchars($fullName) ?> football strip avatar">
                </div>
                <div class="dashboard-player-card__body">
                    <p class="eyebrow">Player card</p>
                    <h2><?= htmlspecialchars($fullName) ?></h2>
                    <p class="dashboard-note">
                        <?= htmlspecialchars((string) (($profile['haspaid'] ?? 'No') === 'Yes' ? 'Entry fee paid' : 'Entry fee pending')) ?>
                        <?php if (!empty($profile['signupdate'])) : ?>
                            · signed up <?= htmlspecialchars(date('j F Y', strtotime((string) $profile['signupdate']))) ?>
                        <?php endif; ?>
                    </p>
                    <div class="dashboard-player-stats">
                        <span><strong><?= htmlspecialchars((string) $currentPosition) ?></strong>Rank</span>
                        <span><strong><?= htmlspecialchars((string) $totalPoints) ?></strong>Points</span>
                        <span><strong><?= htmlspecialchars((string) ($moveMeta['label'] ?? '0')) ?></strong>Move</span>
                    </div>
                    <dl class="dashboard-player-details">
                        <div><dt>Favourite club</dt><dd><?= htmlspecialchars((string) ($profile['faveteam'] ?? 'Not set')) ?></dd></div>
                        <div><dt>Supporting</dt><dd><?= htmlspecialchars($tournwinner !== '' ? $tournwinner : 'Not chosen') ?></dd></div>
                        <div><dt>Location</dt><dd><?= htmlspecialchars((string) ($profile['location'] ?? 'Not set')) ?></dd></div>
                    </dl>
                </div>
            </article>

            <div class="content-panel">
                <div class="dashboard-panel__header">
                    <div>
                        <p class="eyebrow mb-2">Fixture list</p>
                        <h2 class="mb-1"><?= htmlspecialchars($selectedStage ? $selectedStage['label'] : 'Predictions and results') ?></h2>
                        <p class="dashboard-subtle mb-0">Predictions and results for the selected tournament stage.</p>
                    </div>
                </div>

                <div class="user-stage-nav">
                    <?php foreach ($stageContexts as $stageKey => $stageContext) : ?>
                        <a class="user-stage-nav__link <?= $stageKey === $selectedStageKey ? 'is-active' : '' ?>" href="user.php?id=<?= urlencode((string) $userId) ?>&stage=<?= urlencode($stageKey) ?>">
                            <?= htmlspecialchars(hh_stage_short_label($stageKey, (string) $stageContext['label'])) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="table-responsive user-table">
                    <table id="table" class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th class="d-none d-lg-table-cell">Fixture</th>
                                <th></th>
                                <th class="d-none d-lg-table-cell"></th>
                                <th class="text-center">Pred.</th>
                                <th class="text-center">Res.</th>
                                <th class="d-none d-lg-table-cell"></th>
                                <th class="text-end"></th>
                                <th class="text-center user-table__pts-head">Pts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visibleFixtures as $fixture) : ?>
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
                                            <span><?= hh_render_team_name_responsive((string) $fixture['hometeam']) ?></span>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td class="text-center"><span class="prediction"><?= $predictedHome !== null && $predictedAway !== null ? htmlspecialchars($predictedHome . ' - ' . $predictedAway) : '&mdash;' ?></span></td>
                                    <td class="text-center"><span class="result"><?= $actualHome !== null && $actualAway !== null ? htmlspecialchars($actualHome . ' - ' . $actualAway) : '&mdash;' ?></span></td>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td>
                                        <div class="team-line team-line--away">
                                            <span><?= hh_render_team_name_responsive((string) $fixture['awayteam']) ?></span>
                                            <img src="<?= htmlspecialchars((string) $fixture['awayteamimg']) ?>" alt="<?= htmlspecialchars((string) $fixture['awayteam']) ?> flag">
                                        </div>
                                    </td>
                                    <td class="text-center user-table__pts-cell"><span class="points"><?= $points !== null ? htmlspecialchars((string) $points) : '&mdash;' ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end d-table-cell d-md-none">Stage total</td>
                                <td colspan="7" class="text-end d-none d-md-table-cell">Stage total</td>
                                <td class="text-center user-table__pts-cell"><span class="points"><?= htmlspecialchars((string) $selectedStagePoints) ?></span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'php/footer.php'; ?>
