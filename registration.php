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

    <title>Hendy's Hunches: Registration</title>

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
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.js"></script>
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

  <body>
  
  <div class="container">
  
        <h1>Hendy's Hunches: Registration</h1>
        <p>Register your details below to sign up or return to the <a href="index.php">login page</a> to sign in. All fields are required to be completed.</p>       
                
        <form id="registrationForm" name="registrationForm" class="form-horizontal" method="post" action="php/register.php" onSubmit="return validateFullForm()">
            <!-- Username -->            
            <div class="form-group">
            	<label for="username" class="col-sm-3 control-label">Username: </label>
                <div class="col-sm-5">
                <input type="text" class="form-control" id="username" name="username" placeholder="Create username" />
                </div>
                <div class="col-sm-4"><p id="un-msg" class="additional-info"></p>
                </div>
            </div>            
            <!-- Password -->
            <div class="form-group">
            	<label for="password" class="col-sm-3 control-label">Password: </label>
                <div class="col-sm-5">
                <input type="password" class="form-control" id="password" name="password" placeholder="Create password" onBlur="return validatePassword();" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" onchange="form.password2.pattern = this.value;" />
                </div>
                <div class="col-sm-4">
                	<p class="additional-info">Minimum of 6 characters; at least 1 uppercase letter and 1 number.</p>
                </div>
            </div>
            <!-- Confirm Password -->
            <div class="form-group">
            	<label for="password2" class="col-sm-3 control-label">Confirm Password: </label>
                <div class="col-sm-5">
                <input type="password" class="form-control" id="password2" name="password2" placeholder="Confirm password" onBlur="return validatePassword2();" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" />
                </div>
                <div class="col-sm-4"></div>
            </div>                        
            <!-- First Name -->               
            <div class="form-group">       
	    		<label for="firstname" class="col-sm-3 control-label">First Name: </label>
                <div class="col-sm-5">
	        	<input type="text" class="form-control" id="firstname" name="firstname" placeholder="Enter your first name" onBlur="return validateName('firstname');" required />                
                </div>
                <div class="col-sm-4"></div>
            </div>            
            <!-- Surname -->
        	<div class="form-group">
            	<label for="surname" class="col-sm-3 control-label">Surname:</label>
                <div class="col-sm-5">
	        	<input type="text" class="form-control" id="surname" name="surname" placeholder="Enter your surname" onBlur="return validateName('surname');" required />
                </div>
                <div class="col-sm-4"></div>
            </div>     	                  
            <!-- Email Address -->
            <div class="form-group">
           		<label for="email" class="col-sm-3 control-label">Email:</label>
                <div class="col-sm-5">
	        	<input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" onBlur="return validateEmail();" required />
                </div>
                <div class="col-sm-4"></div>
            </div>            
			<!-- Avatar Selection -->
            <div class="form-group" id="avatars">
           		<label for="avatars" class="col-sm-3 control-label">Select Avatar:</label>
                <div class="col-sm-6">
	        	<button type="button" class="btn btn-default avatar" id="fk1" name="<?php echo $fk1; ?>" value="<?php echo $fk1; ?>" onClick="chooseImage('fk1');">
                <img src="<?php echo $fk1; ?>" width="100%" height="100%" alt="" border="0" />
                </button>                
   	        	<button type="button" class="btn btn-default avatar" id="fk2" name="<?php echo $fk2; ?>" value="<?php echo $fk2; ?>" onClick="chooseImage('fk2');">
                <img src="<?php echo $fk2; ?>" width="100%" height="100%" alt="" border="0" />
                </button>                
   	        	<button type="button" class="btn btn-default avatar" id="fk3" name="<?php echo $fk3; ?>" value="<?php echo $fk3; ?>" onClick="chooseImage('fk3');">
                <img src="<?php echo $fk3; ?>" width="100%" height="100%" alt="" border="0" />
                </button>                                
   	        	<button type="button" class="btn btn-default avatar" id="fk4" name="<?php echo $fk4; ?>" value="<?php echo $fk4; ?>" onClick="chooseImage('fk4');">
                <img src="<?php echo $fk4; ?>" width="100%" height="100%" alt="" border="0" />
                </button>                
   	        	<button type="button" class="btn btn-default avatar" id="fk5" name="<?php echo $fk5; ?>" value="<?php echo $fk5; ?>" onClick="chooseImage('fk5');">
                <img src="<?php echo $fk5; ?>" width="100%" height="100%" alt="" border="0" />
                </button>                
   	        	<button type="button" class="btn btn-default avatar" id="fk6" name="<?php echo $fk6; ?>" value="<?php echo $fk6; ?>" onClick="chooseImage('fk6');">
                <img src="<?php echo $fk6; ?>" width="100%" height="100%" alt="" border="0" />
                </button>                
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
                <!-- Hidden form to capture user's avatar selection-->
                <input type="hidden" class="form-control" id="avatarSelection" name="avatarSelection" />           
                </div>
            </div><!-- Avatars -->
            <!-- Field of Work -->
            <div class="form-group">
        		<label for="fieldofwork" class="col-sm-3 control-label">Field of Work:</label>
                <div class="col-sm-5">        
				<select id="fieldofwork" name="fieldofwork" class="form-control" onBlur="return validateDropDown('fieldofwork');" />
				<option selected="selected" disabled class="text-success">--- Choose Employment Field ---</option>
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
        		</select>
            	</div>
                <div class="col-sm-4"></div>
            </div> 
            <!-- Favourite Team -->                           
            <div class="form-group">
        		<label for="faveteam" class="col-sm-3 control-label">Favourite Team:</label>
                <div class="col-sm-5">
				<select id="faveteam" name="faveteam" class="form-control" onBlur="return validateDropDown('faveteam');" />
				<option selected="selected" disabled class="text-success">--- Your Favourite Team ---</option>            
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
				</select>
            	</div>
                <div class="col-sm-4"></div>
            </div>
            <!-- Tournament Winner -->
            <div class="form-group">
            <label for="tournwinner" class="col-sm-3 control-label">Euro 2016 Winner:</label>
            <div class="col-sm-5">        
			<select id="tournwinner" name="tournwinner" class="form-control" onBlur="return validateDropDown('tournwinner');" />
				<option selected="selected" disabled class="text-success">--- Vote The Winner ---</option>        
				<?php
                    // Source file for extracting data
                    $file = 'text/select-euro2016teams-input.txt';        
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
	        	</select>
                </div>
                <div class="col-sm-4">
                	<p class="additional-info">Please note that no points are awarded for this selection.</p>
                </div>
            </div>
            
            <!-- Disclaimer -->               
            <div class="form-group">       
	    		<label for="disclaimer" class="col-sm-3 control-label"></label>
                <div class="col-sm-9">
	        	<p><input type="checkbox" id="disclaimer" name="disclaimer" value="disclaimer">&nbsp;By signing up, I acknowledge and accept the <a href="" data-toggle="modal" data-target="#HHterms">terms and conditions</a> of Hendy's Hunches.</p>                
                </div>
            </div>
            
            <!-- Signup/Reset Form Button -->               
            <div class="form-group">       
	    		<label for="buttons" class="col-sm-3 control-label"></label>
                <div class="col-sm-9">
	        	<input type="submit" class="btn btn-primary" value="Sign me up!" name="predictionsSubmitted" />     
          		<input type="reset" class="btn btn-default" value="Reset all" onClick="resetBorders();" />            
                </div>
            </div>                          
        
          <!-- Site footer -->
          <div class="footer">
          <?php include "includes/footer.php" ?>
          </div>          
   	</form>
    
      <!-- Modal -->
      <div id="HHterms" class="modal fade" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Hendy's Hunches: Terms &amp; Conditions</h4>
            </div>
            <div class="modal-body" style="font-size: 0.95em;">
              <img src="img/hh-logo-v8.png" class="img-responsive center-block" title="Hendy's Hunches Logo" alt="Hendy's Hunches Logo" style="width: 150px; background-color: #222; padding: 10px; margin-bottom: 10px;">
              <p>By registering to play Hendy's Hunches, you acknowledge that your participation in this game, and the game itself, is only for fun and light-hearted entertainment.</p>
              <p>Only one registration per person is allowed and there is a participation fee of £5 which is to be paid to James Henderson prior to 8th June, 2016. This participation fee compromises of a 40% donation to the <a href="http://ballboys.org.uk/" title="Ballboys Charity" target="_blank">Ballboys charity</a>, 40% prize fund and 20% towards the cost of ongoing overheads.</p>
              <p>The game is based upon the UEFA Euro 2016 tournament (all 51 fixtures).</p>
              <p>There will be a minimum of 3 prize funds and this number may be increased depending on the total number of participants. The number of prize funds available, and their amounts, will be indicated in the rankings table shortly after the game commences. Those participants who occupy a prize fund place after the final tournament fixture will receive the corresponding prize amount shortly thereafter. In the event of a shared spot, prizes will be split.</p>
              <p>You are very welcome to invite family and friends to take part but be aware that any unpaid entrance fees will result in a participant being removed from the game.</p>
            </div>
            <!--
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
            -->
          </div>
        </div>
      </div>                      
     
    </div><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
	<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>    
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript">
		$("button").click(function(){			
			$("button").css("background-color", "transparent").css("border", "1px solid #cccccc");
			$(this).css("background-color", "#FFFF00").css("border", "1px solid #090");
		}); 	
		function chooseImage(imageId) {			
			var x = document.getElementById(imageId).value;		
			document.getElementById("avatarSelection").value = x;
			document.getElementById("avatars").style.border = "none";
		}
	</script>    
  </body>
</html>