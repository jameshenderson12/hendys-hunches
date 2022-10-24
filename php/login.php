<?php 
if($_POST['remember']) {
	$year = time() + 31536000;
	setcookie('remember_me', $_POST['username'], $year);
}
elseif(!$_POST['remember']) {
	if(isset($_COOKIE['remember_me'])) {
		$past = time() - 100;
		setcookie('remember_me', $past);
	}
}

session_start();

	// Sanitize incoming username and password
	$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
	$password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
	
	include 'db-connect.php';	
	
	$stmt = mysqli_prepare($con, "SELECT id FROM live_user_information WHERE username = ? and password = md5(?)"); 
	
	// Bind the input parameters to the prepared statement
	mysqli_stmt_bind_param($stmt, "ss", $username, $password);

	// Execute the query
	mysqli_stmt_execute($stmt);
	
	// Store the result so we can determine how many rows have been returned
	mysqli_stmt_store_result($stmt);
		
	if (mysqli_stmt_num_rows($stmt) == 1) {
		
		// Bind the returned user ID to the $id variable
		mysqli_stmt_bind_result($stmt, $id);
		mysqli_stmt_fetch($stmt);				
			
		// Update the account's last_login
		$stmt = mysqli_prepare($con, "UPDATE live_user_information SET lastlogin = NOW() WHERE id = ?");
		mysqli_stmt_bind_param($stmt, "d", $id);		
		mysqli_stmt_execute($stmt);
		mysqli_stmt_free_result($stmt);
		
		// Retrieve the corresponding login information into session variables
		$stmt = mysqli_prepare($con, "SELECT id, username, password, firstname, surname FROM live_user_information WHERE id = ?");
		mysqli_stmt_bind_param($stmt, "d", $id);		
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $id, $username, $password, $firstname, $surname);
		mysqli_stmt_fetch($stmt);		
		// Assign user session variables
		$_SESSION['id'] = $id;
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $password;
		$_SESSION['firstname'] = $firstname;
		$_SESSION['surname'] = $surname;		
		$_SESSION['login'] = "1";
					
		// Redirect the user to the successful page
		header('Location: ../dashboard.php');
		exit;
	}	
	else {
		$_SESSION['login'] = "";		
		// Redirect the user to the 'unsuccessful' page
		header('Location: ../index.php');
		exit;
	}		
	
	// Close statement and connection
	mysqli_stmt_close($stmt);

	// Close database connection
	mysqli_close($con);	
?>

<?php 

if($_POST['remember']) {
	$year = time() + 31536000;
	setcookie('remember_me', $_POST['username'], $year);
}
elseif(!$_POST['remember']) {
	if(isset($_COOKIE['remember_me'])) {
		$past = time() - 100;
		setcookie('remember_me', $past);
	}
}

?>