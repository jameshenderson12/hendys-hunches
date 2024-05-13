<?php
// Start the session
session_start();
if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
	header ("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
		<title>Hendy's Hunches: Overview</title>
    <?php include "php/config.php" ?>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu|Lora">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
		<link rel="stylesheet" href="css/default.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </head>

  <body>
		<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Offcanvas navbar large">
		    <div class="container">
					<img src="img/hh-favicon-2018.jpg" class="img-responsive" style="margin: 0px 20px 0px; height:50px">
		      <a class="navbar-brand" href="#">Hendy's Hunches</a>
		      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar2" aria-controls="offcanvasNavbar2">
		        <span class="navbar-toggler-icon"></span>
		      </button>
		      <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasNavbar2" aria-labelledby="offcanvasNavbar2Label">
		        <div class="offcanvas-header">
		          <h5 class="offcanvas-title" id="offcanvasNavbar2Label">Hendy's Hunches</h5>
		          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		        </div>
		        <div class="offcanvas-body">
		          <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
		            <li class="nav-item">
		              <a class="nav-link active" aria-current="page" href="dashboard.php">Home</a>
		            </li>
		            <li class="nav-item">
		              <a class="nav-link disabled" href="predictions.php">Submit Predictions</a>
		            </li>
								<li class="nav-item">
		              <a class="nav-link" href="rankings.php">Rankings</a>
		            </li>
								<li class="nav-item">
		              <a class="nav-link" href="howitworks.php">How It Works</a>
		            </li>
								<li class="nav-item">
									<a class="nav-link" href="about.php">About</a>
								</li>
		            <li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
										<?php returnAvatar();	?>
		              </a>
		              <ul class="dropdown-menu">
										<!--<li><a class="dropdown-item" href="overview.php">Application overview</a></li>-->
		                <li><a class="dropdown-item" href="change-password.php">Change my password</a></li>
		                <li><a class="dropdown-item" href="user.php?id=<?php echo $_SESSION['id']; ?>" class="card-link">View my predictions</a></li>
		                <li>
		                  <hr class="dropdown-divider">
		                </li>
		                <li><a class="dropdown-item" href="php/logout.php">Logout</a></li>
		              </ul>
		            </li>
		          </ul>
		        </div>
		      </div>
		    </div>
		  </nav>

			<main class="container px-4 py-4">
	      <h1>Application overview</h1>
	      <p class="lead">A simple overview of the application data for administrative reference.</p>
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
							$no_of_chatbots = $row["no_of_matches"];
						}
						$latest_user = mysqli_fetch_assoc(mysqli_query($con, $sql_get_latest_user));
						$latest_user_added = $latest_user['firstname']. " " .$latest_user['surname']. " ";
						//	$mysql_info = mysqli_get_server_info($con));
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
							<td><?php echo $config['title']; ?></td>
						</tr>
						<tr>
							<th scope="row">Version</th>
							<td><?php echo $config['version']; ?></td>
						</tr>
						<tr>
							<th scope="row">Developer</th>
							<td><?php echo $config['developer']; ?></td>
						</tr>
						<tr>
							<th scope="row">Last Updated</th>
							<td><?php echo $config['last_update']; ?></td>
						</tr>
						<tr>
							<th scope="row">Base URL</th>
							<td><?php echo $config['base_url']; ?></td>
						</tr>
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
							<td><?php printf("Server version: %s\n", mysqli_get_server_info($con)); ?></td>
						</tr>
						<tr>
							<th scope="row">Matches Recorded</th>
							<td><?php echo $no_of_matches + " of " + ($prelim_fixtures + $knockout_fixtures); ?></td>
						</tr>
						<tr>
							<th scope="row">Users Playing</th>
							<td><?php echo $no_of_users; ?></td>
						</tr>
						<tr>
							<th scope="row">Latest User</th>
							<td><?php echo $latest_user_added; ?></td>
						</tr>
						</table>
					</div>
      	</div><!--row-->
				<!-- Site footer -->
				<footer class="mt-auto">
					<hr>
					<p class="small fw-light">Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022">FIFA World Cup 2022™</a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
				</footer>
			</main>

  </body>
</html>
