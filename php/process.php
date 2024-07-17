<?php

function setInitialRanking() {
	// Connect to the database
	include 'db-connect.php';

	// Create a query to return the rankings information
	$sql_getrankings = "SELECT * FROM live_user_information";

	// Execute the query and return the results or display an appropriate error message
	$rankings = mysqli_query($con, $sql_getrankings) or die(mysqli_error());

	// Carry out the following actions for each row in the returned results
    while ($row = mysqli_fetch_assoc($rankings)) {
		// Capture last position and current position before updating
		$sql_setinitialrank = "UPDATE live_user_information SET lastpos = startpos, currpos = startpos WHERE NOT EXISTS (SELECT * FROM live_match_results)";
		mysqli_query($con, $sql_setinitialrank) or die(mysql_error());
	}
    // Close the database connection
    mysqli_close($con);
}

function updateMoveStatus() {
	// Connect to the database
	include 'db-connect.php';

	// Create a query to return the rankings information
	$sql_getrankings = "SELECT lui.id, lui.firstname, lui.surname, lui.avatar, lui.faveteam, lui.startpos, lui.currpos, lui.lastpos, 
						(lup_groups.points_total + 
						IFNULL(lup_ro16.points_total, 0) + 
						IFNULL(lup_qf.points_total, 0) + 
						IFNULL(lup_sf.points_total, 0) + 
						IFNULL(lup_fi.points_total, 0)) AS points_total,
						FIND_IN_SET(
							(lup_groups.points_total + 
							IFNULL(lup_ro16.points_total, 0) + 
							IFNULL(lup_qf.points_total, 0) + 
							IFNULL(lup_sf.points_total, 0) + 
							IFNULL(lup_fi.points_total, 0)), 
							(
								SELECT GROUP_CONCAT(
									DISTINCT (lup_groups.points_total + 
											IFNULL(lup_ro16.points_total, 0) + 
											IFNULL(lup_qf.points_total, 0) + 
											IFNULL(lup_sf.points_total, 0) + 
											IFNULL(lup_fi.points_total, 0)) 
									ORDER BY (lup_groups.points_total + 
											IFNULL(lup_ro16.points_total, 0) + 
											IFNULL(lup_qf.points_total, 0) + 
											IFNULL(lup_sf.points_total, 0) + 
											IFNULL(lup_fi.points_total, 0)) DESC
								)
								FROM live_user_predictions_groups lup_groups
								LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lup_groups.id = lup_ro16.id
								LEFT JOIN live_user_predictions_qf lup_qf ON lup_groups.id = lup_qf.id
								LEFT JOIN live_user_predictions_sf lup_sf ON lup_groups.id = lup_sf.id
								LEFT JOIN live_user_predictions_final lup_fi ON lup_groups.id = lup_fi.id
							)
						) AS rank
					FROM live_user_information lui
					INNER JOIN live_user_predictions_groups lup_groups ON lui.id = lup_groups.id
					LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lui.id = lup_ro16.id
					LEFT JOIN live_user_predictions_qf lup_qf ON lui.id = lup_qf.id
					LEFT JOIN live_user_predictions_sf lup_sf ON lui.id = lup_sf.id
					LEFT JOIN live_user_predictions_final lup_fi ON lui.id = lup_fi.id
					ORDER BY rank ASC, surname ASC";

	// Execute the query and return the results or display an appropriate error message
	$rankings = mysqli_query($con, $sql_getrankings) or die(mysqli_error());

	// Carry out the following actions for each row in the returned results
    while ($row = mysqli_fetch_assoc($rankings)) {
		// Capture last position and current position before updating
		$id = $row["id"];
		$fn = $row["firstname"];
		$lastpos = $row["lastpos"];
		$currpos = $row["currpos"];
		$rank = $row["rank"];

		$sql_updatelastpos = "UPDATE live_user_information SET lastpos = $currpos WHERE id=$id";
		$sql_updatecurrpos = "UPDATE live_user_information SET currpos = $rank WHERE id=$id";

		mysqli_query($con, $sql_updatelastpos) or die(mysqli_error());
		mysqli_query($con, $sql_updatecurrpos) or die(mysqli_error());
	}
    // Close the database connection
    mysqli_close($con);
}

function console_log($data){
  echo '<script>';
  echo 'console.log('. json_encode($data) .')';
  echo '</script>';
}

function compareValues() {
	// Initialise global variables
	$identical_points = 3;
	$outcome_points = 2;
	$score_points = 1;
	$blank_points = 0;
	$ids = array();

	// Connect to the database
	include 'db-connect.php';

	// Global SQL query strings
	$sql_getresults = "SELECT SUM(score1_r) as score1_r, SUM(score2_r) as score2_r, SUM(score3_r) as score3_r, SUM(score4_r) as score4_r, SUM(score5_r) as score5_r, SUM(score6_r) as score6_r, SUM(score7_r) as score7_r, SUM(score8_r) as score8_r, SUM(score9_r) as score9_r, SUM(score10_r) as score10_r,
	SUM(score11_r) as score11_r, SUM(score12_r) as score12_r, SUM(score13_r) as score13_r, SUM(score14_r) as score14_r, SUM(score15_r) as score15_r, SUM(score16_r) as score16_r, SUM(score17_r) as score17_r, SUM(score18_r) as score18_r, SUM(score19_r) as score19_r, SUM(score20_r) as score20_r,
	SUM(score21_r) as score21_r, SUM(score22_r) as score22_r, SUM(score23_r) as score23_r, SUM(score24_r) as score24_r, SUM(score25_r) as score25_r, SUM(score26_r) as score26_r, SUM(score27_r) as score27_r, SUM(score28_r) as score28_r, SUM(score29_r) as score29_r, SUM(score30_r) as score30_r,
	SUM(score31_r) as score31_r, SUM(score32_r) as score32_r, SUM(score33_r) as score33_r, SUM(score34_r) as score34_r, SUM(score35_r) as score35_r, SUM(score36_r) as score36_r, SUM(score37_r) as score37_r, SUM(score38_r) as score38_r, SUM(score39_r) as score39_r, SUM(score40_r) as score40_r,
	SUM(score41_r) as score41_r, SUM(score42_r) as score42_r, SUM(score43_r) as score43_r, SUM(score44_r) as score44_r, SUM(score45_r) as score45_r, SUM(score46_r) as score46_r, SUM(score47_r) as score47_r, SUM(score48_r) as score48_r, SUM(score49_r) as score49_r, SUM(score50_r) as score50_r,
	SUM(score51_r) as score51_r, SUM(score52_r) as score52_r, SUM(score53_r) as score53_r, SUM(score54_r) as score54_r, SUM(score55_r) as score55_r, SUM(score56_r) as score56_r, SUM(score57_r) as score57_r, SUM(score58_r) as score58_r, SUM(score59_r) as score59_r, SUM(score60_r) as score60_r,
	SUM(score61_r) as score61_r, SUM(score62_r) as score62_r, SUM(score63_r) as score63_r, SUM(score64_r) as score64_r, SUM(score65_r) as score65_r, SUM(score66_r) as score66_r, SUM(score67_r) as score67_r, SUM(score68_r) as score68_r, SUM(score69_r) as score69_r, SUM(score70_r) as score70_r,
	SUM(score71_r) as score71_r, SUM(score72_r) as score72_r FROM live_match_results";
	$sql_getusernames = "SELECT username FROM live_user_predictions_groups";
	$sql_setzero = "UPDATE live_user_predictions_groups SET points_total = 0";

	// Return all match fixtures (ids)
	$sql_getmatchid = "SELECT match_id FROM live_match_results";

	// Create an array of match ids
	$list_of_matchids = mysqli_query($con, $sql_getmatchid);
	while($row = mysqli_fetch_array($list_of_matchids)) {
		$matchids[] = $row['match_id'];
	}

	// Create an array of usernames
	mysqli_query($con, $sql_setzero);
	$list_of_usernames = mysqli_query($con, $sql_getusernames);
	while($row = mysqli_fetch_array($list_of_usernames)) {
		$usernames[] = $row['username'];
	}
	// Check all predictions for any matched scores against results
	foreach ($usernames as $usernamevalue) {

		// SQL query strings to be looped on username value
		$sql_getspecificuser = "SELECT username, firstname, surname, score1_p, score2_p, score3_p, score4_p, score5_p, score6_p, score7_p, score8_p, score9_p, score10_p,
		score11_p, score12_p, score13_p, score14_p, score15_p, score16_p, score17_p, score18_p, score19_p, score20_p, score21_p, score22_p, score23_p, score24_p, score25_p,
		score26_p, score27_p, score28_p, score29_p, score30_p, score31_p, score32_p, score33_p, score34_p, score35_p, score36_p, score37_p, score38_p, score39_p, score40_p,
		score41_p, score42_p, score43_p, score44_p, score45_p, score46_p, score47_p, score48_p, score49_p, score50_p, score51_p, score52_p, score53_p, score54_p, score55_p,
		score56_p, score57_p, score58_p, score59_p, score60_p, score61_p, score62_p, score63_p, score64_p, score65_p, score66_p, score67_p, score68_p, score69_p, score70_p,
		score71_p, score72_p FROM live_user_predictions_groups WHERE username='".$usernamevalue."'";
		$sql_setblankpoints = "UPDATE live_user_predictions_groups SET points_total = points_total + '".$blank_points."' WHERE username='".$usernamevalue."'";
		$sql_setscorepoints = "UPDATE live_user_predictions_groups SET points_total = points_total + '".$score_points."' WHERE username='".$usernamevalue."'";

		$pvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecificuser));
		$rvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));


		// Determine a matched score for home or away result
		//==================================================
		// Cycle through the number of potential matches (e.g. 1-48)
		$length = sizeof($matchids) * 2;

		for ($i=1; $i<=$length; $i++) {
			if($pvalue["score".$i."_p"] === $rvalue["score".$i."_r"]) {
				//printf ("%s %s's Prediction: %s, Result: %s", $pvalue["firstname"], $pvalue["surname"], $pvalue["score".$i."_p"], $rvalue["score".$i."_r"]);
				mysqli_query($con, $sql_setscorepoints);
				//print "<br />The results are a match - well done!<br /><br />";
			}
			else {
				//printf ("%s %s's Prediction: %s, Result: %s", $pvalue["firstname"], $pvalue["surname"], $pvalue["score".$i."_p"], $rvalue["score".$i."_r"]);
				//print "<br />No match - better luck next time!<br /><br />";
			}
		}

		// Determine a correct match outcome (home win/away win/draw)
		//===========================================================
		// Cycle through the home and away scores

		for ($j=1, $k=2; $j<=$length, $k<=$length; $j+=2, $k+=2) {

			$sql_setoutcomepoints = "UPDATE live_user_predictions_groups SET points_total = points_total + '".$outcome_points."' WHERE username='".$usernamevalue."'";
			$sql_setidenticalpoints = "UPDATE live_user_predictions_groups SET points_total = points_total + '".$identical_points."' WHERE username='".$usernamevalue."'";

		if( is_numeric($pvalue["score".$j."_p"]) && is_numeric($pvalue["score".$k."_p"]) ) {

				if ( (($pvalue["score".$j."_p"] > $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] > $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] < $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] < $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] === $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] === $rvalue["score".$k."_r"])) ) {
					mysqli_query($con, $sql_setoutcomepoints);
				}

				// Determine if identical match result
				//====================================
				if ( ($pvalue["score".$j."_p"] === $rvalue["score".$j."_r"]) && ($pvalue["score".$k."_p"] === $rvalue["score".$k."_r"]) ) {
					mysqli_query($con, $sql_setidenticalpoints);
					//printf ("%s %s's Exact Match for game above!<br />", $pvalue["firstname"], $pvalue["surname"]);
				}
			}
		}
		//mysqli_free_result($pvalue);
		//mysqli_free_result($rvalue);
	}
	// Close the database connection
	mysqli_close($con);
	// Now update the move indicators within the rankings table
	updateMoveStatus();
}

