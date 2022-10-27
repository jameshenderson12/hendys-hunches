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
      });

    	function flagIncorrect() {
    		$(this).addClass("incorrect");
    	}
    	function flagCorrect() {
    		$(this).addClass("correct");
    	}
    	function validateFullForm() {
    		// Validate the username input
    		var un = document.forms["registrationForm"]["username"];
    		var unmsg = document.getElementById("un-msg");
    		if ((un.value == null) || (un.value == "") || ($('#un-msg').html() != "") ) {
    			alert("Please enter a unique username.");
    			un.style.border="1px solid #C33";
    			un.focus();
    			return false;
    		}
    		// Validate the password input
    		var pwd1 = document.forms["registrationForm"]["password"];
    		var pwd2 = document.forms["registrationForm"]["password2"];
    		if(pwd1.value != "" && pwd1.value == pwd2.value) {
    		  if(!checkPassword(pwd1.value)) {
    			alert("The password you have entered is not valid.");
    			pwd1.focus();
    			return false;
    		  }
    		} else {
    		  alert("Please check that you've entered and confirmed your password correctly.");
    		  pwd1.focus();
    		  return false;
    		}
    		/*
    		// Validate the first name input
    		var fn = document.forms["registrationForm"]["firstname"];
    		if (fn.value == null || fn.value == "") {
    			alert("Please enter your first name.");
    			fn.style.border="1px solid #C33";
    			fn.focus();
    			return false;
    		}
    		// Validate the surname input
    		var sn = document.forms["registrationForm"]["surname"];
    		if (sn.value == null || sn.value == "") {
    			alert("Please enter your surname.");
    		    sn.style.border="1px solid #C33";
    			sn.focus();
    			return false;
    		}
    		// Validate the email input
    		var x = document.forms["registrationForm"]["email"].value;
    		var y = document.forms["registrationForm"]["email"];
    		var atpos = x.indexOf("@");
    		var dotpos = x.lastIndexOf(".");
    		if (atpos < 1 || dotpos < atpos + 2 || dotpos + 2 >= x.length) {
    			alert("Please enter a valid email address.");
    		    y.style.border="1px solid #C33";
    			y.focus();
    			return false;
    		}
    		// Validate the avatar selection
    		var avs = document.getElementById("avatarSelection");
    		var avt = document.getElementById("avatars");
    		if (avs.value == null || avs.value == "") {
    			alert("Please select a football kit avatar.");
    			avt.style.border="1px solid #C33";
    			return false;
    		}
    		// Validate the field of work input
    		var xd1 = document.getElementById("fieldofwork");
    		var yd1 = document.getElementById("fieldofwork").options;
    			if (yd1[xd1.selectedIndex].index == "0") {
    				alert("Please specify your field of work.");
    				xd1.style.border="1px solid #C33";
    				xd1.focus();
    				return false;
    			}
    		// Validate the favourite team input
    		var xd2 = document.getElementById("faveteam");
    		var yd2 = document.getElementById("faveteam").options;
    			if (yd2[xd2.selectedIndex].index == "0") {
    				alert("Please choose your favourite team.");
    				xd2.style.border="1px solid #C33";
    				xd2.focus();
    				return false;
    			}
    		// Validate the world cup winner input
    		var xd3 = document.getElementById("tournwinner");
    		var yd3 = document.getElementById("tournwinner").options;
    			if (yd3[xd3.selectedIndex].index == "0") {
    				alert("Please specify who you think will win the tournament.");
    				xd3.style.border="1px solid #C33";
    				xd3.focus();
    				return false;
    			}
    		// Validate the disclaimer checkbox input
    		var dc = document.forms["registrationForm"]["disclaimer"];
    		if (!dc.checked) {
    			alert("You must agree to the terms and conditions.");
    			dc.focus();
    			return false;
    		}
    	}
    	// Turn the name fields red if not input (onBlur - focus leaving the field)
    	function validateName(nameID) {
    		var x = document.getElementById(nameID);
    		if (x.value == null || x.value == "") {
    			x.style.border="1px solid #C33";
    			return false;
    		}
    		else x.style.border="1px solid #090";
    	}
    	function checkPassword(str) {
    	    var re = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/;
        	return re.test(str);
      	}
    	// Turn the password field red if not input correct (onBlur - focus leaving the field)
    	function validatePassword() {
    		var x = document.getElementById("password");
    		if ((x.value == null) || (x.value == "") || (!checkPassword(x.value))) {
    			x.style.border="1px solid #C33";
    			return false;
    		}
    		else x.style.border="1px solid #090";
    	}
    	// Turn the confirm password field red if not input correct (onBlur - focus leaving the field)
    	function validatePassword2() {
    		var x = document.getElementById("password2");
    		var y = document.getElementById("password");
    		if ((x.value == null) || (x.value == "") || (!checkPassword(x.value)) || (x.value != y.value)) {
    			x.style.border="1px solid #C33";
    			return false;
    		}
    		else x.style.border="1px solid #090";
    	}
    	// Turn the email field red if not input correct (onBlur - focus leaving the field)
    	function validateEmail() {
    		var x = document.getElementById("email").value;
    		var y = document.getElementById("email");
    		var atpos = x.indexOf("@");
    		var dotpos = x.lastIndexOf(".");
    		if (atpos < 1 || dotpos < atpos + 2 || dotpos + 2 >= x.length) {
    			y.style.border="1px solid #C33";
    			return false;
    		}
    		else
    		{
    			y.style.border="1px solid #090";
    		}
    	}
    	// Turn the dropdown fields red if no selection made (onBlur - focus leaving the field)
    	function validateDropDown(dropDownID) {
    		var x = document.getElementById(dropDownID);
    		var y = document.getElementById(dropDownID).options;

    		if (y[x.selectedIndex].index = 0) {
    			x.style.border="1px solid #C33";
    			return false;
    		}
    		else if (x.selectedIndex > 0) {
    			x.style.border="1px solid #090";
    		}
    		else {
    			x.style.border="1px solid #C33";
    		}
    	}
    	// Turn the score fields red if not input (onBlur - focus leaving the field)
    	function validateScore(inputID) {
    		var x = document.getElementById(inputID);
    		if (x.value == null || x.value == "") {
    			x.style.border="1px solid #C33";
    			return false;
    		}
    		else if ((x.value >= 0) && (x.value <= 10)) {
    			x.style.border="1px solid #090";
    		}
    		else x.style.border="1px solid #C33";
    	}
    	// Reset all guidance borders to original colour
    	function resetBorders() {
    		var x = document.getElementById("registrationForm");
    		$("button").removeClass("highlight");
    		for (var i = 0; i < x.length; i++) {
    			x.elements[i].style.border="1px solid #CCC";
    		}*/
    	}
  	</script>

    <script>
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
      <form class="row g-3 needs-validation" novalidate> <!-- border border-white p-2 my-2 border-opacity-25  id="registrationForm" name="registrationForm" class="form-horizontal" method="post" action="php/register.php" onSubmit="return validateFullForm()" -->
        <div class="col-md-6">
          <label for="firstname" class="form-label">First name</label>
          <input type="text" class="form-control" id="firstname" required>
          <div class="valid-feedback">
            Looks good!
          </div>
        </div>
        <div class="col-md-6">
          <label for="surname" class="form-label">Last name</label>
          <input type="text" class="form-control" id="surname" required>
          <div class="valid-feedback">
            Looks good!
          </div>
        </div>
        <div class="col-md-6">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" required>
          <div class="valid-feedback">
            Looks good!
          </div>
        </div>
        <div class="col-md-6">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" required>
          <span class="un-msg"></span>
          <div class="invalid-feedback">
            Please choose a username.
          </div>
        </div>
        <div class="col-md-6">
          <label for="password" class="form-label">Password</label>
          <input type="text" class="form-control" id="password" name="password" onBlur="return validatePassword();" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" onchange="form.password2.pattern = this.value;">
          <div class="invalid-feedback">
            Minimum of 6 characters; at least 1 uppercase letter and 1 number.
          </div>
        </div>
        <div class="col-md-6">
          <label for="password2" class="form-label">Confirm Password</label>
          <input type="text" class="form-control" id="password2" name="password2" onBlur="return validatePassword2();" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}">
          <div class="invalid-feedback">
            Please choose a username.
          </div>
        </div>



        <div class="container text-center g-3">
          <label for="avatar" class="form-label">Select your avatar strip</label>
          <div class="row row-cols-4 row-cols-md-6 g-2">
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
          </div>
        </div>



