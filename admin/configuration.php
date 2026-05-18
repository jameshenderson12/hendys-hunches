<?php
session_start();
$page_title = 'Site Configuration';

require_once dirname(__DIR__) . '/php/auth.php';
require_once dirname(__DIR__) . '/php/config.php';
require_once dirname(__DIR__) . '/php/email.php';

hh_require_admin('../dashboard.php');

$configPath = dirname(__DIR__) . '/php/config.php';
$messages = [];
$errors = [];
$testEmailAddress = trim((string) ($_POST['test_email_to'] ?? ''));
$testEmailName = trim((string) ($_POST['test_email_name'] ?? ''));

function hh_config_editor_state(): array
{
    global $hh_site_config, $hh_competition_config, $hh_finance_config, $hh_runtime_config, $hh_preview_config, $hh_email_config, $hh_path_config, $hh_asset_config;

    return [
        'site' => $hh_site_config,
        'competition' => $hh_competition_config,
        'finance' => $hh_finance_config,
        'runtime' => $hh_runtime_config,
        'preview' => $hh_preview_config,
        'email' => $hh_email_config,
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
    $ro32Fixtures = max(0, (int) ($source['no_of_ro32_fixtures'] ?? 0));
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
            'no_of_ro32_fixtures' => $ro32Fixtures,
            'no_of_ro16_fixtures' => $ro16Fixtures,
            'no_of_qf_fixtures' => $qfFixtures,
            'no_of_sf_fixtures' => $sfFixtures,
            'no_of_final_fixtures' => $finalFixtures,
            'no_of_total_fixtures' => $groupFixtures + $ro32Fixtures + $ro16Fixtures + $qfFixtures + $sfFixtures + $finalFixtures,
        ],
        'preview' => [
            'today_override' => trim((string) ($source['today_override'] ?? '')),
        ],
        'email' => [
            'enabled' => !empty($source['email_enabled']),
            'transport' => in_array((string) ($source['email_transport'] ?? 'smtp'), ['smtp', 'mail'], true) ? (string) $source['email_transport'] : 'smtp',
            'from_name' => trim((string) ($source['email_from_name'] ?? '')),
            'from_email' => trim((string) ($source['email_from_email'] ?? '')),
            'reply_to_name' => trim((string) ($source['email_reply_to_name'] ?? '')),
            'reply_to_email' => trim((string) ($source['email_reply_to_email'] ?? '')),
            'smtp_host' => trim((string) ($source['email_smtp_host'] ?? '')),
            'smtp_port' => max(1, (int) ($source['email_smtp_port'] ?? 587)),
            'smtp_secure' => in_array((string) ($source['email_smtp_secure'] ?? 'tls'), ['', 'tls', 'ssl'], true) ? (string) $source['email_smtp_secure'] : 'tls',
            'smtp_username' => trim((string) ($source['email_smtp_username'] ?? '')),
            'smtp_password' => (string) ($source['email_smtp_password'] ?? ''),
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
    $preview = var_export($config['preview'], true);
    $email = var_export($config['email'], true);
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
        . '$hh_preview_config = ' . $preview . ";\n\n"
        . '$hh_email_config = ' . $email . ";\n\n"
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

$no_of_group_fixtures = (int) ($hh_runtime_config['no_of_group_fixtures'] ?? 0);
$no_of_ro32_fixtures = (int) ($hh_runtime_config['no_of_ro32_fixtures'] ?? 0);
$no_of_ro16_fixtures = (int) ($hh_runtime_config['no_of_ro16_fixtures'] ?? 0);
$no_of_qf_fixtures = (int) ($hh_runtime_config['no_of_qf_fixtures'] ?? 0);
$no_of_sf_fixtures = (int) ($hh_runtime_config['no_of_sf_fixtures'] ?? 0);
$no_of_final_fixtures = (int) ($hh_runtime_config['no_of_final_fixtures'] ?? 0);
$no_of_total_fixtures = (int) ($hh_runtime_config['no_of_total_fixtures'] ?? 0);
$preview_today_override = trim((string) ($hh_preview_config['today_override'] ?? ''));

$no_of_knockout_fixtures = $no_of_ro32_fixtures + $no_of_ro16_fixtures + $no_of_qf_fixtures + $no_of_sf_fixtures + $no_of_final_fixtures;

$mail_enabled = !empty($hh_email_config['enabled']);

$backup_dir = (string) $hh_path_config['backup_dir'];
$datalists_dir = (string) $hh_path_config['datalists_dir'];
$sql_dir = (string) $hh_path_config['sql_dir'];
$football_kits = array_values((array) $hh_asset_config['football_kits']);
for ($kitIndex = 0; $kitIndex < 18; $kitIndex++) {
    ${'fk' . ($kitIndex + 1)} = $football_kits[$kitIndex] ?? '';
}

if (!function_exists('hh_knockout_label_from_fixture_count')) {
    function hh_knockout_label_from_fixture_count(int $fixtureCount, bool $isFinalStage = false): string
    {
        if ($isFinalStage) {
            return $fixtureCount > 1 ? 'Final Stage' : 'Final';
        }

        return match ($fixtureCount) {
            16 => 'Round of 32',
            8 => 'Round of 16',
            4 => 'Quarter-Finals',
            2 => 'Semi-Finals',
            1 => 'Knockout Match',
            default => 'Knockout Round',
        };
    }
}

if (!function_exists('hh_stage_blueprint')) {
    function hh_stage_blueprint(): array
    {
        global $no_of_group_fixtures, $no_of_ro32_fixtures, $no_of_ro16_fixtures, $no_of_qf_fixtures, $no_of_sf_fixtures, $no_of_final_fixtures;

        $stages = [
            [
                'key' => 'groups',
                'label' => 'Group Stage',
                'fixtures' => $no_of_group_fixtures,
                'table' => 'live_user_predictions_groups',
                'legacy_key' => 'groups',
            ],
        ];

        $knockoutStages = [
            ['key' => 'ro32', 'label' => hh_knockout_label_from_fixture_count($no_of_ro32_fixtures), 'fixtures' => $no_of_ro32_fixtures, 'table' => 'live_user_predictions_ro32'],
            ['key' => 'ro16', 'label' => hh_knockout_label_from_fixture_count($no_of_ro16_fixtures), 'fixtures' => $no_of_ro16_fixtures, 'table' => 'live_user_predictions_ro16'],
            ['key' => 'qf', 'label' => hh_knockout_label_from_fixture_count($no_of_qf_fixtures), 'fixtures' => $no_of_qf_fixtures, 'table' => 'live_user_predictions_qf'],
            ['key' => 'sf', 'label' => hh_knockout_label_from_fixture_count($no_of_sf_fixtures), 'fixtures' => $no_of_sf_fixtures, 'table' => 'live_user_predictions_sf'],
            ['key' => 'final', 'label' => hh_knockout_label_from_fixture_count($no_of_final_fixtures, true), 'fixtures' => $no_of_final_fixtures, 'table' => 'live_user_predictions_final'],
        ];

        foreach ($knockoutStages as $stage) {
            if (($stage['fixtures'] ?? 0) > 0) {
                $stages[] = $stage;
            }
        }

        return $stages;
    }
}

if (!function_exists('hh_prediction_stage_contexts')) {
    function hh_prediction_stage_contexts(): array
    {
        global $no_of_group_fixtures, $no_of_ro32_fixtures, $no_of_ro16_fixtures, $no_of_qf_fixtures, $no_of_sf_fixtures, $no_of_final_fixtures;

        $contexts = [];
        $fixtureStart = 1;
        $scoreStart = 1;

        foreach (hh_stage_blueprint() as $stage) {
            $fixtureCount = (int) ($stage['fixtures'] ?? 0);
            if ($fixtureCount <= 0) {
                continue;
            }

            $scoreCount = $fixtureCount * 2;
            $contexts[(string) $stage['key']] = [
                'key' => (string) $stage['key'],
                'label' => (string) $stage['label'],
                'table' => (string) $stage['table'],
                'fixtures' => $fixtureCount,
                'fixture_start' => $fixtureStart,
                'fixture_end' => $fixtureStart + $fixtureCount - 1,
                'score_start' => $scoreStart,
                'score_end' => $scoreStart + $scoreCount - 1,
            ];

            $fixtureStart += $fixtureCount;
            $scoreStart += $scoreCount;
        }

        return $contexts;
    }
}

if (!function_exists('hh_is_preview_mode')) {
    function hh_is_preview_mode(): bool
    {
        return defined('IS_PREVIEW') && IS_PREVIEW;
    }
}

if (!function_exists('hh_effective_now')) {
    function hh_effective_now(?DateTimeZone $timezone = null): DateTimeImmutable
    {
        global $preview_today_override;

        $timezone = $timezone ?? new DateTimeZone(date_default_timezone_get());
        $now = new DateTimeImmutable('now', $timezone);

        if (hh_is_preview_mode() && $preview_today_override !== '') {
            $override = DateTimeImmutable::createFromFormat('Y-m-d', $preview_today_override, $timezone);
            if ($override instanceof DateTimeImmutable) {
                return $override->setTime(
                    (int) $now->format('H'),
                    (int) $now->format('i'),
                    (int) $now->format('s')
                );
            }
        }

        return $now;
    }
}

if (!function_exists('hh_effective_today')) {
    function hh_effective_today(?DateTimeZone $timezone = null): DateTimeImmutable
    {
        $timezone = $timezone ?? new DateTimeZone(date_default_timezone_get());
        return hh_effective_now($timezone)->setTime(0, 0, 0);
    }
}

if (!function_exists('hh_effective_today_sql')) {
    function hh_effective_today_sql(): string
    {
        return hh_effective_today()->format('Y-m-d');
    }
}

if (!function_exists('hh_effective_today_label')) {
    function hh_effective_today_label(string $format = 'jS F, Y'): string
    {
        return hh_effective_today()->format($format);
    }
}

if (!function_exists('hh_prediction_stage_windows')) {
    function hh_prediction_stage_windows(mysqli $con): array
    {
        $contexts = hh_prediction_stage_contexts();
        if (empty($contexts)) {
            return [];
        }

        $rowsByMatch = [];
        $result = mysqli_query($con, "SELECT match_number, date, kotime, stage, venue FROM live_match_schedule ORDER BY match_number ASC");
        if ($result instanceof mysqli_result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $matchNumber = (int) ($row['match_number'] ?? 0);
                if ($matchNumber > 0) {
                    $rowsByMatch[$matchNumber] = $row;
                }
            }
            mysqli_free_result($result);
        }

        $windows = [];
        $previousLastKickoff = null;
        $effectiveNowUtc = hh_effective_now(new DateTimeZone('UTC'));
        $upcomingThresholdUtc = $effectiveNowUtc->modify('+3 days');

        foreach ($contexts as $key => $context) {
            $kickoffs = [];
            for ($matchNumber = $context['fixture_start']; $matchNumber <= $context['fixture_end']; $matchNumber++) {
                $row = $rowsByMatch[$matchNumber] ?? null;
                if (!$row) {
                    continue;
                }

                $dateValue = trim((string) ($row['date'] ?? ''));
                $timeValue = trim((string) ($row['kotime'] ?? ''));
                if ($dateValue === '' || $timeValue === '') {
                    continue;
                }

                $kickoff = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateValue . ' ' . $timeValue, new DateTimeZone('UTC'));
                if ($kickoff instanceof DateTimeImmutable) {
                    $kickoffs[] = $kickoff;
                }
            }

            usort($kickoffs, static fn(DateTimeImmutable $left, DateTimeImmutable $right): int => $left <=> $right);

            $firstKickoff = $kickoffs[0] ?? null;
            $lastKickoff = !empty($kickoffs) ? $kickoffs[count($kickoffs) - 1] : null;
            $opensAt = $key === 'groups' ? null : ($previousLastKickoff instanceof DateTimeImmutable ? $previousLastKickoff->modify('+5 hours') : null);
            $closesAt = $firstKickoff instanceof DateTimeImmutable ? $firstKickoff->modify('-2 hours') : null;

            $status = 'pending';
            if ($firstKickoff instanceof DateTimeImmutable) {
                if ($opensAt instanceof DateTimeImmutable && $effectiveNowUtc < $opensAt) {
                    $status = $opensAt <= $upcomingThresholdUtc ? 'upcoming' : 'na';
                } elseif ($closesAt instanceof DateTimeImmutable && $effectiveNowUtc >= $closesAt) {
                    $status = 'closed';
                } else {
                    $status = 'open';
                }
            }

            $windows[$key] = $context + [
                'first_kickoff' => $firstKickoff,
                'last_kickoff' => $lastKickoff,
                'opens_at' => $opensAt,
                'closes_at' => $closesAt,
                'status' => $status,
                'is_open' => $status === 'open',
            ];

            if ($lastKickoff instanceof DateTimeImmutable) {
                $previousLastKickoff = $lastKickoff;
            }
        }

        return $windows;
    }
}

