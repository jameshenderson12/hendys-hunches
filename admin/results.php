<?php
session_start();
$page_title = 'Results';

require_once dirname(__DIR__) . '/php/auth.php';
require_once dirname(__DIR__) . '/php/config.php';
require_once dirname(__DIR__) . '/php/process.php';

hh_require_admin('../dashboard.php');

include '../php/db-connect.php';

$messages = [];
$errors = [];
$fixtures = [];
$totalFixtures = (int) ($no_of_total_fixtures ?? 0);
$resultScores = [];
$recordedFixtureCount = 0;
$resultSnapshotCount = 0;
$lockedScores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lockResult = mysqli_query(
        $con,
        "SELECT match_number, homescore, awayscore
         FROM live_match_schedule
         WHERE homescore IS NOT NULL AND awayscore IS NOT NULL"
    );

    if ($lockResult instanceof mysqli_result) {
        while ($lockRow = mysqli_fetch_assoc($lockResult)) {
            $matchNumber = (int) ($lockRow['match_number'] ?? 0);
            if ($matchNumber <= 0) {
                continue;
            }

            $lockedScores[($matchNumber * 2) - 1] = (int) ($lockRow['homescore'] ?? 0);
            $lockedScores[$matchNumber * 2] = (int) ($lockRow['awayscore'] ?? 0);
        }
        mysqli_free_result($lockResult);
    }

    $postedScores = [];
    $totalScoreColumns = max(0, $totalFixtures * 2);

    for ($scoreIndex = 1; $scoreIndex <= $totalScoreColumns; $scoreIndex++) {
        if (array_key_exists($scoreIndex, $lockedScores)) {
            $postedScores[$scoreIndex] = $lockedScores[$scoreIndex];
            continue;
        }

        $value = $_POST["score{$scoreIndex}_r"] ?? '';
        $value = trim((string) $value);
        $postedScores[$scoreIndex] = $value === '' ? null : (int) $value;
    }

    try {
        hh_save_match_results_with_connection($con, $postedScores);
        $messages[] = 'Match results saved. Player points and rankings have been recalculated.';
    } catch (Throwable $exception) {
        $errors[] = 'Results could not be saved: ' . $exception->getMessage();
    }
}

$fixtureResult = mysqli_query(
    $con,
    "SELECT match_number, stage, round_number, date, kotime, venue, hometeam, awayteam, hometeamimg, awayteamimg, homescore, awayscore
     FROM live_match_schedule
     ORDER BY match_number ASC"
);

if ($fixtureResult instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($fixtureResult)) {
        if ($row['homescore'] !== null && $row['awayscore'] !== null) {
            $recordedFixtureCount++;
        }
        $fixtures[] = $row;
    }
    mysqli_free_result($fixtureResult);
}

$snapshotCountResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM live_match_results");
if ($snapshotCountResult instanceof mysqli_result) {
    $snapshotRow = mysqli_fetch_assoc($snapshotCountResult);
    $resultSnapshotCount = (int) ($snapshotRow['total'] ?? 0);
    mysqli_free_result($snapshotCountResult);
}

$resultsResult = mysqli_query($con, "SELECT * FROM live_match_results ORDER BY match_id DESC LIMIT 1");
if ($resultsResult instanceof mysqli_result) {
    $resultScores = mysqli_fetch_assoc($resultsResult) ?: [];
    mysqli_free_result($resultsResult);
}

mysqli_close($con);

$app_path_prefix = '../';
$app_logout_path = '../php/logout.php';

$resolveAssetPath = static function (?string $path) use ($app_path_prefix): string {
    $path = trim((string) $path);
    if ($path === '') {
        return $app_path_prefix . 'img/icon.png';
    }

    if (preg_match('#^(?:https?:)?//#i', $path) || str_starts_with($path, '/')) {
        return $path;
    }

    return $app_path_prefix . ltrim($path, '/');
};

include '../php/header.php';
include '../php/navigation.php';
?>

<style>
.results-shell {
  display: grid;
  gap: 18px;
}

.results-panel {
  padding: 18px;
  border: 1px solid var(--hh-line);
  border-radius: 8px;
  background: rgba(251, 252, 248, 0.96);
  box-shadow: 0 18px 38px rgba(0, 0, 0, 0.14);
}

