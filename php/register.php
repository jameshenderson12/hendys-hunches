<?php
	// Sanitize incoming username and password
	$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
	$password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
	$firstname = ucfirst($_POST['firstname']);
	$surname = ucfirst($_POST['surname']);
	$email = $_POST['email'];
	$avatar = $_POST['avatarSelection'];
	$fieldofwork = $_POST['fieldofwork'];
	$faveteam = filter_var($_POST['faveteam'], FILTER_SANITIZE_STRING);
	$tournwinner = filter_var($_POST['tournwinner'], FILTER_SANITIZE_STRING);
	
	include 'db-connect.php';
	
	// Initial query to set intial positional values
	$sql1 = "SELECT count(*) AS totalusers FROM live_user_information";
	// Execute the query and return the result or display appropriate error message
	$totalusers = mysqli_query($con, $sql1) or die(mysqli_error());		  
	// For each instance of the returned result		
	while ($row = mysqli_fetch_assoc($totalusers)) {
		$setdefstartpos = $row["totalusers"];
		$setdefcurrpos = $row["totalusers"] + 1;
		$setdeflastpos = $row["totalusers"] + 1;		
	  }			  		  	  
	
	$stmt1 = mysqli_prepare($con, "INSERT INTO live_user_information (username, password, firstname, surname, email, avatar, fieldofwork, faveteam, tournwinner, startpos, lastpos, currpos) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
	$stmt2 = mysqli_prepare($con, "INSERT INTO live_temp_information (username) VALUES (?)");
	
	// Bind the input parameters to the prepared statement
	mysqli_stmt_bind_param($stmt1, "sssssssssddd", $username, md5($password), $firstname, $surname, $email, $avatar, $fieldofwork, $faveteam, $tournwinner, $setdefstartpos, $setdeflastpos, $setdefcurrpos);
	mysqli_stmt_bind_param($stmt2, "s", $username);

	// Execute the query
	mysqli_stmt_execute($stmt1);
	mysqli_stmt_execute($stmt2);
//	printf("%d Row inserted.\n", mysqli_affected_rows($con));

	// Close statement and connection
	mysqli_stmt_close($stmt1);
	mysqli_stmt_close($stmt2);

	// Close database connection
	mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="3;url=../index.php">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <?php include "php/config.php" ?>
	<?php include "php/process.php" ?>    
    <link rel="shortcut icon" href="../ico/favicon.ico">

    <title>Hendy's Hunches: Registration</title>

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.css" rel="stylesheet">
    <link href="../css/registration.css" rel="stylesheet">
    
    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
  
  <div class="container">

      <h1>Hendy's Hunches: Registration</h1>
      
      <h3>You have successfully registered!</h3>      	 
        
      <p>Thank you for signing up to play Hendy's Hunches.</p>
      <p>You will now be automatically redirected back to the login page.</p>
      <p>If you are not redirected automatically, please <a href='../index.php'>click here</a>.</p>
      
      <div class="spinner"></div>
      
      <div id="spacer"></div>
      
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