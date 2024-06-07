<?php

// function retrieveScorePrediction($num) {
// 	// Connect to the database
// 	include 'php/db-connect.php';
// 	// Create a query to return a user's specific predictions
// 	$sql_getscore = "SELECT * FROM live_user_predictions WHERE id='{$_SESSION['id']}'";
// 	// Execute the query and return the results or display an appropriate error message
// 	$userpred = mysqli_query($con, $sql_getscore) or die(mysqli_error());
// 	// Output score value
//     while($row = mysqli_fetch_assoc($userpred)) {
//         echo $row["score".$num."_p"];
//    	}
// }

// function retrieveScoreResult($num) {
// 	// Connect to the database
// 	include 'php/db-connect.php';
// 	// Create a query to return a user's specific predictions
// 	$sql_getscore = "SELECT * FROM live_match_results";
// 	// Execute the query and return the results or display an appropriate error message
// 	$result = mysqli_query($con, $sql_getscore) or die(mysqli_error());
// 	// Output score value
//     if($row = mysqli_fetch_assoc($result)) {
//         echo $row["score".$num."_r"];
//    	}
// 	else {
// 		echo $row["0"];
// 	}
// }

// function displayRankings() {
// 	// Connect to the database
// 	include 'php/db-connect.php';

// 	// Set up SQL query to retrieve data from database tables
// 	$sql_maketable = "SELECT live_user_information.id, live_user_information.firstname, live_user_information.surname, live_user_information.faveteam, live_user_information.startpos, live_user_information.currpos, live_user_information.lastpos, live_user_predictions.points_total,
// 						FIND_IN_SET(points_total, (
// 							SELECT GROUP_CONCAT( DISTINCT points_total
// 							ORDER BY points_total DESC )
// 							FROM live_user_predictions )
// 						) AS rank
// 						FROM live_user_information
// 						INNER JOIN live_user_predictions ON live_user_information.id = live_user_predictions.id
// 						ORDER BY rank ASC, surname ASC";

// 	$sql_matchresults = "SELECT * FROM live_match_results";

// 	// Execute the query and return the results or display an appropriate error message
// 	$table = mysqli_query($con, $sql_maketable) or die(mysqli_error());
// 	// Execute the query to see if match results table contains any data
// 	$result = mysqli_query($con, $sql_matchresults) or die(mysqli_error());

// 	// Start creating the table to display the returned values
// 	print "<table class='table table-striped' style='background-color:#FFF'>";
// 	print "<tr><th width='10%'></th><th width='10%'>Rank</th><th width='10%'></th><th width='30%'>Name</th><th width='40%'>Favourite Team</th><th width='10%'>Points</th></tr>";

// 	while ($row = mysqli_fetch_assoc($table)) {

// 		// Check if match results table contains any data
// 		if (mysqli_num_rows($result) == 0) {
// 			// Set rank value to start position value if there is no match data
// 			$rank = $row["startpos"];
// 		}
// 		else {
// 			// Set rank value to rank position once match data exists
// 			$rank = $row["rank"];
// 		}

// 		// Determine if move is upwards, downwards or the same and calculate the difference between current and previous ranking
// 		if ($row["lastpos"] > $row["currpos"]) {
// 			$diff = $row["lastpos"] - $row["currpos"];
// 			$move = "<span style='color: green;'>&#x25B2;</span>";
// 		}
// 		if ($row["lastpos"] < $row["currpos"]) {
// 			$diff = $row["currpos"] - $row["lastpos"];
// 			$move = "<span style='color: red;'>&#x25BC;</span>";
// 		}
// 		if ($row["lastpos"] == $row["currpos"]) {
// 			$diff = 0;
// 			$move = "<span style='color: #888;'>&#x25B6;</span>";
// 		}

// 		print "<tr>";
// 		// Ensure both name variables being with upper case letters
// 		$uppCaseFN = ucfirst($row["firstname"]);
// 		$uppCaseSN = ucfirst($row["surname"]);

