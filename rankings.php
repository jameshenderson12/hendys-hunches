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
		<title>Hendy's Hunches: Rankings</title>
    <?php include "php/config.php" ?>
		<?php include "php/process.php" ?>
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
		              <a class="nav-link active" aria-current="page" href="rankings.php">Rankings</a>
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
	      <h1>Rankings</h1>

	      <p class="lead">Check your progress against others.</p>
	      <p class="alert alert-success">Congratulations to our winners Chloe (1st), Howard (2nd) and Andrew (3rd).</p>

	      <div class="row">
					<div class="col-xs-12">
		      	<!-- Display table of rankings from process.php -->
						<?php displayRankings(); ?>
						<!--
						<div class="well well-sm">
		        <?php displayInfo(); ?>
		        </div>
					-->
		        <a class="btn btn-light" href="#top" role="button">Return to top</a>
		      </div>
      	</div><!--row-->
				<!-- Site footer -->
				<footer class="mt-auto">
					<hr>
					<p class="small fw-light">Predictions game based on <a href="<?=$competition_url?>"><?=$competition?></a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
				</footer>
			</main>

    </div><!-- /.main-section -->

    <script>
	$(document).ready(function () {	 
	  $("td:eq(0)").css("background","#FFD700").css("text-align", "center").append("£50"); // Create 'rank 1' cell 
	  $("td:eq(3)").css("background","#C0C0C0").css("text-align", "center").append("£35"); // Create 'rank 2' cell
	  $("td:eq(6)").css("background","#CD7F32").css("text-align", "center").append("£23"); // Create 'rank 3' cell	  
	});
    </script>    
  </body>
</html>