function compareRO16Values() {
	// Initialise global variables
	$identical_points = 3;
	$outcome_points = 2;
	$score_points = 1;
	$blank_points = 0;
	$ids = array();

	// Connect to the database
	include 'db-connect.php';

	// Global SQL query strings
	$sql_getresults = "SELECT SUM(score73_r) as score73_r, SUM(score74_r) as score74_r, SUM(score75_r) as score75_r, SUM(score76_r) as score76_r, SUM(score77_r) as score77_r, SUM(score78_r) as score78_r, SUM(score79_r) as score79_r, SUM(score80_r) as score80_r, SUM(score81_r) as score81_r, SUM(score82_r) as score82_r, SUM(score83_r) as score83_r, SUM(score84_r) as score84_r, SUM(score85_r) as score85_r, SUM(score86_r) as score86_r, SUM(score87_r) as score87_r, SUM(score88_r) as score88_r FROM live_match_results";
	$sql_getusernames = "SELECT username FROM live_user_predictions_ro16";
	$sql_setzero = "UPDATE live_user_predictions_ro16 SET points_total = 0";

	// Return all match fixtures (ids)
	$sql_getmatchid = "SELECT match_id FROM live_match_results";

	// Create an array of match ids
	$list_of_matchids = mysqli_query($con, $sql_getmatchid);
	while($row = mysqli_fetch_array($list_of_matchids)) {
		$matchids[] = $row['match_id'];
	}

	// Create an array of usernames
	mysqli_query($con, $sql_setzero);
	$list_of_usernames = mysqli_query($con, $sql_getusernames);
	while($row = mysqli_fetch_array($list_of_usernames)) {
		$usernames[] = $row['username'];
	}
	// Check all predictions for any matched scores against results
	foreach ($usernames as $usernamevalue) {

		// SQL query strings to be looped on username value
		$sql_getspecificuser = "SELECT username, firstname, surname, score73_p, score74_p, score75_p, score76_p, score77_p, score78_p, score79_p, score80_p, score81_p, score82_p, score83_p, score84_p, score85_p, score86_p, score87_p, score88_p FROM live_user_predictions_ro16 WHERE username='".$usernamevalue."'";
		$sql_setblankpoints = "UPDATE live_user_predictions_ro16 SET points_total = points_total + '".$blank_points."' WHERE username='".$usernamevalue."'";
		$sql_setscorepoints = "UPDATE live_user_predictions_ro16 SET points_total = points_total + '".$score_points."' WHERE username='".$usernamevalue."'";

		$pvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecificuser));
		$rvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));


		// Determine a matched score for home or away result
		//==================================================
		// Cycle through the number of potential matches (e.g. 1-48)
		$length = sizeof($matchids) * 2;

		for ($i=73; $i<=$length; $i++) {
			if($pvalue["score".$i."_p"] === $rvalue["score".$i."_r"]) {
				//printf ("%s %s's Prediction: %s, Result: %s", $pvalue["firstname"], $pvalue["surname"], $pvalue["score".$i."_p"], $rvalue["score".$i."_r"]);
				mysqli_query($con, $sql_setscorepoints);
				//print "<br />The results are a match - well done!<br /><br />";
			}
			else {
				//printf ("%s %s's Prediction: %s, Result: %s", $pvalue["firstname"], $pvalue["surname"], $pvalue["score".$i."_p"], $rvalue["score".$i."_r"]);
				//print "<br />No match - better luck next time!<br /><br />";
			}
		}

		// Determine a correct match outcome (home win/away win/draw)
		//===========================================================
		// Cycle through the home and away scores

		for ($j=73, $k=74; $j<=$length, $k<=$length; $j+=2, $k+=2) {

			$sql_setoutcomepoints = "UPDATE live_user_predictions_ro16 SET points_total = points_total + '".$outcome_points."' WHERE username='".$usernamevalue."'";
			$sql_setidenticalpoints = "UPDATE live_user_predictions_ro16 SET points_total = points_total + '".$identical_points."' WHERE username='".$usernamevalue."'";

		if( is_numeric($pvalue["score".$j."_p"]) && is_numeric($pvalue["score".$k."_p"]) ) {

				if ( (($pvalue["score".$j."_p"] > $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] > $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] < $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] < $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] === $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] === $rvalue["score".$k."_r"])) ) {
					mysqli_query($con, $sql_setoutcomepoints);
				}

				// Determine if identical match result
				//====================================
				if ( ($pvalue["score".$j."_p"] === $rvalue["score".$j."_r"]) && ($pvalue["score".$k."_p"] === $rvalue["score".$k."_r"]) ) {
					mysqli_query($con, $sql_setidenticalpoints);
					//printf ("%s %s's Exact Match for game above!<br />", $pvalue["firstname"], $pvalue["surname"]);
				}
			}
		}
		//mysqli_free_result($pvalue);
		//mysqli_free_result($rvalue);
	}
	// Close the database connection
	mysqli_close($con);
	// Now update the move indicators within the rankings table
	updateMoveStatus();
}

