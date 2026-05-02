<?php
session_start();
$page_title = 'Site Configuration';

require_once dirname(__DIR__) . '/php/auth.php';
require_once dirname(__DIR__) . '/php/config.php';

hh_require_admin('../dashboard.php');

$configPath = dirname(__DIR__) . '/php/config.php';
$messages = [];
$errors = [];

function hh_config_editor_state(): array
{
    global $hh_site_config, $hh_competition_config, $hh_finance_config, $hh_runtime_config, $hh_path_config, $hh_asset_config;

    return [
        'site' => $hh_site_config,
        'competition' => $hh_competition_config,
        'finance' => $hh_finance_config,
        'runtime' => $hh_runtime_config,
        'paths' => $hh_path_config,
        'assets' => $hh_asset_config,
    ];
}

function hh_config_editor_parse_lines(string $value): array
{
    return array_values(
        array_filter(
            array_map('trim', preg_split('/[\r\n,]+/', $value) ?: []),
            static fn ($item) => $item !== ''
        )
    );
}

function hh_config_editor_normalize(array $source, array $current): array
{
    $adminUsernames = hh_config_editor_parse_lines((string) ($source['admin_usernames'] ?? ''));
    $footballKits = hh_config_editor_parse_lines((string) ($source['football_kits'] ?? ''));
    $currentKits = array_values((array) ($current['assets']['football_kits'] ?? []));

    for ($index = count($footballKits); $index < 18; $index++) {
        $footballKits[] = $currentKits[$index] ?? '';
    }
    $footballKits = array_slice($footballKits, 0, 18);

    $groupFixtures = max(0, (int) ($source['no_of_group_fixtures'] ?? 0));
    $ro16Fixtures = max(0, (int) ($source['no_of_ro16_fixtures'] ?? 0));
    $qfFixtures = max(0, (int) ($source['no_of_qf_fixtures'] ?? 0));
    $sfFixtures = max(0, (int) ($source['no_of_sf_fixtures'] ?? 0));
    $finalFixtures = max(0, (int) ($source['no_of_final_fixtures'] ?? 0));

    return [
        'site' => [
            'title' => trim((string) ($source['title'] ?? '')),
            'version' => trim((string) ($source['version'] ?? '')),
            'year' => trim((string) ($source['year'] ?? '')),
            'base_url' => trim((string) ($source['base_url'] ?? '')),
            'developer' => trim((string) ($source['developer'] ?? '')),
            'admin_usernames' => $adminUsernames,
        ],
        'competition' => [
            'competition' => trim((string) ($source['competition'] ?? '')),
            'competition_url' => trim((string) ($source['competition_url'] ?? '')),
            'competition_location' => trim((string) ($source['competition_location'] ?? '')),
            'signup_close_date' => trim((string) ($source['signup_close_date'] ?? '')),
            'signup_url' => trim((string) ($source['signup_url'] ?? '')),
            'charity' => trim((string) ($source['charity'] ?? '')),
            'charity_url' => trim((string) ($source['charity_url'] ?? '')),
        ],
        'finance' => [
            'signup_fee' => round((float) ($source['signup_fee'] ?? 0), 2),
            'charity_fee' => round((float) ($source['charity_fee'] ?? 0), 2),
            'prize_fee' => round((float) ($source['prize_fee'] ?? 0), 2),
        ],
        'runtime' => [
            'no_of_group_fixtures' => $groupFixtures,
            'no_of_ro16_fixtures' => $ro16Fixtures,
            'no_of_qf_fixtures' => $qfFixtures,
            'no_of_sf_fixtures' => $sfFixtures,
            'no_of_final_fixtures' => $finalFixtures,
            'no_of_total_fixtures' => $groupFixtures + $ro16Fixtures + $qfFixtures + $sfFixtures + $finalFixtures,
        ],
        'paths' => [
            'backup_dir' => trim((string) ($source['backup_dir'] ?? '')),
            'datalists_dir' => trim((string) ($source['datalists_dir'] ?? '')),
            'sql_dir' => trim((string) ($source['sql_dir'] ?? '')),
        ],
        'assets' => [
            'football_kits' => $footballKits,
        ],
    ];
}

