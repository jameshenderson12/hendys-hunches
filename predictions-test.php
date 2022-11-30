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
		<title>Hendy's Hunches: Test Predictions</title>
    <?php include "php/config.php" ?>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu|Lora">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
		<link rel="stylesheet" href="css/default.css">
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
	<style>
	body {
		font-family: 'Lora';
	}
	h1, h2, h3 {
		font-family: 'Ubuntu';
	}
	table {
		width: 100%;
	}
	td:nth-child(2), td:nth-child(7) {
		text-align: right;
	}
	td:nth-child(4), td:nth-child(6) {
		width: 5%;
		min-width: 40px;
		text-align: center;
	}
	td:nth-child(5) {
		width: 3%;
	}
	input {
		font-size: larger;
		text-align: center;
	}
	</style>
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
		              <a class="nav-link active" aria-current="page" href="predictions.php">Submit Predictions</a>
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
										<?php returnAvatar();	?>
		              </a>
		              <ul class="dropdown-menu">
		                <li><a class="dropdown-item" href="change-password.php">Change password</a></li>
		                <!--<li><a class="dropdown-item" href="#">Another action</a></li>-->
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
      <h1>Test Predictions</h1>
      <!--<p class="lead">Can you correctly predict your way to victory?</p>-->
      <p>To make your predictions, enter a score value into each box below and hit the 'Submit my predictions' button.</p>
			<p class="alert alert-warning" id="submitMsg"><strong>Note:</strong> You must predict all 48 fixtures before submitting! You only need to do this once.</p>

			<?php
				// Create DB connection
				include 'php/db-connect.php';
				$un = $_SESSION["username"];
				consoleMsg($_SESSION["username"]);
				// Get team information from the DB	counting occurrences too
				$sql_predstatus = "SELECT EXISTS (SELECT NULL FROM live_user_predictions_groups WHERE username = 'testuser')";
				$predstatus = mysqli_query($con, $sql_predstatus);
				//if ($predstatus = 1) {
					consoleMsg($predstatus);
					echo("<p class='alert alert-success p-4'><span class='bi bi-check2-square text-success'></span> It appears that you have already submitted your predictions for this round. Good luck.</p>");
				//}
				//print("<img src='$avatar' id='avatar' class='img-fluid rounded-circle mx-1' alt='User Avatar' name='User Avatar' width='25'> $firstname");
				?>

      <a name="matches"></a><!--anchor point for filters-->
      <form id="predictionForm" name="predictionForm" class="form-horizontal" action="submit.php" method="POST">
      <div class="row">

        <!-- Placeholder for JSON table construction -->
        <table id="table" class="table table-sm table-striped">

					<!-- ROUND OF 16 ------------------------>
			        <!--===================================-->
			      	<tr>
			        <td class="date-venue">RO16<br>03/12/22</td>
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
			        <td class="date-venue">RO16<br>03/12/22</td>
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
			        <td class="date-venue">RO16<br>04/12/22</td>
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
			        <td class="date-venue">RO16<br>04/12/22</td>
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
			        <td class="date-venue">RO16<br>05/12/22</td>
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
			        <td class="date-venue">RO16<br>05/12/22</td>
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
			        <td class="date-venue">RO16<br>06/12/22</td>
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
			        <td class="date-venue">RO16<br>06/12/22</td>
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
			        <!--===================================
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
			        <!--===================================
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
			        <!--===================================
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
					<!--
            <script>
                $(document).ready(function () {
                    // Fetch data from JSON file
                    $.getJSON("json/fifa-world-cup-2022-fixtures-groups.json",
                    	function (data) {
                        var fixture = '';
												var x = 1;
												var y = 2;
                        // Iterate through objects
                        $.each(data, function (key, value) {
														var homeTeam = value.HomeTeam;
														var awayTeam = value.AwayTeam;
														var homeTeamFlag = "flag-icons/24/" + homeTeam.toLowerCase().replaceAll(' ', '-') + ".png";
														var awayTeamFlag = "flag-icons/24/" + awayTeam.toLowerCase().replaceAll(' ', '-') + ".png";
														const str = value.DateUtc;
														const [dateValues, timeValues] = str.split(' ');
														const [year, month, day] = dateValues.split('-');
														const [hours, minutes] = timeValues.split(':');
														const date = new Date(+year, +month - 1, +day, +hours, +minutes).toLocaleString().slice(0, -3);
														//console.log(date);
                            fixture += '<tr>';
														fixture += '<td class="small text-muted d-none d-md-block">' + value.Group + '</td>';
                            fixture += '<td>' + value.HomeTeam + '</td>';
														fixture += '<td><img src="' + homeTeamFlag + '" alt="Flag of ' + homeTeam + '" title="Flag of ' + homeTeam + '"></td>';
														fixture += '<td><input type="text" id="score' + x + '_p" name="score' + x + '_p" class="form-control" required /></td>';
														fixture += '<td align="center">v<br><span class="badge bg-light text-primary">' + value.MatchNumber + '</span></td>';
														fixture += '<td><input type="text" id="score' + y + '_p" name="score' + y + '_p" class="form-control" required /></td>';
														fixture += '<td><img src="' + awayTeamFlag + '" alt="Flag of ' + awayTeam + '" title="Flag of ' + awayTeam + '"></td>';
                            fixture += '<td>' + value.AwayTeam + '</td>';
                            fixture += '<td class="small text-muted d-none d-md-block"> ' + date + '<br>' + value.Location + '</td>';
                            fixture += '</tr>';
														x+=2;
														y+=2;
                        });
                      // Insert rows into table
                      $('#table').append(fixture);
                    });
                });
            </script>
			</table>-->

        <div id="submit-footer" class="navbar navbar-default navbar-fixed-bottom col-md-10 col-md-offset-1">
            <div class="pull-right">
            <!-- Results being processed - updating temporarily unavailable... -->
            <input type="submit" class="navbar-btn btn btn-primary" value="Submit my predictions" name="predictionsSubmitted" />
            <a class="navbar-btn btn btn-light" href="#top" role="button">Return to top</a>
            </div>
        </div>
      </div><!--row-->
   	</form>

		<!-- Site footer -->
		<footer class="mt-auto">
			<hr>
			<p class="small fw-light">Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022">FIFA World Cup 2022™</a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
		</footer>
	</main>

    <script type="text/javascript">
			function parseDate(str) {
				var s = str.split(" "),
					d = s[0].split("-"),
					t = s[1].replace(/:/g, "");
				return d[2] + d[1] + d[0] + t;
			}
		</script>

	</body>
</html>
