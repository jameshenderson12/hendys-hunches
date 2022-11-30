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
		              <a class="nav-link disabled" href="predictions.php">Submit Predictions</a>
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
					    <a href="rankings.php" class="card-link">Return to Rankings</a>
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
									<th width="10%"></th>
					        <th width="15%"></th>
					        <th width="5%"></th>
					        <th width="3%"></th>
					        <th width="5%"></th>
					        <th width="15%"></th>
					        <th width="15%">Prediction</th>
					        <th width="15%">Result</th>
					        <th width="10%">Points</th>
				        </tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group A<br>20/11/2022</td>
								  <td style="text-align: right"><label for="score1_p"><?php echo $A1; ?></label></td>
							    <td><img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"></td>
							    <td align="center"><span>v</span></td>
									<td><img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"></td>
									<td class="right-team"><label for="score2_p"><?php echo $A2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score1_p'] ?> - <?php echo $userdata['score2_p'] ?></span></td>
							    <td><?php if($matchids[0]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[0]."_r"], $matchresult["score".$evengameno[0]."_r"]); } else echo "N/A"; ?></td>
							    <td><?php if($matchids[0]) { echo $matchpoints[0]; } else { echo "-"; } ?></td>
				      	</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group B<br>21/11/2022</td>
								  <td style="text-align: right"><label for="score3_p"><?php echo $B1; ?></label></td>
							    <td><img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"></td>
							    <td align="center"><span>v</span></td>
									<td><img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"></td>
									<td class="right-team"><label for="score4_p"><?php echo $B2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score3_p'] ?> - <?php echo $userdata['score4_p'] ?></span></td>
							    <td><?php if($matchids[1]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[1]."_r"], $matchresult["score".$evengameno[1]."_r"]); } else echo "N/A"; ?></td>
							    <td><?php if($matchids[1]) { echo $matchpoints[1]; } else { echo "-"; } ?></td>
				      	</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group A<br>21/11/2022</td>
									<td style="text-align: right"><label for="score5_p"><?php echo $A3; ?></label></td>
									<td><img src="<?php echo $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"></td>
									<td class="right-team"><label for="score6_p"><?php echo $A4; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score5_p'] ?> - <?php echo $userdata['score6_p'] ?></span></td>
									<td><?php if($matchids[2]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[2]."_r"], $matchresult["score".$evengameno[2]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[2]) { echo $matchpoints[2]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group B<br>21/11/2022</td>
									<td style="text-align: right"><label for="score7_p"><?php echo $B3; ?></label></td>
									<td><img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"></td>
									<td class="right-team"><label for="score8_p"><?php echo $B4; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score7_p'] ?> - <?php echo $userdata['score8_p'] ?></span></td>
									<td><?php if($matchids[3]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[3]."_r"], $matchresult["score".$evengameno[3]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[3]) { echo $matchpoints[3]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group C<br>22/11/2022</td>
									<td style="text-align: right"><label for="score9_p"><?php echo $C1; ?></label></td>
									<td><img src="<?php echo $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"></td>
									<td class="right-team"><label for="score10_p"><?php echo $C2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score9_p'] ?> - <?php echo $userdata['score10_p'] ?></span></td>
									<td><?php if($matchids[4]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[4]."_r"], $matchresult["score".$evengameno[4]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[4]) { echo $matchpoints[4]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group D<br>22/11/2022</td>
									<td style="text-align: right"><label for="score11_p"><?php echo $D3; ?></label></td>
									<td><img src="<?php echo $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"></td>
									<td class="right-team"><label for="score12_p"><?php echo $D4; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score11_p'] ?> - <?php echo $userdata['score12_p'] ?></span></td>
									<td><?php if($matchids[5]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[5]."_r"], $matchresult["score".$evengameno[5]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[5]) { echo $matchpoints[5]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group C<br>22/11/2022</td>
									<td style="text-align: right"><label for="score13_p"><?php echo $C3; ?></label></td>
									<td><img src="<?php echo $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"></td>
									<td class="right-team"><label for="score14_p"><?php echo $C4; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score13_p'] ?> - <?php echo $userdata['score14_p'] ?></span></td>
									<td><?php if($matchids[6]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[6]."_r"], $matchresult["score".$evengameno[6]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[6]) { echo $matchpoints[6]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group D<br>22/11/2022</td>
									<td style="text-align: right"><label for="score15_p"><?php echo $D1; ?></label></td>
									<td><img src="<?php echo $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"></td>
									<td class="right-team"><label for="score16_p"><?php echo $D2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score15_p'] ?> - <?php echo $userdata['score16_p'] ?></span></td>
									<td><?php if($matchids[7]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[7]."_r"], $matchresult["score".$evengameno[7]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[7]) { echo $matchpoints[7]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group F<br>23/11/2022</td>
									<td style="text-align: right"><label for="score17_p"><?php echo $F3; ?></label></td>
									<td><img src="<?php echo $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"></td>
									<td class="right-team"><label for="score18_p"><?php echo $F4; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score17_p'] ?> - <?php echo $userdata['score18_p'] ?></span></td>
									<td><?php if($matchids[8]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[8]."_r"], $matchresult["score".$evengameno[8]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[8]) { echo $matchpoints[8]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group E<br>23/11/2022</td>
									<td style="text-align: right"><label for="score19_p"><?php echo $E3; ?></label></td>
									<td><img src="<?php echo $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"></td>
									<td class="right-team"><label for="score20_p"><?php echo $E4; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score19_p'] ?> - <?php echo $userdata['score20_p'] ?></span></td>
									<td><?php if($matchids[9]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[9]."_r"], $matchresult["score".$evengameno[9]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[9]) { echo $matchpoints[9]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group E<br>23/11/2022</td>
									<td style="text-align: right"><label for="score21_p"><?php echo $E1; ?></label></td>
									<td><img src="<?php echo $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"></td>
									<td class="right-team"><label for="score22_p"><?php echo $E2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score21_p'] ?> - <?php echo $userdata['score22_p'] ?></span></td>
									<td><?php if($matchids[10]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[10]."_r"], $matchresult["score".$evengameno[10]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[10]) { echo $matchpoints[10]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group F<br>23/11/2022</td>
									<td style="text-align: right"><label for="score23_p"><?php echo $F1; ?></label></td>
									<td><img src="<?php echo $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"></td>
									<td class="right-team"><label for="score24_p"><?php echo $F2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score23_p'] ?> - <?php echo $userdata['score24_p'] ?></span></td>
									<td><?php if($matchids[11]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[11]."_r"], $matchresult["score".$evengameno[11]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[11]) { echo $matchpoints[11]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group G<br>24/11/2022</td>
									<td style="text-align: right"><label for="score25_p"><?php echo $G3; ?></label></td>
									<td><img src="<?php echo $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"></td>
									<td class="right-team"><label for="score26_p"><?php echo $G4; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score25_p'] ?> - <?php echo $userdata['score26_p'] ?></span></td>
									<td><?php if($matchids[12]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[12]."_r"], $matchresult["score".$evengameno[12]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[12]) { echo $matchpoints[12]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group H<br>24/11/2022</td>
									<td style="text-align: right"><label for="score27_p"><?php echo $H3; ?></label></td>
									<td><img src="<?php echo $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"></td>
									<td class="right-team"><label for="score28_p"><?php echo $H4; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score27_p'] ?> - <?php echo $userdata['score28_p'] ?></span></td>
									<td><?php if($matchids[13]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[13]."_r"], $matchresult["score".$evengameno[13]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[13]) { echo $matchpoints[13]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group H<br>24/11/2022</td>
									<td style="text-align: right"><label for="score29_p"><?php echo $H1; ?></label></td>
									<td><img src="<?php echo $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"></td>
									<td class="right-team"><label for="score30_p"><?php echo $H2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score29_p'] ?> - <?php echo $userdata['score30_p'] ?></span></td>
									<td><?php if($matchids[14]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[14]."_r"], $matchresult["score".$evengameno[14]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[14]) { echo $matchpoints[14]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group G<br>24/11/2022</td>
									<td style="text-align: right"><label for="score31_p"><?php echo $G1; ?></label></td>
									<td><img src="<?php echo $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"></td>
									<td class="right-team"><label for="score32_p"><?php echo $G2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score31_p'] ?> - <?php echo $userdata['score32_p'] ?></span></td>
									<td><?php if($matchids[15]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[15]."_r"], $matchresult["score".$evengameno[15]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[15]) { echo $matchpoints[15]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group B<br>25/11/2022</td>
									<td style="text-align: right"><label for="score33_p"><?php echo $B4; ?></label></td>
									<td><img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"></td>
									<td class="right-team"><label for="score34_p"><?php echo $B2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score33_p'] ?> - <?php echo $userdata['score34_p'] ?></span></td>
									<td><?php if($matchids[16]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[16]."_r"], $matchresult["score".$evengameno[16]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[16]) { echo $matchpoints[16]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group A<br>25/11/2022</td>
									<td style="text-align: right"><label for="score35_p"><?php echo $A1; ?></label></td>
									<td><img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"></td>
									<td class="right-team"><label for="score36_p"><?php echo $A3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score35_p'] ?> - <?php echo $userdata['score36_p'] ?></span></td>
									<td><?php if($matchids[17]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[17]."_r"], $matchresult["score".$evengameno[17]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[17]) { echo $matchpoints[17]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group A<br>25/11/2022</td>
									<td style="text-align: right"><label for="score37_p"><?php echo $A4; ?></label></td>
									<td><img src="<?php echo $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"></td>
									<td class="right-team"><label for="score38_p"><?php echo $A2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score37_p'] ?> - <?php echo $userdata['score38_p'] ?></span></td>
									<td><?php if($matchids[18]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[18]."_r"], $matchresult["score".$evengameno[18]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[18]) { echo $matchpoints[18]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group B<br>25/11/2022</td>
									<td style="text-align: right"><label for="score39_p"><?php echo $B1; ?></label></td>
									<td><img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"></td>
									<td class="right-team"><label for="score40_p"><?php echo $B3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score39_p'] ?> - <?php echo $userdata['score40_p'] ?></span></td>
									<td><?php if($matchids[19]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[19]."_r"], $matchresult["score".$evengameno[19]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[19]) { echo $matchpoints[19]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group D<br>26/11/2022</td>
									<td style="text-align: right"><label for="score41_p"><?php echo $D4; ?></label></td>
									<td><img src="<?php echo $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"></td>
									<td class="right-team"><label for="score42_p"><?php echo $D2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score41_p'] ?> - <?php echo $userdata['score42_p'] ?></span></td>
									<td><?php if($matchids[20]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[20]."_r"], $matchresult["score".$evengameno[20]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[20]) { echo $matchpoints[20]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group C<br>26/11/2022</td>
									<td style="text-align: right"><label for="score43_p"><?php echo $C4; ?></label></td>
									<td><img src="<?php echo $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"></td>
									<td class="right-team"><label for="score44_p"><?php echo $C2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score43_p'] ?> - <?php echo $userdata['score44_p'] ?></span></td>
									<td><?php if($matchids[21]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[21]."_r"], $matchresult["score".$evengameno[21]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[21]) { echo $matchpoints[21]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group D<br>26/11/2022</td>
									<td style="text-align: right"><label for="score45_p"><?php echo $D1; ?></label></td>
									<td><img src="<?php echo $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"></td>
									<td class="right-team"><label for="score46_p"><?php echo $D3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score45_p'] ?> - <?php echo $userdata['score46_p'] ?></span></td>
									<td><?php if($matchids[22]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[22]."_r"], $matchresult["score".$evengameno[22]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[22]) { echo $matchpoints[22]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group C<br>26/11/2022</td>
									<td style="text-align: right"><label for="score47_p"><?php echo $C1; ?></label></td>
									<td><img src="<?php echo $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"></td>
									<td class="right-team"><label for="score48_p"><?php echo $C3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score47_p'] ?> - <?php echo $userdata['score48_p'] ?></span></td>
									<td><?php if($matchids[23]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[23]."_r"], $matchresult["score".$evengameno[23]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[23]) { echo $matchpoints[23]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group E<br>27/11/2022</td>
									<td style="text-align: right"><label for="score49_p"><?php echo $E4; ?></label></td>
									<td><img src="<?php echo $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"></td>
									<td class="right-team"><label for="score50_p"><?php echo $E2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score49_p'] ?> - <?php echo $userdata['score50_p'] ?></span></td>
									<td><?php if($matchids[24]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[24]."_r"], $matchresult["score".$evengameno[24]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[24]) { echo $matchpoints[24]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group F<br>27/11/2022</td>
									<td style="text-align: right"><label for="score51_p"><?php echo $F1; ?></label></td>
									<td><img src="<?php echo $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"></td>
									<td class="right-team"><label for="score52_p"><?php echo $F3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score51_p'] ?> - <?php echo $userdata['score52_p'] ?></span></td>
									<td><?php if($matchids[25]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[25]."_r"], $matchresult["score".$evengameno[25]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[25]) { echo $matchpoints[25]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group F<br>27/11/2022</td>
									<td style="text-align: right"><label for="score53_p"><?php echo $F4; ?></label></td>
									<td><img src="<?php echo $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"></td>
									<td class="right-team"><label for="score54_p"><?php echo $F2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score53_p'] ?> - <?php echo $userdata['score54_p'] ?></span></td>
									<td><?php if($matchids[26]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[26]."_r"], $matchresult["score".$evengameno[26]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[26]) { echo $matchpoints[26]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group E<br>27/11/2022</td>
									<td style="text-align: right"><label for="score55_p"><?php echo $E1; ?></label></td>
									<td><img src="<?php echo $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"></td>
									<td class="right-team"><label for="score56_p"><?php echo $E3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score55_p'] ?> - <?php echo $userdata['score56_p'] ?></span></td>
									<td><?php if($matchids[27]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[27]."_r"], $matchresult["score".$evengameno[27]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[27]) { echo $matchpoints[27]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group G<br>28/11/2022</td>
									<td style="text-align: right"><label for="score57_p"><?php echo $G4; ?></label></td>
									<td><img src="<?php echo $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"></td>
									<td class="right-team"><label for="score58_p"><?php echo $G2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score57_p'] ?> - <?php echo $userdata['score58_p'] ?></span></td>
									<td><?php if($matchids[28]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[28]."_r"], $matchresult["score".$evengameno[28]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[28]) { echo $matchpoints[28]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group H<br>28/11/2022</td>
									<td style="text-align: right"><label for="score59_p"><?php echo $H4; ?></label></td>
									<td><img src="<?php echo $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"></td>
									<td class="right-team"><label for="score60_p"><?php echo $H2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score59_p'] ?> - <?php echo $userdata['score60_p'] ?></span></td>
									<td><?php if($matchids[29]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[29]."_r"], $matchresult["score".$evengameno[29]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[29]) { echo $matchpoints[29]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group G<br>28/11/2022</td>
									<td style="text-align: right"><label for="score61_p"><?php echo $G1; ?></label></td>
									<td><img src="<?php echo $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"></td>
									<td class="right-team"><label for="score62_p"><?php echo $G3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score61_p'] ?> - <?php echo $userdata['score62_p'] ?></span></td>
									<td><?php if($matchids[30]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[30]."_r"], $matchresult["score".$evengameno[30]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[30]) { echo $matchpoints[30]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group H<br>28/11/2022</td>
									<td style="text-align: right"><label for="score63_p"><?php echo $H1; ?></label></td>
									<td><img src="<?php echo $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"></td>
									<td class="right-team"><label for="score64_p"><?php echo $H3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score63_p'] ?> - <?php echo $userdata['score64_p'] ?></span></td>
									<td><?php if($matchids[31]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[31]."_r"], $matchresult["score".$evengameno[31]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[31]) { echo $matchpoints[31]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group A<br>29/11/2022</td>
									<td style="text-align: right"><label for="score65_p"><?php echo $A2; ?></label></td>
									<td><img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"></td>
									<td class="right-team"><label for="score66_p"><?php echo $A3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score65_p'] ?> - <?php echo $userdata['score66_p'] ?></span></td>
									<td><?php if($matchids[32]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[32]."_r"], $matchresult["score".$evengameno[32]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[32]) { echo $matchpoints[32]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group A<br>29/11/2022</td>
									<td style="text-align: right"><label for="score67_p"><?php echo $A4; ?></label></td>
									<td><img src="<?php echo $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"></td>
									<td class="right-team"><label for="score68_p"><?php echo $A1; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score67_p'] ?> - <?php echo $userdata['score68_p'] ?></span></td>
									<td><?php if($matchids[33]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[33]."_r"], $matchresult["score".$evengameno[33]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[33]) { echo $matchpoints[33]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group B<br>29/11/2022</td>
									<td style="text-align: right"><label for="score69_p"><?php echo $B4; ?></label></td>
									<td><img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"></td>
									<td class="right-team"><label for="score70_p"><?php echo $B1; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score69_p'] ?> - <?php echo $userdata['score70_p'] ?></span></td>
									<td><?php if($matchids[34]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[34]."_r"], $matchresult["score".$evengameno[34]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[34]) { echo $matchpoints[34]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group B<br>29/11/2022</td>
									<td style="text-align: right"><label for="score71_p"><?php echo $B2; ?></label></td>
									<td><img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"></td>
									<td class="right-team"><label for="score72_p"><?php echo $B3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score71_p'] ?> - <?php echo $userdata['score72_p'] ?></span></td>
									<td><?php if($matchids[35]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[35]."_r"], $matchresult["score".$evengameno[35]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[35]) { echo $matchpoints[35]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group D<br>30/11/2022</td>
									<td style="text-align: right"><label for="score73_p"><?php echo $D2; ?></label></td>
									<td><img src="<?php echo $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"></td>
									<td class="right-team"><label for="score74_p"><?php echo $D3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score73_p'] ?> - <?php echo $userdata['score74_p'] ?></span></td>
									<td><?php if($matchids[36]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[36]."_r"], $matchresult["score".$evengameno[36]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[36]) { echo $matchpoints[36]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group D<br>30/11/2022</td>
									<td style="text-align: right"><label for="score75_p"><?php echo $D4; ?></label></td>
									<td><img src="<?php echo $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"></td>
									<td class="right-team"><label for="score76_p"><?php echo $D1; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score75_p'] ?> - <?php echo $userdata['score76_p'] ?></span></td>
									<td><?php if($matchids[37]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[37]."_r"], $matchresult["score".$evengameno[37]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[37]) { echo $matchpoints[37]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group C<br>30/11/2022</td>
									<td style="text-align: right"><label for="score77_p"><?php echo $C4; ?></label></td>
									<td><img src="<?php echo $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"></td>
									<td class="right-team"><label for="score78_p"><?php echo $C1; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score77_p'] ?> - <?php echo $userdata['score78_p'] ?></span></td>
									<td><?php if($matchids[38]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[38]."_r"], $matchresult["score".$evengameno[38]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[38]) { echo $matchpoints[38]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group C<br>30/11/2022</td>
									<td style="text-align: right"><label for="score79_p"><?php echo $C2; ?></label></td>
									<td><img src="<?php echo $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"></td>
									<td class="right-team"><label for="score80_p"><?php echo $C3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score79_p'] ?> - <?php echo $userdata['score80_p'] ?></span></td>
									<td><?php if($matchids[39]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[39]."_r"], $matchresult["score".$evengameno[39]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[39]) { echo $matchpoints[39]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group F<br>01/12/2022</td>
									<td style="text-align: right"><label for="score81_p"><?php echo $F4; ?></label></td>
									<td><img src="<?php echo $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"></td>
									<td class="right-team"><label for="score82_p"><?php echo $F1; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score81_p'] ?> - <?php echo $userdata['score82_p'] ?></span></td>
									<td><?php if($matchids[40]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[40]."_r"], $matchresult["score".$evengameno[40]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[40]) { echo $matchpoints[40]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group F<br>01/12/2022</td>
									<td style="text-align: right"><label for="score83_p"><?php echo $F2; ?></label></td>
									<td><img src="<?php echo $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"></td>
									<td class="right-team"><label for="score84_p"><?php echo $F3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score83_p'] ?> - <?php echo $userdata['score84_p'] ?></span></td>
									<td><?php if($matchids[41]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[41]."_r"], $matchresult["score".$evengameno[41]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[41]) { echo $matchpoints[41]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group E<br>01/12/2022</td>
									<td style="text-align: right"><label for="score85_p"><?php echo $E4; ?></label></td>
									<td><img src="<?php echo $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"></td>
									<td class="right-team"><label for="score86_p"><?php echo $E1; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score85_p'] ?> - <?php echo $userdata['score86_p'] ?></span></td>
									<td><?php if($matchids[42]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[42]."_r"], $matchresult["score".$evengameno[42]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[42]) { echo $matchpoints[42]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group E<br>01/12/2022</td>
									<td style="text-align: right"><label for="score87_p"><?php echo $E2; ?></label></td>
									<td><img src="<?php echo $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"></td>
									<td class="right-team"><label for="score88_p"><?php echo $E3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score87_p'] ?> - <?php echo $userdata['score88_p'] ?></span></td>
									<td><?php if($matchids[43]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[43]."_r"], $matchresult["score".$evengameno[43]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[43]) { echo $matchpoints[43]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group H<br>02/12/2022</td>
									<td style="text-align: right"><label for="score89_p"><?php echo $H2; ?></label></td>
									<td><img src="<?php echo $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"></td>
									<td class="right-team"><label for="score90_p"><?php echo $H3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score89_p'] ?> - <?php echo $userdata['score90_p'] ?></span></td>
									<td><?php if($matchids[44]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[44]."_r"], $matchresult["score".$evengameno[44]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[44]) { echo $matchpoints[44]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group H<br>02/12/2022</td>
									<td style="text-align: right"><label for="score91_p"><?php echo $H4; ?></label></td>
									<td><img src="<?php echo $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"></td>
									<td class="right-team"><label for="score92_p"><?php echo $H1; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score91_p'] ?> - <?php echo $userdata['score92_p'] ?></span></td>
									<td><?php if($matchids[45]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[45]."_r"], $matchresult["score".$evengameno[45]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[45]) { echo $matchpoints[45]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group G<br>02/12/2022</td>
									<td style="text-align: right"><label for="score93_p"><?php echo $G2; ?></label></td>
									<td><img src="<?php echo $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"></td>
									<td class="right-team"><label for="score94_p"><?php echo $G3; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score93_p'] ?> - <?php echo $userdata['score94_p'] ?></span></td>
									<td><?php if($matchids[46]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[46]."_r"], $matchresult["score".$evengameno[46]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[46]) { echo $matchpoints[46]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">Group G<br>02/12/2022</td>
									<td style="text-align: right"><label for="score95_p"><?php echo $G4; ?></label></td>
									<td><img src="<?php echo $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"></td>
									<td class="right-team"><label for="score96_p"><?php echo $G1; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score95_p'] ?> - <?php echo $userdata['score96_p'] ?></span></td>
									<td><?php if($matchids[47]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[47]."_r"], $matchresult["score".$evengameno[47]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[47]) { echo $matchpoints[47]; } else { echo "-"; } ?></td>
								</tr>								
								<tr>
									<td class="small text-muted d-none d-md-block">RO16<br>03/12/2022</td>
									<td style="text-align: right"><label for="score97_p"><?php echo $R1; ?></label></td>
									<td><img src="<?php echo $R1img; ?>" alt="<?php echo $R1; ?>" title="<?php echo $R1; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $R2img; ?>" alt="<?php echo $R2; ?>" title="<?php echo $R2; ?>"></td>
									<td class="right-team"><label for="score98_p"><?php echo $R2; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score97_p'] ?> - <?php echo $userdata['score98_p'] ?></span></td>
									<td><?php if($matchids[48]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[48]."_r"], $matchresult["score".$evengameno[48]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[48]) { echo $matchpoints[48]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">RO16<br>03/12/2022</td>
									<td style="text-align: right"><label for="score99_p"><?php echo $R3; ?></label></td>
									<td><img src="<?php echo $R3img; ?>" alt="<?php echo $R3; ?>" title="<?php echo $R3; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $R4img; ?>" alt="<?php echo $R4; ?>" title="<?php echo $R4; ?>"></td>
									<td class="right-team"><label for="score100_p"><?php echo $R4; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score99_p'] ?> - <?php echo $userdata['score100_p'] ?></span></td>
									<td><?php if($matchids[49]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[49]."_r"], $matchresult["score".$evengameno[49]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[49]) { echo $matchpoints[49]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">RO16<br>04/12/2022</td>
									<td style="text-align: right"><label for="score101_p"><?php echo $R5; ?></label></td>
									<td><img src="<?php echo $R5img; ?>" alt="<?php echo $R5; ?>" title="<?php echo $R5; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $R6img; ?>" alt="<?php echo $R6; ?>" title="<?php echo $R6; ?>"></td>
									<td class="right-team"><label for="score102_p"><?php echo $R6; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score101_p'] ?> - <?php echo $userdata['score102_p'] ?></span></td>
									<td><?php if($matchids[50]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[50]."_r"], $matchresult["score".$evengameno[50]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[50]) { echo $matchpoints[50]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">RO16<br>04/12/2022</td>
									<td style="text-align: right"><label for="score103_p"><?php echo $R7; ?></label></td>
									<td><img src="<?php echo $R7img; ?>" alt="<?php echo $R7; ?>" title="<?php echo $R7; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $R8img; ?>" alt="<?php echo $R8; ?>" title="<?php echo $R8; ?>"></td>
									<td class="right-team"><label for="score104_p"><?php echo $R8; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score103_p'] ?> - <?php echo $userdata['score104_p'] ?></span></td>
									<td><?php if($matchids[51]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[51]."_r"], $matchresult["score".$evengameno[51]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[51]) { echo $matchpoints[51]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">RO16<br>05/12/2022</td>
									<td style="text-align: right"><label for="score105_p"><?php echo $R9; ?></label></td>
									<td><img src="<?php echo $R9img; ?>" alt="<?php echo $R9; ?>" title="<?php echo $R9; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $R10img; ?>" alt="<?php echo $R10; ?>" title="<?php echo $R10; ?>"></td>
									<td class="right-team"><label for="score106_p"><?php echo $R10; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score105_p'] ?> - <?php echo $userdata['score106_p'] ?></span></td>
									<td><?php if($matchids[52]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[52]."_r"], $matchresult["score".$evengameno[52]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[52]) { echo $matchpoints[52]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">RO16<br>05/12/2022</td>
									<td style="text-align: right"><label for="score107_p"><?php echo $R11; ?></label></td>
									<td><img src="<?php echo $R11img; ?>" alt="<?php echo $R11; ?>" title="<?php echo $R11; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $R12img; ?>" alt="<?php echo $R12; ?>" title="<?php echo $R12; ?>"></td>
									<td class="right-team"><label for="score108_p"><?php echo $R12; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score107_p'] ?> - <?php echo $userdata['score108_p'] ?></span></td>
									<td><?php if($matchids[53]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[53]."_r"], $matchresult["score".$evengameno[53]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[53]) { echo $matchpoints[53]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">RO16<br>06/12/2022</td>
									<td style="text-align: right"><label for="score109_p"><?php echo $R13; ?></label></td>
									<td><img src="<?php echo $R13img; ?>" alt="<?php echo $R13; ?>" title="<?php echo $R13; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $R14img; ?>" alt="<?php echo $R14; ?>" title="<?php echo $R14; ?>"></td>
									<td class="right-team"><label for="score110_p"><?php echo $R14; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score109_p'] ?> - <?php echo $userdata['score110_p'] ?></span></td>
									<td><?php if($matchids[54]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[54]."_r"], $matchresult["score".$evengameno[54]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[54]) { echo $matchpoints[54]; } else { echo "-"; } ?></td>
								</tr>
								<tr>
									<td class="small text-muted d-none d-md-block">RO16<br>04/12/2022</td>
									<td style="text-align: right"><label for="score111_p"><?php echo $R15; ?></label></td>
									<td><img src="<?php echo $R15img; ?>" alt="<?php echo $R15; ?>" title="<?php echo $R15; ?>"></td>
									<td align="center"><span>v</span></td>
									<td><img src="<?php echo $R16img; ?>" alt="<?php echo $R16; ?>" title="<?php echo $R16; ?>"></td>
									<td class="right-team"><label for="score112_p"><?php echo $R16; ?></label></td>
									<td><span class="prediction"><?php echo $userdata['score111_p'] ?> - <?php echo $userdata['score112_p'] ?></span></td>
									<td><?php if($matchids[55]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[55]."_r"], $matchresult["score".$evengameno[55]."_r"]); } else echo "N/A"; ?></td>
									<td><?php if($matchids[55]) { echo $matchpoints[55]; } else { echo "-"; } ?></td>
								</tr>
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
