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
		<title>Hendy's Hunches: Predictions</title>
    <?php include "php/config.php" ?>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Trirong">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>

    <script type="text/javascript">
		function validateFullForm() {
			// Validate the match score inputs
			var x = document.getElementsByTagName("input");
			for (var i = 0; i < x.length; i++) {
				if(x[i].name.indexOf('score') == 0) {
					if ((x[i].value == null) || (x[i].value == "")) {
					alert("Please check your match predictions again as it looks like there are imcomplete scores.");
					x[i].style.border="1px solid red";
					x[i].focus();
					return false;
					}
				}
			}
		}
		// Turn the score fields red if not input (onBlur - focus leaving the field)
		function validateScore(inputID) {
			var x = document.getElementById(inputID);
			if (x.value == null || x.value == "") {
				x.style.border="1px solid red";
				return false;
			}
			else if ((x.value >= 0) && (x.value <= 10)) {
				x.style.border="1px solid green";
			}
			else x.style.border="1px solid red";
		}
		// Reset all guidance borders to original colour
		function resetBorders() {
			var x = document.getElementById("predictionForm");
			for (var i = 0; i < x.length; i++) {
				x.elements[i].style.border="1px solid #CCC";
			}
		}
	</script>

  </head>

  <body>

		<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Offcanvas navbar large">
		    <div class="container">
					<img src="img/hh-favicon-2018.jpg" class="img-responsive" style="margin: 0px 20px 0px; height:50px">
		      <a class="navbar-brand" href="#">Hendy's Hunches</a>
		      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar2" aria-controls="offcanvasNavbar2">
		        <span class="navbar-toggler-icon"></span>
		      </button>
		      <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasNavbar2" aria-labelledby="offcanvasNavbar2Label">
		        <div class="offcanvas-header">
		          <h5 class="offcanvas-title" id="offcanvasNavbar2Label">Offcanvas</h5>
		          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		        </div>
		        <div class="offcanvas-body">
		          <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
		            <li class="nav-item">
		              <a class="nav-link active" aria-current="page" href="dashboard.php">Home</a>
		            </li>
		            <li class="nav-item">
		              <a class="nav-link" href="predictions.php">My Predictions</a>
		            </li>
								<li class="nav-item">
		              <a class="nav-link" href="rankings.php">Rankings</a>
		            </li>
								<li class="nav-item">
		              <a class="nav-link" href="howitworks.php">How It Works</a>
		            </li>
								<li class="nav-item">
									<a class="nav-link" href="about.php">About</a>
								</li>

		            <li class="nav-item dropdown">
		              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
		                <img src="img/scores.jpg" alt="Profile icon" class="img-fluid rounded mr-2" width="20px;">
										<?php
											// Echo session variables that were set on previous page
											echo $_SESSION["firstname"];
										?>
		              </a>
		              <ul class="dropdown-menu">
		                <li><a class="dropdown-item" href="#">Action</a></li>
		                <li><a class="dropdown-item" href="#">Another action</a></li>
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


	<main>
      <h1 class="page-header">My Predictions</h1>
      <!--<p class="lead">Can you correctly predict your way to victory?</p>-->
      <p>To make your predictions, enter a score value into each box below. Remember to hit the 'Update my predictions' button to save your scores.</p>

      <ul>
		  <li>You can change predictions for any game until 1 hour before its kick-off</li>
          <li>You can filter on fixtures from each round</li>
      	  <li>Unentered predictions will result in 0 points being awarded</li>
		  <li>Predictions for knockout fixtures are based on result after 90 minutes only</li>
      </ul>
      <a name="matches"></a><!--anchor point for filters-->
      <form id="predictionForm" name="predictionForm" class="form-horizontal" action="submit.php" method="POST" onSubmit="#">
      <div class="row">
        <div class="col-xs-12">

        <div class="btn-group" role="group" aria-label="Fixture Filters">
          <button id="all" type="button" class="btn btn-default" onClick="showOnly('All')">All Matches</button>
          <!--
          <div class="btn-group" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Group
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              <li id="groupA"><a href="#matches" onClick="showOnly('A')">Group A</a></li>
              <li id="groupB"><a href="#matches" onClick="showOnly('B')">Group B</a></li>
              <li id="groupC"><a href="#matches" onClick="showOnly('C')">Group C</a></li>
              <li id="groupD"><a href="#matches" onClick="showOnly('D')">Group D</a></li>
              <li id="groupE"><a href="#matches" onClick="showOnly('E')">Group E</a></li>
              <li id="groupF"><a href="#matches" onClick="showOnly('F')">Group F</a></li>
            </ul>
          </div>-->


          <div class="btn-group" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Knockout Stage
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              <li id="groups"><a href="#matches" onClick="showOnly('Groups')">All Groups</a></li>
              <li id="ro16"><a href="#matches" onClick="showOnly('RO16')">Round of 16</a></li>
              <li id="qf"><a href="#matches" onClick="showOnly('QF')">Quarter Finals</a></li>
              <li id="sf"><a href="#matches" onClick="showOnly('SF')">Semi Finals</a></li>
			  <li id="po"><a href="#matches" onClick="showOnly('PO')">3rd Place Playoff</a></li>
              <li id="final"><a href="#matches" onClick="showOnly('Final')">Final</a></li>
            </ul>
          </div>
          <!--
          <div class="btn-group hidden-xs" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Date
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              <li><a href="#matches" onClick="showOnly('10Jun')">10th June</a></li>
              <li><a href="#matches" onClick="showOnly('11Jun')">11th June</a></li>
              <li><a href="#matches" onClick="showOnly('12Jun')">12th June</a></li>
              <li><a href="#matches" onClick="showOnly('13Jun')">13th June</a></li>
              <li><a href="#matches" onClick="showOnly('14Jun')">14th June</a></li>
              <li><a href="#matches" onClick="showOnly('15Jun')">15th June</a></li>
              <li><a href="#matches" onClick="showOnly('16Jun')">16th June</a></li>
              <li><a href="#matches" onClick="showOnly('17Jun')">17th June</a></li>
              <li><a href="#matches" onClick="showOnly('18Jun')">18th June</a></li>
              <li><a href="#matches" onClick="showOnly('19Jun')">19th June</a></li>
              <li><a href="#matches" onClick="showOnly('20Jun')">20th June</a></li>
              <li><a href="#matches" onClick="showOnly('21Jun')">21st June</a></li>
              <li><a href="#matches" onClick="showOnly('22Jun')">22nd June</a></li>
              <li><a href="#matches" onClick="showOnly('25Jun')">25th June</a></li>
              <li><a href="#matches" onClick="showOnly('26Jun')">26th June</a></li>
              <li><a href="#matches" onClick="showOnly('27Jun')">27th June</a></li>
              <li><a href="#matches" onClick="showOnly('30Jun')">30th June</a></li>
              <li><a href="#matches" onClick="showOnly('01Jul')">1st July</a></li>
              <li><a href="#matches" onClick="showOnly('02Jul')">2nd July</a></li>
              <li><a href="#matches" onClick="showOnly('03Jul')">3rd July</a></li>
              <li><a href="#matches" onClick="showOnly('06Jul')">6th July</a></li>
              <li><a href="#matches" onClick="showOnly('07Jul')">7th July</a></li>
              <li><a href="#matches" onClick="showOnly('10Jul')">10th July</a></li>
            </ul>
          </div>-->

		</div>

	  	<table class="table table-sm">
				<!--
        <tr>
        <th width="10%">Info</th>
        <th width="18%">Home</th>
        <th width="10%">HS</th>
        <th width="4%">v</th>
        <th width="10%">AS</th>
        <th width="18%">Away</th>
        <th colspan="5" width="60%"></th>
        <th width="30%">KO &amp; Venue</th>
        </tr>

				<tr>
        <th>Match</th>
        <th>Home Team</th>
				<th>Home Flag</th>
        <th>Home Score</th>
        <th>v</th>
        <th>Away Score</th>
				<th>Away Flag</th>
        <th>Away Team</th>
        <th></th>
        </tr>
