<?php
// Start the session
session_start();
if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
	header ("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en-GB">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
    <?php include "php/config.php" ?>
	<?php include "php/process.php" ?>
    <link rel="shortcut icon" href="ico/favicon.ico">

    <title>Hendy's Hunches: Change Password</title>

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
	function validateFullForm() {
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
		return true;				
	}	
	
	function checkPassword(str) {
	    var re = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/;
    	return re.test(str);
  	}
		
	// Turn the password field red if not input correct (onBlur - focus leaving the field)
	function validatePassword() {
		var x = document.getElementById("password");
		if ((x.value == null) || (x.value == "") || (!checkPassword(x.value))) {
			x.style.border="1px solid red";
			return false;
		}
		else x.style.border="1px solid green";
	}
	
	// Turn the confirm password field red if not input correct (onBlur - focus leaving the field)
	function validatePassword2() {
		var x = document.getElementById("password2");
		var y = document.getElementById("password");
		if ((x.value == null) || (x.value == "") || (!checkPassword(x.value)) || (x.value != y.value)) {
			x.style.border="1px solid red";
			return false;
		}
		else x.style.border="1px solid green";
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
	
	function changePass(){
		var pwd = _("password").value;
		if (pwd == ""){
			_("status").innerHTML = "Please type in a new password.";
		} 
		else {
			//_("changepassbtn").style.display = "none";
			_("status").innerHTML = 'Please wait ...';
			//_("status").innerHTML = '<div class="spinner"></div>';
			var ajax = ajaxObj("POST", "php/change-pwd.php");
			ajax.onreadystatechange = function() {
				if(ajaxReturn(ajax) == true) {
					var response = ajax.responseText;
					//alert(response);
					if(response = "success"){
						//alert("Gotcha!");
						_("changePassForm").innerHTML = '<h3>Password successfully changed!</p>';
						//$("#forgotPassForm").hide();
						//$("#confirm-msg").show();
					} 
					else if (response = "no_exist"){
						_("status").innerHTML = "Sorry, that email address has not been registered.";
					} 
					/*else if (response = "email_send_failed"){
						_("status").innerHTML = "Mail function failed to execute.";
					}*/ 
					else {
						_("status").innerHTML = "An unknown error occurred.";
					}
				}
			}
			ajax.send("pwd="+pwd);
		}
	}
	
	function test() {
		alert("<?php echo $_SESSION["username"]; ?>");
	}
	</script>
  </head>

  <body>
  
  <div class="container">
  
    <h1>Hendy's Hunches: Change Password</h1>
    <p>Please enter a new password or return to the <a href="dashboard.php">dashboard page</a>.</p>       
    <form id="changePassForm" name="changePassForm" class="form-horizontal" onSubmit="return false;">
        <!-- New Password -->
        <div class="form-group">
            <label for="password" class="col-sm-3 control-label">New Password: </label>
            <div class="col-sm-5">
            <input type="password" class="form-control" id="password" name="password" placeholder="Set new password" onBlur="return validatePassword();" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" onchange="form.password2.pattern = this.value;" />
            </div>
            <div class="col-sm-4"><p id="status"></p></div>
        </div>
        <!-- Confirm New Password -->
        <div class="form-group">
            <label for="password2" class="col-sm-3 control-label">Confirm New Password: </label>
            <div class="col-sm-5">
            <input type="password" class="form-control" id="password2" name="password2" placeholder="Confirm new password" onBlur="return validatePassword2();" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" />
            </div>
            <div class="col-sm-4"></div>
        </div>                        
        
        <div class="form-group">
            <div class="col-sm-3"></div>           
            <div class="col-sm-5">
            <input type="button" id="changepassbtn" class="btn btn-primary" value="Change password" onClick="changePass()" />   			            
            </div>
            <div class="col-sm-4">                
            </div>
        </div>                        
    
    </form>         
    
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