function hh_config_editor_export(array $config): string
{
    $site = var_export($config['site'], true);
    $competition = var_export($config['competition'], true);
    $finance = var_export($config['finance'], true);
    $runtime = var_export($config['runtime'], true);
    $paths = var_export($config['paths'], true);
    $assets = var_export($config['assets'], true);

    return "<?php\n\n"
        . "/***********************************\n"
        . "* Application: Hendy's Hunches\n"
        . "* File: config.php\n"
        . "* Created By: James Henderson\n"
        . "***********************************/\n\n"
        . "define('IS_PREVIEW', true);\n\n"
        . '$hh_site_config = ' . $site . ";\n\n"
        . '$hh_competition_config = ' . $competition . ";\n\n"
        . '$hh_finance_config = ' . $finance . ";\n\n"
        . '$hh_runtime_config = ' . $runtime . ";\n\n"
        . '$hh_path_config = ' . $paths . ";\n\n"
        . '$hh_asset_config = ' . $assets . ";\n\n"
        . <<<'PHP'
$title = (string) $hh_site_config['title'];
$version = (string) $hh_site_config['version'];
$year = (string) $hh_site_config['year'];
$base_url = (string) $hh_site_config['base_url'];
$developer = (string) $hh_site_config['developer'];
$admin_usernames = array_values(
    array_filter(
        array_map('trim', (array) $hh_site_config['admin_usernames']),
        static fn ($username) => $username !== ''
    )
);

$competition = (string) $hh_competition_config['competition'];
$competition_url = (string) $hh_competition_config['competition_url'];
$competition_location = (string) $hh_competition_config['competition_location'];
$signup_close_date = (string) $hh_competition_config['signup_close_date'];
$signup_url = (string) $hh_competition_config['signup_url'];
$charity = (string) $hh_competition_config['charity'];
$charity_url = (string) $hh_competition_config['charity_url'];

$signup_fee = (float) $hh_finance_config['signup_fee'];
$charity_fee = (float) $hh_finance_config['charity_fee'];
$prize_fee = (float) $hh_finance_config['prize_fee'];
$signup_fee_formatted = number_format($signup_fee, 2, '.', '');
$charity_fee_formatted = number_format($charity_fee, 2, '.', '');
$prize_fee_formatted = number_format($prize_fee, 2, '.', '');

$no_of_group_fixtures = (int) $hh_runtime_config['no_of_group_fixtures'];
$no_of_ro16_fixtures = (int) $hh_runtime_config['no_of_ro16_fixtures'];
$no_of_qf_fixtures = (int) $hh_runtime_config['no_of_qf_fixtures'];
$no_of_sf_fixtures = (int) $hh_runtime_config['no_of_sf_fixtures'];
$no_of_final_fixtures = (int) $hh_runtime_config['no_of_final_fixtures'];
$no_of_total_fixtures = (int) $hh_runtime_config['no_of_total_fixtures'];

$no_of_knockout_fixtures = $no_of_ro16_fixtures + $no_of_qf_fixtures + $no_of_sf_fixtures + $no_of_final_fixtures;

$backup_dir = (string) $hh_path_config['backup_dir'];
$datalists_dir = (string) $hh_path_config['datalists_dir'];
$sql_dir = (string) $hh_path_config['sql_dir'];
$football_kits = array_values((array) $hh_asset_config['football_kits']);
for ($kitIndex = 0; $kitIndex < 18; $kitIndex++) {
    ${'fk' . ($kitIndex + 1)} = $football_kits[$kitIndex] ?? '';
}

if (!function_exists('returnAvatar')) {
    function returnAvatar(): void
    {
        if (!empty($_SESSION['is_dev_bypass'])) {
            print("<img src='img/hh-icon-2024.png' id='avatar' class='img-fluid rounded-circle mx-1' alt='Developer Preview' name='Developer Preview' width='25'> Local Developer");
            return;
        }

        $dbPath = __DIR__ . '/db-connect.php';
        if (!file_exists($dbPath)) {
            print("<img src='img/hh-icon-2024.png' id='avatar' class='img-fluid rounded-circle mx-1' alt='User Avatar' name='User Avatar' width='25'> Preview User");
            return;
        }

        include $dbPath;

        $sql_getavatar = "SELECT firstname, surname, avatar FROM live_user_information WHERE username = '".$_SESSION["username"]."'";
        $getavatar = mysqli_query($con, $sql_getavatar);
        $userid = mysqli_fetch_assoc($getavatar);

        if (!$userid) {
            print("<img src='img/hh-icon-2024.png' id='avatar' class='img-fluid rounded-circle mx-1' alt='User Avatar' name='User Avatar' width='25'> Preview User");
            return;
        }

        $firstname = $userid['firstname'];
        $surname = $userid['surname'];
        $avatar = $userid['avatar'];
        print("<img src='$avatar' id='avatar' class='img-fluid rounded-circle mx-1' alt='User Avatar' name='User Avatar' width='25'> $firstname $surname");
    }
}
?>
PHP;
}

