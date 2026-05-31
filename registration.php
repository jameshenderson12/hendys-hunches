<?php
session_start();

// Include necessary files for configuration and database connection
include 'php/config.php';
include 'php/process.php';
require_once 'php/email.php';
require_once 'php/terms.php';

function hh_registration_client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        $value = trim((string) ($_SERVER[$key] ?? ''));
        if ($value === '') {
            continue;
        }

        if ($key === 'HTTP_X_FORWARDED_FOR') {
            $parts = array_filter(array_map('trim', explode(',', $value)));
            $value = (string) ($parts[0] ?? '');
        }

        if ($value !== '') {
            return $value;
        }
    }

    return 'unknown';
}

function hh_registration_rate_limit_file(string $scope, string $identifier): string
{
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'hh_reg_' . $scope . '_' . sha1($identifier) . '.json';
}

function hh_registration_read_attempts(string $filePath, int $windowSeconds, int $now): array
{
    if (!is_file($filePath)) {
        return [];
    }

    $payload = @file_get_contents($filePath);
    if ($payload === false || trim($payload) === '') {
        return [];
    }

    $decoded = json_decode($payload, true);
    if (!is_array($decoded)) {
        return [];
    }

    return array_values(array_filter(array_map('intval', $decoded), static fn(int $timestamp): bool => $timestamp > ($now - $windowSeconds)));
}

function hh_registration_write_attempts(string $filePath, array $attempts): void
{
    @file_put_contents($filePath, json_encode(array_values($attempts)), LOCK_EX);
}

function hh_registration_apply_rate_limit(string $scope, string $identifier, int $windowSeconds, int $limit, int $now): array
{
    $filePath = hh_registration_rate_limit_file($scope, $identifier);
    $attempts = hh_registration_read_attempts($filePath, $windowSeconds, $now);

    if (count($attempts) >= $limit) {
        $oldestAttempt = min($attempts);
        $retryAfter = max(1, ($oldestAttempt + $windowSeconds) - $now);

        return [
            'blocked' => true,
            'retry_after' => $retryAfter,
            'attempts' => count($attempts),
        ];
    }

    $attempts[] = $now;
    hh_registration_write_attempts($filePath, $attempts);

    return [
        'blocked' => false,
        'retry_after' => 0,
        'attempts' => count($attempts),
    ];
}

function hh_registration_fallback_file_options(string $filePath): array
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

