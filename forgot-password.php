<?php
// Include necessary files for configuration and database connection
include 'php/config.php';
include 'php/db-connect.php';
include 'php/send-temppass-email.php';

// Initialise variable for error messages
$generateTempPassSuccess = false;

if (isset($_POST['e'])) {
    $e = mysqli_real_escape_string($con, $_POST['e']);
    $sql = "SELECT id, firstname, username FROM live_user_information WHERE email='$e' LIMIT 1";
    $query = mysqli_query($con, $sql);
    $numrows = mysqli_num_rows($query);

    if ($numrows > 0) {
        while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $id = $row["id"];
            $fn = $row["firstname"];
            $u = $row["username"];            
        }
        $emailcut = substr($e, 0, 4);
        $randNum = rand(10000, 99999);
        $tempPass = "$emailcut$randNum";
        $hashTempPass = md5($tempPass);
        $sql = "UPDATE live_temp_information SET temp_pass='$hashTempPass' WHERE username='$u' LIMIT 1";
        $query = mysqli_query($con, $sql);
      }      
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
        echo "There is no match for that username with that temporary password in the system. We cannot proceed.";
        exit();
    } else {
        $row = mysqli_fetch_row($query);
        $id = $row[0];
        $sql = "UPDATE live_user_information SET password='$temppasshash' WHERE id='$id' AND username='$u' LIMIT 1";
        $query = mysqli_query($con, $sql);
        $sql = "UPDATE live_temp_information SET temp_pass='' WHERE username='$u' LIMIT 1";
        $query = mysqli_query($con, $sql);
        header("location: index.php");
        exit();
    }
}

mysqli_close($con);

// Set success flag
$generateTempPassSuccess = true;

// If registration is successful, send the welcome email
if ($generateTempPassSuccess) {  
  sendTempPasswordEmail($fn, $u, $e, $tempPass, $hashTempPass);
}
?>
<!DOCTYPE html>
<html lang="en-GB" class="h-100">
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
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
    <meta name="keywords" content="football, predictions, game">
		<title>Forgot Password - Hendy's Hunches</title>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/registration.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script type="text/javascript">
      // Turn the email field red if not input correct (onBlur - focus leaving the field)
      function validateEmail() {
      var x = document.getElementById("email").value;
      var y = document.getElementById("email");
      var atpos = x.indexOf("@");
      var dotpos = x.lastIndexOf(".");
      if (atpos < 1 || dotpos < atpos + 2 || dotpos + 2 >= x.length) {
        y.style.border="1px solid red";
        return false;
      }
      else
      {
        y.style.border="1px solid green";
      }
      }

      // Reset all guidance borders to original colour
      function resetBorders() {
      var x = document.getElementById("registrationForm");
      for (var i = 0; i < x.length; i++) {
        x.elements[i].style.border="1px solid #CCC";
      }
      }

      function _(x){
      return document.getElementById(x);
      }

      function ajaxObj(meth, url) {
      var x = new XMLHttpRequest();
      x.open( meth, url, true );
      x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      return x;
      }

      function ajaxReturn(x){
      if(x.readyState == 4 && x.status == 200){
        return true;
      }
      }

      function forgotPass(){
      var e = _("email").value;
      if (e == ""){
        _("status").innerHTML = "Please type in your email address.";
      }
      else {
        //_("forgotpassbtn").style.display = "none";
        //_("status").innerHTML = 'Please wait ...';
        _("status").innerHTML = '<div class="spinner"></div>';
        var ajax = ajaxObj("POST", "forgot-password.php");
        ajax.onreadystatechange = function() {
          if(ajaxReturn(ajax) == true) {
            var response = ajax.responseText;
            //alert(response);
            if(response == "success"){
              //alert("Gotcha!");
              //_("forgotPassForm").innerHTML = '<h3>Step 2. Check your email inbox in a few minutes</h3><p>You can close this window or tab if you like.</p>';
              $("#forgotPassForm").hide();
              $("#confirm-msg").show();
            } else if (response == "no_exist"){
              _("status").innerHTML = "Sorry, that email address has not been registered.";
            } else if (response == "email_send_failed"){
              _("status").innerHTML = "Mail function failed to execute.";
            } else {
              _("status").innerHTML = "An unknown error occurred.";
            }
          }
        }
        ajax.send("e="+e);
      }
      }

      function windowClose() {
      window.open('','_parent','');
      window.close();
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
  					<a class="nav-link fw-bold py-1 px-0" href="registration.php">Register</a>
            <a class="nav-link fw-bold py-1 px-0" href="#" data-bs-toggle="modal" data-bs-target="#terms">Terms</a>
  				</nav>
  			</div>
  		</header>

  		<main class="px-3">
        
      <h1>Forgotten Password</h1>
  			<!-- <img src="img/germany-2024-logo-md.png" alt="Germany 2024 edition of Hendy's Hunches" class="w-50 mb-3"> -->
        
        <p>Enter your email address for password reset.</p>

        <form id="forgotPassForm" name="forgotPassForm" onSubmit="return false;" class="">

            <div class="mb-3 row d-flex justify-content-center">
              <label for="email" class="col-sm-2 col-form-label">Email</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="email" name="email" required>
                <p id="status"></p>
              </div>
            </div>

            <hr />
            <input type="button" id="forgotpassbtn" class="btn btn-primary" value="Generate temporary password" onClick="forgotPass();" />
        </form>

        <div id="confirm-msg" style="display: none;">
            <h3>Now check your email inbox (junk/spam folder)</h3>
            <p>Please check your email inbox (including junk/spam folder) for an email containing a temporary password. Carefully follow the instructions within the email so you can log in to Hendy's Hunches again. You can then change your password should you wish to.</p><p>You can now close this window.</p>
            <button type="button" class="btn btn-danger" onClick="windowClose()">Close Window</button>
        </div>

  		</main>

      <!-- HH Terms Modal -->
      <div class="modal fade" id="terms" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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
                <li>an entry fee of £<?=$signup_fee_formatted?> is to be paid prior to <?=$signup_close_date?>; split for charity donation and prize funds</li>
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
        <p class="small fw-light">Predictions game based on <a href="https://www.uefa.com/euro2024/" class="text-white"><?=$competition?></a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
      </footer>

	  </div>
  </body>
</html>
