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
  color: inherit;
  text-decoration: none;
}

.predictions-stage-card:hover {
  text-decoration: none;
}

.predictions-stage-card.is-active {
  border-color: rgba(143, 102, 216, 0.34);
  box-shadow: inset 0 0 0 2px rgba(143, 102, 216, 0.16);
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
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
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
        <p class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill"></i> Predictions can include draws because they are based on the 90-minute score only, not extra time or penalties.</p>

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

        <?php if ($selectedStage) : ?>
            <div class="predictions-stage-summary">
                <div class="predictions-stage-summary__item">
                    <strong><?= htmlspecialchars($selectedStage['label']) ?></strong>
                    <span>Matches <?= htmlspecialchars((string) $selectedStage['fixture_start']) ?>-<?= htmlspecialchars((string) $selectedStage['fixture_end']) ?></span>
                </div>
                <div class="predictions-stage-summary__item">
                    <strong><?= htmlspecialchars((string) count($fixtures)) ?> fixtures loaded</strong>
                    <span>Built from the live schedule in your database</span>
                </div>
                <div class="predictions-stage-summary__item">
                    <strong><?= $stageLastUpdate !== '' ? htmlspecialchars($stageLastUpdate) : 'Not submitted yet' ?></strong>
                    <span>Latest save for this stage</span>
                </div>
                <?php if ($selectedWindow) : ?>
                    <div class="predictions-stage-summary__item">
                        <strong><?= htmlspecialchars(ucfirst((string) $selectedWindow['status'])) ?></strong>
                        <span>
                            <?php if ($selectedWindow['status'] === 'open' && $selectedWindow['closes_at'] instanceof DateTimeImmutable) : ?>
                                Closes <?= htmlspecialchars($selectedWindow['closes_at']->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('D j M H:i')) ?>
                            <?php elseif ($selectedWindow['status'] === 'upcoming' && $selectedWindow['opens_at'] instanceof DateTimeImmutable) : ?>
                                Opens <?= htmlspecialchars($selectedWindow['opens_at']->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('D j M H:i')) ?>
                            <?php elseif ($selectedWindow['status'] === 'closed') : ?>
                                This stage is locked
                            <?php else : ?>
                                Waiting for fixture timings
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($selectedWindow && $selectedWindow['status'] === 'open') : ?>
            <p class="alert alert-info"><i class="bi bi-clock-history"></i> <?= htmlspecialchars($selectedStage['label']) ?> predictions are open. They will lock 2 hours before the first kick-off of this stage.</p>
        <?php elseif ($selectedWindow && $selectedWindow['status'] === 'upcoming') : ?>
            <p class="alert alert-secondary"><i class="bi bi-hourglass-split"></i> <?= htmlspecialchars($selectedStage['label']) ?> predictions are not open yet. They unlock 5 hours after the previous stage’s last kick-off.</p>
        <?php elseif ($selectedWindow && $selectedWindow['status'] === 'closed') : ?>
            <p class="alert alert-warning"><i class="bi bi-lock-fill"></i> <?= htmlspecialchars($selectedStage['label']) ?> predictions are now closed.</p>
        <?php endif; ?>

        <a id="matches"></a>

        <?php if (!$selectedStage || empty($fixtures)) : ?>
            <div class="content-panel">
                <p class="mb-0 text-muted">No fixtures were found for this stage yet.</p>
            </div>
        <?php else : ?>
            <form id="predictionForm" name="predictionForm" class="form-horizontal" action="submit.php" method="POST">
                <input type="hidden" name="stage" value="<?= htmlspecialchars($selectedStageKey) ?>">

                <div class="content-panel table-responsive">
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
                                $metaBits = array_filter([$stageLabel, trim($kickoffDate . ($kickoffTime !== '' ? ' · ' . $kickoffTime : '')), (string) ($fixture['venue'] ?? '')]);
                                ?>
                                <tr>
                                    <td class="d-none d-lg-table-cell">
                                        <div class="fixture-meta">
                                            <strong>Match <?= htmlspecialchars((string) $matchNumber) ?></strong><br>
                                            <?= htmlspecialchars(implode(' · ', $metaBits)) ?>
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

                <button type="button" class="btn btn-secondary mt-3 mb-2 populate-scores" <?= ($selectedWindow && !$selectedWindow['is_open']) ? 'disabled' : '' ?>><i class="bi bi-magic"></i> Populate for me</button>
                <button type="submit" class="btn btn-primary mt-3 mb-2" name="predictionsSubmitted" <?= ($selectedWindow && !$selectedWindow['is_open']) ? 'disabled' : '' ?>><i class="bi bi-send-check-fill"></i> Save <?= htmlspecialchars($selectedStage['label']) ?> predictions</button>
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