if (!function_exists('hh_stage_status_label')) {
    function hh_stage_status_label(string $status): string
    {
        return match ($status) {
            'na' => 'N/A',
            'open' => 'Open',
            'upcoming' => 'Upcoming',
            'closed' => 'Closed',
            'pending' => 'Pending',
            default => ucfirst($status),
        };
    }
}

if (!function_exists('returnAvatar')) {
    function returnAvatar(): void
    {
        global $app_path_prefix;
        $assetPrefix = $app_path_prefix ?? '';
        $fallbackLabel = !empty($_SESSION['firstname']) || !empty($_SESSION['surname'])
            ? trim((string) ($_SESSION['firstname'] ?? '') . ' ' . (string) ($_SESSION['surname'] ?? ''))
            : 'Preview User';

        if (!empty($_SESSION['is_dev_bypass'])) {
            print("<img src='" . $assetPrefix . "img/hh-icon-2024.png' id='avatar' class='img-fluid rounded-circle mx-1' alt='Developer Preview' name='Developer Preview' width='25'> Local Developer");
            return;
        }

        $dbPath = __DIR__ . '/db-connect.php';
        if (!file_exists($dbPath)) {
            print("<img src='" . $assetPrefix . "img/hh-icon-2024.png' id='avatar' class='img-fluid rounded-circle mx-1' alt='User Avatar' name='User Avatar' width='25'> " . htmlspecialchars($fallbackLabel, ENT_QUOTES));
            return;
        }

        include $dbPath;

        try {
            $sql_getavatar = "SELECT firstname, surname, avatar FROM live_user_information WHERE username = '".$_SESSION["username"]."'";
            $getavatar = mysqli_query($con, $sql_getavatar);
            $userid = $getavatar instanceof mysqli_result ? mysqli_fetch_assoc($getavatar) : null;
            if ($getavatar instanceof mysqli_result) {
                mysqli_free_result($getavatar);
            }
        } catch (Throwable $exception) {
            print("<img src='" . $assetPrefix . "img/hh-icon-2024.png' id='avatar' class='img-fluid rounded-circle mx-1' alt='User Avatar' name='User Avatar' width='25'> " . htmlspecialchars($fallbackLabel, ENT_QUOTES));
            return;
        }

        if (!$userid) {
            print("<img src='" . $assetPrefix . "img/hh-icon-2024.png' id='avatar' class='img-fluid rounded-circle mx-1' alt='User Avatar' name='User Avatar' width='25'> " . htmlspecialchars($fallbackLabel, ENT_QUOTES));
            return;
        }

        $firstname = $userid['firstname'];
        $surname = $userid['surname'];
        $avatar = trim((string) ($userid['avatar'] ?? ''));
        if ($avatar === '') {
            $avatar = $assetPrefix . 'img/hh-icon-2024.png';
        } elseif (!preg_match('#^(?:[a-z]+:)?//#i', $avatar) && !str_starts_with($avatar, '/')) {
            $avatar = $assetPrefix . ltrim($avatar, './');
        }
        print("<img src='$avatar' id='avatar' class='img-fluid rounded-circle mx-1' alt='User Avatar' name='User Avatar' width='25'> $firstname $surname");
    }
}
?>
PHP;
}

