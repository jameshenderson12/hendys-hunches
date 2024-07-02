<?php
session_start();
$page_title = 'View Predictions';

if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
    header("Location: index.php");
    exit();
}

include "php/header.php";
include "php/navigation.php";

?>

<!-- Main Content Section -->
<main id="main" class="main">

	<?php
					// Connect to the database
					include 'php/db-connect.php';

					// Set up variable to capture result of SQL query to retrieve data from database tables
					$sql_getuserinfo = "SELECT live_user_predictions_groups.*, live_user_information.avatar, live_user_information.faveteam, live_user_information.fieldofwork, live_user_information.location, live_user_information.tournwinner, live_user_information.currpos
															FROM live_user_predictions_groups INNER JOIN live_user_information
															ON live_user_predictions_groups.id = live_user_information.id
															WHERE live_user_predictions_groups.id='".$_GET["id"]."'";

					$sql_getuserro16 = "SELECT live_user_predictions_ro16.*, live_user_information.avatar, live_user_information.faveteam, live_user_information.fieldofwork, live_user_information.location, live_user_information.tournwinner, live_user_information.currpos
					 										FROM live_user_predictions_ro16 INNER JOIN live_user_information
					 										ON live_user_predictions_ro16.id = live_user_information.id
					 										WHERE live_user_predictions_ro16.id='".$_GET["id"]."'";

					$sql_getuserqf = "SELECT live_user_predictions_qf.*, live_user_information.avatar, live_user_information.faveteam, live_user_information.fieldofwork, live_user_information.location, live_user_information.tournwinner, live_user_information.currpos
					 										FROM live_user_predictions_qf INNER JOIN live_user_information
					 										ON live_user_predictions_qf.id = live_user_information.id
					 										WHERE live_user_predictions_qf.id='".$_GET["id"]."'";

					// $sql_getusersf = "SELECT live_user_predictions_sf.*, live_user_information.avatar, live_user_information.faveteam, live_user_information.fieldofwork, live_user_information.location, live_user_information.tournwinner, live_user_information.currpos
					// 										FROM live_user_predictions_sf INNER JOIN live_user_information
					// 										ON live_user_predictions_sf.id = live_user_information.id
					// 										WHERE live_user_predictions_sf.id='".$_GET["id"]."'";

					// $sql_getuserfi = "SELECT live_user_predictions_final.*, live_user_information.avatar, live_user_information.faveteam, live_user_information.fieldofwork, live_user_information.location, live_user_information.tournwinner, live_user_information.currpos
					// 										FROM live_user_predictions_final INNER JOIN live_user_information
					// 										ON live_user_predictions_final.id = live_user_information.id
					// 										WHERE live_user_predictions_final.id='".$_GET["id"]."'";															

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
					SUM(score91_r) as score91_r, SUM(score92_r) as score92_r, SUM(score93_r) as score93_r, SUM(score94_r) as score94_r, SUM(score95_r) as score95_r, SUM(score96_r) as score96_r, SUM(score97_r) as score97_r, SUM(score98_r) as score98_r, SUM(score99_r) as score99_r, SUM(score100_r) as score100_r,
					SUM(score101_r) as score101_r, SUM(score102_r) as score102_r FROM live_match_results";

					$sql_getid = "SELECT match_id FROM live_match_results";

					// Create an array of match ids
					$list_of_ids = mysqli_query($con, $sql_getid);
					while($row = mysqli_fetch_array($list_of_ids)) {
							$matchids[] = $row['match_id'];
					}

					$userdata = mysqli_fetch_assoc(mysqli_query($con, $sql_getuserinfo));					
					$userdata2 = mysqli_fetch_assoc(mysqli_query($con, $sql_getuserro16));
					$result = mysqli_query($con, $sql_getuserqf);
					if (!$result || mysqli_num_rows($result) == 0) {
						$userdata3 = array('message' => 'No data available');
					} else {
						$userdata3 = mysqli_fetch_assoc($result);
					}
					// $userdata4 = mysqli_fetch_assoc(mysqli_query($con, $sql_getusersf));
					// $userdata5 = mysqli_fetch_assoc(mysqli_query($con, $sql_getuserfi));
					$uppCaseFN = ucfirst($userdata["firstname"]);
					$uppCaseSN = ucfirst($userdata["surname"]);
					$userid = $userdata["id"];
					$avatar = $userdata["avatar"];
					$fieldofwork = $userdata["fieldofwork"];
					$location = $userdata["location"];
					$faveteam = $userdata["faveteam"];
					$tournwinner = $userdata["tournwinner"];
						// Get string for tournwinner flag (convert to lowercase)
						$tournwinner_lower = strtolower($tournwinner);
						// Replace spaces with hyphens
						$tournwinner_kebab = str_replace(' ', '-', $tournwinner_lower);
						// Construct the file path
						$tournwinnerflag = "flag-icons/24/{$tournwinner_kebab}.png";
					$currentpos = ordinal($userdata["currpos"]);

					$pointstotal = $userdata["points_total"];
					$pointstotal2 = $userdata2["points_total"];
					$pointstotal3 = $userdata3["points_total"];
					// $pointstotal4 = $userdata4["points_total"];
					// $pointstotal5 = $userdata5["points_total"];
					//$pointstotal = $pointstotal1 + $pointstotal2 + $pointstotal3 + $pointstotal4 + $pointstotal5;
					$pointstotal = $pointstotal + $pointstotal2 + $pointstotal3;
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
					// $sql_getspecid = "SELECT id, firstname, surname, score1_p, score2_p, score3_p, score4_p, score5_p, score6_p, score7_p, score8_p, score9_p, score10_p,
					// score11_p, score12_p, score13_p, score14_p, score15_p, score16_p, score17_p, score18_p, score19_p, score20_p, score21_p, score22_p, score23_p,
					// score24_p, score25_p, score26_p, score27_p, score28_p, score29_p, score30_p, score31_p, score32_p, score33_p, score34_p, score35_p, score36_p,
					// score37_p, score38_p, score39_p, score40_p, score41_p, score42_p, score43_p, score44_p, score45_p, score46_p, score47_p, score48_p, score49_p,
					// score50_p, score51_p, score52_p, score53_p, score54_p, score55_p, score56_p, score57_p, score58_p, score59_p, score60_p, score61_p, score62_p,
					// score63_p, score64_p, score65_p, score66_p, score67_p, score68_p, score69_p, score70_p, score71_p, score72_p FROM live_user_predictions_groups WHERE id='".$userid."'";
					// $pvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecid));
					// $rvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));

					// for ($gameno=1; $gameno<103; $gameno+=2) {
					// 		$oddgameno[] = $gameno;
					// 		$evengameno[] = $gameno + 1;
					// }

					// for ($i=0; $i<=35; $i++) {
					// 		$matchpoints[$i] = 0;

					// 		if( is_numeric($pvalue["score".$oddgameno[$i]."_p"]) && is_numeric($pvalue["score".$evengameno[$i]."_p"]) ) {

					// 			if($pvalue["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) {
					// 				$matchpoints[$i] += 1;
					// 			}
					// 			if($pvalue["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"]) {
					// 				$matchpoints[$i] += 1;
					// 			}
					// 			if (($pvalue["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) && ($pvalue["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"])) {
					// 				$matchpoints[$i] += 3;
					// 			}

					// 			if ((($pvalue["score".$oddgameno[$i]."_p"] > $pvalue["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] > $rvalue["score".$evengameno[$i]."_r"]))
					// 			|| (($pvalue["score".$oddgameno[$i]."_p"] < $pvalue["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] < $rvalue["score".$evengameno[$i]."_r"]))
					// 			|| (($pvalue["score".$oddgameno[$i]."_p"] === $pvalue["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] === $rvalue["score".$evengameno[$i]."_r"])) ) {
					// 				$matchpoints[$i] += 2;
					// 			}
					// 		}
					// }

					// $sql_getspecidro16 = "SELECT id, firstname, surname, score73_p, score74_p, score75_p, score76_p, score77_p, score78_p, score79_p, score80_p, score81_p, score82_p, score83_p, score84_p, score85_p, score86_p, score87_p, score88_p FROM live_user_predictions_ro16 WHERE id='".$userid."'";
					// $pval = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecidro16));

					// for ($i=36; $i<=43; $i++) {
					// 		$matchpoints[$i] = 0;

					// 		if( is_numeric($pval["score".$oddgameno[$i]."_p"]) && is_numeric($pval["score".$evengameno[$i]."_p"]) ) {

					// 			if($pval["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) {
					// 				$matchpoints[$i] += 1;
					// 			}
					// 			if($pval["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"]) {
					// 				$matchpoints[$i] += 1;
					// 			}
					// 			if (($pval["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) && ($pval["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"])) {
					// 				$matchpoints[$i] += 3;
					// 			}
					// 			if ((($pval["score".$oddgameno[$i]."_p"] > $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] > $rvalue["score".$evengameno[$i]."_r"]))
					// 			|| (($pval["score".$oddgameno[$i]."_p"] < $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] < $rvalue["score".$evengameno[$i]."_r"]))
					// 			|| (($pval["score".$oddgameno[$i]."_p"] === $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] === $rvalue["score".$evengameno[$i]."_r"])) ) {
					// 				$matchpoints[$i] += 2;
					// 			}
					// 		}
					// }

					$sql_getspecidqf = "SELECT id, firstname, surname, score89_p, score90_p, score91_p, score92_p, score93_p, score94_p, score95_p, score96_p FROM live_user_predictions_qf WHERE id='".$userid."'";
					$pval = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecidqf));

					for ($i=44; $i<=48; $i++) {
							$matchpoints[$i] = 0;

							if( is_numeric($pval["score".$oddgameno[$i]."_p"]) && is_numeric($pval["score".$evengameno[$i]."_p"]) ) {

								if($pval["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) {
									$matchpoints[$i] += 1;
								}
								if($pval["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"]) {
									$matchpoints[$i] += 1;
								}
								if (($pval["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) && ($pval["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"])) {
									$matchpoints[$i] += 3;
								}
								if ((($pval["score".$oddgameno[$i]."_p"] > $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] > $rvalue["score".$evengameno[$i]."_r"]))
								|| (($pval["score".$oddgameno[$i]."_p"] < $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] < $rvalue["score".$evengameno[$i]."_r"]))
								|| (($pval["score".$oddgameno[$i]."_p"] === $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] === $rvalue["score".$evengameno[$i]."_r"])) ) {
									$matchpoints[$i] += 2;
								}
							}
					}

					// $sql_getspecidsf = "SELECT id, firstname, surname, score121_p, score122_p, score123_p, score124_p FROM live_user_predictions_sf WHERE id='".$userid."'";
					// $pval = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecidsf));

					// for ($i=60; $i<=61; $i++) {
					// 		$matchpoints[$i] = 0;

					// 		if( is_numeric($pval["score".$oddgameno[$i]."_p"]) && is_numeric($pval["score".$evengameno[$i]."_p"]) ) {

					// 			if($pval["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) {
					// 				$matchpoints[$i] += 1;
					// 			}
					// 			if($pval["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"]) {
					// 				$matchpoints[$i] += 1;
					// 			}
					// 			if (($pval["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) && ($pval["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"])) {
					// 				$matchpoints[$i] += 3;
					// 			}
					// 			if ((($pval["score".$oddgameno[$i]."_p"] > $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] > $rvalue["score".$evengameno[$i]."_r"]))
					// 			|| (($pval["score".$oddgameno[$i]."_p"] < $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] < $rvalue["score".$evengameno[$i]."_r"]))
					// 			|| (($pval["score".$oddgameno[$i]."_p"] === $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] === $rvalue["score".$evengameno[$i]."_r"])) ) {
					// 				$matchpoints[$i] += 2;
					// 			}
					// 		}
					// }

					// $sql_getspecidfi = "SELECT id, firstname, surname, score125_p, score126_p, score127_p, score128_p FROM live_user_predictions_final WHERE id='".$userid."'";
					// $pval = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecidfi));

					// for ($i=62; $i<=63; $i++) {
					// 		$matchpoints[$i] = 0;

					// 		if( is_numeric($pval["score".$oddgameno[$i]."_p"]) && is_numeric($pval["score".$evengameno[$i]."_p"]) ) {

					// 			if($pval["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) {
					// 				$matchpoints[$i] += 1;
					// 			}
					// 			if($pval["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"]) {
					// 				$matchpoints[$i] += 1;
					// 			}
					// 			if (($pval["score".$oddgameno[$i]."_p"] === $rvalue["score".$oddgameno[$i]."_r"]) && ($pval["score".$evengameno[$i]."_p"] === $rvalue["score".$evengameno[$i]."_r"])) {
					// 				$matchpoints[$i] += 3;
					// 			}
					// 			if ((($pval["score".$oddgameno[$i]."_p"] > $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] > $rvalue["score".$evengameno[$i]."_r"]))
					// 			|| (($pval["score".$oddgameno[$i]."_p"] < $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] < $rvalue["score".$evengameno[$i]."_r"]))
					// 			|| (($pval["score".$oddgameno[$i]."_p"] === $pval["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] === $rvalue["score".$evengameno[$i]."_r"])) ) {
					// 				$matchpoints[$i] += 2;
					// 			}
					// 		}
					// }

	// Convert PHP array to JSON
	$userdata_json = json_encode($userdata3);	