// 		// Display the table complete with all data variables
// 		printf ("<td></td>");
// 		printf ("<td><strong>%s</strong> <span class='text-muted'>(%s)</span></td>", $rank, $row["lastpos"]);
// 		printf ("<td>%s %s</td>", $move, $diff);
// 		printf ("<td><a href='user.php?id=%s'>%s %s</a></td>", $row["id"], $uppCaseFN, $uppCaseSN);
// 		printf ("<td>%s</td>", $row["faveteam"]);
// 		printf ("<td>%s</td>", $row["points_total"]);
// 		print "</tr>";
// 	}
// 	// Complete the physical table layout
// 	print "</tr>";
// 	print "</table>";

// 	// Close the database connection
// 	mysqli_close($con);
// }

function displayTeamData() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getteams = "SELECT faveteam, count(faveteam) AS occs FROM live_user_information GROUP BY faveteam LIMIT 0, 300";

	// Obtain the SQL query result
	$result = mysqli_query($con, $sql_getteams) or die(mysqli_error());

	// Carry out the following for each result item
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		// Remove all carriage returns and new lines from array values
		$team = str_replace("\r\n", "", $row["faveteam"]);
		$occs = str_replace("\r\n", "", $row["occs"]);

		// Now put the values into a 'clean' array for output
		//$arr = vsprintf("['%s', %d],", array($team, $occs));
		$arr = vsprintf("[%d,'%s'],", array($occs, $team));
		// Output array data
		echo $arr;
	}
	// Free result set
	mysqli_free_result($result);
	// Close DB connection
	mysqli_close($con);
}

function displayNationData() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getnations = "SELECT tournwinner, count(tournwinner) AS occs FROM live_user_information GROUP BY tournwinner LIMIT 0, 300";

	// Obtain the SQL query result
	$result = mysqli_query($con, $sql_getnations) or die(mysqli_error());

	// Carry out the following for each result item
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		// Remove all carriage returns and new lines from array values
		$nation = str_replace("\r\n", "", $row["tournwinner"]);
		$occs = str_replace("\r\n", "", $row["occs"]);
		// Now put the values into a 'clean' array for output
		$arr = vsprintf("['%d',%s],", array($occs, $nation));
		// Output array data
		echo $arr;
	}
	// Free result set
	mysqli_free_result($result);
	// Close DB connection
	mysqli_close($con);
}

function displayNationDatav2() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getnations = "SELECT tournwinner, count(tournwinner) AS occs FROM live_user_information GROUP BY tournwinner LIMIT 0, 300";

	// Obtain the SQL query result
	$result = mysqli_query($con, $sql_getnations) or die(mysqli_error());

	// Carry out the following for each result item
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		// Remove all carriage returns and new lines from array values
		$nation = str_replace("\r\n", "", $row["tournwinner"]);
		$occs = str_replace("\r\n", "", $row["occs"]);
		// Now put the values into a 'clean' array for output
		$arr = vsprintf("[%d,'%s'],", array($occs, $nation));
		// Output array data
		echo $arr;
	}
	// Free result set
	mysqli_free_result($result);
	// Close DB connection
	mysqli_close($con);
}

