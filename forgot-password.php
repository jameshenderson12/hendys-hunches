<?php
// Ajax calls this code to execute
include 'php/db-connect.php';
if(isset($_POST['e'])){
	$e = mysqli_real_escape_string($con, $_POST['e']);
	$sql = "SELECT id, username FROM live_user_information WHERE email='$e' LIMIT 1";
	$query = mysqli_query($con, $sql);
	$numrows = mysqli_num_rows($query);
	//echo $numrows;
	if($numrows > 0){
		while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)){
			$id = $row["id"];
			$u = $row["username"];
		}
		$emailcut = substr($e, 0, 4);
		$randNum = rand(10000,99999);
		$tempPass = "$emailcut$randNum";
		$hashTempPass = md5($tempPass);
		$sql = "UPDATE live_temp_information SET temp_pass='$hashTempPass' WHERE username='$u' LIMIT 1";
	    $query = mysqli_query($con, $sql);
		$to = "$e";
		$from = "no-reply@hendyshunches.co.uk";
		$headers ="From: $from\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1 \n";
		$subject ="Hendy's Hunches Temporary Password";
		$msg = '<h3>Hello '.$u.'</h3><p>This is an automated message from Hendy&rsquo;s Hunches. Use the temporary password and link below to get logged in again. You can then change your password to one of your own choice from the home page.</p><ol><li>Note your username: <b>'.$u.'</b></li><li>Note your temporary password: <b>'.$tempPass.'</b></li><li><a href="http://www.hendyshunches.co.uk/forgot-password.php?u='.$u.'&p='.$hashTempPass.'">Now click this link to apply temporary password and use these details to log in</a></li></ol><p>If you do not click the link in this email, no changes will be made to your account. In order to set your login password to the temporary password you must click the link above.</p>';
		if(mail($to,$subject,$msg,$headers)) {
			echo "success";
			exit();
		} else {
			echo "email_send_failed";
			exit();
		}
    } else {
        echo "no_exist";
    }
    exit();
}
?>
<?php
// Email link click executes this code
if(isset($_GET['u']) && isset($_GET['p'])){
	//$u = preg_replace('#[^a-z0-9]#i', '', $_GET['u']);
	//$temppasshash = preg_replace('#[^a-z0-9]#i', '', $_GET['p']);
	$u = $_GET['u'];
	$temppasshash = $_GET['p'];
	if(strlen($temppasshash) < 10){
		exit();
	}
	$sql = "SELECT id FROM live_temp_information WHERE username='$u' AND temp_pass='$temppasshash' LIMIT 1";
	$query = mysqli_query($con, $sql);
	$numrows = mysqli_num_rows($query);
	if($numrows == 0){
		//header("location: message.php?msg=There is no match for that username with that temporary password in the system. We cannot proceed.");
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
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
		<title>Hendy's Hunches: Forgot Password</title>
    <?php include "php/config.php" ?>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
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
  			<!--<h1>Welcome</h1>-->
<!--
  			<img src="img/qatar-2022-logo.png" alt="Qatar 2022 edition of Hendy's Hunches" class="w-50 mb-3">
-->
        <h1>Forgotten Password</h1>
        <p>Enter your email address for password reset or return to the <a href="index.php">login page</a> to sign in.</p>

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
            <p>Please check your email inbox (including junk/spam folder) for an email containing a temporary password. Carefully follow the instructions within the email so you can log in to Hendy's Hunches again. You can then change your password from the home page to one of your own choice should you wish to.</p><p>You can now close this window.</p>
            <button type="button" class="btn btn-danger" onClick="windowClose()">Close Window</button>
        </div>

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
                <li>the game is based on FIFA World Cup 2022???</li>
                <li>only one registration per person is permitted although family and friends are welcome to participate</li>
                <li>an entry fee of ??5 is to be paid prior to 20/11/2022; split for charity (TBC) donation and prize funds</li>
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
        <p class="small fw-light">Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022" class="text-white">FIFA World Cup Qatar 2022???</a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
      </footer>

	  </div>
  </body>
</html>