function compareQFValues() {
	// Initialise global variables
	$identical_points = 3;
	$outcome_points = 2;
	$score_points = 1;
	$blank_points = 0;
	$ids = array();

	// Connect to the database
	include 'db-connect.php';

	// Global SQL query strings
	$sql_getresults = "SELECT SUM(score89_r) as score89_r, SUM(score90_r) as score90_r, SUM(score91_r) as score91_r, SUM(score92_r) as score92_r, SUM(score93_r) as score93_r, SUM(score94_r) as score94_r, SUM(score95_r) as score95_r, SUM(score96_r) as score96_r FROM live_match_results";
	$sql_getusernames = "SELECT username FROM live_user_predictions_qf";
	$sql_setzero = "UPDATE live_user_predictions_qf SET points_total = 0";

	// Return all match fixtures (ids)
	$sql_getmatchid = "SELECT match_id FROM live_match_results";

	// Create an array of match ids
	$list_of_matchids = mysqli_query($con, $sql_getmatchid);
	while($row = mysqli_fetch_array($list_of_matchids)) {
		$matchids[] = $row['match_id'];
	}

	// Create an array of usernames
	mysqli_query($con, $sql_setzero);
	$list_of_usernames = mysqli_query($con, $sql_getusernames);
	while($row = mysqli_fetch_array($list_of_usernames)) {
		$usernames[] = $row['username'];
	}
	// Check all predictions for any matched scores against results
	foreach ($usernames as $usernamevalue) {

		// SQL query strings to be looped on username value
		$sql_getspecificuser = "SELECT username, firstname, surname, score89_p, score90_p, score91_p, score92_p, score93_p, score94_p, score95_p, score96_p FROM live_user_predictions_qf WHERE username='".$usernamevalue."'";
		$sql_setblankpoints = "UPDATE live_user_predictions_qf SET points_total = points_total + '".$blank_points."' WHERE username='".$usernamevalue."'";
		$sql_setscorepoints = "UPDATE live_user_predictions_qf SET points_total = points_total + '".$score_points."' WHERE username='".$usernamevalue."'";

		$pvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecificuser));
		$rvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));


		// Determine a matched score for home or away result
		//==================================================
		// Cycle through the number of potential matches (e.g. 1-48)
		$length = sizeof($matchids) * 2;

		for ($i=89; $i<=$length; $i++) {
			if($pvalue["score".$i."_p"] === $rvalue["score".$i."_r"]) {
				//printf ("%s %s's Prediction: %s, Result: %s", $pvalue["firstname"], $pvalue["surname"], $pvalue["score".$i."_p"], $rvalue["score".$i."_r"]);
				mysqli_query($con, $sql_setscorepoints);
				//print "<br />The results are a match - well done!<br /><br />";
			}
			else {
				//printf ("%s %s's Prediction: %s, Result: %s", $pvalue["firstname"], $pvalue["surname"], $pvalue["score".$i."_p"], $rvalue["score".$i."_r"]);
				//print "<br />No match - better luck next time!<br /><br />";
			}
		}

		// Determine a correct match outcome (home win/away win/draw)
		//===========================================================
		// Cycle through the home and away scores

		for ($j=89, $k=90; $j<=$length, $k<=$length; $j+=2, $k+=2) {

			$sql_setoutcomepoints = "UPDATE live_user_predictions_qf SET points_total = points_total + '".$outcome_points."' WHERE username='".$usernamevalue."'";
			$sql_setidenticalpoints = "UPDATE live_user_predictions_qf SET points_total = points_total + '".$identical_points."' WHERE username='".$usernamevalue."'";

		if( is_numeric($pvalue["score".$j."_p"]) && is_numeric($pvalue["score".$k."_p"]) ) {

				if ( (($pvalue["score".$j."_p"] > $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] > $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] < $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] < $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] === $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] === $rvalue["score".$k."_r"])) ) {
					mysqli_query($con, $sql_setoutcomepoints);
				}

				// Determine if identical match result
				//====================================
				if ( ($pvalue["score".$j."_p"] === $rvalue["score".$j."_r"]) && ($pvalue["score".$k."_p"] === $rvalue["score".$k."_r"]) ) {
					mysqli_query($con, $sql_setidenticalpoints);
					//printf ("%s %s's Exact Match for game above!<br />", $pvalue["firstname"], $pvalue["surname"]);
				}
			}
		}
		//mysqli_free_result($pvalue);
		//mysqli_free_result($rvalue);
	}
	// Close the database connection
	mysqli_close($con);
	// Now update the move indicators within the rankings table
	updateMoveStatus();
}

function compareSFValues() {
	// Initialise global variables
	$identical_points = 3;
	$outcome_points = 2;
	$score_points = 1;
	$blank_points = 0;
	$ids = array();

	// Connect to the database
	include 'db-connect.php';

	// Global SQL query strings
	$sql_getresults = "SELECT SUM(score97_r) as score97_r, SUM(score98_r) as score98_r, SUM(score99_r) as score99_r, SUM(score100_r) as score100_r FROM live_match_results";
	$sql_getusernames = "SELECT username FROM live_user_predictions_sf";
	$sql_setzero = "UPDATE live_user_predictions_sf SET points_total = 0";

	// Return all match fixtures (ids)
	$sql_getmatchid = "SELECT match_id FROM live_match_results";

	// Create an array of match ids
	$list_of_matchids = mysqli_query($con, $sql_getmatchid);
	while($row = mysqli_fetch_array($list_of_matchids)) {
		$matchids[] = $row['match_id'];
	}

	// Create an array of usernames
	mysqli_query($con, $sql_setzero);
	$list_of_usernames = mysqli_query($con, $sql_getusernames);
	while($row = mysqli_fetch_array($list_of_usernames)) {
		$usernames[] = $row['username'];
	}
	// Check all predictions for any matched scores against results
	foreach ($usernames as $usernamevalue) {

		// SQL query strings to be looped on username value
		$sql_getspecificuser = "SELECT username, firstname, surname, score97_p, score98_p, score99_p, score100_p FROM live_user_predictions_sf WHERE username='".$usernamevalue."'";
		$sql_setblankpoints = "UPDATE live_user_predictions_sf SET points_total = points_total + '".$blank_points."' WHERE username='".$usernamevalue."'";
		$sql_setscorepoints = "UPDATE live_user_predictions_sf SET points_total = points_total + '".$score_points."' WHERE username='".$usernamevalue."'";

		$pvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecificuser));
		$rvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));


		// Determine a matched score for home or away result
		//==================================================
		// Cycle through the number of potential matches (e.g. 1-48)
		$length = sizeof($matchids) * 2;

		for ($i=97; $i<=$length; $i++) {
			if($pvalue["score".$i."_p"] === $rvalue["score".$i."_r"]) {
				//printf ("%s %s's Prediction: %s, Result: %s", $pvalue["firstname"], $pvalue["surname"], $pvalue["score".$i."_p"], $rvalue["score".$i."_r"]);
				mysqli_query($con, $sql_setscorepoints);
				//print "<br />The results are a match - well done!<br /><br />";
			}
			else {
				//printf ("%s %s's Prediction: %s, Result: %s", $pvalue["firstname"], $pvalue["surname"], $pvalue["score".$i."_p"], $rvalue["score".$i."_r"]);
				//print "<br />No match - better luck next time!<br /><br />";
			}
		}

		// Determine a correct match outcome (home win/away win/draw)
		//===========================================================
		// Cycle through the home and away scores

		for ($j=97, $k=98; $j<=$length, $k<=$length; $j+=2, $k+=2) {

			$sql_setoutcomepoints = "UPDATE live_user_predictions_sf SET points_total = points_total + '".$outcome_points."' WHERE username='".$usernamevalue."'";
			$sql_setidenticalpoints = "UPDATE live_user_predictions_sf SET points_total = points_total + '".$identical_points."' WHERE username='".$usernamevalue."'";

		if( is_numeric($pvalue["score".$j."_p"]) && is_numeric($pvalue["score".$k."_p"]) ) {

				if ( (($pvalue["score".$j."_p"] > $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] > $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] < $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] < $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] === $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] === $rvalue["score".$k."_r"])) ) {
					mysqli_query($con, $sql_setoutcomepoints);
				}

				// Determine if identical match result
				//====================================
				if ( ($pvalue["score".$j."_p"] === $rvalue["score".$j."_r"]) && ($pvalue["score".$k."_p"] === $rvalue["score".$k."_r"]) ) {
					mysqli_query($con, $sql_setidenticalpoints);
					//printf ("%s %s's Exact Match for game above!<br />", $pvalue["firstname"], $pvalue["surname"]);
				}
			}
		}
		//mysqli_free_result($pvalue);
		//mysqli_free_result($rvalue);
	}
	// Close the database connection
	mysqli_close($con);
	// Now update the move indicators within the rankings table
	updateMoveStatus();
}

