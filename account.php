<?php
session_start();
$page_title = 'My Details';

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/config.php';

hh_require_login('index.php');

include __DIR__ . '/php/db-connect.php';

function hh_account_file_options(string $filePath): array
{
    $options = [];
    $handle = @fopen($filePath, 'r');
    if (!$handle) {
        return $options;
    }

    while (!feof($handle)) {
        $line = fgets($handle, 4096);
        $value = trim((string) $line);
        if ($value !== '') {
            $options[] = $value;
        }
    }

    fclose($handle);

    $options = array_values(array_unique($options));
    natcasesort($options);

    return array_values($options);
}

function hh_account_tournament_team_options(mysqli $con): array
{
    $options = [];
    $result = mysqli_query(
        $con,
        "SELECT hometeam AS team_name FROM live_match_schedule WHERE TRIM(hometeam) <> ''
         UNION
         SELECT awayteam AS team_name FROM live_match_schedule WHERE TRIM(awayteam) <> ''"
    );

    if (!($result instanceof mysqli_result)) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $teamName = trim((string) ($row['team_name'] ?? ''));
        if ($teamName === '' || strcasecmp($teamName, 'To be announced') === 0 || preg_match('/^\d[A-Z]+$/', $teamName)) {
            continue;
        }
        $options[] = $teamName;
    }

    mysqli_free_result($result);

    $options = array_values(array_unique($options));
    natcasesort($options);

    return array_values($options);
}

