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
		<title>Hendy's Hunches: Dashboard</title>
    <?php include "php/config.php" ?>		
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu|Lora">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
		<link rel="stylesheet" href="css/default.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
  </head>

  <body>
		<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Offcanvas navbar large">
		    <div class="container">
					<img src="ico/favicon.ico" class="img-fluid bg-light mx-2" style="--bs-bg-opacity: 0.80" width="50px">
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
		              <a class="nav-link" href="predictions.php">My Predictions</a>
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
		                <li><a class="dropdown-item" href="change-password.php">Change password</a></li>
		                <!--<li><a class="dropdown-item" href="#">Another action</a></li>-->
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

			<?php
					// Connect to the database
					include 'php/db-connect.php';

					// Set up variable to capture result of SQL query to retrieve data from database tables
					$sql_getuserinfo = "SELECT live_user_predictions_groups.*, live_user_information.avatar, live_user_information.faveteam, live_user_information.fieldofwork, live_user_information.location, live_user_information.tournwinner, live_user_information.currpos
															FROM live_user_predictions_groups INNER JOIN live_user_information
															ON live_user_predictions_groups.id = live_user_information.id
															WHERE live_user_predictions_groups.id = '".$_SESSION["id"]."'";

					$userdata = mysqli_fetch_assoc(mysqli_query($con, $sql_getuserinfo));
					$uppCaseFN = ucfirst($userdata["firstname"]);
					$uppCaseSN = ucfirst($userdata["surname"]);
					$userid = $userdata["id"];
					$avatar = $userdata["avatar"];
					$fieldofwork = $userdata["fieldofwork"];
					$location = $userdata["location"];
					$faveteam = $userdata["faveteam"];
					$tournwinner = $userdata["tournwinner"];
					$currentpos = ordinal($userdata["currpos"]);
					$pointstotal = $userdata["points_total"];

					// Function for adding correct extention to a number
					function ordinal($number) {
						$ends = array('th','st','nd','rd','th','th','th','th','th','th');
						if ($number == "N/A") {
							return $number;
						}
						if ((($number % 100) >= 11) && (($number%100) <= 13))
							return $number. 'th';
						else
							return $number. $ends[$number % 10];
					}
			?>

			<main class="container px-4 py-4">
	      <h1>My Dashboard</h1>
	      <p class="lead">Use the dashboard to track your progress.</p>
					<div class="alert alert-secondary">
						<h3>C'mon James, where's my updated score?</h3>
						<p>The site is pending an update which includes information added to the dashboard and the ability to view yours and others' predictions via the rankings page. However, it is anticipated this update will be applied this evening and therefore scores from today's matches will be updated after all games have been played. This will occur for today only. In future days, results will be updated as soon as possible after the match has ended. Thanks for your patience while things get up and running.</p>
					</div>
					<div class="row g-4">
						<div class="col-lg-3">
							<div class="card">
								<img src="<?php echo $avatar ?>" id="avatar" class="img-fluid mx-auto p-2" alt="User Avatar" name="User Avatar" width="100">
								<div class="card-body">
									<h5 class="card-title" style="text-align: center; font-weight: bolder; margin:-15px 0px;"><?php printf("%s<span class='mx-2' style='color:#CCC;'>|</span>%s pts", $currentpos, $pointstotal); ?></h5>
								</div>
								<ul class="list-group list-group-flush">
									<li class="list-group-item"><?php printf ("<strong>Backed to win:</strong><br> %s", $tournwinner); ?></li>
									<li class="list-group-item"><?php printf ("<strong>Favourite team:</strong><br> %s", $faveteam); ?></li>
									<li class="list-group-item"><?php printf ("<strong>Location:</strong><br> %s", $location); ?></li>
									<li class="list-group-item"><?php printf ("<strong>Field of work:</strong><br> %s", $fieldofwork); ?></li>
								</ul>
								<div class="card-body">
									<a href="#" class="card-link">View Rankings</a>
									<a href="#" class="card-link">View My Predictions</a>
								</div>
							</div>
						</div>
						<div class="col-md-8 col-lg-6">
						</div>
						<div class="col-lg-3">
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
