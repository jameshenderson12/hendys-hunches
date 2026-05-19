<?php
session_start();
$page_title = 'Change Password';

require_once __DIR__ . '/php/auth.php';
hh_require_login('index.php');

include 'php/header.php';
include 'php/navigation.php';
?>

<main id="main" class="main">
  <div class="page-hero page-hero--account">
    <div>
      <p class="eyebrow">Account</p>
      <h1>Change Password</h1>
      <p class="lead mb-0">Update your password with the same clear guidance used during signup.</p>
    </div>
    <div class="page-hero__actions">
      <a class="btn btn-outline-dark" href="dashboard.php"><i class="bi bi-arrow-left-circle"></i> Back to dashboard</a>
    </div>
  </div>

  <section class="section account-page">
    <div class="content-panel content-panel--narrow account-password-card">
      <p class="account-password-card__intro">Choose a new password, confirm it once, and we’ll update your account straight away.</p>

      <form id="changePassForm" name="changePassForm" class="account-password-form" onsubmit="return false;" novalidate>
        <div class="account-password-field">
          <label for="password" class="form-label">New Password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" autocomplete="new-password">
            <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Show password">
              <i class="bi bi-eye-slash-fill"></i>
            </button>
          </div>
          <div class="mt-2">
            <div class="progress" role="progressbar" aria-label="Password strength">
              <div class="progress-bar" id="passwordStrengthBar" style="width: 0%"></div>
            </div>
            <div class="small text-muted mt-1" id="passwordStrengthText">Strength: Not set</div>
          </div>
          <div class="invalid-feedback">
            Password must contain at least 6 characters, including uppercase, lowercase, and a number.
          </div>
          <div class="account-password-rules small" id="pwdMsg">
            <ul class="mb-0">
              <li id="length" class="invalid">Minimum <b>6 characters</b></li>
              <li id="letter" class="invalid">1 <b>uppercase</b> and 1 <b>lowercase</b> letter</li>
              <li id="number" class="invalid">1 <b>number</b></li>
            </ul>
          </div>
        </div>

        <div class="account-password-field">
          <label for="password2" class="form-label">Confirm New Password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password2" name="password2" required autocomplete="new-password">
            <button class="btn btn-outline-secondary" type="button" id="togglePassword2" aria-label="Show password confirmation">
              <i class="bi bi-eye-slash-fill"></i>
            </button>
          </div>
          <div class="invalid-feedback">
            Please make sure the two passwords match.
          </div>
        </div>

        <p id="status" class="account-password-status" aria-live="polite"></p>

        <div class="account-password-actions">
          <button type="button" id="changepassbtn" class="btn btn-primary" onclick="changePass()">
            <i class="bi bi-floppy-fill"></i> Change password
          </button>
        </div>
      </form>
    </div>
  </section>
</main>