function displayLatestInformation() {
	// Create DB connection
	//include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	//$sql_getlatest5 = "SELECT firstname, surname, fieldofwork, signupdate FROM live_user_information ORDER BY signupdate DESC LIMIT 0, 5";
	//$sql_countusers = "SELECT count(*) AS totalusers FROM live_user_information";

	// Execute the query and return the result or display appropriate error message
	//$totalusers = mysqli_query($con, $sql_countusers) or die(mysqli_error());
	// For each instance of the returned result
	/*
	while ($row = mysqli_fetch_assoc($totalusers)) {
		$countoftotalusers = $row["totalusers"];
		$prizefund = ($countoftotalusers * 2);
	}
	*/
	//printf("<p><strong>Total players:</strong> %d</p>", $countoftotalusers);
	//printf("<p><strong>Total prize fund:</strong> £%d.00</p>", $prizefund);

 	print("<p><strong>Game complete:</strong><br>Thank you all for your participation!</p>");
	print("<p>Congratulations to the prize winners who are as follows:</p>");
	print("<table class='table table-condensed table-responsive table-bordered'>");
	print("<tr><td>1st</td><td>£100</td><td>Nick Chandler</td></tr>");
	print("<tr><td>2nd =</td><td>£37.50</td><td>Snigdha Dutta</td></tr>");
	print("<tr><td>2nd =</td><td>£37.50</td><td>Sonia Fernandez</td></tr>");
	print("<tr><td>3rd</td><td>£50</td><td>Daniel Waite</td></tr>");
	print("<tr><td>4th</td><td>£25</td><td>Paul Hendrick</td></tr>");
	print("<tr><td>5th</td><td>£10</td><td>Rebecca Reeves</td></tr>");
	print("</table>");

	/*print("<p><strong>Prizes announced:</strong></p>");
	print("<table class='table table-condensed table-responsive table-bordered table-striped'>");
	print("<tr><td>1st</td><td>£100</td></tr>");
	print("<tr><td>2nd</td><td>£75</td></tr>");
	print("<tr><td>3rd</td><td>£50</td></tr>");
	print("<tr><td>4th</td><td>£25</td></tr>");
	print("<tr><td>5th</td><td>£10</td></tr>");
	print("</table>");
	print("<p>Awarded to those who occupy these ranks after the full 64 games. Monies will be shared where joint ranks occur.</p>");
	//print("<p><strong>Remaining games</strong>: 1</p>");

	/* WINNING MESSAGES
 	print("<p><strong>Game complete:</strong><br>Thank you all for your participation!</p>");
	print("<p><strong>Prize winners:</strong></p>");
	print("<ul type='none'>");
	print("<li><img src='img/gold_ros.png' height='20px' alt='Gold rosette' />Jonathan Lamley (£80)</li>");
	print("<li><img src='img/silver_ros.png' height='20px' alt='Silver rosette' />Sam McGuigan (£50)</li>");
	print("<li><img src='img/bronze_ros.png' height='20px' alt='Bronze rosette' />Steve Butt (£15)</li>");
	print("<li><img src='img/bronze_ros.png' height='20px' alt='Bronze rosette' />Kirsty Yarnold (£15)</li>");
	print("</ul>");

	print("<p><strong>Feedback opportunity:</strong><br>Have your say on Hendy's Hunches! I'd be grateful if you could spare 2 mins to <a href='https://www.surveymonkey.co.uk/r/BQP9FGQ'>complete this quick survey</a>.</p>");
	*/

	// Obtain the SQL query result
	//$result = mysqli_query($con, $sql_getlatest5) or die(mysqli_error());

	// Carry out the following for each result item
	/*
	printf("<strong>Recent signups:\n</strong>");
	printf("<ul>");
	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		// Remove all carriage returns and new lines from array values
		$field = str_replace("\r\n", '', $row["fieldofwork"]);
		// Now output the values into a list for display
		printf ("<li>%s %s (%s)</li>", $row["firstname"], $row["surname"], $field);
	}
	printf("</ul>");
	// Free result set
	mysqli_free_result($result);
	// Close DB connection
	mysqli_close($con);
	*/
}

function displayCharityInformation() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get donation information from the DB	(counting occurrences)
	$sql_countusers = "SELECT count(*) AS totalusers FROM live_user_information";

	// Execute the query and return the result or display appropriate error message
	$totalusers = mysqli_query($con, $sql_countusers) or die(mysqli_error());
	// For each instance of the returned result
	while ($row = mysqli_fetch_assoc($totalusers)) {
		$countoftotalusers = $row["totalusers"];
		$donation = ($countoftotalusers * $GLOBALS["charity_fee"]);
	}

	print("<a href='https://www.sands.org.uk' target='_blank' title='Sands charity website'><img src='img/sands-logo.jpg' class='img-fluid w-50'></a>");
	//print("<h4 class='my-3'><strong>£74</strong> has been donated! Thank you.</h4>");
	//printf("<p><strong>Hendy's Hunches donation:</strong> £%d.00 (40&#37; from entry fees)", $donation);
	//print("<span class='label label-success'>A huge thank you to all players!</span>");
	printf("<p>Thanks to your participation, Hendy's Hunches currently has %d to donate to" . $GLOBALS["charity"] .":</p>");	
	// Free result set
	//mysqli_free_result($totalusers);
	// Close DB connection
	//mysqli_close($con);
}