?>

<div class="pagetitle d-flex justify-content-between">
    <nav>
	<h1 class="page-header">Predictions by <?php print "$uppCaseFN $uppCaseSN" ?></h1>
        <!-- <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
          <li class="breadcrumb-item active">Part #3 - 11.30</li>
        </ol> -->
      </nav> 
    </div><!-- End Page Title -->

<section class="section">
    <p>Currently viewing predictions by <?php print "$uppCaseFN $uppCaseSN" ?>. Return to the <a href="rankings.php">rankings</a> table.</p>
	      <div class="row">
					<div class="col-md-3 col-sm-12">
					<div class="card">
					  <img src="<?php echo $avatar ?>" id="avatar" class="img-fluid mx-auto p-2" alt="User Avatar" name="User Avatar" width="100">
					  <div class="card-body">
						<!-- <h5 class="card-title"></h5> -->
					    <p style="text-align: center; font-weight: bolder; font-size: 30px;"><?php printf("%s pts", $pointstotal); ?></p>					    
					  </div>
					  <ul class="list-group list-group-flush">
						<li class="list-group-item"><?php printf ("<strong>Tournament winner:</strong><br> <img src='%s' alt='National flag of %s'> %s", $tournwinnerflag, $tournwinner, $tournwinner); ?></li>
					    <li class="list-group-item"><?php printf ("<strong>Favourite team:</strong><br> %s", $faveteam); ?></li>
					    <li class="list-group-item"><?php printf ("<strong>Location:</strong><br> %s", $location); ?></li>
						<li class="list-group-item"><?php printf ("<strong>Field of expertise:</strong><br> %s", $fieldofwork); ?></li>
					  </ul>
					</div>
				</div>
				<div class="col-md-9 col-sm-12">
					<div class="card">
						<div class="card-body">
							<!-- Placeholder for JSON table construction -->
							<table id="table" class="table table-sm table-striped">
								<thead>
									<tr>
										<th class="d-none d-md-table-cell"></th>
										<th class="d-none d-md-table-cell"></th>
										<th></th>
										<th></th>
										<th></th>
										<th class="d-none d-md-table-cell"></th>
										<th>Pred.</th>
										<th>Res.</th>
										<th>Pts</th>
									</tr>
								</thead>
								<tbody>
									<!-- Rows will be appended here by JavaScript -->
								</tbody>
							</table>
						</div>
					</div>
				</div>
      	</div><!--row-->
    </section>

