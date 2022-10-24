<?php
// Start the session
session_start();

// Ajax calls this code to execute
include 'db-connect.php';

if(isset($_POST['pwd'])){
	// Assign user session variables
	$id = $_SESSION['id'];
	$username = $_SESSION['username'];
	// Sanitise the retreived password
	$pwd = filter_var($_POST['pwd'], FILTER_SANITIZE_STRING);
	// Hash the password
	$hashPass = md5($pwd);
	$sql_update = "UPDATE live_user_information SET password = '$hashPass' WHERE username='$username' LIMIT 1";
	$query = mysqli_query($con, $sql_update);	
	
	$sql_email = "SELECT * FROM live_user_information WHERE id='$id'";
	$query2 = mysqli_query($con, $sql_email);	
	$numrows = mysqli_num_rows($query2);
	//echo $numrows;
	//echo "success";
	//exit();

	if ($numrows > 0) {
	while($row = mysqli_fetch_array($query2, MYSQLI_ASSOC)){
			$id = $row["id"];
			$u = $row["username"];
			$e = $row["email"];			
		}
		$to = "$e";
		$from = "no-reply@hendyshunches.co.uk";
		$headers ="From: $from\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1 \n";
		$subject ="Successful Hendy's Hunches Password Change";
		$msg = '<h3>Hello '.$u.'</h3><p>This is an automated message from Hendy&#39;s Hunches.</p><p>You have successfully changed your password.</p><p>If you are not aware that you have changed your password then please contact James Henderson as soon as possible.</p>';
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
// Close the database connection    
mysqli_close($con);