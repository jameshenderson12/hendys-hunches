<?php
session_start();
$page_title = 'About';

if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
    header("Location: index.php");
    exit();
}

include "php/header.php";
include "php/navigation.php";

?>

<!-- Main Content Section -->
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between">
		<nav>
		<h1>About this game</h1>
			<!-- <ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="home.php">Home</a></li>
			<li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
			<li class="breadcrumb-item active">Part #3 - 11.30</li>
			</ol> -->
		</nav> 
    </div><!-- End Page Title -->

    <section class="section">
		<p class="lead">Let's start with a little bit of history...</p>
		<p>Hendy's Hunches has grown over the years from an idea that I had, back in 2005, for a little game to add some fun to World Cup 2006. Today, it has become something of a project that I have developed in my spare time around the clock (see below for the history). The online version began back in 2013 and, despite the coding of it throwing many challenges, I hope that it holds up well from kick-off and that you, colleagues, family and friends all continue to enjoy it. It may not be much to look at but if it adds some fun to the big competitions then that I am very pleased with that.<p>
		      <p>A special mention of thanks to my very supportive wife, EJ, whose patience has been much appreciated in the hours of our time I've dedicated to this project!</p>
		      <div class="row">
		      <div class="col-sm-12 col-md-4">
						<div class="card">
					  	<img src="img/wc2006-ss.png" alt="World Cup 2006 Game Image" class="card-img-top">
						  <div class="card-body">
						    <h5 class="card-title">FIFA World Cup 2006</h5>
						    <p class="card-text">The first origins of Hendy's Hunches - complete with no game name and no supporting website! It was a very monotonous process which consisted simply of sending friends a basic spreadsheet template, having them input their scores for each game and return it to me before the competition began. It was flaky at best although it did seem to be well perceived by those who had taken part. I'd spend a couple of hours a day trauling through each player's spreadsheet and manually calculating points before sending a daily email update of a table with scores and rankings. Despite the tedious effort, it left me thinking that it would be great to repeat the event again some time in the future.</p>
						  </div>
						  <ul class="list-group list-group-flush">
						    <li class="list-group-item"><strong>1st:</strong> Steven Lough, James Henderson</li>
						    <li class="list-group-item"><strong>2nd:</strong> Kirsty Yarnold</li>
						    <li class="list-group-item"><strong>3rd:</strong> Julien Alégre, Andrew Lough</li>
						  </ul>
						</div>
					</div>

					<div class="col-sm-12 col-md-4">
						<div class="card">
					  	<img src="img/wc2014-site.png" alt="World Cup 2014 Game Image" class="card-img-top">
						  <div class="card-body">
						    <h5 class="card-title">FIFA World Cup 2014</h5>
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
						    <h5 class="card-title">UEFA Euro 2016</h5>
						    <p class="card-text">Determined to build on the success of the World Cup 2014 version, feedback and positive suggestions has seen significant improvements. Not all changes are widely visible as a lot of the 'under the hood' mechanics have been reworked. Some of the most major improvements include a statistics dashboard, improved rankings system, better in-game communication methods and the ability to make changes to a prediction close up until its match kick-off. All of this and more now sits behind a new and secure login facility. There is always room for improvement so I'm happy to take any comments people have for what could be in a future version. Who will finish in the top places?</p>
						  </div>
						  <ul class="list-group list-group-flush">
						    <li class="list-group-item"><strong>1st:</strong> Jonathan Lamley</li>
						    <li class="list-group-item"><strong>2nd:</strong> Sam McGuigan</li>
						    <li class="list-group-item"><strong>3rd:</strong> Steve Butt, Kirsty Yarnold</li>
						  </ul>
						</div>
					</div>
					
				<div class="row">

				<div class="col-sm-12 col-md-4">
						<div class="card">
					  	<img src="img/hh-logo-2024.jpg" alt="FIFA World Cup 2018 Game Image" class="card-img-top">
						  <div class="card-body">
						    <h5 class="card-title">FIFA World Cup 2018</h5>
						    <p class="card-text"></p>
						  </div>
						  <ul class="list-group list-group-flush">
						    <li class="list-group-item"><strong>1st:</strong> Nick Chandler</li>
						    <li class="list-group-item"><strong>2nd:</strong> Snigdha Dutta, Sonia Fernandez</li>
						    <li class="list-group-item"><strong>3rd:</strong> Daniel Waite</li>
						  </ul>
						</div>
					</div>	

					<div class="col-sm-12 col-md-4">
						<div class="card">
					  	<img src="img/qatar-2022-logo.png" alt="FIFA World Cup 2022 Game Image" class="card-img-top">
						  <div class="card-body">
						    <h5 class="card-title">FIFA World Cup 2022</h5>
						    <p class="card-text"></p>
						  </div>
						  <ul class="list-group list-group-flush">
						    <li class="list-group-item"><strong>1st:</strong> Chloe McCandlish</li>
						    <li class="list-group-item"><strong>2nd:</strong> Howard Kilbourn</li>
						    <li class="list-group-item"><strong>3rd:</strong> Andrew Lough</li>
						  </ul>
						</div>
					</div>	
					
					<div class="col-sm-12 col-md-4">
						<div class="card">
					  	<img src="img/germany-2024-logo-md.png" alt="UEFA Euro 2024 Game Image" class="card-img-top">
						  <div class="card-body">
						    <h5 class="card-title">UEFA Euro 2024</h5>
						    <p class="card-text"></p>
						  </div>
						  <ul class="list-group list-group-flush">
						    <li class="list-group-item"><strong>1st:</strong> ??</li>
						    <li class="list-group-item"><strong>2nd:</strong> ??</li>
						    <li class="list-group-item"><strong>3rd:</strong> ??</li>
						  </ul>
						</div>
					</div>	
					
				</div>
		    </div>
    </section>        
    </div>
  </div>
</main>

<!-- Footer -->
<?php include "php/footer.php" ?>