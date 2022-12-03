<?php
// Start the session
session_start();
if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
	header ("Location: index.php");
}
//checkSubmitted();
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
      <p class="lead">Round of 16</p>
      <p>Predict all 8 'Round of 16' fixtures before submitting! You only need to do this once.</p>
			<p class="alert alert-warning" id="submitMsg"><strong>Note:</strong> You can predict a draw as predictions are for 90 mins only (do not include extra time and penalties).</p>
      <a name="matches"></a><!--anchor point for filters-->
      <form id="predictionForm" name="predictionForm" class="form-horizontal" action="submit.php" method="POST">
      <div class="row">

				<table id="table" class="table table-sm table-striped">

					<!-- ROUND OF 16 ------------------------>
			        <!--===================================-->

							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>03/12/2022</td>
								<td style="text-align: right"><label for="score97_p"><?php echo $R1; ?></label></td>
								<td><img src="<?php echo $R1img; ?>" alt="<?php echo $R1; ?>" title="<?php echo $R1; ?>"></td>
								<td><input type="text" id="score97_p" name="score97_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score98_p" name="score98_p" class="form-control" required /></td>
								<td><img src="<?php echo $R2img; ?>" alt="<?php echo $R2; ?>" title="<?php echo $R2; ?>"></td>
								<td class="right-team"><label for="score98_p"><?php echo $R2; ?></label></td>
								<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue2 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>03/12/2022</td>
								<td style="text-align: right"><label for="score99_p"><?php echo $R3; ?></label></td>
								<td><img src="<?php echo $R3img; ?>" alt="<?php echo $R3; ?>" title="<?php echo $R3; ?>"></td>
								<td><input type="text" id="score99_p" name="score99_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score100_p" name="score100_p" class="form-control" required /></td>
								<td><img src="<?php echo $R4img; ?>" alt="<?php echo $R4; ?>" title="<?php echo $R4; ?>"></td>
								<td class="right-team"><label for="score100_p"><?php echo $R4; ?></label></td>
								<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue4 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>04/12/2022</td>
								<td style="text-align: right"><label for="score101_p"><?php echo $R5; ?></label></td>
								<td><img src="<?php echo $R5img; ?>" alt="<?php echo $R5; ?>" title="<?php echo $R5; ?>"></td>
								<td><input type="text" id="score101_p" name="score101_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score102_p" name="score102_p" class="form-control" required /></td>
								<td><img src="<?php echo $R6img; ?>" alt="<?php echo $R6; ?>" title="<?php echo $R6; ?>"></td>
								<td class="right-team"><label for="score102_p"><?php echo $R6; ?></label></td>
								<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue3 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>04/12/2022</td>
								<td style="text-align: right"><label for="score103_p"><?php echo $R7; ?></label></td>
								<td><img src="<?php echo $R7img; ?>" alt="<?php echo $R7; ?>" title="<?php echo $R7; ?>"></td>
								<td><input type="text" id="score103_p" name="score103_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score104_p" name="score104_p" class="form-control" required /></td>
								<td><img src="<?php echo $R8img; ?>" alt="<?php echo $R8; ?>" title="<?php echo $R8; ?>"></td>
								<td class="right-team"><label for="score104_p"><?php echo $R8; ?></label></td>
								<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue1 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>05/12/2022</td>
								<td style="text-align: right"><label for="score105_p"><?php echo $R9; ?></label></td>
								<td><img src="<?php echo $R9img; ?>" alt="<?php echo $R9; ?>" title="<?php echo $R9; ?>"></td>
								<td><input type="text" id="score105_p" name="score105_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score106_p" name="score106_p" class="form-control" required /></td>
								<td><img src="<?php echo $R10img; ?>" alt="<?php echo $R10; ?>" title="<?php echo $R10; ?>"></td>
								<td class="right-team"><label for="score106_p"><?php echo $R10; ?></label></td>
								<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue9 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>05/12/2022</td>
								<td style="text-align: right"><label for="score107_p"><?php echo $R11; ?></label></td>
								<td><img src="<?php echo $R11img; ?>" alt="<?php echo $R11; ?>" title="<?php echo $R11; ?>"></td>
								<td><input type="text" id="score107_p" name="score107_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score108_p" name="score108_p" class="form-control" required /></td>
								<td><img src="<?php echo $R12img; ?>" alt="<?php echo $R12; ?>" title="<?php echo $R12; ?>"></td>
								<td class="right-team"><label for="score108_p"><?php echo $R12; ?></label></td>
								<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue7 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>06/12/2022</td>
								<td style="text-align: right"><label for="score109_p"><?php echo $R13; ?></label></td>
								<td><img src="<?php echo $R13img; ?>" alt="<?php echo $R13; ?>" title="<?php echo $R13; ?>"></td>
								<td><input type="text" id="score109_p" name="score109_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score110_p" name="score110_p" class="form-control" required /></td>
								<td><img src="<?php echo $R14img; ?>" alt="<?php echo $R14; ?>" title="<?php echo $R14; ?>"></td>
								<td class="right-team"><label for="score110_p"><?php echo $R14; ?></label></td>
								<td class="small text-muted d-none d-md-block">15:00<br><?php echo $venue8 ?></td>
							</tr>
							<tr>
								<td class="small text-muted d-none d-md-block">RO16<br>06/12/2022</td>
								<td style="text-align: right"><label for="score111_p"><?php echo $R15; ?></label></td>
								<td><img src="<?php echo $R15img; ?>" alt="<?php echo $R15; ?>" title="<?php echo $R15; ?>"></td>
								<td><input type="text" id="score111_p" name="score111_p" class="form-control" required /></td>
								<td align="center"><span>v</span></td>
								<td><input type="text" id="score112_p" name="score112_p" class="form-control" required /></td>
								<td><img src="<?php echo $R16img; ?>" alt="<?php echo $R16; ?>" title="<?php echo $R16; ?>"></td>
								<td class="right-team"><label for="score112_p"><?php echo $R16; ?></label></td>
								<td class="small text-muted d-none d-md-block">19:00<br><?php echo $venue6 ?></td>
							</tr>
					</table>

        <!-- Placeholder for JSON table construction
        <table id="table" class="table table-sm table-striped">
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
														console.log(date);
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
