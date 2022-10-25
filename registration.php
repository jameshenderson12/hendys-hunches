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
		<link rel="canonical" href="https://getbootstrap.com/docs/5.2/examples/cover/">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <!--<link href="css/registration.css" rel="stylesheet">-->
		<style>
		/* Custom default button */
		.btn-secondary,
		.btn-secondary:hover,
		.btn-secondary:focus {
		  color: #333;
		  text-shadow: none; /* Prevent inheritance from `body` */
		}
		body {
		  /*text-shadow: 0 .05rem .1rem rgba(0, 0, 0, .5);*/
		  /*box-shadow: inset 0 0 5rem rgba(0, 0, 0, .5);*/
			background-image: url(img/football-stadium.jpg);
		}
		main {
			background: rgba(10, 20, 50, 0.5);
			border-radius: 10px;
			padding: 1em;
			box-shadow: inset 0 0 5rem rgba(0, 0, 0, .5);
      text-shadow: 0 .05rem .1rem rgba(0, 0, 0, .5);
		}
		.cover-container {
		  max-width: 42em;
		}
		.nav-masthead .nav-link {
		  color: rgba(255, 255, 255, .5);
		  border-bottom: .25rem solid transparent;
		}
		.nav-masthead .nav-link:hover,
		.nav-masthead .nav-link:focus {
		  border-bottom-color: rgba(255, 255, 255, .25);
		}
		.nav-masthead .nav-link + .nav-link {
		  margin-left: 1rem;
		}
		.nav-masthead .active {
		  color: #fff;
		  border-bottom-color: #fff;
		}
		.bd-placeholder-img {
			font-size: 1.125rem;
			text-anchor: middle;
			-webkit-user-select: none;
			-moz-user-select: none;
			user-select: none;
		}
    .form-check .form-check-input {
	     float: none !important;
    }
    .modal {
      color: #000;
    }
    .modal li {
      text-align: left;
    }
    .modal img {
      text-align: center;
    }
		@media (min-width: 768px) {
			.bd-placeholder-img-lg {
				font-size: 3.5rem;
			}
		}
		.b-example-divider {
			height: 3rem;
			background-color: rgba(0, 0, 0, .1);
			border: solid rgba(0, 0, 0, .15);
			border-width: 1px 0;
			box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
		}
		.b-example-vr {
			flex-shrink: 0;
			width: 1.5rem;
			height: 100vh;
		}
		.bi {
			vertical-align: -.125em;
			fill: currentColor;
		}
		.nav-scroller {
			position: relative;
			z-index: 2;
			height: 2.75rem;
			overflow-y: hidden;
		}
		.nav-scroller .nav {
			display: flex;
			flex-wrap: nowrap;
			padding-bottom: 1rem;
			margin-top: -1px;
			overflow-x: auto;
			text-align: center;
			white-space: nowrap;
			-webkit-overflow-scrolling: touch;
		}
		</style>
    <script type="text/javascript">
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
    		/*return true;*/
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
    		}
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

      <form class="row g-3 needs-validation" novalidate>
        <div class="col-md-4">
          <label for="validationCustom01" class="form-label">First name</label>
          <input type="text" class="form-control" id="validationCustom01" value="" required>
          <div class="valid-feedback">
            Looks good!
          </div>
        </div>
        <div class="col-md-4">
          <label for="validationCustom02" class="form-label">Last name</label>
          <input type="text" class="form-control" id="validationCustom02" value="" required>
          <div class="valid-feedback">
            Looks good!
          </div>
        </div>
        <div class="col-md-4">
          <label for="validationCustomUsername" class="form-label">Username</label>
          <div class="input-group has-validation">
            <span class="input-group-text" id="inputGroupPrepend">@</span>
            <input type="text" class="form-control" id="validationCustomUsername" aria-describedby="inputGroupPrepend" required>
            <div class="invalid-feedback">
              Please choose a username.
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <label for="validationCustom03" class="form-label">City</label>
          <input type="text" class="form-control" id="validationCustom03" required>
          <div class="invalid-feedback">
            Please provide a valid city.
          </div>
        </div>
        <div class="col-md-3">
          <label for="validationCustom04" class="form-label">State</label>
          <select class="form-select" id="validationCustom04" required>
            <option selected disabled value="">Choose...</option>
            <option>...</option>
          </select>
          <div class="invalid-feedback">
            Please select a valid state.
          </div>
        </div>
        <div class="col-md-3">
          <label for="validationCustom05" class="form-label">Zip</label>
          <input type="text" class="form-control" id="validationCustom05" required>
          <div class="invalid-feedback">
            Please provide a valid zip.
          </div>
        </div>
        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
            <label class="form-check-label" for="invalidCheck">
              Agree to terms and conditions
            </label>
            <div class="invalid-feedback">
              You must agree before submitting.
            </div>
          </div>
        </div>
        <div class="col-12">
          <button class="btn btn-primary" type="submit">Submit form</button>
        </div>
      </form>

			<!--<p class="lead">Pit your FIFA World Cup 2022 predictions against others for a chance to earn a prize spot or bragging rights in the rankings.</p>-->
			<p class="lead">
				<a href="#" class="btn btn-lg btn-secondary fw-bold border-white bg-white" type="submit"><i class="fw-bold bi bi-box-arrow-in-right"></i> Log in</a>
			</p>
		</main>

    <!-- HH Terms Modal -->
    <div class="modal fade" id="terms" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="staticBackdropLabel">Hendy's Hunches: Terms &amp; Conditions</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
		</footer>
		</div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
	</body>
</html>
