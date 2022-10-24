<?php
	// Initialise global variables
	$identical_points = 3;	
	$score_points = 2;
	$outcome_points = 1;
	$ids = array();

	// Connect to the database
	include '../php/db-connect.php';
	
	// Global SQL query strings
	$sql_getresults = "SELECT SUM(score1_r) as score1_r, SUM(score2_r) as score2_r, SUM(score3_r) as score3_r, SUM(score4_r) as score4_r, SUM(score5_r) as score5_r, SUM(score6_r) as score6_r, SUM(score7_r) as score7_r, SUM(score8_r) as score8_r, SUM(score9_r) as score9_r, SUM(score10_r) as score10_r, SUM(score11_r) as score11_r, SUM(score12_r) as score12_r, SUM(score13_r) as score13_r, SUM(score14_r) as score14_r, SUM(score15_r) as score15_r, SUM(score16_r) as score16_r, SUM(score17_r) as score17_r, SUM(score18_r) as score18_r, SUM(score19_r) as score19_r, SUM(score20_r) as score20_r, SUM(score21_r) as score21_r, SUM(score22_r) as score22_r, SUM(score23_r) as score23_r, SUM(score24_r) as score24_r, SUM(score25_r) as score25_r, SUM(score26_r) as score26_r, SUM(score27_r) as score27_r, SUM(score28_r) as score28_r, SUM(score29_r) as score29_r, SUM(score30_r) as score30_r, SUM(score31_r) as score31_r, SUM(score32_r) as score32_r, SUM(score33_r) as score33_r, SUM(score34_r) as score34_r, SUM(score35_r) as score35_r, SUM(score36_r) as score36_r, SUM(score37_r) as score37_r, SUM(score38_r) as score38_r, SUM(score39_r) as score39_r, SUM(score40_r) as score40_r, SUM(score41_r) as score41_r, SUM(score42_r) as score42_r, SUM(score43_r) as score43_r, SUM(score44_r) as score44_r, SUM(score45_r) as score45_r, SUM(score46_r) as score46_r, SUM(score47_r) as score47_r, SUM(score48_r) as score48_r, SUM(score49_r) as score49_r, SUM(score50_r) as score50_r, SUM(score51_r) as score51_r, SUM(score52_r) as score52_r, SUM(score53_r) as score53_r, SUM(score54_r) as score54_r, SUM(score55_r) as score55_r, SUM(score56_r) as score56_r, SUM(score57_r) as score57_r, SUM(score58_r) as score58_r, SUM(score59_r) as score59_r, SUM(score60_r) as score60_r, SUM(score61_r) as score61_r, SUM(score62_r) as score62_r, SUM(score63_r) as score63_r, SUM(score64_r) as score64_r, SUM(score65_r) as score65_r, SUM(score66_r) as score66_r, SUM(score67_r) as score67_r, SUM(score68_r) as score68_r, SUM(score69_r) as score69_r, SUM(score70_r) as score70_r, SUM(score71_r) as score71_r, SUM(score72_r) as score72_r, SUM(score73_r) as score73_r, SUM(score74_r) as score74_r, SUM(score75_r) as score75_r, SUM(score76_r) as score76_r, SUM(score77_r) as score77_r, SUM(score78_r) as score78_r, SUM(score79_r) as score79_r, SUM(score80_r) as score80_r, SUM(score81_r) as score81_r, SUM(score82_r) as score82_r, SUM(score83_r) as score83_r, SUM(score84_r) as score84_r, SUM(score85_r) as score85_r, SUM(score86_r) as score86_r, SUM(score87_r) as score87_r, SUM(score88_r) as score88_r, SUM(score89_r) as score89_r, SUM(score90_r) as score90_r, SUM(score91_r) as score91_r, SUM(score92_r) as score92_r, SUM(score93_r) as score93_r, SUM(score94_r) as score94_r, SUM(score95_r) as score95_r, SUM(score96_r) as score96_r, SUM(score97_r) as score97_r, SUM(score98_r) as score98_r, SUM(score99_r) as score99_r, SUM(score100_r) as score100_r, SUM(score101_r) as score101_r, SUM(score102_r) as score102_r FROM live_match_results";
	$sql_getusernames = "SELECT username FROM live_user_predictions";
	$sql_setzero = "UPDATE live_user_predictions SET points_total = 0";
	
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
		$sql_getspecificuser = "SELECT username, firstname, surname, score1_p, score2_p, score3_p, score4_p, score5_p, score6_p, score7_p, score8_p, score9_p, score10_p, score11_p, score12_p, score13_p, score14_p, score15_p, score16_p, score17_p, score18_p, score19_p, score20_p, score21_p, score22_p, score23_p, score24_p, score25_p, score26_p, score27_p, score28_p, score29_p, score30_p, score31_p, score32_p, score33_p, score34_p, score35_p, score36_p, score37_p, score38_p, score39_p, score40_p, score41_p, score42_p, score43_p, score44_p, score45_p, score46_p, score47_p, score48_p, score49_p, score50_p, score51_p, score52_p, score53_p, score54_p, score55_p, score56_p, score57_p, score58_p, score59_p, score60_p, score61_p, score62_p, score63_p, score64_p, score65_p, score66_p, score67_p, score68_p, score69_p, score70_p, score71_p, score72_p, score73_p, score74_p, score75_p, score76_p, score77_p, score78_p, score79_p, score80_p, score81_p, score82_p, score83_p, score84_p, score85_p, score86_p, score87_p, score88_p, score89_p, score90_p, score91_p, score92_p, score93_p, score94_p, score95_p, score96_p, score97_p, score98_p, score99_p, score100_p, score101_p, score102_p FROM live_user_predictions WHERE username='".$usernamevalue."'";
		$sql_setscorepoints = "UPDATE live_user_predictions SET points_total = points_total + '".$score_points."' WHERE username='".$usernamevalue."'";		

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
		
			$sql_setoutcomepoints = "UPDATE live_user_predictions SET points_total = points_total + '".$outcome_points."' WHERE username='".$usernamevalue."'";
			$sql_setidenticalpoints = "UPDATE live_user_predictions SET points_total = points_total + '".$identical_points."' WHERE username='".$usernamevalue."'";
								
				if ( (($pvalue["score".$j."_p"] > $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] > $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] < $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] < $rvalue["score".$k."_r"])) || (($pvalue["score".$j."_p"] === $pvalue["score".$k."_p"]) && ($rvalue["score".$j."_r"] === $rvalue["score".$k."_r"])) ) {
					mysqli_query($con, $sql_setoutcomepoints);	
				}
				else {
					//printf ("%s %s's Home/Away/Draw Lose!<br />", $pvalue["firstname"], $pvalue["surname"]);
					//echo $j, $k;			
				}

				// Determine if identical match result
				//====================================
				if ( ($pvalue["score".$j."_p"] === $rvalue["score".$j."_r"]) && ($pvalue["score".$k."_p"] === $rvalue["score".$k."_r"]) ) {
					mysqli_query($con, $sql_setidenticalpoints);	
					//printf ("%s %s's Exact Match for game above!<br />", $pvalue["firstname"], $pvalue["surname"]);					
				}
		}						
		mysqli_free_result($pvalue);
		mysqli_free_result($rvalue);
	}
	// Close the database connection
	mysqli_close($con);
?>