.results-toolbar {
  position: sticky;
  top: 92px;
  z-index: 5;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 14px 16px;
  border: 1px solid var(--hh-line);
  border-radius: 8px;
  background: rgba(251, 252, 248, 0.98);
  box-shadow: 0 14px 26px rgba(0, 0, 0, 0.10);
}

.results-toolbar__copy h2 {
  margin: 0;
  color: var(--hh-green-dark);
  font-size: 1.2rem;
}

.results-toolbar__copy p {
  margin: 6px 0 0;
  color: var(--hh-muted);
}

.results-toolbar__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
}

.results-toolbar__meta span,
.results-fixture-state {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 10px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.results-toolbar__meta span {
  background: rgba(12, 90, 67, 0.08);
  color: var(--hh-green-dark);
}

.results-toolbar__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.results-table td,
.results-table th {
  vertical-align: middle;
}

.results-fixture-meta {
  color: var(--hh-muted);
  font-size: 0.84rem;
  line-height: 1.35;
}

.results-fixture-meta strong {
  display: block;
  color: var(--hh-green-dark);
  font-size: 0.86rem;
}

.results-fixture-meta span {
  display: block;
}

.results-fixture-meta__venue {
  font-size: 0.68rem;
}

.results-fixture-state {
  margin-top: 8px;
}

.results-fixture-state--recorded {
  background: rgba(25, 135, 84, 0.12);
  color: #146c43;
}

.results-fixture-state--pending {
  background: rgba(255, 193, 7, 0.14);
  color: #8a6d03;
}

.results-team {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 800;
}

.results-team--away {
  justify-content: flex-end;
  text-align: right;
}

.results-team img {
  height: 24px;
  border: 1px solid var(--hh-line);
  background: #ffffff;
}

.results-team--away img {
  margin-left: 8px;
  margin-right: 0;
}

.results-score-input {
  width: 64px;
  min-height: 46px;
  margin: 0 auto;
  text-align: center;
  font-size: 1.05rem;
  font-weight: 800;
}

.results-score-input[readonly] {
  background: rgba(12, 90, 67, 0.08);
  border-color: rgba(12, 90, 67, 0.18);
  color: var(--hh-muted);
  cursor: not-allowed;
}

.results-locked {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  min-height: 34px;
  border-radius: 8px;
  background: rgba(12, 90, 67, 0.08);
  color: var(--hh-muted);
  font-weight: 900;
}

@media (max-width: 991.98px) {
  .results-toolbar {
    position: static;
    align-items: stretch;
    flex-direction: column;
  }

  .results-toolbar__actions {
    width: 100%;
  }
}
</style>

<main id="main" class="main">
    <div class="page-hero page-hero--admin">
        <div>
            <p class="eyebrow">Administration</p>
            <h1>Record Match Results</h1>
            <p class="lead mb-0">Update the live tournament scores, then recalculate every player’s points and ranking movement in one save.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="functions.php"><i class="bi bi-sliders"></i> Admin functions</a>
            <a class="btn btn-outline-dark" href="../rankings.php"><i class="bi bi-list-ol"></i> Rankings</a>
        </div>
    </div>

    <section class="section results-shell">
        <?php foreach ($messages as $message) : ?>
            <p class="alert alert-success mb-0"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($message) ?></p>
        <?php endforeach; ?>
        <?php foreach ($errors as $error) : ?>
            <p class="alert alert-danger mb-0"><i class="bi bi-exclamation-octagon-fill"></i> <?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <?php if (empty($fixtures)) : ?>
            <div class="results-panel">
                <p class="mb-0 text-muted">No fixtures are currently loaded in the live schedule.</p>
            </div>
        <?php else : ?>
            <form method="POST" action="results.php">
                <div class="results-toolbar">
                    <div class="results-toolbar__copy">
                        <h2>Live score entry</h2>
                        <p><?= htmlspecialchars((string) count($fixtures)) ?> fixtures loaded. Saving here updates both the schedule and the scoring engine.</p>
                        <div class="results-toolbar__meta">
                            <span><i class="bi bi-check2-circle"></i> <?= $recordedFixtureCount ?> fixtures recorded</span>
                            <span><i class="bi bi-clock-history"></i> <?= $resultSnapshotCount ?> result snapshots saved</span>
                        </div>
                    </div>
                    <div class="results-toolbar__actions">
                        <button type="reset" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset unsaved edits</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-floppy-fill"></i> Save results and recalculate</button>
                    </div>
                </div>

                <div class="results-panel table-responsive">
                    <table class="table table-sm table-striped results-table">
                        <thead>
                            <tr>
                                <th class="d-none d-lg-table-cell">Fixture</th>
                                <th>Home</th>
                                <th></th>
                                <th class="text-center">Score</th>
                                <th class="text-center"></th>
                                <th class="text-center">Score</th>
                                <th></th>
                                <th class="text-end">Away</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fixtures as $fixture) : ?>
                                <?php
                                $matchNumber = (int) ($fixture['match_number'] ?? 0);
                                $homeScoreIndex = ($matchNumber * 2) - 1;
                                $awayScoreIndex = $matchNumber * 2;
                                $stageLabel = trim((string) ($fixture['stage'] ?? ''));
                                $kickoffDate = !empty($fixture['date']) ? date('D j M', strtotime((string) $fixture['date'])) : '';
                                $kickoffTime = trim((string) ($fixture['kotime'] ?? ''));
                                $fixtureDateTime = trim($kickoffDate . ($kickoffTime !== '' ? ' ' . $kickoffTime : ''));
                                $fixtureVenue = trim((string) ($fixture['venue'] ?? ''));
                                $homeValue = $resultScores["score{$homeScoreIndex}_r"] ?? ($fixture['homescore'] ?? '');
                                $awayValue = $resultScores["score{$awayScoreIndex}_r"] ?? ($fixture['awayscore'] ?? '');
                                $isRecorded = $homeValue !== null && $homeValue !== '' && $awayValue !== null && $awayValue !== '';
                                $lockAttribute = $isRecorded ? ' readonly aria-readonly="true"' : '';
                                ?>
                                <tr>
                                    <td class="d-none d-lg-table-cell">
                                        <div class="results-fixture-meta">
                                            <strong>Match <?= htmlspecialchars((string) $matchNumber) ?> · <?= htmlspecialchars($stageLabel !== '' ? $stageLabel : 'Fixture') ?></strong>
                                            <span><?= htmlspecialchars($fixtureDateTime !== '' ? $fixtureDateTime : 'Kick-off TBC') ?></span>
                                            <span class="results-fixture-meta__venue"><?= htmlspecialchars($fixtureVenue !== '' ? $fixtureVenue : 'Venue TBC') ?></span>
                                            <span class="results-fixture-state <?= $isRecorded ? 'results-fixture-state--recorded' : 'results-fixture-state--pending' ?>">
                                                <i class="bi <?= $isRecorded ? 'bi-check-circle-fill' : 'bi-hourglass-split' ?>"></i>
                                                <?= $isRecorded ? 'Recorded' : 'Pending' ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="results-team">
                                            <img src="<?= htmlspecialchars($resolveAssetPath($fixture['hometeamimg'] ?? '')) ?>" alt="<?= htmlspecialchars((string) $fixture['hometeam']) ?> flag">
                                            <span><?= htmlspecialchars((string) $fixture['hometeam']) ?></span>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td class="text-center">
                                        <input type="number" min="0" max="20" inputmode="numeric" class="form-control results-score-input" name="score<?= $homeScoreIndex ?>_r" value="<?= htmlspecialchars((string) $homeValue) ?>"<?= $lockAttribute ?>>
                                    </td>
                                    <td class="text-center"><span class="results-locked">v</span></td>
                                    <td class="text-center">
                                        <input type="number" min="0" max="20" inputmode="numeric" class="form-control results-score-input" name="score<?= $awayScoreIndex ?>_r" value="<?= htmlspecialchars((string) $awayValue) ?>"<?= $lockAttribute ?>>
                                    </td>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td>
                                        <div class="results-team results-team--away">
                                            <span><?= htmlspecialchars((string) $fixture['awayteam']) ?></span>
                                            <img src="<?= htmlspecialchars($resolveAssetPath($fixture['awayteamimg'] ?? '')) ?>" alt="<?= htmlspecialchars((string) $fixture['awayteam']) ?> flag">
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>

<?php include '../php/footer.php'; ?>
