<?php
	// Setup global variables
	$dbusername = "jamesc2_3";
	$dbpassword = "igraFEbO12";
	$database = "jamesc2_3";
	$server = "db2";
	
	// Create DB connection
	$con = mysqli_connect($server, $dbusername, $dbpassword, $database);

	// Check connection
	if (mysqli_connect_errno()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	else {
		/*echo "<script type='text/javascript'>alert('Connected to DataBase!');</script>";*/
	}
?>