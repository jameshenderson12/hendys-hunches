<?php
session_start();
require_once __DIR__ . '/php/auth.php';
hh_handle_dev_bypass_request();
?>
<!DOCTYPE html>
<html lang="en-GB">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
    <title>Login - Hendy's Hunches</title>
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

      .login-shell {
        display: grid;
        grid-template-rows: auto 1fr auto;
        min-height: 100vh;
        padding: 24px;
        position: relative;
      }

      .login-shell::before {
        background:
          radial-gradient(circle at 20% 20%, rgba(240, 197, 86, 0.22), transparent 26%),
          radial-gradient(circle at 78% 26%, rgba(143, 102, 216, 0.3), transparent 30%),
          linear-gradient(180deg, rgba(255, 255, 255, 0.08), transparent 44%);
        content: "";
        inset: 0;
        pointer-events: none;
        position: fixed;
      }

      .login-topbar,
      .login-main,
      .login-footer {
        position: relative;
        z-index: 1;
      }

      .login-topbar {
        align-items: center;
        display: flex;
        gap: 16px;
        justify-content: space-between;
        margin: 0 auto;
        max-width: 1180px;
        width: 100%;
      }

      .login-brand {
        display: grid;
        gap: 2px;
        text-decoration: none;
      }

      .login-brand strong,
      .login-brand span {
        display: block;
        letter-spacing: 0;
      }

      .login-brand strong {
        color: #ffffff;
        font-size: 1.08rem;
        font-weight: 900;
        line-height: 1;
      }

      .login-brand span {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.78rem;
        font-weight: 900;
        text-transform: uppercase;
      }

      .login-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: flex-end;
      }

      .login-nav a {
        border-bottom: 2px solid transparent;
        font-weight: 900;
        padding: 6px 0;
        text-decoration: none;
      }

      .login-nav a:hover,
      .login-nav a:focus {
        border-bottom-color: var(--hh-gold);
      }

      .login-main {
        align-items: center;
        display: grid;
        gap: 28px;
        grid-template-columns: minmax(0, 1fr) minmax(360px, 0.9fr);
        margin: 0 auto;
        max-width: 1180px;
        padding: 28px 0 22px;
        width: 100%;
      }

      .brand-stage {
        align-items: center;
        display: grid;
        justify-items: center;
        min-height: 470px;
        padding: 12px 22px;
        text-align: center;
      }

      .brand-stage__logo {
        filter: drop-shadow(0 24px 42px rgba(0, 0, 0, 0.42));
        height: auto;
        max-height: min(44vh, 340px);
        max-width: min(88vw, 380px);
        width: auto;
      }

      .brand-stage__copy {
        display: grid;
        gap: 12px;
        margin-top: 6px;
        max-width: 620px;
      }

      .brand-stage__copy h1 {
        color: #ffffff;
        font-size: clamp(2.4rem, 7vw, 5.8rem);
        font-weight: 900;
        letter-spacing: 0;
        line-height: 0.92;
        margin: 0;
        text-transform: uppercase;
      }

      .brand-stage__copy p {
        color: rgba(255, 255, 255, 0.82);
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0 auto;
        max-width: 520px;
      }

      .countdown-card {
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 8px;
        display: inline-flex;
        gap: 12px;
        justify-content: center;
        margin: 4px auto 0;
        padding: 12px 16px;
      }

      .countdown-card__value {
        color: #ffffff;
        font-size: clamp(1.8rem, 5vw, 2.5rem);
        font-weight: 900;
        letter-spacing: 0;
        line-height: 1;
      }

      .countdown-card__label {
        color: rgba(255, 255, 255, 0.82);
        display: grid;
        font-size: 0.82rem;
        font-weight: 900;
        gap: 2px;
        line-height: 1.1;
        text-align: left;
        text-transform: uppercase;
      }

      .host-flags {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        justify-content: center;
        margin-top: 2px;
      }

      .host-flags span {
        align-items: center;
        color: rgba(255, 255, 255, 0.88);
        display: inline-flex;
        gap: 8px;
        font-weight: 900;
      }

      .host-flags img {
        border: 1px solid rgba(255, 255, 255, 0.42);
        border-radius: 4px;
        box-shadow: 0 5px 12px rgba(0, 0, 0, 0.22);
        height: 24px;
        object-fit: cover;
        width: 36px;
      }

      .login-panel {
        background: rgba(251, 252, 248, 0.96);
        border: 1px solid rgba(255, 255, 255, 0.55);
        border-radius: 8px;
        box-shadow: var(--hh-shadow);
        color: var(--hh-ink);
        margin-top: 22px;
        padding: 34px 26px 26px;
        position: relative;
        z-index: 1;
      }

      .login-panel-wrap {
        margin-top: 22px;
        position: relative;
        overflow: visible;
      }

      .login-panel__mascot {
        filter: drop-shadow(0 16px 20px rgba(0, 0, 0, 0.24));
        height: 250px;
        object-fit: contain;
        object-position: center bottom;
        pointer-events: none;
        position: absolute;
        right: -12px;
        top: -132px;
        width: 280px;
        z-index: 30;
      }

      .login-panel::after {
        background: linear-gradient(90deg, rgba(251, 252, 248, 0), rgba(251, 252, 248, 0.96) 34%);
        content: "";
        height: 160px;
        pointer-events: none;
        position: absolute;
        right: 0;
        top: 16px;
        width: min(58%, 250px);
        z-index: 1;
      }

      .login-panel__eyebrow {
        align-items: center;
        color: var(--hh-purple-dark);
        display: inline-flex;
        font-size: 0.78rem;
        font-weight: 900;
        gap: 8px;
        letter-spacing: 0;
        margin: 0 0 10px;
        position: relative;
        text-transform: uppercase;
        z-index: 2;
      }

      .login-panel h2 {
        color: var(--hh-ink);
        font-size: clamp(1.9rem, 4vw, 3rem);
        font-weight: 900;
        letter-spacing: 0;
        line-height: 1;
        margin: 0 0 10px;
        position: relative;
        z-index: 2;
      }

      .login-panel__intro {
        color: #59635f;
        font-weight: 700;
        margin: 0 0 24px;
        position: relative;
        z-index: 2;
      }

      .login-panel form {
        position: relative;
        z-index: 2;
      }

      .form-label {
        color: var(--hh-ink);
        font-weight: 900;
      }

      .form-control {
        border: 1px solid rgba(22, 35, 29, 0.22);
        border-radius: 8px;
        min-height: 48px;
      }

      .form-control:focus {
        border-color: var(--hh-purple);
        box-shadow: 0 0 0 0.2rem rgba(143, 102, 216, 0.22);
      }

      .password-control {
        display: grid;
        gap: 8px;
        grid-template-columns: minmax(0, 1fr) 48px;
      }

      .password-control .btn {
        border-radius: 8px;
      }

      .btn-hh-primary {
        align-items: center;
        background: var(--hh-purple);
        border: 1px solid var(--hh-purple);
        border-radius: 8px;
        color: #ffffff;
        display: inline-flex;
        font-weight: 900;
        gap: 8px;
        justify-content: center;
        min-height: 50px;
      }

      .btn-hh-primary:hover,
      .btn-hh-primary:focus {
        background: #7650bd;
        border-color: #7650bd;
        color: #ffffff;
      }

      .btn-hh-secondary {
        align-items: center;
        background: #2f8f63;
        border: 1px solid #2f8f63;
        border-radius: 8px;
        color: #ffffff;
        display: inline-flex;
        font-weight: 900;
        gap: 8px;
        justify-content: center;
        min-height: 46px;
      }

      .btn-hh-secondary:hover,
      .btn-hh-secondary:focus {
        background: #257a54;
        border-color: #257a54;
        color: #ffffff;
      }

      .login-actions {
        display: grid;
        gap: 12px;
        grid-template-columns: minmax(0, 7fr) minmax(0, 3fr);
        margin-top: 20px;
      }

      .login-meta {
        align-items: center;
        border-top: 1px solid rgba(22, 35, 29, 0.12);
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin-top: 22px;
        padding-top: 18px;
      }

      .login-meta a,
      .login-meta span {
        color: var(--hh-green);
        font-weight: 900;
        text-align: center;
      }

      .login-footer {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.9rem;
        font-weight: 700;
        margin: 0 auto;
        max-width: 1180px;
        text-align: center;
        width: 100%;
      }

      .modal {
        color: var(--hh-ink);
      }

      @media (max-width: 991px) {
        .login-shell {
          padding: 18px;
        }

        .login-topbar {
          align-items: flex-start;
        }

        .login-main {
          gap: 20px;
          grid-template-columns: minmax(0, 0.95fr) minmax(340px, 0.9fr);
          padding: 30px 0 34px;
        }

        .brand-stage {
          min-height: 0;
          padding: 8px 0 4px;
        }

        .brand-stage__logo {
          max-height: none;
          max-width: min(38vw, 320px);
        }

        .brand-stage__copy {
          margin-top: 0;
        }

        .brand-stage__copy p {
          font-size: 1rem;
        }

        .login-panel {
          width: 100%;
        }

        .login-panel__mascot {
          display: none !important;
        }
      }

      @media (max-width: 576px) {
        .login-shell {
          padding: 12px 12px 16px;
        }

        .login-topbar {
          align-items: flex-start;
          flex-direction: column;
          gap: 10px;
        }

        .login-nav {
          justify-content: flex-start;
          gap: 10px;
        }

        .login-nav a {
          font-size: 0.92rem;
          padding: 2px 0;
        }

        .login-main {
          gap: 10px;
          grid-template-areas:
            "brandcopy brandlogo"
            "login login";
          grid-template-columns: minmax(0, 1fr) 92px;
          padding: 10px 0 20px;
        }

        .brand-stage {
          display: contents;
        }

        .brand-stage > div {
          display: contents;
        }

        .brand-stage__logo {
          align-self: start;
          grid-area: brandlogo;
          justify-self: end;
          max-width: 86px;
        }

        .brand-stage__copy {
          align-self: center;
          gap: 6px;
          grid-area: brandcopy;
          justify-items: start;
          margin-top: 0;
          text-align: left;
        }

        .brand-stage__copy p {
          font-size: 0.88rem;
          margin: 0;
          max-width: 220px;
        }

        .countdown-card {
          justify-content: flex-start;
          margin-left: 0;
          padding: 10px 12px;
        }

        .countdown-card__value {
          font-size: 1.45rem;
        }

        .countdown-card__label {
          font-size: 0.72rem;
        }

        .login-panel {
          grid-area: login;
          padding: 26px 18px 18px;
        }

        .login-panel::after {
          display: none;
        }

        .login-panel h2 {
          font-size: 2rem;
        }

        .login-panel__intro {
          margin-bottom: 18px;
        }

        .host-flags {
          gap: 7px;
          justify-content: flex-start;
        }

        .host-flags span {
          font-size: 0;
          gap: 0;
        }

        .host-flags img {
          height: 18px;
          width: 28px;
        }

        .login-actions {
          grid-template-columns: 1fr;
        }

        .login-footer {
          font-size: 0.82rem;
        }
      }

      @media (max-width: 380px) {
        .login-brand strong {
          font-size: 1rem;
        }

        .login-brand span,
        .login-nav a {
          font-size: 0.76rem;
        }

        .login-main {
          grid-template-columns: minmax(0, 1fr) 76px;
        }

        .brand-stage__logo {
          max-width: 74px;
        }

        .brand-stage__copy p {
          font-size: 0.84rem;
          max-width: 205px;
        }

        .countdown-card {
          gap: 10px;
          padding: 8px 10px;
        }

        .login-panel {
          padding: 24px 14px 16px;
        }

        .login-footer {
          font-size: 0.76rem;
        }
      }
    </style>
  </head>

  <body>
    <div class="login-shell">
      <header class="login-topbar">
        <a class="login-brand" href="index.php" aria-label="Hendy's Hunches login">
          <strong>Hendy's Hunches</strong>
          <span>Football prediction game</span>
        </a>
        <nav class="login-nav" aria-label="Login links">
          <a href="registration.php">Register</a>
          <a href="forgot-password.php">Reset Password</a>
          <a href="#" data-bs-toggle="modal" data-bs-target="#terms">Terms</a>
        </nav>
      </header>

      <main class="login-main">
        <section class="brand-stage" aria-label="Hendy's Hunches brand">
          <div>
            <img class="brand-stage__logo" src="img/hh-logo-2026-purple.png" alt="Hendy's Hunches football predictions logo">
            <div class="brand-stage__copy">
              <div class="countdown-card" aria-label="Countdown to World Cup 2026 opening day">
                <span class="countdown-card__value" id="countdownDays">--</span>
                <span class="countdown-card__label">
                  <span>Days until</span>
                  <span>11 June 2026</span>
                </span>
              </div>
              <div class="host-flags" aria-label="World Cup 2026 host nations">
                <span><img src="img/flags/ca.svg" alt=""> Canada</span>
                <span><img src="img/flags/mx.svg" alt=""> Mexico</span>
                <span><img src="img/flags/us.svg" alt=""> United States</span>
              </div>
            </div>
          </div>
        </section>

        <div class="login-panel-wrap">
          <img class="login-panel__mascot" src="img/james-scotland-ed-lg.png" alt="">
          <section class="login-panel" aria-label="Login">
            <p class="login-panel__eyebrow"><i class="bi bi-trophy-fill"></i> Matchday sign in</p>

            <form id="loginForm" class="needs-validation" method="POST" action="php/login.php" novalidate>
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autocomplete="username" autofocus>
                <div class="invalid-feedback">Please provide your username.</div>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="password-control">
                  <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                  <button class="btn btn-outline-dark" type="button" id="toggleLoginPwd" aria-label="Show password">
                    <i class="bi bi-eye-slash-fill"></i>
                  </button>
                </div>
                <div class="invalid-feedback">Please provide your password.</div>
              </div>

              <div class="login-actions">
                <button type="submit" class="btn btn-hh-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Log in</button>
                <a class="btn btn-hh-secondary w-100" href="registration.php"><i class="bi bi-person-fill-add"></i> Register</a>
              </div>

              <div class="login-meta">
                <a href="forgot-password.php">Forgot your password?</a>
              </div>
            </form>
          </section>
        </div>
      </main>

      <footer class="login-footer">
        <p class="mb-0">Predictions game based on <a href="<?= htmlspecialchars($competition_url, ENT_QUOTES) ?>"><?= htmlspecialchars($competition) ?></a><br><?= htmlspecialchars($title) ?> <?= htmlspecialchars($version) ?> &copy; <?= htmlspecialchars($year) ?> <?= htmlspecialchars($developer) ?>.</p>
      </footer>
    </div>

    <div class="modal fade" id="terms" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="termsTitle" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="termsTitle">Hendy's Hunches: Terms &amp; Conditions</h1>
          </div>
          <div class="modal-body">
            <img src="img/hh-logo-2026-purple.png" class="img-fluid d-block mx-auto mb-3" alt="Hendy's Hunches logo" style="max-width: 180px;">
            <p>By registering to play Hendy's Hunches, you acknowledge that:</p>
            <ul>
              <li>your involvement in this game, and the game itself, is intended only for entertainment</li>
              <li>the game is based on <?= htmlspecialchars($competition) ?></li>
              <li>only one registration per person is permitted although family and friends are welcome to participate</li>
              <li>an entry fee of £<?= htmlspecialchars($signup_fee_formatted) ?> is to be paid prior to <?= htmlspecialchars($signup_close_date) ?>; split for charity donation and prize funds</li>
              <li>an unpaid entry fee results in removal from the game</li>
              <li>the number of prize funds and their amounts are revealed in due course, then awarded after the final tournament fixture</li>
            </ul>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-hh-primary" data-bs-dismiss="modal">Understood</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      const toggleLoginPwd = document.querySelector('#toggleLoginPwd');
      const loginPassword = document.querySelector('#password');
      const countdownDays = document.querySelector('#countdownDays');

      if (countdownDays) {
        const worldCupStart = new Date('2026-06-11T00:00:00');
        const now = new Date();
        const msPerDay = 1000 * 60 * 60 * 24;
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const target = new Date(worldCupStart.getFullYear(), worldCupStart.getMonth(), worldCupStart.getDate());
        const daysRemaining = Math.max(0, Math.ceil((target - today) / msPerDay));
        countdownDays.textContent = String(daysRemaining);
      }

      if (toggleLoginPwd && loginPassword) {
        toggleLoginPwd.addEventListener('click', () => {
          const type = loginPassword.getAttribute('type') === 'password' ? 'text' : 'password';
          loginPassword.setAttribute('type', type);
          const icon = toggleLoginPwd.querySelector('i');
          if (icon) {
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash-fill');
          }
        });
      }
    </script>
  </body>
</html>
