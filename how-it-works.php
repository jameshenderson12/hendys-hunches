<?php
session_start();
$page_title = 'How It Works';

require_once __DIR__ . '/php/auth.php';
hh_require_login('index.php');
require_once __DIR__ . '/php/email.php';

$userGuideState = [
    'registered' => true,
    'logged_in' => !empty($_SESSION['login']),
    'has_paid' => null,
    'predictions_submitted' => null,
    'results_live' => null,
];
$guidePaymentUrl = '';

if (file_exists(__DIR__ . '/php/db-connect.php')) {
    include __DIR__ . '/php/db-connect.php';

    if (isset($con) && $con instanceof mysqli) {
        $userId = (int) ($_SESSION['id'] ?? 0);

        if ($userId > 0) {
            $stmt = mysqli_prepare($con, 'SELECT haspaid FROM live_user_information WHERE id = ? LIMIT 1');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $hasPaidValue);
                if (mysqli_stmt_fetch($stmt)) {
                    $userGuideState['has_paid'] = strcasecmp((string) $hasPaidValue, 'Yes') === 0;
                }
                mysqli_stmt_close($stmt);
            }

            $stmt = mysqli_prepare($con, 'SELECT lastupdate FROM live_user_predictions_groups WHERE id = ? LIMIT 1');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $predictionLastUpdate);
                if (mysqli_stmt_fetch($stmt)) {
                    $userGuideState['predictions_submitted'] = !empty($predictionLastUpdate);
                }
                mysqli_stmt_close($stmt);
            }
        }

        $result = mysqli_query($con, 'SELECT COUNT(*) AS total FROM live_match_schedule WHERE homescore IS NOT NULL AND awayscore IS NOT NULL');
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $userGuideState['results_live'] = ((int) ($row['total'] ?? 0)) > 0;
            mysqli_free_result($result);
        }

        mysqli_close($con);
    }
}

$guidePaymentUrl = hh_mail_signup_url(
    (string) ($signup_url ?? ''),
    (string) ($_SESSION['firstname'] ?? ''),
    (string) ($_SESSION['surname'] ?? '')
);

function hh_status_badge(?bool $state, string $doneText = 'Done', string $pendingText = 'To do', string $neutralText = 'Waiting'): string
{
    if ($state === true) {
        return '<span class="guide-status guide-status--done">' . htmlspecialchars($doneText, ENT_QUOTES) . '</span>';
    }

    if ($state === false) {
        return '<span class="guide-status guide-status--pending">' . htmlspecialchars($pendingText, ENT_QUOTES) . '</span>';
    }

    return '<span class="guide-status guide-status--neutral">' . htmlspecialchars($neutralText, ENT_QUOTES) . '</span>';
}

include 'php/header.php';
include 'php/navigation.php';
?>