function displayTopRankings() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_gettop5 = "SELECT firstname, surname, points_total FROM live_user_predictions_groups ORDER BY points_total DESC, surname ASC LIMIT 0, 5";
	// Check if any results have been recorded
	$sql_findmatches = "SELECT * FROM live_match_results";

	// Obtain the SQL query results
	$result1 = mysqli_query($con, $sql_gettop5) or die(mysqli_error());
	$result2 = mysqli_query($con, $sql_findmatches) or die(mysqli_error());

	// Carry out the following for each result item
	printf("<ul>");

	if (mysqli_num_rows($result2) == 0) {
		// Remove all carriage returns and new lines from array values
		printf("<li>No results available</li>");
	}
	else {
		while($row = mysqli_fetch_array($result1, MYSQLI_ASSOC)) {
			// Remove all carriage returns and new lines from array values
			$points = str_replace("\r\n", '', $row["points_total"]);
			// Now output the values into a list for display
			printf ("<li>%s %s (%s points)</li>", $row["firstname"], $row["surname"], $points);
		}
	}
	printf("</ul>");
	// Free result set
	mysqli_free_result($result1);
	mysqli_free_result($result2);
	// Close DB connection
	mysqli_close($con);
}

function displayBottomRankings() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getbottom5 = "SELECT * FROM (SELECT firstname, surname, points_total FROM live_user_predictions_groups ORDER BY points_total ASC, surname DESC LIMIT 0, 5) TmpTable ORDER BY points_total DESC, surname ASC";
	// Check if any results have been recorded
	$sql_findmatches = "SELECT * FROM live_match_results";

	// Obtain the SQL query results
	$result1 = mysqli_query($con, $sql_getbottom5) or die(mysqli_error());
	$result2 = mysqli_query($con, $sql_findmatches) or die(mysqli_error());

	// Carry out the following for each result item
	printf("<ul>");

	if (mysqli_num_rows($result2) == 0) {
		// Remove all carriage returns and new lines from array values
		printf("<li>No results available</li>");
	}
	else {
		while($row = mysqli_fetch_array($result1, MYSQLI_ASSOC)) {
			// Remove all carriage returns and new lines from array values
			$points = str_replace("\r\n", '', $row["points_total"]);
			// Now output the values into a list for display
			printf ("<li>%s %s (%s points)</li>", $row["firstname"], $row["surname"], $points);
		}
	}
	printf("</ul>");
	// Free result set
	mysqli_free_result($result1);
	mysqli_free_result($result2);
	// Close DB connection
	mysqli_close($con);
}

function displayBestMovers() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getbestmovers = "SELECT firstname, surname, lastpos-currpos AS diff FROM live_user_information WHERE (lastpos-currpos) > 0 ORDER BY diff DESC, surname ASC LIMIT 0, 5";

	// Obtain the SQL query result
	$result = mysqli_query($con, $sql_getbestmovers) or die(mysqli_error());

	// Carry out the following for each result item
	printf("<ul>");

	if (mysqli_num_rows($result) == 0) {
		// Remove all carriage returns and new lines from array values
		printf("<li>No results available</li>");
	}

	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		// Output the values into a list for display
		printf ("<li>%s %s <i class='bi bi-arrow-up-circle-fill text-success'></i> %s</li>", $row["firstname"], $row["surname"], $row["diff"]);
	}
	printf("</ul>");
	// Free result set
	mysqli_free_result($result);
	// Close DB connection
	mysqli_close($con);
}

function displayWorstMovers() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getworstmovers = "SELECT firstname, surname, currpos-lastpos AS diff FROM live_user_information WHERE (currpos-lastpos) > 0 ORDER BY diff DESC, surname ASC LIMIT 0, 5";

	// Obtain the SQL query result
	$result = mysqli_query($con, $sql_getworstmovers) or die(mysqli_error());

	// Carry out the following for each result item
	printf("<ul>");

	if (mysqli_num_rows($result) == 0) {
		// Remove all carriage returns and new lines from array values
		printf("<li>No results available</li>");
	}

	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		// Output the values into a list for display
		printf ("<li>%s %s <i class='bi bi-arrow-down-circle-fill text-danger'></i> %s</li>", $row["firstname"], $row["surname"], $row["diff"]);
	}
	printf("</ul>");
	// Free result set
	mysqli_free_result($result);
	// Close DB connection
	mysqli_close($con);
}

