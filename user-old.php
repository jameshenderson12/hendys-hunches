<?php
// Start the session
session_start();
if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
	header ("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-119623195-1"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'UA-119623195-1');
	</script>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Hendy's Hunches: Predictions Game">
	<meta name="author" content="James Henderson">
    <?php include "php/config.php" ?>
    <link rel="icon" href="ico/favicon.ico">

    <title>Hendy's Hunches: Rankings</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/custom.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <style>
	  label {
		  font-size: 1em;
	  }
	  .left-score {
		  float: left;
	  }
	  .right-score {
		  float: right;
	  }
	  .left-team {
		  text-align: left;
	  }
	  .left-team img {
		  float: left;
		  margin-right: 5px;
	  }
	  .table .right-team {
		  text-align: right;
	  }
	  .right-team img {
		  float: right;
		  margin-left: 5px;
	  }
	  .date-venue {
		margin-left: 50px;
		font-size: 9px;
	  }
	  .prediction {
		  background-color: #999;
		  padding: 5px;
		  color: #FFF;
	  }
	  .result {
		  background-color: #428bca;
		  padding: 5px;
		  color: #FFF;
	  }
	  th {
		  text-align: center;
	  }

	/* Code to remove knockout fixture inputs...*/
	tr:nth-child(48) input, tr:nth-child(49) input, tr:nth-child(50) input, tr:nth-child(51) input, tr:nth-child(52) input, tr:nth-child(53) input, tr:nth-child(54) input, tr:nth-child(55) input, tr:nth-child(56) input, tr:nth-child(57) input, tr:nth-child(58) input, tr:nth-child(59) input, tr:nth-child(60) input, tr:nth-child(61) input, tr:nth-child(62) input, tr:nth-child(63) input, tr:nth-child(64) input { display: none; }
	*/

	/*tr:nth-child(51) input { display: none; }	*/

	@media only screen and (max-width: 640px) {
		#avatar { display: none; }
		table td:nth-child(1),
		table th:nth-child(1){display: none;}
		.starter-template { padding: 0px; }
	}
	@media only screen and (max-width: 320px) {
		label { display: none; }
	}
	</style>
  </head>

  <body>

    <!-- Navigation menu for lg, md display -->
    <nav class="navbar navbar-inverse navbar-fixed-top col-md-12 hidden-xs">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!--<a class="navbar-brand" href="#">Project name</a>-->
          <img src="img/hh-favicon-2018.jpg" class="img-responsive" style="margin: 0px 20px 0px; height:50px">
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left" style="margin-left: 15px;">
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="predictions.php">My Predictions</a></li>
            <li class="active"><a href="rankings.php">Rankings</a></li>
            <li><a href="howitworks.php">How It Works</a></li>
			<li><a href="about.php">About</a></li>
          </ul>
          <!--
          <form class="navbar-form navbar-right">
            <input type="text" class="form-control" placeholder="Search...">
          </form>-->
        </div>
        <div class="col-md-12">
           <?php
			// Echo session variables that were set on previous page
			echo "<span id='login'><span style='color: white'><p>Logged in as: <span class='glyphicon glyphicon-user'></span>&nbsp;<span style='font-weight:bold'>" . $_SESSION["firstname"] . " " . $_SESSION["surname"] . "</span> ( <a href='php/logout.php'>Logout</a> )</p></span></span>";
          ?>
        </div>
      </div>
    </nav>

    <!-- Navigation menu for xs display -->
    <nav class="navbar navbar-inverse navbar-fixed-top col-md-12 visible-xs hidden-lg hidden-md">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar2" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!--<a class="navbar-brand" href="#">Project name</a>-->
          <img src="img/hh-favicon-2018.jpg" class="img-responsive" style="margin: 0px 20px 0px; height:50px">
        </div>
        <div id="navbar2" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left">
            <li><a href="dashboard.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>&nbsp;&nbsp;Home</a></li>
            <li><a href="predictions.php"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>&nbsp;&nbsp;My Predictions</a></li>
            <li class="active"><a href="rankings.php"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>&nbsp;&nbsp;Rankings</a></li>
            <li><a href="howitworks.php"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp;How It Works</a></li>
			<li><a href="about.php"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>&nbsp;&nbsp;About</a></li>
            <li><a href="php/logout.php"><span class='glyphicon glyphicon-user' aria-hidden="true"></span>&nbsp;&nbsp;Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

  		  <?php
            // Connect to the database
            include 'php/db-connect.php';

            // Set up variable to capture result of SQL query to retrieve data from database tables
            $sql_getuserinfo = "SELECT live_user_predictions.*, live_user_information.avatar, live_user_information.faveteam, live_user_information.fieldofwork, live_user_information.tournwinner, live_user_information.currpos, live_user_information.wc2014rank, live_user_information.eu2016rank FROM live_user_predictions INNER JOIN live_user_information ON live_user_predictions.id = live_user_information.id
			WHERE live_user_predictions.id='".$_GET["id"]."'";

            // Global SQL query strings
            $sql_getresults = "SELECT SUM(score1_r) as score1_r, SUM(score2_r) as score2_r, SUM(score3_r) as score3_r, SUM(score4_r) as score4_r, SUM(score5_r) as score5_r, SUM(score6_r) as score6_r, SUM(score7_r) as score7_r, SUM(score8_r) as score8_r, SUM(score9_r) as score9_r, SUM(score10_r) as score10_r, SUM(score11_r) as score11_r, SUM(score12_r) as score12_r, SUM(score13_r) as score13_r, SUM(score14_r) as score14_r, SUM(score15_r) as score15_r, SUM(score16_r) as score16_r, SUM(score17_r) as score17_r, SUM(score18_r) as score18_r, SUM(score19_r) as score19_r, SUM(score20_r) as score20_r, SUM(score21_r) as score21_r, SUM(score22_r) as score22_r, SUM(score23_r) as score23_r, SUM(score24_r) as score24_r, SUM(score25_r) as score25_r, SUM(score26_r) as score26_r, SUM(score27_r) as score27_r, SUM(score28_r) as score28_r, SUM(score29_r) as score29_r, SUM(score30_r) as score30_r, SUM(score31_r) as score31_r, SUM(score32_r) as score32_r, SUM(score33_r) as score33_r, SUM(score34_r) as score34_r, SUM(score35_r) as score35_r, SUM(score36_r) as score36_r, SUM(score37_r) as score37_r, SUM(score38_r) as score38_r, SUM(score39_r) as score39_r, SUM(score40_r) as score40_r, SUM(score41_r) as score41_r, SUM(score42_r) as score42_r, SUM(score43_r) as score43_r, SUM(score44_r) as score44_r, SUM(score45_r) as score45_r, SUM(score46_r) as score46_r, SUM(score47_r) as score47_r, SUM(score48_r) as score48_r, SUM(score49_r) as score49_r, SUM(score50_r) as score50_r, SUM(score51_r) as score51_r, SUM(score52_r) as score52_r, SUM(score53_r) as score53_r, SUM(score54_r) as score54_r, SUM(score55_r) as score55_r, SUM(score56_r) as score56_r, SUM(score57_r) as score57_r, SUM(score58_r) as score58_r, SUM(score59_r) as score59_r, SUM(score60_r) as score60_r, SUM(score61_r) as score61_r, SUM(score62_r) as score62_r, SUM(score63_r) as score63_r, SUM(score64_r) as score64_r, SUM(score65_r) as score65_r, SUM(score66_r) as score66_r, SUM(score67_r) as score67_r, SUM(score68_r) as score68_r, SUM(score69_r) as score69_r, SUM(score70_r) as score70_r, SUM(score71_r) as score71_r, SUM(score72_r) as score72_r, SUM(score73_r) as score73_r, SUM(score74_r) as score74_r, SUM(score75_r) as score75_r, SUM(score76_r) as score76_r, SUM(score77_r) as score77_r, SUM(score78_r) as score78_r, SUM(score79_r) as score79_r, SUM(score80_r) as score80_r, SUM(score81_r) as score81_r, SUM(score82_r) as score82_r, SUM(score83_r) as score83_r, SUM(score84_r) as score84_r, SUM(score85_r) as score85_r, SUM(score86_r) as score86_r, SUM(score87_r) as score87_r, SUM(score88_r) as score88_r, SUM(score89_r) as score89_r, SUM(score90_r) as score90_r, SUM(score91_r) as score91_r, SUM(score92_r) as score92_r, SUM(score93_r) as score93_r, SUM(score94_r) as score94_r, SUM(score95_r) as score95_r, SUM(score96_r) as score96_r, SUM(score97_r) as score97_r, SUM(score98_r) as score98_r, SUM(score99_r) as score99_r, SUM(score100_r) as score100_r, SUM(score101_r) as score101_r, SUM(score102_r) as score102_r, SUM(score103_r) as score103_r, SUM(score104_r) as score104_r, SUM(score105_r) as score105_r, SUM(score106_r) as score106_r, SUM(score107_r) as score107_r, SUM(score108_r) as score108_r, SUM(score108_r) as score108_r, SUM(score109_r) as score109_r, SUM(score110_r) as score110_r, SUM(score111_r) as score111_r, SUM(score112_r) as score112_r, SUM(score113_r) as score113_r, SUM(score114_r) as score114_r, SUM(score115_r) as score115_r, SUM(score116_r) as score116_r, SUM(score117_r) as score117_r, SUM(score118_r) as score118_r, SUM(score119_r) as score119_r, SUM(score120_r) as score120_r, SUM(score121_r) as score121_r, SUM(score122_r) as score122_r, SUM(score123_r) as score123_r, SUM(score124_r) as score124_r, SUM(score125_r) as score125_r, SUM(score126_r) as score126_r, SUM(score127_r) as score127_r, SUM(score128_r) as score128_r FROM live_match_results";
            //$sql_getid = "SELECT id FROM live_user_predictions";

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
			$faveteam = $userdata["faveteam"];
			$tournwinner = $userdata["tournwinner"];
			$currentpos = ordinal($userdata["currpos"]);
			$wc2014rank = ordinal($userdata["wc2014rank"]);
	  		$eu2016rank = ordinal($userdata["eu2016rank"]);
			$lastupdate = $userdata["lastupdate"];
			$lastupdated = date("D, jS M @ H:i", strtotime($lastupdate));
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
            $sql_getspecid = "SELECT id, firstname, surname, score1_p, score2_p, score3_p, score4_p, score5_p, score6_p, score7_p, score8_p, score9_p, score10_p, score11_p, score12_p, score13_p, score14_p, score15_p, score16_p, score17_p, score18_p, score19_p, score20_p, score21_p, score22_p, score23_p, score24_p, score25_p, score26_p, score27_p, score28_p, score29_p, score30_p, score31_p, score32_p, score33_p, score34_p, score35_p, score36_p, score37_p, score38_p, score39_p, score40_p, score41_p, score42_p, score43_p, score44_p, score45_p, score46_p, score47_p, score48_p, score49_p, score50_p, score51_p, score52_p, score53_p, score54_p, score55_p, score56_p, score57_p, score58_p, score59_p, score60_p, score61_p, score62_p, score63_p, score64_p, score65_p, score66_p, score67_p, score68_p, score69_p, score70_p, score71_p, score72_p, score73_p, score74_p, score75_p, score76_p, score77_p, score78_p, score79_p, score80_p, score81_p, score82_p, score83_p, score84_p, score85_p, score86_p, score87_p, score88_p, score89_p, score90_p, score91_p, score92_p, score93_p, score94_p, score95_p, score96_p, score97_p, score98_p, score99_p, score100_p, score101_p, score102_p, score103_p, score104_p, score105_p, score106_p, score107_p, score108_p, score109_p, score110_p, score111_p, score112_p, score113_p, score114_p, score115_p, score116_p, score117_p, score118_p, score119_p, score120_p, score121_p, score122_p, score123_p, score124_p, score125_p, score126_p, score127_p, score128_p FROM live_user_predictions WHERE id='".$userid."'";
            $pvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getspecid));
            $rvalue = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));

			for ($gameno=1; $gameno<129; $gameno+=2) {
                $oddgameno[] = $gameno;
                $evengameno[] = $gameno + 1;
            }

                for ($i=0; $i<=64; $i++) {
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

						if ((($pvalue["score".$oddgameno[$i]."_p"] > $pvalue["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] > $rvalue["score".$evengameno[$i]."_r"])) || (($pvalue["score".$oddgameno[$i]."_p"] < $pvalue["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] < $rvalue["score".$evengameno[$i]."_r"])) || (($pvalue["score".$oddgameno[$i]."_p"] === $pvalue["score".$evengameno[$i]."_p"]) && ($rvalue["score".$oddgameno[$i]."_r"] === $rvalue["score".$evengameno[$i]."_r"])) ) {
							$matchpoints[$i] += 2;
						}
					}
