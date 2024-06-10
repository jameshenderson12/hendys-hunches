<?php
session_start();
$page_title = 'Overview';

if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
    header("Location: index.php");
    exit();
}

include "php/header.php";
include "php/navigation.php";

?>

<!-- Main Content Section -->
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between">
    <nav>
      <h1>Game Overview</h1>
        <!-- <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
          <li class="breadcrumb-item active">Part #3 - 11.30</li>
        </ol> -->
      </nav> 
    </div><!-- End Page Title -->

    <section class="section">	
		<p class="lead">A simple overview of the application data for reference.</p>
		<div class="row">
		<?php
			include 'php/db-connect.php';
			$sql_get_user_count = "SELECT COUNT(*) AS no_of_users FROM live_user_information";
			$sql_get_match_count = "SELECT COUNT(*) AS no_of_matches FROM live_match_results";
			$sql_get_latest_user = "SELECT firstname, surname FROM live_user_information ORDER BY signupdate DESC LIMIT 1";
			$user_count = mysqli_query($con, $sql_get_user_count);
			$match_count = mysqli_query($con, $sql_get_match_count);
			$latest_user = mysqli_query($con, $sql_get_latest_user);
			while ($row = mysqli_fetch_assoc($user_count)) {
				$no_of_users = $row["no_of_users"];
			}
			while ($row = mysqli_fetch_assoc($match_count)) {
				$no_of_matches = $row["no_of_matches"];
			}
			$latest_user = mysqli_fetch_assoc(mysqli_query($con, $sql_get_latest_user));
			$latest_user_added = $latest_user['firstname']. " " .$latest_user['surname']. " ";
			$mysql_info = mysqli_get_server_info($con);
			mysqli_close($con);

			// Function to get the client IP address
			function get_client_ip() {
				$ipaddress = '';
				if (isset($_SERVER['HTTP_CLIENT_IP']))
					$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
				else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
					$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
				else if(isset($_SERVER['HTTP_X_FORWARDED']))
					$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
				else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
					$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
				else if(isset($_SERVER['HTTP_FORWARDED']))
					$ipaddress = $_SERVER['HTTP_FORWARDED'];
				else if(isset($_SERVER['REMOTE_ADDR']))
					$ipaddress = $_SERVER['REMOTE_ADDR'];
				else
					$ipaddress = 'UNKNOWN';
				return $ipaddress;
			}
		?>
		<div class="table-responsive">
			<table class="table table-striped">
			<tr>
				<th scope="row">Application Name</th>
				<td><?php echo $GLOBALS['title'] ?></td>
			</tr>
			<tr>
				<th scope="row">Version</th>
				<td><?php echo $GLOBALS['version'] ?></td>
			</tr>
			<tr>
				<th scope="row">Developer</th>
				<td><?php echo $GLOBALS['developer'] ?></td>
			</tr>
			<tr>
				<th scope="row">Last Updated</th>
				<td><?php echo $GLOBALS['last_update'] ?></td>
			</tr>
			<tr>
				<th scope="row">Base URL</th>
				<td><?php echo $GLOBALS['base_url'] ?></td>
			</tr>
			<!--
			<tr>
				<th scope="row">Server &amp; Client IP</th>
				<td><?php echo $_SERVER['SERVER_ADDR']; ?> / <?php print_r(get_client_ip()); ?></td>
			</tr>
			<tr>
				<th scope="row">OS &amp; Host Name</th>
				<td><?php echo php_uname(); ?></td>
			</tr>
			<tr>
				<th scope="row">PHP Version</th>
				<td><?php echo phpversion(); ?></td>
			</tr>
			<tr>
				<th scope="row">MySQL Version</th>
				<td><?php echo $mysql_info; ?></td>
			</tr>
		-->
			<tr>
				<th scope="row">No. of Matches</th>
				<td><?php printf("%d (%d at Group Stage and %d in Knockout Stages)", $GLOBALS['no_of_total_fixtures'], $GLOBALS['no_of_group_fixtures'], $GLOBALS['no_of_knockout_fixtures']); ?></td>
			</tr>
			<tr>
				<th scope="row">No. of Players</th>
				<td><?php echo $no_of_users; ?></td>
			</tr>
			<tr>
				<th scope="row">Latest Player</th>
				<td><?php echo $latest_user_added; ?></td>
			</tr>
			</table>
		</div>
	</div><!--row-->
    </section>

</main>

<!-- Footer -->
<?php include "php/footer.php" ?>