function compareFinalValues() {
	// Initialise global variables
	$identical_points = 3;
	$outcome_points = 2;
	$score_points = 1;
	$blank_points = 0;
	$ids = array();

	// Connect to the database
	include 'db-connect.php';

	// Global SQL query strings
	$sql_getresults = "SELECT SUM(score101_r) as score101_r, SUM(score102_r) as score102_r FROM live_match_results";
	$sql_getusernames = "SELECT username FROM live_user_predictions_final";
	$sql_setzero = "UPDATE live_user_predictions_final SET points_total = 0";

	// Return all match fixtures (ids)
	$sql_getmatchid = "SELECT match_id FROM live_match_results";

	// Create an array of match ids
	$list_of_matchids = mysqli_query($con, $sql_getmatchid);
	while($row = mysqli_fetch_array($list_of_matchids)) {
		$matchids[] = $row['match_id'];
	}

	// Create an array of usernames
	mysqli_query($con, $sql_setzero);
	$list_of_usernames = mysqli_query($con, $sql_getusernames);
	while($row = mysqli_fetch_array($list_of_usernames)) {
		$usernames[] = $row['username'];
	}
	// Check all predictions for any matched scores against results
	foreach ($usernames as $usernamevalue) {

		// SQL query strings to be looped on username value
		$sql_getspecificuser = "SELECT username, firstname, surname, score101_p, score102_p FROM live_user_predictions_final WHERE username='".$usernamevalue."'";
		$sql_setblankpoints = "UPDATE live_user_predictions_final SET points_total = points_total + '".$blank_points."' WHERE username='".$usernamevalue."'";
		$sql_setscorepoints = "UPDATE live_user_predictions_final SET points_total = points_total + '".$score_points."' WHERE username='".$usernamevalue."'";

		$pvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecificuser));
		$rvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));


		// Determine a matched score for home or away result
		//==================================================
		// Cycle through the number of potential matches (e.g. 1-48)
		$length = sizeof($matchids) * 2;

		for ($i=101; $i<=$length; $i++) {
			if($pvalue["score".$i."_p"] === $rvalue["score".$i."_r"]) {
				//printf ("%s %s's Prediction: %s, Result: %s", $pvalue["firstname"], $pvalue["surname"], $pvalue["score".$i."_p"], $rvalue["score".$i."_r"]);
				mysqli_query($con, $sql_setscorepoints);
				//print "<br />The results are a match - well done!<br /><br />";
			}
			else {
				//printf ("%s %s's Prediction: %s, Result: %s", $pvalue["firstname"], $pvalue["surname"], $pvalue["score".$i."_p"], $rvalue["score".$i."_r"]);
				//print "<br />No match - better luck next time!<br /><br />";
			}
		}

		// Determine a correct match outcome (home win/away win/draw)
		//===========================================================
		// Cycle through the home and away scores

		for ($j=101, $k=102; $j<=$length, $k<=$length; $j+=2, $k+=2) {

			$sql_setoutcomepoints = "UPDATE live_user_predictions_final SET points_total = points_total + '".$outcome_points."' WHERE username='".$usernamevalue."'";
			$sql_setidenticalpoints = "UPDATE live_user_predictions_final SET points_total = points_total + '".$identical_points."' WHERE username='".$usernamevalue."'";

		if( is_numeric($pvalue["score".$j."_p"]) && is_numeric($pvalue["score".$k."_p"]) ) {

				if ( (($pvalue["score".$j."_p"] > $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] > $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] < $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] < $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] === $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] === $rvalue["score".$k."_r"])) ) {
					mysqli_query($con, $sql_setoutcomepoints);
				}

				// Determine if identical match result
				//====================================
				if ( ($pvalue["score".$j."_p"] === $rvalue["score".$j."_r"]) && ($pvalue["score".$k."_p"] === $rvalue["score".$k."_r"]) ) {
					mysqli_query($con, $sql_setidenticalpoints);
					//printf ("%s %s's Exact Match for game above!<br />", $pvalue["firstname"], $pvalue["surname"]);
				}
			}
		}
		//mysqli_free_result($pvalue);
		//mysqli_free_result($rvalue);
	}
	// Close the database connection
	mysqli_close($con);
	// Now update the move indicators within the rankings table
	updateMoveStatus();
}

function insertMatchResult() {
    // Connect to the database
    include 'db-connect.php';

    // Validate that all expected POST data is set, if empty set to NULL
    $params = [];
    for ($i = 1; $i <= 102; $i++) {
        $param = isset($_POST["score{$i}_r"]) && $_POST["score{$i}_r"] !== '' ? $_POST["score{$i}_r"] : NULL;
        $params[] = $param;
    }

    // Prepare the SQL statement with placeholders
    $sql = "INSERT INTO live_match_results (score1_r, score2_r, score3_r, score4_r, score5_r, score6_r, score7_r, score8_r, score9_r, score10_r, 
    score11_r, score12_r, score13_r, score14_r, score15_r, score16_r, score17_r, score18_r, score19_r, score20_r, 
    score21_r, score22_r, score23_r, score24_r, score25_r, score26_r, score27_r, score28_r, score29_r, score30_r, 
    score31_r, score32_r, score33_r, score34_r, score35_r, score36_r, score37_r, score38_r, score39_r, score40_r, 
    score41_r, score42_r, score43_r, score44_r, score45_r, score46_r, score47_r, score48_r, score49_r, score50_r, 
    score51_r, score52_r, score53_r, score54_r, score55_r, score56_r, score57_r, score58_r, score59_r, score60_r, 
    score61_r, score62_r, score63_r, score64_r, score65_r, score66_r, score67_r, score68_r, score69_r, score70_r, 
    score71_r, score72_r, score73_r, score74_r, score75_r, score76_r, score77_r, score78_r, score79_r, score80_r, 
    score81_r, score82_r, score83_r, score84_r, score85_r, score86_r, score87_r, score88_r, score89_r, score90_r, 
    score91_r, score92_r, score93_r, score94_r, score95_r, score96_r, score97_r, score98_r, score99_r, score100_r, 
    score101_r, score102_r) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Initialize a prepared statement
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $con->error);
    }

    // Bind parameters to the placeholders
    $types = str_repeat('i', 102); // 102 integers, or 's' if using strings
    $stmt->bind_param($types, ...$params);

    // Execute the prepared statement
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    // Close the statement and connection
    $stmt->close();
    mysqli_close($con);

	//compareValues();
	//compareRO16Values();
	//compareQFValues();
	//compareSFValues();
	compareFinalValues();

    // Debug message
    //echo "Record inserted successfully<br>";
}

function insertMatchResultZZ() {
	// Connect to the database
	include '../php/db-connect.php';

	// Write results data to the database
	$sql = "INSERT INTO live_match_results (score1_r, score2_r, score3_r, score4_r, score5_r, score6_r, score7_r, score8_r, score9_r, score10_r, score11_r, score12_r, score13_r, score14_r, score15_r, score16_r, score17_r, score18_r, score19_r, score20_r, score21_r, score22_r, score23_r, score24_r, score25_r, score26_r, score27_r, score28_r, score29_r, score30_r, score31_r, score32_r, score33_r, score34_r, score35_r, score36_r, score37_r, score38_r, score39_r, score40_r, score41_r, score42_r, score43_r, score44_r, score45_r, score46_r, score47_r, score48_r, score49_r, score50_r, score51_r, score52_r, score53_r, score54_r, score55_r, score56_r, score57_r, score58_r, score59_r, score60_r, score61_r, score62_r, score63_r, score64_r, score65_r, score66_r, score67_r, score68_r, score69_r, score70_r, score71_r, score72_r, score73_r, score74_r, score75_r, score76_r, score77_r, score78_r, score79_r, score80_r, score81_r, score82_r, score83_r, score84_r, score85_r, score86_r, score87_r, score88_r, score89_r, score90_r, score91_r, score92_r, score93_r, score94_r, score95_r, score96_r, score97_r, score98_r, score99_r, score100_r, score101_r, score102_r)
	VALUES ('$_POST[score1_r]','$_POST[score2_r]','$_POST[score3_r]','$_POST[score4_r]','$_POST[score5_r]','$_POST[score6_r]','$_POST[score7_r]','$_POST[score8_r]','$_POST[score9_r]','$_POST[score10_r]','$_POST[score11_r]','$_POST[score12_r]','$_POST[score13_r]','$_POST[score14_r]','$_POST[score15_r]','$_POST[score16_r]','$_POST[score17_r]','$_POST[score18_r]','$_POST[score19_r]','$_POST[score20_r]','$_POST[score21_r]','$_POST[score22_r]','$_POST[score23_r]',
		'$_POST[score24_r]','$_POST[score25_r]','$_POST[score26_r]','$_POST[score27_r]','$_POST[score28_r]','$_POST[score29_r]','$_POST[score30_r]','$_POST[score31_r]','$_POST[score32_r]','$_POST[score33_r]','$_POST[score34_r]','$_POST[score35_r]','$_POST[score36_r]','$_POST[score37_r]','$_POST[score38_r]','$_POST[score39_r]','$_POST[score40_r]','$_POST[score41_r]','$_POST[score42_r]','$_POST[score43_r]','$_POST[score44_r]','$_POST[score45_r]','$_POST[score46_r]',
		'$_POST[score47_r]','$_POST[score48_r]','$_POST[score49_r]','$_POST[score50_r]','$_POST[score51_r]','$_POST[score52_r]','$_POST[score53_r]','$_POST[score54_r]','$_POST[score55_r]','$_POST[score56_r]','$_POST[score57_r]','$_POST[score58_r]','$_POST[score59_r]','$_POST[score60_r]','$_POST[score61_r]','$_POST[score62_r]','$_POST[score63_r]','$_POST[score64_r]','$_POST[score65_r]','$_POST[score66_r]','$_POST[score67_r]','$_POST[score68_r]','$_POST[score69_r]',
		'$_POST[score70_r]','$_POST[score71_r]','$_POST[score72_r]','$_POST[score73_r]','$_POST[score74_r]','$_POST[score75_r]','$_POST[score76_r]','$_POST[score77_r]','$_POST[score78_r]','$_POST[score79_r]','$_POST[score80_r]','$_POST[score81_r]','$_POST[score82_r]','$_POST[score83_r]','$_POST[score84_r]','$_POST[score85_r]','$_POST[score86_r]','$_POST[score87_r]','$_POST[score88_r]','$_POST[score89_r]','$_POST[score90_r]','$_POST[score91_r]','$_POST[score92_r]',
		'$_POST[score93_r]','$_POST[score94_r]','$_POST[score95_r]','$_POST[score96_r]','$_POST[score97_r]','$_POST[score98_r]','$_POST[score99_r]','$_POST[score100_r]','$_POST[score101_r]','$_POST[score102_r]')";

	// If the SQL query fails, produce related error message
	if (!mysqli_query($con, $sql)) {
		die('Error: ' . mysqli_error($con));
	}
	// Close the DB connection
	mysqli_close($con);
	// Now update table by comparing match results against user predictions
	//compareValues();
	compareRO16Values();
	//compareQFValues();
	// compareSFValues();
}

