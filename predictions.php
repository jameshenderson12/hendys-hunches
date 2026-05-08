<?php
session_start();
$page_title = 'Submit Predictions';

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/flags.php';
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/process.php';

hh_require_login('index.php');

include 'php/db-connect.php';

function hh_prediction_value(array $row, int $scoreIndex): string
{
    $column = 'score' . $scoreIndex . '_p';
    if (!array_key_exists($column, $row) || $row[$column] === null || $row[$column] === '') {
        return '';
    }

    return (string) $row[$column];
}

function hh_prediction_submission_state(array $row, array $context): array
{
    $filledScores = 0;
    $requiredScores = max(0, ((int) ($context['fixture_end'] ?? 0) - (int) ($context['fixture_start'] ?? 0) + 1) * 2);

    for ($scoreIndex = (int) ($context['score_start'] ?? 0); $scoreIndex <= (int) ($context['score_end'] ?? -1); $scoreIndex++) {
        if ($scoreIndex <= 0) {
            continue;
        }

        $column = 'score' . $scoreIndex . '_p';
        if (array_key_exists($column, $row) && $row[$column] !== null && $row[$column] !== '') {
            $filledScores++;
        }
    }

    return [
        'filled' => $filledScores,
        'required' => $requiredScores,
        'submitted' => $requiredScores > 0 && $filledScores >= $requiredScores,
    ];
}

$stageContexts = hh_prediction_stage_contexts();
$stageWindows = hh_prediction_stage_windows($con);
$selectedStageKey = isset($_GET['stage']) ? trim((string) $_GET['stage']) : '';
if ($selectedStageKey === '' || !isset($stageContexts[$selectedStageKey])) {
    $selectedStageKey = array_key_first($stageContexts) ?: 'groups';
}

$selectedStage = $stageContexts[$selectedStageKey] ?? null;
$selectedWindow = $stageWindows[$selectedStageKey] ?? null;
$fixtures = [];
$predictionRow = [];
$predictionRows = [];
$stageLastUpdate = '';

foreach ($stageContexts as $stageKey => $context) {
    $predictionStatement = mysqli_prepare($con, "SELECT * FROM {$context['table']} WHERE id = ? LIMIT 1");
    if (!$predictionStatement) {
        $predictionRows[$stageKey] = [];
        continue;
    }

    $sessionId = (int) ($_SESSION['id'] ?? 0);
    mysqli_stmt_bind_param($predictionStatement, 'i', $sessionId);
    mysqli_stmt_execute($predictionStatement);
    $predictionResult = mysqli_stmt_get_result($predictionStatement);

    if ($predictionResult instanceof mysqli_result) {
        $predictionRows[$stageKey] = mysqli_fetch_assoc($predictionResult) ?: [];
        mysqli_free_result($predictionResult);
    } else {
        $predictionRows[$stageKey] = [];
    }

    mysqli_stmt_close($predictionStatement);
}

if ($selectedStage) {
    $fixtureStatement = mysqli_prepare(
        $con,
        "SELECT match_number, stage, round_number, date, kotime, venue, hometeam, awayteam, hometeamimg, awayteamimg
         FROM live_match_schedule
         WHERE match_number BETWEEN ? AND ?
         ORDER BY match_number ASC"
    );

    if ($fixtureStatement) {
        mysqli_stmt_bind_param($fixtureStatement, 'ii', $selectedStage['fixture_start'], $selectedStage['fixture_end']);
        mysqli_stmt_execute($fixtureStatement);
        $fixtureResult = mysqli_stmt_get_result($fixtureStatement);

        if ($fixtureResult instanceof mysqli_result) {
            while ($row = mysqli_fetch_assoc($fixtureResult)) {
                $fixtures[] = $row;
            }
            mysqli_free_result($fixtureResult);
        }

        mysqli_stmt_close($fixtureStatement);
    }

    $predictionRow = $predictionRows[$selectedStageKey] ?? [];
    $stageLastUpdate = !empty($predictionRow['lastupdate'])
        ? date('D j M Y, H:i', strtotime((string) $predictionRow['lastupdate']))
        : '';
}

mysqli_close($con);

include 'php/header.php';
include 'php/navigation.php';
?>

<style>
.predictions-stage-grid {
  margin-bottom: 18px;
}

