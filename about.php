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
		<title>Hendy's Hunches: About</title>
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
		              <a class="nav-link" href="dashboard.php">Home</a>
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
									<a class="nav-link active" aria-current="page" href="about.php">About</a>
								</li>
		            <li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
										<?php returnAvatar();	?>
									</a>
		              <ul class="dropdown-menu">
										<li><a class="dropdown-item" href="overview.php">Application overview</a></li>
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
	      <h1>About</h1>
	      <p class="lead">A little bit of history...</p>
				<p>Hendy's Hunches has grown over the years from an idea that I had, back in 2005, for a little game to add some fun to World Cup 2006. Today, it has become something of a project that I have developed in my spare time around the clock (see below for the history). The online version began back in 2013 and, despite the coding of it throwing many challenges, I hope that it holds up well from kick-off and that you, colleagues, family and friends all continue to enjoy it. It may not be much to look at but if it adds some fun to the big competitions then that I am very pleased with that.<p>
		      <p>Please be mindful of the game <a href="" data-toggle="modal" data-target="#HHterms">terms and conditions</a> you acknowledged upon registration, but let me wish you good luck with your quest for some prize fund winnings and those all-important bragging rights over others!</p>
		      <p>A special mention of thanks to my very supportive wife, EJ, whose patience has been much appreciated in the hours of our time I've dedicated to this project!</p>
		      <div class="row">
		      <div class="col-sm-12 col-md-4">
						<div class="card">
					  	<img src="img/wc2006-ss.png" alt="World Cup 2006 Game Image" class="card-img-top">
						  <div class="card-body">
						    <h5 class="card-title">World Cup 2006</h5>
						    <p class="card-text">The first origins of Hendy's Hunches - complete with no game name and no supporting website! It was a very monotonous process which consisted simply of sending friends a basic spreadsheet template, having them input their scores for each game and return it to me before the competition began. It was flaky at best although it did seem to be well perceived by those who had taken part. I'd spend a couple of hours a day trauling through each player's spreadsheet and manually calculating points before sending a daily email update of a table with scores and rankings. Despite the tedious effort, it left me thinking that it would be great to repeat the event again some time in the future.</p>
						  </div>
						  <ul class="list-group list-group-flush">
						    <li class="list-group-item"><strong>1st:</strong> Steven Lough/James Henderson</li>
						    <li class="list-group-item"><strong>2nd:</strong> Kirsty Yarnold</li>
						    <li class="list-group-item"><strong>3rd:</strong> Julien Alégre/Andrew Lough</li>
						  </ul>
						</div>
					</div>

					<div class="col-sm-12 col-md-4">
						<div class="card">
					  	<img src="img/wc2014-site.png" alt="World Cup 2014 Game Image" class="card-img-top">
						  <div class="card-body">
						    <h5 class="card-title">World Cup 2014</h5>
						    <p class="card-text">In need of dusting off my programming skills, I thought it would be good to replicate the fun of the game for 2006 - only bigger and better. The hardest things I had to decide on were 1) what format a site would take for it (look and feel), 2) what each player could expect to do (on a basic level), and 3) a points mechanism that would be fair and present good competition. Users were pointed to an online form which they completed all predictions (only for the group stages) in one go. Then, after each game I would enter results into a page and points were given automatically based on players' predictions against a result. A table of rankings kept everyone's points tally. Not too pretty but efficient.</p>
						  </div>
						  <ul class="list-group list-group-flush">
						    <li class="list-group-item"><strong>1st:</strong> Andrew Booth</li>
						    <li class="list-group-item"><strong>2nd:</strong> Nigel Plant</li>
						    <li class="list-group-item"><strong>3rd:</strong> Luke Fecowycz</li>
						  </ul>
						</div>
					</div>

					<div class="col-sm-12 col-md-4">
						<div class="card">
					  	<img src="img/euro2016-site-v3.png" alt="Euro 2016 Game Image" class="card-img-top">
						  <div class="card-body">
						    <h5 class="card-title">Euro 2016</h5>
						    <p class="card-text">Determined to build on the success of the World Cup 2014 version, feedback and positive suggestions has seen significant improvements. Not all changes are widely visible as a lot of the 'under the hood' mechanics have been reworked. Some of the most major improvements include a statistics dashboard, improved rankings system, better in-game communication methods and the ability to make changes to a prediction close up until its match kick-off. All of this and more now sits behind a new and secure login facility. There is always room for improvement so I'm happy to take any comments people have for what could be in a future version. Who will finish in the top places?</p>
						  </div>
						  <ul class="list-group list-group-flush">
						    <li class="list-group-item"><strong>1st:</strong> Jonathan Lamley</li>
						    <li class="list-group-item"><strong>2nd:</strong> Sam McGuigan</li>
						    <li class="list-group-item"><strong>3rd:</strong> Steve Butt/Kirsty Yarnold</li>
						  </ul>
						</div>
					</div>
		    </div>

		    <a class="btn btn-default" href="#top" role="button">Return to top</a>
				<!-- Site footer -->
				<footer class="mt-auto">
					<hr>
					<p class="small fw-light">Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022">FIFA World Cup 2022™</a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
				</footer>
			</main>



    </div><!-- /.main-section -->

  </body>
</html>
