<?php
// Start the session
session_start();
if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
	header ("Location: index.php");
}
//checkSubmitted();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
	<title>Hendy's Hunches: Predictions</title>
    <?php include "php/config.php" ?>
	<link rel="shortcut icon" href="ico/favicon.ico">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu|Lora">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link rel="stylesheet" href="css/default.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

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
	td img {
		width: 36px;
		border-radius: 50%;
		vertical-align: middle;
	}
	td:nth-child(2), td:nth-child(7) {
		text-align: right;
	}
	/*
	td:nth-child(4), td:nth-child(6) {
		width: 5%;
		text-align: center;
	}
	td:nth-child(5) {
		width: 3%;
	} */
	input {
		font-size: larger !important;
		text-align: center !important;
		border: 1px solid #AAA !important;
		width: 55px !important;
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
		              <a class="nav-link" href="dashboard.php">Home</a>
		            </li>
		            <li class="nav-item">
		              <a class="nav-link active" aria-current="page" href="predictions.php">Submit Predictions</a>
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

		            <li class="nav-item dropdown">
		              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
										<?php returnAvatar(); ?>
		              </a>
		              <ul class="dropdown-menu">
										<!--<li><a class="dropdown-item" href="overview.php">Application overview</a></li>-->
										<li><a class="dropdown-item" href="change-password.php">Change my password</a></li>
										<li><a class="dropdown-item" href="user.php?id=<?php echo $_SESSION['id']; ?>" class="card-link">View my predictions</a></li>
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
      <h1>My Predictions</h1>
      <p class="lead">Predict each of the 36 group stage fixtures and submit using the button below.</p>
	  <p class="small">Estimated time 5-10 mins.</p>
	  <!-- <p class="alert alert-warning" id="submitMsg"><strong>Note:</strong> You can predict a draw as predictions are for 90 mins only (do not include extra time and penalties).</p> -->
      <a name="matches"></a><!--anchor point for filters-->
      <form id="predictionForm" name="predictionForm" class="form-horizontal" action="submit.php" method="POST">
      <div class="row">
<!--
				<table id="table" class="table table-sm table-striped">

					<tr>
						<td class="small text-muted d-none d-md-block">3PP<br>17/12/2022</td>
						<td style="text-align: right"><label for="score125_p"><?php echo $P1; ?></label></td>
						<td><img src="<?php echo $P1_img; ?>" alt="<?php echo $P1; ?>" title="<?php echo $P1; ?>"></td>
						<td><input type="text" id="score125_p" name="score125_p" class="form-control" required /></td>
						<td align="center"><span>v</span></td>
						<td><input type="text" id="score126_p" name="score126_p" class="form-control" required /></td>
						<td><img src="<?php echo $P2_img; ?>" alt="<?php echo $P2; ?>" title="<?php echo $P2; ?>"></td>
						<td class="right-team"><label for="score126_p"><?php echo $P2; ?></label></td>
						<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue2 ?></td>
					</tr>
					<tr>
						<td class="small text-muted d-none d-md-block">Final<br>18/12/2022</td>
						<td style="text-align: right"><label for="score127_p"><?php echo $Fi1; ?></label></td>
						<td><img src="<?php echo $Fi1_img; ?>" alt="<?php echo $Fi1; ?>" title="<?php echo $Fi1; ?>"></td>
						<td><input type="text" id="score127_p" name="score127_p" class="form-control" required /></td>
						<td align="center"><span>v</span></td>
						<td><input type="text" id="score128_p" name="score128_p" class="form-control" required /></td>
						<td><img src="<?php echo $Fi2_img; ?>" alt="<?php echo $Fi2; ?>" title="<?php echo $Fi2; ?>"></td>
						<td class="right-team"><label for="score128_p"><?php echo $Fi2; ?></label></td>
						<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue6 ?></td>
					</tr>

					----- SEMI FINALS --------------------------

					<tr>
						<td class="small text-muted d-none d-md-block">SF1<br>13/12/2022</td>
						<td style="text-align: right"><label for="score121_p"><?php echo $S1; ?></label></td>
						<td><img src="<?php echo $S1_img; ?>" alt="<?php echo $S1; ?>" title="<?php echo $S1; ?>"></td>
						<td><input type="text" id="score121_p" name="score121_p" class="form-control" required /></td>
						<td align="center"><span>v</span></td>
						<td><input type="text" id="score122_p" name="score122_p" class="form-control" required /></td>
						<td><img src="<?php echo $S2_img; ?>" alt="<?php echo $S2; ?>" title="<?php echo $S2; ?>"></td>
						<td class="right-team"><label for="score122_p"><?php echo $S2; ?></label></td>
						<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue6 ?></td>
					</tr>
					<tr>
						<td class="small text-muted d-none d-md-block">SF2<br>14/12/2022</td>
						<td style="text-align: right"><label for="score123_p"><?php echo $S3; ?></label></td>
						<td><img src="<?php echo $S3_img; ?>" alt="<?php echo $S3; ?>" title="<?php echo $S3; ?>"></td>
						<td><input type="text" id="score123_p" name="score123_p" class="form-control" required /></td>
						<td align="center"><span>v</span></td>
						<td><input type="text" id="score124_p" name="score124_p" class="form-control" required /></td>
						<td><img src="<?php echo $S4_img; ?>" alt="<?php echo $S4; ?>" title="<?php echo $S4; ?>"></td>
						<td class="right-team"><label for="score124_p"><?php echo $S4; ?></label></td>
						<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue1 ?></td>
					</tr>

					----- QUARTER FINALS ------------------------

					<tr>
						<td class="small text-muted d-none d-md-block">QF1<br>09/12/2022</td>
						<td style="text-align: right"><label for="score113_p"><?php echo $Q1; ?></label></td>
						<td><img src="<?php echo $Q1_img; ?>" alt="<?php echo $Q1; ?>" title="<?php echo $Q1; ?>"></td>
						<td><input type="text" id="score113_p" name="score113_p" class="form-control" required /></td>
						<td align="center"><span>v</span></td>
						<td><input type="text" id="score114_p" name="score114_p" class="form-control" required /></td>
						<td><img src="<?php echo $Q2_img; ?>" alt="<?php echo $Q2; ?>" title="<?php echo $Q2; ?>"></td>
						<td class="right-team"><label for="score114_p"><?php echo $Q2; ?></label></td>
						<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue8 ?></td>
					</tr>
					<tr>
						<td class="small text-muted d-none d-md-block">QF2<br>09/12/2022</td>
						<td style="text-align: right"><label for="score115_p"><?php echo $Q3; ?></label></td>
						<td><img src="<?php echo $Q3_img; ?>" alt="<?php echo $Q3; ?>" title="<?php echo $Q3; ?>"></td>
						<td><input type="text" id="score115_p" name="score115_p" class="form-control" required /></td>
						<td align="center"><span>v</span></td>
						<td><input type="text" id="score116_p" name="score116_p" class="form-control" required /></td>
						<td><img src="<?php echo $Q4_img; ?>" alt="<?php echo $Q4; ?>" title="<?php echo $Q4; ?>"></td>
						<td class="right-team"><label for="score116_p"><?php echo $Q4; ?></label></td>
						<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue6 ?></td>
					</tr>
					<tr>
						<td class="small text-muted d-none d-md-block">QF3<br>10/12/2022</td>
						<td style="text-align: right"><label for="score117_p"><?php echo $Q5; ?></label></td>
						<td><img src="<?php echo $Q5_img; ?>" alt="<?php echo $Q5; ?>" title="<?php echo $Q5; ?>"></td>
						<td><input type="text" id="score117_p" name="score117_p" class="form-control" required /></td>
						<td align="center"><span>v</span></td>
						<td><input type="text" id="score118_p" name="score118_p" class="form-control" required /></td>
						<td><img src="<?php echo $Q6_img; ?>" alt="<?php echo $Q6; ?>" title="<?php echo $Q6; ?>"></td>
						<td class="right-team"><label for="score118_p"><?php echo $Q6; ?></label></td>
						<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue3 ?></td>
					</tr>
					<tr>
						<td class="small text-muted d-none d-md-block">QF4<br>10/12/2022</td>
						<td style="text-align: right"><label for="score119_p"><?php echo $Q7; ?></label></td>
						<td><img src="<?php echo $Q7_img; ?>" alt="<?php echo $Q7; ?>" title="<?php echo $Q7; ?>"></td>
						<td><input type="text" id="score119_p" name="score119_p" class="form-control" required /></td>
						<td align="center"><span>v</span></td>
						<td><input type="text" id="score120_p" name="score120_p" class="form-control" required /></td>
						<td><img src="<?php echo $Q8_img; ?>" alt="<?php echo $Q8; ?>" title="<?php echo $Q8; ?>"></td>
						<td class="right-team"><label for="score120_p"><?php echo $Q8; ?></label></td>
						<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue1 ?></td>
					</tr>

					----- ROUND OF 16 ------------------------>
			        <!--===================================

							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>03/12/2022</td>
								<td style="text-align: right"><label for="score97_p"><?php echo $R1; ?></label></td>
								<td><img src="<?php echo $R1_img; ?>" alt="<?php echo $R1; ?>" title="<?php echo $R1; ?>"></td>
								<td><input type="text" id="score97_p" name="score97_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score98_p" name="score98_p" class="form-control" required /></td>
								<td><img src="<?php echo $R2_img; ?>" alt="<?php echo $R2; ?>" title="<?php echo $R2; ?>"></td>
								<td class="right-team"><label for="score98_p"><?php echo $R2; ?></label></td>
								<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue2 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>03/12/2022</td>
								<td style="text-align: right"><label for="score99_p"><?php echo $R3; ?></label></td>
								<td><img src="<?php echo $R3_img; ?>" alt="<?php echo $R3; ?>" title="<?php echo $R3; ?>"></td>
								<td><input type="text" id="score99_p" name="score99_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score100_p" name="score100_p" class="form-control" required /></td>
								<td><img src="<?php echo $R4_img; ?>" alt="<?php echo $R4; ?>" title="<?php echo $R4; ?>"></td>
								<td class="right-team"><label for="score100_p"><?php echo $R4; ?></label></td>
								<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue4 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>04/12/2022</td>
								<td style="text-align: right"><label for="score101_p"><?php echo $R5; ?></label></td>
								<td><img src="<?php echo $R5_img; ?>" alt="<?php echo $R5; ?>" title="<?php echo $R5; ?>"></td>
								<td><input type="text" id="score101_p" name="score101_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score102_p" name="score102_p" class="form-control" required /></td>
								<td><img src="<?php echo $R6_img; ?>" alt="<?php echo $R6; ?>" title="<?php echo $R6; ?>"></td>
								<td class="right-team"><label for="score102_p"><?php echo $R6; ?></label></td>
								<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue3 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>04/12/2022</td>
								<td style="text-align: right"><label for="score103_p"><?php echo $R7; ?></label></td>
								<td><img src="<?php echo $R7_img; ?>" alt="<?php echo $R7; ?>" title="<?php echo $R7; ?>"></td>
								<td><input type="text" id="score103_p" name="score103_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score104_p" name="score104_p" class="form-control" required /></td>
								<td><img src="<?php echo $R8_img; ?>" alt="<?php echo $R8; ?>" title="<?php echo $R8; ?>"></td>
								<td class="right-team"><label for="score104_p"><?php echo $R8; ?></label></td>
								<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue1 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>05/12/2022</td>
								<td style="text-align: right"><label for="score105_p"><?php echo $R9; ?></label></td>
								<td><img src="<?php echo $R9_img; ?>" alt="<?php echo $R9; ?>" title="<?php echo $R9; ?>"></td>
								<td><input type="text" id="score105_p" name="score105_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score106_p" name="score106_p" class="form-control" required /></td>
								<td><img src="<?php echo $R10_img; ?>" alt="<?php echo $R10; ?>" title="<?php echo $R10; ?>"></td>
								<td class="right-team"><label for="score106_p"><?php echo $R10; ?></label></td>
								<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue9 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>05/12/2022</td>
								<td style="text-align: right"><label for="score107_p"><?php echo $R11; ?></label></td>
								<td><img src="<?php echo $R11_img; ?>" alt="<?php echo $R11; ?>" title="<?php echo $R11; ?>"></td>
								<td><input type="text" id="score107_p" name="score107_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score108_p" name="score108_p" class="form-control" required /></td>
								<td><img src="<?php echo $R12_img; ?>" alt="<?php echo $R12; ?>" title="<?php echo $R12; ?>"></td>
								<td class="right-team"><label for="score108_p"><?php echo $R12; ?></label></td>
								<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue7 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>06/12/2022</td>
								<td style="text-align: right"><label for="score109_p"><?php echo $R13; ?></label></td>
								<td><img src="<?php echo $R13_img; ?>" alt="<?php echo $R13; ?>" title="<?php echo $R13; ?>"></td>
								<td><input type="text" id="score109_p" name="score109_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score110_p" name="score110_p" class="form-control" required /></td>
								<td><img src="<?php echo $R14_img; ?>" alt="<?php echo $R14; ?>" title="<?php echo $R14; ?>"></td>
								<td class="right-team"><label for="score110_p"><?php echo $R14; ?></label></td>
								<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue8 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>06/12/2022</td>
								<td style="text-align: right"><label for="score111_p"><?php echo $R15; ?></label></td>
								<td><img src="<?php echo $R15_img; ?>" alt="<?php echo $R15; ?>" title="<?php echo $R15; ?>"></td>
								<td><input type="text" id="score111_p" name="score111_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score112_p" name="score112_p" class="form-control" required /></td>
								<td><img src="<?php echo $R16_img; ?>" alt="<?php echo $R16; ?>" title="<?php echo $R16; ?>"></td>
								<td class="right-team"><label for="score112_p"><?php echo $R16; ?></label></td>
								<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue6 ?></td>
							</tr>
					</table>-->

		<button id="populateScores" class="btn btn-primary mt-3">Populate Scores</button>

        <!-- Placeholder for JSON table construction -->
		<table id="table" class="table table-sm table-striped">
			<thead>
				<tr>
					<th class="d-none d-md-table-cell">Group</th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th class="d-none d-md-table-cell">Details</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>

		<button type="submit" class="btn btn-primary mt-2 mb-5" name="predictionsSubmitted">Submit my predictions</button>

      </div><!--row-->
   	</form>

		<!-- Site footer -->
		<footer class="mt-auto">
			<hr>
			<p class="small fw-light">Predictions game based on <a href="<?=$competition_url?>"><?=$competition?></a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
		</footer>
	</main>

    <script type="text/javascript">
    $(document).ready(function () {
        // Fetch data from JSON file
        $.getJSON("json/uefa-euro-2024-fixtures-groups.json", function (data) {
            let fixture = '';
            let x = 1, y = 2;

            // Iterate through objects
            $.each(data, function (key, value) {
                const homeTeam = value.HomeTeam;
                const awayTeam = value.AwayTeam;
                const homeTeamFlag = `flag-icons/24/${homeTeam.toLowerCase().replaceAll(' ', '-')}.png`;
                const awayTeamFlag = `flag-icons/24/${awayTeam.toLowerCase().replaceAll(' ', '-')}.png`;
                const dateStr = value.DateUtc;
                const [dateValues, timeValues] = dateStr.split(' ');
                const [year, month, day] = dateValues.split('-');
                const [hours, minutes] = timeValues.split(':');
                const date = new Date(+year, +month - 1, +day, +hours, +minutes).toLocaleString().slice(0, -3);

                fixture += `
                    <tr>
                        <td class="small text-muted d-none d-md-table-cell">${value.Group}</td>
                        <td><img src="${homeTeamFlag}" alt="Flag of ${homeTeam}" title="Flag of ${homeTeam}" class="img-fluid"></td>
						<td>${homeTeam}</td>                        
                        <td><input type="text" id="score${x}_p" name="score${x}_p" class="form-control" required /></td>
                        <td align="center"><strong>V</strong></td><!--<br><span class="badge bg-light text-primary">${value.MatchNumber}</span>-->
                        <td><input type="text" id="score${y}_p" name="score${y}_p" class="form-control" style="float:right" required /></td>                        
                        <td>${awayTeam}</td>
						<td><img src="${awayTeamFlag}" alt="Flag of ${awayTeam}" title="Flag of ${awayTeam}" class="img-fluid"></td>
                        <td class="small text-muted d-none d-md-table-cell">
                            <span data-bs-toggle="tooltip" title="Match Number: ${value.MatchNumber}, Round: ${value.RoundNumber}, Date: ${date}, Location: ${value.Location}">${date}<br>${value.Location}</span>
                        </td>
                    </tr>
                `;
                x += 2;
                y += 2;
            });

            // Insert rows into table
            $('#table tbody').append(fixture);
            
            // Initialize Bootstrap tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

			// Add click event to populate scores
			$('#populateScores').click(function() {
				populateScores();
			});
		});
	});

	function populateScores() {
		for (let i = 1; i <= 72; i++) {
			$('#score' + i + '_p').val(Math.floor(Math.random() * 5)); // Random score between 0 and 4
		}
	}

	function parseDate(str) {
		var s = str.split(" "),
			d = s[0].split("-"),
			t = s[1].replace(/:/g, "");
		return d[2] + d[1] + d[0] + t;
	}
	</script>

	</body>
</html>
