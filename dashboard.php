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
		<?php include 'php/dashboard-items.php' ?>
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
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Status</h5>
									<!--<?php checkSubmitted() ?>-->

									<div class="row">
										<div class="col-sm-4">
											<p class="small">Matches recorded:</p>
										</div>
										<div class="col-sm-8">
											<?php displayMatchesRecorded() ?>
										</div>
										<div class="alert alert-success" role="alert">
											<h4><strong>Game complete!</strong></h4>
											<p>Congratulations to the winners who share a ??108 total prize fund.</p>
											<table class='table table-condensed table-bordered table-striped'>
											<tr><td>1st</td><td>??50</td><td>Chloe McCandlish-Boyd</td></tr>
											<tr><td>2nd</td><td>??35</td><td>Howard Kilbourn</td></tr>
											<tr><td>3rd</td><td>??23</td><td>Andrew Lough</td></tr>
											</table>
											<p>Thank you all for your participation and well done!</p>
										</div>
										<p>It is possible that this game will take place next year for the FIFA Women???s World Cup Australia & New Zealand 2023. If you have any comments or suggestions for new features or improvements you'd like to see, please feel free to drop me a quick line at <a href="mailto:jameshenderson12@hotmail.com">jameshenderson12@hotmail.com</a>.</p>
									</div>
									<!--
									<div class="row">
										<div class="col-sm-3">
											<p class="small">Group fixtures:</p>
										</div>
										<div class="col-sm-9">
											<?php displayGroupMatchesPlayed() ?>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-3">
											<p class="small">RO16 fixtures:</p>
										</div>
										<div class="col-sm-9">
											<?php displayRO16MatchesPlayed() ?>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-3">
											<p class="small">QF fixtures:</p>
										</div>
										<div class="col-sm-9">
											<?php displayQFMatchesPlayed() ?>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-3">
											<p class="small">SF fixtures:</p>
										</div>
										<div class="col-sm-9">
											<?php displaySFMatchesPlayed() ?>
										</div>
									</div>
								-->
								</div>
							</div>
							<div class="card mt-4">
								<div class="card-body">
									<h5 class="card-title">Announcements</h5>

									<!--
									<div class="alert alert-danger alert-dismissible fade show" role="alert">
									  <strong>Deadline 15:00, Sat, 9th Dec</strong> Predict the Quarter Final stage now. You have until 15:00 on Saturday 9th December.
									  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
									</div>
								-->

									<?php displayCharityInformation() ?>
								</div>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Biggest climbers</h5>
									<?php displayBestMovers() ?>
								</div>
							</div>
							<div class="card mt-4">
								<div class="card-body">
									<h5 class="card-title">Biggest droppers</h5>
									<?php displayWorstMovers() ?>
								</div>
							</div>
							<div class="card mt-4">
								<div class="card-body">
									<h5 class="card-title">Current top 5</h5>
									<!--<?php displayTopRankings() ?>-->
								</div>
							</div>
							<div class="card mt-4">
								<div class="card-body">
									<h5 class="card-title">Current bottom 5</h5>
									<!--<?php displayBottomRankings() ?>-->
								</div>
							</div>
							<!--
							<div class="card mt-4">
								<div class="card-body">
									<h5 class="card-title">Social Feed</h5>
										<a class="twitter-timeline" data-lang="en" data-height="600" data-theme="light" href="https://twitter.com/FIFAWorldCup" data-chrome="noheader nofooter noborders">Tweets by FIFAWorldCup</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
								</div>
							</div>
						-->
						</div>
      		</div><!--row-->
				<!-- Site footer -->
				<footer class="mt-auto">
					<hr>
					<p class="small fw-light">Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022">FIFA World Cup 2022???</a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
				</footer>
			</main>

  </body>
</html>
