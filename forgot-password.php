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
	$u = preg_replace('#[^a-z0-9]#i', '', $_GET['u']);
	$temppasshash = preg_replace('#[^a-z0-9]#i', '', $_GET['p']);
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
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
    <?php include "php/config.php" ?>
	<?php include "php/process.php" ?>    
    <link rel="shortcut icon" href="ico/favicon.ico">

    <title>Hendy's Hunches: Reset Password</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/registration.css" rel="stylesheet">
    
    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
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

  <body>
  
  <div class="container">
  
    <h1>Hendy's Hunches: Forgotten Password</h1>
    <p>Enter your email address for password reset or return to the <a href="index.php">login page</a> to sign in.</p>       
        <form id="forgotPassForm" name="forgotPassForm" class="form-horizontal" onSubmit="return false;">            
            <!-- Email Address -->
            <div class="form-group">
           		<label for="email" class="col-sm-3 control-label">Email: <font color="orangered"><tt><b>*</b></tt></font></label>
                <div class="col-sm-5">
	        	<input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required />
                </div>
                <div class="col-sm-4">
                <p id="status"></p>
                </div>
            </div>
            
            <div class="form-group">
            	<div class="col-sm-3"></div>           
                <div class="col-sm-5">
                <input type="button" id="forgotpassbtn" class="btn btn-primary" value="Generate temporary password" onClick="forgotPass();" />   			                
                </div>
                <div class="col-sm-4">                
                </div>
            </div>                        
        
        </form> 
        
        
    <div id="confirm-msg" style="display: none;">
        <h3>Now check your email inbox (junk/spam folder)</h3>
        <p>Please check your email inbox (including junk/spam folder) for an email containing a temporary password. Carefully follow the instructions within the email so you can log in to Hendy's Hunches again. You can then change your password from the home page to one of your own choice should you wish to.</p><p>You can now close this window.</p>
        <button type="button" class="btn btn-danger" onClick="windowClose()">Close Window</button>
    </div>    
        
    <!-- Site footer -->
    <div class="footer">
    <?php include "includes/footer.php" ?>
    </div>                          	                 
     
    </div><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
	<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>    
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>