<!--
        <div class="btn-group" role="group" aria-label="Radio toggle button group">
          <input type="radio" class="btn-check" autocomplete="off" id="fk1" name="fkradio" value="<?php echo $fk1; ?>" onClick="chooseImage('fk1');">
          <label class="btn btn-outline-light" for="fk1"><img src="<?php echo $fk1; ?>" alt="Football kit description..." class="w-100" /></label>

          <input type="radio" class="btn-check" autocomplete="off" id="fk2" name="fkradio" value="<?php echo $fk2; ?>" onClick="chooseImage('fk2');">
          <label class="btn btn-outline-light" for="fk2"><img src="<?php echo $fk2; ?>" alt="Football kit description..." class="w-100" /></label>

          <input type="radio" class="btn-check" autocomplete="off" id="fk3" name="fkradio" value="<?php echo $fk3; ?>" onClick="chooseImage('fk3');">
          <label class="btn btn-outline-light" for="fk3"><img src="<?php echo $fk3; ?>" alt="Football kit description..."class="w-100" /></label>



          <input type="radio" class="btn-check" autocomplete="off" id="fk5" name="fkradio" value="<?php echo $fk5; ?>" onClick="chooseImage('fk5');">
          <label class="btn btn-outline-light" for="fk5"><img src="<?php echo $fk5; ?>" alt="Football kit description..."class="w-100" /></label>

          <input type="radio" class="btn-check" autocomplete="off" id="fk6" name="fkradio" value="<?php echo $fk6; ?>" onClick="chooseImage('fk6');">
          <label class="btn btn-outline-light" for="fk6"><img src="<?php echo $fk6; ?>" alt="Football kit description..."class="w-100" /></label>

          <input type="radio" class="btn-check" autocomplete="off" id="fk1" name="fkradio" value="<?php echo $fk1; ?>" onClick="chooseImage('fk1');">
          <label class="btn btn-outline-light" for="fk1"><img src="<?php echo $fk1; ?>" alt="Football kit description..." class="w-100" /></label>

          <input type="radio" class="btn-check" autocomplete="off" id="fk2" name="fkradio" value="<?php echo $fk2; ?>" onClick="chooseImage('fk2');">
          <label class="btn btn-outline-light" for="fk2"><img src="<?php echo $fk2; ?>" alt="Football kit description..." class="w-100" /></label>

          <input type="radio" class="btn-check" autocomplete="off" id="fk3" name="fkradio" value="<?php echo $fk3; ?>" onClick="chooseImage('fk3');">
          <label class="btn btn-outline-light" for="fk3"><img src="<?php echo $fk3; ?>" alt="Football kit description..."class="w-100" /></label>

          <input type="radio" class="btn-check" autocomplete="off" id="fk4" name="fkradio" value="<?php echo $fk4; ?>" onClick="chooseImage('fk4');">
          <label class="btn btn-outline-light" for="fk4"><img src="<?php echo $fk4; ?>" alt="Football kit description..."class="w-100" /></label>

          <input type="radio" class="btn-check" autocomplete="off" id="fk5" name="fkradio" value="<?php echo $fk5; ?>" onClick="chooseImage('fk5');">
          <label class="btn btn-outline-light" for="fk5"><img src="<?php echo $fk5; ?>" alt="Football kit description..."class="w-100" /></label>

          <input type="radio" class="btn-check" autocomplete="off" id="fk6" name="fkradio" value="<?php echo $fk6; ?>" onClick="chooseImage('fk6');">
          <label class="btn btn-outline-light" for="fk6"><img src="<?php echo $fk6; ?>" alt="Football kit description..."class="w-100" /></label>
        </div>
      -->
        <input type="text" class="form-control" id="avatarSelection" name="avatarSelection" readonly>

        <div class="col-md-6">
          <label for="fieldofwork" class="form-label">Field of work</label>
          <input id="fieldofwork" name="fieldofwork" class="form-control" onBlur="return validateDropDown('fieldofwork');" list="datalistOptions1" placeholder="Type to search...">
          <datalist id="datalistOptions1">
            <!--<option selected>Open this select menu</option>-->
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
          <input id="faveteam" name="faveteam" class="form-control" onBlur="return validateDropDown('faveteam');" list="datalistOptions2" placeholder="Type to search...">
          <datalist id="datalistOptions2">
            <!--<option selected>Open this select menu</option>-->
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
          <input id="tournwinner" name="tournwinner" class="form-control" onBlur="return validateDropDown('tournwinner');" list="datalistOptions3" placeholder="Type to search...">
          <datalist id="datalistOptions3">
            <!--<option selected>Open this select menu</option>-->
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
              I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#terms">terms and conditions</a> of Hendy's Hunches.
            </label>
            <div class="invalid-feedback">
              You must agree before submitting.
            </div>
          </div>
        </div>
        <hr />
        <div class="col-12 d-flex justify-content-evenly">
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
      <p>Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022" class="text-white">FIFA World Cup Qatar 2022™</a></p>
      <p><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
    </footer>
		</div>


	</body>
