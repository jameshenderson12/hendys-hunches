<?php

// Connect to the database
include 'db-connect.php';

$username = $_POST['username']; //up-to-date PHP

// Create a query to return the rankings information
$sql_query = "SELECT username FROM live_user_information WHERE username = '$username' LIMIT 1";

// Execute the query and return the results or display an appropriate error message					                                   
$result = mysqli_query($con, $sql_query) or die(mysqli_error());
$num = mysqli_num_rows($result);

// Return either 0 or 1 depending on username query result
echo $num;

// Close database connection
mysqli_close();