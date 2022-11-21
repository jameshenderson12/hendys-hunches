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

    <title>Hendy's Hunches: About</title>

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
            <li><a href="howitworks.php">How It Works</a></li>                        
            <li class="active"><a href="about.php">About</a></li>
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
            <li><a href="howitworks.php"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp;How It Works</a></li>
			<li class="active"><a href="about.php"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>&nbsp;&nbsp;About</a></li>            
            <li><a href="php/logout.php"><span class='glyphicon glyphicon-user' aria-hidden="true"></span>&nbsp;&nbsp;Logout</a></li>                        
          </ul>
        </div>
      </div>
    </nav>
    
  	<div id="main-section" class="col-md-10 col-md-offset-1">
      <h1 class="page-header">About</h1>
	  <p>Hendy's Hunches has grown over the years from an idea that I had, back in 2005, for a little game to add some fun to World Cup 2006. Today, it has become something of a project that I have developed in my spare time around the clock (see below for the history). The online version began back in 2013 and, despite the coding of it throwing many challenges, I hope that it holds up well from kick-off and that you, colleagues, family and friends all continue to enjoy it. It may not be much to look at but if it adds some fun to the big competitions then that I am very pleased with that.<p>      
      <p>Please be mindful of the game <a href="" data-toggle="modal" data-target="#HHterms">terms and conditions</a> you acknowledged upon registration, but let me wish you good luck with your quest for some prize fund winnings and those all-important bragging rights over others!</p>
      <p>A special mention of thanks to my very supportive wife, EJ, whose patience has been much appreciated in the hours of our time I've dedicated to this project!</p>
      <div class="row">
      <div class="col-sm-12 col-md-4">
        <div class="thumbnail">
          <img src="img/wc2006-ss.png" alt="World Cup 2006 Game Image">
          <div class="caption">
            <h4>World Cup 2006</h4>
            <p>The first origins of Hendy's Hunches - complete with no game name and no supporting website! It was a very monotonous process which consisted simply of sending friends a basic spreadsheet template, having them input their scores for each game and return it to me before the competition began. It was flaky at best although it did seem to be well perceived by those who had taken part. I'd spend a couple of hours a day trauling through each player's spreadsheet and manually calculating points before sending a daily email update of a table with scores and rankings. Despite the tedious effort, it left me thinking that it would be great to repeat the event again some time in the future.</p>
            <p><strong>Winners:</strong> Steven Lough/James Henderson</p>
            <p><strong>Runner-Up:</strong> Kirsty Yarnold</p>
            <p><strong>Third Place:</strong> Julien Alégre/Andrew Lough</p>
            <p><strong>Last Place:</strong> Dan Gordon</p>
          </div>
        </div>
      </div>
    
      <div class="col-sm-12 col-md-4">
        <div class="thumbnail">
          <img src="img/wc2014-site.png" alt="World Cup 2014 Game Image">
          <div class="caption">
            <h4>World Cup 2014</h4>
            <p>In need of dusting off my programming skills, I thought it would be good to replicate the fun of the game for 2006 - only bigger and better. The hardest things I had to decide on were 1) what format a site would take for it (look and feel), 2) what each player could expect to do (on a basic level), and 3) a points mechanism that would be fair and present good competition. Users were pointed to an online form which they completed all predictions (only for the group stages) in one go. Then, after each game I would enter results into a page and points were given automatically based on players' predictions against a result. A table of rankings kept everyone's points tally. Not too pretty but efficient.</p>
            <p><strong>Winner:</strong> Andrew Booth</p>
            <p><strong>Runner-Up:</strong> Nigel Plant</p>
            <p><strong>Third Place:</strong> Luke Fecowycz</p>
            <p><strong>Last Place:</strong> Alison Naish</p>
          </div>
        </div>
      </div>
      
      <div class="col-sm-12 col-md-4">
        <div class="thumbnail">
          <img src="img/euro2016-site-v3.png" alt="Euro 2016 Game Image">
          <div class="caption">
            <h4>Euro 2016</h4>
            <p>Determined to build on the success of the World Cup 2014 version, feedback and positive suggestions has seen significant improvements. Not all changes are widely visible as a lot of the 'under the hood' mechanics have been reworked. Some of the most major improvements include a statistics dashboard, improved rankings system, better in-game communication methods and the ability to make changes to a prediction close up until its match kick-off. All of this and more now sits behind a new and secure login facility. There is always room for improvement so I'm happy to take any comments people have for what could be in a future version. Who will finish in the top places?</p>
            <p><strong>Winner:</strong> Jonathan Lamley</p>
            <p><strong>Runner-Up:</strong> Sam McGuigan</p>
            <p><strong>Third Place:</strong> Steve Butt/Kirsty Yarnold</p>
            <p><strong>Last Place:</strong> Jono Hilton</p>
          </div>
        </div>
      </div>      
    
    </div>
    
    <a class="btn btn-default" href="#top" role="button">Return to top</a>
      
  <!-- Modal -->
  <div id="HHterms" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Hendy's Hunches: Terms &amp; Conditions</h4>
        </div>
        <div class="modal-body" style="font-size: 0.95em;">
          <img src="img/hh-logo-2018.jpg" class="img-responsive center-block" title="Hendy's Hunches Logo" alt="Hendy's Hunches Logo" style="width: 150px; margin-bottom: 10px;">
          <p>By registering to play Hendy's Hunches, you acknowledge that your participation in this game, and the game itself, is only for fun and light-hearted entertainment.</p>
          <p>Only one registration per person is allowed and there is a participation fee of £5 which is to be paid to James Henderson prior to 14th June, 2018. This participation fee comprises a percentage split of charity donation (charity TBC), prize fund and overheads.</p>
          <p>The game is based upon the 2018 FIFA World Cup Russia tournament (all 64 fixtures).</p>
          <p>There will be a minimum of 3 prize funds and this number may be increased depending on the total number of participants. The number of prize funds available, and their amounts, will be indicated in the rankings table shortly after the game commences. Those participants who occupy a prize fund place after the final tournament fixture will receive the corresponding prize amount shortly thereafter. In the event of a shared spot, prizes will be split.</p>
          <p>You are very welcome to invite family and friends to take part but be aware that any unpaid entrance fees will result in a participant being removed from the game.</p>
        </div>
        <!--
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
        -->
      </div>
    </div>
  </div>                 
      
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