</main>


<script>
$(document).ready(function () {
// Fetch data from JSON file
$.getJSON("json/uefa-euro-2024-fixtures-quarters.json", function (data) {
	let fixture = '';
	let m = 0, x = 89, y = 90;
	const userdata3 = <?php echo $userdata_json; ?>;

        // Function to calculate points
        function calculatePoints(prediction, result) {
            const [predictedHome, predictedAway] = prediction.split(' - ').map(Number);
            const [actualHome, actualAway] = result.split(' - ').map(Number);

            if (isNaN(predictedHome) || isNaN(predictedAway) || isNaN(actualHome) || isNaN(actualAway)) {
                return ''; // If any score is not a number, return empty string
            }

            const predictedOutcome = predictedHome > predictedAway ? 'home' : predictedHome < predictedAway ? 'away' : 'draw';
            const actualOutcome = actualHome > actualAway ? 'home' : actualHome < actualAway ? 'away' : 'draw';

            if (predictedHome === actualHome && predictedAway === actualAway) {
                return 7; // Both scores correct
            }
            if (predictedOutcome === actualOutcome) {
                if (predictedHome === actualHome || predictedAway === actualAway) {
                    return 3; // Correct outcome and one correct score
                }
                return 2; // Correct outcome only
            }
            if (predictedHome === actualHome || predictedAway === actualAway) {
                return 1; // One correct score only
            }
            return 0; // No points
        }


        // Iterate through objects
        $.each(data, function (key, value) {
            const homeTeam = value.HomeTeam;
            const awayTeam = value.AwayTeam;
            const homeTeamFlag = `flag-icons/24/${homeTeam.toLowerCase().replaceAll(' ', '-')}.png`;
            const awayTeamFlag = `flag-icons/24/${awayTeam.toLowerCase().replaceAll(' ', '-')}.png`;
            const homeTeamScore = value.HomeTeamScore ?? "";
            const awayTeamScore = value.AwayTeamScore ?? "";
            const dateStr = value.DateUtc;
            const [dateValues, timeValues] = dateStr.split(' ');
            const [year, month, day] = dateValues.split('-');
            const [hours, minutes] = timeValues.split(':');
            const date = new Date(+year, +month - 1, +day, +hours, +minutes).toLocaleString().slice(0, -3);
            const group = value.Group;
			const stage = value.RoundNumber;
            const matchNumber = value.MatchNumber;
            const roundNumber = value.RoundNumber;
            const location = value.Location;
            const prediction = `${userdata3['score' + x + '_p']} - ${userdata3['score' + y + '_p']}`;
            const result = homeTeamScore !== "" && awayTeamScore !== "" ? `${homeTeamScore} - ${awayTeamScore}` : "";
            const points = result ? calculatePoints(prediction, result) : '';

            fixture += `
                <tr>
                    <td class="small text-muted d-none d-md-table-cell">${stage}<br>${date}</td>
                    <td class="d-none d-md-table-cell" style="text-align: right">${homeTeam}</td>
                    <td><img src="${homeTeamFlag}" alt="Flag of ${homeTeam}" title="Flag of ${homeTeam}" class="img-fluid"></td>
                    <td>v</td>
                    <td><img src="${awayTeamFlag}" alt="Flag of ${awayTeam}" title="Flag of ${awayTeam}" class="img-fluid"></td>
                    <td class="d-none d-md-table-cell" class="right-team">${awayTeam}</td>            
                    <td><span class="prediction">${prediction}</span></td>
                    <td><span class="result">${result}</span></td>
                    <td><span class="points">${points}</span></td>
                </tr>
            `;

            m++;
            x += 2;
            y += 2;
        });

        // Insert rows into table
        $('#table tbody').append(fixture);
        
        // Initialize Bootstrap tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

    });
});
</script>

<!-- Footer -->
<?php include "php/footer.php" ?>
