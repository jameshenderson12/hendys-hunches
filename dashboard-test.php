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
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
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
		<link class="include" rel="stylesheet" type="text/css" href="css/jquery.jqplot.min.css" />
		<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.2.min.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
		<?php include 'php/dashboard-items.php' ?>
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
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

			<main class="container px-4 py-4">
				<!--
	      <h1>My Dashboard</h1>
	      <p class="lead mb-4">Use the dashboard to track your progress.</p>
			-->
					<div class="row g-4">
						<div class="col-lg-3">
							<div class="card">
								<div class="card-body">
									<?php displayPersonalInfo() ?>
								</div>
							</div>
						</div>
						<div class="col-md-8 col-lg-6">
						</div>
						<div class="col-lg-3">
							<div class="card">
								<div class="card-body">
									<?php displayBestMovers() ?>
								</div>
							</div>
							<div class="card">
								<div class="card-body">
								</div>
							</div>
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