function hh_registration_tournament_team_options(?mysqli $con): array
{
    if (!($con instanceof mysqli)) {
        return [];
    }

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

$con = null;
if (file_exists(__DIR__ . '/php/db-connect.php')) {
    include 'php/db-connect.php';
}

$tournamentWinnerOptions = hh_registration_tournament_team_options($con);
if (empty($tournamentWinnerOptions)) {
    $tournamentWinnerOptions = hh_registration_fallback_file_options('text/select-countryteams-input.txt');
}

// Initialise variable for error messages
$registrationSuccess = false;
$registrationError = '';
$registrationMinimumSeconds = 4;
$registrationSessionWindow = 15 * 60;
$registrationSessionLimit = 3;
$registrationIpWindow = 30 * 60;
$registrationIpLimit = 5;

if (empty($_SESSION['registration_form_issued_at'])) {
    $_SESSION['registration_form_issued_at'] = time();
}

$registrationFormIssuedAt = (int) $_SESSION['registration_form_issued_at'];

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = time();
    $honeypotValue = trim((string) ($_POST['website'] ?? ''));
    $postedIssuedAt = (int) ($_POST['form_issued_at'] ?? 0);
    $secondsSpent = $postedIssuedAt > 0 ? ($now - $postedIssuedAt) : 0;

    if ($honeypotValue !== '') {
        $registrationError = 'Registration could not be completed. Please try again shortly.';
    } elseif ($postedIssuedAt <= 0 || $secondsSpent < $registrationMinimumSeconds) {
        $registrationError = 'Please take a little more time to complete the form, then try again.';
    } else {
        $sessionRate = hh_registration_apply_rate_limit('session', session_id(), $registrationSessionWindow, $registrationSessionLimit, $now);
        $ipRate = hh_registration_apply_rate_limit('ip', hh_registration_client_ip(), $registrationIpWindow, $registrationIpLimit, $now);

        if ($sessionRate['blocked']) {
            $minutes = max(1, (int) ceil($sessionRate['retry_after'] / 60));
            $registrationError = 'Too many registration attempts from this browser. Please wait about ' . $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' and try again.';
        } elseif ($ipRate['blocked']) {
            $minutes = max(1, (int) ceil($ipRate['retry_after'] / 60));
            $registrationError = 'Too many registration attempts from this connection. Please wait about ' . $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' and try again.';
        } else {
            // Sanitize and retrieve form data
            $firstname = ucfirst(trim((string) ($_POST['firstname'] ?? '')));
            $surname = ucfirst(trim((string) ($_POST['surname'] ?? '')));
            $email = trim((string) ($_POST['email'] ?? ''));
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = md5((string) ($_POST['pwd1'] ?? ''));
            $avatar = trim((string) ($_POST['avatar'] ?? ''));
            $fieldofwork = trim((string) ($_POST['fieldofwork'] ?? ''));
            $location = trim((string) ($_POST['location'] ?? ''));
            $faveteam = trim((string) ($_POST['faveteam'] ?? ''));
            $tournwinner = trim((string) ($_POST['tournwinner'] ?? ''));

            $fieldofwork = $fieldofwork !== '' ? $fieldofwork : 'Prefer Not To Say';
            $location = $location !== '' ? $location : 'Prefer Not To Say';
            $faveteam = $faveteam !== '' ? $faveteam : 'Prefer Not To Say';
            $tournwinner = $tournwinner !== '' ? $tournwinner : 'Prefer Not To Say';

            // Query to get the total number of users to set positional values
            $sql1 = "SELECT count(*) AS totalusers FROM live_user_information";
            $totalusers = mysqli_query($con, $sql1) or die(mysqli_error($con));
            $row = mysqli_fetch_assoc($totalusers);
            $signupPosition = ((int) ($row["totalusers"] ?? 0)) + 1;
            $setdefstartpos = $signupPosition;
            $setdefcurrpos = $signupPosition;
            $setdeflastpos = $signupPosition;

            // Prepare and bind SQL statements
            $stmt1 = mysqli_prepare($con, "INSERT INTO live_user_information (username, password, firstname, surname, email, avatar, fieldofwork, location, faveteam, tournwinner, startpos, lastpos, currpos) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $blankTempPassword = '';
            $stmt2 = mysqli_prepare($con, "INSERT INTO live_temp_information (username, temp_pass) VALUES (?, ?)");

            mysqli_stmt_bind_param($stmt1, "ssssssssssddd", $username, $password, $firstname, $surname, $email, $avatar, $fieldofwork, $location, $faveteam, $tournwinner, $setdefstartpos, $setdeflastpos, $setdefcurrpos);
            mysqli_stmt_bind_param($stmt2, "ss", $username, $blankTempPassword);

            // Execute the queries
            mysqli_stmt_execute($stmt1);
            mysqli_stmt_execute($stmt2);

            // Close statement and connection
            mysqli_stmt_close($stmt1);
            mysqli_stmt_close($stmt2);

            mysqli_close($con);

            // Set success flag
            $registrationSuccess = true;

            // If registration is successful, send the welcome email
            if ($registrationSuccess) {
              sendWelcomeEmail($firstname, $surname, $username, $email);
            }
        }
    }

    $_SESSION['registration_form_issued_at'] = time();
    $registrationFormIssuedAt = (int) $_SESSION['registration_form_issued_at'];
}
?>

<!DOCTYPE html>
<html lang="en-GB">
  <head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-QN708QFJSD"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-QN708QFJSD');
    </script>    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <meta http-equiv="Content-Type" content="text/html">    
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
    <meta name="keywords" content="football, predictions, game">
	  <title>Registration - Hendy's Hunches</title>
    <link href="ico/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">
    <!-- Vendor CSS Files -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />    
    <!-- Custom CSS Files -->
    <link href="css/registration.css" rel="stylesheet">
    <link href="css/multi-step-form.css" rel="stylesheet">
    <script src="js/multi-step-form.js"></script>
    <!--jQuery Files -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <style>
      :root {
        --hh-ink: #16231d;
        --hh-green: #04331e;
        --hh-green-mid: #2f8f63;
        --hh-purple: #8f66d8;
        --hh-purple-dark: #402064;
        --hh-purple-deep: #28133f;
        --hh-gold: #f0c556;
        --hh-shadow: 0 24px 68px rgba(0, 0, 0, 0.32);
      }

      html {
        height: auto;
        min-height: 100%;
      }

      body.registration-concept {
        min-height: 100vh;
        margin: 0;
        background:
          linear-gradient(120deg, rgba(0, 31, 17, 0.94), rgba(0, 52, 28, 0.7) 46%, rgba(64, 32, 100, 0.92)),
          url("img/football-stadium.jpg") center / cover fixed no-repeat;
        color: #ffffff;
        font-family: 'Lato', sans-serif;
      }

      .registration-page {
        display: grid;
        min-height: 100vh;
        padding: 24px;
        position: relative;
      }

      .registration-page::before {
        background:
          radial-gradient(circle at 20% 20%, rgba(240, 197, 86, 0.22), transparent 26%),
          radial-gradient(circle at 78% 26%, rgba(143, 102, 216, 0.3), transparent 30%),
          linear-gradient(180deg, rgba(255, 255, 255, 0.08), transparent 44%);
        content: "";
        inset: 0;
        pointer-events: none;
        position: fixed;
      }

      .registration-page .cover-container {
        max-width: 1220px;
        position: relative;
        z-index: 1;
      }

      .registration-topbar {
        align-items: flex-start;
        display: flex;
        gap: 16px;
        justify-content: space-between;
        margin-bottom: 22px;
        width: 100%;
      }

      .registration-brand {
        display: grid;
        gap: 2px;
        text-align: left;
      }

      .registration-brand strong,
      .registration-brand span {
        display: block;
        letter-spacing: 0;
      }

      .registration-brand strong {
        color: #ffffff;
        font-size: 1.08rem;
        font-weight: 900;
        line-height: 1;
      }

      .registration-brand span {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.78rem;
        font-weight: 900;
        text-transform: uppercase;
      }

      .registration-page .nav-masthead {
        gap: 12px;
      }

      .registration-page .nav-masthead .nav-link {
        border-bottom: 2px solid transparent;
        color: rgba(255, 255, 255, 0.82);
        margin-left: 0;
        padding: 6px 0;
      }

      .registration-page .nav-masthead .nav-link:hover,
      .registration-page .nav-masthead .nav-link:focus {
        border-bottom-color: var(--hh-gold);
        color: #ffffff;
      }

      .registration-shell {
        align-items: center;
        background: var(--hh-green);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 8px;
        box-shadow: var(--hh-shadow);
        display: grid;
        gap: 28px;
        grid-template-columns: minmax(280px, 0.78fr) minmax(0, 1.22fr);
        margin: 0 auto;
        padding: 28px;
        width: 100%;
      }

      .registration-stage {
        display: grid;
        gap: 16px;
        justify-items: center;
        text-align: center;
      }

      .registration-stage__logo {
        filter: drop-shadow(0 24px 42px rgba(0, 0, 0, 0.42));
        height: auto;
        max-width: min(34vw, 360px);
        width: auto;
      }

      .registration-stage p {
        color: rgba(255, 255, 255, 0.84);
        font-weight: 700;
        margin: 0;
        max-width: 380px;
      }

      .registration-hosts {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: center;
      }

      .registration-hosts span {
        align-items: center;
        color: rgba(255, 255, 255, 0.88);
        display: inline-flex;
        gap: 8px;
        font-weight: 900;
      }

      .registration-hosts img {
        border: 1px solid rgba(255, 255, 255, 0.42);
        border-radius: 4px;
        box-shadow: 0 5px 12px rgba(0, 0, 0, 0.22);
        height: 24px;
        object-fit: cover;
        width: 36px;
      }

      .registration-panel {
        background: rgba(251, 252, 248, 0.96);
        border: 1px solid rgba(255, 255, 255, 0.55);
        border-radius: 8px;
        box-shadow: var(--hh-shadow);
        color: var(--hh-ink);
        padding: 26px;
        text-align: left;
      }

      .registration-panel h1 {
        color: var(--hh-ink);
        font-size: clamp(2rem, 4vw, 3.4rem);
        font-weight: 900;
        letter-spacing: 0;
        line-height: 1;
        margin: 0 0 8px;
        text-align: left;
      }

      .registration-panel .registration-kicker {
        color: var(--hh-purple-dark);
        font-size: 0.78rem;
        font-weight: 900;
        letter-spacing: 0;
        margin: 0 0 8px;
        text-transform: uppercase;
      }

      .registration-panel .small,
      .registration-panel .text-white-50 {
        color: #59635f !important;
      }

      .registration-panel form {
        background: transparent;
        border: 0;
        border-radius: 0;
        box-shadow: none;
        min-height: 0;
        padding: 0;
      }

      .registration-panel label {
        color: var(--hh-ink);
        font-size: 0.95rem;
        font-weight: 800;
        letter-spacing: 0;
      }

      .registration-panel .text-warning {
        color: #b3262d !important;
      }

      .registration-panel .form-control,
      .registration-panel .form-select,
      .registration-panel .input-group {
        margin-bottom: 1rem;
        width: 100% !important;
      }

      .registration-panel .form-control,
      .registration-panel .form-select {
        background: #ffffff;
        border: 1px solid rgba(22, 35, 29, 0.22);
        border-radius: 8px;
        color: var(--hh-ink);
        min-height: 46px;
        padding-right: 44px;
      }

      .registration-panel .form-control:focus,
      .registration-panel .form-select:focus {
        border-color: var(--hh-purple);
        box-shadow: 0 0 0 0.2rem rgba(143, 102, 216, 0.22);
      }

      .registration-panel .form-control.is-valid,
      .registration-panel .form-select.is-valid {
        border-color: #2f8f63;
        box-shadow: 0 0 0 0.18rem rgba(47, 143, 99, 0.18);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Ccircle fill='%232f8f63' cx='8' cy='8' r='8'/%3E%3Cpath fill='white' d='M6.6 10.7 4.3 8.4l-.9.9 3.2 3.2 5.9-5.9-.9-.9z'/%3E%3C/svg%3E");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 18px 18px;
      }

      .registration-panel .form-control.is-invalid,
      .registration-panel .form-select.is-invalid {
        border-color: #d64045;
        box-shadow: 0 0 0 0.18rem rgba(214, 64, 69, 0.18);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Ccircle fill='%23d64045' cx='8' cy='8' r='8'/%3E%3Cpath fill='white' d='M5 5.9 5.9 5 8 7.1 10.1 5l.9.9L8.9 8l2.1 2.1-.9.9L8 8.9 5.9 11l-.9-.9L7.1 8z'/%3E%3C/svg%3E");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 18px 18px;
      }

      .registration-panel .input-group .form-control {
        padding-right: 16px;
      }

      .registration-panel .input-group .form-control.is-valid,
      .registration-panel .input-group .form-control.is-invalid {
        background-image: none;
      }

      .registration-panel .input-group.is-valid,
      .registration-panel .input-group.is-invalid {
        border-radius: 8px;
        position: relative;
      }

      .registration-panel .input-group.is-valid::after,
      .registration-panel .input-group.is-invalid::after {
        align-items: center;
        border-radius: 999px;
        color: #ffffff;
        display: inline-flex;
        font-size: 0.72rem;
        font-weight: 900;
        height: 20px;
        justify-content: center;
        pointer-events: none;
        position: absolute;
        right: 54px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        z-index: 3;
      }

      .registration-panel .input-group.is-valid::after {
        background: #2f8f63;
        content: "✓";
      }

      .registration-panel .input-group.is-invalid::after {
        background: #d64045;
        content: "×";
      }

      .registration-panel .btn-primary {
        background: var(--hh-purple);
        border-color: var(--hh-purple);
        border-radius: 8px;
        color: #ffffff;
        font-weight: 900;
      }

      .registration-panel .btn-primary:hover,
      .registration-panel .btn-primary:focus {
        background: #7650bd;
        border-color: #7650bd;
      }

      .registration-panel .btn-success {
        background: #2f8f63;
        border-color: #2f8f63;
        border-radius: 8px;
        font-weight: 900;
      }

      .registration-panel .btn-success:hover,
      .registration-panel .btn-success:focus {
        background: #257a54;
        border-color: #257a54;
      }

      .registration-panel .progressbar {
        margin: 1.4rem 0 3.3rem;
      }

      .registration-panel .progressbar::before,
      .registration-panel .progress {
        background-color: rgba(22, 35, 29, 0.16);
      }

      .registration-panel .progress {
        background-color: var(--hh-purple);
      }

      .registration-panel .progress-step {
        background-color: #e4dedf;
        color: var(--hh-ink);
      }

      .registration-panel .progress-step-active {
        background-color: var(--hh-purple);
        color: #ffffff;
      }

      .registration-panel .progress-step::after {
        color: #59635f;
        font-weight: 900;
      }

      .registration-panel .btn-outline-light {
        border: 1px solid rgba(22, 35, 29, 0.16);
        border-radius: 8px;
        padding: 4px;
      }

      .registration-panel .btn-check:checked + .btn-outline-light {
        background: rgba(251, 252, 248, 0.96);
        border-color: var(--hh-purple);
        box-shadow: 0 0 0 0.2rem rgba(143, 102, 216, 0.26);
      }

      .registration-panel .terms-panel {
        background: rgba(64, 32, 100, 0.06);
        border: 1px solid rgba(64, 32, 100, 0.16);
        color: var(--hh-ink);
      }

      .registration-panel .terms-panel h5 {
        color: var(--hh-purple-dark);
        font-weight: 900;
      }

      .registration-panel .terms-panel p,
      .registration-panel .terms-panel li {
        color: var(--hh-ink);
      }

      .registration-panel hr {
        border-color: rgba(22, 35, 29, 0.18);
        opacity: 1;
      }

      .registration-panel .invalid-feedback {
        color: #55615a;
        display: none;
        font-size: 0.82rem;
        font-weight: 700;
        margin: -0.55rem 0 0.9rem;
      }

      .registration-panel .invalid-feedback.is-active,
      .registration-panel .invalid-feedback.d-block {
        display: block !important;
      }

      .registration-panel .un-msg {
        color: #55615a;
        display: none;
        font-size: 0.8rem;
        margin: -0.55rem 0 0.9rem;
      }

      .registration-panel .un-msg.is-active,
      .registration-panel .un-msg.is-valid {
        display: block;
      }

      .registration-panel .un-msg.is-valid {
        color: #1f6b3f;
      }

      .registration-panel .un-msg.is-invalid {
        color: #8f1d24;
        display: block;
      }

      .registration-panel .invalid-feedback.d-block,
      .registration-panel .invalid-feedback.is-invalid {
        color: #8f1d24;
      }

      .registration-honeypot {
        position: absolute !important;
        left: -10000px !important;
        top: auto !important;
        width: 1px !important;
        height: 1px !important;
        overflow: hidden !important;
      }

      body.registration-concept #footer {
        color: rgba(255, 255, 255, 0.72);
        margin-left: auto;
        margin-right: auto;
        position: relative;
        text-align: center;
        width: min(1220px, calc(100% - 48px));
        z-index: 1;
      }

      body.registration-concept #footer .copyright {
        text-align: center;
      }

      @media (max-width: 991px) {
        .registration-shell {
          grid-template-columns: 1fr;
        }

        .registration-stage__logo {
          max-width: min(62vw, 300px);
        }

        .registration-panel {
          padding: 22px;
        }

        .registration-shell {
          padding: 22px;
        }
      }

      @media (max-width: 576px) {
        .registration-page {
          padding: 12px;
        }

        .registration-topbar {
          flex-direction: column;
          gap: 10px;
        }

        .registration-stage {
          gap: 10px;
        }

        .registration-stage__logo {
          max-width: 150px;
        }

        .registration-stage p {
          font-size: 0.9rem;
        }

        .registration-hosts span {
          font-size: 0;
          gap: 0;
        }

        .registration-hosts img {
          height: 18px;
          width: 28px;
        }

        .registration-panel {
          padding: 18px;
        }

        .registration-shell {
          padding: 18px;
        }

        body.registration-concept #footer {
          width: calc(100% - 24px);
        }

        .registration-panel .progress-step::after {
          font-size: 0.68rem;
        }

        .registration-panel .row-cols-6 {
          grid-template-columns: repeat(3, 1fr);
        }
      }
    </style>