-->
				<tr id="match1">
        <td>1</td>
	    	<td><?php echo $A1; ?></td>
        <td><img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"></td>
      	<td><input type="text" id="score1_p" name="score1_p" class="form-control" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score2_p" name="score2_p" class="form-control" /></td>
				<td><img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"></td>
      	<td><?php echo $A2; ?></td>
      	<td class="date-venue"><?php echo "$_16, $_20Nov, $venue1"; ?></td>
      	</tr>
				<!--
      	<tr id="match1">
        <td class="date-venue">Match 1<br>Group A</td>
	    	<td class="left-team">
        <img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"><?php echo $A1; ?></td>
      	<td><input type="text" id="score1_p" name="score1_p" class="left-score score-field form-control input-sm" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score2_p" name="score2_p" class="right-score score-field form-control input-sm" /></td>
      	<td class="right-team">
        <img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"><?php echo $A2; ?></td>
      	<td class="date-venue"><?php echo "$_1pm, $_20Nov, $venue1"; ?></td>
      	</tr>
-->
      	<tr>
        <td class="date-venue">Match 2<br>Group A</td>
      	<td class="left-team">
        <img src="<?php echo $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"><label for="score3_p"><?php abbrTeam($A3); ?></label></td>
      	<td><input type="text" id="score3_p" name="score3_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score3_p');" value="<?php retrieveScorePrediction(3); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score4_p" name="score4_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score4_p');" value="<?php retrieveScorePrediction(4); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"><label for="score4_p"><?php abbrTeam($A4); ?></label></td>
      	<td class="date-venue"><?php echo "$_1pm, $_15Jun, $venue2"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 3<br>Group B</td>
      	<td class="left-team">
        <img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"><label for="score5_p"><?php abbrTeam($B3); ?></label></td>
      	<td><input type="text" id="score5_p" name="score5_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score5_p');" value="<?php retrieveScorePrediction(5); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score6_p" name="score6_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score6_p');" value="<?php retrieveScorePrediction(6); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"><label for="score6_p"><?php abbrTeam($B4); ?></label></td>
      	<td class="date-venue"><?php echo "$_4pm, $_15Jun, $venue3"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 4<br>Group B</td>
      	<td class="left-team">
        <img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"><label for="score7_p"><?php abbrTeam($B1); ?></label></td>
      	<td><input type="text" id="score7_p" name="score7_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score7_p');" value="<?php retrieveScorePrediction(7); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score8_p" name="score8_p" class="right-score score-field form-control input-sm"  onBlur="return validateScore('score8_p');" value="<?php retrieveScorePrediction(8); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"><label for="score8_p"><?php abbrTeam($B2); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_15Jun, $venue4"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 5<br>Group C</td>
	    <td class="left-team">
        <img src="<?php echo $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"><label for="score9_p"><?php abbrTeam($C1); ?></label></td>
      	<td><input type="text" id="score9_p" name="score9_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score9_p');" value="<?php retrieveScorePrediction(9); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score10_p" name="score10_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score10_p');" value="<?php retrieveScorePrediction(10); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"><label for="score10_p"><?php abbrTeam($C2); ?></label></td>
      	<td class="date-venue"><?php echo "$_11am, $_16Jun, $venue5"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 6<br>Group D</td>
      	<td class="left-team">
        <img src="<?php echo $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"><label for="score11_p"><?php abbrTeam($D1); ?></label></td>
      	<td><input type="text" id="score11_p" name="score11_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score11_p');" value="<?php retrieveScorePrediction(11); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score12_p" name="score12_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score12_p');" value="<?php retrieveScorePrediction(12); ?>" /></td>
      	<td class="right-team">
		<img src="<?php echo $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"><label for="score12_p"><?php abbrTeam($D2); ?></label></td>
        <td class="date-venue"><?php echo "$_2pm, $_16Jun, $venue6"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 7<br>Group C</td>
      	<td class="left-team">
        <img src="<?php echo $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"><label for="score13_p"><?php abbrTeam($C3); ?></label></td>
      	<td><input type="text" id="score13_p" name="score13_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score13_p');" value="<?php retrieveScorePrediction(13); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score14_p" name="score14_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score14_p');" value="<?php retrieveScorePrediction(14); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"><label for="score14_p"><?php abbrTeam($C4); ?></label></td>
      	<td class="date-venue"><?php echo "$_5pm, $_16Jun, $venue12"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 8<br>Group D</td>
      	<td class="left-team">
        <img src="<?php echo $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"><label for="score15_p"><?php abbrTeam($D3); ?></label></td>
      	<td><input type="text" id="score15_p" name="score15_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score15_p');" value="<?php retrieveScorePrediction(15); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score16_p" name="score16_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score16_p');" value="<?php retrieveScorePrediction(16); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"><label for="score16_p"><?php abbrTeam($D4); ?></label></td>
      	<td class="date-venue"><?php echo "$_8pm, $_16Jun, $venue7"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 9<br>Group E</td>
	    <td class="left-team">
        <img src="<?php echo $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"><label for="score17_p"><?php abbrTeam($E3); ?></label></td>
      	<td><input type="text" id="score17_p" name="score17_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score17_p');" value="<?php retrieveScorePrediction(17); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score18_p" name="score18_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score18_p');" value="<?php retrieveScorePrediction(18); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"><label for="score18_p"><?php abbrTeam($E4); ?></label></td>
      	<td class="date-venue"><?php echo "$_1pm, $_17Jun, $venue8"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 10<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"><label for="score19_p"><?php abbrTeam($F1); ?></label></td>
      	<td><input type="text" id="score19_p" name="score19_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score19_p');" value="<?php retrieveScorePrediction(19); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score20_p" name="score20_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score20_p');" value="<?php retrieveScorePrediction(20); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"><label for="score20_p"><?php abbrTeam($F2); ?></label></td>
      	<td class="date-venue"><?php echo "$_4pm, $_17Jun, $venue1"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 11<br>Group E</td>
      	<td class="left-team">
        <img src="<?php echo $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"><label for="score21_p"><?php abbrTeam($E1); ?></label></td>
      	<td><input type="text" id="score21_p" name="score21_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score21_p');" value="<?php retrieveScorePrediction(21); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score22_p" name="score22_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score22_p');" value="<?php retrieveScorePrediction(22); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"><label for="score22_p"><?php abbrTeam($E2); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_17Jun, $venue9"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 12<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"><label for="score23_p"><?php abbrTeam($F3); ?></label></td>
      	<td><input type="text" id="score23_p" name="score23_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score23_p');" value="<?php retrieveScorePrediction(23); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score24_p" name="score24_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score24_p');" value="<?php retrieveScorePrediction(24); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"><label for="score24_p"><?php abbrTeam($F4); ?></label></td>
      	<td class="date-venue"><?php echo "$_1pm, $_18Jun, $venue10"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 13<br>Group G</td>
	    <td class="left-team">
        <img src="<?php echo $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"><label for="score25_p"><?php abbrTeam($G1); ?></label></td>
      	<td><input type="text" id="score25_p" name="score25_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score25_p');" value="<?php retrieveScorePrediction(25); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score26_p" name="score26_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score26_p');" value="<?php retrieveScorePrediction(26); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"><label for="score26_p"><?php abbrTeam($G2); ?></label></td>
      	<td class="date-venue"><?php echo "$_4pm, $_18Jun, $venue4"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 14<br>Group G</td>
      	<td class="left-team">
        <img src="<?php echo $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"><label for="score27_p"><?php abbrTeam($G3); ?></label></td>
      	<td><input type="text" id="score27_p" name="score27_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score27_p');" value="<?php retrieveScorePrediction(27); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score28_p" name="score28_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score28_p');" value="<?php retrieveScorePrediction(28); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"><label for="score28_p"><?php abbrTeam($G4); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_18Jun, $venue11"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 15<br>Group H</td>
      	<td class="left-team">
        <img src="<?php echo $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"><label for="score29_p"><?php abbrTeam($H3); ?></label></td>
      	<td><input type="text" id="score29_p" name="score29_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score29_p');" value="<?php retrieveScorePrediction(29); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score30_p" name="score30_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score30_p');" value="<?php retrieveScorePrediction(30); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"><label for="score30_p"><?php abbrTeam($H4); ?></label></td>
      	<td class="date-venue"><?php echo "$_1pm, $_19Jun, $venue12"; ?></td>
        </tr>

        <tr>
        <td class="date-venue">Match 16<br>Group H</td>
      	<td class="left-team">
        <img src="<?php echo $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"><label for="score31_p"><?php abbrTeam($H1); ?></label></td>
      	<td><input type="text" id="score31_p" name="score31_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score31_p');" value="<?php retrieveScorePrediction(31); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score32_p" name="score32_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score32_p');" value="<?php retrieveScorePrediction(32); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"><label for="score32_p"><?php abbrTeam($H2); ?></label></td>
      	<td class="date-venue"><?php echo "$_4pm, $_19Jun, $venue6"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 17<br>Group A</td>
	    <td class="left-team">
        <img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"><label for="score33_p"><?php abbrTeam($A1); ?></label></td>
      	<td><input type="text" id="score33_p" name="score33_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score33_p');" value="<?php retrieveScorePrediction(33); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score34_p" name="score34_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score34_p');" value="<?php retrieveScorePrediction(34); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"><label for="score34_p"><?php abbrTeam($A3); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_19Jun, $venue3"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 18<br>Group B</td>
      	<td class="left-team">
        <img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"><label for="score35_p"><?php abbrTeam($B1); ?></label></td>
      	<td><input type="text" id="score35_p" name="score35_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score35_p');" value="<?php retrieveScorePrediction(35); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score36_p" name="score36_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score36_p');" value="<?php retrieveScorePrediction(36); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"><label for="score36_p"><?php abbrTeam($B3); ?></label></td>
      	<td class="date-venue"><?php echo "$_1pm, $_20Jun, $venue1"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 19<br>Group A</td>
      	<td class="left-team">
        <img src="<?php echo $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"><label for="score37_p"><?php abbrTeam($A4); ?></label></td>
      	<td><input type="text" id="score37_p" name="score37_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score37_p');" value="<?php retrieveScorePrediction(37); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score38_p" name="score38_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score38_p');" value="<?php retrieveScorePrediction(38); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"><label for="score38_p"><?php abbrTeam($A2); ?></label></td>
      	<td class="date-venue"><?php echo "$_4pm, $_20Jun, $venue9"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 20<br>Group B</td>
      	<td class="left-team">
        <img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"><label for="score39_p"><?php abbrTeam($B4); ?></label></td>
      	<td><input type="text" id="score39_p" name="score39_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score39_p');" value="<?php retrieveScorePrediction(39); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score40_p" name="score40_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score40_p');" value="<?php retrieveScorePrediction(40); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"><label for="score40_p"><?php abbrTeam($B2); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_20Jun, $venue5"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 21<br>Group C</td>
      	<td class="left-team">
        <img src="<?php echo $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"><label for="score41_p"><?php abbrTeam($C4); ?></label></td>
      	<td><input type="text" id="score41_p" name="score41_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score41_p');" value="<?php retrieveScorePrediction(41); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score42_p" name="score42_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score42_p');" value="<?php retrieveScorePrediction(42); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"><label for="score42_p"><?php abbrTeam($C2); ?></label></td>
      	<td class="date-venue"><?php echo "$_1pm, $_21Jun, $venue8"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 22<br>Group C</td>
      	<td class="left-team">
        <img src="<?php echo $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"><label for="score43_p"><?php abbrTeam($C1); ?></label></td>
      	<td><input type="text" id="score43_p" name="score43_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score43_p');" value="<?php retrieveScorePrediction(43); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score44_p" name="score44_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score44_p');" value="<?php retrieveScorePrediction(44); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"><label for="score44_p"><?php abbrTeam($C3); ?></label></td>
      	<td class="date-venue"><?php echo "$_4pm, $_21Jun, $venue2"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 23<br>Group D</td>
	    <td class="left-team">
        <img src="<?php echo $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"><label for="score45_p"><?php abbrTeam($D1); ?></label></td>
      	<td><input type="text" id="score45_p" name="score45_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score45_p');" value="<?php retrieveScorePrediction(45); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score46_p" name="score46_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score46_p');" value="<?php retrieveScorePrediction(46); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"><label for="score46_p"><?php abbrTeam($D3); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_21Jun, $venue10"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 24<br>Group E</td>
      	<td class="left-team">
        <img src="<?php echo $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"><label for="score47_p"><?php abbrTeam($E1); ?></label></td>
      	<td><input type="text" id="score47_p" name="score47_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score47_p');" value="<?php retrieveScorePrediction(47); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score48_p" name="score48_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score48_p');" value="<?php retrieveScorePrediction(48); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"><label for="score48_p"><?php abbrTeam($E3); ?></label></td>
      	<td class="date-venue"><?php echo "$_1pm, $_22Jun, $venue3"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 25<br>Group D</td>
      	<td class="left-team">
        <img src="<?php echo $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"><label for="score49_p"><?php abbrTeam($D4); ?></label></td>
      	<td><input type="text" id="score49_p" name="score49_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score49_p');" value="<?php retrieveScorePrediction(49); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score50_p" name="score50_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score50_p');" value="<?php retrieveScorePrediction(50); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"><label for="score50_p"><?php abbrTeam($D2); ?></label></td>
      	<td class="date-venue"><?php echo "$_4pm, $_22Jun, $venue11"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 26<br>Group E</td>
      	<td class="left-team">
        <img src="<?php echo $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"><label for="score51_p"><?php abbrTeam($E4); ?></label></td>
      	<td><input type="text" id="score51_p" name="score51_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score51_p');" value="<?php retrieveScorePrediction(51); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score52_p" name="score52_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score52_p');" value="<?php retrieveScorePrediction(52); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"><label for="score52_p"><?php abbrTeam($E2); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_22Jun, $venue7"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 27<br>Group G</td>
	    <td class="left-team">
        <img src="<?php echo $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"><label for="score53_p"><?php abbrTeam($G1); ?></label></td>
      	<td><input type="text" id="score53_p" name="score53_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score53_p');" value="<?php retrieveScorePrediction(53); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score54_p" name="score54_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score54_p');" value="<?php retrieveScorePrediction(54); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"><label for="score54_p"><?php abbrTeam($G3); ?></label></td>
      	<td class="date-venue"><?php echo "$_1pm, $_23Jun, $venue6"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 28<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"><label for="score55_p"><?php abbrTeam($F4); ?></label></td>
      	<td><input type="text" id="score55_p" name="score55_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score55_p');" value="<?php retrieveScorePrediction(55); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score56_p" name="score56_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score56_p');" value="<?php retrieveScorePrediction(56); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"><label for="score56_p"><?php abbrTeam($F2); ?></label></td>
      	<td class="date-venue"><?php echo "$_4pm, $_23Jun, $venue9"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 29<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"><label for="score57_p"><?php abbrTeam($F1); ?></label></td>
      	<td><input type="text" id="score57_p" name="score57_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score57_p');" value="<?php retrieveScorePrediction(57); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score58_p" name="score58_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score58_p');" value="<?php retrieveScorePrediction(58); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"><label for="score58_p"><?php abbrTeam($F3); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_23Jun, $venue4"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 30<br>Group G</td>
      	<td class="left-team">
        <img src="<?php echo $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"><label for="score59_p"><?php abbrTeam($G4); ?></label></td>
      	<td><input type="text" id="score59_p" name="score59_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score59_p');" value="<?php retrieveScorePrediction(59); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score60_p" name="score60_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score60_p');" value="<?php retrieveScorePrediction(60); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"><label for="score60_p"><?php abbrTeam($G2); ?></label></td>
      	<td class="date-venue"><?php echo "$_1pm, $_24Jun, $venue10"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 31<br>Group H</td>
	    <td class="left-team">
        <img src="<?php echo $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"><label for="score61_p"><?php abbrTeam($H4); ?></label></td>
      	<td><input type="text" id="score61_p" name="score61_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score61_p');" value="<?php retrieveScorePrediction(61); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score62_p" name="score62_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score62_p');" value="<?php retrieveScorePrediction(62); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"><label for="score62_p"><?php abbrTeam($H2); ?></label></td>
      	<td class="date-venue"><?php echo "$_4pm, $_24Jun, $venue2"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 32<br>Group H</td>
      	<td class="left-team">
        <img src="<?php echo $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"><label for="score63_p"><?php abbrTeam($H1); ?></label></td>
      	<td><input type="text" id="score63_p" name="score63_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score63_p');" value="<?php retrieveScorePrediction(63); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score64_p" name="score64_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score64_p');" value="<?php retrieveScorePrediction(64); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"><label for="score64_p"><?php abbrTeam($H3); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_24Jun, $venue5"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 33<br>Group A</td>
      	<td class="left-team">
        <img src="<?php echo $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"><label for="score65_p"><?php abbrTeam($A4); ?></label></td>
      	<td><input type="text" id="score65_p" name="score65_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score65_p');" value="<?php retrieveScorePrediction(65); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score66_p" name="score66_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score66_p');" value="<?php retrieveScorePrediction(66); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"><label for="score66_p"><?php abbrTeam($A1); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_25Jun, $venue8"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 34<br>Group A</td>
      	<td class="left-team">
        <img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"><label for="score67_p"><?php abbrTeam($A2); ?></label></td>
      	<td><input type="text" id="score67_p" name="score67_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score67_p');" value="<?php retrieveScorePrediction(67); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score68_p" name="score68_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score68_p');" value="<?php retrieveScorePrediction(68); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"><label for="score68_p"><?php abbrTeam($A3); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_25Jun, $venue11"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 35<br>Group B</td>
	    <td class="left-team">
        <img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"><label for="score69_p"><?php abbrTeam($B2); ?></label></td>
      	<td><input type="text" id="score69_p" name="score69_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score69_p');" value="<?php retrieveScorePrediction(69); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score70_p" name="score70_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score70_p');" value="<?php retrieveScorePrediction(70); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"><label for="score70_p"><?php abbrTeam($B3); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_25Jun, $venue7"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 36<br>Group B</td>
      	<td class="left-team">
        <img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"><label for="score71_p"><?php abbrTeam($B4); ?></label></td>
      	<td><input type="text" id="score71_p" name="score71_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score71_p');" value="<?php retrieveScorePrediction(71); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score72_p" name="score72_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score72_p');" value="<?php retrieveScorePrediction(72); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"><label for="score72_p"><?php abbrTeam($B1); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_25Jun, $venue12"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 37<br>Group C</td>
      	<td class="left-team">
        <img src="<?php echo $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"><label for="score73_p"><?php abbrTeam($C2); ?></label></td>
      	<td><input type="text" id="score73_p" name="score73_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score73_p');" value="<?php retrieveScorePrediction(73); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score74_p" name="score74_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score74_p');" value="<?php retrieveScorePrediction(74); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"><label for="score74_p"><?php abbrTeam($C3); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_26Jun, $venue4"; ?></td>
      	</tr>

       	<tr>
        <td class="date-venue">Match 38<br>Group C</td>
      	<td class="left-team">
        <img src="<?php echo $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"><label for="score75_p"><?php abbrTeam($C4); ?></label></td>
      	<td><input type="text" id="score75_p" name="score75_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score75_p');" value="<?php retrieveScorePrediction(75); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score76_p" name="score76_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score76_p');" value="<?php retrieveScorePrediction(76); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"><label for="score76_p"><?php abbrTeam($C1); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_26Jun, $venue1"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 39<br>Group D</td>
      	<td class="left-team">
        <img src="<?php echo $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"><label for="score77_p"><?php abbrTeam($D4); ?></label></td>
      	<td><input type="text" id="score77_p" name="score77_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score77_p');" value="<?php retrieveScorePrediction(77); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score78_p" name="score78_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score78_p');" value="<?php retrieveScorePrediction(78); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"><label for="score78_p"><?php abbrTeam($D1); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_26Jun, $venue3"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 40<br>Group D</td>
      	<td class="left-team">
        <img src="<?php echo $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"><label for="score79_p"><?php abbrTeam($D2); ?></label></td>
      	<td><input type="text" id="score79_p" name="score79_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score79_p');" value="<?php retrieveScorePrediction(79); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score80_p" name="score80_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score80_p');" value="<?php retrieveScorePrediction(80); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"><label for="score80_p"><?php abbrTeam($D3); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_26Jun, $venue9"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 41<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"><label for="score81_p"><?php abbrTeam($F4); ?></label></td>
      	<td><input type="text" id="score81_p" name="score81_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score81_p');" value="<?php retrieveScorePrediction(81); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score82_p" name="score82_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score82_p');" value="<?php retrieveScorePrediction(82); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"><label for="score82_p"><?php abbrTeam($F1); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_27Jun, $venue5"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 42<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"><label for="score83_p"><?php abbrTeam($F2); ?></label></td>
      	<td><input type="text" id="score83_p" name="score83_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score83_p');" value="<?php retrieveScorePrediction(83); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score84_p" name="score84_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score84_p');" value="<?php retrieveScorePrediction(84); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"><label for="score84_p"><?php abbrTeam($F3); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_27Jun, $venue2"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 43<br>Group E</td>
      	<td class="left-team">
        <img src="<?php echo $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"><label for="score85_p"><?php abbrTeam($E4); ?></label></td>
      	<td><input type="text" id="score85_p" name="score85_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score85_p');" value="<?php retrieveScorePrediction(85); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score86_p" name="score86_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score86_p');" value="<?php retrieveScorePrediction(86); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"><label for="score86_p"><?php abbrTeam($E1); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_27Jun, $venue6"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 44<br>Group E</td>
      	<td class="left-team">
        <img src="<?php echo $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"><label for="score87_p"><?php abbrTeam($E2); ?></label></td>
      	<td><input type="text" id="score87_p" name="score87_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score87_p');" value="<?php retrieveScorePrediction(87); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score88_p" name="score88_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score88_p');" value="<?php retrieveScorePrediction(88); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"><label for="score88_p"><?php abbrTeam($E3); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_27Jun, $venue10"; ?></td>
       	</tr>

        <tr>
        <td class="date-venue">Match 45<br>Group H</td>
      	<td class="left-team">
        <img src="<?php echo $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"><label for="score89_p"><?php abbrTeam($H4); ?></label></td>
      	<td><input type="text" id="score89_p" name="score89_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score89_p');" value="<?php retrieveScorePrediction(89); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score90_p" name="score90_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score90_p');" value="<?php retrieveScorePrediction(90); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"><label for="score90_p"><?php abbrTeam($H1); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_28Jun, $venue11"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 46<br>Group H</td>
      	<td class="left-team">
        <img src="<?php echo $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"><label for="score91_p"><?php abbrTeam($H2); ?></label></td>
      	<td><input type="text" id="score91_p" name="score91_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score91_p');" value="<?php retrieveScorePrediction(91); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score92_p" name="score92_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score92_p');" value="<?php retrieveScorePrediction(92); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"><label for="score92_p"><?php abbrTeam($H3); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_28Jun, $venue8"; ?></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 47<br>Group G</td>
      	<td class="left-team">
        <img src="<?php echo $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"><label for="score93_p"><?php abbrTeam($G2); ?></label></td>
      	<td><input type="text" id="score93_p" name="score93_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score93_p');" value="<?php retrieveScorePrediction(93); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score94_p" name="score94_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score94_p');" value="<?php retrieveScorePrediction(94); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"><label for="score94_p"><?php abbrTeam($G3); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_28Jun, $venue12"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 48<br>Group G</td>
      	<td class="left-team">
        <img src="<?php echo $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"><label for="score95_p"><?php abbrTeam($G4); ?></label></td>
      	<td><input type="text" id="score95_p" name="score95_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score95_p');" value="<?php retrieveScorePrediction(95); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score96_p" name="score96_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score96_p');" value="<?php retrieveScorePrediction(96); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"><label for="score96_p"><?php abbrTeam($G1); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_28Jun, $venue7"; ?></td>
      	</tr>

		<!-- ROUND OF 16 ------------------------>
        <!--===================================-->
      	<tr>
        <td class="date-venue">Match 49<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R1img; ?>" alt="<?php echo $R1; ?>" title="<?php echo $R1; ?>"><label for="score97_p"><?php abbrTeam($R1); ?></label></td>
      	<td><input type="text" id="score97_p" name="score97_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score97_p');" value="<?php retrieveScorePrediction(97); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score98_p" name="score98_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score98_p');" value="<?php retrieveScorePrediction(98); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $R2img; ?>" alt="<?php echo $R2; ?>" title="<?php echo $R2; ?>"><label for="score98_p"><?php abbrTeam($R2); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_30Jun, $venue5"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 50<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R3img; ?>" alt="<?php echo $R3; ?>" title="<?php echo $R3; ?>"><label for="score99_p"><?php abbrTeam($R3); ?></label></td>
      	<td><input type="text" id="score99_p" name="score99_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score99_p');" value="<?php retrieveScorePrediction(99); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score100_p" name="score100_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score100_p');" value="<?php retrieveScorePrediction(100); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $R4img; ?>" alt="<?php echo $R4; ?>" title="<?php echo $R4; ?>"><label for="score100_p"><?php abbrTeam($R4); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_30Jun, $venue4"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 51<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R5img; ?>" alt="<?php echo $R5; ?>" title="<?php echo $R5; ?>"><label for="score101_p"><?php abbrTeam($R5); ?></label></td>
      	<td><input type="text" id="score101_p" name="score101_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score101_p');" value="<?php retrieveScorePrediction(101); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score102_p" name="score102_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score102_p');" value="<?php retrieveScorePrediction(102); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $R6img; ?>" alt="<?php echo $R6; ?>" title="<?php echo $R6; ?>"><label for="score102_p"><?php abbrTeam($R6); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_01Jul, $venue1"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 52<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R7img; ?>" alt="<?php echo $R7; ?>" title="<?php echo $R7; ?>"><label for="score103_p"><?php abbrTeam($R7); ?></label></td>
      	<td><input type="text" id="score103_p" name="score103_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score103_p');" value="<?php retrieveScorePrediction(103); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score104_p" name="score104_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score104_p');" value="<?php retrieveScorePrediction(104); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $R8img; ?>" alt="<?php echo $R8; ?>" title="<?php echo $R8; ?>"><label for="score104_p"><?php abbrTeam($R8); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_01Jul, $venue10"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 53<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R9img; ?>" alt="<?php echo $R9; ?>" title="<?php echo $R9; ?>"><label for="score105_p"><?php abbrTeam($R9); ?></label></td>
      	<td><input type="text" id="score105_p" name="score105_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score105_p');" value="<?php retrieveScorePrediction(105); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score106_p" name="score106_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score106_p');" value="<?php retrieveScorePrediction(106); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $R10img; ?>" alt="<?php echo $R10; ?>" title="<?php echo $R10; ?>"><label for="score106_p"><?php abbrTeam($R10); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_02Jul, $venue8"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 54<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R11img; ?>" alt="<?php echo $R11; ?>" title="<?php echo $R11; ?>"><label for="score107_p"><?php abbrTeam($R11); ?></label></td>
      	<td><input type="text" id="score107_p" name="score107_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score107_p');" value="<?php retrieveScorePrediction(107); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score108_p" name="score108_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score108_p');" value="<?php retrieveScorePrediction(108); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $R12img; ?>" alt="<?php echo $R12; ?>" title="<?php echo $R12; ?>"><label for="score108_p"><?php abbrTeam($R12); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_02Jul, $venue9"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 55<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R13img; ?>" alt="<?php echo $R13; ?>" title="<?php echo $R13; ?>"><label for="score109_p"><?php abbrTeam($R13); ?></label></td>
      	<td><input type="text" id="score109_p" name="score109_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score109_p');" value="<?php retrieveScorePrediction(109); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score110_p" name="score110_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score110_p');" value="<?php retrieveScorePrediction(110); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $R14img; ?>" alt="<?php echo $R14; ?>" title="<?php echo $R14; ?>"><label for="score110_p"><?php abbrTeam($R14); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_03Jul, $venue3"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 56<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R15img; ?>" alt="<?php echo $R15; ?>" title="<?php echo $R15; ?>"><label for="score111_p"><?php abbrTeam($R15); ?></label></td>
      	<td><input type="text" id="score111_p" name="score111_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score111_p');" value="<?php retrieveScorePrediction(111); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score112_p" name="score112_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score112_p');" value="<?php retrieveScorePrediction(112); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $R16img; ?>" alt="<?php echo $R16; ?>" title="<?php echo $R16; ?>"><label for="score112_p"><?php abbrTeam($R16); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_03Jul, $venue6"; ?></td>
      	</tr>

		<!-- QUARTER FINALS ---------------------->
        <!--===================================-->
      	<tr>
        <td class="date-venue">Match 57<br>Quarter</td>
      	<td class="left-team">
        <img src="<?php echo $Q1img; ?>" alt="<?php echo $Q1; ?>" title="<?php echo $Q1; ?>"><label for="score113_p"><?php abbrTeam($Q1); ?></label></td>
      	<td><input type="text" id="score113_p" name="score113_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score113_p');" value="<?php retrieveScorePrediction(113); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score114_p" name="score114_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score114_p');" value="<?php retrieveScorePrediction(114); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $Q2img; ?>" alt="<?php echo $Q2; ?>" title="<?php echo $Q2; ?>"><label for="score114_p"><?php abbrTeam($Q2); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_06Jul, $venue10"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 58<br>Quarter</td>
      	<td class="left-team">
        <img src="<?php echo $Q3img; ?>" alt="<?php echo $Q3; ?>" title="<?php echo $Q3; ?>"><label for="score115_p"><?php abbrTeam($Q3); ?></label></td>
      	<td><input type="text" id="score115_p" name="score115_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score115_p');" value="<?php retrieveScorePrediction(115); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score116_p" name="score116_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score116_p');" value="<?php retrieveScorePrediction(116); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $Q4img; ?>" alt="<?php echo $Q4; ?>" title="<?php echo $Q4; ?>"><label for="score116_p"><?php abbrTeam($Q4); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_06Jul, $venue5"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 59<br>Quarter</td>
      	<td class="left-team">
        <img src="<?php echo $Q5img; ?>" alt="<?php echo $Q5; ?>" title="<?php echo $Q5; ?>"><label for="score117_p"><?php abbrTeam($Q5); ?></label></td>
      	<td><input type="text" id="score117_p" name="score117_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score117_p');" value="<?php retrieveScorePrediction(117); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score118_p" name="score118_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score118_p');" value="<?php retrieveScorePrediction(118); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $Q6img; ?>" alt="<?php echo $Q6; ?>" title="<?php echo $Q6; ?>"><label for="score118_p"><?php abbrTeam($Q6); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_07Jul, $venue8"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 60<br>Quarter</td>
      	<td class="left-team">
        <img src="<?php echo $Q7img; ?>" alt="<?php echo $Q7; ?>" title="<?php echo $Q7; ?>"><label for="score119_p"><?php abbrTeam($Q7); ?></label></td>
      	<td><input type="text" id="score119_p" name="score119_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score119_p');" value="<?php retrieveScorePrediction(119); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score120_p" name="score120_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score120_p');" value="<?php retrieveScorePrediction(120); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $Q8img; ?>" alt="<?php echo $Q8; ?>" title="<?php echo $Q8; ?>"><label for="score120_p"><?php abbrTeam($Q8); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_07Jul, $venue4"; ?></td>
      	</tr>

		<!-- SEMI FINALS ---------------------->
        <!--===================================-->
      	<tr>
        <td class="date-venue">Match 61<br>Semi</td>
      	<td class="left-team">
        <img src="<?php echo $S1img; ?>" alt="<?php echo $S1; ?>" title="<?php echo $S1; ?>"><label for="score121_p"><?php abbrTeam($S1); ?></label></td>
      	<td><input type="text" id="score121_p" name="score121_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score121_p');" value="<?php retrieveScorePrediction(121); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score122_p" name="score122_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score122_p');" value="<?php retrieveScorePrediction(122); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $S2img; ?>" alt="<?php echo $S2; ?>" title="<?php echo $S2; ?>"><label for="score122_p"><?php abbrTeam($S2); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_10Jul, $venue3"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 62<br>Semi</td>
      	<td class="left-team">
        <img src="<?php echo $S3img; ?>" alt="<?php echo $S3; ?>" title="<?php echo $S3; ?>"><label for="score123_p"><?php abbrTeam($S3); ?></label></td>
      	<td><input type="text" id="score123_p" name="score123_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score123_p');" value="<?php retrieveScorePrediction(123); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score124_p" name="score124_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score124_p');" value="<?php retrieveScorePrediction(124); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $S4img; ?>" alt="<?php echo $S4; ?>" title="<?php echo $S4; ?>"><label for="score124_p"><?php abbrTeam($S4); ?></label></td>
      	<td class="date-venue"><?php echo "$_7pm, $_11Jul, $venue1"; ?></td>
      	</tr>

		<!-- 3rd PLACE PLAYOFF ------------------>
        <!--===================================-->
      	<tr>
        <td class="date-venue">Match 63<br>PO 3rd</td>
      	<td class="left-team">
        <img src="<?php echo $P1img; ?>" alt="<?php echo $P1; ?>" title="<?php echo $P1; ?>"><label for="score125_p"><?php abbrTeam($P1); ?></label></td>
      	<td><input type="text" id="score125_p" name="score125_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score125_p');" value="<?php retrieveScorePrediction(125); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score126_p" name="score126_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score126_p');" value="<?php retrieveScorePrediction(126); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $P2img; ?>" alt="<?php echo $P2; ?>" title="<?php echo $P2; ?>"><label for="score126_p"><?php abbrTeam($P2); ?></label></td>
      	<td class="date-venue"><?php echo "$_3pm, $_14Jul, $venue3"; ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 64<br>Final</td>
      	<td class="left-team">
        <img src="<?php echo $Fi1img; ?>" alt="<?php echo $Fi1; ?>" title="<?php echo $Fi1; ?>"><label for="score127_p"><?php abbrTeam($Fi1); ?></label></td>
      	<td><input type="text" id="score127_p" name="score127_p" class="left-score score-field form-control input-sm" onBlur="return validateScore('score127_p');" value="<?php retrieveScorePrediction(127); ?>" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score128_p" name="score128_p" class="right-score score-field form-control input-sm" onBlur="return validateScore('score128_p');" value="<?php retrieveScorePrediction(128); ?>" /></td>
      	<td class="right-team">
        <img src="<?php echo $Fi2img; ?>" alt="<?php echo $Fi2; ?>" title="<?php echo $Fi2; ?>"><label for="score128_p"><?php abbrTeam($Fi2); ?></label></td>
      	<td class="date-venue"><?php echo "$_4pm, $_15Jul, $venue1"; ?></td>
      	</tr>
       	</table>

        <div id="submit-footer" class="navbar navbar-default navbar-fixed-bottom col-md-10 col-md-offset-1">
            <div class="pull-right">
            <!-- Results being processed - updating temporarily unavailable... -->
            <input type="submit" class="navbar-btn btn btn-primary" value="Update my predictions" name="predictionsSubmitted" />
            <a class="navbar-btn btn btn-default" href="#top" role="button">Return to top</a>
            </div>
        </div>

        </div><!--col-md-12-->
      </div><!--row-->
   	</form>

      <!-- Site footer -->
      <div class="footer">
      <?php include "includes/footer.php" ?>
      </div>


    </div><!-- /.main-section -->


    <script type="text/javascript">

		function parseDate(str) {
			var s = str.split(" "),
				d = s[0].split("-"),
				t = s[1].replace(/:/g, "");
			return d[2] + d[1] + d[0] + t;
		}
