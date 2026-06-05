<?php
session_start();
$page_title = 'Communications';

require_once dirname(__DIR__) . '/php/auth.php';
require_once dirname(__DIR__) . '/php/config.php';
require_once dirname(__DIR__) . '/php/email.php';

hh_require_admin('../dashboard.php');

include '../php/db-connect.php';

$welcomeTemplatePath = dirname(__DIR__) . '/template/email_welcome.html';
$resetTemplatePath = dirname(__DIR__) . '/template/email_temppass.html';
$tempTableSetupPath = dirname(__DIR__) . '/sql/setup-temp-information.sql';

function hh_comm_table_exists(mysqli $con, string $table): bool
{
    $escaped = mysqli_real_escape_string($con, $table);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$escaped}'");
    if (!($result instanceof mysqli_result)) {
        return false;
    }

    $exists = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);

    return $exists;
}

function hh_comm_load_users(mysqli $con): array
{
    $users = [];
    $result = mysqli_query(
        $con,
        "SELECT id, username, firstname, surname, email, haspaid, location, tournwinner, signupdate
         FROM live_user_information
         ORDER BY surname ASC, firstname ASC, username ASC"
    );

    if (!($result instanceof mysqli_result)) {
        return $users;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $row['id'] = (int) ($row['id'] ?? 0);
        $users[$row['id']] = $row;
    }

    mysqli_free_result($result);

    return $users;
}

function hh_comm_display_name(array $user): string
{
    $name = trim((string) ($user['firstname'] ?? '') . ' ' . (string) ($user['surname'] ?? ''));
    if ($name !== '') {
        return $name;
    }

    return (string) ($user['username'] ?? 'Player');
}

