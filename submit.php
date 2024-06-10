<?php
session_start();
$page_title = 'Submitting Predictions';

if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
    header("Location: index.php");
    exit();
}
?>

<?php
	include 'php/process.php';
	submitPredictions();
?>

<!DOCTYPE html>
<html lang="en-GB">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="3;url=dashboard.php">
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <meta http-equiv="Content-Type" content="text/html">    
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
    <meta name="keywords" content="football, predictions, game">
	  <title><?= $page_title ?> - Hendy's Hunches</title>
    <link href="ico/favicon.ico" rel="icon">
    <!-- Vendor CSS Files -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS Files -->
    <link href="css/styles.css" rel="stylesheet">
    <!-- Include PHP Config File -->
    <?php include "php/config.php" ?>
    <!--jQuery Files -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <style>
      .spinner {
        height:60px;
        width:60px;
        margin:0px auto;
        position:relative;
        -webkit-animation: rotation .6s infinite linear;
        -moz-animation: rotation .6s infinite linear;
        -o-animation: rotation .6s infinite linear;
        animation: rotation .6s infinite linear;
        border-left:6px solid rgba(0,174,239,.15);
        border-right:6px solid rgba(0,174,239,.15);
        border-bottom:6px solid rgba(0,174,239,.15);
        border-top:6px solid rgba(0,174,239,.8);
        border-radius:100%;
      }

      @-webkit-keyframes rotation {
      from {-webkit-transform: rotate(0deg);}
      to {-webkit-transform: rotate(359deg);}
      }

      @-moz-keyframes rotation {
      from {-moz-transform: rotate(0deg);}
      to {-moz-transform: rotate(359deg);}
      }

      @-o-keyframes rotation {
      from {-o-transform: rotate(0deg);}
      to {-o-transform: rotate(359deg);}
      }

      @keyframes rotation {
      from {transform: rotate(0deg);}
      to {transform: rotate(359deg);}
      }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Offcanvas navbar large">
	<div class="container">
			<img src="img/hh-icon-2024.png" class="img-fluid bg-light mx-2" style="--bs-bg-opacity: 0.80" width="50px">
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
		              <a class="nav-link" aria-current="page" href="dashboard.php">Dashboard</a>
		            </li>
		            <li class="nav-item">
		              <a class="nav-link" href="predictions.php">Submit Predictions</a>
		            </li>
								<li class="nav-item">
		              <a class="nav-link" href="rankings.php">Rankings</a>
		            </li>
								<li class="nav-item">
		              <a class="nav-link" href="how-it-works.php">How It Works</a>
		            </li>
								<li class="nav-item">
									<a class="nav-link" href="about.php">About</a>
								</li>
		          </ul>
		        </div>
		      </div>
		    </div>
		  </nav>


<!-- Main Content Section -->
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between">
    <nav>
      <h1>Submitting Predictions</h1>
        <!-- <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
          <li class="breadcrumb-item active">Part #3 - 11.30</li>
        </ol> -->
      </nav> 
    </div><!-- End Page Title -->

    <section class="section">
    
    <p><span class="badge bg-success">SUCCESS</span> <strong>Predictions recorded.</strong></p>
		<p>Thank you for updating your predictions. You will now be returned to your predictions page.</p>

		<div class="spinner"></div>
    </section>
        
</main>

<!-- Footer -->
<?php include "php/footer.php" ?>