function updateTotalUsers() {
	// Connect to the database
	include 'php/db-connect.php';
	// Create a query to return the total number of users
	$sql_countusers = "SELECT count(*) AS totalusers FROM live_user_information";
	// Execute the query and return the result or display appropriate error message
	$totalusers = mysqli_query($con, $sql_countusers) or die(mysqli_error());
	// For each instance of the returned result
	while ($row = mysqli_fetch_assoc($totalusers)) {
		$countoftotalusers = $row["totalusers"];
		$sql_updatestartpos = "UPDATE live_user_information SET startpos=$countoftotalusers";
		mysqli_query($con, $sql_updatestartpos) or die(mysqli_error());
	}
    // Close the database connection
    mysqli_close($con);
}

function retrieveScorePrediction($num) {
	// Connect to the database
	include 'php/db-connect.php';
	// Create a query to return a user's specific predictions
	$sql_getscore = "SELECT * FROM live_user_predictions_groups WHERE id='{$_SESSION['id']}'";
	// Execute the query and return the results or display an appropriate error message
	$userpred = mysqli_query($con, $sql_getscore) or die(mysqli_error());
	// Output score value
    while($row = mysqli_fetch_assoc($userpred)) {
        echo $row["score".$num."_p"];
   	}
}

function retrieveHomeResult($num) {
	// Connect to the database
	include '../php/db-connect.php';
	// Create a query to return a user's specific predictions
	$sql_getresults = "SELECT * FROM live_match_results WHERE match_id = '$num'";
	// Execute the query and return the results or display an appropriate error message
	$result = mysqli_query($con, $sql_getresults) or die(mysqli_error());
	// Output score value
    while($row = mysqli_fetch_assoc($result)) {
		// Create a query to return a user's specific predictions
		$home = ($num * 2) - 1;
		//$away = $num * 2;
		echo $row["score".$home."_r"];
		//echo $row["score".$away."_r"];
   	}
}

function retrieveAwayResult($num) {
	// Connect to the database
	include '../php/db-connect.php';
	// Create a query to return a user's specific predictions
	$sql_getresults = "SELECT * FROM live_match_results WHERE match_id = '$num'";
	// Execute the query and return the results or display an appropriate error message
	$result = mysqli_query($con, $sql_getresults) or die(mysqli_error());
	// Output score value
    while($row = mysqli_fetch_assoc($result)) {
		// Create a query to return a user's specific predictions
		$away = $num * 2;
		echo $row["score".$away."_r"];
   	}
}
/*
function updatePredictions() {
	// Connect to the database
	include 'php/db-connect.php';
	// SQL query to update predictions once they exist
	$sql_update = "UPDATE live_user_predictions_groups SET score1_p = '$_POST[score1_p]', score2_p = '$_POST[score2_p]', score3_p = '$_POST[score3_p]', score4_p = '$_POST[score4_p]', score5_p = '$_POST[score5_p]', score6_p = '$_POST[score6_p]', score7_p = '$_POST[score7_p]', score8_p = '$_POST[score8_p]', score9_p = '$_POST[score9_p]', score10_p = '$_POST[score10_p]',
	score11_p = '$_POST[score11_p]', score12_p = '$_POST[score12_p]', score13_p = '$_POST[score13_p]', score14_p = '$_POST[score14_p]', score15_p = '$_POST[score15_p]', score16_p = '$_POST[score16_p]', score17_p = '$_POST[score17_p]', score18_p = '$_POST[score18_p]', score19_p = '$_POST[score19_p]', score20_p = '$_POST[score20_p]', score21_p = '$_POST[score21_p]',
	score22_p = '$_POST[score22_p]', score23_p = '$_POST[score23_p]', score24_p = '$_POST[score24_p]', score25_p = '$_POST[score25_p]', score26_p = '$_POST[score26_p]', score27_p = '$_POST[score27_p]', score28_p = '$_POST[score28_p]', score29_p = '$_POST[score29_p]', score30_p = '$_POST[score30_p]', score31_p = '$_POST[score31_p]', score32_p = '$_POST[score32_p]',
	score33_p = '$_POST[score33_p]', score34_p = '$_POST[score34_p]', score35_p = '$_POST[score35_p]', score36_p = '$_POST[score36_p]', score37_p = '$_POST[score37_p]', score38_p = '$_POST[score38_p]', score39_p = '$_POST[score39_p]', score40_p = '$_POST[score40_p]', score41_p = '$_POST[score41_p]', score42_p = '$_POST[score42_p]', score43_p = '$_POST[score43_p]',
	score44_p = '$_POST[score44_p]', score45_p = '$_POST[score45_p]', score46_p = '$_POST[score46_p]', score47_p = '$_POST[score47_p]', score48_p = '$_POST[score48_p]', score49_p = '$_POST[score49_p]', score50_p = '$_POST[score50_p]', score51_p = '$_POST[score51_p]', score52_p = '$_POST[score52_p]', score53_p = '$_POST[score53_p]', score54_p = '$_POST[score54_p]',
	score55_p = '$_POST[score55_p]', score56_p = '$_POST[score56_p]', score57_p = '$_POST[score57_p]', score58_p = '$_POST[score58_p]', score59_p = '$_POST[score59_p]', score60_p = '$_POST[score60_p]', score61_p = '$_POST[score61_p]', score62_p = '$_POST[score62_p]', score63_p = '$_POST[score63_p]', score64_p = '$_POST[score64_p]', score65_p = '$_POST[score65_p]',
	score66_p = '$_POST[score66_p]', score67_p = '$_POST[score67_p]', score68_p = '$_POST[score68_p]', score69_p = '$_POST[score69_p]', score70_p = '$_POST[score70_p]', score71_p = '$_POST[score71_p]', score72_p = '$_POST[score72_p]', score73_p = '$_POST[score73_p]', score74_p = '$_POST[score74_p]', score75_p = '$_POST[score75_p]', score76_p = '$_POST[score76_p]',
	score77_p = '$_POST[score77_p]', score78_p = '$_POST[score78_p]', score79_p = '$_POST[score79_p]', score80_p = '$_POST[score80_p]', score81_p = '$_POST[score81_p]', score82_p = '$_POST[score82_p]', score83_p = '$_POST[score83_p]', score84_p = '$_POST[score84_p]', score85_p = '$_POST[score85_p]', score86_p = '$_POST[score86_p]', score87_p = '$_POST[score87_p]',
	score88_p = '$_POST[score88_p]', score89_p = '$_POST[score89_p]', score90_p = '$_POST[score90_p]', score91_p = '$_POST[score91_p]', score92_p = '$_POST[score92_p]', score93_p = '$_POST[score93_p]', score94_p = '$_POST[score94_p]', score95_p = '$_POST[score95_p]', score96_p = '$_POST[score96_p]', lastupdate = NOW() WHERE id='{$_SESSION['id']}'";

	mysqli_query($con, $sql_update) or die('Error: ' . mysqli_error($con));
	mysqli_close($con);
}
*/
function insertGroupPredictions() {
	// Connect to the database
	include 'php/db-connect.php';
	// SQL query to insert predictions initially
	$sql_insert = "INSERT INTO live_user_predictions_groups (id, username, firstname, surname, score1_p, score2_p, score3_p, score4_p, score5_p, score6_p, score7_p, score8_p, score9_p, score10_p, score11_p, score12_p, score13_p, score14_p, score15_p, score16_p, score17_p, score18_p, score19_p, score20_p, score21_p, score22_p, score23_p, score24_p, score25_p,
	score26_p, score27_p, score28_p, score29_p, score30_p, score31_p, score32_p, score33_p, score34_p, score35_p, score36_p, score37_p, score38_p, score39_p, score40_p, score41_p, score42_p, score43_p, score44_p, score45_p, score46_p, score47_p, score48_p, score49_p, score50_p, score51_p, score52_p, score53_p, score54_p, score55_p, score56_p, score57_p, score58_p,
	score59_p, score60_p, score61_p, score62_p, score63_p, score64_p, score65_p, score66_p, score67_p, score68_p, score69_p, score70_p, score71_p, score72_p, lastupdate)
			   VALUES ('{$_SESSION['id']}','{$_SESSION['username']}','{$_SESSION['firstname']}','{$_SESSION['surname']}','$_POST[score1_p]','$_POST[score2_p]','$_POST[score3_p]','$_POST[score4_p]','$_POST[score5_p]','$_POST[score6_p]','$_POST[score7_p]','$_POST[score8_p]','$_POST[score9_p]','$_POST[score10_p]','$_POST[score11_p]','$_POST[score12_p]','$_POST[score13_p]','$_POST[score14_p]',
					 '$_POST[score15_p]','$_POST[score16_p]','$_POST[score17_p]','$_POST[score18_p]','$_POST[score19_p]','$_POST[score20_p]','$_POST[score21_p]','$_POST[score22_p]','$_POST[score23_p]','$_POST[score24_p]','$_POST[score25_p]','$_POST[score26_p]','$_POST[score27_p]','$_POST[score28_p]','$_POST[score29_p]','$_POST[score30_p]','$_POST[score31_p]','$_POST[score32_p]','$_POST[score33_p]',
					 '$_POST[score34_p]','$_POST[score35_p]','$_POST[score36_p]','$_POST[score37_p]','$_POST[score38_p]','$_POST[score39_p]','$_POST[score40_p]','$_POST[score41_p]','$_POST[score42_p]','$_POST[score43_p]','$_POST[score44_p]','$_POST[score45_p]','$_POST[score46_p]','$_POST[score47_p]','$_POST[score48_p]','$_POST[score49_p]','$_POST[score50_p]','$_POST[score51_p]','$_POST[score52_p]',
					 '$_POST[score53_p]','$_POST[score54_p]','$_POST[score55_p]','$_POST[score56_p]','$_POST[score57_p]','$_POST[score58_p]','$_POST[score59_p]','$_POST[score60_p]','$_POST[score61_p]','$_POST[score62_p]','$_POST[score63_p]','$_POST[score64_p]','$_POST[score65_p]','$_POST[score66_p]','$_POST[score67_p]','$_POST[score68_p]','$_POST[score69_p]','$_POST[score70_p]','$_POST[score71_p]',
					 '$_POST[score72_p]', NOW())";

	mysqli_query($con, $sql_insert) or die('Error: ' . mysqli_error($con));
	mysqli_close($con);
}

