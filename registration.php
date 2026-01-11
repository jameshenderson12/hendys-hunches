<?php
session_start();

// Include necessary files for configuration and database connection
include 'php/config.php';
include 'php/process.php';
//include 'php/send-welcome-email.php';

// Initialise variable for error messages
$registrationSuccess = false;

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $firstname = ucfirst($_POST['firstname']);
    $surname = ucfirst($_POST['surname']);
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = md5($_POST['pwd1']);
    $avatar = $_POST['avatar'];
    $fieldofwork = trim($_POST['fieldofwork'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $faveteam = trim($_POST['faveteam'] ?? '');
    $tournwinner = trim($_POST['tournwinner'] ?? '');

    $fieldofwork = $fieldofwork !== '' ? $fieldofwork : 'Prefer Not To Say';
    $location = $location !== '' ? $location : 'Prefer Not To Say';
    $faveteam = $faveteam !== '' ? $faveteam : 'Prefer Not To Say';
    $tournwinner = $tournwinner !== '' ? $tournwinner : 'Prefer Not To Say';

    // Include database connection
    include 'php/db-connect.php';

    // Query to get the total number of users to set positional values
    $sql1 = "SELECT count(*) AS totalusers FROM live_user_information";
    $totalusers = mysqli_query($con, $sql1) or die(mysqli_error($con));
    $row = mysqli_fetch_assoc($totalusers);
    $setdefstartpos = $row["totalusers"];
    $setdefcurrpos = $row["totalusers"] + 1;
    $setdeflastpos = $row["totalusers"] + 1;

    // Prepare and bind SQL statements
    $stmt1 = mysqli_prepare($con, "INSERT INTO live_user_information (username, password, firstname, surname, email, avatar, fieldofwork, location, faveteam, tournwinner, startpos, lastpos, currpos) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt2 = mysqli_prepare($con, "INSERT INTO live_temp_information (username) VALUES (?)");

    mysqli_stmt_bind_param($stmt1, "ssssssssssddd", $username, $password, $firstname, $surname, $email, $avatar, $fieldofwork, $location, $faveteam, $tournwinner, $setdefstartpos, $setdeflastpos, $setdefcurrpos);
    mysqli_stmt_bind_param($stmt2, "s", $username);

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
      // Set the URL for password change
      //$changePasswordUrl = 'https://www.hendyshunches.co.uk/change-password.php'; // Replace with actual URL
      sendWelcomeEmail($firstname, $username, $email);
  }
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
</head>

  <body class="d-flex h-100 text-center text-bg-dark">

		<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
  		<header class="mb-auto">
  			<div>
  				<h3 class="float-md-start mb-0">Hendy's Hunches</h3>
  				<nav class="nav nav-masthead justify-content-center float-md-end">
  					<a class="nav-link fw-bold py-1 px-0" href="index.php">Login</a>
  					<a class="nav-link fw-bold py-1 px-0" href="forgot-password.php">Reset Password</a>
            <a class="nav-link fw-bold py-1 px-0" href="#terms-panel">Terms</a>
  				</nav>
  			</div>
  		</header>

		<main class="px-3 my-auto">
      <?php if ($registrationSuccess): ?>
          <h1>Registration</h1>
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

  			<h1>Registration</h1>
        <div class="text-start small text-white-50 mt-2" id="stepLabel">Step 1 of 5: Contact</div>

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
            <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
            <span class="un-msg"></span>
            <div class="invalid-feedback">
              Please provide a username.
            </div>
            <label for="pwd1" class="form-label">Password <span class="text-warning">*</span></label>
            <div class="input-group">
              <input type="password" class="form-control" id="pwd1" name="pwd1" onBlur="return validatePassword();" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" onchange="form.pwd2.pattern = this.value;" autocomplete="new-password" />
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
          <label for="tournwinner" class="form-label">Who'll Win <?= $competition ?>? <span class="text-white-50">(Optional)</span></label>
          <input id="tournwinner" name="tournwinner" class="form-select" list="datalistOptions3" placeholder="Start typing to filter">
          <datalist id="datalistOptions3">
          <option value="Prefer Not To Say"></option>        
            <?php
              $file = 'text/select-countryteams-input.txt';
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
            <div class="terms-panel text-start" id="terms-panel">
              <h5 class="text-center">Hendy's Hunches: Terms &amp; Conditions</h5>
              <p>By registering to play Hendy's Hunches, you acknowledge that:</p>
              <ul>
                <li>your involvement in this game, and the game itself, is intended only for entertainment; it is not a gambling site</li>
                <li>the game is based on <?=$competition?></li>
                <li>only one registration per person is permitted although family and friends are welcome to participate</li>
                <li>an entry fee of £<?=$signup_fee_formatted?> is to be paid prior to <?=$signup_close_date?>; split for charity donation and prize funds</li>
                <li>an unpaid entry fee results in removal from the game</li>
                <li>the number of prize funds, and their amounts, are revealed in due course, awarded to winners after the final tournament fixture and, in the event of a shared winning spot, divided accordingly.</li>
              </ul>
            </div>
              <div class="col-auto d-flex align-items-center my-4 mx-auto">                        
                <input class="form-check-input" type="checkbox" id="disclaimer" name="disclaimer" value="disclaimer" required>
                <label class="form-check-label m-3" for="disclaimer">
                  I agree to the terms and conditions of Hendy's Hunches.
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
              const feedback = findInvalidFeedback(input);
              if (input.checkValidity()) {
                input.classList.remove('is-invalid');
                if (feedback) {
                  feedback.classList.remove('d-block');
                }
              } else {
                input.classList.add('is-invalid');
                if (feedback) {
                  feedback.classList.add('d-block');
                }
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
/*
      document.addEventListener('DOMContentLoaded', function () {
        const usernameInput = document.getElementById('username');
        const feedbackElement = document.querySelector('#username + .invalid-feedback');

        usernameInput.addEventListener('keyup', function () {
          const username = usernameInput.value;

          if (username.length >= 3) {
            // Perform AJAX call to check username availability
            fetch('php/username-check.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: `username=${encodeURIComponent(username)}`
            })
            .then(response => response.text())
            .then(data => {
              if (data === '1') {
                // Username exists
                usernameInput.classList.add('is-invalid');
                feedbackElement.classList.add('d-block');
                feedbackElement.textContent = 'Username is already taken.';
              } else {
                // Username is available
                usernameInput.classList.remove('is-invalid');
                feedbackElement.classList.remove('d-block');
              }
            })
            .catch(error => {
              console.error('Error:', error);
            });
          } else {
            // Hide invalid feedback if less than 3 characters
            usernameInput.classList.remove('is-invalid');
            feedbackElement.classList.remove('d-block');
          }
        });
      });
      */
	</script>

  <!-- Footer -->
  <?php include "php/footer.php" ?>