.predictions-stage-card {
  display: grid;
  gap: 12px;
  min-height: 100%;
  color: inherit;
  text-decoration: none;
  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
}

.predictions-stage-card:hover {
  text-decoration: none;
  transform: translateY(-2px);
  box-shadow: 0 18px 30px rgba(22, 35, 29, 0.12);
}

.predictions-stage-card:not(.is-active) {
  background: #f4f5f2;
  border-color: rgba(22, 35, 29, 0.10);
}

.predictions-stage-card.is-active {
  border-color: var(--hh-purple);
  background: #ffffff;
  box-shadow:
    inset 0 0 0 2px rgba(143, 102, 216, 0.16),
    0 18px 30px rgba(22, 35, 29, 0.12);
  transform: translateY(-2px);
}

.predictions-stage-card.is-active .overview-stage-card__top h3 {
  color: var(--hh-purple-dark);
}

.predictions-stage-card.is-active::after {
  content: "Selected";
  justify-self: start;
  margin-top: -2px;
  padding: 0.18rem 0.48rem;
  border-radius: 999px;
  background: rgba(143, 102, 216, 0.14);
  color: var(--hh-purple-dark);
  font-size: 0.72rem;
  font-weight: 900;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.predictions-stage-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding-top: 12px;
  border-top: 1px solid var(--hh-line);
}

.predictions-stage-card__submission {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--hh-muted);
  font-size: 0.86rem;
  font-weight: 800;
}

.predictions-stage-card__submission.is-submitted {
  color: var(--hh-green-dark);
}

.predictions-stage-card__submission.is-pending {
  color: #8f5a14;
}

.predictions-stage-card__updated {
  color: var(--hh-muted);
  font-size: 0.76rem;
  text-align: right;
}

.predictions-stage-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 18px;
}

.predictions-stage-summary__item {
  min-width: 180px;
  padding: 12px 14px;
  border: 1px solid var(--hh-line);
  border-radius: 8px;
  background: #ffffff;
}

.predictions-stage-summary__item strong {
  display: block;
  color: var(--hh-ink);
}

.predictions-stage-summary__item span {
  color: var(--hh-muted);
  font-size: 0.9rem;
}

.predictions-page .fixture-meta {
  color: var(--hh-muted);
  font-size: 0.84rem;
  line-height: 1.35;
}

.predictions-page .fixture-meta strong {
  display: block;
  color: var(--hh-green-dark);
  font-size: 0.85rem;
}

.predictions-page .fixture-meta span {
  display: block;
}

.predictions-page .fixture-meta span:last-child {
  font-size: 0.68rem;
}

.predictions-page .team-cell {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 800;
}

.predictions-page .team-cell--away {
  justify-content: flex-end;
  text-align: right;
}

.predictions-page .team-cell img {
  height: 24px;
  border: 1px solid var(--hh-line);
}

.predictions-page .score-field {
  max-width: 68px;
  margin: 0 auto;
  text-align: center;
  font-size: 1.1rem;
  font-weight: 800;
}

.predictions-page .versus-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 44px;
  min-height: 36px;
  border-radius: 8px;
  background: rgba(12, 90, 67, 0.08);
  color: var(--hh-muted);
  font-weight: 900;
}

.predictions-page .table td,
.predictions-page .table th {
  vertical-align: middle;
}

.predictions-stage-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}

.predictions-stage-heading__content {
  min-width: 0;
}

.predictions-stage-heading h2 {
  margin: 0;
  color: var(--hh-green-dark);
  font-size: 1.35rem;
}

.predictions-stage-heading p {
  margin: 6px 0 0;
  color: var(--hh-muted);
}

.predictions-stage-actions {
  position: sticky;
  top: 104px;
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 10px;
  padding: 10px 0;
  margin-left: auto;
  background: linear-gradient(180deg, rgba(251, 252, 248, 0.98), rgba(251, 252, 248, 0.94));
  z-index: 4;
}

.predictions-stage-actions .btn {
  min-width: 180px;
}

@media (max-width: 991.98px) {
  .predictions-stage-heading {
    align-items: stretch;
    flex-direction: column;
  }

  .predictions-stage-actions {
    position: static;
    width: 100%;
    justify-content: flex-start;
    padding: 0;
    margin-left: 0;
    background: transparent;
  }
}
</style>