$currentConfig = hh_config_editor_state();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $draftConfig = hh_config_editor_normalize($_POST, $currentConfig);

    if ($draftConfig['site']['title'] === '' || $draftConfig['competition']['competition'] === '') {
        $errors[] = 'Site title and competition name are required.';
        $currentConfig = $draftConfig;
    } elseif (!is_writable($configPath)) {
        $errors[] = 'The config file is not writable, so the changes could not be saved.';
        $currentConfig = $draftConfig;
    } else {
        $configContents = hh_config_editor_export($draftConfig);
        @copy($configPath, $configPath . '.bak');

        if (file_put_contents($configPath, $configContents, LOCK_EX) === false) {
            $errors[] = 'The config file could not be written.';
            $currentConfig = $draftConfig;
        } else {
            header('Location: configuration.php?saved=1');
            exit();
        }
    }
}

if (isset($_GET['saved']) && $_GET['saved'] === '1') {
    $messages[] = 'Configuration saved to php/config.php.';
}

$totalFixtures = (int) ($currentConfig['runtime']['no_of_total_fixtures'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html">
    <meta name="description" content="Hendy's Hunches site configuration">
    <meta name="author" content="James Henderson">
    <title><?= $page_title ?> - Hendy's Hunches</title>
    <link href="../ico/favicon.ico" rel="icon">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <style>
      .admin-shell {
        width: min(1320px, calc(100% - 32px));
        margin: 18px auto 28px;
      }
      .admin-grid {
        display: grid;
        gap: 18px;
      }
      .admin-grid--two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
      .admin-card {
        padding: 22px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(251, 252, 248, 0.96);
        box-shadow: 0 18px 38px rgba(0, 0, 0, 0.14);
      }
      .admin-card h2,
      .admin-card h3 {
        margin: 0 0 12px;
        font-weight: 900;
      }
      .admin-card form {
        display: grid;
        gap: 18px;
      }
      .admin-grid .form-label {
        font-weight: 700;
      }
      .admin-note {
        margin: 0;
        color: var(--hh-muted);
        font-size: 0.92rem;
      }
      .config-summary {
        display: grid;
        gap: 12px;
      }
      .config-summary__item {
        padding: 14px 16px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(143, 102, 216, 0.08);
      }
      .config-summary__item strong,
      .config-summary__item span {
        display: block;
      }
      .config-summary__item span {
        color: var(--hh-muted);
        margin-top: 4px;
      }
      @media (max-width: 991px) {
        .admin-grid--two {
          grid-template-columns: 1fr;
        }
      }
    </style>
</head>
<body>
<?php hh_render_dev_banner('../php/logout.php'); ?>

<div class="admin-shell">
    <div class="page-hero page-hero--admin">
        <div>
            <p class="eyebrow">Admin control room</p>
            <h1>Site Configuration</h1>
            <p class="lead mb-0">A single place to keep the live runtime values, branding and admin usernames tidy without hand-editing PHP.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="functions.php"><i class="bi bi-sliders"></i> Admin functions</a>
            <a class="btn btn-outline-dark" href="../setup/setup-wizard.php"><i class="bi bi-magic"></i> Installation manager</a>
        </div>
    </div>

    <?php foreach ($messages as $message) : ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($message, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $error) : ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <div class="admin-grid admin-grid--two">
        <div class="admin-card">
            <h2>What Lives Here Now</h2>
            <p class="admin-note">This is now focused on real runtime values only: branding, fees, admin access, fixture counts and reusable asset paths.</p>
            <div class="config-summary mt-3">
                <div class="config-summary__item">
                    <strong><?= htmlspecialchars($currentConfig['site']['title'], ENT_QUOTES) ?></strong>
                    <span>Site title</span>
                </div>
                <div class="config-summary__item">
                    <strong><?= htmlspecialchars($currentConfig['competition']['competition'], ENT_QUOTES) ?></strong>
                    <span>Current tournament</span>
                </div>
                <div class="config-summary__item">
                    <strong><?= $totalFixtures ?></strong>
                    <span>Total fixtures tracked at runtime</span>
                </div>
                <div class="config-summary__item">
                    <strong><?= count((array) $currentConfig['site']['admin_usernames']) ?></strong>
                    <span>Configured admin usernames</span>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2>Save Behaviour</h2>
            <p class="admin-note">Saving rewrites <code>php/config.php</code> and keeps a lightweight <code>.bak</code> copy beside it. The page then reloads so you are looking at the fresh file contents, not a stale in-memory copy.</p>
        </div>
    </div>

    <div class="admin-card mt-3">
        <form method="post">
            <section class="admin-grid admin-grid--two">
                <div>
                    <h3>Site Identity</h3>
                    <div class="mb-3">
                        <label class="form-label" for="title">Site title</label>
                        <input class="form-control" id="title" name="title" type="text" value="<?= htmlspecialchars((string) $currentConfig['site']['title'], ENT_QUOTES) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="version">Version</label>
                        <input class="form-control" id="version" name="version" type="text" value="<?= htmlspecialchars((string) $currentConfig['site']['version'], ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="year">Footer year</label>
                        <input class="form-control" id="year" name="year" type="text" value="<?= htmlspecialchars((string) $currentConfig['site']['year'], ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="base_url">Base URL</label>
                        <input class="form-control" id="base_url" name="base_url" type="url" value="<?= htmlspecialchars((string) $currentConfig['site']['base_url'], ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="developer">Developer / owner name</label>
                        <input class="form-control" id="developer" name="developer" type="text" value="<?= htmlspecialchars((string) $currentConfig['site']['developer'], ENT_QUOTES) ?>">
                    </div>
                    <div>
                        <label class="form-label" for="admin_usernames">Admin usernames</label>
                        <textarea class="form-control" id="admin_usernames" name="admin_usernames" rows="5"><?= htmlspecialchars(implode("\n", (array) $currentConfig['site']['admin_usernames']), ENT_QUOTES) ?></textarea>
                        <p class="admin-note mt-2">One username per line. These accounts see the admin menu and can open protected admin pages.</p>
                    </div>
                </div>

                <div>
                    <h3>Competition And Fees</h3>
                    <div class="mb-3">
                        <label class="form-label" for="competition">Competition name</label>
                        <input class="form-control" id="competition" name="competition" type="text" value="<?= htmlspecialchars((string) $currentConfig['competition']['competition'], ENT_QUOTES) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="competition_url">Competition URL</label>
                        <input class="form-control" id="competition_url" name="competition_url" type="url" value="<?= htmlspecialchars((string) $currentConfig['competition']['competition_url'], ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="competition_location">Hosts / location</label>
                        <input class="form-control" id="competition_location" name="competition_location" type="text" value="<?= htmlspecialchars((string) $currentConfig['competition']['competition_location'], ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="signup_close_date">Sign-up close date</label>
                        <input class="form-control" id="signup_close_date" name="signup_close_date" type="text" value="<?= htmlspecialchars((string) $currentConfig['competition']['signup_close_date'], ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="signup_url">Sign-up payment URL</label>
                        <input class="form-control" id="signup_url" name="signup_url" type="url" value="<?= htmlspecialchars((string) $currentConfig['competition']['signup_url'], ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="charity">Charity name</label>
                        <input class="form-control" id="charity" name="charity" type="text" value="<?= htmlspecialchars((string) $currentConfig['competition']['charity'], ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="charity_url">Charity URL</label>
                        <input class="form-control" id="charity_url" name="charity_url" type="url" value="<?= htmlspecialchars((string) $currentConfig['competition']['charity_url'], ENT_QUOTES) ?>">
                    </div>
                    <div class="admin-grid admin-grid--two">
                        <div>
                            <label class="form-label" for="signup_fee">Entry fee</label>
                            <input class="form-control" id="signup_fee" name="signup_fee" type="number" min="0" step="0.01" value="<?= htmlspecialchars(number_format((float) $currentConfig['finance']['signup_fee'], 2, '.', ''), ENT_QUOTES) ?>">
                        </div>
                        <div>
                            <label class="form-label" for="charity_fee">Charity share</label>
                            <input class="form-control" id="charity_fee" name="charity_fee" type="number" min="0" step="0.01" value="<?= htmlspecialchars(number_format((float) $currentConfig['finance']['charity_fee'], 2, '.', ''), ENT_QUOTES) ?>">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="prize_fee">Prize share</label>
                        <input class="form-control" id="prize_fee" name="prize_fee" type="number" min="0" step="0.01" value="<?= htmlspecialchars(number_format((float) $currentConfig['finance']['prize_fee'], 2, '.', ''), ENT_QUOTES) ?>">
                    </div>
                </div>
            </section>

            <section class="admin-grid admin-grid--two">
                <div>
                    <h3>Runtime Fixture Counts</h3>
                    <p class="admin-note mb-3">These values are still used by the live dashboard progress widgets, so they do belong in config for now.</p>
                    <div class="admin-grid admin-grid--two">
                        <div>
                            <label class="form-label" for="no_of_group_fixtures">Group fixtures</label>
                            <input class="form-control js-fixture-count" id="no_of_group_fixtures" name="no_of_group_fixtures" type="number" min="0" step="1" value="<?= (int) $currentConfig['runtime']['no_of_group_fixtures'] ?>">
                        </div>
                        <div>
                            <label class="form-label" for="no_of_ro16_fixtures">Round of 16 fixtures</label>
                            <input class="form-control js-fixture-count" id="no_of_ro16_fixtures" name="no_of_ro16_fixtures" type="number" min="0" step="1" value="<?= (int) $currentConfig['runtime']['no_of_ro16_fixtures'] ?>">
                        </div>
                        <div>
                            <label class="form-label" for="no_of_qf_fixtures">Quarter-final fixtures</label>
                            <input class="form-control js-fixture-count" id="no_of_qf_fixtures" name="no_of_qf_fixtures" type="number" min="0" step="1" value="<?= (int) $currentConfig['runtime']['no_of_qf_fixtures'] ?>">
                        </div>
                        <div>
                            <label class="form-label" for="no_of_sf_fixtures">Semi-final fixtures</label>
                            <input class="form-control js-fixture-count" id="no_of_sf_fixtures" name="no_of_sf_fixtures" type="number" min="0" step="1" value="<?= (int) $currentConfig['runtime']['no_of_sf_fixtures'] ?>">
                        </div>
                        <div>
                            <label class="form-label" for="no_of_final_fixtures">Final fixtures</label>
                            <input class="form-control js-fixture-count" id="no_of_final_fixtures" name="no_of_final_fixtures" type="number" min="0" step="1" value="<?= (int) $currentConfig['runtime']['no_of_final_fixtures'] ?>">
                        </div>
                        <div>
                            <label class="form-label" for="no_of_total_fixtures">Total fixtures</label>
                            <input class="form-control" id="no_of_total_fixtures" type="number" value="<?= $totalFixtures ?>" readonly>
                        </div>
                    </div>
                </div>

                <div>
                    <h3>Installer Defaults And Assets</h3>
                    <div class="mb-3">
                        <label class="form-label" for="backup_dir">Backup directory</label>
                        <input class="form-control" id="backup_dir" name="backup_dir" type="text" value="<?= htmlspecialchars((string) $currentConfig['paths']['backup_dir'], ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="datalists_dir">Text lists directory</label>
                        <input class="form-control" id="datalists_dir" name="datalists_dir" type="text" value="<?= htmlspecialchars((string) $currentConfig['paths']['datalists_dir'], ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="sql_dir">SQL exports directory</label>
                        <input class="form-control" id="sql_dir" name="sql_dir" type="text" value="<?= htmlspecialchars((string) $currentConfig['paths']['sql_dir'], ENT_QUOTES) ?>">
                    </div>
                    <div>
                        <label class="form-label" for="football_kits">Football kit image paths</label>
                        <textarea class="form-control" id="football_kits" name="football_kits" rows="10"><?= htmlspecialchars(implode("\n", (array) $currentConfig['assets']['football_kits']), ENT_QUOTES) ?></textarea>
                        <p class="admin-note mt-2">One image path per line. The registration page uses the first 18 entries.</p>
                    </div>
                </div>
            </section>

            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-floppy"></i> Save configuration</button>
                <a class="btn btn-outline-dark" href="functions.php"><i class="bi bi-arrow-left"></i> Back to admin functions</a>
            </div>
        </form>
    </div>
</div>

<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
  const fixtureInputs = Array.from(document.querySelectorAll('.js-fixture-count'));
  const totalField = document.getElementById('no_of_total_fixtures');

  function refreshFixtureTotal() {
    const total = fixtureInputs.reduce((sum, input) => sum + (parseInt(input.value || '0', 10) || 0), 0);
    if (totalField) {
      totalField.value = total;
    }
  }

  fixtureInputs.forEach((input) => {
    input.addEventListener('input', refreshFixtureTotal);
  });

  refreshFixtureTotal();
</script>
</body>
</html>