/*
		$(document).ready(function(e) {

			// Create an array of 'lockdown' dates and times to disable specific fields
			var lockdown = ["14-06-2018 16:00:00", <!--Fixture 1-->
							"15-06-2018 12:00:00", <!--Fixture 2-->
							"15-06-2018 15:00:00", <!--Fixture 3-->
							"15-06-2018 18:00:00", <!--Fixture 4-->
							"16-06-2018 10:00:00", <!--Fixture 5-->
							"16-06-2018 13:00:00", <!--Fixture 6-->
							"16-06-2018 16:00:00", <!--Fixture 7-->
							"16-06-2018 19:00:00", <!--Fixture 8-->
							"17-06-2018 12:00:00", <!--Fixture 9-->
							"17-06-2018 15:00:00", <!--Fixture 10-->
							"17-06-2018 18:00:00", <!--Fixture 11-->
							"18-06-2018 12:00:00", <!--Fixture 12-->
							"18-06-2018 15:00:00", <!--Fixture 13-->
							"18-06-2018 18:00:00", <!--Fixture 14-->
							"19-06-2018 12:00:00", <!--Fixture 15-->
							"19-06-2018 15:00:00", <!--Fixture 16-->
							"19-06-2018 18:00:00", <!--Fixture 17-->
							"20-06-2018 12:00:00", <!--Fixture 18-->
							"20-06-2018 15:00:00", <!--Fixture 19-->
							"20-06-2018 18:00:00", <!--Fixture 20-->
							"21-06-2018 12:00:00", <!--Fixture 21-->
							"21-06-2018 15:00:00", <!--Fixture 22-->
							"21-06-2018 18:00:00", <!--Fixture 23-->
							"22-06-2018 12:00:00", <!--Fixture 24-->
							"22-06-2018 15:00:00", <!--Fixture 25-->
							"22-06-2018 18:00:00", <!--Fixture 26-->
							"23-06-2018 12:00:00", <!--Fixture 27-->
							"23-06-2018 15:00:00", <!--Fixture 28-->
							"23-06-2018 18:00:00", <!--Fixture 29-->
							"24-06-2018 12:00:00", <!--Fixture 30-->
							"24-06-2018 15:00:00", <!--Fixture 31-->
							"24-06-2018 18:00:00", <!--Fixture 32-->
							"25-06-2018 14:00:00", <!--Fixture 33-->
							"25-06-2018 14:00:00", <!--Fixture 34-->
							"25-06-2018 18:00:00", <!--Fixture 35-->
							"25-06-2018 18:00:00", <!--Fixture 36-->
							"26-06-2018 14:00:00", <!--Fixture 37-->
							"26-06-2018 14:00:00", <!--Fixture 38-->
							"26-06-2018 18:00:00", <!--Fixture 39-->
							"26-06-2018 18:00:00", <!--Fixture 40-->
							"27-06-2018 14:00:00", <!--Fixture 41-->
							"27-06-2018 14:00:00", <!--Fixture 42-->
							"27-06-2018 18:00:00", <!--Fixture 43-->
							"27-06-2018 18:00:00", <!--Fixture 44-->
							"28-06-2018 14:00:00", <!--Fixture 45-->
							"28-06-2018 14:00:00", <!--Fixture 46-->
							"28-06-2018 18:00:00", <!--Fixture 47-->
							"28-06-2018 18:00:00", <!--Fixture 48-->
							"30-06-2018 14:00:00", <!--Fixture 49-->
							"30-06-2018 18:00:00", <!--Fixture 50-->
							"01-07-2018 14:00:00", <!--Fixture 51-->
							"01-07-2018 18:00:00", <!--Fixture 52-->
							"02-07-2018 14:00:00", <!--Fixture 53-->
							"02-07-2018 18:00:00", <!--Fixture 54-->
							"03-07-2018 14:00:00", <!--Fixture 55-->
							"03-07-2018 18:00:00", <!--Fixture 56-->
							"06-07-2018 14:00:00", <!--Fixture 57-->
							"06-07-2018 18:00:00", <!--Fixture 58-->
							"07-07-2018 14:00:00", <!--Fixture 59-->
							"07-07-2018 18:00:00", <!--Fixture 60-->
							"10-07-2018 18:00:00", <!--Fixture 61-->
							"11-07-2018 18:00:00", <!--Fixture 62-->
							"14-07-2018 14:00:00", <!--Fixture 63-->
							"15-07-2018 15:00:00"  <!--Fixture 64-->
							];

			for (i=1, j=2, k=0; i<129, j<129; i+=2, j+=2, k++) {
				/// i and j < values = (number of matches * 2) + 1

				// Port current server date/time to JS variable
				var currTime = "<?php print date("d-m-Y H:i:s") ?>";
				var x = "#score"+[i]+"_p";  // i = 1 3 5 7
				var y = "#score"+[j]+"_p";  // j = 2 4 6 8
				var z = "#updateBtn"+[k+1];
				//alert(currTime);

				if (parseDate(currTime) >= parseDate(lockdown[k])) {
					// Use alert to step through sequence
					//alert("Yes, current time has passed set value!");
					//$(x).prop("disabled", true);
					$(x).prop("readOnly", true);
					$(y).prop("readOnly", true);
					$(z).hide();
				}
				else {
					// Use alert to step through sequence
					//alert("No, current time has not passed set value!");
					/*$(x).prop("enabled", true);
					$(y).prop("enabled", true);
					$(z).prop("enabled", true);
					$(xFixed).hide();
					$(yFixed).hide();
				}
			}
        });
*/
		function showOnly(GoS) {
			/* GoS = Group or Stage
			if (GoS == "All") {
				$("#groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #qf, #sf, #po, #final").removeClass("active");
				$("#all").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Match 63')), tr:has(td:contains('Final'))").show();
			}
			if (GoS == "Groups") {
				$("#groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #qf, #sf, #po, #final").removeClass("active");
				$("#groups").addClass("active");
				$("tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Match 63')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H'))").show();
			}/*
			if (GoS == 'A') {
				$("#all, #groups, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupA").addClass("active");
				$("tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group A'))").show();
			}
			if (GoS == 'B') {
				$("#all, #groups, #groupA, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupB").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group B'))").show();
			}
			if (GoS == 'C') {
				$("#all, #groups, #groupA, #groupB, #groupD, #groupE, #groupF, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupC").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group C'))").show();
			}
			if (GoS == 'D') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupE, #groupF, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupD").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('QF')), tr:has(td:contains('SF')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group D'))").show();
			}
			if (GoS == 'E') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupF, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupE").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group E'))").show();
			}
			if (GoS == 'F') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupF").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group F'))").show();
			}

			if (GoS == 'RO16') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #qf, #sf, #po, #final").removeClass("active");
				$("#ro16").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Match 63')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('RO16'))").show();
			}
			if (GoS == 'QF') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #sf, #po, #final").removeClass("active");
				$("#qf").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('RO16')), tr:has(td:contains('Semi')), tr:has(td:contains('Match 63')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Quarter'))").show();
			}
			if (GoS == 'SF') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #qf, #po, #final").removeClass("active");
				$("#sf").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Match 63')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Semi'))").show();
			}
			if (GoS == 'PO') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #qf, #sf, #final").removeClass("active");
				$("#po").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Match 63'))").show();
			}
			if (GoS == 'Final') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #qf, #sf, #po").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Match 63'))").hide();
				$("tr:has(td:contains('Match 64'))").show();
			}
			/*
			if (GoS == '10Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('10 June'))").show();
			}
			if (GoS == '11Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('11 June'))").show();
			}
			if (GoS == '12Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('12 June'))").show();
			}
			if (GoS == '13Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('13 June'))").show();
			}
			if (GoS == '14Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('14 June'))").show();
			}
			if (GoS == '15Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('15 June'))").show();
			}
			if (GoS == '16Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('16 June'))").show();
			}
			if (GoS == '17Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('17 June'))").show();
			}
			if (GoS == '18Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('18 June'))").show();
			}
			if (GoS == '19Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('19 June'))").show();
			}
			if (GoS == '20Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('20 June'))").show();
			}
			if (GoS == '21Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('21 June'))").show();
			}
			if (GoS == '22Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('22 June'))").show();
			}
			if (GoS == '25Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('25 June'))").show();
			}
			if (GoS == '26Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('26 June'))").show();
			}
			if (GoS == '27Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('27 June'))").show();
			}
			if (GoS == '30Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('30 June'))").show();
			}
			if (GoS == '01Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('01 July'))").show();
			}
			if (GoS == '02Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('02 July'))").show();
			}
			if (GoS == '03Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('03 July'))").show();
			}
			if (GoS == '06Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('06 July'))").show();
			}
			if (GoS == '07Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('07 July'))").show();
			}
			if (GoS == '10Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('10 July'))").show();
			}*/
		}
	</script>
  </body>
</html>
