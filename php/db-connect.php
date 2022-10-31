<?php
	// Setup global variables
	$dbusername = "hh_james_adm";
	$dbpassword = "Q8CwYWmkG8pCwcM";
	$database = "hh_wc2022_live";
	$server = "92.205.14.36";

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