function displayTodaysFixtures() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_gettodaysgames = "SELECT * FROM live_match_schedule WHERE date = CURDATE()";

	// Obtain the SQL query result and set corresponding result variables
	$gamedata = mysqli_query($con, $sql_gettodaysgames);

	$today = date("jS F, Y");
	printf ("<p class='text-center'>%s</p>", $today);

	while($row = mysqli_fetch_assoc($gamedata)) {
		// Assign variables for printing
		$HT = strtoupper($row['hometeam']);
		$AT = strtoupper($row['awayteam']);
		$htabb = substr($HT,0,3);
		$atabb = substr($AT,0,3);
		$kotime = $row['kotime'];
		$venue = $row['venue'];
		$hs = $row['homescore'];
		$as = $row['awayscore'];

		if((is_null($hs)) || (is_null($as))) {
			$hs = '';
			$as = '';
		}

		printf ("<div class='text-center'><img src=".$row['hometeamimg']." alt='Nation Flag' name='Nation Flag' style=''> <strong>%s</strong> <span class='label label-default'>%s</span> v <span class='label label-default'>%s</span> <strong>%s</strong> <img src=".$row['awayteamimg']." alt='Nation Flag' name='Nation Flag' style=''><br/><span style='font-size: 11px;'>(%s @ %s)<br><br></span></div>", $htabb, $hs, $as, $atabb, $kotime, $venue);
   	}

	if (mysqli_num_rows($gamedata) == 0) {
		// Remove all carriage returns and new lines from array values
		printf("<p class='text-center'><strong>No matches today</strong></p>");
	}

	// Free result set
	mysqli_free_result($gamedata);
	// Close DB connection
	mysqli_close($con);
}

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

function returnProfileData() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getprofileinfo1 = "SELECT avatar, faveteam, fieldofwork, location, tournwinner, signupdate, haspaid, currpos FROM live_user_information WHERE username = '".$_SESSION["username"]."'";
	$sql_getprofileinfo2 = "SELECT points_total FROM live_user_predictions_groups WHERE username = '".$_SESSION["username"]."'";

	// Obtain the SQL query result and set corresponding result variables
	$result1 = mysqli_query($con, $sql_getprofileinfo1);
	$userdata1 = mysqli_fetch_assoc($result1);
	$result2 = mysqli_query($con, $sql_getprofileinfo2);
	$userdata2 = mysqli_fetch_assoc($result2);
	// Assign returned data to variables
	$uppCaseFN = ucfirst($userdata1["firstname"]);
	$uppCaseSN = ucfirst($userdata1["surname"]);
	$avatar = $userdata1["avatar"];
	$fieldofwork = $userdata1["fieldofwork"];
	$location = $userdata1["location"];
	$faveteam = $userdata1["faveteam"];
	$tournwinner = $userdata1["tournwinner"];
	$originalsignupdate = $userdata1["signupdate"];
	$haspaid = $userdata1["haspaid"];
	$currpos = ordinal($userdata1["currpos"]);
	$pointstotal = $userdata2["points_total"];
	$convertedDate = date("l jS \of F", strtotime($originalsignupdate));
}

function checkSubmitted() {
	// Create DB connection
	include 'db-connect.php';
	$un = $_SESSION["username"];
	// Get team information from the DB	counting occurrences too
	$sql_predstatus = sprintf("SELECT username FROM live_user_predictions_groups WHERE username = '%s'", $un);
	$predstatus = mysqli_query($con, $sql_predstatus);

	if (mysqli_num_rows($predstatus) > 0) {
		// consoleMsg($predstatus);
		print("<p class='alert alert-success'><i class='bi bi-check2-square text-success'></i> Successfully submitted your group fixture predictions.</p>");
	}
	else {
		print("<p class='alert alert-danger'><i class='bi bi-exclamation-square text-danger'></i> Please <a href='predictions.php' title='Submit your predictions'>submit your predictions</a> for the group fixtures</a>.</p>");
	}
}