</head>

  <body class="registration-concept text-bg-dark">

		<div class="registration-page">
		<div class="cover-container d-flex w-100 mx-auto flex-column">
  		<header class="registration-topbar">
  			<div class="registration-brand">
  				<strong>Hendy's Hunches</strong>
          <span>Football prediction game</span>
  			</div>
  				<nav class="nav nav-masthead justify-content-center">
            <a class="nav-link fw-bold py-1 px-0" href="index.php">Login</a>
  					<a class="nav-link fw-bold py-1 px-0" href="forgot-password.php">Reset Password</a>
            <a class="nav-link fw-bold py-1 px-0" href="#terms-panel">Terms</a>
  				</nav>
  		</header>

		<main class="registration-shell">
      <section class="registration-stage" aria-label="Hendy's Hunches registration">
        <img class="registration-stage__logo" src="img/hh-logo-2026-main.png" alt="Hendy's Hunches football predictions logo">
        <p>Join the World Cup 2026 predictions game and get your squad ready before kick-off.</p>
        <div class="registration-hosts" aria-label="World Cup 2026 host nations">
          <span><img src="img/flags/ca.svg" alt=""> Canada</span>
          <span><img src="img/flags/mx.svg" alt=""> Mexico</span>
          <span><img src="img/flags/us.svg" alt=""> United States</span>
        </div>
      </section>
      <section class="registration-panel" aria-label="Registration form">
      <?php if ($registrationSuccess): ?>
          <p class="registration-kicker"><i class="bi bi-check-circle-fill"></i> Registration complete</p>
          <h1>You're in</h1>
          <h3 class="my-5"><i class="bi bi-check-circle-fill text-success"></i><br>You have successfully registered!</h3>
          <p class="mb-3">Thank you for signing up to play Hendy's Hunches.</p>
          <p>You will now be automatically redirected back to the login page.</p> 
          <p>If you are not redirected automatically, please <a href='index.php'>click here</a>.</p>
          <script>
            setTimeout(function() {
              window.location.href = 'index.php';
            }, 5000); // Redirect after 5 seconds
          </script>
        <?php else: ?>

        <p class="registration-kicker"><i class="bi bi-person-plus-fill"></i> New player signup</p>
  			<h1>Register</h1>
        <div class="text-start small text-white-50 mt-2" id="stepLabel">Step 1 of 5: Contact</div>

        <?php if ($registrationError !== '') : ?>
          <div class="alert alert-danger mt-3 mb-0" role="alert"><?= htmlspecialchars($registrationError, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <!-- Progress bar -->
        <div class="progressbar">
          <div class="progress" id="progress"></div>
          <div class="progress-step progress-step-active" data-title="Contact"></div>
          <div class="progress-step" data-title="Account"></div>
          <div class="progress-step" data-title="Avatar"></div>
          <div class="progress-step" data-title="Details"></div>
          <div class="progress-step" data-title="Terms"></div>
        </div>
        <div class="text-start small text-white-50 mt-1">Fields marked with <span class="text-warning">*</span> are required.</div>

      <form class="d-flex flex-column needs-validation" method="POST" action="" id="registrationForm" name="registrationForm" novalidate> <!--  onsubmit="validateAvatar()" onSubmit="return validateFullForm()" border border-white p-2 my-2 border-opacity-25   -->
          <input type="hidden" name="form_issued_at" value="<?= (int) $registrationFormIssuedAt ?>">
          <div class="registration-honeypot" aria-hidden="true">
            <label for="website">Website</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
          </div>
          <!-- Steps -->
          <div class="form-step form-step-active">
            <label for="firstname" class="form-label">First Name <span class="text-warning">*</span></label>
            <input type="text" class="form-control" id="firstname" name="firstname" required autocomplete="given-name">
            <div class="invalid-feedback">
              Please provide your first name.
            </div>
            <label for="surname" class="form-label">Last Name <span class="text-warning">*</span></label>
            <input type="text" class="form-control" id="surname" name="surname" required autocomplete="family-name">
            <div class="invalid-feedback">
              Please provide your last name.
            </div>
            <label for="email" class="form-label">Email <span class="text-warning">*</span></label>
            <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
            <div class="invalid-feedback">
              Please provide a valid email address.
            </div>
            <label for="emailConfirm" class="form-label">Confirm Email <span class="text-warning">*</span></label>
            <input type="email" class="form-control" id="emailConfirm" name="emailConfirm" required autocomplete="email">
            <div class="invalid-feedback">
              Email addresses must match.
            </div>
              <div class="row">
                <hr>
                <div class="col-12 text-end">
                  <button type="button" class="btn btn-primary btn-next w-50">Next <i class="bi bi-arrow-right ms-2"></i></button>
                </div>
              </div>
          </div>
          <div class="form-step">
            <label for="username" class="form-label">Username <span class="text-warning">*</span></label>
            <input type="text" class="form-control" id="username" name="username" required minlength="3" autocomplete="username">
            <span class="un-msg" id="usernameStatus">Choose something short and memorable for logins.</span>
            <label for="pwd1" class="form-label">Password <span class="text-warning">*</span></label>
            <div class="input-group">
              <input type="password" class="form-control" id="pwd1" name="pwd1" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" onchange="form.pwd2.pattern = this.value;" autocomplete="new-password" />
              <button class="btn btn-outline-secondary" type="button" id="togglePwd1" aria-label="Show password">
                <i class="bi bi-eye-slash-fill"></i>
              </button>
            </div>
            <div class="mt-2">
              <div class="progress" role="progressbar" aria-label="Password strength">
                <div class="progress-bar" id="passwordStrengthBar" style="width: 0%"></div>
              </div>
              <div class="small text-white-50 mt-1" id="passwordStrengthText">Strength: Not set</div>
            </div>
            <div class="invalid-feedback">
              Password does not meet criteria.
              <div id="pwdMsg">
                <ul type="none" class="small">
                  <li id="length" class="invalid">Minimum <b>6 characters</b></li>
                  <li id="letter" class="invalid">1 <b>uppercase</b> and 1 <b>lowercase</b> letter</li>
                  <li id="number" class="invalid">1 <b>number</b></li>
                </ul>
              </div>
            </div>
            <label for="pwd2" class="form-label">Confirm Password <span class="text-warning">*</span></label>
            <div class="input-group">
              <input type="password" class="form-control" id="pwd2" name="pwd2" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" autocomplete="new-password">
              <button class="btn btn-outline-secondary" type="button" id="togglePwd2" aria-label="Show password confirmation">
                <i class="bi bi-eye-slash-fill"></i>
              </button>
            </div>
            <div class="invalid-feedback">
              Passwords do not meet criteria or match.
            </div>
            <div class="row">
              <hr>
              <div class="col-6">
                <button type="button" class="btn btn-primary btn-prev w-100"><i class="bi bi-arrow-left me-2"></i>Previous</button>
              </div>
              <div class="col-6 text-end">
                <button type="button" class="btn btn-primary btn-next w-100">Next <i class="bi bi-arrow-right ms-2"></i></button>
              </div>
            </div>
        </div>      
        <div class="form-step">
          <div class="container text-center g-3">
              <label for="avatar" class="form-label">Choose Your Avatar <span class="text-warning">*</span></label>
              <div class="row row-cols-6 g-1">
                  <?php
                  $avatars = [$fk1, $fk2, $fk3, $fk4, $fk5, $fk6, $fk7, $fk8, $fk9, $fk10, $fk11, $fk12, $fk13, $fk14, $fk15, $fk16, $fk17, $fk18];
                  foreach ($avatars as $index => $avatar) {
                    $filename = pathinfo($avatar, PATHINFO_FILENAME);
                    echo "
                    <div class='col'>
                      <input type='radio' class='btn-check' autocomplete='off' id='fk" . ($index + 1) . "' name='fkradio' value='$avatar' onclick='chooseImage(\"fk" . ($index + 1) . "\");' required>
                      <label class='btn btn-outline-light' for='fk" . ($index + 1) . "'>
                        <img src='$avatar' alt='Football kit $filename' class='w-100 img-fluid'/>
                      </label>
                    </div>";
                  }
                  ?>
                  <div id="avatarMsg"></div>
              </div>
          </div>
          <input type="hidden" class="form-control" id="avatar" name="avatar">
          <div class="row">
            <hr>
            <div class="col-6">
              <button type="button" class="btn btn-primary btn-prev w-100"><i class="bi bi-arrow-left me-2"></i>Previous</button>
            </div>
            <div class="col-6 text-end">
              <button type="button" class="btn btn-primary btn-next w-100">Next <i class="bi bi-arrow-right ms-2"></i></button>
            </div>
          </div>
        </div>
        <div class="form-step">
          <label for="fieldofwork" class="form-label">Field of Expertise <span class="text-white-50">(Optional)</span></label>
          <input id="fieldofwork" name="fieldofwork" class="form-select" list="datalistOptions1" placeholder="Start typing to filter">
          <datalist id="datalistOptions1">
            <option value="Prefer Not To Say"></option>        
            <?php
              $file = 'text/select-sectors-input.txt';
              $handle = @fopen($file, 'r');
              if ($handle) {
                while (!feof($handle)) {
                  $line = fgets($handle, 4096);
                  $item = explode('\n', $line);
                  echo '<option value="' . trim($item[0]) . '">' . trim($item[0]) . '</option>' . "\n";
                }
                fclose($handle);
              }
            ?>
          </datalist>
          <!-- Repeat similar structure for other input fields -->
          <label for="location" class="form-label">Location (Nearest Town/City) <span class="text-white-50">(Optional)</span></label>
          <input id="location" name="location" class="form-select" list="datalistOptions4" placeholder="Start typing to filter">
          <datalist id="datalistOptions4">
          <option value="Prefer Not To Say"></option>
            <?php
              $file = 'text/select-ukcities-input.txt';
              $handle = @fopen($file, 'r');
              if ($handle) {
                while (!feof($handle)) {
                  $line = fgets($handle, 4096);
                  $item = explode('\n', $line);
                  echo '<option value="' . trim($item[0]) . '">' . trim($item[0]) . '</option>' . "\n";
                }
                fclose($handle);
              }
            ?>
          </datalist>
          <label for="faveteam" class="form-label">Favourite Team <span class="text-white-50">(Optional)</span></label>
          <input id="faveteam" name="faveteam" class="form-select" list="datalistOptions2" placeholder="Start typing to filter">
          <datalist id="datalistOptions2">
            <option value="Prefer Not To Say"></option>
            <?php
              $file = 'text/select-clubteams-input.txt';
              $handle = @fopen($file, 'r');
              if ($handle) {
                while (!feof($handle)) {
                  $line = fgets($handle, 4096);
                  $item = explode('\n', $line);
                  echo '<option value="' . trim($item[0]) . '">' . trim($item[0]) . '</option>' . "\n";
                }
                fclose($handle);
              }
            ?>
          </datalist>
          <label for="tournwinner" class="form-label">Nation you'll be supporting <span class="text-white-50">(Optional)</span></label>
          <input id="tournwinner" name="tournwinner" class="form-select" list="datalistOptions3" placeholder="Start typing to filter">
          <datalist id="datalistOptions3">
          <option value="Prefer Not To Say"></option>        
            <?php
              foreach ($tournamentWinnerOptions as $winnerOption) {
                echo '<option value="' . htmlspecialchars($winnerOption, ENT_QUOTES) . '">' . htmlspecialchars($winnerOption) . '</option>' . "\n";
              }
            ?>
          </datalist>
          <div class="text-white-50 small mt-2">Just for fun/social - no points awarded for these choices!</div>
          <div class="row">
            <hr>
            <div class="col-6">
              <button type="button" class="btn btn-primary btn-prev w-100"><i class="bi bi-arrow-left me-2"></i>Previous</button>
            </div>
            <div class="col-6 text-end">
              <button type="button" class="btn btn-primary btn-next w-100">Next <i class="bi bi-arrow-right ms-2"></i></button>
            </div>
          </div>
        </div>

          <div class="form-step">             
            <div class="row">
            <?php hh_render_terms_inline_panel(); ?>
              <div class="col-auto d-flex align-items-center my-4 mx-auto">                        
                <input class="form-check-input" type="checkbox" id="disclaimer" name="disclaimer" value="disclaimer" required>
                <label class="form-check-label m-3" for="disclaimer">
                  I agree to the terms and conditions of Hendy's Hunches.
                  <span class="text-danger">*</span>
                </label>
              </div>
              <div class="invalid-feedback">
                You must agree before submitting.
              </div>
            </div>
            <div class="text-white-50 small text-start mt-2">
              You can update your profile details after signup.
            </div>
            <div class="row">
              <hr>
              <div class="col-6">
                <button type="button" class="btn btn-primary btn-prev w-100"><i class="bi bi-arrow-left me-2"></i>Previous</button>
              </div>
              <div class="col-6 text-end">
                <button type="submit" class="btn btn-success w-100"><i class="bi bi-person-plus-fill me-2"></i>Sign up!</button><!-- <i class="fw-bold bi bi-hand-thumbs-up"></i> -->
              </div>
            </div>
          </div>
            <!-- <hr />
            <div class="col-12 d-flex justify-content-evenly" style="margin: 0px 0px 10px 0px;">
              <button class="btn btn-lg btn-primary" type="submit"><i class="fw-bold bi bi-hand-thumbs-up"></i> Sign me up!</button>
              <button class="btn btn-lg btn-outline-light" type="reset" onClick="resetAll();"><i class="fw-bold bi bi-x"></i> Reset all</button>
            </div> -->
      </form>
      <?php endif; ?>
      </section>

		</main>

    <script type="text/javascript">
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (() => {
        'use strict';
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        const forms = document.querySelectorAll('.needs-validation');
        // Loop over them and prevent submission
        Array.from(forms).forEach(form => {
          form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated');
          }, false);

          const findInvalidFeedback = (element) => {
            if (!element) {
              return null;
            }

            if (element.parentElement?.classList.contains('input-group')) {
              return element.parentElement.nextElementSibling?.classList.contains('invalid-feedback')
                ? element.parentElement.nextElementSibling
                : null;
            }

            let sibling = element.nextElementSibling;
            while (sibling) {
              if (sibling.classList?.contains('invalid-feedback')) {
                return sibling;
              }
              sibling = sibling.nextElementSibling;
            }
            return null;
          };

          // Add event listeners to all inputs to handle real-time validation feedback
          const inputs = form.querySelectorAll('input, select, textarea');
          inputs.forEach(input => {
            input.addEventListener('input', () => {
              if (typeof syncFieldState === 'function') {
                syncFieldState(input, {
                  showValidation: input.classList.contains('hh-touched'),
                  showActive: document.activeElement === input
                });
              }
            });
            input.addEventListener('focus', () => {
              if (typeof syncFieldState === 'function') {
                syncFieldState(input, {
                  showValidation: input.classList.contains('hh-touched'),
                  showActive: true
                });
              }
            });
            input.addEventListener('blur', () => {
              input.classList.add('hh-touched');
              if (typeof syncFieldState === 'function') {
                syncFieldState(input, { showValidation: true });
              }
            });
          });
        });
      })();

  		function chooseImage(imageId) {
  			var x = document.getElementById(imageId).value;
  			document.getElementById("avatar").value = x;
  		}

      const togglePwd1 = document.querySelector('#togglePwd1');
      const togglePwd2 = document.querySelector('#togglePwd2');
      const togglePwd1Icon = togglePwd1?.querySelector('i');
      const togglePwd2Icon = togglePwd2?.querySelector('i');
      const pwd1 = document.querySelector('#pwd1');
      const pwd2 = document.querySelector('#pwd2');
      const email = document.querySelector('#email');
      const emailConfirm = document.querySelector('#emailConfirm');
      const passwordStrengthBar = document.querySelector('#passwordStrengthBar');
      const passwordStrengthText = document.querySelector('#passwordStrengthText');
      const usernameInput = document.querySelector('#username');
      const usernameStatus = document.querySelector('#usernameStatus');

      const findInvalidFeedback = (element) => {
        if (!element) {
          return null;
        }

        if (element.parentElement?.classList.contains('input-group')) {
          let sibling = element.parentElement.nextElementSibling;
          while (sibling) {
            if (sibling.classList?.contains('invalid-feedback')) {
              return sibling;
            }
            sibling = sibling.nextElementSibling;
          }
          return null;
        }

        let sibling = element.nextElementSibling;
        while (sibling) {
          if (sibling.classList?.contains('invalid-feedback')) {
            return sibling;
          }
          sibling = sibling.nextElementSibling;
        }

        return null;
      };

      const findValidationHelp = (element) => {
        if (!element) {
          return null;
        }

        let sibling = element.nextElementSibling;
        while (sibling) {
          if (sibling.classList?.contains('validation-help') || sibling.classList?.contains('un-msg')) {
            return sibling;
          }
          if (sibling.classList?.contains('invalid-feedback')) {
            return null;
          }
          sibling = sibling.nextElementSibling;
        }

        if (element.parentElement?.classList.contains('input-group')) {
          let groupSibling = element.parentElement.nextElementSibling;
          while (groupSibling) {
            if (groupSibling.classList?.contains('validation-help') || groupSibling.classList?.contains('un-msg')) {
              return groupSibling;
            }
            if (groupSibling.classList?.contains('invalid-feedback')) {
              return null;
            }
            groupSibling = groupSibling.nextElementSibling;
          }
        }

        return null;
      };

      const updateGroupState = (input, stateClass) => {
        const group = input?.parentElement?.classList.contains('input-group') ? input.parentElement : null;
        if (!group) {
          return;
        }
        group.classList.remove('is-valid', 'is-invalid');
        if (stateClass) {
          group.classList.add(stateClass);
        }
      };

      const syncFieldState = (input, options = {}) => {
        if (!input || input.type === 'hidden') {
          return true;
        }

        const showValidation = !!options.showValidation;
        const showActive = !!options.showActive;
        const feedback = findInvalidFeedback(input);
        const help = findValidationHelp(input);
        const isValid = input.checkValidity();
        const isOptionalBlank = !input.required && input.value.trim() === '';

        input.classList.remove('is-valid', 'is-invalid');
        updateGroupState(input, '');

        if (isOptionalBlank) {
          if (feedback) {
            feedback.classList.remove('d-block', 'is-active');
          }
          if (help) {
            help.classList.remove('is-valid', 'is-invalid', 'is-active');
          }
          return true;
        }

        if (isValid && input.value.trim() !== '') {
          input.classList.add('is-valid');
          updateGroupState(input, 'is-valid');
          if (feedback) {
            feedback.classList.remove('d-block', 'is-active');
          }
          if (help) {
            help.classList.remove('is-invalid', 'is-active');
            help.classList.add('is-valid');
          }
        } else if (showValidation) {
          input.classList.add('is-invalid');
          updateGroupState(input, 'is-invalid');
          if (feedback) {
            feedback.classList.add('d-block');
          }
          if (help) {
            help.classList.remove('is-valid', 'is-active');
            help.classList.add('is-invalid');
          }
        } else if (showActive) {
          if (help) {
            help.classList.remove('is-valid', 'is-invalid');
            help.classList.add('is-active');
          }
        } else {
          if (feedback) {
            feedback.classList.remove('d-block', 'is-active');
          }
          if (help) {
            help.classList.remove('is-valid', 'is-invalid', 'is-active');
          }
        }

        return isValid;
      };

      window.syncFieldState = syncFieldState;

      const validateMatchingFields = (field, confirmField, message) => {
        if (!field || !confirmField) {
          return;
        }

        const checkMatch = () => {
          if (confirmField.value && field.value !== confirmField.value) {
            confirmField.setCustomValidity(message);
          } else {
            confirmField.setCustomValidity('');
          }
          syncFieldState(field, { showValidation: field.classList.contains('hh-touched') });
          syncFieldState(confirmField, { showValidation: confirmField.classList.contains('hh-touched') });
        };

        field.addEventListener('input', checkMatch);
        confirmField.addEventListener('input', checkMatch);
      };

      validateMatchingFields(email, emailConfirm, 'Email addresses must match.');
      validateMatchingFields(pwd1, pwd2, 'Passwords must match.');

      togglePwd1.addEventListener('click', function (e) {
          // Toggle the type attribute
          const type = pwd1.getAttribute('type') === 'password' ? 'text' : 'password';
          pwd1.setAttribute('type', type);
          // Toggle the eye / eye slash icon
          togglePwd1Icon?.classList.toggle('bi-eye');
          togglePwd1Icon?.classList.toggle('bi-eye-slash-fill');
      });

      togglePwd2.addEventListener('click', function (e) {
          // Toggle the type attribute
          const type = pwd2.getAttribute('type') === 'password' ? 'text' : 'password';
          pwd2.setAttribute('type', type);
          // Toggle the eye / eye slash icon
          togglePwd2Icon?.classList.toggle('bi-eye');
          togglePwd2Icon?.classList.toggle('bi-eye-slash-fill');
      });

      var myInput = document.getElementById("pwd1");
      var letter = document.getElementById("letter");
      var number = document.getElementById("number");
      var length = document.getElementById("length");

      // When the user clicks on the password field, show the  box
      myInput.onfocus = function() {
        document.getElementById("pwdMsg").style.display = "block";
      }
      // When the user clicks outside of the password field, hide the message box
      myInput.onblur = function() {
        document.getElementById("pwdMsg").style.display = "none";
      }
      // When the user starts to type something inside the password field
      myInput.onkeyup = function() {
        // Validate lowercase letters
        var lowerCaseLetters = /[a-z]/g;
        var upperCaseLetters = /[A-Z]/g;
        if( (myInput.value.match(lowerCaseLetters) && (myInput.value.match(upperCaseLetters)) )) {
          letter.classList.remove("invalid");
          letter.classList.add("valid");
        } else {
          letter.classList.remove("valid");
          letter.classList.add("invalid");
        }
        // Validate numbers
        var numbers = /[0-9]/g;
        if(myInput.value.match(numbers)) {
          number.classList.remove("invalid");
          number.classList.add("valid");
        } else {
          number.classList.remove("valid");
          number.classList.add("invalid");
        }
        // Validate length
        if(myInput.value.length >= 6) {
          length.classList.remove("invalid");
          length.classList.add("valid");
        } else {
          length.classList.remove("valid");
          length.classList.add("invalid");
        }

        if (passwordStrengthBar && passwordStrengthText) {
          const hasLower = lowerCaseLetters.test(myInput.value);
          const hasUpper = upperCaseLetters.test(myInput.value);
          const hasNumber = numbers.test(myInput.value);
          const longEnough = myInput.value.length >= 6;
          const extraLength = myInput.value.length >= 10;
          let score = 0;

          if (hasLower && hasUpper) score += 1;
          if (hasNumber) score += 1;
          if (longEnough) score += 1;
          if (extraLength) score += 1;

          const percent = Math.min((score / 4) * 100, 100);
          passwordStrengthBar.style.width = `${percent}%`;
          passwordStrengthBar.classList.remove('bg-danger', 'bg-warning', 'bg-success');

          if (score <= 1) {
            passwordStrengthBar.classList.add('bg-danger');
            passwordStrengthText.textContent = 'Strength: Weak';
          } else if (score === 2) {
            passwordStrengthBar.classList.add('bg-warning');
            passwordStrengthText.textContent = 'Strength: Fair';
          } else {
            passwordStrengthBar.classList.add('bg-success');
            passwordStrengthText.textContent = 'Strength: Strong';
          }
        }
      }

      let usernameLookupTimeout = null;

      if (usernameInput && usernameStatus) {
        usernameInput.addEventListener('input', function () {
          const username = usernameInput.value.trim();
          usernameInput.setCustomValidity('');

          if (usernameLookupTimeout) {
            window.clearTimeout(usernameLookupTimeout);
          }

          if (username.length < 3) {
            usernameInput.setCustomValidity('Please provide a username of at least 3 characters.');
            usernameStatus.textContent = 'Use at least 3 characters for your username.';
            usernameStatus.classList.remove('is-valid');
            usernameStatus.classList.add('is-invalid');
            syncFieldState(usernameInput, {
              showValidation: usernameInput.classList.contains('hh-touched'),
              showActive: document.activeElement === usernameInput
            });
            return;
          }

          usernameStatus.textContent = 'Checking username availability…';
          usernameStatus.classList.remove('is-valid', 'is-invalid');

          usernameLookupTimeout = window.setTimeout(() => {
            fetch('php/username-check.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: `username=${encodeURIComponent(username)}`
            })
              .then(response => response.text())
              .then(data => {
                const result = data.trim();
                if (result === '1') {
                  usernameInput.setCustomValidity('Username is already taken.');
                  usernameStatus.textContent = 'That username is already taken.';
                  usernameStatus.classList.remove('is-valid');
                  usernameStatus.classList.add('is-invalid');
                } else {
                  usernameInput.setCustomValidity('');
                  usernameStatus.textContent = 'Username available.';
                  usernameStatus.classList.remove('is-invalid');
                  usernameStatus.classList.add('is-valid');
                }

                syncFieldState(usernameInput, {
                  showValidation: usernameInput.classList.contains('hh-touched') || result === '1',
                  showActive: document.activeElement === usernameInput
                });
              })
              .catch(() => {
                usernameInput.setCustomValidity('We could not verify that username right now.');
                usernameStatus.textContent = 'We could not check that username right now.';
                usernameStatus.classList.remove('is-valid');
                usernameStatus.classList.add('is-invalid');
                syncFieldState(usernameInput, {
                  showValidation: true,
                  showActive: document.activeElement === usernameInput
                });
              });
          }, 250);
        });
      }
	</script>
    </div>
    </div>

  <!-- Footer -->
  <?php include "php/footer.php" ?>