$currentConfig = hh_config_editor_state();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $draftConfig = hh_config_editor_normalize($_POST, $currentConfig);
    $action = trim((string) ($_POST['configuration_action'] ?? 'save'));

    if ($draftConfig['site']['title'] === '' || $draftConfig['competition']['competition'] === '') {
        $errors[] = 'Site title and competition name are required.';
        $currentConfig = $draftConfig;
    } elseif ($action === 'send_test_email') {
        $currentConfig = $draftConfig;
        $hh_email_config = $draftConfig['email'];

        if ($testEmailAddress === '') {
            $errors[] = 'Enter an email address to receive the test message.';
        } elseif (!filter_var($testEmailAddress, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid recipient email address for the test message.';
        } elseif (!hh_mail_is_enabled()) {
            $errors[] = 'Enable outbound email before sending a test message.';
        } elseif (!sendTestEmail($testEmailAddress, $testEmailName)) {
            $errors[] = 'The test email could not be sent. Check the SMTP details and try again.';
        } else {
            $messages[] = 'Test email sent to ' . $testEmailAddress . '.';
        }
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

$app_path_prefix = '../';
$app_logout_path = '../php/logout.php';
include '../php/header.php';
include '../php/navigation.php';
?>
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

<div class="admin-shell">
    <div class="page-hero page-hero--admin">
        <div>
            <p class="eyebrow" style="color: #FF0000 !important">Admin control room</p>
            <h1>Site Configuration</h1>
            <p class="lead mb-0">A place to keep the live runtime values, branding and admin usernames tidy.</p>
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
                    <strong><?= htmlspecialchars((string) (($currentConfig['preview']['today_override'] ?? '') !== '' ? $currentConfig['preview']['today_override'] : 'Live system date'), ENT_QUOTES) ?></strong>
                    <span>Preview day used as “today”</span>
                </div>
                <div class="config-summary__item">
                    <strong><?= count((array) $currentConfig['site']['admin_usernames']) ?></strong>
                    <span>Configured admin usernames</span>
                </div>
                <div class="config-summary__item">
                    <strong><?= !empty($currentConfig['email']['enabled']) ? 'Enabled' : 'Disabled' ?></strong>
                    <span>Player email delivery</span>
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
                            <label class="form-label" for="no_of_ro32_fixtures">Round of 32 fixtures</label>
                            <input class="form-control js-fixture-count" id="no_of_ro32_fixtures" name="no_of_ro32_fixtures" type="number" min="0" step="1" value="<?= (int) ($currentConfig['runtime']['no_of_ro32_fixtures'] ?? 0) ?>">
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
                    <h3>Preview Date</h3>
                    <div class="mb-3">
                        <label class="form-label" for="today_override">Preview today override</label>
                        <input class="form-control" id="today_override" name="today_override" type="date" value="<?= htmlspecialchars((string) ($currentConfig['preview']['today_override'] ?? ''), ENT_QUOTES) ?>">
                        <p class="admin-note mt-2">Leave blank to use the real current day. Set a tournament date such as <code>2026-06-11</code> or <code>2026-06-12</code> to preview what the dashboard treats as today.</p>
                    </div>

                    <h3>Email Comms</h3>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" id="email_enabled" name="email_enabled" type="checkbox" value="1" <?= !empty($currentConfig['email']['enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="email_enabled">Enable outbound email</label>
                    </div>
                    <div class="admin-grid admin-grid--two">
                        <div>
                            <label class="form-label" for="email_transport">Transport</label>
                            <select class="form-select" id="email_transport" name="email_transport">
                                <option value="smtp" <?= (($currentConfig['email']['transport'] ?? 'smtp') === 'smtp') ? 'selected' : '' ?>>SMTP</option>
                                <option value="mail" <?= (($currentConfig['email']['transport'] ?? '') === 'mail') ? 'selected' : '' ?>>PHP mail()</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="email_from_name">From name</label>
                            <input class="form-control" id="email_from_name" name="email_from_name" type="text" value="<?= htmlspecialchars((string) ($currentConfig['email']['from_name'] ?? ''), ENT_QUOTES) ?>">
                        </div>
                        <div>
                            <label class="form-label" for="email_from_email">From email</label>
                            <input class="form-control" id="email_from_email" name="email_from_email" type="email" value="<?= htmlspecialchars((string) ($currentConfig['email']['from_email'] ?? ''), ENT_QUOTES) ?>">
                        </div>
                        <div>
                            <label class="form-label" for="email_reply_to_email">Reply-to email</label>
                            <input class="form-control" id="email_reply_to_email" name="email_reply_to_email" type="email" value="<?= htmlspecialchars((string) ($currentConfig['email']['reply_to_email'] ?? ''), ENT_QUOTES) ?>">
                        </div>
                        <div>
                            <label class="form-label" for="email_reply_to_name">Reply-to name</label>
                            <input class="form-control" id="email_reply_to_name" name="email_reply_to_name" type="text" value="<?= htmlspecialchars((string) ($currentConfig['email']['reply_to_name'] ?? ''), ENT_QUOTES) ?>">
                        </div>
                        <div>
                            <label class="form-label" for="email_smtp_host">SMTP host</label>
                            <input class="form-control" id="email_smtp_host" name="email_smtp_host" type="text" value="<?= htmlspecialchars((string) ($currentConfig['email']['smtp_host'] ?? ''), ENT_QUOTES) ?>">
                        </div>
                        <div>
                            <label class="form-label" for="email_smtp_port">SMTP port</label>
                            <input class="form-control" id="email_smtp_port" name="email_smtp_port" type="number" min="1" step="1" value="<?= (int) ($currentConfig['email']['smtp_port'] ?? 587) ?>">
                        </div>
                        <div>
                            <label class="form-label" for="email_smtp_secure">SMTP security</label>
                            <select class="form-select" id="email_smtp_secure" name="email_smtp_secure">
                                <option value="" <?= (($currentConfig['email']['smtp_secure'] ?? '') === '') ? 'selected' : '' ?>>None</option>
                                <option value="tls" <?= (($currentConfig['email']['smtp_secure'] ?? 'tls') === 'tls') ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= (($currentConfig['email']['smtp_secure'] ?? '') === 'ssl') ? 'selected' : '' ?>>SSL</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="email_smtp_username">SMTP username</label>
                            <input class="form-control" id="email_smtp_username" name="email_smtp_username" type="text" value="<?= htmlspecialchars((string) ($currentConfig['email']['smtp_username'] ?? ''), ENT_QUOTES) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email_smtp_password">SMTP password</label>
                        <input class="form-control" id="email_smtp_password" name="email_smtp_password" type="text" value="<?= htmlspecialchars((string) ($currentConfig['email']['smtp_password'] ?? ''), ENT_QUOTES) ?>">
                        <p class="admin-note mt-2">Keep this disabled until you are happy with the SMTP details. Once enabled, registration and password reset emails can be sent for real.</p>
                    </div>
                    <div class="admin-card" style="padding:16px 18px;">
                        <h3 class="mb-2">Send Test Email</h3>
                        <p class="admin-note mb-3">Uses the email settings currently shown on this form, even if you have not saved them yet.</p>
                        <div class="admin-grid admin-grid--two">
                            <div>
                                <label class="form-label" for="test_email_to">Recipient email</label>
                                <input class="form-control" id="test_email_to" name="test_email_to" type="email" value="<?= htmlspecialchars($testEmailAddress, ENT_QUOTES) ?>" placeholder="you@example.com">
                            </div>
                            <div>
                                <label class="form-label" for="test_email_name">Recipient name</label>
                                <input class="form-control" id="test_email_name" name="test_email_name" type="text" value="<?= htmlspecialchars($testEmailName, ENT_QUOTES) ?>" placeholder="Optional">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-outline-dark" type="submit" name="configuration_action" value="send_test_email"><i class="bi bi-envelope-check"></i> Send test email</button>
                        </div>
                    </div>

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
                <button class="btn btn-primary" type="submit" name="configuration_action" value="save"><i class="bi bi-floppy"></i> Save configuration</button>
                <a class="btn btn-outline-dark" href="functions.php"><i class="bi bi-arrow-left"></i> Back to admin functions</a>
            </div>
        </form>
    </div>
</div>

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
<?php include "../php/footer.php"; ?>
