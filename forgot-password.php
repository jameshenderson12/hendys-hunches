<?php
include 'php/config.php';
include 'php/db-connect.php';
require_once 'php/email.php';
require_once 'php/terms.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['e'])) {
    $e = mysqli_real_escape_string($con, $_POST['e']);
    $sql = "SELECT id, firstname, username FROM live_user_information WHERE email='$e' LIMIT 1";
    $query = mysqli_query($con, $sql);
    $numrows = mysqli_num_rows($query);

    if ($numrows > 0) {
        while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $id = $row['id'];
            $fn = $row['firstname'];
            $u = $row['username'];
        }
        $emailcut = substr($e, 0, 4);
        $randNum = rand(10000, 99999);
        $tempPass = "$emailcut$randNum";
        $hashTempPass = md5($tempPass);
        $sql = "UPDATE live_temp_information SET temp_pass='$hashTempPass' WHERE username='$u' LIMIT 1";
        $query = mysqli_query($con, $sql);
        $mailSent = sendTempPasswordEmail($fn, $u, $e, $tempPass, $hashTempPass);
        mysqli_close($con);

        if ($mailSent) {
            echo 'success';
        } elseif (hh_mail_is_enabled()) {
            echo 'email_send_failed';
        } else {
            echo 'success';
        }
        exit();
    }

    mysqli_close($con);
    echo 'no_exist';
    exit();
}

if (isset($_GET['u']) && isset($_GET['p'])) {
    $u = $_GET['u'];
    $temppasshash = $_GET['p'];
    if (strlen($temppasshash) < 10) {
        exit();
    }
    $sql = "SELECT id FROM live_temp_information WHERE username='$u' AND temp_pass='$temppasshash' LIMIT 1";
    $query = mysqli_query($con, $sql);
    $numrows = mysqli_num_rows($query);
    if ($numrows == 0) {
        echo 'There is no match for that username with that temporary password in the system. We cannot proceed.';
        exit();
    } else {
        $row = mysqli_fetch_row($query);
        $id = $row[0];
        $sql = "UPDATE live_user_information SET password='$temppasshash' WHERE id='$id' AND username='$u' LIMIT 1";
        $query = mysqli_query($con, $sql);
        $sql = "UPDATE live_temp_information SET temp_pass='' WHERE username='$u' LIMIT 1";
        $query = mysqli_query($con, $sql);
        header('location: index.php');
        exit();
    }
}

mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en-GB">
  <head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-QN708QFJSD"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-QN708QFJSD');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
    <meta name="keywords" content="football, predictions, game">
    <title>Forgot Password - Hendy's Hunches</title>
    <link rel="shortcut icon" href="ico/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
      :root {
        --hh-ink: #16231d;
        --hh-cream: #fbfcf8;
        --hh-green: #04331e;
        --hh-green-mid: #0a5a38;
        --hh-purple: #8f66d8;
        --hh-purple-dark: #402064;
        --hh-purple-deep: #28133f;
        --hh-gold: #f0c556;
        --hh-line: rgba(255, 255, 255, 0.22);
        --hh-shadow: 0 24px 68px rgba(0, 0, 0, 0.32);
      }

      * {
        box-sizing: border-box;
      }

      body {
        min-height: 100vh;
        margin: 0;
        background:
          linear-gradient(120deg, rgba(0, 31, 17, 0.94), rgba(0, 52, 28, 0.7) 46%, rgba(64, 32, 100, 0.92)),
          url("img/football-stadium.jpg") center / cover fixed no-repeat;
        color: #ffffff;
        font-family: 'Lato', sans-serif;
      }

      a {
        color: #ffffff;
      }

      .reset-shell {
        display: grid;
        grid-template-rows: auto 1fr auto;
        min-height: 100vh;
        padding: 24px;
        position: relative;
      }

      .reset-shell::before {
        background:
          radial-gradient(circle at 20% 20%, rgba(240, 197, 86, 0.22), transparent 26%),
          radial-gradient(circle at 78% 26%, rgba(143, 102, 216, 0.3), transparent 30%),
          linear-gradient(180deg, rgba(255, 255, 255, 0.08), transparent 44%);
        content: "";
        inset: 0;
        pointer-events: none;
        position: fixed;
      }

      .reset-topbar,
      .reset-main,
      .reset-footer {
        position: relative;
        z-index: 1;
      }

      .reset-topbar {
        align-items: center;
        display: flex;
        gap: 16px;
        justify-content: space-between;
        margin: 0 auto;
        max-width: 1180px;
        width: 100%;
      }

      .reset-brand {
        display: grid;
        gap: 2px;
        text-decoration: none;
      }

      .reset-brand strong,
      .reset-brand span {
        display: block;
        letter-spacing: 0;
      }

      .reset-brand strong {
        color: #ffffff;
        font-size: 1.08rem;
        font-weight: 900;
        line-height: 1;
      }

      .reset-brand span {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.78rem;
        font-weight: 900;
        text-transform: uppercase;
      }

      .reset-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: flex-end;
      }

      .reset-nav a {
        border-bottom: 2px solid transparent;
        font-weight: 900;
        padding: 6px 0;
        text-decoration: none;
      }

      .reset-nav a:hover,
      .reset-nav a:focus {
        border-bottom-color: var(--hh-gold);
      }

      .reset-main {
        align-items: center;
        display: grid;
        justify-items: center;
        margin: 0 auto;
        max-width: 1180px;
        padding: 48px 0 22px;
        width: 100%;
      }

      .reset-panel-wrap {
        max-width: 620px;
        position: relative;
        width: 100%;
      }

      .reset-panel {
        background: rgba(251, 252, 248, 0.96);
        border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 8px;
        box-shadow: var(--hh-shadow);
        color: var(--hh-ink);
        overflow: hidden;
        position: relative;
      }

      .reset-panel__band {
        background: linear-gradient(135deg, rgba(143, 102, 216, 0.96), rgba(64, 32, 100, 0.98));
        color: #ffffff;
        padding: 18px 24px;
      }

      .reset-panel__band p {
        color: rgba(255, 255, 255, 0.74);
        font-size: 0.74rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        margin: 0 0 6px;
        text-transform: uppercase;
      }

      .reset-panel__band h2 {
        font-size: 1.6rem;
        font-weight: 900;
        margin: 0;
      }

      .reset-panel__body {
        display: grid;
        gap: 18px;
        padding: 24px;
      }

      .reset-copy {
        color: #45524c;
        margin: 0;
      }

      .reset-field {
        display: grid;
        gap: 8px;
      }

      .reset-field label {
        color: var(--hh-green-dark, var(--hh-green));
        font-size: 0.78rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        margin: 0;
        text-transform: uppercase;
      }

      .reset-input {
        background: #ffffff;
        border: 1px solid rgba(4, 51, 30, 0.16);
        border-radius: 8px;
        color: var(--hh-ink);
        font-size: 1rem;
        min-height: 52px;
        padding: 0 16px;
        width: 100%;
      }

      .reset-input:focus {
        border-color: rgba(143, 102, 216, 0.7);
        box-shadow: 0 0 0 0.18rem rgba(143, 102, 216, 0.18);
        outline: none;
      }

      .reset-status {
        border-radius: 8px;
        display: none;
        font-size: 0.94rem;
        font-weight: 700;
        margin: 0;
        padding: 12px 14px;
      }

      .reset-status.is-visible {
        display: block;
      }

      .reset-status.is-loading {
        align-items: center;
        color: #4b5563;
        display: inline-flex;
        gap: 10px;
      }

      .reset-status.is-error {
        background: rgba(214, 64, 69, 0.12);
        color: #a52f33;
      }

      .reset-status.is-success {
        background: rgba(25, 135, 84, 0.12);
        color: #146c43;
      }

      .reset-spinner {
        animation: reset-spin 0.9s linear infinite;
        border: 2px solid rgba(22, 35, 29, 0.16);
        border-radius: 50%;
        border-top-color: var(--hh-purple);
        height: 18px;
        width: 18px;
      }

      @keyframes reset-spin {
        to {
          transform: rotate(360deg);
        }
      }

      .reset-actions {
        display: grid;
        gap: 10px;
      }

      .btn-hh-primary,
      .btn-hh-secondary {
        align-items: center;
        border-radius: 8px;
        display: inline-flex;
        font-weight: 900;
        gap: 10px;
        justify-content: center;
        min-height: 52px;
        padding: 0 20px;
        text-decoration: none;
      }

      .btn-hh-primary {
        background: linear-gradient(135deg, var(--hh-purple), #7c59c3);
        border: none;
        color: #ffffff;
      }

      .btn-hh-primary:hover,
      .btn-hh-primary:focus {
        background: linear-gradient(135deg, #9b74e0, #6f47b9);
        color: #ffffff;
      }

      .btn-hh-secondary {
        background: transparent;
        border: 1px solid rgba(4, 51, 30, 0.14);
        color: var(--hh-green);
      }

      .btn-hh-secondary:hover,
      .btn-hh-secondary:focus {
        background: rgba(4, 51, 30, 0.06);
        color: var(--hh-green);
      }

      .reset-help {
        color: #5c6b64;
        font-size: 0.92rem;
        margin: 0;
      }

      .reset-confirm {
        display: none;
        gap: 16px;
      }

      .reset-confirm.is-visible {
        display: grid;
      }

      .reset-confirm__card {
        background: rgba(4, 51, 30, 0.04);
        border: 1px solid rgba(4, 51, 30, 0.1);
        border-radius: 8px;
        display: grid;
        gap: 12px;
        padding: 20px;
      }

      .reset-confirm__card h3 {
        color: var(--hh-green);
        font-size: 1.18rem;
        font-weight: 900;
        margin: 0;
      }

      .reset-confirm__card p {
        color: #4c5a53;
        margin: 0;
      }

      .reset-footer {
        color: rgba(255, 255, 255, 0.76);
        font-size: 0.88rem;
        margin: 0 auto;
        max-width: 1180px;
        text-align: center;
        width: 100%;
      }

      .reset-footer p {
        margin: 0;
      }

      .reset-footer a {
        color: #ffffff;
        text-decoration: underline;
      }

      .modal-content {
        color: var(--hh-ink);
      }

      .modal-body,
      .modal-body p,
      .modal-body li {
        color: var(--hh-ink);
      }

      @media (max-width: 767px) {
        .reset-shell {
          padding: 18px;
        }

        .reset-topbar {
          align-items: flex-start;
          flex-direction: column;
        }

        .reset-nav {
          gap: 10px 14px;
          justify-content: flex-start;
        }

        .reset-main {
          padding-top: 20px;
        }

        .reset-panel__band,
        .reset-panel__body {
          padding: 20px;
        }
      }

      @media (max-width: 520px) {
        .reset-shell {
          padding: 14px;
        }

        .reset-nav a {
          font-size: 0.95rem;
        }

        .reset-panel__band h2 {
          font-size: 1.4rem;
        }
      }
    </style>
    <script>
      function _(x) {
        return document.getElementById(x);
      }

      function ajaxObj(meth, url) {
        var x = new XMLHttpRequest();
        x.open(meth, url, true);
        x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        return x;
      }

      function ajaxReturn(x) {
        if (x.readyState == 4 && x.status == 200) {
          return true;
        }
      }

      function setStatus(message, state) {
        var status = _("status");
        if (!status) {
          return;
        }

        status.className = "reset-status is-visible";
        if (state) {
          status.classList.add("is-" + state);
        }

        if (state === "loading") {
          status.innerHTML = '<span class="reset-spinner" aria-hidden="true"></span><span>' + message + '</span>';
        } else {
          status.textContent = message;
        }
      }

      function forgotPass() {
        var e = _("email").value.trim();
        if (e === "") {
          setStatus("Please type in your email address.", "error");
          return;
        }

        setStatus("Checking your account and preparing your temporary password…", "loading");

        var ajax = ajaxObj("POST", "forgot-password.php");
        ajax.onreadystatechange = function() {
          if (ajaxReturn(ajax) == true) {
            var response = ajax.responseText;
            if (response == "success") {
              _("forgotPassForm").style.display = "none";
              _("confirm-msg").classList.add("is-visible");
            } else if (response == "no_exist") {
              setStatus("Sorry, that email address has not been registered.", "error");
            } else if (response == "email_send_failed") {
              setStatus("The email could not be sent. Please check the mail settings and try again.", "error");
            } else {
              setStatus("An unknown error occurred. Please try again.", "error");
            }
          }
        };
        ajax.send("e=" + encodeURIComponent(e));
      }

      function windowClose() {
        window.open('', '_parent', '');
        window.close();
      }
    </script>
  </head>

  <body>
    <div class="reset-shell">
      <header class="reset-topbar">
        <a class="reset-brand" href="index.php">
          <strong>Hendy's Hunches</strong>
          <span>Football Predictions Game</span>
        </a>
        <nav class="reset-nav" aria-label="Reset password links">
          <a href="index.php">Login</a>
          <a href="registration.php">Register</a>
          <a href="#" data-bs-toggle="modal" data-bs-target="#terms">Terms</a>
        </nav>
      </header>

      <main class="reset-main">
        <section class="reset-panel-wrap">
          <div class="reset-panel">
            <div class="reset-panel__band">
              <p>Password help</p>
              <h2>Forgotten Password</h2>
            </div>

            <div class="reset-panel__body">
              <form id="forgotPassForm" name="forgotPassForm" onsubmit="return false;" class="reset-actions" novalidate>
                <p class="reset-copy">Use the email address you registered with. We’ll send a temporary password and then you can choose something new once you’re back in.</p>

                <div class="reset-field">
                  <label for="email">Email address</label>
                  <input type="email" class="reset-input" id="email" name="email" autocomplete="email" required>
                </div>

                <p id="status" class="reset-status" aria-live="polite"></p>

                <button type="button" id="forgotpassbtn" class="btn-hh-primary" onclick="forgotPass();">
                  <i class="bi bi-envelope-paper-heart"></i> Generate temporary password
                </button>

                <p class="reset-help">Check your junk or spam folder too once the email is on its way.</p>
              </form>

              <div id="confirm-msg" class="reset-confirm" aria-live="polite">
                <div class="reset-confirm__card">
                  <h3>Now check your inbox</h3>
                  <p>Please check your email inbox, including junk or spam, for a message containing your temporary password. Follow the link in that email to get back into Hendy’s Hunches.</p>
                  <p>You can close this window once you’ve opened the message.</p>
                </div>
                <div class="reset-actions">
                  <button type="button" class="btn-hh-secondary" onclick="windowClose()">
                    <i class="bi bi-x-circle"></i> Close window
                  </button>
                  <a class="btn-hh-primary" href="index.php">
                    <i class="bi bi-arrow-left-circle"></i> Back to login
                  </a>
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>

      <?php hh_render_terms_modal(); ?>

      <footer class="reset-footer">
        <p>Predictions game based on <a href="<?= htmlspecialchars($competition_url, ENT_QUOTES) ?>"><?= htmlspecialchars($competition, ENT_QUOTES) ?></a><br><?= htmlspecialchars($title, ENT_QUOTES) ?> <?= htmlspecialchars($version, ENT_QUOTES) ?> &copy; <?= htmlspecialchars($year, ENT_QUOTES) ?> <?= htmlspecialchars($developer, ENT_QUOTES) ?>.</p>
      </footer>
    </div>
  </body>
</html>
