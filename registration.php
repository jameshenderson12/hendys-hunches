<!DOCTYPE html>
<html lang="en" class="h-100">
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link href="https://getbootstrap.com/docs/5.2/assets/css/docs.css" rel="stylesheet">
    <link href="css/registration.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
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
    		var avs = document.getElementById("avatarSelection");
    		var avt = document.getElementById("avatars");
    		if (avs.value == null || avs.value == "") {
    			alert("Please select a football kit avatar.");
    			avt.style.border="1px solid #C33";
    			return false;
        }
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
      <!--<p>Register your details below to sign up or return to the <a href="index.php">login page</a> to sign in. All fields are required to be completed.</p>-->
      <form class="row g-3 needs-validation" method="post" action="php/register.php" id="registrationForm" name="registrationForm" novalidate> <!-- onSubmit="return validateFullForm()" border border-white p-2 my-2 border-opacity-25   -->
        <div class="col-md-6">
          <label for="firstname" class="form-label">First name</label>
          <input type="text" class="form-control" id="firstname" required>
          <!--
          <div class="valid-feedback">
            Looks good!
          </div>-->
          <div class="invalid-feedback">
            Please provide your first name.
          </div>
        </div>
        <div class="col-md-6">
          <label for="surname" class="form-label">Last name</label>
          <input type="text" class="form-control" id="surname" required>
          <div class="invalid-feedback">
            Please provide your last name.
          </div>
        </div>
        <div class="col-md-6">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" required>
          <div class="invalid-feedback">
            Please provide a valid email address.
          </div>
        </div>
        <div class="col-md-6">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" required>
          <span class="un-msg"></span>
          <div class="invalid-feedback">
            Please provide a username.
          </div>
        </div>
        <div class="col-md-6">
          <label for="pwd1" class="form-label">Password</label> <i class="bi bi-eye-slash-fill" id="togglePwd1"></i>
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
        </div>
        <div class="col-md-6">
          <label for="pwd2" class="form-label">Confirm Password</label> <i class="bi bi-eye-slash-fill" id="togglePwd2"></i>
          <input type="password" class="form-control" id="pwd2" name="pwd2" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}">
          <div class="invalid-feedback">
            Passwords do not meet criteria or match.
          </div>
        </div>

        <div class="container text-center g-3">
          <label for="avatar" class="form-label">Select your avatar strip</label>
          <div class="invalid-feedback">
            Please select a kit avatar.
          </div>
          <div class="row row-cols-4 row-cols-sm-6 g-1">
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk1" name="fkradio" value="<?php echo $fk1; ?>" onClick="chooseImage('fk1');">
              <label class="btn btn-outline-light" for="fk1"><img src="<?php echo $fk1; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk2" name="fkradio" value="<?php echo $fk2; ?>" onClick="chooseImage('fk2');">
              <label class="btn btn-outline-light" for="fk2"><img src="<?php echo $fk2; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk3" name="fkradio" value="<?php echo $fk3; ?>" onClick="chooseImage('fk3');">
              <label class="btn btn-outline-light" for="fk3"><img src="<?php echo $fk3; ?>" alt="Football kit description..."class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk4" name="fkradio" value="<?php echo $fk4; ?>" onClick="chooseImage('fk4');">
              <label class="btn btn-outline-light" for="fk4"><img src="<?php echo $fk4; ?>" alt="Football kit description..."class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk5" name="fkradio" value="<?php echo $fk5; ?>" onClick="chooseImage('fk5');">
              <label class="btn btn-outline-light" for="fk5"><img src="<?php echo $fk5; ?>" alt="Football kit description..."class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk6" name="fkradio" value="<?php echo $fk6; ?>" onClick="chooseImage('fk6');">
              <label class="btn btn-outline-light" for="fk6"><img src="<?php echo $fk6; ?>" alt="Football kit description..."class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk7" name="fkradio" value="<?php echo $fk7; ?>" onClick="chooseImage('fk7');">
              <label class="btn btn-outline-light" for="fk7"><img src="<?php echo $fk7; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk8" name="fkradio" value="<?php echo $fk8; ?>" onClick="chooseImage('fk8');">
              <label class="btn btn-outline-light" for="fk8"><img src="<?php echo $fk8; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk9" name="fkradio" value="<?php echo $fk9; ?>" onClick="chooseImage('fk9');">
              <label class="btn btn-outline-light" for="fk9"><img src="<?php echo $fk9; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk10" name="fkradio" value="<?php echo $fk10; ?>" onClick="chooseImage('fk10');">
              <label class="btn btn-outline-light" for="fk10"><img src="<?php echo $fk10; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk11" name="fkradio" value="<?php echo $fk11; ?>" onClick="chooseImage('fk11');">
              <label class="btn btn-outline-light" for="fk11"><img src="<?php echo $fk11; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk12" name="fkradio" value="<?php echo $fk12; ?>" onClick="chooseImage('fk12');">
              <label class="btn btn-outline-light" for="fk12"><img src="<?php echo $fk12; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk13" name="fkradio" value="<?php echo $fk13; ?>" onClick="chooseImage('fk13');">
              <label class="btn btn-outline-light" for="fk13"><img src="<?php echo $fk13; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk14" name="fkradio" value="<?php echo $fk14; ?>" onClick="chooseImage('fk14');">
              <label class="btn btn-outline-light" for="fk14"><img src="<?php echo $fk14; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk15" name="fkradio" value="<?php echo $fk15; ?>" onClick="chooseImage('fk15');">
              <label class="btn btn-outline-light" for="fk15"><img src="<?php echo $fk15; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk16" name="fkradio" value="<?php echo $fk16; ?>" onClick="chooseImage('fk16');">
              <label class="btn btn-outline-light" for="fk16"><img src="<?php echo $fk16; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk17" name="fkradio" value="<?php echo $fk17; ?>" onClick="chooseImage('fk17');">
              <label class="btn btn-outline-light" for="fk17"><img src="<?php echo $fk17; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <div class="col">
              <input type="radio" class="btn-check" autocomplete="off" id="fk18" name="fkradio" value="<?php echo $fk18; ?>" onClick="chooseImage('fk18');">
              <label class="btn btn-outline-light" for="fk18"><img src="<?php echo $fk18; ?>" alt="Football kit description..." class="w-100" /></label>
            </div>
            <!--
            <div class="valid-feedback">
              Love the kit choice!
            </div>-->
          </div>
        </div>
        <input type="text" class="form-control" id="avatarSelection" name="avatarSelection" readonly hidden>

        <div class="col-md-6">
          <label for="fieldofwork" class="form-label">Field of work</label>
          <input id="fieldofwork" name="fieldofwork" class="form-select" onBlur="return validateDropDown('fieldofwork');" list="datalistOptions1" placeholder="Type to search..." required>
          <div class="invalid-feedback">
            Please provide a field of work.
          </div>
          <datalist id="datalistOptions1">
            <option selected disabled></option>
              <?php
                // Source file for extracting data
                $file = 'text/select-sectors-input.txt';
                $handle = @fopen($file, 'r');
                if ($handle) {
                 while (!feof($handle)) {
                   $line = fgets($handle, 4096);
                   $item = explode('\n', $line);
                   echo '<option value="' . $item[0] . '">' . $item[0] . '</option>' . "\n";
                   }
                fclose($handle);
                }
              ?>
          </datalist>
        </div>

        <div class="col-md-6">
          <label for="faveteam" class="form-label">Favourite team</label>
          <input id="faveteam" name="faveteam" class="form-select" onBlur="return validateDropDown('faveteam');" list="datalistOptions2" placeholder="Type to search..." required>
          <datalist id="datalistOptions2">
            <option selected disabled></option>
              <?php
                // Source file for extracting data
                $file = 'text/select-clubteams-input.txt';
                $handle = @fopen($file, 'r');
                if ($handle) {
                 while (!feof($handle)) {
                   $line = fgets($handle, 4096);
                   $item = explode('\n', $line);
                   echo '<option value="' . $item[0] . '">' . $item[0] . '</option>' . "\n";
                   }
                fclose($handle);
                }
              ?>
          </datalist>
        </div>

        <div class="col-md-6">
          <label for="tournwinner" class="form-label">Predicted winner</label>
          <input id="tournwinner" name="tournwinner" class="form-select" onBlur="return validateDropDown('tournwinner');" list="datalistOptions3" placeholder="Type to search..." required>
          <datalist id="datalistOptions3">
            <option selected disabled></option>
              <?php
                // Source file for extracting data
                $file = 'text/select-wc2018teams-input.txt';
                $handle = @fopen($file, 'r');
                if ($handle) {
                 while (!feof($handle)) {
                   $line = fgets($handle, 4096);
                   $item = explode('\n', $line);
                   echo '<option value="' . $item[0] . '">' . $item[0] . '</option>' . "\n";
                   }
                fclose($handle);
                }
              ?>
          </datalist>
        </div>

        <div class="col-md-6">
          <label for="validationCustom03" class="form-label">City</label>
          <input type="text" class="form-control" id="validationCustom03" required>
          <div class="invalid-feedback">
            Please provide a valid city.
          </div>
        </div>

        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="disclaimer" name="disclaimer" value="disclaimer" required>
            <label class="form-check-label" for="disclaimer">
              I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#terms" class="text-white">terms and conditions</a> of Hendy's Hunches.
            </label>
            <div class="invalid-feedback">
              You must agree before submitting.
            </div>
          </div>
        </div>
        <hr />
        <div class="col-12 d-flex justify-content-evenly" style="margin: 0px 0px 10px 0px;">
          <button class="btn btn-lg btn-primary fw-bold" type="submit"><i class="fw-bold bi bi-hand-thumbs-up"></i> Sign me up!</button>
          <button class="btn btn-lg btn-outline-light" type="reset" onClick="resetBorders();"><i class="fw-bold bi bi-x"></i> Reset all</button>
        </div>
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
              <li>the game is based on FIFA World Cup Qatar 2022™</li>
              <li>only one registration per person is permitted although family and friends are welcome to participate</li>
              <li>an entry fee of £5 is to be paid prior to 20/11/2022; split for charity (TBC) donation and prize funds</li>
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
      <p class="small fw-light">Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022" class="text-white">FIFA World Cup Qatar 2022™</a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
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
  			document.getElementById("avatarSelection").value = x;
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
