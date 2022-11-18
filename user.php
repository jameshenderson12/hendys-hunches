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
		<title>Hendy's Hunches: User</title>
    <?php include "php/config.php" ?>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu|Lora">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
		<link rel="stylesheet" href="css/default.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
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
		          <h5 class="offcanvas-title" id="offcanvasNavbar2Label">Hendy's Hunches</h5>
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

			<?php
					// Connect to the database
					include 'php/db-connect.php';

					// Set up variable to capture result of SQL query to retrieve data from database tables
					$sql_getuserinfo = "SELECT live_user_predictions_groups.*, live_user_information.avatar, live_user_information.faveteam, live_user_information.fieldofwork, live_user_information.location, live_user_information.tournwinner, live_user_information.currpos
															FROM live_user_predictions_groups INNER JOIN live_user_information
															ON live_user_predictions_groups.id = live_user_information.id
															WHERE live_user_predictions_groups.id='".$_GET["id"]."'";

					// Global SQL query strings
					$sql_getresults = "SELECT SUM(score1_r) as score1_r, SUM(score2_r) as score2_r, SUM(score3_r) as score3_r, SUM(score4_r) as score4_r, SUM(score5_r) as score5_r, SUM(score6_r) as score6_r, SUM(score7_r) as score7_r, SUM(score8_r) as score8_r, SUM(score9_r) as score9_r, SUM(score10_r) as score10_r,
					SUM(score11_r) as score11_r, SUM(score12_r) as score12_r, SUM(score13_r) as score13_r, SUM(score14_r) as score14_r, SUM(score15_r) as score15_r, SUM(score16_r) as score16_r, SUM(score17_r) as score17_r, SUM(score18_r) as score18_r, SUM(score19_r) as score19_r, SUM(score20_r) as score20_r,
					SUM(score21_r) as score21_r, SUM(score22_r) as score22_r, SUM(score23_r) as score23_r, SUM(score24_r) as score24_r, SUM(score25_r) as score25_r, SUM(score26_r) as score26_r, SUM(score27_r) as score27_r, SUM(score28_r) as score28_r, SUM(score29_r) as score29_r, SUM(score30_r) as score30_r,
					SUM(score31_r) as score31_r, SUM(score32_r) as score32_r, SUM(score33_r) as score33_r, SUM(score34_r) as score34_r, SUM(score35_r) as score35_r, SUM(score36_r) as score36_r, SUM(score37_r) as score37_r, SUM(score38_r) as score38_r, SUM(score39_r) as score39_r, SUM(score40_r) as score40_r,
					SUM(score41_r) as score41_r, SUM(score42_r) as score42_r, SUM(score43_r) as score43_r, SUM(score44_r) as score44_r, SUM(score45_r) as score45_r, SUM(score46_r) as score46_r, SUM(score47_r) as score47_r, SUM(score48_r) as score48_r, SUM(score49_r) as score49_r, SUM(score50_r) as score50_r,
					SUM(score51_r) as score51_r, SUM(score52_r) as score52_r, SUM(score53_r) as score53_r, SUM(score54_r) as score54_r, SUM(score55_r) as score55_r, SUM(score56_r) as score56_r, SUM(score57_r) as score57_r, SUM(score58_r) as score58_r, SUM(score59_r) as score59_r, SUM(score60_r) as score60_r,
					SUM(score61_r) as score61_r, SUM(score62_r) as score62_r, SUM(score63_r) as score63_r, SUM(score64_r) as score64_r, SUM(score65_r) as score65_r, SUM(score66_r) as score66_r, SUM(score67_r) as score67_r, SUM(score68_r) as score68_r, SUM(score69_r) as score69_r, SUM(score70_r) as score70_r,
					SUM(score71_r) as score71_r, SUM(score72_r) as score72_r, SUM(score73_r) as score73_r, SUM(score74_r) as score74_r, SUM(score75_r) as score75_r, SUM(score76_r) as score76_r, SUM(score77_r) as score77_r, SUM(score78_r) as score78_r, SUM(score79_r) as score79_r, SUM(score80_r) as score80_r,
					SUM(score81_r) as score81_r, SUM(score82_r) as score82_r, SUM(score83_r) as score83_r, SUM(score84_r) as score84_r, SUM(score85_r) as score85_r, SUM(score86_r) as score86_r, SUM(score87_r) as score87_r, SUM(score88_r) as score88_r, SUM(score89_r) as score89_r, SUM(score90_r) as score90_r,
					SUM(score91_r) as score91_r, SUM(score92_r) as score92_r, SUM(score93_r) as score93_r, SUM(score94_r) as score94_r, SUM(score95_r) as score95_r, SUM(score96_r) as score96_r FROM live_match_results";

					$sql_getid = "SELECT match_id FROM live_match_results";

					// Create an array of match ids
					$list_of_ids = mysqli_query($con, $sql_getid);
					while($row = mysqli_fetch_array($list_of_ids)) {
							$matchids[] = $row['match_id'];
					}

					$userdata = mysqli_fetch_assoc(mysqli_query($con, $sql_getuserinfo));
					$uppCaseFN = ucfirst($userdata["firstname"]);
					$uppCaseSN = ucfirst($userdata["surname"]);
					$userid = $userdata["id"];
					$avatar = $userdata["avatar"];
					$fieldofwork = $userdata["fieldofwork"];
					$location = $userdata["location"];
					$faveteam = $userdata["faveteam"];
					$tournwinner = $userdata["tournwinner"];
					$currentpos = ordinal($userdata["currpos"]);
					$pointstotal = $userdata["points_total"];
					$matchresult = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));

		// Function for adding correct extention to a number
		function ordinal($number) {
			$ends = array('th','st','nd','rd','th','th','th','th','th','th');
			if ($number == "N/A") {
				return $number;
			}
			if ((($number % 100) >= 11) && (($number%100) <= 13))
				return $number. 'th';
			else
				return $number. $ends[$number % 10];
		}

					// Test for match point totals...
					$matchpoints[] = 0;

					// SQL query strings to be looped on ID value
					$sql_getspecid = "SELECT id, firstname, surname, score1_p, score2_p, score3_p, score4_p, score5_p, score6_p, score7_p, score8_p, score9_p, score10_p,
					score11_p, score12_p, score13_p, score14_p, score15_p, score16_p, score17_p, score18_p, score19_p, score20_p, score21_p, score22_p, score23_p,
					score24_p, score25_p, score26_p, score27_p, score28_p, score29_p, score30_p, score31_p, score32_p, score33_p, score34_p, score35_p, score36_p,
					score37_p, score38_p, score39_p, score40_p, score41_p, score42_p, score43_p, score44_p, score45_p, score46_p, score47_p, score48_p, score49_p,
					score50_p, score51_p, score52_p, score53_p, score54_p, score55_p, score56_p, score57_p, score58_p, score59_p, score60_p, score61_p, score62_p,
					score63_p, score64_p, score65_p, score66_p, score67_p, score68_p, score69_p, score70_p, score71_p, score72_p, score73_p, score74_p, score75_p,
					score76_p, score77_p, score78_p, score79_p, score80_p, score81_p, score82_p, score83_p, score84_p, score85_p, score86_p, score87_p, score88_p,
					score89_p, score90_p, score91_p, score92_p, score93_p, score94_p, score95_p, score96_p FROM live_user_predictions_groups WHERE id='".$userid."'";
					$pvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecid));
					$rvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));

					for ($gameno=1; $gameno<97; $gameno+=2) {
							$oddgameno[] = $gameno;
							$evengameno[] = $gameno + 1;
					}

					for ($i=0; $i<=48; $i++) {
							$matchpoints[$i] = 0;

							if( is_numeric($pvalue["score".$oddgameno[$i]."_p"]) && is_numeric($pvalue["score".$evengameno[$i]."_p"]) ) {

								if($pvalue["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) {
									$matchpoints[$i] += 1;
								}
								if($pvalue["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"]) {
									$matchpoints[$i] += 1;
								}
								if (($pvalue["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) && ($pvalue["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"])) {
									$matchpoints[$i] += 3;
								}

								if ((($pvalue["score".$oddgameno[$i]."_p"] > $pvalue["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] > $rvalue["score".$evengameno[$i]."_r"]))
								|| (($pvalue["score".$oddgameno[$i]."_p"] < $pvalue["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] < $rvalue["score".$evengameno[$i]."_r"]))
								|| (($pvalue["score".$oddgameno[$i]."_p"] === $pvalue["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] === $rvalue["score".$evengameno[$i]."_r"])) ) {
									$matchpoints[$i] += 2;
								}
							}
					}
			 ?>

			<main class="container px-4 py-4">
				<h1 class="page-header">Predictions by <?php print "$uppCaseFN $uppCaseSN" ?></h1>
        <p>Currently viewing predictions by <?php print "$uppCaseFN $uppCaseSN" ?>. Return to the <a href="rankings.php">rankings</a> table.</p>
	      <div class="row">
					<div class="col-md-3">
					<div class="card">
					  <img src="<?php echo $avatar ?>" id="avatar" class="img-fluid mx-auto p-2" alt="User Avatar" name="User Avatar" width="100">
					  <div class="card-body">
					    <h5 class="card-title" style="text-align: center; font-weight: bolder; margin:-15px 0px;"><?php printf("%s<span class='mx-2' style='color:#CCC;'>|</span>%s pts", $currentpos, $pointstotal); ?></h5>
					    <!--<p class="card-text"><?php printf ("%s thinks %s will win FIFA World Cup 2022.", $uppCaseFN, $tournwinner); ?></p>-->
					  </div>
					  <ul class="list-group list-group-flush">
							<li class="list-group-item"><?php printf ("<strong>Backed to win:</strong><br> %s", $tournwinner); ?></li>
					    <li class="list-group-item"><?php printf ("<strong>Favourite team:</strong><br> %s", $faveteam); ?></li>
					    <li class="list-group-item"><?php printf ("<strong>Location:</strong><br> %s", $location); ?></li>
							<li class="list-group-item"><?php printf ("<strong>Field of work:</strong><br> %s", $fieldofwork); ?></li>
					  </ul>
					  <div class="card-body">
					    <a href="#" class="card-link">Return to Rankings</a>
					    <!--<a href="#" class="card-link">Another link</a>-->
					  </div>
					</div>
				</div>
				<div class="col-md-9">
					<div class="card">
						<div class="card-body">

							<!-- Placeholder for JSON table construction -->
			        <table id="table" class="table table-sm table-striped">
								<tr>
									<th></th>
					        <th></th>
					        <th></th>
					        <th></th>
					        <th></th>
					        <th></th>
									<th></th>
					        <th align="center" width="">Prediction</th>
					        <th align="center" width="">Result</th>
					        <th align="center" width="10%">Points</th>
				        </tr>
			            <script>
			                $(document).ready(function () {
			                    // Fetch data from JSON file
			                    $.getJSON("json/fifa-world-cup-2022-fixtures-groups.json",
			                    	function (data) {
			                        var fixture = '';
															<?php
																$x = 1;
																$y = 2;
															?>
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
			                            fixture += '<tr>';
																	fixture += '<td class="small text-muted d-none d-md-block">' + value.Group + '</td>';
			                            fixture += '<td class="" style="text-align: right">' + value.HomeTeam + '</td>';
																	fixture += '<td><img src="' + homeTeamFlag + '" alt="Flag of ' + homeTeam + '" title="Flag of ' + homeTeam + '"></td>';
																	fixture += '<td align="center">v<br><span class="badge bg-light text-primary">' + value.MatchNumber + '</span></td>';
																	fixture += '<td><img src="' + awayTeamFlag + '" alt="Flag of ' + awayTeam + '" title="Flag of ' + awayTeam + '"></td>';
			                            fixture += '<td>' + value.AwayTeam + '</td>';
			                            fixture += '<td class="small text-muted d-none d-md-block">' + date + '</td>';
																	fixture += '<td align="center"><span class="prediction"><?php echo $userdata["score".$x."_p"] ?> - <?php echo $userdata["score".$y."_p"] ?></span></td>';
													        fixture += '<td align="center"><?php if($matchids[0]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[0]."_r"], $matchresult["score".$evengameno[0]."_r"]); } else echo "N / A"; ?></td>';
													      	fixture += '<td align="center"><?php if($matchids[0]) { echo $matchpoints[0]; } else { echo "-"; } ?></td>';
			                            fixture += '</tr>';
																	<?php
																		++$x;
																		++$y;
																	?>
			                        });
			                      // Insert rows into table
			                      $('#table').append(fixture);
			                    });
			                });
			            </script>
						</table>

						</div>
					</div>
				</div>
      	</div><!--row-->
				<!-- Site footer -->
				<footer class="mt-auto">
					<hr>
					<p class="small fw-light">Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022">FIFA World Cup 2022™</a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
				</footer>
			</main>

  </body>
</html>