</html>



            <!-- Username
            <div class="form-group">
            	<label for="username" class="col-sm-3 control-label">Username: </label>
                <div class="col-sm-5">
                <input type="text" class="form-control" id="username" name="username" placeholder="Create username" />
                </div>
                <div class="col-sm-4"><p id="un-msg" class="additional-info"></p>
                </div>
            </div>
            <!-- Password
            <div class="form-group">
            	<label for="password" class="col-sm-3 control-label">Password: </label>
                <div class="col-sm-5">
                <input type="password" class="form-control" id="password" name="password" placeholder="Create password" onBlur="return validatePassword();" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" onchange="form.password2.pattern = this.value;" />
                </div>
                <div class="col-sm-4">
                	<p class="additional-info">Minimum of 6 characters; at least 1 uppercase letter and 1 number.</p>
                </div>
            </div>
            <!-- Confirm Password
            <div class="form-group">
            	<label for="password2" class="col-sm-3 control-label">Confirm Password: </label>
                <div class="col-sm-5">
                <input type="password" class="form-control" id="password2" name="password2" placeholder="Confirm password" onBlur="return validatePassword2();" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" />
                </div>
                <div class="col-sm-4"></div>
            </div>
            <!-- First Name
            <div class="form-group">
	    		<label for="firstname" class="col-sm-3 control-label">First Name: </label>
                <div class="col-sm-5">
	        	<input type="text" class="form-control" id="firstname" name="firstname" placeholder="Enter your first name" onBlur="return validateName('firstname');" required />
                </div>
                <div class="col-sm-4"></div>
            </div>
            <!-- Surname
        	<div class="form-group">
            	<label for="surname" class="col-sm-3 control-label">Surname:</label>
                <div class="col-sm-5">
	        	<input type="text" class="form-control" id="surname" name="surname" placeholder="Enter your surname" onBlur="return validateName('surname');" required />
                </div>
                <div class="col-sm-4"></div>
            </div>
            <!-- Email Address
            <div class="form-group">
           		<label for="email" class="col-sm-3 control-label">Email:</label>
                <div class="col-sm-5">
	        	<input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" onBlur="return validateEmail();" required />
                </div>
                <div class="col-sm-4"></div>
            </div>
			<!-- Avatar Selection
            <div class="form-group" id="avatars">
           		<label for="avatars" class="col-sm-3 control-label">Select Avatar:</label>
                <div class="col-sm-6">
   	        	<button type="button" class="btn btn-default avatar" id="fk7" name="<?php echo $fk7; ?>" value="<?php echo $fk7; ?>" onClick="chooseImage('fk7');">
                <img src="<?php echo $fk7; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
   	        	<button type="button" class="btn btn-default avatar" id="fk8" name="<?php echo $fk8; ?>" value="<?php echo $fk8; ?>" onClick="chooseImage('fk8');">
                <img src="<?php echo $fk8; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
   	        	<button type="button" class="btn btn-default avatar" id="fk9" name="<?php echo $fk9; ?>" value="<?php echo $fk9; ?>" onClick="chooseImage('fk9');">
                <img src="<?php echo $fk9; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
                <button type="button" class="btn btn-default avatar" id="fk10" name="<?php echo $fk10; ?>" value="<?php echo $fk10; ?>" onClick="chooseImage('fk10');">
                <img src="<?php echo $fk10; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
                <button type="button" class="btn btn-default avatar" id="fk11" name="<?php echo $fk11; ?>" value="<?php echo $fk11; ?>" onClick="chooseImage('fk11');">
                <img src="<?php echo $fk11; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
                <button type="button" class="btn btn-default avatar" id="fk12" name="<?php echo $fk12; ?>" value="<?php echo $fk12; ?>" onClick="chooseImage('fk12');">
                <img src="<?php echo $fk12; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
                <button type="button" class="btn btn-default avatar" id="fk13" name="<?php echo $fk13; ?>" value="<?php echo $fk13; ?>" onClick="chooseImage('fk13');">
                <img src="<?php echo $fk13; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
                <button type="button" class="btn btn-default avatar" id="fk14" name="<?php echo $fk14; ?>" value="<?php echo $fk14; ?>" onClick="chooseImage('fk14');">
                <img src="<?php echo $fk14; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
                <button type="button" class="btn btn-default avatar" id="fk15" name="<?php echo $fk15; ?>" value="<?php echo $fk15; ?>" onClick="chooseImage('fk15');">
                <img src="<?php echo $fk15; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
                <button type="button" class="btn btn-default avatar" id="fk16" name="<?php echo $fk16; ?>" value="<?php echo $fk16; ?>" onClick="chooseImage('fk16');">
                <img src="<?php echo $fk16; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
                <button type="button" class="btn btn-default avatar" id="fk17" name="<?php echo $fk17; ?>" value="<?php echo $fk17; ?>" onClick="chooseImage('fk17');">
                <img src="<?php echo $fk17; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
                <button type="button" class="btn btn-default avatar" id="fk18" name="<?php echo $fk18; ?>" value="<?php echo $fk18; ?>" onClick="chooseImage('fk18');">
                <img src="<?php echo $fk18; ?>" width="100%" height="100%" alt="" border="0" />
                </button>
                <!-- Hidden form to capture user's avatar selection
                <input type="hidden" class="form-control" id="avatarSelection" name="avatarSelection" />
                </div>
            </div><!-- Avatars -->
            <!-- Signup/Reset Form Button
            <div class="form-group">
	    		<label for="buttons" class="col-sm-3 control-label"></label>
                <div class="col-sm-9">
	        	<input type="submit" class="btn btn-primary" value="Sign me up!" name="predictionsSubmitted" />
          		<input type="reset" class="btn btn-default" value="Reset all" onClick="resetBorders();" />
                </div>
            </div>
   	</form>

    </div><!-- /.container -->

    <?php include "includes/footer.php" ?>

    <script type="text/javascript">
  		function chooseImage(imageId) {
  			var x = document.getElementById(imageId).value;
  			document.getElementById("avatarSelection").value = x;
  		}
	</script>
  </body>
</html>
