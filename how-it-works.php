<?php
session_start();
$page_title = 'PAGE_NAME';

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
      <h1>How it all works</h1>
        <!-- <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
          <li class="breadcrumb-item active">Part #3 - 11.30</li>
        </ol> -->
      </nav> 
    </div><!-- End Page Title -->

    <section class="section">
		<p class="lead">A detailed overview of how the game works.</p>
		<p>The below indicates how best to approach this game and summarises each page:</p>
	      <ol>
	      <li>Register to play (you're already registered! <span class="bi bi-check2-square text-success"></span>)</li>
	      <li>Login using your registered username and password (you're already logged in! <span class="bi bi-check2-square text-success"></span>)</li>
	      <li>Go to the 'Submit Predictions' page to fill out your predictions</li>
	      <li>Check the 'Rankings' page shortly after each match to see where you and your colleagues, friends or family stand</li>
	      </ol>

	      <p><strong>Home:</strong> A dashboard allowing players to interact via message board and Twitter feed. Also contains all the latest statistics for the site during each day of the competition. It may appear a bit bare to begin with until players sign up and the first match begins.</p>
	      <p><strong>My Predictions: </strong> To make your predictions, enter a score value into each box below. You can change score values for a game up until 1 hour before its kick-off. After which, the scores are “locked down” and won’t be editable. Remember to hit the 'Update my predictions' button to save your scores. Any game that doesn’t have a prediction will be awarded a default 0 points.</p>
	      <p><strong>Rankings:</strong> After the result of every match, the rankings table will add points to players scores (depending on their predictions) and update players positions automatically. Players can then check their progress against everyone else. To see the possibilities for how points are awarded, based on predictions, see the 'Scoring' section below. Please be patient in allowing a little time shortly after each match for positions to be updated. You will not appear in the rankings table until you have submitted at least 1 prediction.</p>
	      <p><strong>How It Works:</strong> Details of how to play and scoring information.</p>
	      <p><strong>About:</strong> A light-hearted look at the background to the game.</p>

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
    </section>
        
    </div>
  </div>
</main>

<!-- Footer -->
<?php include "php/footer.php" ?>