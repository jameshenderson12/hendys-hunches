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
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <?php include "../php/config.php" ?>
    <?php include "../php/process.php" ?>
    <link rel="shortcut icon" href="../ico/favicon.ico">

    <title>Hendy's Hunches: Administration</title>

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.css" rel="stylesheet">
    <link href="../css/custom.css" rel="stylesheet">
    
      <style>
	  label {
		  font-size: 1em;
	  }	  
	  .left-score {
		  float: left;
		  width: 30px;
	  }
	  .right-score {
		  float: right;
		  width: 30px;		  
	  }
	  .left-team {
		  text-align: left;
	  }
	  .left-team img {
		  float: left;
		  margin-right: 5px;
		  margin-top: 4px;
	  }
	  .table .right-team {
		  text-align: right;
	  }
	  .right-team img {
		  float: right;
		  margin-left: 5px;
		  margin-top: 4px;
	  }	
	.date-venue {
		margin-left: 50px;
		font-size: 9px;
	}
	.dropdown-header {
		font-weight: bold;
		font-style: italic;
	}
    </style>    

    <!-- Custom styles for this template -->
    <!--<link href="../css/starter-template.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

<script type="text/javascript">	
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
          <img src="../img/hh-logo-v8.png" class="img-responsive" style="margin: 5px 0px 0px; height:45px">
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left" style="margin-left: 15px;">
            <li><a href="../dashboard.php">Home</a></li>
            <li><a href="../predictions.php">My Predictions</a></li>
            <li><a href="../rankings.php">Rankings</a></li>
            <li><a href="../howitworks.php">How It Works</a></li>                        
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
          <img src="../img/hh-logo-v8.png" class="img-responsive" style="margin: 5px 0px 0px; height:45px">
        </div>
        <div id="navbar2" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left">
            <li><a href="../dashboard.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>&nbsp;&nbsp;Home</a></li>
            <li><a href="../predictions.php"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>&nbsp;&nbsp;My Predictions</a></li>
            <li><a href="../rankings.php"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>&nbsp;&nbsp;Rankings</a></li>
            <li><a href="../howitworks.php"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp;How It Works</a></li>
            <li><a href="php/logout.php"><span class='glyphicon glyphicon-user' aria-hidden="true"></span>&nbsp;&nbsp;Logout</a></li>                        
          </ul>
        </div>
      </div>
    </nav>
  
        <?php 	  
		
	  	// Connect to the database
	  	include '../php/db-connect.php';
		
	    // Global SQL query strings
		$sql_getresults = "SELECT SUM(score1_r) as score1_r, SUM(score2_r) as score2_r, SUM(score3_r) as score3_r, SUM(score4_r) as score4_r, SUM(score5_r) as score5_r, SUM(score6_r) as score6_r, SUM(score7_r) as score7_r, SUM(score8_r) as score8_r, SUM(score9_r) as score9_r, SUM(score10_r) as score10_r, SUM(score11_r) as score11_r, SUM(score12_r) as score12_r, SUM(score13_r) as score13_r, SUM(score14_r) as score14_r, SUM(score15_r) as score15_r, SUM(score16_r) as score16_r, SUM(score17_r) as score17_r, SUM(score18_r) as score18_r, SUM(score19_r) as score19_r, SUM(score20_r) as score20_r, SUM(score21_r) as score21_r, SUM(score22_r) as score22_r, SUM(score23_r) as score23_r, SUM(score24_r) as score24_r, SUM(score25_r) as score25_r, SUM(score26_r) as score26_r, SUM(score27_r) as score27_r, SUM(score28_r) as score28_r, SUM(score29_r) as score29_r, SUM(score30_r) as score30_r, SUM(score31_r) as score31_r, SUM(score32_r) as score32_r, SUM(score33_r) as score33_r, SUM(score34_r) as score34_r, SUM(score35_r) as score35_r, SUM(score36_r) as score36_r, SUM(score37_r) as score37_r, SUM(score38_r) as score38_r, SUM(score39_r) as score39_r, SUM(score40_r) as score40_r, SUM(score41_r) as score41_r, SUM(score42_r) as score42_r, SUM(score43_r) as score43_r, SUM(score44_r) as score44_r, SUM(score45_r) as score45_r, SUM(score46_r) as score46_r, SUM(score47_r) as score47_r, SUM(score48_r) as score48_r, SUM(score49_r) as score49_r, SUM(score50_r) as score50_r, SUM(score51_r) as score51_r, SUM(score52_r) as score52_r, SUM(score53_r) as score53_r, SUM(score54_r) as score54_r, SUM(score55_r) as score55_r, SUM(score56_r) as score56_r, SUM(score57_r) as score57_r, SUM(score58_r) as score58_r, SUM(score59_r) as score59_r, SUM(score60_r) as score60_r, SUM(score61_r) as score61_r, SUM(score62_r) as score62_r, SUM(score63_r) as score63_r, SUM(score64_r) as score64_r, SUM(score65_r) as score65_r, SUM(score66_r) as score66_r, SUM(score67_r) as score67_r, SUM(score68_r) as score68_r, SUM(score69_r) as score69_r, SUM(score70_r) as score70_r, SUM(score71_r) as score71_r, SUM(score72_r) as score72_r, SUM(score73_r) as score73_r, SUM(score74_r) as score74_r, SUM(score75_r) as score75_r, SUM(score76_r) as score76_r, SUM(score77_r) as score77_r, SUM(score78_r) as score78_r, SUM(score79_r) as score79_r, SUM(score80_r) as score80_r, SUM(score81_r) as score81_r, SUM(score82_r) as score82_r, SUM(score83_r) as score83_r, SUM(score84_r) as score84_r, SUM(score85_r) as score85_r, SUM(score86_r) as score86_r, SUM(score87_r) as score87_r, SUM(score88_r) as score88_r, SUM(score89_r) as score89_r, SUM(score90_r) as score90_r, SUM(score91_r) as score91_r, SUM(score92_r) as score92_r, SUM(score93_r) as score93_r, SUM(score94_r) as score94_r, SUM(score95_r) as score95_r, SUM(score96_r) as score96_r, SUM(score97_r) as score97_r, SUM(score98_r) as score98_r, SUM(score99_r) as score99_r, SUM(score100_r) as score100_r, SUM(score101_r) as score101_r, SUM(score102_r) as score102_r, SUM(score103_r) as score103_r, SUM(score104_r) as score104_r, SUM(score105_r) as score105_r, SUM(score106_r) as score106_r, SUM(score107_r) as score107_r, SUM(score108_r) as score108_r, SUM(score109_r) as score109_r, SUM(score110_r) as score110_r, SUM(score111_r) as score111_r, SUM(score112_r) as score112_r, SUM(score113_r) as score113_r, SUM(score114_r) as score114_r, SUM(score115_r) as score115_r, SUM(score116_r) as score116_r, SUM(score117_r) as score117_r, SUM(score118_r) as score118_r, SUM(score119_r) as score119_r, SUM(score120_r) as score120_r, SUM(score121_r) as score121_r, SUM(score122_r) as score122_r, SUM(score123_r) as score123_r, SUM(score124_r) as score124_r, SUM(score125_r) as score125_r, SUM(score126_r) as score126_r, SUM(score127_r) as score127_r, SUM(score128_r) as score128_r FROM live_match_results";
						   
		$sql_getid = "SELECT match_id FROM live_match_results";
		
		// Create an array of match ids
		$list_of_ids = mysqli_query($con, $sql_getid);
		while($row = mysqli_fetch_array($list_of_ids)) {
			$matchids[] = $row['match_id'];		
		}			
		
		for ($i=0; $i<64; $i++) {
			if ($matchids[$i]) {
				$matchstatus[$i] = 'True';
				// Return existing match values from DB
				// Disable input buttons
			}
			else $matchstatus[$i] = 'False';
		}
		
		$matchresult = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));	  	 
		?> 

		<div id="main" class="col-md-12">
        <h1 class="page-header">Match Results (Admin)</h1>         
        <p class="lead" style="color: red">This page is used to record the match results by administrator only.</p>
      
        <h2>Sequence of Results</h2>
        
        <div class="row">
        <div class="col-xs-12">
        <form id="resultForm" action="../php/insert-result.php" method="POST">
	  	<table class="table table-striped">
        <!--<tr>
        <th width="10%">Info</th>
        <th colspan="5" width="60%"></th>
        <th width="30%">KO &amp; Venue</th>                        
        </tr>-->
      	<tr id="match1">
        <td class="date-venue">Match 1<br>Group A</td>
	    <td class="left-team">
        <img src="<?php echo "../" . $A1img ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"><label for="score1_r"><?php echo $A1; ?></label></td>
      	<td><input type="text" id="score1_r" name="score1_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score1_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score2_r" name="score2_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score2_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"><label for="score2_r"><?php echo $A2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[0]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn1" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>
                
      	<tr>
        <td class="date-venue">Match 2<br>Group A</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"><label for="score3_r"><?php echo $A3; ?></label></td>
      	<td><input type="text" id="score3_r" name="score3_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score3_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score4_r" name="score4_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score4_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"><label for="score4_r"><?php echo $A4; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[1]; ?></td>
		<td class="update-button"><input type="submit" id="updateBtn2" class="btn btn-success btn-sm" value="Update this result" /></td>               
      	</tr>

      	<tr>
        <td class="date-venue">Match 3<br>Group B</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"><label for="score5_r"><?php echo $B3; ?></label></td>
      	<td><input type="text" id="score5_r" name="score5_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score5_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score6_r" name="score6_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score6_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"><label for="score6_r"><?php echo $B4; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[2]; ?></td>    
        <td class="update-button"><input type="submit" id="updateBtn3" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>
       
      	<tr>
        <td class="date-venue">Match 4<br>Group B</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"><label for="score7_r"><?php echo $B1; ?></label></td>
      	<td><input type="text" id="score7_r" name="score7_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score7_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score8_r" name="score8_r" class="right-score score-field form-control input-sm"  onBlur="return validateScore('score8_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"><label for="score8_r"><?php echo $B2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[3]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn4" class="btn btn-success btn-sm" value="Update this result" /></td>       
      	</tr>
                        
      	<tr>        
        <td class="date-venue">Match 5<br>Group C</td>        
	    <td class="left-team">
        <img src="<?php echo "../" . $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"><label for="score9_r"><?php echo $C1; ?></label></td>
      	<td><input type="text" id="score9_r" name="score9_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score9_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score10_r" name="score10_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score10_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"><label for="score10_r"><?php echo $C2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[4]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn5" class="btn btn-success btn-sm" value="Update this result" /></td>        
      	</tr>
      	<tr>
        
        <td class="date-venue">Match 6<br>Group D</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"><label for="score11_r"><?php echo $D1; ?></label></td>
      	<td><input type="text" id="score11_r" name="score11_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score11_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score12_r" name="score12_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score12_r');" /></td>
      	<td class="right-team">
		<img src="<?php echo "../" . $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"><label for="score12_r"><?php echo $D2; ?></label></td>        <td class="date-venue">Match Recorded: <?php echo $matchstatus[5]; ?></td>
		<td class="update-button"><input type="submit" id="updateBtn6" class="btn btn-success btn-sm" value="Update this result" /></td>              
      	</tr>

      	<tr>
        <td class="date-venue">Match 7<br>Group C</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"><label for="score13_r"><?php echo $C3; ?></label></td>       
      	<td><input type="text" id="score13_r" name="score13_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score13_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score14_r" name="score14_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score14_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"><label for="score14_r"><?php echo $C4; ?></label></td>       
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[6]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn7" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
      	<tr>
        
        <td class="date-venue">Match 8<br>Group D</td>                
      	<td class="left-team">
        <img src="<?php echo "../" . $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"><label for="score15_r"><?php echo $D3; ?></label></td>
      	<td><input type="text" id="score15_r" name="score15_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score15_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score16_r" name="score16_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score16_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"><label for="score16_r"><?php echo $D4; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[7]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn8" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>
        
      	<tr>     
        <td class="date-venue">Match 9<br>Group E</td>           
	    <td class="left-team">
        <img src="<?php echo "../" . $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"><label for="score17_r"><?php echo $E3; ?></label></td>
      	<td><input type="text" id="score17_r" name="score17_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score17_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score18_r" name="score18_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score18_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"><label for="score18_r"><?php echo $E4; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[8]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn9" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 10<br>Group F</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"><label for="score19_r"><?php echo $F1; ?></label></td>
      	<td><input type="text" id="score19_r" name="score19_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score19_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score20_r" name="score20_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score20_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"><label for="score20_r"><?php echo $F2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[9]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn10" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
        <tr>
        <td class="date-venue">Match 11<br>Group E</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"><label for="score21_r"><?php echo $E1; ?></label></td>
      	<td><input type="text" id="score21_r" name="score21_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score21_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score22_r" name="score22_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score22_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"><label for="score22_r"><?php echo $E2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[10]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn11" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 12<br>Group F</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"><label for="score23_r"><?php echo $F3; ?></label></td>
      	<td><input type="text" id="score23_r" name="score23_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score23_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score24_r" name="score24_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score24_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"><label for="score24_r"><?php echo $F4; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[11]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn12" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>
      	<tr>
          
        <td class="date-venue">Match 13<br>Group G</td>        
	    <td class="left-team">
        <img src="<?php echo "../" . $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"><label for="score25_r"><?php echo $G1; ?></label></td>
      	<td><input type="text" id="score25_r" name="score25_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score25_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score26_r" name="score26_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score26_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"><label for="score26_r"><?php echo $G2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[12]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn13" class="btn btn-success btn-sm" value="Update this result" /></td>        
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 14<br>Group G</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"><label for="score27_r"><?php echo $G3; ?></label></td>
      	<td><input type="text" id="score27_r" name="score27_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score27_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score28_r" name="score28_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score28_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"><label for="score28_r"><?php echo $G4; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[13]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn14" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>
        
        <tr>
        <td class="date-venue">Match 15<br>Group H</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"><label for="score29_r"><?php echo $H3; ?></label></td>
      	<td><input type="text" id="score29_r" name="score29_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score29_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score30_r" name="score30_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score30_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"><label for="score30_r"><?php echo $H4; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[14]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn15" class="btn btn-success btn-sm" value="Update this result" /></td>
        </tr>
      	
        <tr>
        <td class="date-venue">Match 16<br>Group H</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"><label for="score31_r"><?php echo $H1; ?></label></td>
      	<td><input type="text" id="score31_r" name="score31_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score31_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score32_r" name="score32_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score32_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"><label for="score32_r"><?php echo $H2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[15]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn16" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>        
        <td class="date-venue">Match 17<br>Group A</td>        
	    <td class="left-team">
        <img src="<?php echo "../" . $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"><label for="score33_r"><?php echo $A1; ?></label></td>
      	<td><input type="text" id="score33_r" name="score33_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score33_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score34_r" name="score34_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score34_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"><label for="score34_r"><?php echo $A3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[16]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn17" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 18<br>Group B</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"><label for="score35_r"><?php echo $B1; ?></label></td>
      	<td><input type="text" id="score35_r" name="score35_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score35_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score36_r" name="score36_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score36_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"><label for="score36_r"><?php echo $B3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[17]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn18" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 19<br>Group A</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"><label for="score37_r"><?php echo $A4; ?></label></td>
      	<td><input type="text" id="score37_r" name="score37_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score37_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score38_r" name="score38_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score38_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"><label for="score38_r"><?php echo $A2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[18]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn19" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 20<br>Group B</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"><label for="score39_r"><?php echo $B4; ?></label></td>
      	<td><input type="text" id="score39_r" name="score39_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score39_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score40_r" name="score40_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score40_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"><label for="score40_r"><?php echo $B2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[19]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn20" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 21<br>Group C</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"><label for="score41_r"><?php echo $C4; ?></label></td>
      	<td><input type="text" id="score41_r" name="score41_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score41_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score42_r" name="score42_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score42_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"><label for="score42_r"><?php echo $C2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[20]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn21" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 22<br>Group C</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"><label for="score43_r"><?php echo $C1; ?></label></td>
      	<td><input type="text" id="score43_r" name="score43_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score43_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score44_r" name="score44_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score44_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"><label for="score44_r"><?php echo $C3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[21]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn22" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>        
        <td class="date-venue">Match 23<br>Group D</td>        
	    <td class="left-team">
        <img src="<?php echo "../" . $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"><label for="score45_r"><?php echo $D1; ?></label></td>
      	<td><input type="text" id="score45_r" name="score45_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score45_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score46_r" name="score46_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score46_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"><label for="score46_r"><?php echo $D3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[22]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn23" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 24<br>Group E</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"><label for="score47_r"><?php echo $E1; ?></label></td>
      	<td><input type="text" id="score47_r" name="score47_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score47_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score48_r" name="score48_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score48_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"><label for="score48_r"><?php echo $E3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[23]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn24" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 25<br>Group D</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"><label for="score49_r"><?php echo $D4; ?></label></td>
      	<td><input type="text" id="score49_r" name="score49_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score49_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score50_r" name="score50_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score50_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"><label for="score50_r"><?php echo $D2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[24]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn25" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>        
        <td class="date-venue">Match 26<br>Group E</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"><label for="score51_r"><?php echo $E4; ?></label></td>
      	<td><input type="text" id="score51_r" name="score51_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score51_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score52_r" name="score52_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score52_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"><label for="score52_r"><?php echo $E2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[25]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn26" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>        
        <td class="date-venue">Match 27<br>Group G</td>        
	    <td class="left-team">
        <img src="<?php echo "../" . $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"><label for="score53_r"><?php echo $G1; ?></label></td>
      	<td><input type="text" id="score53_r" name="score53_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score53_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score54_r" name="score54_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score54_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"><label for="score54_r"><?php echo $G3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[26]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn27" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 28<br>Group F</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"><label for="score55_r"><?php echo $F4; ?></label></td>
      	<td><input type="text" id="score55_r" name="score55_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score55_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score56_r" name="score56_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score56_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"><label for="score56_r"><?php echo $F2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[27]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn28" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 29<br>Group F</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"><label for="score57_r"><?php echo $F1; ?></label></td>
      	<td><input type="text" id="score57_r" name="score57_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score57_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score58_r" name="score58_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score58_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"><label for="score58_r"><?php echo $F3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[28]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn29" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 30<br>Group G</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"><label for="score59_r"><?php echo $G4; ?></label></td>
      	<td><input type="text" id="score59_r" name="score59_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score59_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score60_r" name="score60_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score60_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"><label for="score60_r"><?php echo $G2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[29]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn30" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 31<br>Group H</td>        
	    <td class="left-team">
        <img src="<?php echo "../" . $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"><label for="score61_r"><?php echo $H4; ?></label></td>
      	<td><input type="text" id="score61_r" name="score61_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score61_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score62_r" name="score62_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score62_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"><label for="score62_r"><?php echo $H2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[30]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn31" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 32<br>Group H</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"><label for="score63_r"><?php echo $H1; ?></label></td>
      	<td><input type="text" id="score63_r" name="score63_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score63_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score64_r" name="score64_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score64_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"><label for="score64_r"><?php echo $H3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[31]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn32" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

        <tr>        
        <td class="date-venue">Match 33<br>Group A</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"><label for="score65_r"><?php echo $A4; ?></label></td>
      	<td><input type="text" id="score65_r" name="score65_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score65_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score66_r" name="score66_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score66_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"><label for="score66_r"><?php echo $A1; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[32]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn33" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 34<br>Group A</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"><label for="score67_r"><?php echo $A2; ?></label></td>
      	<td><input type="text" id="score67_r" name="score67_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score67_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score68_r" name="score68_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score68_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"><label for="score68_r"><?php echo $A3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[33]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn34" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 35<br>Group B</td>        
	    <td class="left-team">
        <img src="<?php echo "../" . $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"><label for="score69_r"><?php echo $B2; ?></label></td>
      	<td><input type="text" id="score69_r" name="score69_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score69_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score70_r" name="score70_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score70_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"><label for="score70_r"><?php echo $B3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[34]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn35" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

      	<tr>
        <td class="date-venue">Match 36<br>Group B</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"><label for="score71_r"><?php echo $B4; ?></label></td>
      	<td><input type="text" id="score71_r" name="score71_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score71_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score72_r" name="score72_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score72_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"><label for="score72_r"><?php echo $B1; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[35]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn36" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 37<br>Group C</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $C2img; ?>" alt="<?php echo $C2; ?>" title="<?php echo $C2; ?>"><label for="score73_r"><?php echo $C2; ?></label></td>
      	<td><input type="text" id="score73_r" name="score73_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score73_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score74_r" name="score74_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score74_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $C3img; ?>" alt="<?php echo $C3; ?>" title="<?php echo $C3; ?>"><label for="score74_r"><?php echo $C3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[36]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn37" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

       	<tr>
        <td class="date-venue">Match 38<br>Group C</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $C4img; ?>" alt="<?php echo $C4; ?>" title="<?php echo $C4; ?>"><label for="score75_r"><?php echo $C4; ?></label></td>
      	<td><input type="text" id="score75_r" name="score75_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score75_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score76_r" name="score76_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score76_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $C1img; ?>" alt="<?php echo $C1; ?>" title="<?php echo $C1; ?>"><label for="score76_r"><?php echo $C1; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[37]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn38" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 39<br>Group D</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $D4img; ?>" alt="<?php echo $D4; ?>" title="<?php echo $D4; ?>"><label for="score77_r"><?php echo $D4; ?></label></td>
      	<td><input type="text" id="score77_r" name="score77_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score77_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score78_r" name="score78_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score78_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $D1img; ?>" alt="<?php echo $D1; ?>" title="<?php echo $D1; ?>"><label for="score78_r"><?php echo $D1; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[38]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn39" class="btn btn-success btn-sm" value="Update this result" /></td>   
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 40<br>Group D</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $D2img; ?>" alt="<?php echo $D2; ?>" title="<?php echo $D2; ?>"><label for="score79_r"><?php echo $D2; ?></label></td>
      	<td><input type="text" id="score79_r" name="score79_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score79_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score80_r" name="score80_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score80_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $D3img; ?>" alt="<?php echo $D3; ?>" title="<?php echo $D3; ?>"><label for="score80_r"><?php echo $D3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[39]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn40" class="btn btn-success btn-sm" value="Update this result" /></td>
      	</tr>

        <tr>
        <td class="date-venue">Match 41<br>Group F</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $F4img; ?>" alt="<?php echo $F4; ?>" title="<?php echo $F4; ?>"><label for="score81_r"><?php echo $F4; ?></label></td>
      	<td><input type="text" id="score81_r" name="score81_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score81_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score82_r" name="score82_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score82_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $F1img; ?>" alt="<?php echo $F1; ?>" title="<?php echo $F1; ?>"><label for="score82_r"><?php echo $F1; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[40]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn41" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 42<br>Group F</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $F2img; ?>" alt="<?php echo $F2; ?>" title="<?php echo $F2; ?>"><label for="score83_r"><?php echo $F2; ?></label></td>
      	<td><input type="text" id="score83_r" name="score83_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score83_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score84_r" name="score84_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score84_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $F3img; ?>" alt="<?php echo $F3; ?>" title="<?php echo $F3; ?>"><label for="score84_r"><?php echo $F3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[41]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn42" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>

        <tr>
        <td class="date-venue">Match 43<br>Group E</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $E4img; ?>" alt="<?php echo $E4; ?>" title="<?php echo $E4; ?>"><label for="score85_r"><?php echo $E4; ?></label></td>
      	<td><input type="text" id="score85_r" name="score85_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score85_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score86_r" name="score86_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score86_r');" /></td>
      	<td class="right-team"><img src="<?php echo "../" . $E1img; ?>" alt="<?php echo $E1; ?>" title="<?php echo $E1; ?>"><label for="score86_r"><?php echo $E1; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[42]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn43" class="btn btn-success btn-sm" value="Update this result" /></td>          
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 44<br>Group E</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $E2img; ?>" alt="<?php echo $E2; ?>" title="<?php echo $E2; ?>"><label for="score87_r"><?php echo $E2; ?></label></td>
      	<td><input type="text" id="score87_r" name="score87_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score87_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score88_r" name="score88_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score88_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $E3img; ?>" alt="<?php echo $E3; ?>" title="<?php echo $E3; ?>"><label for="score88_r"><?php echo $E3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[43]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn44" class="btn btn-success btn-sm" value="Update this result" /></td>      
       	</tr>
        
        <tr>
        <td class="date-venue">Match 45<br>Group H</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $H4img; ?>" alt="<?php echo $H4; ?>" title="<?php echo $H4; ?>"><label for="score89_r"><?php echo $H4; ?></label></td>
      	<td><input type="text" id="score89_r" name="score89_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score89_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score90_r" name="score90_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score90_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $H1img; ?>" alt="<?php echo $H1; ?>" title="<?php echo $H1; ?>"><label for="score90_r"><?php echo $H1; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[44]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn45" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>

      	<tr>
        <td class="date-venue">Match 46<br>Group H</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $H2img; ?>" alt="<?php echo $H2; ?>" title="<?php echo $H2; ?>"><label for="score91_r"><?php echo $H2; ?></label></td>
      	<td><input type="text" id="score91_r" name="score91_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score91_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score92_r" name="score92_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score92_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $H3img; ?>" alt="<?php echo $H3; ?>" title="<?php echo $H3; ?>"><label for="score92_r"><?php echo $H3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[45]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn46" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>                        

        <tr>
        <td class="date-venue">Match 47<br>Group G</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $G2img; ?>" alt="<?php echo $G2; ?>" title="<?php echo $G2; ?>"><label for="score93_r"><?php echo $G2; ?></label></td>
      	<td><input type="text" id="score93_r" name="score93_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score93_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score94_r" name="score94_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score94_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $G3img; ?>" alt="<?php echo $G3; ?>" title="<?php echo $G3; ?>"><label for="score94_r"><?php echo $G3; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[46]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn47" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>

      	<tr>
        <td class="date-venue">Match 48<br>Group G</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $G4img; ?>" alt="<?php echo $G4; ?>" title="<?php echo $G4; ?>"><label for="score95_r"><?php echo $G4; ?></label></td>
      	<td><input type="text" id="score95_r" name="score95_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score95_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score96_r" name="score96_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score96_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $G1img; ?>" alt="<?php echo $G1; ?>" title="<?php echo $G1; ?>"><label for="score96_r"><?php echo $G1; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[47]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn48" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
		<!-- RO16------------------------>
        <!--===================================-->      
      	<tr>
        <td class="date-venue">Match 49<br>RO16</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $R1img; ?>" alt="<?php echo $R1; ?>" title="<?php echo $R1; ?>"><label for="score97_r"><?php echo $R1; ?></label></td>
      	<td><input type="text" id="score97_r" name="score97_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score97_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score98_r" name="score98_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score98_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $R2img; ?>" alt="<?php echo $R2; ?>" title="<?php echo $R2; ?>"><label for="score98_r"><?php echo $R2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[48]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn49" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>         
        
      	<tr>
        <td class="date-venue">Match 50<br>RO16</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $R3img; ?>" alt="<?php echo $R3; ?>" title="<?php echo $R3; ?>"><label for="score99_r"><?php echo $R3; ?></label></td>
      	<td><input type="text" id="score99_r" name="score99_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score99_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score100_r" name="score100_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score100_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $R4img; ?>" alt="<?php echo $R4; ?>" title="<?php echo $R4; ?>"><label for="score100_r"><?php echo $R4; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[49]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn50" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 51<br>RO16</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $R5img; ?>" alt="<?php echo $R5; ?>" title="<?php echo $R5; ?>"><label for="score101_r"><?php echo $R5; ?></label></td>
      	<td><input type="text" id="score101_r" name="score101_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score101_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score102_r" name="score102_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score102_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $R6img; ?>" alt="<?php echo $R6; ?>" title="<?php echo $R6; ?>"><label for="score102_r"><?php echo $R6; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[50]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn51" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 52<br>RO16</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $R7img; ?>" alt="<?php echo $R7; ?>" title="<?php echo $R7; ?>"><label for="score103_r"><?php echo $R7; ?></label></td>
      	<td><input type="text" id="score103_r" name="score103_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score103_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score104_r" name="score104_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score104_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $R8img; ?>" alt="<?php echo $R8; ?>" title="<?php echo $R8; ?>"><label for="score104_r"><?php echo $R8; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[51]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn52" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 53<br>RO16</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $R9img; ?>" alt="<?php echo $R9; ?>" title="<?php echo $R9; ?>"><label for="score105_r"><?php echo $R9; ?></label></td>
      	<td><input type="text" id="score105_r" name="score105_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score105_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score106_r" name="score106_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score106_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $R10img; ?>" alt="<?php echo $R10; ?>" title="<?php echo $R10; ?>"><label for="score106_r"><?php echo $R10; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[52]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn53" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 54<br>RO16</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $R11img; ?>" alt="<?php echo $R11; ?>" title="<?php echo $R11; ?>"><label for="score107_r"><?php echo $R11; ?></label></td>
      	<td><input type="text" id="score107_r" name="score107_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score107_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score108_r" name="score108_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score108_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $R12img; ?>" alt="<?php echo $R12; ?>" title="<?php echo $R12; ?>"><label for="score108_r"><?php echo $R12; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[53]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn54" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 55<br>RO16</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $R13img; ?>" alt="<?php echo $R13; ?>" title="<?php echo $R13; ?>"><label for="score109_r"><?php echo $R13; ?></label></td>
      	<td><input type="text" id="score109_r" name="score109_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score109_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score110_r" name="score110_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score110_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $R14img; ?>" alt="<?php echo $R14; ?>" title="<?php echo $R14; ?>"><label for="score110_r"><?php echo $R14; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[54]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn55" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 56<br>RO16</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $R15img; ?>" alt="<?php echo $R15; ?>" title="<?php echo $R15; ?>"><label for="score111_r"><?php echo $R15; ?></label></td>
      	<td><input type="text" id="score111_r" name="score111_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score111_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score112_r" name="score112_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score112_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $R16img; ?>" alt="<?php echo $R16; ?>" title="<?php echo $R16; ?>"><label for="score112_r"><?php echo $R16; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[55]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn56" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
			
      	<tr>
        <td class="date-venue">Match 57<br>Quarter</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $Q1img; ?>" alt="<?php echo $Q1img; ?>" title="<?php echo $Q1; ?>"><label for="score113_r"><?php echo $Q1; ?></label></td>
      	<td><input type="text" id="score113_r" name="score113_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score113_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score114_r" name="score114_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score114_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $Q2img; ?>" alt="<?php echo $Q2; ?>" title="<?php echo $Q2; ?>"><label for="score114_r"><?php echo $Q2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[56]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn57" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
			
      	<tr>
        <td class="date-venue">Match 58<br>Quarter</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $Q3img; ?>" alt="<?php echo $Q3; ?>" title="<?php echo $Q3; ?>"><label for="score115_r"><?php echo $Q3; ?></label></td>
      	<td><input type="text" id="score115_r" name="score115_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score115_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score116_r" name="score116_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score116_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $Q4img; ?>" alt="<?php echo $Q4; ?>" title="<?php echo $Q4; ?>"><label for="score116_r"><?php echo $Q4; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[57]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn58" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
			
      	<tr>
        <td class="date-venue">Match 59<br>Quarter</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $Q5img; ?>" alt="<?php echo $Q5; ?>" title="<?php echo $Q5; ?>"><label for="score117_r"><?php echo $Q5; ?></label></td>
      	<td><input type="text" id="score117_r" name="score117_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score117_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score118_r" name="score118_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score118_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $Q6img; ?>" alt="<?php echo $Q6; ?>" title="<?php echo $Q6; ?>"><label for="score118_r"><?php echo $Q6; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[58]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn59" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
			
      	<tr>
        <td class="date-venue">Match 60<br>Quarter</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $Q7img; ?>" alt="<?php echo $Q7; ?>" title="<?php echo $Q7; ?>"><label for="score119_r"><?php echo $Q7; ?></label></td>
      	<td><input type="text" id="score119_r" name="score119_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score119_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score120_r" name="score120_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score120_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $Q8img; ?>" alt="<?php echo $Q8; ?>" title="<?php echo $Q8; ?>"><label for="score120_r"><?php echo $Q8; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[59]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn60" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>

      	<tr>
        <td class="date-venue">Match 61<br>Semi</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $S1img; ?>" alt="<?php echo $S1; ?>" title="<?php echo $S1; ?>"><label for="score121_r"><?php echo $S1; ?></label></td>
      	<td><input type="text" id="score121_r" name="score121_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score121_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score122_r" name="score122_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score122_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $S2img; ?>" alt="<?php echo $S2; ?>" title="<?php echo $S2; ?>"><label for="score122_r"><?php echo $S2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[60]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn61" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 62<br>Semi</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $S3img; ?>" alt="<?php echo $S3; ?>" title="<?php echo $S3; ?>"><label for="score123_r"><?php echo $S3; ?></label></td>
      	<td><input type="text" id="score123_r" name="score123_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score123_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score124_r" name="score124_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score124_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $S4img; ?>" alt="<?php echo $S4; ?>" title="<?php echo $S4; ?>"><label for="score124_r"><?php echo $S4; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[61]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn62" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 63<br>3rd PO</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $P1img; ?>" alt="<?php echo $P1; ?>" title="<?php echo $P1; ?>"><label for="score125_r"><?php echo $P1; ?></label></td>
      	<td><input type="text" id="score125_r" name="score125_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score125_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score126_r" name="score126_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score126_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $P2img; ?>" alt="<?php echo $P2; ?>" title="<?php echo $P2; ?>"><label for="score126_r"><?php echo $P2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[62]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn63" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>
        
      	<tr>
        <td class="date-venue">Match 64<br>Final</td>        
      	<td class="left-team">
        <img src="<?php echo "../" . $Fi1img; ?>" alt="<?php echo $Fi1; ?>" title="<?php echo $Fi1; ?>"><label for="score127_r"><?php echo $Fi1; ?></label></td>
      	<td><input type="text" id="score127_r" name="score127_r" class="left-score score-field form-control input-sm" onBlur="return validateScore('score127_r');" /></td>
      	<td align="center">v</td>
      	<td><input type="text" id="score128_r" name="score128_r" class="right-score score-field form-control input-sm" onBlur="return validateScore('score128_r');" /></td>
      	<td class="right-team">
        <img src="<?php echo "../" . $Fi2img; ?>" alt="<?php echo $Fi2; ?>" title="<?php echo $Fi2; ?>"><label for="score128_r"><?php echo $Fi2; ?></label></td>
      	<td class="date-venue">Match Recorded: <?php echo $matchstatus[63]; ?></td>
        <td class="update-button"><input type="submit" id="updateBtn64" class="btn btn-success btn-sm" value="Update this result" /></td>      
      	</tr>                                			
      	</table>                           
      	<input type="submit" class="btn btn-default" value="Submit Results" />     
      	<input type="reset" class="btn btn-default" value="Reset All" />
        </form>
        </div><!--col-md-12-->       
      </div><!--row-->      
             
      </div><!--starter-template-->
     
      <!-- Site footer -->
      <div class="footer">
      <?php include "../includes/footer.php" ?>
      </div>       
     
    </div><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
  </body>
</html>