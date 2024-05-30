<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
		<title>Hendy's Hunches: Registration</title>
    <?php include "php/config.php" ?>
    <?php include "php/process.php" ?>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://getbootstrap.com/docs/5.2/assets/css/docs.css" rel="stylesheet">
    <link href="css/registration.css" rel="stylesheet">
    <link href="css/multi-step-form.css" rel="stylesheet">
    <script src="js/multi-step-form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script type="text/javascript">
      $(document).ready(function(){
        $('#username').keyup(username_check);
  		  $('#username').addClass("incorrect");
      });
      function username_check(){
      	var username = $('#username').val();
      	if(username == "" || username.length < 4) {
    			$('#username').removeClass("correct");
        	$('#username').addClass("incorrect");
    			$('#username').css('border', '1px #CCC solid');
    			$('#un-msg').html("");
      		//$('#tick').hide();
  			}
  			else {
  				jQuery.ajax({
  				   type: "POST",
  				   url: "php/username-check.php",
  				   data: 'username='+ username,
  				   cache: false,
  				   success: function(response){
  					if(response == 1) {
  						$('#username').css('border', '1px #C33 solid');
  						$('#username').removeClass("correct");
  						$('#username').addClass("incorrect");
  						$('#un-msg').html("Sorry but this username is already taken.");
  					}
  					else {
  						$('#username').css('border', '1px #090 solid');
  						$('#username').removeClass("incorrect");
  						$('#username').addClass("correct");
  						$('#un-msg').html("");
  					}
  			}
      	});
      	}
      }
      function validateAvatar() {
    		// Validate the avatar selection
    		var avs = document.getElementById("avatar");
    		if (avs.value == null || avs.value == "") {
    			$('#avatarMsg').html("<p>Please select a football kit avatar.</p>")
    			return false;
        }
    	}
      function resetAll() {
        $('#registrationForm').removeClass('needs-validation');
        $('#registrationForm').addClass('needs-validation');
        $('.invalid-feedback').css('display', 'none');
      }
      </script>
  </head>


  <body class="d-flex h-100 text-center text-bg-dark">

		<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
  		<header class="mb-auto">
  			<div>
  				<h3 class="float-md-start mb-0">Hendy's Hunches</h3>
  				<nav class="nav nav-masthead justify-content-center float-md-end">
  					<a class="nav-link fw-bold py-1 px-0" href="index.php">Login</a>
  					<a class="nav-link fw-bold py-1 px-0" href="forgot-password.php">Reset Password</a>
            <a class="nav-link fw-bold py-1 px-0" href="#" data-bs-toggle="modal" data-bs-target="#terms">Terms</a>
  				</nav>
  			</div>
  		</header>

		<main class="px-3">
			<h1>Registration</h1>

      <!-- Progress bar -->
      <div class="progressbar">
        <div class="progress" id="progress"></div>
        <div class="progress-step progress-step-active" data-title="Contact"></div>
        <div class="progress-step" data-title="Account"></div>
        <div class="progress-step" data-title="Avatar"></div>
        <div class="progress-step" data-title="Details"></div>
        <div class="progress-step" data-title="Terms"></div>
      </div>
      <!--<p>Register your details below to sign up or return to the <a href="index.php">login page</a> to sign in. All fields are required to be completed.</p>-->
      <form class="d-flex flex-column needs-validation" method="post" action="php/register.php" id="registrationForm" name="registrationForm" novalidate> <!--  onsubmit="validateAvatar()" onSubmit="return validateFullForm()" border border-white p-2 my-2 border-opacity-25   -->
      <!-- Steps -->
      <div class="form-step form-step-active">
        <label for="firstname" class="form-label">First Name</label>
        <input type="text" class="form-control" id="firstname" name="firstname" required>
        <div class="invalid-feedback">
          Please provide your first name.
        </div>
        <label for="surname" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="surname" name="surname" required>
        <div class="invalid-feedback">
          Please provide your last name.
        </div>
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
        <div class="invalid-feedback">
          Please provide a valid email address.
        </div>        
          <div class="row">
            <hr>
            <div class="col-12 text-end">
              <button type="button" class="btn btn-primary btn-next w-50">Next</button>
            </div>
          </div>
      </div>
      <div class="form-step">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
        <span class="un-msg"></span>
        <div class="invalid-feedback">
          Please provide a username.
        </div>
        <label for="pwd1" class="form-label">Password <i class="bi bi-eye-slash-fill m-4" id="togglePwd1"></i></label>
        <input type="password" class="form-control" id="pwd1" name="pwd1" onBlur="return validatePassword();" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" onchange="form.pwd2.pattern = this.value;" />
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
        <label for="pwd2" class="form-label">Confirm Password <i class="bi bi-eye-slash-fill m-4" id="togglePwd2"></i></label> 
        <input type="password" class="form-control" id="pwd2" name="pwd2" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}">
        <div class="invalid-feedback">
          Passwords do not meet criteria or match.
        </div>
        <div class="row">
          <hr>
          <div class="col-6">
            <button type="button" class="btn btn-primary btn-prev w-100">Previous</button>
          </div>
          <div class="col-6 text-end">
            <button type="button" class="btn btn-primary btn-next w-100">Next</button>
          </div>
        </div>
    </div>      
    <div class="form-step">
      <div class="container text-center g-3">
          <label for="avatar" class="form-label">Choose Your Avatar</label>
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
          <button type="button" class="btn btn-primary btn-prev w-100">Previous</button>
        </div>
        <div class="col-6 text-end">
          <button type="button" class="btn btn-primary btn-next w-100">Next</button>
        </div>
      </div>
    </div>
    <div class="form-step">
      <span class="badge text-bg-light">Begin typing to search or use drop-down menus</span>
      <label for="fieldofwork" class="form-label">Field of Expertise</label>
      <input id="fieldofwork" name="fieldofwork" class="form-select" list="datalistOptions1" required>
      <div class="invalid-feedback">
        Please tell us your field of expertise.
      </div>
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
      <label for="location" class="form-label">Location (Nearest Town/City)</label>
      <input id="location" name="location" class="form-select" list="datalistOptions4" required>
      <div class="invalid-feedback">
        Please tell us your nearest city.
      </div>
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
      <label for="faveteam" class="form-label">Favourite Team</label>
      <input id="faveteam" name="faveteam" class="form-select" list="datalistOptions2" required>
      <div class="invalid-feedback">
        Please tell us your team.
      </div>
      <datalist id="datalistOptions2">
        <option value="None"></option>
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
      <label for="tournwinner" class="form-label">Who'll Win <?= $competition ?>?</label>
      <input id="tournwinner" name="tournwinner" class="form-select" list="datalistOptions3" required>
      <div class="invalid-feedback">
        Please tell us who'll win <?= $competition ?>.
      </div>
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
          <button type="button" class="btn btn-primary btn-prev w-100">Previous</button>
        </div>
        <div class="col-6 text-end">
          <button type="button" class="btn btn-primary btn-next w-100">Next</button>
        </div>
      </div>
    </div>

      <div class="form-step">                 
        <div class="row">
          <div class="col-auto d-flex align-items-center m-5">
            <img src="img/hh-logo-2018.jpg" class="img-fluid mt-auto" title="Hendy's Hunches Logo" alt="Hendy's Hunches Logo" style="width: 200px; margin-bottom: 10px;"> 
            <input class="form-check-input" type="checkbox" id="disclaimer" name="disclaimer" value="disclaimer" required>
            <label class="form-check-label m-3" for="disclaimer">
              I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#terms" class="text-white">terms and conditions</a> of Hendy's Hunches.
            </label>
          </div>
          <div class="invalid-feedback">
            You must agree before submitting.
          </div>
        </div>
        <div class="row">
          <hr>
          <div class="col-6">
            <button type="button" class="btn btn-primary btn-prev w-100">Previous</button>
          </div>
          <div class="col-6 text-end">
            <button type="submit" class="btn btn-success w-100">Sign up!</button><!-- <i class="fw-bold bi bi-hand-thumbs-up"></i> -->
          </div>
        </div>
      </div>
        <!-- <hr />
        <div class="col-12 d-flex justify-content-evenly" style="margin: 0px 0px 10px 0px;">
          <button class="btn btn-lg btn-primary" type="submit"><i class="fw-bold bi bi-hand-thumbs-up"></i> Sign me up!</button>
          <button class="btn btn-lg btn-outline-light" type="reset" onClick="resetAll();"><i class="fw-bold bi bi-x"></i> Reset all</button>
        </div> -->
      </form>

		</main>

    <!-- HH Terms Modal -->
    <div class="modal fade" id="terms" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="staticBackdropLabel">Hendy's Hunches: Terms &amp; Conditions</h1>
            <!--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
          </div>
          <div class="modal-body">
            <img src="img/hh-logo-2018.jpg" class="img-responsive mt-auto" title="Hendy's Hunches Logo" alt="Hendy's Hunches Logo" style="width: 180px; margin-bottom: 10px;">
            <p>By registering to play Hendy's Hunches, you acknowledge that:</p>
            <ul>
              <li>your involvement in this game, and the game itself, is intended only for entertainment; it is not a gambling site</li>
              <li>the game is based on <?=$competition?></li>
              <li>only one registration per person is permitted although family and friends are welcome to participate</li>
              <li>an entry fee of <?=$signup_fee?> is to be paid prior to <?=$signup_close_date?>; split for charity (TBC) donation and prize funds</li>
              <li>an unpaid entry fee results in removal from the game</li>
              <li>the number of prize funds, and their amounts, are revealed in due course, awarded to winners after the final tournament fixture and, in the event of a shared winning spot, divided accordingly.</li>
            </ul>
          </div>
          <div class="modal-footer">
            <!--<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>-->
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Understood</button>
          </div>
        </div>
      </div>
    </div>

    <footer class="mt-auto">
      <p class="small fw-light">Predictions game based on <a href="<?=$competition_url?>" class="text-white"><?=$competition?></a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
    </footer>

		</div>

    <script type="text/javascript">
    // Example starter JavaScript for disabling form submissions if there are invalid fields
      (() => {
      'use strict'
      // Fetch all the forms we want to apply custom Bootstrap validation styles to
      const forms = document.querySelectorAll('.needs-validation')
      // Loop over them and prevent submission
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
      })()

  		function chooseImage(imageId) {
  			var x = document.getElementById(imageId).value;
  			document.getElementById("avatar").value = x;
  		}

      const togglePwd1 = document.querySelector('#togglePwd1');
      const togglePwd2 = document.querySelector('#togglePwd2');
      const pwd1 = document.querySelector('#pwd1');
      const pwd2 = document.querySelector('#pwd2');

      togglePwd1.addEventListener('click', function (e) {
          // Toggle the type attribute
          const type = pwd1.getAttribute('type') === 'password' ? 'text' : 'password';
          pwd1.setAttribute('type', type);
          // Toggle the eye / eye slash icon
          this.classList.toggle('bi-eye');
      });

      togglePwd2.addEventListener('click', function (e) {
          // Toggle the type attribute
          const type = pwd2.getAttribute('type') === 'password' ? 'text' : 'password';
          pwd2.setAttribute('type', type);
          // Toggle the eye / eye slash icon
          this.classList.toggle('bi-eye');
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
      }
	</script>
  </body>
</html>