function displayMatchesRecorded() {
	// Create DB connection
	include 'php/db-connect.php';

	$sql_get_matches_played = "SELECT COUNT(*) AS matches_played FROM live_match_results";
	$matches_played = mysqli_query($con, $sql_get_matches_played);

	while ($row = mysqli_fetch_assoc($matches_played)) {
		$no_of_matches_played = $row["matches_played"];
		// console.log($no_of_matches_played);
		//$percent_group_played = round($no_of_matches_played * 100 / 48);
	}

	printf("<p>Matches recorded: %d of 64</p>", $no_of_matches_played);
	// Close DB connection
	mysqli_close($con);
}

function displayGroupMatchesPlayed() {
	// Create DB connection
	include 'php/db-connect.php';

	$sql_get_matches_played = "SELECT COUNT(*) AS matches_played FROM live_match_results";
	$matches_played = mysqli_query($con, $sql_get_matches_played);

	while ($row = mysqli_fetch_assoc($matches_played)) {
		$no_of_matches_played = $row["matches_played"];
		//console.log($no_of_matches_played);
		$percent_group_played = round($no_of_matches_played * 100 / $GLOBALS["no_of_group_fixtures"]);
	}
	echo "<div class='row'>
			<div class='col-sm-3'>
				<p class='text-black'>Group stage:</p>
			</div>
			<div class='col-sm-9'>			
				<div class='progress my-1'><div class='progress-bar bg-success' role='progressbar' aria-label='Competition progress bar' style='width: 0%;' aria-valuenow='$percent_group_played' aria-valuemin='0' aria-valuemax='100'></div></div>
			</div>
		</div>";

	// Close DB connection
	mysqli_close($con);
}



function displayRO16MatchesPlayed() {
	// Create DB connection
	include 'php/db-connect.php';

	$sql_get_matches_played = "SELECT COUNT(*) AS matches_played FROM live_match_results";
	$matches_played = mysqli_query($con, $sql_get_matches_played);

	while ($row = mysqli_fetch_assoc($matches_played)) {
		$no_of_matches_played = $row["matches_played"];
		$no_of_ro16_matches_played = $no_of_matches_played - $GLOBALS["no_of_group_fixtures"];
		//console.log($no_of_matches_played);
		$percent_ro16_played = round($no_of_ro16_matches_played * 100 / $GLOBALS["no_of_ro16_fixtures"]);
	}
	echo "<div class='row'>
			<div class='col-sm-3'>
				<p class='text-muted'>Round of 16:</p>
			</div>
			<div class='col-sm-9'>			
				<div class='progress my-1'><div class='progress-bar bg-secondary' role='progressbar' aria-label='Competition progress bar' style='width: 0%;' aria-valuenow='$percent_ro16_played' aria-valuemin='0' aria-valuemax='100'>$percent_ro16_played%</div></div>
			</div>
		</div>";	
	// Close DB connection
	mysqli_close($con);
}

function displayQFMatchesPlayed() {
	// Create DB connection
	include 'php/db-connect.php';

	$sql_get_matches_played = "SELECT COUNT(*) AS matches_played FROM live_match_results";
	$matches_played = mysqli_query($con, $sql_get_matches_played);

	while ($row = mysqli_fetch_assoc($matches_played)) {
		$no_of_matches_played = $row["matches_played"];
		$no_of_qf_matches_played = $no_of_matches_played - ($GLOBALS["no_of_group_fixtures"] + $GLOBALS["no_of_ro16_fixtures"]);
		//console.log($no_of_matches_played);
		$percent_qf_played = round($no_of_qf_matches_played * 100 / $GLOBALS["no_of_qf_fixtures"]);
	}
	echo "<div class='row'>
			<div class='col-sm-3'>
				<p class='text-muted'>Quarter finals:</p>
			</div>
			<div class='col-sm-9'>			
				<div class='progress my-1'><div class='progress-bar bg-success' role='progressbar' aria-label='Competition progress bar' style='width: 0%;' aria-valuenow='$percent_qf_played' aria-valuemin='0' aria-valuemax='100'></div></div>
			</div>
		</div>";	
	
	// Close DB connection
	mysqli_close($con);
}