<main id="main" class="main">
    <div class="page-hero page-hero--guide">
        <div>
            <p class="eyebrow">Game guide</p>
            <h1>How it all works</h1>
            <p class="lead mb-0">A detailed walkthrough of the game and any initial actions to take.</p>
        </div>
        <div class="page-hero__actions">
            <button class="btn btn-light guide-tour-launch" type="button" id="launch-site-tour"><i class="bi bi-signpost-split"></i> Start site tour</button>
            <a class="btn btn-outline-dark" href="predictions.php"><i class="bi bi-pencil-square"></i> Predictions</a>
        </div>
    </div>

    <section class="section info-page guide-page">
        <div class="guide-grid mb-4">
            <div class="guide-panel">
                <p class="eyebrow mb-2">Your progress</p>
                <h2>Quick start</h2>
                <p class="text-muted mb-0">This checklist reflects your current account status, so you can see what is already sorted and what still needs attention.</p>

                <ol class="guide-checklist">
                    <li>
                        <div>
                            <strong>Register to play</strong>
                            <span>Your account is all set and ready for the tournament.</span>
                        </div>
                        <?= hh_status_badge($userGuideState['registered']) ?>
                    </li>
                    <li>
                        <div>
                            <strong>Sign in and head to your dashboard</strong>
                            <span>You are signed in right now, so you can move straight into predictions and rankings.</span>
                        </div>
                        <?= hh_status_badge($userGuideState['logged_in']) ?>
                    </li>
                    <li>
                        <div>
                            <strong>Pay your £10 entry fee</strong>
                            <span>
                                This confirms your place in the game.
                                <?php if ($guidePaymentUrl !== '' && $userGuideState['has_paid'] !== true) : ?>
                                    <a href="<?= htmlspecialchars($guidePaymentUrl, ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer">Pay £10 entry fee</a>.
                                <?php endif; ?>
                            </span>
                        </div>
                        <?= hh_status_badge($userGuideState['has_paid'], 'Done', 'Needs action', 'Awaiting check') ?>
                    </li>
                    <li>
                        <div>
                            <strong>Save your first round of predictions</strong>
                            <span>Fill in every match in the round before your first save, then come back and tweak individual scores until the deadline.</span>
                        </div>
                        <?= hh_status_badge($userGuideState['predictions_submitted'], 'Done', 'Not yet', 'Awaiting') ?>
                    </li>
                </ol>
            </div>

            <div class="guide-panel guide-panel--tour">
                <p class="eyebrow mb-2">Navigation help</p>
                <h2>Take the site tour</h2>
                <p class="text-muted">Rather than describing pages in abstract, this guided tour walks around the actual menu and points out what each section is for.</p>
                <div class="guide-tour-card">
                    <div class="guide-tour-card__icon"><i class="bi bi-compass"></i></div>
                    <div>
                        <strong>See the real navigation in context</strong>
                        <p class="mb-0">Dashboard, Fan Zone, Predictions, Rankings, Competition, About, and your account menu are all covered.</p>
                    </div>
                </div>
                <p class="guide-tour-mobile-note mb-0">Site tour is unavailable on this device.</p>
                <div class="guide-panel__actions">
                    <button class="btn btn-primary guide-tour-launch" type="button" id="launch-site-tour-secondary"><i class="bi bi-play-circle"></i> Launch guided tour</button>
                </div>
            </div>
        </div>

        <div class="guide-panel mb-4">
            <p class="eyebrow mb-2">Prediction windows</p>
            <h2>How stage submissions work</h2>
            <div class="guide-flow">
                <div class="guide-flow__step">
                    <span class="guide-flow__number">1</span>
                    <strong>Each stage has its own window</strong>
                    <p class="mb-0">Group stage, Round of 32, Round of 16 and beyond each open and close separately.</p>
                </div>
                <div class="guide-flow__step">
                    <span class="guide-flow__number">2</span>
                    <strong>Complete the whole stage before saving</strong>
                    <p class="mb-0">A stage is only recorded when every fixture in that stage has a prediction entered and you press save.</p>
                </div>
                <div class="guide-flow__step">
                    <span class="guide-flow__number">3</span>
                    <strong>Edit freely until the deadline</strong>
                    <p class="mb-0">Once a stage has been saved, you can return and change individual predictions right up until that stage locks.</p>
                </div>
            </div>
        </div>

        <div class="guide-panel mb-4">
            <p class="eyebrow mb-2">Scoring</p>
            <h2>Scoring at a glance</h2>
            <p class="text-muted mb-3">For any match, you can score 0, 1, 2, 3 or 7 points depending on how close your prediction is to the real result.</p>

            <div class="guide-score-grid mb-3">
                <div class="guide-score-card guide-score-card--one">
                    <span class="guide-score-card__points">1</span>
                    <strong>One score right</strong>
                    <p class="mb-0">Correct home score or away score.</p>
                </div>
                <div class="guide-score-card guide-score-card--two">
                    <span class="guide-score-card__points">2</span>
                    <strong>Correct outcome</strong>
                    <p class="mb-0">Home win, away win or draw.</p>
                </div>
                <div class="guide-score-card guide-score-card--three">
                    <span class="guide-score-card__points">3</span>
                    <strong>Outcome plus one score</strong>
                    <p class="mb-0">The sweet spot before perfection.</p>
                </div>
                <div class="guide-score-card guide-score-card--seven">
                    <span class="guide-score-card__points">7</span>
                    <strong>Exact scoreline</strong>
                    <p class="mb-0">Maximum points for a perfect call.</p>
                </div>
            </div>

            <div class="content-panel table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>You Predict</th><th>Match Result</th><th>Description</th><th>Points Awarded</th></tr>
                    </thead>
                    <tbody>
                        <tr class="table-success"><td>1 - 0</td><td>1 - 0</td><td>Home win, both correct scores and identical result predicted</td><td><span class="badge bg-success">7</span></td></tr>
                        <tr class="table-success"><td>1 - 2</td><td>1 - 2</td><td>Away win, both correct scores and identical result predicted</td><td><span class="badge bg-success">7</span></td></tr>
                        <tr class="table-success"><td>1 - 1</td><td>1 - 1</td><td>Draw, both correct scores and identical result predicted</td><td><span class="badge bg-success">7</span></td></tr>
                        <tr class="table-warning"><td>3 - 1</td><td>3 - 0</td><td>Home win and correct home score predicted</td><td><span class="badge bg-warning text-dark">3</span></td></tr>
                        <tr class="table-warning"><td>3 - 2</td><td>4 - 2</td><td>Home win and correct away score predicted</td><td><span class="badge bg-warning text-dark">3</span></td></tr>
                        <tr class="table-warning"><td>0 - 2</td><td>0 - 3</td><td>Away win and correct home score predicted</td><td><span class="badge bg-warning text-dark">3</span></td></tr>
                        <tr class="table-warning"><td>1 - 2</td><td>0 - 2</td><td>Away win and correct away score predicted</td><td><span class="badge bg-warning text-dark">3</span></td></tr>
                        <tr class="table-warning"><td>1 - 0</td><td>2 - 1</td><td>Home win predicted</td><td><span class="badge bg-primary">2</span></td></tr>
                        <tr class="table-warning"><td>0 - 3</td><td>1 - 2</td><td>Away win predicted</td><td><span class="badge bg-primary">2</span></td></tr>
                        <tr class="table-warning"><td>3 - 3</td><td>1 - 1</td><td>Draw predicted</td><td><span class="badge bg-primary">2</span></td></tr>
                        <tr class="table-warning"><td>0 - 0</td><td>0 - 1</td><td>Home score predicted</td><td><span class="badge bg-secondary">1</span></td></tr>
                        <tr class="table-warning"><td>1 - 1</td><td>0 - 1</td><td>Away score predicted</td><td><span class="badge bg-secondary">1</span></td></tr>
                        <tr class="table-danger"><td>1 - 0</td><td>0 - 2</td><td>Incorrect outcome and no scores predicted</td><td><span class="badge bg-dark">0</span></td></tr>
                        <tr class="table-danger"><td>0 - 2</td><td>1 - 1</td><td>Incorrect outcome and no scores predicted</td><td><span class="badge bg-dark">0</span></td></tr>
                        <tr class="table-danger"><td>3 - 3</td><td>2 - 1</td><td>Incorrect outcome and no scores predicted</td><td><span class="badge bg-dark">0</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<div class="site-tour" id="site-tour" hidden>
    <div class="site-tour__scrim"></div>
    <div class="site-tour__highlight" id="site-tour-highlight"></div>
    <div class="site-tour__card" id="site-tour-card" role="dialog" aria-modal="true" aria-labelledby="site-tour-title">
        <p class="eyebrow mb-2">Guided tour</p>
        <h3 id="site-tour-title" class="h5 mb-2"></h3>
        <p id="site-tour-body" class="mb-3"></p>
        <div class="site-tour__footer">
            <span id="site-tour-count" class="site-tour__count"></span>
            <div class="site-tour__actions">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="site-tour-back">Back</button>
                <button class="btn btn-outline-secondary btn-sm" type="button" id="site-tour-skip">Close</button>
                <button class="btn btn-primary btn-sm" type="button" id="site-tour-next">Next</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var launchButtons = [
    document.getElementById('launch-site-tour'),
    document.getElementById('launch-site-tour-secondary')
  ].filter(Boolean);

  var tourRoot = document.getElementById('site-tour');
  var highlight = document.getElementById('site-tour-highlight');
  var card = document.getElementById('site-tour-card');
  var title = document.getElementById('site-tour-title');
  var body = document.getElementById('site-tour-body');
  var count = document.getElementById('site-tour-count');
  var nextButton = document.getElementById('site-tour-next');
  var backButton = document.getElementById('site-tour-back');
  var skipButton = document.getElementById('site-tour-skip');

  if (!tourRoot || !highlight || !card || !title || !body || !count || !nextButton || !backButton || !skipButton) {
    return;
  }

  if (window.matchMedia('(max-width: 767.98px)').matches) {
    return;
  }

  var competitionToggle = document.getElementById('tour-nav-competition');
  var accountToggle = document.getElementById('tour-nav-account');
  var competitionDropdown = competitionToggle ? bootstrap.Dropdown.getOrCreateInstance(competitionToggle) : null;
  var accountDropdown = accountToggle ? bootstrap.Dropdown.getOrCreateInstance(accountToggle) : null;

  var steps = [
    { selector: '#tour-nav-dashboard', title: 'Dashboard', body: 'Your home base for the tournament. This is where the headline stuff lives: your profile snapshot, fixtures, standings, and the feel of the game day.' },
    { selector: '#tour-nav-fanzone', title: 'Fan Zone', body: 'The social corner. Use this for chat, reactions, updates, and little community moments as the tournament gets going.' },
    { selector: '#tour-nav-predictions', title: 'Submit Prediction', body: 'This is the business end. Enter your scorelines here before the lock time so you are in the points race for every fixture.' },
    { selector: '#tour-nav-rankings', title: 'Rankings', body: 'The live table of how everybody is doing. Once results start landing, this is where the movement gets fun.' },
    { selector: '#tour-nav-competition', title: 'Competition', body: 'Open this menu for the tournament structure itself. Group stage and knockout stages both sit here, so you can check the shape of the competition quickly.', before: function () { if (competitionDropdown) competitionDropdown.show(); } },
    { selector: '#tour-nav-guide', title: 'How It Works', body: 'This page is your orientation point. Come back here any time you need a refresher on the flow or the scoring.' , after: function () { if (competitionDropdown) competitionDropdown.hide(); } },
    { selector: '#tour-nav-about', title: 'About', body: 'A lighter page with the background to the game itself and the story behind Hendy’s Hunches.' },
    { selector: '#tour-nav-account', title: 'Your account menu', body: 'Your avatar menu is where you jump to your own predictions, change your password, and get to account-level tools.' , before: function () { if (accountDropdown) accountDropdown.show(); }, after: function () { if (accountDropdown) accountDropdown.hide(); } }
  ];

  var currentStep = 0;

  function closeTour() {
    tourRoot.hidden = true;
    document.body.classList.remove('tour-active');
    if (competitionDropdown) {
      competitionDropdown.hide();
    }
    if (accountDropdown) {
      accountDropdown.hide();
    }
  }

  function positionCard(rect) {
    var viewportWidth = window.innerWidth;
    var viewportHeight = window.innerHeight;
    var cardWidth = Math.min(360, viewportWidth - 24);
    var preferredTop = rect.bottom + 16;
    var preferredLeft = Math.min(Math.max(12, rect.left), viewportWidth - cardWidth - 12);

    card.style.width = cardWidth + 'px';
    card.style.left = preferredLeft + 'px';
    card.style.top = preferredTop + 'px';

    var cardRect = card.getBoundingClientRect();
    if (cardRect.bottom > viewportHeight - 12) {
      card.style.top = Math.max(12, rect.top - cardRect.height - 16) + 'px';
    }
  }

  function showStep(index) {
    if (index < 0 || index >= steps.length) {
      return;
    }

    if (steps[currentStep] && typeof steps[currentStep].after === 'function' && currentStep !== index) {
      steps[currentStep].after();
    }

    currentStep = index;
    var step = steps[currentStep];

    if (typeof step.before === 'function') {
      step.before();
    }

    var target = document.querySelector(step.selector);
    if (!target) {
      return;
    }

    target.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });

    window.setTimeout(function () {
      var rect = target.getBoundingClientRect();
      highlight.style.top = Math.max(8, rect.top - 8) + 'px';
      highlight.style.left = Math.max(8, rect.left - 8) + 'px';
      highlight.style.width = rect.width + 16 + 'px';
      highlight.style.height = rect.height + 16 + 'px';

      title.textContent = step.title;
      body.textContent = step.body;
      count.textContent = (currentStep + 1) + ' of ' + steps.length;
      backButton.disabled = currentStep === 0;
      nextButton.textContent = currentStep === steps.length - 1 ? 'Finish' : 'Next';

      positionCard(rect);
    }, 220);
  }

  launchButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      tourRoot.hidden = false;
      document.body.classList.add('tour-active');
      showStep(0);
    });
  });

  nextButton.addEventListener('click', function () {
    if (currentStep === steps.length - 1) {
      closeTour();
      return;
    }

    showStep(currentStep + 1);
  });

  backButton.addEventListener('click', function () {
    if (currentStep > 0) {
      showStep(currentStep - 1);
    }
  });

  skipButton.addEventListener('click', closeTour);
  tourRoot.querySelector('.site-tour__scrim').addEventListener('click', closeTour);
  window.addEventListener('resize', function () {
    if (!tourRoot.hidden) {
      showStep(currentStep);
    }
  });
});
</script>

<?php include 'php/footer.php' ?>
