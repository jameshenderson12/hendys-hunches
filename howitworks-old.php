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
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-119623195-1"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'UA-119623195-1');
	</script>	  
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Hendy's Hunches: Predictions Game">
	<meta name="author" content="James Henderson">
    <link rel="shortcut icon" href="ico/favicon.ico">

    <title>Hendy's Hunches: How It Works</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/custom.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
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
          <img src="img/hh-favicon-2018.jpg" class="img-responsive" style="margin: 0px 20px 0px; height:50px">
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left" style="margin-left: 15px;">
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="predictions.php">Submit Predictions</a></li>
            <li><a href="rankings.php">Rankings</a></li>
            <li class="active"><a href="howitworks.php">How It Works</a></li>
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
          <img src="img/hh-favicon-2018.jpg" class="img-responsive" style="margin: 0px 20px 0px; height:50px">
        </div>
        <div id="navbar2" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left">
            <li><a href="dashboard.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>&nbsp;&nbsp;Home</a></li>
            <li><a href="predictions.php"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>&nbsp;&nbsp;My Predictions</a></li>
            <li><a href="rankings.php"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>&nbsp;&nbsp;Rankings</a></li>
            <li class="active"><a href="howitworks.php"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp;How It Works</a></li>
			<li><a href="about.php"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>&nbsp;&nbsp;About</a></li>
            <li><a href="php/logout.php"><span class='glyphicon glyphicon-user' aria-hidden="true"></span>&nbsp;&nbsp;Logout</a></li>                        
          </ul>
        </div>
      </div>
    </nav>
    
  	<div id="main-section" class="col-md-10 col-md-offset-1">
      <h1 class="page-header">How It Works</h1>
      <p>The below indicates how best to approach this game and summarises each page:</p>
      <ol>
      <li>Register to play (you're already registered! <span class="glyphicon glyphicon-thumbs-up"></span>)</li>
      <li>Login using your registered username and password (you're already logged in! <span class="glyphicon glyphicon-thumbs-up"></span>)</li>
      <li>Go to the 'My Predictions' page to start recording/updating your predictions</li>
      <li>Check the 'Rankings' page shortly after each match to see where you and your colleagues, friends or family stand</li>
      </ol>
      <div class="well well-sm">      
      <p><strong>Home:</strong> A dashboard allowing players to interact via message board and Twitter feed. Also contains all the latest statistics for the site during each day of the competition. It may appear a bit bare to begin with until players sign up and the first match begins.</p>
      <p><strong>My Predictions: </strong> To make your predictions, enter a score value into each box below. You can change score values for a game up until 1 hour before its kick-off. After which, the scores are “locked down” and won’t be editable. Remember to hit the 'Update my predictions' button to save your scores. Any game that doesn’t have a prediction will be awarded a default 0 points.</p>
      <p><strong>Rankings:</strong> After the result of every match, the rankings table will add points to players scores (depending on their predictions) and update players positions automatically. Players can then check their progress against everyone else. To see the possibilities for how points are awarded, based on predictions, see the 'Scoring' section below. Please be patient in allowing a little time shortly after each match for positions to be updated. You will not appear in the rankings table until you have submitted at least 1 prediction.</p>
      <p><strong>How It Works:</strong> Details of how to play and scoring information.</p>
      <p><strong>About:</strong> A light-hearted look at the background to the game.</p>
      <!--<p>Quiz: ??</p>-->
      </div>
      <h2>Scoring</h2>
      <p>For any match, you can be awarded either 0, 1, 2, 3 or 7 points.</p>
      <p>The different scenarios for points scoring are as follows:</p>
      <ol type="A">
      <li><strong>1 point</strong> is awarded if you correctly predict either the home or away score (goals).</li>
      <li><strong>2 points</strong> are awarded if you correctly predict any match outcome of home win, away win or draw.</li>
      <li><strong>3 points</strong> are awarded if you correctly predict the match outcome and either the home or away score (scenario A + B).</li>
      <li><strong>7 points</strong> are awarded if you correctly predict both home and away scores. In this case, you should take a bow and light up a cigar!</li>
      </ol>
      <p>Examples of what you would be awarded, for a given prediction and result, are shown below in the following table:</p>
      <table class="table table-bordered table-condensed table-hover">
      <tr><th>You Predict</th><th>Match Result</th><th>Description</th><th>Points Awarded</th></tr>      
      <tr class="success"><td>1 - 0</td><td>1 - 0</td><td>Home win, both correct scores and identical result predicted</td><td>7</td></tr>
      <tr class="success"><td>1 - 2</td><td>1 - 2</td><td>Away win, both correct scores and identical result predicted</td><td>7</td></tr>
      <tr class="success"><td>1 - 1</td><td>1 - 1</td><td>Draw, both correct scores and identical result predicted</td><td>7</td></tr>                       
      <tr class="warning"><td>3 - 1</td><td>3 - 0</td><td>Home win and correct home score predicted</td><td>3</td></tr>
      <tr class="warning"><td>3 - 2</td><td>4 - 2</td><td>Home win and correct away score predicted</td><td>3</td></tr>
      <tr class="warning"><td>0 - 2</td><td>0 - 3</td><td>Away win and correct home score predicted</td><td>3</td></tr>                    
      <tr class="warning"><td>1 - 2</td><td>0 - 2</td><td>Away win and correct away score predicted</td><td>3</td></tr>    
      <tr class="warning"><td>1 - 0</td><td>2 - 1</td><td>Home win predicted</td><td>2</td></tr>
      <tr class="warning"><td>0 - 3</td><td>1 - 2</td><td>Away win predicted</td><td>2</td></tr>         
      <tr class="warning"><td>3 - 3</td><td>1 - 1</td><td>Draw predicted</td><td>2</td></tr>
      <tr class="warning"><td>0 - 0</td><td>0 - 1</td><td>Home score predicted</td><td>1</td></tr>         
      <tr class="warning"><td>1 - 1</td><td>0 - 1</td><td>Away score predicted</td><td>1</td></tr>  		  
      <tr class="danger"><td>1 - 0</td><td>0 - 2</td><td>Incorrect outcome and no scores predicted</td><td>0</td></tr>
      <tr class="danger"><td>0 - 2</td><td>1 - 1</td><td>Incorrect outcome and no scores predicted</td><td>0</td></tr>
      <tr class="danger"><td>3 - 3</td><td>2 - 1</td><td>Incorrect outcome and no scores predicted</td><td>0</td></tr>
      </table>  
    <a class="btn btn-default" href="#top" role="button">Return to top</a>
            
      <!-- Site footer -->
      <div class="footer">
      <?php include "includes/footer.php" ?>
      </div>            

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>