function displaySFMatchesPlayed() {
	// Create DB connection
	include 'php/db-connect.php';

	$sql_get_matches_played = "SELECT COUNT(*) AS matches_played FROM live_match_results";
	$matches_played = mysqli_query($con, $sql_get_matches_played);

	while ($row = mysqli_fetch_assoc($matches_played)) {
		$no_of_matches_played = $row["matches_played"];
		$no_of_sf_matches_played = $no_of_matches_played - ($GLOBALS["no_of_group_fixtures"] + $GLOBALS["no_of_ro16_fixtures"] + $GLOBALS["no_of_qf_fixtures"]);
		//console.log($no_of_matches_played);
		$percent_sf_played = round($no_of_sf_matches_played * 100 / $GLOBALS["no_of_sf_fixtures"]);
	}
	echo "<div class='row'>
			<div class='col-sm-3'>
				<p class='text-muted'>Semi finals:</p>
			</div>
			<div class='col-sm-9'>			
				<div class='progress my-1'><div class='progress-bar bg-success' role='progressbar' aria-label='Competition progress bar' style='width: 0%;' aria-valuenow='$percent_sf_played' aria-valuemin='0' aria-valuemax='100'></div></div>
			</div>
		</div>";
	// Close DB connection
	mysqli_close($con);
}