function hh_comm_blank_markup(): string
{
    return <<<'HTML'
<!DOCTYPE html>
<html lang="en-GB">
<body style="margin:0;padding:0;background-color:#f4f6f1;font-family:Arial,Helvetica,sans-serif;color:#16231d;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f4f6f1;">
        <tr>
            <td style="padding:24px 12px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:640px;margin:0 auto;background-color:#ffffff;border:1px solid #dfe5df;border-radius:8px;">
                    <tr>
                        <td bgcolor="#073629" style="padding:28px 28px 18px;text-align:center;border-bottom:1px solid #eef1ee;background-color:#073629;background-image:linear-gradient(90deg,#073629,#8f66d8);border-radius:8px 8px 0 0;">
                            <img src="cid:logo" alt="{{gamename}} logo" width="140" style="display:block;margin:0 auto;max-width:140px;width:100%;height:auto;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 12px;font-size:22px;line-height:1.3;font-weight:700;color:#16231d;">A quick update from {{gamename}}</p>
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">Hi {{firstname}},</p>
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">We just wanted to send you a quick update.</p>
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">{{stage_label}} closes at <strong>{{stage_close}}</strong>. If you still need to get your picks in, now is a good time.</p>
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">You can log in here whenever you're ready: <a href="{{login_url}}" style="color:#8f66d8;font-weight:700;text-decoration:none;">Open {{gamename}}</a></p>
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">Best of luck, and thanks for playing.</p>
                            <p style="margin:0;font-size:16px;line-height:1.6;">{{developer}}<br>Developer of {{gamename}}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

function hh_comm_default_subject(string $messageType, string $siteTitle, string $stageLabel = ''): string
{
    return match ($messageType) {
        'welcome' => "Welcome to {$siteTitle}",
        'password_reset' => "{$siteTitle} password reset",
        default => $stageLabel !== ''
            ? "{$siteTitle} update: {$stageLabel}"
            : "{$siteTitle} update",
    };
}

function hh_comm_logo_preview_src(): string
{
    $candidates = [
        ['relative' => '../img/hh-logo-2026-main.png', 'absolute' => dirname(__DIR__) . '/img/hh-logo-2026-main.png'],
        ['relative' => '../img/hendys-hunches-football-predictions-logo.png', 'absolute' => dirname(__DIR__) . '/img/hendys-hunches-football-predictions-logo.png'],
        ['relative' => '../img/hh-icon-2024.png', 'absolute' => dirname(__DIR__) . '/img/hh-icon-2024.png'],
    ];

    foreach ($candidates as $candidate) {
        if (file_exists($candidate['absolute'])) {
            return $candidate['relative'];
        }
    }

    return '../img/hh-logo-2026-simple.png';
}

function hh_comm_template_markup(string $messageType, string $welcomeTemplatePath, string $resetTemplatePath): string
{
    if ($messageType === 'welcome' && file_exists($welcomeTemplatePath)) {
        return (string) file_get_contents($welcomeTemplatePath);
    }

    if ($messageType === 'password_reset' && file_exists($resetTemplatePath)) {
        return (string) file_get_contents($resetTemplatePath);
    }

    return hh_comm_blank_markup();
}

function hh_comm_stage_submission_sets(mysqli $con): array
{
    $sets = [];

    foreach (hh_prediction_stage_contexts() as $stageKey => $context) {
        $table = (string) ($context['table'] ?? '');
        $sets[$stageKey] = [];

        if ($table === '' || !hh_comm_table_exists($con, $table)) {
            continue;
        }

        $result = mysqli_query($con, "SELECT id FROM {$table}");
        if (!($result instanceof mysqli_result)) {
            continue;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $sets[$stageKey][(int) ($row['id'] ?? 0)] = true;
        }

        mysqli_free_result($result);
    }

    return $sets;
}

function hh_comm_current_and_next_stage(array $stageWindows): array
{
    $current = null;
    $next = null;

    foreach ($stageWindows as $stageKey => $window) {
        $status = (string) ($window['status'] ?? '');
        if ($current === null && $status === 'open') {
            $current = $stageKey;
        }
        if ($next === null && $status === 'pending') {
            $next = $stageKey;
        }
    }

    return ['current' => $current, 'next' => $next];
}

function hh_comm_format_stage_time(?DateTimeImmutable $time): string
{
    if (!($time instanceof DateTimeImmutable)) {
        return '';
    }

    return $time->format('D j M Y, H:i');
}

function hh_comm_placeholder_data(array $user, array $stageWindow = [], array $extra = []): array
{
    global $title, $developer, $base_url, $signup_url;

    $firstname = trim((string) ($user['firstname'] ?? ''));
    $surname = trim((string) ($user['surname'] ?? ''));
    $username = trim((string) ($user['username'] ?? ''));
    $fullName = trim($firstname . ' ' . $surname);
    $displayName = $fullName !== '' ? $fullName : ($username !== '' ? $username : 'Player');
    $supporting = trim((string) ($user['tournwinner'] ?? ''));
    if ($supporting === '' || strcasecmp($supporting, 'Prefer Not To Say') === 0) {
        $supporting = '-';
    }

    $stageLabel = trim((string) ($stageWindow['label'] ?? ''));
    $stageOpen = hh_comm_format_stage_time($stageWindow['opens_at'] ?? null);
    $stageClose = hh_comm_format_stage_time($stageWindow['closes_at'] ?? null);

    return array_merge([
        'firstname' => $firstname !== '' ? $firstname : $displayName,
        'surname' => $surname,
        'fullname' => $displayName,
        'username' => $username !== '' ? $username : $displayName,
        'email' => trim((string) ($user['email'] ?? '')),
        'gamename' => (string) $title,
        'developer' => (string) $developer,
        'login_url' => rtrim((string) $base_url, '/') . '/index.php',
        'forgot_password_url' => rtrim((string) $base_url, '/') . '/forgot-password.php',
        'signup_url' => hh_mail_signup_url((string) $signup_url, $firstname, $surname),
        'payment_url' => hh_mail_signup_url((string) $signup_url, $firstname, $surname),
        'stage_label' => $stageLabel,
        'stage_open' => $stageOpen,
        'stage_close' => $stageClose,
        'supporting' => $supporting,
        'haspaid' => trim((string) ($user['haspaid'] ?? 'No')),
    ], $extra);
}

function hh_comm_ensure_temp_table(mysqli $con, string $setupPath): bool
{
    if (hh_comm_table_exists($con, 'live_temp_information')) {
        return true;
    }

    if (!file_exists($setupPath)) {
        return false;
    }

    $sql = (string) file_get_contents($setupPath);
    if (trim($sql) === '') {
        return false;
    }

    return mysqli_multi_query($con, $sql) ? (function () use ($con): bool {
        while (mysqli_more_results($con) && mysqli_next_result($con)) {
            // consume any remaining results
        }
        return hh_comm_table_exists($con, 'live_temp_information');
    })() : false;
}

function hh_comm_issue_temp_password(mysqli $con, array $user, string $setupPath): array
{
    if (!hh_comm_ensure_temp_table($con, $setupPath)) {
        throw new RuntimeException('Temporary password table is missing and could not be created.');
    }

    $username = trim((string) ($user['username'] ?? ''));
    $email = trim((string) ($user['email'] ?? ''));
    if ($username === '' || $email === '') {
        throw new RuntimeException('That player record is missing a username or email address.');
    }

    $emailCut = substr($email, 0, 4);
    $tempPass = $emailCut . random_int(10000, 99999);
    $hashTempPass = md5($tempPass);

    $statement = mysqli_prepare(
        $con,
        "INSERT INTO live_temp_information (username, temp_pass)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE temp_pass = VALUES(temp_pass)"
    );

    if (!$statement) {
        throw new RuntimeException(mysqli_error($con));
    }

    mysqli_stmt_bind_param($statement, 'ss', $username, $hashTempPass);
    $ok = mysqli_stmt_execute($statement);
    $error = mysqli_stmt_error($statement);
    mysqli_stmt_close($statement);

    if (!$ok) {
        throw new RuntimeException($error !== '' ? $error : 'Could not prepare a temporary password.');
    }

    return [
        'temp_password' => $tempPass,
        'temp_password_link' => rtrim((string) $GLOBALS['base_url'], '/') . '/forgot-password.php?u=' . rawurlencode($username) . '&p=' . rawurlencode($hashTempPass),
    ];
}

$users = hh_comm_load_users($con);
$stageContexts = hh_prediction_stage_contexts();
$stageWindows = hh_prediction_stage_windows($con);
$stageSubmissionSets = hh_comm_stage_submission_sets($con);
$stageFocus = hh_comm_current_and_next_stage($stageWindows);
$currentStageLabel = (($stageFocus['current'] ?? null) !== null && isset($stageWindows[$stageFocus['current']]))
    ? (string) ($stageWindows[$stageFocus['current']]['label'] ?? '—')
    : '—';
$nextStageLabel = (($stageFocus['next'] ?? null) !== null && isset($stageWindows[$stageFocus['next']]))
    ? (string) ($stageWindows[$stageFocus['next']]['label'] ?? '—')
    : '—';

$messages = [];
$errors = [];

$messageType = (string) ($_POST['message_type'] ?? 'custom');
$stageFocusKey = (string) ($_POST['stage_focus_key'] ?? ($stageFocus['current'] ?? $stageFocus['next'] ?? 'groups'));
if (!isset($stageContexts[$stageFocusKey]) && !empty($stageContexts)) {
    $stageFocusKey = (string) array_key_first($stageContexts);
}

$selectedRecipientIds = array_values(array_unique(array_map('intval', (array) ($_POST['recipient_ids'] ?? []))));
$previewUserId = (int) ($_POST['preview_user_id'] ?? ($_SESSION['id'] ?? 0));
$communicationsAction = (string) ($_POST['communications_action'] ?? '');
$throttlePerSecond = 10;

$editorMarkup = trim((string) ($_POST['message_markup'] ?? ''));
$subject = trim((string) ($_POST['message_subject'] ?? ''));

if ($editorMarkup === '') {
    $editorMarkup = hh_comm_template_markup($messageType, $welcomeTemplatePath, $resetTemplatePath);
}

if ($subject === '') {
    $subject = hh_comm_default_subject($messageType, (string) $title, (string) ($stageWindows[$stageFocusKey]['label'] ?? ''));
}

if (in_array($communicationsAction, ['load_blank', 'load_welcome', 'load_password_reset'], true)) {
    if ($communicationsAction === 'load_welcome') {
        $messageType = 'welcome';
        $editorMarkup = hh_comm_template_markup('welcome', $welcomeTemplatePath, $resetTemplatePath);
        $subject = hh_comm_default_subject($messageType, (string) $title);
    } elseif ($communicationsAction === 'load_password_reset') {
        $messageType = 'password_reset';
        $editorMarkup = hh_comm_template_markup('password_reset', $welcomeTemplatePath, $resetTemplatePath);
        $subject = hh_comm_default_subject($messageType, (string) $title);
    } else {
        $messageType = 'custom';
        $editorMarkup = hh_comm_blank_markup();
        $subject = hh_comm_default_subject($messageType, (string) $title, (string) ($stageWindows[$stageFocusKey]['label'] ?? ''));
    }
}

$previewUser = $users[$previewUserId] ?? reset($users) ?: [
    'firstname' => 'Sample',
    'surname' => 'Player',
    'username' => 'sampleplayer',
    'email' => 'sample@example.com',
    'haspaid' => 'No',
    'tournwinner' => 'Prefer Not To Say',
];
$previewStageWindow = $stageWindows[$stageFocusKey] ?? [];
$previewSubject = hh_mail_render_markup(
    $subject,
    hh_comm_placeholder_data(
        $previewUser,
        $previewStageWindow,
        $messageType === 'password_reset'
            ? ['temp_password' => 'TEMP12345', 'temp_password_link' => rtrim((string) $base_url, '/') . '/forgot-password.php?u=sampleplayer&p=example']
            : []
    )
);
$previewMarkup = hh_mail_render_markup(
    $editorMarkup,
    hh_comm_placeholder_data(
        $previewUser,
        $previewStageWindow,
        $messageType === 'password_reset'
            ? ['temp_password' => 'TEMP12345', 'temp_password_link' => rtrim((string) $base_url, '/') . '/forgot-password.php?u=sampleplayer&p=example']
            : []
    )
);
$previewMarkup = str_replace('cid:logo', hh_comm_logo_preview_src(), $previewMarkup);

if ($communicationsAction === 'send') {
    if (!hh_mail_is_enabled()) {
        $errors[] = 'Email sending is not enabled in the site configuration yet.';
    }
    if ($subject === '') {
        $errors[] = 'Add a subject before sending.';
    }
    if ($editorMarkup === '') {
        $errors[] = 'Add some email markup before sending.';
    }
    if (empty($selectedRecipientIds)) {
        $errors[] = 'Choose at least one player to receive the communication.';
    }

    $recipients = [];
    foreach ($selectedRecipientIds as $userId) {
        if (!isset($users[$userId])) {
            continue;
        }
        $email = trim((string) ($users[$userId]['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            continue;
        }
        $recipients[] = $users[$userId];
    }

    if (empty($recipients) && empty($errors)) {
        $errors[] = 'None of the selected players have a valid email address.';
    }

    if (empty($errors)) {
        @set_time_limit(0);

        $sentCount = 0;
        $failedRecipients = [];
        $totalRecipients = count($recipients);

        foreach ($recipients as $index => $recipient) {
            $extraPlaceholders = [];

            if ($messageType === 'password_reset') {
                try {
                    $extraPlaceholders = hh_comm_issue_temp_password($con, $recipient, $tempTableSetupPath);
                } catch (Throwable $exception) {
                    $failedRecipients[] = hh_comm_display_name($recipient) . ' (' . trim((string) ($recipient['email'] ?? '')) . '): ' . $exception->getMessage();
                    if ($index < ($totalRecipients - 1)) {
                        usleep((int) ceil(1000000 / $throttlePerSecond));
                    }
                    continue;
                }
            }

            $replacements = hh_comm_placeholder_data($recipient, $previewStageWindow, $extraPlaceholders);
            $html = hh_mail_render_markup($editorMarkup, $replacements);
            $personalisedSubject = hh_mail_render_markup($subject, $replacements);
            $ok = hh_send_email(
                trim((string) ($recipient['email'] ?? '')),
                hh_comm_display_name($recipient),
                $personalisedSubject,
                $html
            );

            if ($ok) {
                $sentCount++;
            } else {
                $failedRecipients[] = hh_comm_display_name($recipient) . ' (' . trim((string) ($recipient['email'] ?? '')) . ')';
            }

            if ($index < ($totalRecipients - 1)) {
                usleep((int) ceil(1000000 / $throttlePerSecond));
            }
        }

        if ($sentCount > 0) {
            $messages[] = "Sent {$sentCount} email" . ($sentCount === 1 ? '' : 's') . " at a throttled pace of {$throttlePerSecond} per second.";
        }

        if (!empty($failedRecipients)) {
            $errors[] = 'Some emails could not be sent: ' . implode('; ', $failedRecipients);
        }
    }
}

$app_path_prefix = '../';
include '../php/header.php';
include '../php/navigation.php';
?>

<style>
  .admin-shell {
    width: min(1320px, calc(100% - 32px));
    margin: 20px auto 36px;
  }
  .admin-grid {
    display: grid;
    gap: 18px;
  }
  .admin-grid > *,
  .admin-grid--two > *,
  .admin-grid--three > *,
  .admin-shell > * {
    min-width: 0;
  }
  .admin-grid--two {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .admin-grid--three {
    grid-template-columns: 1.1fr 1fr 1fr;
  }
  .admin-card {
    background: #fff;
    border: 1px solid rgba(22, 35, 29, 0.12);
    border-radius: 8px;
    box-shadow: 0 18px 38px rgba(7, 54, 41, 0.08);
    padding: 20px;
  }
  .admin-card h2,
  .admin-card h3 {
    margin: 0 0 10px;
    color: var(--hh-ink);
  }
  .admin-note {
    margin: 0;
    color: #5d655f;
    font-size: 0.94rem;
    line-height: 1.55;
  }
  .admin-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .comm-kpi {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
  }
  .comm-kpi__item {
    padding: 14px;
    border-radius: 8px;
    background: #f7f9f6;
    border: 1px solid rgba(22, 35, 29, 0.08);
  }
  .comm-kpi__item strong {
    display: block;
    font-size: 1.35rem;
    color: var(--hh-ink);
  }
  .comm-kpi__item span {
    color: #5d655f;
    font-size: 0.88rem;
  }
  .comm-editor-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
    gap: 18px;
  }
  .comm-recipient-list {
    max-height: 420px;
    overflow: auto;
    border: 1px solid rgba(22, 35, 29, 0.1);
    border-radius: 8px;
    padding: 8px;
    background: #fcfdfb;
  }
  .comm-recipient {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    gap: 10px;
    align-items: start;
    padding: 10px;
    border-radius: 8px;
  }
  .comm-recipient + .comm-recipient {
    border-top: 1px solid rgba(22, 35, 29, 0.08);
  }
  .comm-recipient:hover {
    background: #f5f8f4;
  }
  .comm-recipient__name {
    display: block;
    color: var(--hh-ink);
    font-weight: 800;
  }
  .comm-recipient__meta {
    display: block;
    color: #5d655f;
    font-size: 0.88rem;
    line-height: 1.45;
    word-break: break-word;
  }
  .comm-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
  }
  .comm-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 800;
    background: rgba(143, 102, 216, 0.12);
    color: var(--hh-purple-dark);
  }
  .comm-badge--pending {
    background: rgba(240, 197, 86, 0.24);
    color: #8a5a00;
  }
  .comm-badge--paid {
    background: rgba(10, 90, 56, 0.12);
    color: #0a5a38;
  }
  .comm-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  .comm-placeholder-list code {
    white-space: nowrap;
  }
  .comm-preview {
    min-height: 220px;
    padding: 18px;
    border: 1px solid rgba(22, 35, 29, 0.12);
    border-radius: 8px;
    background: #f7f9f6;
    overflow: auto;
  }
  .comm-preview__frame {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid rgba(22, 35, 29, 0.08);
    overflow: hidden;
  }
  .comm-preview__subject {
    padding: 12px 14px;
    border-bottom: 1px solid rgba(22, 35, 29, 0.08);
    background: #fcfdfb;
    color: var(--hh-ink);
    font-size: 0.92rem;
  }
  .comm-preview__subject strong {
    margin-right: 6px;
  }
  .comm-preview__body {
    padding: 14px;
    background: #ffffff;
  }
  .comm-preview__body img {
    max-width: 100%;
    height: auto;
  }
  @media (max-width: 991px) {
    .admin-grid--two,
    .admin-grid--three,
    .comm-editor-grid,
    .comm-kpi {
      grid-template-columns: 1fr;
    }
  }
  @media (max-width: 575.98px) {
    .admin-shell {
      width: min(100%, calc(100% - 16px));
      margin: 12px auto 22px;
    }
    .admin-card {
      padding: 16px;
    }
    .admin-actions .btn,
    .page-hero__actions .btn {
      width: 100%;
      justify-content: center;
    }
  }
</style>

<div class="admin-shell">
    <div class="page-hero page-hero--admin">
        <div>
            <p class="eyebrow" style="color: #FF0000 !important">Admin control room</p>
            <h1>Communications</h1>
            <p class="lead mb-0">Compose, preview and send updates to selected players without outrunning SES or wrestling with raw SQL.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="functions.php"><i class="bi bi-sliders"></i> Admin functions</a>
            <a class="btn btn-outline-dark" href="../dashboard.php"><i class="bi bi-grid"></i> Back to dashboard</a>
        </div>
    </div>

    <?php foreach ($messages as $message) : ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($message, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $error) : ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <section class="admin-grid">
        <div class="admin-card">
            <h2>At a glance</h2>
            <div class="comm-kpi">
                <div class="comm-kpi__item"><strong><?= count($users) ?></strong><span>players available to email</span></div>
                <div class="comm-kpi__item"><strong><?= htmlspecialchars($currentStageLabel, ENT_QUOTES) ?></strong><span>current live stage</span></div>
                <div class="comm-kpi__item"><strong><?= htmlspecialchars($nextStageLabel, ENT_QUOTES) ?></strong><span>next stage opening</span></div>
                <div class="comm-kpi__item"><strong>10/sec</strong><span>safe send pace against your 14/sec SES limit</span></div>
            </div>
        </div>

        <div class="comm-editor-grid">
            <div class="admin-grid">
                <div class="admin-card">
                    <h3>Compose the message</h3>
                    <p class="admin-note mb-3">Use this for live reminders, payment nudges, resend-style welcome emails, or proper password reset emails. The reset mode generates a fresh temporary password for each selected player.</p>
                    <form method="post" id="communicationsComposeForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="message_type">Message type</label>
                                <select class="form-select" id="message_type" name="message_type">
                                    <option value="custom"<?= $messageType === 'custom' ? ' selected' : '' ?>>Custom communication</option>
                                    <option value="welcome"<?= $messageType === 'welcome' ? ' selected' : '' ?>>Registration / welcome email</option>
                                    <option value="password_reset"<?= $messageType === 'password_reset' ? ' selected' : '' ?>>Password reset email</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="stage_focus_key">Stage placeholders</label>
                                <select class="form-select" id="stage_focus_key" name="stage_focus_key">
                                    <?php foreach ($stageWindows as $stageKey => $window) : ?>
                                        <option value="<?= htmlspecialchars($stageKey, ENT_QUOTES) ?>"<?= $stageFocusKey === $stageKey ? ' selected' : '' ?>>
                                            <?= htmlspecialchars((string) ($window['label'] ?? $stageKey), ENT_QUOTES) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="preview_user_id">Preview using</label>
                                <select class="form-select" id="preview_user_id" name="preview_user_id">
                                    <?php foreach ($users as $user) : ?>
                                        <option value="<?= (int) $user['id'] ?>"<?= ((int) $user['id'] === $previewUserId) ? ' selected' : '' ?>>
                                            <?= htmlspecialchars(hh_comm_display_name($user) . ' (@' . (string) ($user['username'] ?? '') . ')', ENT_QUOTES) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="comm-toolbar mt-3">
                            <button class="btn btn-outline-dark btn-sm" type="submit" name="communications_action" value="load_blank"><i class="bi bi-file-earmark-plus"></i> Load blank starter</button>
                            <button class="btn btn-outline-dark btn-sm" type="submit" name="communications_action" value="load_welcome"><i class="bi bi-envelope-heart"></i> Load registration template</button>
                            <button class="btn btn-outline-dark btn-sm" type="submit" name="communications_action" value="load_password_reset"><i class="bi bi-key"></i> Load forgot password template</button>
                        </div>

                        <div class="mt-3">
                            <label class="form-label" for="message_subject">Subject</label>
                            <input class="form-control" id="message_subject" name="message_subject" type="text" value="<?= htmlspecialchars($subject, ENT_QUOTES) ?>">
                        </div>

                        <div class="mt-3">
                            <label class="form-label" for="message_markup">HTML markup</label>
                            <textarea class="form-control font-monospace" id="message_markup" name="message_markup" rows="20"><?= htmlspecialchars($editorMarkup, ENT_QUOTES) ?></textarea>
                        </div>

                        <div class="comm-placeholder-list mt-3">
                            <p class="admin-note">
                                Useful placeholders:
                                <code>{{firstname}}</code>,
                                <code>{{fullname}}</code>,
                                <code>{{username}}</code>,
                                <code>{{gamename}}</code>,
                                <code>{{login_url}}</code>,
                                <code>{{signup_url}}</code>,
                                <code>{{payment_url}}</code>,
                                <code>{{stage_label}}</code>,
                                <code>{{stage_open}}</code>,
                                <code>{{stage_close}}</code>,
                                <code>{{supporting}}</code>,
                                <code>{{developer}}</code>.
                                Reset emails also support <code>{{temp_password}}</code> and <code>{{temp_password_link}}</code>.
                            </p>
                        </div>

                        <div class="admin-actions mt-3">
                            <button class="btn btn-outline-dark" type="submit" name="communications_action" value="preview"><i class="bi bi-eye"></i> Refresh preview</button>
                        </div>

                        <?php foreach ($selectedRecipientIds as $selectedRecipientId) : ?>
                            <input type="hidden" name="recipient_ids[]" value="<?= (int) $selectedRecipientId ?>">
                        <?php endforeach; ?>
                    </form>
                </div>

                <div class="admin-card">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <h3>Recipient picker</h3>
                            <p class="admin-note">Choose exactly who should receive this email. The quick selectors are there to save you from a lot of checkbox fatigue.</p>
                        </div>
                        <div class="comm-toolbar">
                            <button type="button" class="btn btn-outline-dark btn-sm" data-comm-select="all">Select all</button>
                            <button type="button" class="btn btn-outline-dark btn-sm" data-comm-select="none">Clear</button>
                            <button type="button" class="btn btn-outline-dark btn-sm" data-comm-select="unpaid">Unpaid only</button>
                            <?php if (($stageFocus['current'] ?? null) !== null) : ?>
                                <button type="button" class="btn btn-outline-dark btn-sm" data-comm-select="stage" data-stage-key="<?= htmlspecialchars((string) $stageFocus['current'], ENT_QUOTES) ?>">
                                    Missing <?= htmlspecialchars((string) ($stageWindows[$stageFocus['current']]['label'] ?? 'current stage'), ENT_QUOTES) ?>
                                </button>
                            <?php endif; ?>
                            <?php if (($stageFocus['next'] ?? null) !== null) : ?>
                                <button type="button" class="btn btn-outline-dark btn-sm" data-comm-select="stage" data-stage-key="<?= htmlspecialchars((string) $stageFocus['next'], ENT_QUOTES) ?>">
                                    Missing <?= htmlspecialchars((string) ($stageWindows[$stageFocus['next']]['label'] ?? 'next stage'), ENT_QUOTES) ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form method="post" class="mt-3" id="communicationsSendForm">
                        <input type="hidden" name="communications_action" value="send">
                        <input type="hidden" name="message_type" id="send_message_type" value="<?= htmlspecialchars($messageType, ENT_QUOTES) ?>">
                        <input type="hidden" name="stage_focus_key" id="send_stage_focus_key" value="<?= htmlspecialchars($stageFocusKey, ENT_QUOTES) ?>">
                        <input type="hidden" name="preview_user_id" id="send_preview_user_id" value="<?= (int) $previewUserId ?>">
                        <input type="hidden" name="message_subject" id="send_message_subject" value="<?= htmlspecialchars($subject, ENT_QUOTES) ?>">
                        <textarea name="message_markup" id="send_message_markup" hidden><?= htmlspecialchars($editorMarkup, ENT_QUOTES) ?></textarea>

                        <div class="mb-3">
                            <label class="form-label" for="stageSelectionHelper">Stage reminder helper</label>
                            <div class="input-group">
                                <select class="form-select" id="stageSelectionHelper">
                                    <option value="">Choose a stage to target missing submissions…</option>
                                    <?php foreach ($stageWindows as $stageKey => $window) : ?>
                                        <option value="<?= htmlspecialchars($stageKey, ENT_QUOTES) ?>"><?= htmlspecialchars((string) ($window['label'] ?? $stageKey), ENT_QUOTES) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline-dark" type="button" id="stageSelectionApply">Select missing</button>
                            </div>
                        </div>

                        <div class="comm-recipient-list">
                            <?php foreach ($users as $user) : ?>
                                <?php
                                $userId = (int) ($user['id'] ?? 0);
                                $displayName = hh_comm_display_name($user);
                                $email = trim((string) ($user['email'] ?? ''));
                                $safeLocation = trim((string) ($user['location'] ?? ''));
                                if ($safeLocation === '' || strcasecmp($safeLocation, 'Prefer Not To Say') === 0) {
                                    $safeLocation = '-';
                                }
                                $missingStages = [];
                                foreach ($stageContexts as $stageKey => $_unusedContext) {
                                    if (empty($stageSubmissionSets[$stageKey][$userId])) {
                                        $missingStages[] = $stageKey;
                                    }
                                }
                                ?>
                                <label
                                    class="comm-recipient"
                                    data-user-id="<?= $userId ?>"
                                    data-unpaid="<?= ((string) ($user['haspaid'] ?? 'No') !== 'Yes') ? '1' : '0' ?>"
                                    <?php foreach ($stageContexts as $stageKey => $_unusedContext) : ?>
                                        data-missing-<?= htmlspecialchars($stageKey, ENT_QUOTES) ?>="<?= in_array($stageKey, $missingStages, true) ? '1' : '0' ?>"
                                    <?php endforeach; ?>
                                >
                                    <input class="form-check-input comm-recipient-checkbox" type="checkbox" name="recipient_ids[]" value="<?= $userId ?>"<?= in_array($userId, $selectedRecipientIds, true) ? ' checked' : '' ?>>
                                    <span>
                                        <span class="comm-recipient__name"><?= htmlspecialchars($displayName, ENT_QUOTES) ?></span>
                                        <span class="comm-recipient__meta"><?= htmlspecialchars($email !== '' ? $email : 'No email address stored', ENT_QUOTES) ?> · <?= htmlspecialchars($safeLocation, ENT_QUOTES) ?></span>
                                        <span class="comm-badges">
                                            <span class="comm-badge <?= ((string) ($user['haspaid'] ?? 'No') === 'Yes') ? 'comm-badge--paid' : 'comm-badge--pending' ?>">
                                                <?= ((string) ($user['haspaid'] ?? 'No') === 'Yes') ? 'Paid' : 'Entry pending' ?>
                                            </span>
                                            <?php foreach ($stageContexts as $stageKey => $context) : ?>
                                                <span class="comm-badge <?= empty($stageSubmissionSets[$stageKey][$userId]) ? 'comm-badge--pending' : '' ?>">
                                                    <?= empty($stageSubmissionSets[$stageKey][$userId]) ? 'Missing ' : 'Done ' ?>
                                                    <?= htmlspecialchars((string) ($context['label'] ?? $stageKey), ENT_QUOTES) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="admin-actions mt-3">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Send communication</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="admin-grid">
                <div class="admin-card">
                    <h3>Preview</h3>
                    <p class="admin-note mb-3">Rendered using <?= htmlspecialchars(hh_comm_display_name($previewUser), ENT_QUOTES) ?> and <?= htmlspecialchars((string) ($previewStageWindow['label'] ?? 'no stage context'), ENT_QUOTES) ?>. This is how the current editor markup will be personalised.</p>
                    <div class="comm-preview">
                        <div class="comm-preview__frame">
                            <div class="comm-preview__subject"><strong>Subject:</strong> <?= htmlspecialchars($previewSubject, ENT_QUOTES) ?></div>
                            <div class="comm-preview__body"><?= $previewMarkup ?></div>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <h3>How sending works</h3>
                    <p class="admin-note mb-3">To stay safely under your SES limit, this page sends at a throttled pace of 10 emails per second. That gives you a little breathing room under the 14-per-second cap without making waits silly.</p>
                    <ul class="mb-0">
                        <li>Custom and welcome emails use the current editor markup and personalise placeholders per player.</li>
                        <li>Password reset sends create a fresh temporary password for each selected player before emailing them.</li>
                        <li>The existing registration and forgot password templates can be loaded, edited, previewed and then sent from here.</li>
                        <li>There is no hidden queue yet, so this page is best for small-to-medium sends rather than giant blasts.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
  (() => {
    const composeForm = document.getElementById('communicationsComposeForm');
    const sendForm = document.getElementById('communicationsSendForm');
    const recipientCards = Array.from(document.querySelectorAll('.comm-recipient'));
    const recipientCheckboxes = recipientCards.map((card) => card.querySelector('.comm-recipient-checkbox')).filter(Boolean);

    const setSelection = (predicate) => {
      recipientCards.forEach((card) => {
        const checkbox = card.querySelector('.comm-recipient-checkbox');
        if (!checkbox) {
          return;
        }
        checkbox.checked = predicate(card);
      });
    };

    document.querySelectorAll('[data-comm-select]').forEach((button) => {
      button.addEventListener('click', () => {
        const mode = button.getAttribute('data-comm-select');
        if (mode === 'all') {
          setSelection(() => true);
          return;
        }
        if (mode === 'none') {
          setSelection(() => false);
          return;
        }
        if (mode === 'unpaid') {
          setSelection((card) => card.getAttribute('data-unpaid') === '1');
          return;
        }
        if (mode === 'stage') {
          const stageKey = button.getAttribute('data-stage-key') || '';
          setSelection((card) => card.getAttribute('data-missing-' + stageKey) === '1');
        }
      });
    });

    const helperSelect = document.getElementById('stageSelectionHelper');
    const helperButton = document.getElementById('stageSelectionApply');
    if (helperSelect && helperButton) {
      helperButton.addEventListener('click', () => {
        const stageKey = helperSelect.value;
        if (!stageKey) {
          return;
        }
        setSelection((card) => card.getAttribute('data-missing-' + stageKey) === '1');
      });
    }

    if (composeForm && sendForm) {
      sendForm.addEventListener('submit', () => {
        const messageType = composeForm.querySelector('#message_type');
        const stageFocusKey = composeForm.querySelector('#stage_focus_key');
        const previewUserId = composeForm.querySelector('#preview_user_id');
        const messageSubject = composeForm.querySelector('#message_subject');
        const messageMarkup = composeForm.querySelector('#message_markup');

        const sendMessageType = sendForm.querySelector('#send_message_type');
        const sendStageFocusKey = sendForm.querySelector('#send_stage_focus_key');
        const sendPreviewUserId = sendForm.querySelector('#send_preview_user_id');
        const sendMessageSubject = sendForm.querySelector('#send_message_subject');
        const sendMessageMarkup = sendForm.querySelector('#send_message_markup');

        if (messageType && sendMessageType) sendMessageType.value = messageType.value;
        if (stageFocusKey && sendStageFocusKey) sendStageFocusKey.value = stageFocusKey.value;
        if (previewUserId && sendPreviewUserId) sendPreviewUserId.value = previewUserId.value;
        if (messageSubject && sendMessageSubject) sendMessageSubject.value = messageSubject.value;
        if (messageMarkup && sendMessageMarkup) sendMessageMarkup.value = messageMarkup.value;
      });
    }
  })();
</script>

<?php include '../php/footer.php'; ?>