/*
					else {
						print ("There is a non-numeric value somewhere!");
					}
					*/
                }
         ?>

	<div id="main-section" class="col-md-10 col-md-offset-1">
        <h1 class="page-header">Opponent Predictions: <?php print "$uppCaseFN $uppCaseSN" ?></h1>
        <!--<p class="lead">View all predictions and points collected by <?php print "$uppCaseFN" ?> below.</p>-->
        <p>You are currently view all predictions as made by <?php print "$uppCaseFN $uppCaseSN" ?>. Return to the <a href="rankings.php">rankings</a> table.</p>

    <div class="panel panel-default">
	  	<div class="panel-body">
		    <div class="row" style="">
              <div class="col-md-2 col-xs-12">
                <img src="<?php echo $avatar ?>" id="avatar" class="img-responsive" alt="User Avatar" name="User Avatar" width="120" style="margin: 0 auto; padding: 10px;">
                <?php printf("<p class='text-center 'style='font-size: 1.5em; color: #222;'><strong>%s&nbsp;&nbsp;<span style='color:#CCC;'>|</span>&nbsp;&nbsp;%spts</strong></p>", $currentpos, $pointstotal); ?>
              </div>
              <div class="col-md-10 col-xs-12">
                <p><?php printf ("%s thinks %s will win the 2018 FIFA World Cup.", $uppCaseFN, $tournwinner); ?></p>
                <p><?php printf ("<strong>Favourite Team:</strong> %s", $faveteam); ?></p>
                <p><?php printf ("<strong>Field of Work:</strong> %s", $fieldofwork); ?></p>
                <p><?php printf ("<strong>Previous Ranks:</strong> %s (WC2014), %s (EURO2016)", $wc2014rank, $eu2016rank); ?></p>
                <p><?php printf ("<strong>Last Updated:</strong> %s", $lastupdated); ?></p>
                <a href='rankings.php' class='btn btn-default'>Return to rankings table</a>
              </div>
            </div>
		 </div>
	</div>

      <div class="row">
      <div class="col-xs-12">
        <table class="table table-striped" style="background-color: #FFF;">
        <tr>
        <th width="6%"></th>
        <th width="22%"></th>
        <th width="10%"></th>
        <th width="22%"></th>
        <th align="center" width="">Prediction</th>
        <th align="center" width="">Result</th>
        <th align="center" width="10%">Points</th>
        </tr>



				<!--
					<script>
							$(document).ready(function () {
									// Fetch data from JSON file
									$.getJSON("json/fifa-world-cup-2022-fixtures-groups.json",
										function (data) {
											var fixture = '';
											var	x = 1;
											var y = 2;
											var z = 0;
											// Iterate through objects
											$.each(data, function (key, value) {
													var hid = "score"+x+"_p";
													var aid = "score"+y+"_p";
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
													fixture += '<td class="small text-muted d-none d-md-block">' + value.Group + '<br></td>';
													fixture += '<td style="text-align: right">' + value.HomeTeam + '</td>';
													fixture += '<td><img src="' + homeTeamFlag + '" alt="Flag of ' + homeTeam + '" title="Flag of ' + homeTeam + '"></td>';
													fixture += '<td align="center">v<br><span class="badge bg-light text-primary">' + value.MatchNumber + '</span></td>';
													fixture += '<td><img src="' + awayTeamFlag + '" alt="Flag of ' + awayTeam + '" title="Flag of ' + awayTeam + '"></td>';
													fixture += '<td>' + value.AwayTeam + '</td>';
													fixture += '<td class="small text-muted d-none d-md-block">' + date + '<br></td>';
													fixture += '<td align="center"><span class="prediction"><?php echo $userdata[]; ?> - <?php echo $userdata[] ?></span></td>';
													fixture += '<td align="center"><?php if($matchids[z]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[z]."_r"], $matchresult["score".$evengameno[z]."_r"]); } else echo "N/A"; ?></td>';
													fixture += '<td align="center"><?php if($matchids[z]) { echo $matchpoints[z]; } else { echo "-"; } ?></td>';
													fixture += '</tr>';
													x+=2;
													y+=2;
													z++;
											});
										// Insert rows into table
										$('#table').append(fixture);
									});
							});
					</script>-->






      	<tr>
		<td class="date-venue">Match 1<br>Group A</td>
	    <td class="left-team">
        <img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"><label for="score1_p"><?php echo $A1; ?></label></td>
        <td align="center"><span>v</span></td>
		<td class="right-team">
        <img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"><label for="score2_p"><?php echo $A2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score1_p'] ?> - <?php echo $userdata['score2_p'] ?></span></td>
        <td align="center"><?php if($matchids[0]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[0]."_r"], $matchresult["score".$evengameno[0]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[0]) { echo $matchpoints[0]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
		<td class="date-venue">Match 2<br>Group A</td>
      	<td class="left-team">
        <img src="<?php echo $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"><label for="score3_p"><?php echo $A3; ?></label></td>
      	<td align="center"><span>v</span></td>
      	<td class="right-team">
        <img src="<?php echo $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"><label for="score4_p"><?php echo $A4; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score3_p'] ?> - <?php echo $userdata['score4_p'] ?></span></td>
      	<td align="center"><?php if($matchids[1]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[1]."_r"], $matchresult["score".$evengameno[1]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[1]) { echo $matchpoints[1]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 3<br>Group B</td>
      	<td class="left-team">
        <img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"><label for="score5_p"><?php echo $B3; ?></label></td>
       	<td align="center"><span>v</span></td>
        <td class="right-team">
        <img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"><label for="score6_p"><?php echo $B4; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score5_p'] ?> - <?php echo $userdata['score6_p'] ?></span></td>
        <td align="center"><?php if($matchids[2]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[2]."_r"], $matchresult["score".$evengameno[2]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[2]) { echo $matchpoints[2]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
      	<td class="date-venue">Match 4<br>Group B</td>
        <td class="left-team">
        <img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"><label for="score7_p"><?php echo $B1; ?></label></td>
        <td align="center"><span>v</span></td>
      	<td class="right-team">
        <img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"><label for="score8_p"><?php echo $B2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score7_p'] ?> - <?php echo $userdata['score8_p'] ?></span></td>
      	<td align="center"><?php if($matchids[3]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[3]."_r"], $matchresult["score".$evengameno[3]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[3]) { echo $matchpoints[3]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 5<br>Group C</td>
      	<td class="left-team">
		<img src="<?php echo $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"><label for="score9_p"><?php echo $C1; ?></label></td>
        <td align="center"><span>v</span></td>
      	<td class="right-team">
		<img src="<?php echo $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"><label for="score10_p"><?php echo $C2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score9_p'] ?> - <?php echo $userdata['score10_p'] ?></span></td>
      	<td align="center"><?php if($matchids[4]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[4]."_r"], $matchresult["score".$evengameno[4]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[4]) { echo $matchpoints[4]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 6<br>Group D</td>
      	<td class="left-team">
        <img src="<?php echo $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"><label for="score11_p"><?php echo $D1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"><label for="score12_p"><?php echo $D2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score11_p'] ?> - <?php echo $userdata['score12_p'] ?></span></td>
      	<td align="center"><?php if($matchids[5]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[5]."_r"], $matchresult["score".$evengameno[5]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[5]) { echo $matchpoints[5]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 7<br>Group C</td>
      	<td class="left-team">
        <img src="<?php echo $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"><label for="score13_p"><?php echo $C3; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"><label for="score14_p"><?php echo $C4; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score13_p'] ?> - <?php echo $userdata['score14_p'] ?></span></td>
		<td align="center"><?php if($matchids[6]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[6]."_r"], $matchresult["score".$evengameno[6]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[6]) { echo $matchpoints[6]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 8<br>Group D</td>
      	<td class="left-team">
		<img src="<?php echo $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"><label for="score15_p"><?php echo $D3; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
		<img src="<?php echo $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"><label for="score16_p"><?php echo $D4; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score15_p'] ?> - <?php echo $userdata['score16_p'] ?></span></td>
		<td align="center"><?php if($matchids[7]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[7]."_r"], $matchresult["score".$evengameno[7]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[7]) { echo $matchpoints[7]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 9<br>Group E</td>
	    <td class="left-team">
        <img src="<?php echo $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"><label for="score17_p"><?php echo $E3; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"><label for="score18_p"><?php echo $E4; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score17_p'] ?> - <?php echo $userdata['score18_p'] ?></span></td>
		<td align="center"><?php if($matchids[8]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[8]."_r"], $matchresult["score".$evengameno[8]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[8]) { echo $matchpoints[8]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 10<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"><label for="score19_p"><?php echo $F1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"><label for="score20_p"><?php echo $F2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score19_p'] ?> - <?php echo $userdata['score20_p'] ?></span></td>
		<td align="center"><?php if($matchids[9]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[9]."_r"], $matchresult["score".$evengameno[9]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[9]) { echo $matchpoints[9]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 11<br>Group E</td>
      	<td class="left-team">
        <img src="<?php echo $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"><label for="score21_p"><?php echo $E1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"><label for="score22_p"><?php echo $E2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score21_p'] ?> - <?php echo $userdata['score22_p'] ?></span></td>
		<td align="center"><?php if($matchids[10]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[10]."_r"], $matchresult["score".$evengameno[10]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[10]) { echo $matchpoints[10]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 12<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"><label for="score23_p"><?php echo $F3; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"><label for="score24_p"><?php echo $F4; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score23_p'] ?> - <?php echo $userdata['score24_p'] ?></span></td>
		<td align="center"><?php if($matchids[11]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[11]."_r"], $matchresult["score".$evengameno[11]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[11]) { echo $matchpoints[11]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 13<br>Group G</td>
	    <td class="left-team">
        <img src="<?php echo $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"><label for="score25_p"><?php echo $G1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"><label for="score26_p"><?php echo $G2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score25_p'] ?> - <?php echo $userdata['score26_p'] ?></span></td>
		<td align="center"><?php if($matchids[12]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[12]."_r"], $matchresult["score".$evengameno[12]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[12]) { echo $matchpoints[12]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 14<br>Group G</td>
      	<td class="left-team">
        <img src="<?php echo $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"><label for="score27_p"><?php echo $G3; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"><label for="score28_p"><?php echo $G4; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score27_p'] ?> - <?php echo $userdata['score28_p'] ?></span></td>
		<td align="center"><?php if($matchids[13]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[13]."_r"], $matchresult["score".$evengameno[13]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[13]) { echo $matchpoints[13]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 15<br>Group H</td>
      	<td class="left-team">
        <img src="<?php echo $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"><label for="score29_p"><?php echo $H3; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"><label for="score30_p"><?php echo $H4; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score29_p'] ?> - <?php echo $userdata['score30_p'] ?></span></td>
		<td align="center"><?php if($matchids[14]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[14]."_r"], $matchresult["score".$evengameno[14]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[14]) { echo $matchpoints[14]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 16<br>Group H</td>
      	<td class="left-team">
        <img src="<?php echo $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"><label for="score31_p"><?php echo $H1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"><label for="score32_p"><?php echo $H2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score31_p'] ?> - <?php echo $userdata['score32_p'] ?></span></td>
		<td align="center"><?php if($matchids[15]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[15]."_r"], $matchresult["score".$evengameno[15]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[15]) { echo $matchpoints[15]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 17<br>Group A</td>
	    <td class="left-team">
        <img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"><label for="score33_p"><?php echo $A1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"><label for="score34_p"><?php echo $A3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score33_p'] ?> - <?php echo $userdata['score34_p'] ?></span></td>
		<td align="center"><?php if($matchids[16]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[16]."_r"], $matchresult["score".$evengameno[16]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[16]) { echo $matchpoints[16]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 18<br>Group B</td>
      	<td class="left-team">
        <img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"><label for="score35_p"><?php echo $B1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"><label for="score36_p"><?php echo $B3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score35_p'] ?> - <?php echo $userdata['score36_p'] ?></span></td>
		<td align="center"><?php if($matchids[17]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[17]."_r"], $matchresult["score".$evengameno[17]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[17]) { echo $matchpoints[17]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 19<br>Group A</td>
      	<td class="left-team">
        <img src="<?php echo $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"><label for="score37_p"><?php echo $A4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"><label for="score38_p"><?php echo $A2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score37_p'] ?> - <?php echo $userdata['score38_p'] ?></span></td>
		<td align="center"><?php if($matchids[18]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[18]."_r"], $matchresult["score".$evengameno[18]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[18]) { echo $matchpoints[18]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 20<br>Group B</td>
      	<td class="left-team">
        <img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"><label for="score39_p"><?php echo $B4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"><label for="score40_p"><?php echo $B2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score39_p'] ?> - <?php echo $userdata['score40_p'] ?></span></td>
		<td align="center"><?php if($matchids[19]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[19]."_r"], $matchresult["score".$evengameno[19]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[19]) { echo $matchpoints[19]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 21<br>Group C</td>
      	<td class="left-team">
        <img src="<?php echo $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"><label for="score41_p"><?php echo $C4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"><label for="score42_p"><?php echo $C2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score41_p'] ?> - <?php echo $userdata['score42_p'] ?></span></td>
		<td align="center"><?php if($matchids[20]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[20]."_r"], $matchresult["score".$evengameno[20]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[20]) { echo $matchpoints[20]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 22<br>Group C</td>
      	<td class="left-team">
        <img src="<?php echo $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"><label for="score43_p"><?php echo $C1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"><label for="score44_p"><?php echo $C3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score43_p'] ?> - <?php echo $userdata['score44_p'] ?></span></td>
		<td align="center"><?php if($matchids[21]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[21]."_r"], $matchresult["score".$evengameno[21]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[21]) { echo $matchpoints[21]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 23<br>Group D</td>
	    <td class="left-team">
        <img src="<?php echo $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1img; ?>"><label for="score45_p"><?php echo $D1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"><label for="score46_p"><?php echo $D3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score45_p'] ?> - <?php echo $userdata['score46_p'] ?></span></td>
		<td align="center"><?php if($matchids[22]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[22]."_r"], $matchresult["score".$evengameno[22]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[22]) { echo $matchpoints[22]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 24<br>Group E</td>
      	<td class="left-team">
        <img src="<?php echo $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"><label for="score47_p"><?php echo $E1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"><label for="score48_p"><?php echo $E3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score47_p'] ?> - <?php echo $userdata['score48_p'] ?></span></td>
		<td align="center"><?php if($matchids[23]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[23]."_r"], $matchresult["score".$evengameno[23]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[23]) { echo $matchpoints[23]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 25<br>Group D</td>
      	<td class="left-team">
        <img src="<?php echo $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"><label for="score49_p"><?php echo $D4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"><label for="score50_p"><?php echo $D2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score49_p'] ?> - <?php echo $userdata['score50_p'] ?></span></td>
		<td align="center"><?php if($matchids[24]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[24]."_r"], $matchresult["score".$evengameno[24]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[24]) { echo $matchpoints[24]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 26<br>Group E</td>
      	<td class="left-team">
        <img src="<?php echo $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"><label for="score51_p"><?php echo $E4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"><label for="score52_p"><?php echo $E2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score51_p'] ?> - <?php echo $userdata['score52_p'] ?></span></td>
		<td align="center"><?php if($matchids[25]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[25]."_r"], $matchresult["score".$evengameno[25]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[25]) { echo $matchpoints[25]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 27<br>Group G</td>
	    <td class="left-team">
        <img src="<?php echo $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"><label for="score53_p"><?php echo $G1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"><label for="score54_p"><?php echo $G3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score53_p'] ?> - <?php echo $userdata['score54_p'] ?></span></td>
		<td align="center"><?php if($matchids[26]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[26]."_r"], $matchresult["score".$evengameno[26]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[26]) { echo $matchpoints[26]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
		<td class="date-venue">Match 28<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"><label for="score55_p"><?php echo $F4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"><label for="score56_p"><?php echo $F2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score55_p'] ?> - <?php echo $userdata['score56_p'] ?></span></td>
		<td align="center"><?php if($matchids[27]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[27]."_r"], $matchresult["score".$evengameno[27]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[27]) { echo $matchpoints[27]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 29<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"><label for="score57_p"><?php echo $F1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"><label for="score58_p"><?php echo $F3; ?></label></td>
		<td align="center"><span class="prediction"><?php echo $userdata['score57_p'] ?> - <?php echo $userdata['score58_p'] ?></span></td>
		<td align="center"><?php if($matchids[28]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[28]."_r"], $matchresult["score".$evengameno[28]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[28]) { echo $matchpoints[28]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 30<br>Group G</td>
      	<td class="left-team">
        <img src="<?php echo $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"><label for="score59_p"><?php echo $G4; ?></label></td>
        <td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"><label for="score60_p"><?php echo $G2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score59_p'] ?> - <?php echo $userdata['score60_p'] ?></span></td>
		<td align="center"><?php if($matchids[29]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[29]."_r"], $matchresult["score".$evengameno[29]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[29]) { echo $matchpoints[29]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 31<br>Group H</td>
	    <td class="left-team">
        <img src="<?php echo $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"><label for="score61_p"><?php echo $H4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"><label for="score62_p"><?php echo $H2; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score61_p'] ?> - <?php echo $userdata['score62_p'] ?></span></td>
		<td align="center"><?php if($matchids[30]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[30]."_r"], $matchresult["score".$evengameno[30]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[30]) { echo $matchpoints[30]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 32<br>Group H</td>
      	<td class="left-team">
        <img src="<?php echo $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"><label for="score63_p"><?php echo $H1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"><label for="score64_p"><?php echo $H3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score63_p'] ?> - <?php echo $userdata['score64_p'] ?></span></td>
		<td align="center"><?php if($matchids[31]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[31]."_r"], $matchresult["score".$evengameno[31]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[31]) { echo $matchpoints[31]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 33<br>Group A</td>
      	<td class="left-team">
        <img src="<?php echo $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"><label for="score65_p"><?php echo $A4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"><label for="score66_p"><?php echo $A1; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score65_p'] ?> - <?php echo $userdata['score66_p'] ?></span></td>
		<td align="center"><?php if($matchids[32]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[32]."_r"], $matchresult["score".$evengameno[32]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[32]) { echo $matchpoints[32]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 34<br>Group A</td>
      	<td class="left-team">
        <img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"><label for="score67_p"><?php echo $A2; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"><label for="score68_p"><?php echo $A3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score67_p'] ?> - <?php echo $userdata['score68_p'] ?></span></td>
		<td align="center"><?php if($matchids[33]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[33]."_r"], $matchresult["score".$evengameno[33]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[33]) { echo $matchpoints[33]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 35<br>Group B</td>
	    <td class="left-team">
        <img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"><label for="score69_p"><?php echo $B2; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"><label for="score70_p"><?php echo $B3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score69_p'] ?> - <?php echo $userdata['score70_p'] ?></span></td>
		<td align="center"><?php if($matchids[34]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[34]."_r"], $matchresult["score".$evengameno[34]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[34]) { echo $matchpoints[34]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 36<br>Group B</td>
      	<td class="left-team">
        <img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"><label for="score71_p"><?php echo $B4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"><label for="score72_p"><?php echo $B1; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score71_p'] ?> - <?php echo $userdata['score72_p'] ?></span></td>
		<td align="center"><?php if($matchids[35]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[35]."_r"], $matchresult["score".$evengameno[35]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[35]) { echo $matchpoints[35]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 37<br>Group C</td>
      	<td class="left-team">
        <img src="<?php echo $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"><label for="score73_p"><?php echo $C2; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"><label for="score74_p"><?php echo $C3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score73_p'] ?> - <?php echo $userdata['score74_p'] ?></span></td>
		<td align="center"><?php if($matchids[36]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[36]."_r"], $matchresult["score".$evengameno[36]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[36]) { echo $matchpoints[36]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 38<br>Group C</td>
      	<td class="left-team">
        <img src="<?php echo $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"><label for="score75_p"><?php echo $C4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"><label for="score76_p"><?php echo $C1; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score75_p'] ?> - <?php echo $userdata['score76_p'] ?></span></td>
		<td align="center"><?php if($matchids[37]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[37]."_r"], $matchresult["score".$evengameno[37]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[37]) { echo $matchpoints[37]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 39<br>Group D</td>
      	<td class="left-team">
        <img src="<?php echo $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"><label for="score77_p"><?php echo $D4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"><label for="score78_p"><?php echo $D1; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score77_p'] ?> - <?php echo $userdata['score78_p'] ?></span></td>
		<td align="center"><?php if($matchids[38]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[38]."_r"], $matchresult["score".$evengameno[38]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[38]) { echo $matchpoints[38]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 40<br>Group D</td>
      	<td class="left-team">
        <img src="<?php echo $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"><label for="score79_p"><?php echo $D2; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"><label for="score80_p"><?php echo $D3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score79_p'] ?> - <?php echo $userdata['score80_p'] ?></span></td>
		<td align="center"><?php if($matchids[39]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[39]."_r"], $matchresult["score".$evengameno[39]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[39]) { echo $matchpoints[39]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 41<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"><label for="score81_p"><?php echo $F4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"><label for="score82_p"><?php echo $F1; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score81_p'] ?> - <?php echo $userdata['score82_p'] ?></span></td>
		<td align="center"><?php if($matchids[40]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[40]."_r"], $matchresult["score".$evengameno[40]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[40]) { echo $matchpoints[40]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 42<br>Group F</td>
      	<td class="left-team">
        <img src="<?php echo $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"><label for="score83_p"><?php echo $F2; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"><label for="score84_p"><?php echo $F3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score83_p'] ?> - <?php echo $userdata['score84_p'] ?></span></td>
		<td align="center"><?php if($matchids[41]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[41]."_r"], $matchresult["score".$evengameno[41]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[41]) { echo $matchpoints[41]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 43<br>Group E</td>
      	<td class="left-team">
        <img src="<?php echo $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"><label for="score85_p"><?php echo $E4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"><label for="score86_p"><?php echo $E1; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score85_p'] ?> - <?php echo $userdata['score86_p'] ?></span></td>
		<td align="center"><?php if($matchids[42]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[42]."_r"], $matchresult["score".$evengameno[42]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[42]) { echo $matchpoints[42]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 44<br>Group E</td>
      	<td class="left-team">
        <img src="<?php echo $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"><label for="score87_p"><?php echo $E2; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"><label for="score88_p"><?php echo $E3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score87_p'] ?> - <?php echo $userdata['score88_p'] ?></span></td>
		<td align="center"><?php if($matchids[43]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[43]."_r"], $matchresult["score".$evengameno[43]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[43]) { echo $matchpoints[43]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 45<br>Group H</td>
      	<td class="left-team">
        <img src="<?php echo $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4img; ?>"><label for="score89_p"><?php echo $H4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"><label for="score90_p"><?php echo $H1; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score89_p'] ?> - <?php echo $userdata['score90_p'] ?></span></td>
		<td align="center"><?php if($matchids[44]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[44]."_r"], $matchresult["score".$evengameno[44]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[44]) { echo $matchpoints[44]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 46<br>Group H</td>
      	<td class="left-team">
        <img src="<?php echo $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"><label for="score91_p"><?php echo $H2; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"><label for="score92_p"><?php echo $H3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score91_p'] ?> - <?php echo $userdata['score92_p'] ?></span></td>
		<td align="center"><?php if($matchids[45]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[45]."_r"], $matchresult["score".$evengameno[45]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[45]) { echo $matchpoints[45]; } else { echo "-"; } ?></td>
      	</tr>
        <tr>
        <td class="date-venue">Match 47<br>Group G</td>
      	<td class="left-team">
        <img src="<?php echo $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"><label for="score93_p"><?php echo $G2; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"><label for="score94_p"><?php echo $G3; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score93_p'] ?> - <?php echo $userdata['score94_p'] ?></span></td>
		<td align="center"><?php if($matchids[46]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[46]."_r"], $matchresult["score".$evengameno[46]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[46]) { echo $matchpoints[46]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 48<br>Group G</td>
      	<td class="left-team">
        <img src="<?php echo $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"><label for="score95_p"><?php echo $G4; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"><label for="score96_p"><?php echo $G1; ?></label></td>
      	<td align="center"><span class="prediction"><?php echo $userdata['score95_p'] ?> - <?php echo $userdata['score96_p'] ?></span></td>
		<td align="center"><?php if($matchids[47]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[47]."_r"], $matchresult["score".$evengameno[47]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[47]) { echo $matchpoints[47]; } else { echo "-"; } ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 49<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R1img; ?>" alt="<?php echo $R1; ?>" title="<?php echo $R1; ?>"><label for="score97_p"><?php echo $R1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $R2img; ?>" alt="<?php echo $R2; ?>" title="<?php echo $R2; ?>"><label for="score98_p"><?php echo $R2; ?></label></td>
      	<!--<td align="center"><span class="prediction">* - *</span></td>-->
        <td align="center"><span class="prediction"><?php echo $userdata['score97_p'] ?> - <?php echo $userdata['score98_p'] ?></span></td>
		<td align="center"><?php if($matchids[48]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[48]."_r"], $matchresult["score".$evengameno[48]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[48]) { echo $matchpoints[48]; } else { echo "-"; } ?></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 50<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R3img; ?>" alt="<?php echo $R3; ?>" title="<?php echo $R3; ?>"><label for="score99_p"><?php echo $R3; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $R4img; ?>" alt="<?php echo $R4; ?>" title="<?php echo $R4; ?>"><label for="score100_p"><?php echo $R4; ?></label></td>
        <!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score99_p'] ?> - <?php echo $userdata['score100_p'] ?></span></td>
		<td align="center"><?php if($matchids[49]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[49]."_r"], $matchresult["score".$evengameno[49]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[49]) { echo $matchpoints[49]; } else { echo "-"; } ?></td>
      	</tr>
      	<tr>
        <td class="date-venue">Match 51<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R5img; ?>" alt="<?php echo $R5; ?>" title="<?php echo $R5; ?>"><label for="score101_p"><?php echo $R5; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $R6img; ?>" alt="<?php echo $R6; ?>" title="<?php echo $R6; ?>"><label for="score102_p"><?php echo $R6; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score101_p'] ?> - <?php echo $userdata['score102_p'] ?></span></td>
		<td align="center"><?php if($matchids[50]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[50]."_r"], $matchresult["score".$evengameno[50]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[50]) { echo $matchpoints[50]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 52<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R7img; ?>" alt="<?php echo $R7; ?>" title="<?php echo $R7; ?>"><label for="score103_p"><?php echo $R7; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $R8img; ?>" alt="<?php echo $R8; ?>" title="<?php echo $R8; ?>"><label for="score104_p"><?php echo $R8; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score103_p'] ?> - <?php echo $userdata['score104_p'] ?></span></td>
		<td align="center"><?php if($matchids[51]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[51]."_r"], $matchresult["score".$evengameno[51]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[51]) { echo $matchpoints[51]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 53<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R9img; ?>" alt="<?php echo $R9; ?>" title="<?php echo $R9; ?>"><label for="score105_p"><?php echo $R9; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $R10img; ?>" alt="<?php echo $R10; ?>" title="<?php echo $R10; ?>"><label for="score106_p"><?php echo $R10; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score105_p'] ?> - <?php echo $userdata['score106_p'] ?></span></td>
		<td align="center"><?php if($matchids[52]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[52]."_r"], $matchresult["score".$evengameno[52]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[52]) { echo $matchpoints[52]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 54<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R11img; ?>" alt="<?php echo $R11; ?>" title="<?php echo $R11; ?>"><label for="score107_p"><?php echo $R11; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $R12img; ?>" alt="<?php echo $R12; ?>" title="<?php echo $R12; ?>"><label for="score108_p"><?php echo $R12; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score107_p'] ?> - <?php echo $userdata['score108_p'] ?></span></td>
		<td align="center"><?php if($matchids[53]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[53]."_r"], $matchresult["score".$evengameno[53]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[53]) { echo $matchpoints[53]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 55<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R13img; ?>" alt="<?php echo $R13; ?>" title="<?php echo $R13; ?>"><label for="score109_p"><?php echo $R13; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $R14img; ?>" alt="<?php echo $R14; ?>" title="<?php echo $R14; ?>"><label for="score110_p"><?php echo $R14; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score109_p'] ?> - <?php echo $userdata['score110_p'] ?></span></td>
		<td align="center"><?php if($matchids[54]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[54]."_r"], $matchresult["score".$evengameno[54]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[54]) { echo $matchpoints[54]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 56<br>RO16</td>
      	<td class="left-team">
        <img src="<?php echo $R15img; ?>" alt="<?php echo $R15; ?>" title="<?php echo $R15; ?>"><label for="score111_p"><?php echo $R15; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $R16img; ?>" alt="<?php echo $R16; ?>" title="<?php echo $R16; ?>"><label for="score112_p"><?php echo $R16; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score111_p'] ?> - <?php echo $userdata['score112_p'] ?></span></td>
		<td align="center"><?php if($matchids[55]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[55]."_r"], $matchresult["score".$evengameno[55]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[55]) { echo $matchpoints[55]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 57<br>Quarter</td>
      	<td class="left-team">
        <img src="<?php echo $Q1img; ?>" alt="<?php echo $Q1; ?>" title="<?php echo $Q1; ?>"><label for="score113_p"><?php echo $Q1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $Q2img; ?>" alt="<?php echo $Q2; ?>" title="<?php echo $Q2; ?>"><label for="score114_p"><?php echo $Q2; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score113_p'] ?> - <?php echo $userdata['score114_p'] ?></span></td>
		<td align="center"><?php if($matchids[56]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[56]."_r"], $matchresult["score".$evengameno[56]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[56]) { echo $matchpoints[56]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 58<br>Quarter</td>
      	<td class="left-team">
        <img src="<?php echo $Q3img; ?>" alt="<?php echo $Q3; ?>" title="<?php echo $Q3; ?>"><label for="score115_p"><?php echo $Q3; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $Q4img; ?>" alt="<?php echo $Q4; ?>" title="<?php echo $Q4; ?>"><label for="score116_p"><?php echo $Q4; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score115_p'] ?> - <?php echo $userdata['score116_p'] ?></span></td>
		<td align="center"><?php if($matchids[57]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[57]."_r"], $matchresult["score".$evengameno[57]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[57]) { echo $matchpoints[57]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 59<br>Quarter</td>
      	<td class="left-team">
        <img src="<?php echo $Q5img; ?>" alt="<?php echo $Q5; ?>" title="<?php echo $Q5; ?>"><label for="score117_p"><?php echo $Q5; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $Q6img; ?>" alt="<?php echo $Q6; ?>" title="<?php echo $Q6; ?>"><label for="score118_p"><?php echo $Q6; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score117_p'] ?> - <?php echo $userdata['score118_p'] ?></span></td>
		<td align="center"><?php if($matchids[58]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[58]."_r"], $matchresult["score".$evengameno[58]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[58]) { echo $matchpoints[58]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 60<br>Quarter</td>
      	<td class="left-team">
        <img src="<?php echo $Q7img; ?>" alt="<?php echo $Q7; ?>" title="<?php echo $Q7; ?>"><label for="score119_p"><?php echo $Q7; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $Q8img; ?>" alt="<?php echo $Q8; ?>" title="<?php echo $Q8; ?>"><label for="score120_p"><?php echo $Q8; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score119_p'] ?> - <?php echo $userdata['score120_p'] ?></span></td>
		<td align="center"><?php if($matchids[59]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[59]."_r"], $matchresult["score".$evengameno[59]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[59]) { echo $matchpoints[59]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 61<br>Semi</td>
      	<td class="left-team">
        <img src="<?php echo $S1img; ?>" alt="<?php echo $S1; ?>" title="<?php echo $S1; ?>"><label for="score121_p"><?php echo $S1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $S2img; ?>" alt="<?php echo $S2; ?>" title="<?php echo $S2; ?>"><label for="score122_p"><?php echo $S2; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score121_p'] ?> - <?php echo $userdata['score122_p'] ?></span></td>
		<td align="center"><?php if($matchids[60]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[60]."_r"], $matchresult["score".$evengameno[60]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[60]) { echo $matchpoints[60]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 62<br>Semi</td>
      	<td class="left-team">
        <img src="<?php echo $S3img; ?>" alt="<?php echo $S3; ?>" title="<?php echo $S3; ?>"><label for="score123_p"><?php echo $S3; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $S4img; ?>" alt="<?php echo $S4; ?>" title="<?php echo $S4; ?>"><label for="score124_p"><?php echo $S4; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score123_p'] ?> - <?php echo $userdata['score124_p'] ?></span></td>
		<td align="center"><?php if($matchids[61]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[61]."_r"], $matchresult["score".$evengameno[61]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[61]) { echo $matchpoints[61]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 63<br>3rd PO</td>
      	<td class="left-team">
        <img src="<?php echo $P1img; ?>" alt="<?php echo $P1; ?>" title="<?php echo $P1; ?>"><label for="score125_p"><?php echo $P1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $P2img; ?>" alt="<?php echo $P2; ?>" title="<?php echo $P2; ?>"><label for="score126_p"><?php echo $P2; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score125_p'] ?> - <?php echo $userdata['score126_p'] ?></span></td>
		<td align="center"><?php if($matchids[62]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[62]."_r"], $matchresult["score".$evengameno[62]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[62]) { echo $matchpoints[62]; } else { echo "-"; } ?></td>
      	</tr>

        <td class="date-venue">Match 64<br>Final</td>
      	<td class="left-team">
        <img src="<?php echo $Fi1img; ?>" alt="<?php echo $Fi1; ?>" title="<?php echo $Fi1; ?>"><label for="score127_p"><?php echo $Fi1; ?></label></td>
      	<td align="center">v</td>
      	<td class="right-team">
        <img src="<?php echo $Fi2img; ?>" alt="<?php echo $Fi2; ?>" title="<?php echo $Fi2; ?>"><label for="score128_p"><?php echo $Fi2; ?></label></td>
		<!--<td align="center"><span class="prediction">* - *</span></td>-->
      	<td align="center"><span class="prediction"><?php echo $userdata['score127_p'] ?> - <?php echo $userdata['score128_p'] ?></span></td>
		<td align="center"><?php if($matchids[63]) { printf ("<span class='result'>%s - %s</span>", $matchresult["score".$oddgameno[63]."_r"], $matchresult["score".$evengameno[63]."_r"]); } else echo "N / A"; ?></td>
      	<td align="center"><?php if($matchids[63]) { echo $matchpoints[63]; } else { echo "-"; } ?></td>
      	</tr>
      	</table>
       	<a href="rankings.php" class="btn btn-default">Return to rankings table</a>
        <a href="#top" class="btn btn-default">Return to top</a>
        <br><br>
        </div><!--col-xs-12-->
      </div><!--row-->


      <!-- Site footer -->
      <div class="footer">
      <?php include "includes/footer.php" ?>
      </div>

    </div><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
