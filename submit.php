<?php
session_start();
$page_title = 'Submitting Predictions';

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/process.php';

hh_require_login('index.php');

$submitResult = submitPredictions();
$submittedStage = isset($submitResult['stage']) ? trim((string) $submitResult['stage']) : trim((string) ($_POST['stage'] ?? ''));

$redirectTarget = 'predictions.php?saved=1';
if (!($submitResult['ok'] ?? false)) {
    $redirectTarget = 'predictions.php?error=' . rawurlencode((string) ($submitResult['message'] ?? 'Unable to save predictions.'));
}

if ($submittedStage !== '') {
    $redirectTarget .= (str_contains($redirectTarget, '?') ? '&' : '?') . 'stage=' . rawurlencode($submittedStage);
}

$statusOkay = (bool) ($submitResult['ok'] ?? false);
$statusIcon = $statusOkay ? 'bi-check-circle-fill' : 'bi-exclamation-octagon-fill';
$statusClass = $statusOkay ? 'is-success' : 'is-error';
$statusTitle = $statusOkay ? 'Predictions saved' : 'Update not saved';
$statusMessage = (string) ($submitResult['message'] ?? ($statusOkay ? 'Your predictions have been recorded.' : 'Unable to save predictions.'));
$redirectSeconds = 2;
$redirectLabel = $statusOkay ? 'Taking you back to your predictions now.' : 'Returning you to the predictions page now.';

include 'php/header.php';
include 'php/navigation.php';
?>

<main id="main" class="main">
    <div class="page-hero page-hero--submit">
        <div>
            <p class="eyebrow">Prediction update</p>
            <h1><?= htmlspecialchars($statusTitle) ?></h1>
            <p class="lead mb-0"><?= htmlspecialchars($redirectLabel) ?></p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-outline-dark" href="<?= htmlspecialchars($redirectTarget, ENT_QUOTES) ?>">
                <i class="bi bi-arrow-right-circle"></i> Continue now
            </a>
        </div>
    </div>

    <section class="section submit-page">
        <div class="content-panel content-panel--narrow submit-status-card <?= $statusClass ?>">
            <div class="submit-status-card__icon" aria-hidden="true">
                <i class="bi <?= htmlspecialchars($statusIcon, ENT_QUOTES) ?>"></i>
            </div>
            <div class="submit-status-card__body">
                <p class="eyebrow mb-2"><?= $statusOkay ? 'All set' : 'Needs attention' ?></p>
                <h2><?= htmlspecialchars($statusTitle) ?></h2>
                <p class="mb-0"><?= htmlspecialchars($statusMessage) ?></p>
            </div>
            <div class="submit-status-card__meta">
                <div class="submit-status-spinner" aria-hidden="true"></div>
                <p class="submit-status-card__redirect mb-0">
                    Redirecting in <?= (int) $redirectSeconds ?> second<?= $redirectSeconds === 1 ? '' : 's' ?>.
                </p>
            </div>
        </div>
    </section>
</main>

<script>
  window.setTimeout(function () {
    window.location.href = <?= json_encode($redirectTarget, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  }, <?= (int) $redirectSeconds * 1000 ?>);
</script>

<?php include 'php/footer.php'; ?>