<style>
  .account-password-card {
    padding: 24px;
  }

  .account-password-card__intro {
    color: var(--hh-muted);
    margin-bottom: 18px;
  }

  .account-password-form {
    display: grid;
    gap: 18px;
  }

  .account-password-field .form-label {
    color: var(--hh-ink);
    font-weight: 800;
  }

  .account-password-field .form-control {
    border: 1px solid rgba(22, 35, 29, 0.22);
    border-radius: 8px;
    min-height: 46px;
    padding-right: 44px;
  }

  .account-password-field .form-control:focus {
    border-color: var(--hh-purple);
    box-shadow: 0 0 0 0.2rem rgba(143, 102, 216, 0.22);
  }

  .account-password-field .form-control.is-valid {
    border-color: #1f6b3f;
    box-shadow: 0 0 0 0.18rem rgba(31, 107, 63, 0.18);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Ccircle fill='%231f6b3f' cx='8' cy='8' r='8'/%3E%3Cpath fill='white' d='M6.6 10.7 4.3 8.4l-.9.9 3.2 3.2 5.9-5.9-.9-.9z'/%3E%3C/svg%3E");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 18px 18px;
  }

  .account-password-field .form-control.is-invalid {
    border-color: #8f1d24;
    box-shadow: 0 0 0 0.18rem rgba(143, 29, 36, 0.18);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Ccircle fill='%238f1d24' cx='8' cy='8' r='8'/%3E%3Cpath fill='white' d='M5 5.9 5.9 5 8 7.1 10.1 5l.9.9L8.9 8l2.1 2.1-.9.9L8 8.9 5.9 11l-.9-.9L7.1 8z'/%3E%3C/svg%3E");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 18px 18px;
  }

  .account-password-field .input-group .form-control {
    padding-right: 16px;
  }

  .account-password-field .input-group .form-control.is-valid,
  .account-password-field .input-group .form-control.is-invalid {
    background-image: none;
  }

  .account-password-field .input-group.is-valid,
  .account-password-field .input-group.is-invalid {
    position: relative;
  }

  .account-password-field .input-group.is-valid::after,
  .account-password-field .input-group.is-invalid::after {
    align-items: center;
    border-radius: 999px;
    color: #fff;
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

  .account-password-field .input-group.is-valid::after {
    background: #1f6b3f;
    content: "✓";
  }

  .account-password-field .input-group.is-invalid::after {
    background: #8f1d24;
    content: "×";
  }

  .account-password-field .invalid-feedback {
    color: #8f1d24;
    display: none;
    font-size: 0.82rem;
    font-weight: 700;
    margin-top: 8px;
  }

  .account-password-field .invalid-feedback.d-block {
    display: block;
  }

  .account-password-rules {
    color: #55615a;
    margin-top: 10px;
  }

  .account-password-rules ul {
    list-style: none;
    padding-left: 0;
  }

  .account-password-rules li + li {
    margin-top: 4px;
  }

  .account-password-rules .valid {
    color: #1f6b3f;
  }

  .account-password-rules .invalid {
    color: #8f1d24;
  }

  .account-password-status {
    color: #55615a;
    font-weight: 700;
    margin: 0;
    min-height: 24px;
  }

  .account-password-status.is-success {
    color: #1f6b3f;
  }

  .account-password-status.is-error {
    color: #8f1d24;
  }

  .account-password-actions {
    display: flex;
    justify-content: flex-start;
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
    return x.readyState == 4 && x.status == 200;
  }

  const password = document.querySelector('#password');
  const password2 = document.querySelector('#password2');
  const togglePassword = document.querySelector('#togglePassword');
  const togglePassword2 = document.querySelector('#togglePassword2');
  const togglePasswordIcon = togglePassword?.querySelector('i');
  const togglePassword2Icon = togglePassword2?.querySelector('i');
  const passwordStrengthBar = document.querySelector('#passwordStrengthBar');
  const passwordStrengthText = document.querySelector('#passwordStrengthText');
  const status = document.querySelector('#status');
  const form = document.querySelector('#changePassForm');
  const myInput = document.getElementById('password');
  const letter = document.getElementById('letter');
  const number = document.getElementById('number');
  const length = document.getElementById('length');

  const findInvalidFeedback = (element) => {
    if (!element) return null;

    if (element.parentElement?.classList.contains('input-group')) {
      let sibling = element.parentElement.nextElementSibling;
      while (sibling) {
        if (sibling.classList?.contains('invalid-feedback')) {
          return sibling;
        }
        sibling = sibling.nextElementSibling;
      }
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

  const updateGroupState = (input, stateClass) => {
    const group = input?.parentElement?.classList.contains('input-group') ? input.parentElement : null;
    if (!group) return;
    group.classList.remove('is-valid', 'is-invalid');
    if (stateClass) {
      group.classList.add(stateClass);
    }
  };

  const syncFieldState = (input, showValidation = false) => {
    if (!input) return true;

    const feedback = findInvalidFeedback(input);
    const isValid = input.checkValidity();
    input.classList.remove('is-valid', 'is-invalid');
    updateGroupState(input, '');

    if (isValid && input.value.trim() !== '') {
      input.classList.add('is-valid');
      updateGroupState(input, 'is-valid');
      feedback?.classList.remove('d-block');
    } else if (showValidation) {
      input.classList.add('is-invalid');
      updateGroupState(input, 'is-invalid');
      feedback?.classList.add('d-block');
    } else {
      feedback?.classList.remove('d-block');
    }

    return isValid;
  };

  const checkMatch = () => {
    if (password2.value && password.value !== password2.value) {
      password2.setCustomValidity('Passwords must match.');
    } else {
      password2.setCustomValidity('');
    }
    syncFieldState(password, password.classList.contains('hh-touched'));
    syncFieldState(password2, password2.classList.contains('hh-touched'));
  };

  [password, password2].forEach((input) => {
    input?.addEventListener('input', () => {
      if (input === password || input === password2) {
        checkMatch();
      }
    });
    input?.addEventListener('blur', () => {
      input.classList.add('hh-touched');
      if (input === password || input === password2) {
        checkMatch();
      }
      syncFieldState(input, true);
    });
  });

  togglePassword?.addEventListener('click', () => {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    togglePasswordIcon?.classList.toggle('bi-eye');
    togglePasswordIcon?.classList.toggle('bi-eye-slash-fill');
  });

  togglePassword2?.addEventListener('click', () => {
    const type = password2.getAttribute('type') === 'password' ? 'text' : 'password';
    password2.setAttribute('type', type);
    togglePassword2Icon?.classList.toggle('bi-eye');
    togglePassword2Icon?.classList.toggle('bi-eye-slash-fill');
  });

  myInput.onfocus = function() {
    document.getElementById('pwdMsg').style.display = 'block';
  };

  myInput.onblur = function() {
    document.getElementById('pwdMsg').style.display = 'none';
  };

  myInput.onkeyup = function() {
    var lowerCaseLetters = /[a-z]/g;
    var upperCaseLetters = /[A-Z]/g;
    if ((myInput.value.match(lowerCaseLetters) && myInput.value.match(upperCaseLetters))) {
      letter.classList.remove('invalid');
      letter.classList.add('valid');
    } else {
      letter.classList.remove('valid');
      letter.classList.add('invalid');
    }

    var numbers = /[0-9]/g;
    if (myInput.value.match(numbers)) {
      number.classList.remove('invalid');
      number.classList.add('valid');
    } else {
      number.classList.remove('valid');
      number.classList.add('invalid');
    }

    if (myInput.value.length >= 6) {
      length.classList.remove('invalid');
      length.classList.add('valid');
    } else {
      length.classList.remove('valid');
      length.classList.add('invalid');
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
  };

  function setStatus(message, className = '') {
    status.textContent = message;
    status.className = 'account-password-status' + (className ? ' ' + className : '');
  }

  function changePass() {
    if (!syncFieldState(password, true) || !syncFieldState(password2, true) || !form.checkValidity()) {
      setStatus('Please fix the highlighted password fields before continuing.', 'is-error');
      return;
    }

    const pwd = _('password').value;
    setStatus('Please wait while your password is updated…');

    var ajax = ajaxObj('POST', 'php/change-pwd.php');
    ajax.onreadystatechange = function() {
      if (ajaxReturn(ajax)) {
        var response = ajax.responseText.trim();
        if (response === 'success') {
          _('changePassForm').innerHTML = '<h3 class="mb-0 text-success">Password successfully changed.</h3><p class="mt-3 mb-0">Your account is updated and ready to use.</p>';
        } else if (response === 'no_exist') {
          setStatus('We could not find your account for this password change.', 'is-error');
        } else if (response === 'email_send_failed') {
          setStatus('Your password changed, but the confirmation email could not be sent.', 'is-error');
        } else {
          setStatus('An unknown error occurred. Please try again.', 'is-error');
        }
      }
    };
    ajax.send('pwd=' + encodeURIComponent(pwd));
  }
</script>

<?php include "php/footer.php" ?>
