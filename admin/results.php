<?php
session_start();
$page_title = 'Results';

if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en-GB">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <meta http-equiv="Content-Type" content="text/html">    
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
    <meta name="keywords" content="">
	<title><?= $page_title ?> - Hendy's Hunches</title>
    <link href="../ico/favicon.ico" rel="icon">
    <!-- Vendor CSS Files -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS Files -->
    <link href="../css/styles.css" rel="stylesheet">    
    <!-- Include PHP Config File -->
    <?php include "../php/config.php" ?>
    <!--jQuery Files -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
	<style>
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
			<img src="../img/hh-icon-2024.png" class="img-fluid bg-light mx-2" style="--bs-bg-opacity: 0.80" width="50px">
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
		              <a class="nav-link" aria-current="page" href="../dashboard.php">Dashboard</a>
		            </li>
		            <li class="nav-item">
		              <a class="nav-link" href="../predictions.php">Submit Predictions</a>
		            </li>
								<li class="nav-item">
		              <a class="nav-link" href="../rankings.php">Rankings</a>
		            </li>
								<li class="nav-item">
		              <a class="nav-link" href="../how-it-works.php">How It Works</a>
		            </li>
					<li class="nav-item">
						<a class="nav-link" href="../about.php">About</a>
					</li>
		          </ul>
		        </div>
		      </div>
		    </div>
		  </nav>


  <?php

		// Connect to the database
	 	include '../php/db-connect.php';

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

		// for ($i=0; $i<35; $i++) {
		// 	if ($matchids[$i]) {
		// 		$matchstatus[$i] = 'True';
		// 		// Return existing match values from DB
		// 		// Disable input buttons
		// 	}
		// 	else $matchstatus[$i] = 'False';
		// }

		$matchresult = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));
		?>


	<!-- Main Content Section -->
	<main id="main" class="main">

		<div class="pagetitle d-flex justify-content-between">
		<nav>
		<h1>Record Match Results (Admin)</h1>
			<!-- <ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="home.php">Home</a></li>
			<li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
			<li class="breadcrumb-item active">Part #3 - 11.30</li>
			</ol> -->
		</nav> 
		</div><!-- End Page Title -->

		<section class="section">
			<p class="lead">Admin page to record match results and update the game.</p>

        <form id="resultForm" action="../php/insert-result.php" method="POST">

					<!-- Placeholder for JSON table construction -->
	        <table id="table" class="table table-sm table-striped">
	            <script>
	                $(document).ready(function () {
	                    // Fetch data from JSON file
	                    $.getJSON("../json/uefa-euro-2024-fixtures-groups.json",
	                    	function (data) {
	                        var fixture = '';
													var x = 1;
													var y = 2;
	                        // Iterate through objects
	                        $.each(data, function (key, value) {
								var homeTeam = value.HomeTeam;
								var awayTeam = value.AwayTeam;
								var homeTeamFlag = "../flag-icons/24/" + homeTeam.toLowerCase().replaceAll(' ', '-') + ".png";
								var awayTeamFlag = "../flag-icons/24/" + awayTeam.toLowerCase().replaceAll(' ', '-') + ".png";
								const str = value.DateUtc;
								const [dateValues, timeValues] = str.split(' ');
								const [year, month, day] = dateValues.split('-');
								const [hours, minutes] = timeValues.split(':');
								const date = new Date(+year, +month - 1, +day, +hours, +minutes).toLocaleString().slice(0, -3);
	                            fixture += '<tr>';
								fixture += '<td class="small text-muted">' + value.Group + '</td>';
	                            fixture += '<td>' + value.HomeTeam + '</td>';
								fixture += '<td><img src="' + homeTeamFlag + '" alt="Flag of ' + homeTeam + '" title="Flag of ' + homeTeam + '"></td>';
								fixture += '<td><input type="text" id="score' + x + '_r" name="score' + x + '_r" class="form-control" /></td>';
								fixture += '<td align="center">v<br><span class="badge bg-light text-primary">' + value.MatchNumber + '</span></td>';
								fixture += '<td><input type="text" id="score' + y + '_r" name="score' + y + '_r" class="form-control" /></td>';
								fixture += '<td><img src="' + awayTeamFlag + '" alt="Flag of ' + awayTeam + '" title="Flag of ' + awayTeam + '"></td>';
	                            fixture += '<td>' + value.AwayTeam + '</td>';
	                            fixture += '<td class="small text-muted"> ' + date + '</td>';
								fixture += '<td class="date-venue">Match Recorded: </td>';
	                            fixture += '</tr>';
								
								x+=2;
								y+=2;
	                        });
	                      // Insert rows into table
	                      $('#table').append(fixture);
	                    });
	                });
	            </script>
				</table>
				<input type="submit" class="btn btn-primary" value="Submit Results" />
				<input type="reset" class="btn btn-default" value="Reset All" />
				</form>
			</section>

			</main>
<!--
		<td class="date-venue">Match Recorded: <?php echo $matchstatus[0]; ?></td>
		<td class="update-button"><input type="submit" id="updateBtn1" class="btn btn-success btn-sm" value="Update this result" /></td>
		</tr>
-->
    <!-- Footer -->
    <?php include "php/footer.php" ?>