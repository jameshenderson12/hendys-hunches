<?php
// Start the session
session_start();
if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
	header ("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Hendy's Hunches: Predictions Game">
	<meta name="author" content="James Henderson">
    <link rel="icon" href="ico/favicon.ico">

    <title>Hendy's Hunches: Rankings</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/custom.css" rel="stylesheet">
   	<link class="include" rel="stylesheet" type="text/css" href="css/jquery.jqplot.min.css" />
	<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.2.min.js"></script>
	<?php include 'php/process.php'; ?>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style type="text/css">
	.floatRight { 
		float: right; 
		position: relative;
		height: 25px;
		width: 25px;
	}	
	.text {
		position: relative;
		float: right;
		font-size: 0.75em;
	}
	@media only screen and (max-width: 320px) {
		table th:nth-child(5), table td:nth-child(5),
		table th:nth-child(3), table td:nth-child(3) {display: none;}		
	}
	</style>
  </head>

  <body>
  
    <!-- Navigation menu for lg, md display -->
    <nav class="navbar navbar-inverse navbar-fixed-top col-md-12 hidden-xs">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!--<a class="navbar-brand" href="#">Project name</a>-->
          <img src="img/hh-logo-v8.png" class="img-responsive" style="margin: 5px 0px 0px; height:45px">
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left" style="margin-left: 15px;">
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="predictions.php">My Predictions</a></li>
            <li class="active"><a href="rankings.php">Rankings</a></li>
            <li><a href="howitworks.php">How It Works</a></li>
			<li><a href="about.php">About</a></li>                        
          </ul>
          <!--
          <form class="navbar-form navbar-right">
            <input type="text" class="form-control" placeholder="Search...">
          </form>-->
        </div>
        <div class="col-md-12">   
           <?php		 
			// Echo session variables that were set on previous page
			echo "<span id='login'><span style='color: white'><p>Logged in as: <span class='glyphicon glyphicon-user'></span>&nbsp;<span style='font-weight:bold'>" . $_SESSION["firstname"] . " " . $_SESSION["surname"] . "</span> ( <a href='php/logout.php'>Logout</a> )</p></span></span>";
          ?>
        </div>
      </div>
    </nav>
    
    <!-- Navigation menu for xs display -->
    <nav class="navbar navbar-inverse navbar-fixed-top col-md-12 visible-xs hidden-lg hidden-md">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar2" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!--<a class="navbar-brand" href="#">Project name</a>-->
          <img src="img/hh-logo-v8.png" class="img-responsive" style="margin: 5px 0px 0px; height:45px">
        </div>
        <div id="navbar2" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left">
            <li><a href="dashboard.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>&nbsp;&nbsp;Home</a></li>
            <li><a href="predictions.php"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>&nbsp;&nbsp;My Predictions</a></li>
            <li class="active"><a href="rankings.php"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>&nbsp;&nbsp;Rankings</a></li>
            <li><a href="howitworks.php"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp;How It Works</a></li>
			<li><a href="about.php"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>&nbsp;&nbsp;About</a></li>            
            <li><a href="php/logout.php"><span class='glyphicon glyphicon-user' aria-hidden="true"></span>&nbsp;&nbsp;Logout</a></li>                        
          </ul>
        </div>
      </div>
    </nav>
      
	<div id="main-section" class="col-md-10 col-md-offset-1">
        <h1 class="page-header">Rankings</h1>
        <!--<p class="lead">Check your position in the rankings table.</p>-->
        <p>Check your position in the rankings table below.</p>
        <p>Curious about anyone else's predictions? Click on their name to display their profile and information.</p>
      
      <div class="row">
      <div class="col-xs-12">
      	<!-- Display table of rankings from process.php -->
		<?php displayRankings(); ?> 
        <div class="well well-sm">
        <?php displayInfo(); ?>
        </div>
        <a class="btn btn-default" href="#top" role="button">Return to top</a>

      </div>
      </div>
     
      <!-- Site footer -->
      <div class="footer">
      <?php include "includes/footer.php" ?>
      </div>       
     
    </div><!-- /.main-section -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
	$(document).ready(function () {	 
	  //$("td:eq(0)").append('<div class="floatRight"><img src="img/red_ros.png" class="img-responsive" /></div>').append('<span class="text">£100</span>');
	  //$("td:eq(0)").append('<div class="floatRight"><img src="img/gold_ros.png" class="img-responsive" /></div>').append('<span class="text">£60</span>');
	  //$("td:eq(5)").append('<div class="floatRight"><img src="img/silver_ros.png" class="img-responsive" /></div>').append('<span class="text">£30</span>');
	  //$("td:eq(10)").append('<div class="floatRight"><img src="img/bronze_ros.png" class="img-responsive" /></div>').append('<span class="text">£20</span>');  		  
	  /*
	  $("td:eq(0)").css("background","#FFFF9E").css("text-align", "center").css("font-size","8px").append("£100"); // Create 'rank 1' cell
	  //$("tr:eq(1)").css("background","#FFFF9E"); // Create gold rank cell	  
	  $("td:eq(6)").css("background","#FFFFA7").css("text-align", "center").css("font-size","8px").append("£50"); // Create 'rank 2' cell
	  $("td:eq(12)").css("background","#FFFFB1").css("text-align", "center").css("font-size","8px").append("£25"); // Create 'rank 3' cell	  
	  $("td:eq(18)").css("background","#FFFFBB").css("text-align", "center").css("font-size","8px").append("£X"); // Create 'rank 4' cell		  
	  $("td:eq(24)").css("background","#FFFFC4").css("text-align", "center").css("font-size","8px").append("£X"); // Create 'rank 5' cell		  
	  $("td:eq(30)").css("background","#FFFFCE").css("text-align", "center").css("font-size","8px").append("£X"); // Create 'rank 6' cell	
  	  $("td:eq(36)").css("background","#FFFFD8").css("text-align", "center").css("font-size","8px").append("£X"); // Create 'rank 7' cell	
	  $("td:eq(42)").css("background","#FFFFE1").css("text-align", "center").css("font-size","8px").append("£X"); // Create 'rank 8' cell	
	  $("td:eq(48)").css("background","#FFFFEB").css("text-align", "center").css("font-size","8px").append("£X"); // Create 'rank 9' cell	
	  $("td:eq(54)").css("background","#FFFFF5").css("text-align", "center").css("font-size","8px").append("£X"); // Create 'rank 10' cell
	  //$("td:eq(-5)").css("text-align", "center").css("font-size","0.85em").append('<span class="text">Cellar Dweller</span>');*/		  
	});
    </script>    
  </body>
</html>