$messages = [];
$errors = [];
$userId = (int) ($_SESSION['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['account_action'] ?? '') === 'save_profile') {
    $avatar = trim((string) ($_POST['avatar'] ?? ''));
    $fieldofwork = trim((string) ($_POST['fieldofwork'] ?? ''));
    $location = trim((string) ($_POST['location'] ?? ''));
    $faveteam = trim((string) ($_POST['faveteam'] ?? ''));
    $tournwinner = trim((string) ($_POST['tournwinner'] ?? ''));

    $fieldofwork = $fieldofwork !== '' ? $fieldofwork : 'Prefer Not To Say';
    $location = $location !== '' ? $location : 'Prefer Not To Say';
    $faveteam = $faveteam !== '' ? $faveteam : 'Prefer Not To Say';
    $tournwinner = $tournwinner !== '' ? $tournwinner : 'Prefer Not To Say';

    if ($avatar === '') {
        $errors[] = 'Please choose an avatar before saving.';
    } else {
        $statement = mysqli_prepare(
            $con,
            "UPDATE live_user_information
             SET avatar = ?, fieldofwork = ?, location = ?, faveteam = ?, tournwinner = ?
             WHERE id = ?
             LIMIT 1"
        );

        if ($statement) {
            mysqli_stmt_bind_param($statement, 'sssssi', $avatar, $fieldofwork, $location, $faveteam, $tournwinner, $userId);
            mysqli_stmt_execute($statement);
            mysqli_stmt_close($statement);
            $messages[] = 'Your profile details have been updated.';
        } else {
            $errors[] = 'We could not save your profile changes just now.';
        }
    }
}

$profile = null;
$statement = mysqli_prepare(
    $con,
    "SELECT username, firstname, surname, email, avatar, fieldofwork, location, faveteam, tournwinner
     FROM live_user_information
     WHERE id = ?
     LIMIT 1"
);
if ($statement) {
    mysqli_stmt_bind_param($statement, 'i', $userId);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    if ($result instanceof mysqli_result) {
        $profile = mysqli_fetch_assoc($result) ?: null;
        mysqli_free_result($result);
    }
    mysqli_stmt_close($statement);
}

$tournamentWinnerOptions = hh_account_tournament_team_options($con);
if (empty($tournamentWinnerOptions)) {
    $tournamentWinnerOptions = hh_account_file_options(__DIR__ . '/text/select-countryteams-input.txt');
}
$sectorOptions = hh_account_file_options(__DIR__ . '/text/select-sectors-input.txt');
$cityOptions = hh_account_file_options(__DIR__ . '/text/select-ukcities-input.txt');
$clubOptions = hh_account_file_options(__DIR__ . '/text/select-clubteams-input.txt');

mysqli_close($con);

if (!$profile) {
    header('Location: dashboard.php');
    exit;
}

$avatars = [$fk1, $fk2, $fk3, $fk4, $fk5, $fk6, $fk7, $fk8, $fk9, $fk10, $fk11, $fk12, $fk13, $fk14, $fk15, $fk16, $fk17, $fk18];
$selectedAvatar = (string) ($profile['avatar'] ?? '');

include __DIR__ . '/php/header.php';
include __DIR__ . '/php/navigation.php';
?>

<style>
  .account-editor-shell {
    width: min(1120px, calc(100% - 32px));
    margin: 20px auto 32px;
  }
  .account-editor-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(280px, 320px) minmax(0, 1fr);
  }
  .account-editor-card {
    padding: 22px;
    border: 1px solid var(--hh-line);
    border-radius: 8px;
    background: rgba(251, 252, 248, 0.96);
    box-shadow: 0 18px 38px rgba(0, 0, 0, 0.14);
  }
  .account-editor-card h2,
  .account-editor-card h3 {
    margin: 0 0 12px;
    font-weight: 900;
  }
  .account-editor-summary img {
    width: 120px;
    display: block;
    margin: 0 auto 14px;
  }
  .account-editor-summary dl {
    margin: 0;
    display: grid;
    gap: 10px;
  }
  .account-editor-summary dt {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--hh-muted);
    margin-bottom: 2px;
  }
  .account-editor-summary dd {
    margin: 0;
    font-weight: 700;
  }
  .account-editor-form {
    display: grid;
    gap: 18px;
  }
  .account-editor-fields {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .account-editor-avatars {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(6, minmax(0, 1fr));
  }
  .account-editor-avatars .btn-check:checked + label {
    background: rgba(143, 102, 216, 0.12);
    border-color: rgba(143, 102, 216, 0.34);
  }
  .account-editor-avatars label {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px;
    border: 1px solid var(--hh-line);
    border-radius: 8px;
    background: #f6f7f2;
    cursor: pointer;
  }
  .account-editor-avatars img {
    width: 100%;
    max-width: 74px;
    height: auto;
  }
  .account-editor-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
  }
  .account-editor-note {
    margin: 0;
    color: var(--hh-muted);
    font-size: 0.92rem;
  }
  @media (max-width: 991px) {
    .account-editor-grid,
    .account-editor-fields,
    .account-editor-avatars {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="account-editor-shell">
  <div class="page-hero page-hero--account">
    <div>
      <p class="eyebrow">Player settings</p>
      <h1>My Details</h1>
      <p class="lead mb-0">Keep your optional details and avatar current without touching the core account bits that keep the game steady.</p>
    </div>
    <div class="page-hero__actions">
      <a class="btn btn-outline-dark" href="change-password.php"><i class="bi bi-shield-lock"></i> Change password</a>
      <a class="btn btn-primary" href="dashboard.php"><i class="bi bi-grid"></i> Back to dashboard</a>
    </div>
  </div>

  <?php foreach ($messages as $message) : ?>
    <div class="alert alert-success" role="alert"><?= htmlspecialchars($message, ENT_QUOTES) ?></div>
  <?php endforeach; ?>

  <?php foreach ($errors as $error) : ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
  <?php endforeach; ?>

  <div class="account-editor-grid">
    <aside class="account-editor-card account-editor-summary">
      <img src="<?= htmlspecialchars($selectedAvatar, ENT_QUOTES) ?>" alt="<?= htmlspecialchars((string) ($profile['username'] ?? 'Player'), ENT_QUOTES) ?> kit avatar" id="accountAvatarPreview">
      <h2><?= htmlspecialchars((string) ($profile['firstname'] ?? '') . ' ' . (string) ($profile['surname'] ?? ''), ENT_QUOTES) ?></h2>
      <p class="account-editor-note mb-3">@<?= htmlspecialchars((string) ($profile['username'] ?? ''), ENT_QUOTES) ?></p>
      <dl>
        <div>
          <dt>Email</dt>
          <dd><?= htmlspecialchars((string) ($profile['email'] ?? ''), ENT_QUOTES) ?></dd>
        </div>
        <div>
          <dt>Current location</dt>
          <dd><?= htmlspecialchars((string) ($profile['location'] ?? 'Prefer Not To Say'), ENT_QUOTES) ?></dd>
        </div>
        <div>
          <dt>Favourite team</dt>
          <dd><?= htmlspecialchars((string) ($profile['faveteam'] ?? 'Prefer Not To Say'), ENT_QUOTES) ?></dd>
        </div>
        <div>
          <dt>Winner pick</dt>
          <dd><?= htmlspecialchars((string) ($profile['tournwinner'] ?? 'Prefer Not To Say'), ENT_QUOTES) ?></dd>
        </div>
      </dl>
    </aside>

    <section class="account-editor-card">
      <h3>Edit Optional Details</h3>
      <p class="account-editor-note mb-3">This is intentionally lightweight: avatar plus the optional profile details that add a bit of personality around the game.</p>

      <form method="post" class="account-editor-form">
        <input type="hidden" name="account_action" value="save_profile">
        <input type="hidden" name="avatar" id="accountAvatarInput" value="<?= htmlspecialchars($selectedAvatar, ENT_QUOTES) ?>">

        <div>
          <label class="form-label">Choose your avatar</label>
          <div class="account-editor-avatars">
            <?php foreach ($avatars as $index => $avatar) : ?>
              <div>
                <input
                  type="radio"
                  class="btn-check"
                  name="avatar_choice"
                  id="account_avatar_<?= $index + 1 ?>"
                  value="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>"
                  <?= $selectedAvatar === $avatar ? 'checked' : '' ?>
                >
                <label for="account_avatar_<?= $index + 1 ?>">
                  <img src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="Football kit avatar <?= $index + 1 ?>">
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="account-editor-fields">
          <div>
            <label for="fieldofwork" class="form-label">Field of expertise</label>
            <input id="fieldofwork" name="fieldofwork" class="form-control" list="accountFieldOptions" value="<?= htmlspecialchars((string) (($profile['fieldofwork'] ?? '') === 'Prefer Not To Say' ? '' : $profile['fieldofwork']), ENT_QUOTES) ?>" placeholder="Start typing to filter">
            <datalist id="accountFieldOptions">
              <option value="Prefer Not To Say"></option>
              <?php foreach ($sectorOptions as $option) : ?>
                <option value="<?= htmlspecialchars($option, ENT_QUOTES) ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </div>

          <div>
            <label for="location" class="form-label">Location</label>
            <input id="location" name="location" class="form-control" list="accountLocationOptions" value="<?= htmlspecialchars((string) (($profile['location'] ?? '') === 'Prefer Not To Say' ? '' : $profile['location']), ENT_QUOTES) ?>" placeholder="Start typing to filter">
            <datalist id="accountLocationOptions">
              <option value="Prefer Not To Say"></option>
              <?php foreach ($cityOptions as $option) : ?>
                <option value="<?= htmlspecialchars($option, ENT_QUOTES) ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </div>

          <div>
            <label for="faveteam" class="form-label">Favourite team</label>
            <input id="faveteam" name="faveteam" class="form-control" list="accountFavouriteOptions" value="<?= htmlspecialchars((string) (($profile['faveteam'] ?? '') === 'Prefer Not To Say' ? '' : $profile['faveteam']), ENT_QUOTES) ?>" placeholder="Start typing to filter">
            <datalist id="accountFavouriteOptions">
              <option value="Prefer Not To Say"></option>
              <?php foreach ($clubOptions as $option) : ?>
                <option value="<?= htmlspecialchars($option, ENT_QUOTES) ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </div>

          <div>
            <label for="tournwinner" class="form-label">Who'll win <?= htmlspecialchars((string) ($competition ?? 'the tournament'), ENT_QUOTES) ?>?</label>
            <input id="tournwinner" name="tournwinner" class="form-control" list="accountWinnerOptions" value="<?= htmlspecialchars((string) (($profile['tournwinner'] ?? '') === 'Prefer Not To Say' ? '' : $profile['tournwinner']), ENT_QUOTES) ?>" placeholder="Start typing to filter">
            <datalist id="accountWinnerOptions">
              <option value="Prefer Not To Say"></option>
              <?php foreach ($tournamentWinnerOptions as $option) : ?>
                <option value="<?= htmlspecialchars($option, ENT_QUOTES) ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </div>
        </div>

        <div class="account-editor-actions">
          <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save details</button>
          <a class="btn btn-outline-dark" href="user.php?id=<?= (int) $userId ?>"><i class="bi bi-person-badge"></i> View my profile</a>
        </div>
      </form>
    </section>
  </div>
</div>

<script>
  (() => {
    const avatarInput = document.getElementById('accountAvatarInput');
    const avatarPreview = document.getElementById('accountAvatarPreview');
    const avatarChoices = Array.from(document.querySelectorAll('input[name="avatar_choice"]'));

    avatarChoices.forEach((choice) => {
      choice.addEventListener('change', () => {
        avatarInput.value = choice.value;
        avatarPreview.src = choice.value;
      });
    });
  })();
</script>

<?php include __DIR__ . '/php/footer.php'; ?>
