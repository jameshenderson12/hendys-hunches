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

    <section class="section about-page">
		<div class="about-hero card border-0 mb-4">
			<div class="card-body">
				<span class="about-kicker">Since 2006</span>
				<h2 class="about-headline">A friendly tournament tradition with a competitive streak.</h2>
				<p class="lead">Let's start with a little bit of history...</p>
				<p>Hendy's Hunches has grown over the years from an idea that I had, back in 2005, for a little game to add some fun to World Cup 2006. Today, it has become something of a project that I have developed in my spare time around the clock (see below for the history). The online version began back in 2013 and, despite the coding of it throwing many challenges, I hope that it holds up well from kick-off and that you, colleagues, family and friends all continue to enjoy it. It may not be much to look at but if it adds some fun to the big competitions then that I am very pleased with that.</p>
				<p class="mb-0">A special mention of thanks to my very supportive wife, EJ, whose patience has been much appreciated in the hours of our time I've dedicated to this project!</p>
			</div>
		</div>

		<div class="row g-4 about-grid">
			<div class="col-12 col-md-6 col-lg-4">
				<div class="card about-card h-100">
					<img src="img/wc2006-ss.png" alt="World Cup 2006 Game Image" class="card-img-top">
					<div class="card-body">
						<span class="about-season">FIFA World Cup 2006</span>
						<h5 class="card-title">The spreadsheet era begins</h5>
						<p class="card-text">The first origins of Hendy's Hunches - complete with no game name and no supporting website! It was a very monotonous process which consisted simply of sending friends a basic spreadsheet template, having them input their scores for each game and return it to me before the competition began. It was flaky at best although it did seem to be well perceived by those who had taken part. I'd spend a couple of hours a day trauling through each player's spreadsheet and manually calculating points before sending a daily email update of a table with scores and rankings. Despite the tedious effort, it left me thinking that it would be great to repeat the event again some time in the future.</p>
					</div>
					<ul class="about-insights list-unstyled">
						<li class="about-insights__item about-insights__item--pro">
							<i class="bi bi-plus-circle-fill" aria-hidden="true"></i>
							<span>Kick-started the tradition with plenty of banter.</span>
						</li>
						<li class="about-insights__item about-insights__item--con">
							<i class="bi bi-dash-circle-fill" aria-hidden="true"></i>
							<span>Manual score updates took hours of effort.</span>
						</li>
					</ul>
					<ul class="about-podium list-unstyled">
						<li class="about-podium__item">
							<span class="about-podium__rank">1st</span>
							<span class="about-podium__name">Steven Lough, James Henderson</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">2nd</span>
							<span class="about-podium__name">Kirsty Yarnold</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">3rd</span>
							<span class="about-podium__name">Julien Alégre, Andrew Lough</span>
						</li>
					</ul>
				</div>
			</div>

			<div class="col-12 col-md-6 col-lg-4">
				<div class="card about-card h-100">
					<img src="img/wc2014-site.png" alt="World Cup 2014 Game Image" class="card-img-top">
					<div class="card-body">
						<span class="about-season">FIFA World Cup 2014</span>
						<h5 class="card-title">The online leap</h5>
						<p class="card-text">In need of dusting off my programming skills, I thought it would be good to replicate the fun of the game for 2006 - only bigger and better. The hardest things I had to decide on were 1) what format a site would take for it (look and feel), 2) what each player could expect to do (on a basic level), and 3) a points mechanism that would be fair and present good competition. Users were pointed to an online form which they completed all predictions (only for the group stages) in one go. Then, after each game I would enter results into a page and points were given automatically based on players' predictions against a result. A table of rankings kept everyone's points tally. Not too pretty but efficient.</p>
					</div>
					<ul class="about-insights list-unstyled">
						<li class="about-insights__item about-insights__item--pro">
							<i class="bi bi-plus-circle-fill" aria-hidden="true"></i>
							<span>Online submissions made joining the game effortless.</span>
						</li>
						<li class="about-insights__item about-insights__item--con">
							<i class="bi bi-dash-circle-fill" aria-hidden="true"></i>
							<span>Predictions were limited to the group stages only.</span>
						</li>
					</ul>
					<ul class="about-podium list-unstyled">
						<li class="about-podium__item">
							<span class="about-podium__rank">1st</span>
							<span class="about-podium__name">Andrew Booth</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">2nd</span>
							<span class="about-podium__name">Nigel Plant</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">3rd</span>
							<span class="about-podium__name">Luke Fecowycz</span>
						</li>
					</ul>
				</div>
			</div>

			<div class="col-12 col-md-6 col-lg-4">
				<div class="card about-card h-100">
					<img src="img/euro2016-site-v3.png" alt="Euro 2016 Game Image" class="card-img-top">
					<div class="card-body">
						<span class="about-season">UEFA Euro 2016</span>
						<h5 class="card-title">Feature-rich fan favorite</h5>
						<p class="card-text">Determined to build on the success of the World Cup 2014 version, feedback and positive suggestions has seen significant improvements. Not all changes are widely visible as a lot of the 'under the hood' mechanics have been reworked. Some of the most major improvements include a statistics dashboard, improved rankings system, better in-game communication methods and the ability to make changes to a prediction close up until its match kick-off. All of this and more now sits behind a new and secure login facility. There is always room for improvement so I'm happy to take any comments people have for what could be in a future version. Who will finish in the top places?</p>
					</div>
					<ul class="about-insights list-unstyled">
						<li class="about-insights__item about-insights__item--pro">
							<i class="bi bi-plus-circle-fill" aria-hidden="true"></i>
							<span>Richer stats and flexible edits kept everyone engaged.</span>
						</li>
						<li class="about-insights__item about-insights__item--con">
							<i class="bi bi-dash-circle-fill" aria-hidden="true"></i>
							<span>More features meant more upkeep behind the scenes.</span>
						</li>
					</ul>
					<ul class="about-podium list-unstyled">
						<li class="about-podium__item">
							<span class="about-podium__rank">1st</span>
							<span class="about-podium__name">Jonathan Lamley</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">2nd</span>
							<span class="about-podium__name">Sam McGuigan</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">3rd</span>
							<span class="about-podium__name">Steve Butt, Kirsty Yarnold</span>
						</li>
					</ul>
				</div>
			</div>

			<div class="col-12 col-md-6 col-lg-4">
				<div class="card about-card h-100">
					<img src="img/hh-logo-2024.jpg" alt="FIFA World Cup 2018 Game Image" class="card-img-top">
					<div class="card-body">
						<span class="about-season">FIFA World Cup 2018</span>
						<h5 class="card-title">The community grows</h5>
						<p class="card-text">More players, tighter competition, and a thriving scoreboard made this edition one of the most competitive yet.</p>
					</div>
					<ul class="about-insights list-unstyled">
						<li class="about-insights__item about-insights__item--pro">
							<i class="bi bi-plus-circle-fill" aria-hidden="true"></i>
							<span>The growing community brought nonstop match chatter.</span>
						</li>
						<li class="about-insights__item about-insights__item--con">
							<i class="bi bi-dash-circle-fill" aria-hidden="true"></i>
							<span>Points were harder to come by with so many sharp predictors.</span>
						</li>
					</ul>
					<ul class="about-podium list-unstyled">
						<li class="about-podium__item">
							<span class="about-podium__rank">1st</span>
							<span class="about-podium__name">Nick Chandler</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">2nd</span>
							<span class="about-podium__name">Snigdha Dutta, Sonia Fernandez</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">3rd</span>
							<span class="about-podium__name">Daniel Waite</span>
						</li>
					</ul>
				</div>
			</div>

			<div class="col-12 col-md-6 col-lg-4">
				<div class="card about-card h-100">
					<img src="img/qatar-2022-logo.png" alt="FIFA World Cup 2022 Game Image" class="card-img-top">
					<div class="card-body">
						<span class="about-season">FIFA World Cup 2022</span>
						<h5 class="card-title">Global spotlight</h5>
						<p class="card-text">A fast-paced tournament where every last-minute goal kept the predictions on edge.</p>
					</div>
					<ul class="about-insights list-unstyled">
						<li class="about-insights__item about-insights__item--pro">
							<i class="bi bi-plus-circle-fill" aria-hidden="true"></i>
							<span>Rapid updates kept the leaderboard feeling alive.</span>
						</li>
						<li class="about-insights__item about-insights__item--con">
							<i class="bi bi-dash-circle-fill" aria-hidden="true"></i>
							<span>Quick turnarounds left little time for last tweaks.</span>
						</li>
					</ul>
					<ul class="about-podium list-unstyled">
						<li class="about-podium__item">
							<span class="about-podium__rank">1st</span>
							<span class="about-podium__name">Chloe McCandlish</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">2nd</span>
							<span class="about-podium__name">Howard Kilbourn</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">3rd</span>
							<span class="about-podium__name">Andrew Lough</span>
						</li>
					</ul>
				</div>
			</div>

			<div class="col-12 col-md-6 col-lg-4">
				<div class="card about-card h-100">
					<img src="img/germany-2024-logo-md.png" alt="UEFA Euro 2024 Game Image" class="card-img-top">
					<div class="card-body">
						<span class="about-season">UEFA Euro 2024</span>
						<h5 class="card-title">Next chapter loading</h5>
						<p class="card-text">The latest edition is underway. Who will earn the next bragging rights?</p>
					</div>
					<ul class="about-insights list-unstyled">
						<li class="about-insights__item about-insights__item--pro">
							<i class="bi bi-plus-circle-fill" aria-hidden="true"></i>
							<span>Fresh format ideas kept predictions feeling new again.</span>
						</li>
						<li class="about-insights__item about-insights__item--con">
							<i class="bi bi-dash-circle-fill" aria-hidden="true"></i>
							<span>Still fine-tuning the scoring rules for next time.</span>
						</li>
					</ul>
					<ul class="about-podium list-unstyled">
						<li class="about-podium__item">
							<span class="about-podium__rank">1st</span>
							<span class="about-podium__name">Jonathan Lamley</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">2nd</span>
							<span class="about-podium__name">Paul Hendrick</span>
						</li>
						<li class="about-podium__item">
							<span class="about-podium__rank">3rd</span>
							<span class="about-podium__name">David Holmes</span>
						</li>
					</ul>
				</div>
			</div>

			<div class="col-12 col-md-6 col-lg-4">
				<div class="card about-card h-100">
					<img src="img/hh-logo-2024.jpg" alt="FIFA World Cup 2026 Game Image" class="card-img-top">
					<div class="card-body">
						<span class="about-season">FIFA World Cup 2026</span>
						<h5 class="card-title">The next horizon</h5>
						<p class="card-text">Planning is underway for the biggest tournament yet. Expect more teams, more matches, and more chances to climb the leaderboard.</p>
					</div>
					<ul class="about-insights list-unstyled">
						<li class="about-insights__item about-insights__item--pro">
							<i class="bi bi-plus-circle-fill" aria-hidden="true"></i>
							<span>Expanded format should bring a wider mix of matchups.</span>
						</li>
						<li class="about-insights__item about-insights__item--con">
							<i class="bi bi-dash-circle-fill" aria-hidden="true"></i>
							<span>Final schedule and scoring tweaks are still TBC.</span>
						</li>
					</ul>
					<ul class="about-podium list-unstyled">
						<li class="about-podium__item about-podium__item--pending">
							<span class="about-podium__rank">1st</span>
							<span class="about-podium__name">Pending</span>
						</li>
						<li class="about-podium__item about-podium__item--pending">
							<span class="about-podium__rank">2nd</span>
							<span class="about-podium__name">TBC</span>
						</li>
						<li class="about-podium__item about-podium__item--pending">
							<span class="about-podium__rank">3rd</span>
							<span class="about-podium__name">TBC</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
    </section>        
    </div>
  </div>
</main>

<!-- Footer -->
<?php include "php/footer.php" ?>