function insertRO16Predictions() {
	// Connect to the database
	include 'php/db-connect.php';
	// SQL query to insert predictions initially
	$sql_insert = "INSERT INTO live_user_predictions_ro16 (id, username, firstname, surname, score73_p, score74_p, score75_p, score76_p, score77_p, score78_p, score79_p, score80_p, score81_p, score82_p, score83_p, score84_p, score85_p, score86_p, score87_p, score88_p, lastupdate)
			   VALUES ('{$_SESSION['id']}','{$_SESSION['username']}','{$_SESSION['firstname']}','{$_SESSION['surname']}','$_POST[score73_p]','$_POST[score74_p]','$_POST[score75_p]','$_POST[score76_p]','$_POST[score77_p]','$_POST[score78_p]','$_POST[score79_p]','$_POST[score80_p]','$_POST[score81_p]','$_POST[score82_p]','$_POST[score83_p]','$_POST[score84_p]','$_POST[score85_p]','$_POST[score86_p]',
					 '$_POST[score87_p]','$_POST[score88_p]', NOW())";
	mysqli_query($con, $sql_insert) or die('Error: ' . mysqli_error($con));
	mysqli_close($con);
}

function insertQFPredictions() {
	// Connect to the database
	include 'php/db-connect.php';
	// SQL query to insert predictions initially
	$sql_insert = "INSERT INTO live_user_predictions_qf (id, username, firstname, surname, score89_p, score90_p, score91_p, score92_p, score93_p, score94_p, score95_p, score96_p, lastupdate)
			   VALUES ('{$_SESSION['id']}','{$_SESSION['username']}','{$_SESSION['firstname']}','{$_SESSION['surname']}','$_POST[score89_p]','$_POST[score90_p]','$_POST[score91_p]','$_POST[score92_p]','$_POST[score93_p]','$_POST[score94_p]','$_POST[score95_p]','$_POST[score96_p]', NOW())";
	mysqli_query($con, $sql_insert) or die('Error: ' . mysqli_error($con));
	mysqli_close($con);
}

function insertSFPredictions() {
	// Connect to the database
	include 'php/db-connect.php';
	// SQL query to insert predictions initially
	$sql_insert = "INSERT INTO live_user_predictions_sf (id, username, firstname, surname, score97_p, score98_p, score99_p, score100_p, lastupdate)
			   VALUES ('{$_SESSION['id']}','{$_SESSION['username']}','{$_SESSION['firstname']}','{$_SESSION['surname']}','$_POST[score97_p]','$_POST[score98_p]','$_POST[score99_p]','$_POST[score100_p]', NOW())";
	mysqli_query($con, $sql_insert) or die('Error: ' . mysqli_error($con));
	mysqli_close($con);
}

function insertFiPredictions() {
	// Connect to the database
	include 'php/db-connect.php';
	// SQL query to insert predictions initially
	$sql_insert = "INSERT INTO live_user_predictions_final (id, username, firstname, surname, score101_p, score102_p, lastupdate)
			   VALUES ('{$_SESSION['id']}','{$_SESSION['username']}','{$_SESSION['firstname']}','{$_SESSION['surname']}','$_POST[score101_p]','$_POST[score102_p]', NOW())";
	mysqli_query($con, $sql_insert) or die('Error: ' . mysqli_error($con));
	mysqli_close($con);
}

function submitPredictions() {
	// Connect to the database
	include 'php/db-connect.php';

  	//$sql_exists = "SELECT * FROM live_user_predictions_groups WHERE id='{$_SESSION['id']}'";
	$sql_exists = "SELECT * FROM live_user_predictions_final WHERE id='{$_SESSION['id']}'";

	$result = mysqli_query($con, $sql_exists);
	if(mysqli_num_rows($result) == 0) {
		//insertGroupPredictions();
		//insertRO16Predictions();
		// insertQFPredictions();
		// insertSFPredictions();
		insertFiPredictions();		
		/* Alert to check if predictions are inserted...
		print '<script type="text/javascript">';
		print 'alert("Predictions have been inserted! '. $_SESSION['firstname'].' '. $_SESSION['surname'].' yeah.")';
		print '</script>';
		*/
	}
	else {
		//updatePredictions();
		//mysqli_query($con, $sql_update) or die('Error: ' . mysqli_error($con));
		/* Alert to check if predictions are updated...
		print '<script type="text/javascript">';
		print 'alert("Predictions have been updated!")';
		print '</script>';
		*/
	}

	// Close the DB connection
	mysqli_close($con);
	//updateTotalUsers();
	//setInitialRanking();
//	  }
}