function displayPersonalInfo() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getprofileinfo1 = "SELECT firstname, surname, avatar, faveteam, fieldofwork, location, tournwinner, signupdate, currpos FROM live_user_information WHERE username = '".$_SESSION["username"]."'";
	$sql_getprofileinfo2 = "SELECT  points_total FROM live_user_predictions_groups WHERE username = '".$_SESSION["username"]."'";
	/*$sql_getpointstotal = "SELECT live_user_information.id, live_user_predictions_groups.points_total as group_points, live_user_predictions_ro16.points_total as ro16_points, live_user_predictions_qf.points_total as qf_points, live_user_predictions_sf.points_total as sf_points, live_user_predictions_groups.points_total + live_user_predictions_ro16.points_total + live_user_predictions_qf.points_total + live_user_predictions_sf.points_total as points_total
						FROM live_user_information
						INNER JOIN live_user_predictions_groups ON live_user_information.id = live_user_predictions_groups.id
						INNER JOIN live_user_predictions_ro16 ON live_user_information.id = live_user_predictions_ro16.id
						INNER JOIN live_user_predictions_qf ON live_user_information.id = live_user_predictions_qf.id
						INNER JOIN live_user_predictions_sf ON live_user_information.id = live_user_predictions_sf.id
            			WHERE live_user_information.id = '".$_SESSION["id"]."'";*/
	$sql_getpointstotal = "SELECT live_user_information.id, live_user_predictions_groups.points_total
						FROM live_user_information
						INNER JOIN live_user_predictions_groups ON live_user_information.id = live_user_predictions_groups.id
            			WHERE live_user_information.id = '".$_SESSION["id"]."'";

	// Obtain the SQL query result and set corresponding result variables
	$result1 = mysqli_query($con, $sql_getprofileinfo1);
	$userdata1 = mysqli_fetch_assoc($result1);
	$result2 = mysqli_query($con, $sql_getprofileinfo2);
	$userdata2 = mysqli_fetch_assoc($result2);
	$result3 = mysqli_query($con, $sql_getpointstotal);
	$userdata3 = mysqli_fetch_assoc($result3);

	// Assign returned data to variables
	$uppCaseFN = ucfirst($userdata1["firstname"]);
	$uppCaseSN = ucfirst($userdata1["surname"]);
	$avatar = $userdata1["avatar"];
	$fieldofwork = $userdata1["fieldofwork"];
	$location = $userdata1["location"];
	$faveteam = $userdata1["faveteam"];
	$tournwinner = $userdata1["tournwinner"];
	$originalsignupdate = $userdata1["signupdate"];
	$currpos = ordinal($userdata1["currpos"]);
	$pointstotal = $userdata3["points_total"] ?? 0;
	$converteddate = date("jS F Y", strtotime($originalsignupdate));
	$startyear = "2006";
	$highestfinish = ordinal(1);
	//$matchresult = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));

	// If table contains no data, then display 'not available message'
	if ((mysqli_num_rows($result1) == 0) || (mysqli_num_rows($result2) == 0)) {
		// Remove all carriage returns and new lines from array values
		printf("<br><p class='text-center'><strong>No information available</strong><br>(Until a <a href='predictions.php'>prediction is made</a>)</p><br><br><br>");
	}
	// Else display the user's available data
	else {
		echo '<div class="row">';
		echo '    <div class="col-4">';
		echo '        <img class="img-fluid" alt="Football kit avatar" src="' . $avatar . '">';
		echo '    </div>';
		echo '    <div class="col-8">';
		echo '        <h3>' . $_SESSION["firstname"] . ' ' . $_SESSION["surname"] . '</h3>';
		echo '        <p class="fs-4 text-dark text-center" style="border: 1px solid #000; padding: 0.5rem;"><strong>' . $currpos . ' <span class="text-muted">|</span> ' . $pointstotal . ' pts</strong></p>';
		echo '        <ul style="list-style-type: none; margin: 0; padding: 0;"><li><i class="bi bi-heart-fill"></i> ' . $faveteam . ' Football Club</li>';
		echo '        <li><i class="bi bi-wrench-adjustable-circle-fill"></i> ' . $fieldofwork . '</li>';
		echo '        <li><i class="bi bi-trophy-fill"></i> Thinks ' . $tournwinner . ' will win '. $GLOBALS['competition'] .'</li>';
		echo '        <li><i class="bi bi-calendar2-week-fill"></i> Signed up on ' . $converteddate . '</li>';
		echo '        <li><i class="bi bi-hourglass-split"></i> Playing since ' . $startyear . '</li>';
		echo '        <li><i class="bi bi-star-fill"></i> Highest finish is ' . $highestfinish . '</li></ul>';	
		echo '    </div>';
		echo '</div>';
		echo '<hr>';
		echo '<div class="d-flex justify-content-center align-items-center flex-wrap gap-2 mt-2">';
		echo '    <a class="btn btn-secondary" href="user.php?id=' . $_SESSION['id'] . '" title="Show predictions"><i class="bi bi-person-lines-fill"></i> View my predictions</a>';
		// echo '    <a class="btn btn-sm btn-secondary" href="change-password.php">Change password</a>';		
		echo '    <a class="btn btn-secondary" href="rankings.php"><i class="bi bi-list-ol"></i> Check current rankings</a>';
		echo '</div>';
	}
	// Free result set
	mysqli_free_result($result1);
	mysqli_free_result($result2);
	mysqli_free_result($result3);
	// Close DB connection
	mysqli_close($con);
}


function displayPayStatus() {
	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getpaystatus = "SELECT haspaid FROM live_user_information WHERE username = '".$_SESSION["username"]."'";

	// Obtain the SQL query result and set corresponding result variables
	$result = mysqli_query($con, $sql_getpaystatus);
	$userdata = mysqli_fetch_assoc($result);
	// Assign returned data to variables
	$haspaid = $userdata["haspaid"];	

	if ($haspaid == "No") {
		echo "<p class='alert alert-danger'><i class='bi bi-exclamation-square text-danger'></i> Please pay ". $GLOBALS['signup_fee'] ." to play before $GLOBALS[competition_start_date]. <a class='btn btn-sm btn-primary' href='https://monzo.me/jamescolinhenderson/5.00?d=Hendy%27s%20Hunches%20-%20%5BYour%20Name%5D' role='button' target='_blank'><i class='bi bi-credit-card-fill'></i> Pay Now</a></p>";
	}
	else {
		echo "<p class='alert alert-success'><i class='bi bi-check2-square text-success'></i> You've paid to play! Thank you.</p>";
	}

	// Free result set
	mysqli_free_result($result);
	// Close DB connection
	mysqli_close($con);
}
?>
