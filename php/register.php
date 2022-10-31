<?php
	// Sanitize incoming username and password
	$firstname = ucfirst($_POST['firstname']);
	$surname = ucfirst($_POST['surname']);
	$email = $_POST['email'];
	$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
	$password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
	$avatar = $_POST['avatar'];
	$fieldofwork = filter_var($_POST['fieldofwork'], FILTER_SANITIZE_STRING);
	$location = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
	$faveteam = filter_var($_POST['faveteam'], FILTER_SANITIZE_STRING);
	$tournwinner = filter_var($_POST['tournwinner'], FILTER_SANITIZE_STRING);

	include 'php/db-connect.php';

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

	$stmt1 = mysqli_prepare($con, "INSERT INTO live_user_information (username, password, firstname, surname, email, avatar, fieldofwork, location, faveteam, tournwinner, startpos, lastpos, currpos) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
	$stmt2 = mysqli_prepare($con, "INSERT INTO live_temp_information (username) VALUES (?)");

	// Bind the input parameters to the prepared statement
	mysqli_stmt_bind_param($stmt1, "ssssssssssddd", $username, md5($password), $firstname, $surname, $email, $avatar, $fieldofwork, $location, $faveteam, $tournwinner, $setdefstartpos, $setdeflastpos, $setdefcurrpos);
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
<html lang="en" class="h-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
		<title>Hendy's Hunches: Registration</title>
    <?php include "config.php" ?>
		<link rel="shortcut icon" href="../ico/favicon.ico">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link href="https://getbootstrap.com/docs/5.2/assets/css/docs.css" rel="stylesheet">
    <link href="../css/registration.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
  </head>

	<body class="d-flex h-100 text-center text-bg-dark">

		<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">

      <h1>Hendy's Hunches: Registration</h1>

      <h3>You have successfully registered!</h3>

      <p>Thank you for signing up to play Hendy's Hunches.</p>
      <p>You will now be automatically redirected back to the login page.</p>
      <p>If you are not redirected automatically, please <a href='../index.php'>click here</a>.</p>

      <div class="spinner"></div>

      <div id="spacer"></div>

			<footer class="mt-auto">
	      <p class="small fw-light">Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022" class="text-white">FIFA World Cup Qatar 2022™</a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
	    </footer>

    </div><!-- /.container -->

  </body>
</html>