function displayRankings() {
    // Connect to the database
    include 'php/db-connect.php';

    // Set up SQL query to retrieve data from database tables
    $sql_maketable = "SELECT live_user_information.id, live_user_information.firstname, live_user_information.surname, live_user_information.avatar, live_user_information.faveteam, live_user_information.startpos, live_user_information.currpos, live_user_information.lastpos, live_user_predictions_groups.points_total,
                        FIND_IN_SET(points_total, (
                            SELECT GROUP_CONCAT(DISTINCT points_total ORDER BY points_total DESC)
                            FROM live_user_predictions_groups )
                        ) AS rank
                        FROM live_user_information
                        INNER JOIN live_user_predictions_groups ON live_user_information.id = live_user_predictions_groups.id
                        ORDER BY rank ASC, surname ASC";

    $sql_matchresults = "SELECT * FROM live_match_results";

    // Execute the query and return the results or display an appropriate error message
    $table = mysqli_query($con, $sql_maketable) or die(mysqli_error($con));
    $result = mysqli_query($con, $sql_matchresults) or die(mysqli_error($con));

    echo "<div class='table-responsive'>";
    echo "<table id='rankingsTable' class='table table-striped'>";
    echo "<thead><tr><th>Rank</th><th>Player</th><th>Points</th></tr></thead>";
    echo "<tbody>";

    while ($row = mysqli_fetch_assoc($table)) {
        // Check if match results table contains any data
        if (mysqli_num_rows($result) == 0) {
            $rank = $row["startpos"];
        } else {
            $rank = $row["rank"];
        }

        // Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
        if ($row["lastpos"] > $row["currpos"]) {
            $diff = $row["lastpos"] - $row["currpos"];
            $move = "<i class='bi bi-arrow-up-circle-fill text-success'></i>";
        } elseif ($row["lastpos"] < $row["currpos"]) {
            $diff = $row["currpos"] - $row["lastpos"];
            $move = "<i class='bi bi-arrow-down-circle-fill text-danger'></i>";
        } else {
            $diff = 0;
            $move = "<i class='bi bi-arrow-right-circle-fill text-secondary'></i>";
        }

        // Ensure both name variables begin with upper case letters
        $uppCaseFN = ucfirst($row["firstname"]);
        $uppCaseSN = ucfirst($row["surname"]);

        // Display the table complete with all data variables
        echo "<tr>";
        echo "<td>".$row["rank"]."</span><span style='margin-left: 5px'>".$move."</span></td>";        
        echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;<a href='user.php?id=".$row["id"]."'>".$uppCaseFN." ".$uppCaseSN."</a></td>";
        echo "<td>".$row["points_total"]."</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    mysqli_close($con);
}


function displayRankingsEq3() {
    // Connect to the database
    include 'php/db-connect.php';

    // Set up SQL query to retrieve data from database tables
    $sql_maketable = "SELECT live_user_information.id, live_user_information.firstname, live_user_information.surname, live_user_information.avatar, live_user_information.faveteam, live_user_information.startpos, live_user_information.currpos, live_user_information.lastpos, test_user_predictions_groups.points_total,
                        FIND_IN_SET(points_total, (
                            SELECT GROUP_CONCAT(DISTINCT points_total ORDER BY points_total DESC)
                            FROM test_user_predictions_groups )
                        ) AS rank
                        FROM live_user_information
                        INNER JOIN test_user_predictions_groups ON live_user_information.id = test_user_predictions_groups.id
                        ORDER BY rank ASC, surname ASC";

    $sql_matchresults = "SELECT * FROM live_match_results";

    // Execute the query and return the results or display an appropriate error message
    $table = mysqli_query($con, $sql_maketable) or die(mysqli_error($con));
    $result = mysqli_query($con, $sql_matchresults) or die(mysqli_error($con));

    echo "<div class='table-responsive'>";
    echo "<table id='rankingsTable' class='table table-striped'>";
    echo "<thead><tr><th>Rank</th><th>Move</th><th>Player</th><th>Points</th></tr></thead>";
    echo "<tbody>";

    // Keep track of the previous rank to identify non-unique ranks
    $prevRank = null;
    while ($row = mysqli_fetch_assoc($table)) {
        // Check if match results table contains any data
        if (mysqli_num_rows($result) == 0) {
            $rank = $row["startpos"];
        } else {
            $rank = $row["rank"];
        }

        // Append '=' if the rank is not unique
        if ($rank == $prevRank) {
            $displayRank = '<strong>'. $rank . '</strong>'."=";
        } else {
            $displayRank = '<strong>'. $rank . '</strong>';
        }

        // Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
        if ($row["lastpos"] > $row["currpos"]) {
            $diff = $row["lastpos"] - $row["currpos"];
            $move = "<span class='text-success'><i class='bi bi-caret-up-fill'></i>" . $diff . "</span>";
        } elseif ($row["lastpos"] < $row["currpos"]) {
            $diff = $row["currpos"] - $row["lastpos"];
            $move = "<span class='text-danger'><i class='bi bi-caret-down-fill'></i>" . $diff . "</span>";
        } else {
            $diff = 0;
            $move = "<span class='text-secondary'><i class='bi bi-caret-right-fill'></i>" . $diff . "</span>";
        }

        // Ensure both name variables begin with upper case letters
        $uppCaseFN = ucfirst($row["firstname"]);
        $uppCaseSN = ucfirst($row["surname"]);

        // Display the table complete with all data variables
        echo "<tr>";		
        echo "<td><span class=''>" . $displayRank . "</span></td>";
		echo "<td><span class=''>" . $move . "</span></td>";		
        echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;<a href='user.php?id=".$row["id"]."'>".$uppCaseFN." ".$uppCaseSN."</a></td>";
        echo "<td>".$row["points_total"]."</td>";		
        echo "</tr>";

        // Update the previous rank
        $prevRank = $rank;
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    mysqli_close($con);
}

function displayRankingsEq4() {
    // Connect to the database
    include 'php/db-connect.php';

    // Set up SQL query to retrieve data from database tables
    $sql_maketable = "SELECT lui.id, lui.firstname, lui.surname, lui.avatar, lui.faveteam, lui.startpos, lui.currpos, lui.lastpos, 
						(lup_groups.points_total + IFNULL(lup_ro16.points_total, 0)) AS points_total,
						FIND_IN_SET((lup_groups.points_total + IFNULL(lup_ro16.points_total, 0)), (
							SELECT GROUP_CONCAT(DISTINCT (lup_groups.points_total + IFNULL(lup_ro16.points_total, 0)) ORDER BY (lup_groups.points_total + IFNULL(lup_ro16.points_total, 0)) DESC)
							FROM live_user_predictions_groups lup_groups
							LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lup_groups.id = lup_ro16.id)
						) AS rank
					FROM live_user_information lui
					INNER JOIN live_user_predictions_groups lup_groups ON lui.id = lup_groups.id
					LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lui.id = lup_ro16.id
					ORDER BY rank ASC, surname ASC";

    $sql_matchresults = "SELECT * FROM live_match_results";

    // Execute the query and return the results or display an appropriate error message
    $table = mysqli_query($con, $sql_maketable) or die(mysqli_error($con));
    $result = mysqli_query($con, $sql_matchresults) or die(mysqli_error($con));

    echo "<div class='table-responsive'>";
    echo "<table id='rankingsTable' class='table table-striped'>";
    echo "<thead><tr><th>Rank</th><th>Move</th><th>Player</th><th>Points</th></tr></thead>";
    echo "<tbody>";

    // Keep track of the previous rank to identify non-unique ranks
    $prevRank = null;
    while ($row = mysqli_fetch_assoc($table)) {
        // Check if match results table contains any data
        if (mysqli_num_rows($result) == 0) {
            $rank = $row["startpos"];
        } else {
            $rank = $row["rank"];
        }

        // Append '=' if the rank is not unique
        if ($rank == $prevRank) {
            $displayRank = '<strong>'. $rank . '</strong>'."=";
        } else {
            $displayRank = '<strong>'. $rank . '</strong>';
        }

        // Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
        if ($row["lastpos"] > $row["currpos"]) {
            $diff = $row["lastpos"] - $row["currpos"];
            $move = "<span class='text-success'><i class='bi bi-caret-up-fill'></i>" . $diff . "</span>";
        } elseif ($row["lastpos"] < $row["currpos"]) {
            $diff = $row["currpos"] - $row["lastpos"];
            $move = "<span class='text-danger'><i class='bi bi-caret-down-fill'></i>" . $diff . "</span>";
        } else {
            $diff = 0;
            $move = "<span class='text-secondary'><i class='bi bi-caret-right-fill'></i>" . $diff . "</span>";
        }

        // Ensure both name variables begin with upper case letters
        $uppCaseFN = ucfirst($row["firstname"]);
        $uppCaseSN = ucfirst($row["surname"]);

        // Display the table complete with all data variables
        echo "<tr>";		
        echo "<td><span class=''>" . $displayRank . "</span></td>";
		echo "<td><span class=''>" . $move . "</span></td>";		
        echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;<a href='user.php?id=".$row["id"]."'>".$uppCaseFN." ".$uppCaseSN."</a></td>";
        echo "<td>".$row["points_total"]."</td>";		
        echo "</tr>";

        // Update the previous rank
        $prevRank = $rank;
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    mysqli_close($con);
}

function displayRankingsEq5() {
    // Connect to the database
    include 'php/db-connect.php';

    // Set up SQL query to retrieve data from database tables
	$sql_maketable = "SELECT lui.id, lui.firstname, lui.surname, lui.avatar, lui.faveteam, lui.startpos, lui.currpos, lui.lastpos, 
						(lup_groups.points_total + 
						IFNULL(lup_ro16.points_total, 0) + 
						IFNULL(lup_qf.points_total, 0) + 
						IFNULL(lup_sf.points_total, 0) + 
						IFNULL(lup_fi.points_total, 0)) AS points_total,
						FIND_IN_SET(
							(lup_groups.points_total + 
							IFNULL(lup_ro16.points_total, 0) + 
							IFNULL(lup_qf.points_total, 0) + 
							IFNULL(lup_sf.points_total, 0) + 
							IFNULL(lup_fi.points_total, 0)), 
							(
								SELECT GROUP_CONCAT(
									DISTINCT (lup_groups.points_total + 
											IFNULL(lup_ro16.points_total, 0) + 
											IFNULL(lup_qf.points_total, 0) + 
											IFNULL(lup_sf.points_total, 0) + 
											IFNULL(lup_fi.points_total, 0)) 
									ORDER BY (lup_groups.points_total + 
											IFNULL(lup_ro16.points_total, 0) + 
											IFNULL(lup_qf.points_total, 0) + 
											IFNULL(lup_sf.points_total, 0) + 
											IFNULL(lup_fi.points_total, 0)) DESC
								)
								FROM live_user_predictions_groups lup_groups
								LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lup_groups.id = lup_ro16.id
								LEFT JOIN live_user_predictions_qf lup_qf ON lup_groups.id = lup_qf.id
								LEFT JOIN live_user_predictions_sf lup_sf ON lup_groups.id = lup_sf.id
								LEFT JOIN live_user_predictions_final lup_fi ON lup_groups.id = lup_fi.id
							)
						) AS rank
					FROM live_user_information lui
					INNER JOIN live_user_predictions_groups lup_groups ON lui.id = lup_groups.id
					LEFT JOIN live_user_predictions_ro16 lup_ro16 ON lui.id = lup_ro16.id
					LEFT JOIN live_user_predictions_qf lup_qf ON lui.id = lup_qf.id
					LEFT JOIN live_user_predictions_sf lup_sf ON lui.id = lup_sf.id
					LEFT JOIN live_user_predictions_final lup_fi ON lui.id = lup_fi.id
					ORDER BY rank ASC, surname ASC";

    $sql_matchresults = "SELECT * FROM live_match_results";

    // Execute the query and return the results or display an appropriate error message
    $table = mysqli_query($con, $sql_maketable) or die(mysqli_error($con));
    $result = mysqli_query($con, $sql_matchresults) or die(mysqli_error($con));

    echo "<div class='table-responsive'>";
    echo "<table id='rankingsTable' class='table table-striped'>";
    echo "<thead><tr><th>Rank</th><th>Move</th><th>Player</th><th>Points</th></tr></thead>";
    echo "<tbody>";

    // Keep track of the previous rank to identify non-unique ranks
    $prevRank = null;
    while ($row = mysqli_fetch_assoc($table)) {
        // Check if match results table contains any data
        if (mysqli_num_rows($result) == 0) {
            $rank = $row["startpos"];
        } else {
            $rank = $row["rank"];
        }

        // Append '=' if the rank is not unique
        if ($rank == $prevRank) {
            $displayRank = '<strong>'. $rank . '</strong>'."=";
        } else {
            $displayRank = '<strong>'. $rank . '</strong>';
        }

        // Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
        if ($row["lastpos"] > $row["currpos"]) {
            $diff = $row["lastpos"] - $row["currpos"];
            $move = "<span class='text-success'><i class='bi bi-caret-up-fill'></i>" . $diff . "</span>";
        } elseif ($row["lastpos"] < $row["currpos"]) {
            $diff = $row["currpos"] - $row["lastpos"];
            $move = "<span class='text-danger'><i class='bi bi-caret-down-fill'></i>" . $diff . "</span>";
        } else {
            $diff = 0;
            $move = "<span class='text-secondary'><i class='bi bi-caret-right-fill'></i>" . $diff . "</span>";
        }

        // Ensure both name variables begin with upper case letters
        $uppCaseFN = ucfirst($row["firstname"]);
        $uppCaseSN = ucfirst($row["surname"]);

        // Display the table complete with all data variables
        echo "<tr>";		
        echo "<td><span class=''>" . $displayRank . "</span></td>";
		echo "<td><span class=''>" . $move . "</span></td>";		
        echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;<a href='user.php?id=".$row["id"]."'>".$uppCaseFN." ".$uppCaseSN."</a></td>";
        //echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;".$uppCaseFN." ".$uppCaseSN."</td>";
        echo "<td>".$row["points_total"]."</td>";		
        echo "</tr>";

        // Update the previous rank
        $prevRank = $rank;
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    mysqli_close($con);
}

function displayRankingsEq2() {
    // Connect to the database
    include 'php/db-connect.php';

    // Set up SQL query to retrieve data from database tables
    $sql_maketable = "SELECT lui.id, lui.firstname, lui.surname, lui.avatar, lui.faveteam, lui.startpos, lui.currpos, lui.lastpos, (lup_groups.points_total + lup_ro16.points_total) AS points_total,
						FIND_IN_SET((lup_groups.points_total + lup_ro16.points_total), (
							SELECT GROUP_CONCAT(DISTINCT (lup_groups.points_total + lup_ro16.points_total) ORDER BY (lup_groups.points_total + lup_ro16.points_total) DESC)
							FROM live_user_predictions_groups lup_groups
							JOIN live_user_predictions_ro16 lup_ro16 ON lup_groups.id = lup_ro16.id)
						) AS rank
						FROM live_user_information lui
						INNER JOIN live_user_predictions_groups lup_groups ON lui.id = lup_groups.id
						INNER JOIN live_user_predictions_ro16 lup_ro16 ON lui.id = lup_ro16.id
						ORDER BY rank ASC, surname ASC";

    $sql_matchresults = "SELECT * FROM live_match_results";

    // Execute the query and return the results or display an appropriate error message
    $table = mysqli_query($con, $sql_maketable) or die(mysqli_error($con));
    $result = mysqli_query($con, $sql_matchresults) or die(mysqli_error($con));

    echo "<div class='table-responsive'>";
    echo "<table id='rankingsTable' class='table table-striped'>";
    echo "<thead><tr><th>Rank</th><th>Move</th><th>Player</th><th>Points</th></tr></thead>";
    echo "<tbody>";

    // Keep track of the previous rank to identify non-unique ranks
    $prevRank = null;
    while ($row = mysqli_fetch_assoc($table)) {
        // Check if match results table contains any data
        if (mysqli_num_rows($result) == 0) {
            $rank = $row["startpos"];
        } else {
            $rank = $row["rank"];
        }

        // Append '=' if the rank is not unique
        if ($rank == $prevRank) {
            $displayRank = '<strong>'. $rank . '</strong>'."=";
        } else {
            $displayRank = '<strong>'. $rank . '</strong>';
        }

        // Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
        if ($row["lastpos"] > $row["currpos"]) {
            $diff = $row["lastpos"] - $row["currpos"];
            $move = "<span class='text-success'><i class='bi bi-caret-up-fill'></i>" . $diff . "</span>";
        } elseif ($row["lastpos"] < $row["currpos"]) {
            $diff = $row["currpos"] - $row["lastpos"];
            $move = "<span class='text-danger'><i class='bi bi-caret-down-fill'></i>" . $diff . "</span>";
        } else {
            $diff = 0;
            $move = "<span class='text-secondary'><i class='bi bi-caret-right-fill'></i>" . $diff . "</span>";
        }

        // Ensure both name variables begin with upper case letters
        $uppCaseFN = ucfirst($row["firstname"]);
        $uppCaseSN = ucfirst($row["surname"]);

        // Display the table complete with all data variables
        echo "<tr>";		
        echo "<td><span class=''>" . $displayRank . "</span></td>";
		echo "<td><span class=''>" . $move . "</span></td>";		
        echo "<td><img src='".$row["avatar"]."' class='img-responsive pull-left' width='20px'>&nbsp;<a href='user.php?id=".$row["id"]."'>".$uppCaseFN." ".$uppCaseSN."</a></td>";
        echo "<td>".$row["points_total"]."</td>";		
        echo "</tr>";

        // Update the previous rank
        $prevRank = $rank;
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    mysqli_close($con);
}

function displayInfo() {
	// Connect to the database
	include 'php/db-connect.php';

	$sql_countusers = "SELECT count(*) AS totalusers FROM live_user_information";

	// Execute the query and return the result or display appropriate error message
	$totalusers = mysqli_query($con, $sql_countusers) or die(mysqli_error());
	// For each instance of the returned result
	
	while ($row = mysqli_fetch_assoc($totalusers)) {
		$countoftotalusers = $row["totalusers"];
		//$prizefund = ($countoftotalusers * 3);
		print "<p class='text-center' style='margin: 0px 15px;'>";
		printf("Players: %d", $countoftotalusers-1);
		printf("<span style='margin: 0px 15px;'>");
		//printf("Prize Fund: £%d.00", $prizefund);
		print "Prizes TBC</span></p>";	
/*
		print "<p class='text-center' style='margin: 0px 10px;'>";
		print("Players: 92");
		print("<span style='margin: 0px 15px;'>");
		print("Prizes TBC");
		print "</span></p>";
*/
	}
}

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

function abbrTeam($team) {
	// Include the configuration file
	include 'php/config.php';

	// If user is on a small mobile device, do...
	if(isMobile()){
		$teamupper = strtoupper($team);
		$teamabb = substr($teamupper,0,3);
		echo $teamabb;
	}
	// Else users on desktop get...
	else {
		echo $team;
	}
}
?>
