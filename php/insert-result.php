<?php
  include "../php/process.php";
	insertMatchResult();
?>
<!DOCTYPE html>
<html lang="en-GB">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="5;url=../admin/results.php">    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../ico/favicon.ico">
    <title>Hendy's Hunches: Administration</title>
    <!-- Vendor CSS Files -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS Files -->
    <link href="../css/styles.css" rel="stylesheet">  
  
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
      label {
        font-size: 1em;
        }	  
      .dropdown-header {
        font-weight: bold;
        font-style: italic;
      }
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
  
	<!-- Main Content Section -->
	<main id="main" class="main">

		<div class="pagetitle d-flex justify-content-between">
		  <nav>
		  <h1>Record Match Results (Admin)</h1>
			<!-- <ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="home.php">Home</a></li>
			<li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
			<li class="breadcrumb-item active">Part #3 - 11.30</li>
			</ol> -->
		  </nav> 
		</div><!-- End Page Title -->

		<section class="section">
			<p class="lead">Admin page to record match results and update the game.</p>       
      <h2>Success</h2>
      <div class="row">	            
        <div class="col-xs-12"> 
          <p>The match result has been recorded successfully.</p>
          <p>Redirecting to the administration results page...</p><div class="spinner"></div>
        </div><!--col-xs-12-->
      </div><!--row-->
    </section> 

    <!-- Footer -->
    <footer id="footer" class="footer mt-4">
    <div class="copyright">        
        <!-- <p>Predictions game based on <a href="<?=$competition_url?>" class="text-white"><?=$competition?></a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>         -->
    </div>
    </footer><!-- End Footer -->

  <!-- Vendor JS Files -->
  <!-- <script src="vendor/apexcharts/apexcharts.min.js"></script> -->
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- <script src="vendor/chart.js/chart.umd.js"></script>
  <script src="vendor/echarts/echarts.min.js"></script> -->
  <script src="../vendor/progressbar/progressbar.js"></script>  
  <!-- <script async src="vendor/bootbox/bootbox.min.js"></script>
  <script async src="vendor/lodash/lodash.min.js"></script> -->
  <!-- <script async src="vendor/highlight-text/highlight-text.js"></script> -->
  <!-- <script async src="js/confetti.js"></script> -->
  <script src="../js/multi-step-form.js"></script>
  <!-- Template Main JS File -->
  <script src="../js/main.js"></script>

  </body>
</html>