<main id="main" class="main">
    <div class="page-hero page-hero--predictions">
        <div>
            <p class="eyebrow">Predictions</p>
            <h1>Submit your predictions</h1>
            <p class="lead mb-0">Work through each tournament stage and lock in your scorelines before kick-off.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="#matches"><i class="bi bi-pencil-square"></i> Match list</a>
            <a class="btn btn-outline-dark" href="how-it-works.php"><i class="bi bi-question-circle"></i> Scoring guide</a>
        </div>
    </div>

    <section class="section predictions-page">
        <?php if (isset($_GET['saved'])) : ?>
            <p class="alert alert-success"><i class="bi bi-check-circle-fill"></i> Your <?= htmlspecialchars((string) ($selectedStage['label'] ?? 'stage')) ?> predictions were saved.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] !== '') : ?>
            <p class="alert alert-danger"><i class="bi bi-exclamation-octagon-fill"></i> <?= htmlspecialchars((string) $_GET['error']) ?></p>
        <?php endif; ?>

        <div class="overview-stage-grid predictions-stage-grid">
            <?php foreach ($stageContexts as $stageContext) : ?>
                <?php
                $window = $stageWindows[$stageContext['key']] ?? null;
                $status = (string) ($window['status'] ?? 'pending');
                $submission = hh_prediction_submission_state($predictionRows[$stageContext['key']] ?? [], $stageContext);
                $stageUpdated = !empty($predictionRows[$stageContext['key']]['lastupdate'])
                    ? date('D j M H:i', strtotime((string) $predictionRows[$stageContext['key']]['lastupdate']))
                    : '';
                ?>
                <a class="overview-stage-card overview-stage-card--<?= htmlspecialchars($status) ?> predictions-stage-card <?= $stageContext['key'] === $selectedStageKey ? 'is-active' : '' ?>" href="predictions.php?stage=<?= urlencode($stageContext['key']) ?>">
                    <div class="overview-stage-card__top">
                        <h3><?= htmlspecialchars($stageContext['label']) ?></h3>
                        <span class="overview-stage-pill overview-stage-pill--<?= htmlspecialchars($status) ?>">
                            <?= htmlspecialchars(ucfirst($status)) ?>
                        </span>
                    </div>
                    <p><?= htmlspecialchars((string) (($stageContext['fixture_end'] - $stageContext['fixture_start']) + 1)) ?> fixtures · matches <?= htmlspecialchars((string) $stageContext['fixture_start']) ?>-<?= htmlspecialchars((string) $stageContext['fixture_end']) ?></p>
                    <dl>
                        <div>
                            <dt>Opens</dt>
                            <dd>
                                <?php if ($window && $window['opens_at'] instanceof DateTimeImmutable) : ?>
                                    <?= htmlspecialchars($window['opens_at']->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('D j M, g:ia')) ?>
                                <?php else : ?>
                                    Open from setup
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt>Closes</dt>
                            <dd>
                                <?php if ($window && $window['closes_at'] instanceof DateTimeImmutable) : ?>
                                    <?= htmlspecialchars($window['closes_at']->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('D j M, g:ia')) ?>
                                <?php else : ?>
                                    Awaiting fixture timings
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                    <div class="predictions-stage-card__footer">
                        <span class="predictions-stage-card__submission <?= $submission['submitted'] ? 'is-submitted' : 'is-pending' ?>">
                            <i class="bi <?= $submission['submitted'] ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
                            <?= $submission['submitted'] ? 'Predictions submitted' : 'Predictions not submitted' ?>
                        </span>
                        <span class="predictions-stage-card__updated">
                            <?= $stageUpdated !== '' ? htmlspecialchars('Saved ' . $stageUpdated) : 'No saved set yet' ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <a id="matches"></a>

        <?php if (!$selectedStage || empty($fixtures)) : ?>
            <div class="content-panel">
                <p class="mb-0 text-muted">No fixtures were found for this stage yet.</p>
            </div>
        <?php else : ?>
            <form id="predictionForm" name="predictionForm" class="form-horizontal" action="submit.php" method="POST">
                <input type="hidden" name="stage" value="<?= htmlspecialchars($selectedStageKey) ?>">

                <div class="content-panel table-responsive">
                    <div class="predictions-stage-heading">
                        <div class="predictions-stage-heading__content">
                            <p class="eyebrow mb-2">Selected stage</p>
                            <h2><?= htmlspecialchars($selectedStage['label']) ?></h2>
                            <p>
                                Matches <?= htmlspecialchars((string) $selectedStage['fixture_start']) ?>-<?= htmlspecialchars((string) $selectedStage['fixture_end']) ?>
                                <?php if ($stageLastUpdate !== '') : ?> · last saved <?= htmlspecialchars($stageLastUpdate) ?><?php endif; ?>
                            </p>
                        </div>
                        <div class="predictions-stage-actions">
                            <button type="button" class="btn btn-secondary populate-scores" <?= ($selectedWindow && !$selectedWindow['is_open']) ? 'disabled' : '' ?>><i class="bi bi-magic"></i> Populate for me</button>
                            <button type="submit" class="btn btn-primary" name="predictionsSubmitted" <?= ($selectedWindow && !$selectedWindow['is_open']) ? 'disabled' : '' ?>><i class="bi bi-floppy-fill"></i> Save <?= htmlspecialchars($selectedStage['label']) ?> predictions</button>
                        </div>
                    </div>
                    <table id="table" class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th class="d-none d-lg-table-cell">Fixture</th>
                                <th>Home</th>
                                <th></th>
                                <th class="text-center">Pred.</th>
                                <th class="text-center"></th>
                                <th class="text-center">Pred.</th>
                                <th></th>
                                <th>Away</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fixtures as $fixture) : ?>
                                <?php
                                $matchNumber = (int) ($fixture['match_number'] ?? 0);
                                $homeScoreIndex = ($matchNumber * 2) - 1;
                                $awayScoreIndex = $matchNumber * 2;
                                $homeValue = hh_prediction_value($predictionRow, $homeScoreIndex);
                                $awayValue = hh_prediction_value($predictionRow, $awayScoreIndex);
                                $stageLabel = trim((string) ($fixture['stage'] ?? '')) ?: ($selectedStage['label'] ?? '');
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
                                        <div class="team-cell">
                                            <img src="<?= htmlspecialchars((string) $fixture['hometeamimg']) ?>" alt="<?= htmlspecialchars((string) $fixture['hometeam']) ?> flag">
                                            <span><?= htmlspecialchars((string) $fixture['hometeam']) ?></span>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td class="text-center">
                                        <input type="number" min="0" max="20" inputmode="numeric" id="score<?= $homeScoreIndex ?>_p" name="score<?= $homeScoreIndex ?>_p" class="form-control score-field" value="<?= htmlspecialchars($homeValue) ?>" required <?= ($selectedWindow && !$selectedWindow['is_open']) ? 'disabled' : '' ?>>
                                    </td>
                                    <td class="text-center"><span class="versus-pill">v</span></td>
                                    <td class="text-center">
                                        <input type="number" min="0" max="20" inputmode="numeric" id="score<?= $awayScoreIndex ?>_p" name="score<?= $awayScoreIndex ?>_p" class="form-control score-field" value="<?= htmlspecialchars($awayValue) ?>" required <?= ($selectedWindow && !$selectedWindow['is_open']) ? 'disabled' : '' ?>>
                                    </td>
                                    <td class="d-none d-md-table-cell"></td>
                                    <td>
                                        <div class="team-cell team-cell--away">
                                            <span><?= htmlspecialchars((string) $fixture['awayteam']) ?></span>
                                            <img src="<?= htmlspecialchars((string) $fixture['awayteamimg']) ?>" alt="<?= htmlspecialchars((string) $fixture['awayteam']) ?> flag">
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

<script>
$(document).ready(function () {
    $('.populate-scores').on('click', function() {
        function getRandomScore() {
            const rand = Math.random();
            if (rand < 0.37) return 0;
            if (rand < 0.67) return 1;
            if (rand < 0.88) return 2;
            if (rand < 0.95) return 3;
            if (rand < 0.99) return 4;
            return 5;
        }

        $('#predictionForm input[name^="score"]').each(function() {
            $(this).val(getRandomScore());
        });
    });
});
</script>

<?php include 'php/footer.php'; ?>
