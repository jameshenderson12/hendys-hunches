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
	}
	</style>
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
		                <img src="img/scores.jpg" alt="Profile icon" class="img-fluid rounded-circle mx-1" width="25px;">
										<?php
											// Echo session variables that were set on previous page
											echo $_SESSION["firstname"];
										?>
		              </a>
		              <ul class="dropdown-menu">
		                <li><a class="dropdown-item" href="change-password.php">Change Password</a></li>
		                <li><a class="dropdown-item" href="#">Another action</a></li>
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
      <!--<p class="lead">Can you correctly predict your way to victory?</p>-->
      <p>To make your predictions, enter a score value into each box below. Remember to hit the 'Update my predictions' button to submit.</p>
      <a name="matches"></a><!--anchor point for filters-->
      <form id="predictionForm" name="predictionForm" class="form-horizontal" action="submit.php" method="POST">
      <div class="row">
        <div class="col-xs-12">

        <div class="btn-group" role="group" aria-label="Fixture Filters">
          <button id="all" type="button" class="btn btn-default" onClick="showOnly('All')">All Matches</button>
          <!--
          <div class="btn-group" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Group
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              <li id="groupA"><a href="#matches" onClick="showOnly('A')">Group A</a></li>
              <li id="groupB"><a href="#matches" onClick="showOnly('B')">Group B</a></li>
              <li id="groupC"><a href="#matches" onClick="showOnly('C')">Group C</a></li>
              <li id="groupD"><a href="#matches" onClick="showOnly('D')">Group D</a></li>
              <li id="groupE"><a href="#matches" onClick="showOnly('E')">Group E</a></li>
              <li id="groupF"><a href="#matches" onClick="showOnly('F')">Group F</a></li>
            </ul>
          </div>-->

          <div class="btn-group" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Knockout Stage
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              <li id="groups"><a href="#matches" onClick="showOnly('Groups')">All Groups</a></li>
              <li id="ro16"><a href="#matches" onClick="showOnly('RO16')">Round of 16</a></li>
              <li id="qf"><a href="#matches" onClick="showOnly('QF')">Quarter Finals</a></li>
              <li id="sf"><a href="#matches" onClick="showOnly('SF')">Semi Finals</a></li>
			  <li id="po"><a href="#matches" onClick="showOnly('PO')">3rd Place Playoff</a></li>
              <li id="final"><a href="#matches" onClick="showOnly('Final')">Final</a></li>
            </ul>
          </div>
          <!--
          <div class="btn-group hidden-xs" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Date
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              <li><a href="#matches" onClick="showOnly('10Jun')">10th June</a></li>
              <li><a href="#matches" onClick="showOnly('11Jun')">11th June</a></li>
              <li><a href="#matches" onClick="showOnly('12Jun')">12th June</a></li>
              <li><a href="#matches" onClick="showOnly('13Jun')">13th June</a></li>
              <li><a href="#matches" onClick="showOnly('14Jun')">14th June</a></li>
              <li><a href="#matches" onClick="showOnly('15Jun')">15th June</a></li>
              <li><a href="#matches" onClick="showOnly('16Jun')">16th June</a></li>
              <li><a href="#matches" onClick="showOnly('17Jun')">17th June</a></li>
              <li><a href="#matches" onClick="showOnly('18Jun')">18th June</a></li>
              <li><a href="#matches" onClick="showOnly('19Jun')">19th June</a></li>
              <li><a href="#matches" onClick="showOnly('20Jun')">20th June</a></li>
              <li><a href="#matches" onClick="showOnly('21Jun')">21st June</a></li>
              <li><a href="#matches" onClick="showOnly('22Jun')">22nd June</a></li>
              <li><a href="#matches" onClick="showOnly('25Jun')">25th June</a></li>
              <li><a href="#matches" onClick="showOnly('26Jun')">26th June</a></li>
              <li><a href="#matches" onClick="showOnly('27Jun')">27th June</a></li>
              <li><a href="#matches" onClick="showOnly('30Jun')">30th June</a></li>
              <li><a href="#matches" onClick="showOnly('01Jul')">1st July</a></li>
              <li><a href="#matches" onClick="showOnly('02Jul')">2nd July</a></li>
              <li><a href="#matches" onClick="showOnly('03Jul')">3rd July</a></li>
              <li><a href="#matches" onClick="showOnly('06Jul')">6th July</a></li>
              <li><a href="#matches" onClick="showOnly('07Jul')">7th July</a></li>
              <li><a href="#matches" onClick="showOnly('10Jul')">10th July</a></li>
            </ul>
          </div>-->

		</div>



		<section>

        <!-- TABLE CONSTRUCTION-->
        <table id="table" class="table table-sm">
            <!-- HEADING FORMATION -->
            <tr>
							<!--
                <th>Match No.</th>
                <th>Home Team</th>
                <th>Away Team</th>
                <th>Date/Location</th>
							-->
            </tr>

            <script>
                $(document).ready(function () {

                    // FETCHING DATA FROM JSON FILE
                    $.getJSON("json/fifa-world-cup-2022-fixtures.json",
                    	function (data) {
                        var fixture = '';

                        // ITERATING THROUGH OBJECTS
                        $.each(data, function (key, value) {
                            //CONSTRUCTION OF ROWS HAVING
                            // DATA FROM JSON OBJECT
                            fixture += '<tr>';
														fixture += '<td class="small text-muted">' + value.Group + '</td>';
                            fixture += '<td>' + value.HomeTeam + '</td>';
														fixture += '<td><img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?></td>';
														fixture += '<td><input type="text" id="score1_p" name="score1_p" class="form-control" /></td>';
														fixture += '<td>v<br><span class="badge bg-light text-primary">' + value.MatchNumber + '</span></td>';
														fixture += '<td><input type="text" id="score2_p" name="score2_p" class="form-control" /></td>';
														fixture += '<td><img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"></td>';
                            fixture += '<td>' + value.AwayTeam + '</td>';
                            fixture += '<td class="small text-muted"> ' + value.DateUtc + '<br>' + value.Location + '</td>';
                            fixture += '</tr>';
                        });
                        //INSERTING ROWS INTO TABLE
                        $('#table').append(fixture);
                    });
                });
            </script>
    </section>

<!--
	  	<table class="table table-sm">

				<tr id="match1">
	        <td class="small text-muted">Grp A</td>
		    	<td><?php echo $A1; ?></td>
	        <td><img src="<?php echo $A1img; ?>" alt="<?php echo $A1; ?>" title="<?php echo $A1; ?>"></td>
	      	<td><input type="text" id="score1_p" name="score1_p" class="form-control" /></td>
	      	<td align="center">v<br><span class="badge bg-light text-primary">01</span></td>
	      	<td><input type="text" id="score2_p" name="score2_p" class="form-control" /></td>
					<td><img src="<?php echo $A2img; ?>" alt="<?php echo $A2; ?>" title="<?php echo $A2; ?>"></td>
	      	<td><?php echo $A2; ?></td>
	      	<td class="date-venue small text-muted"><?php echo "$_16, $_20Nov, $year <br> $venue1"; ?></td>
      	</tr>
				<tr id="match2">
	        <td class="small text-muted">Grp A</td>
		    	<td><?php echo $A3; ?></td>
	        <td><img src="<?php echo $A3img; ?>" alt="<?php echo $A3; ?>" title="<?php echo $A3; ?>"></td>
	      	<td><input type="text" id="score3_p" name="score3_p" class="form-control" /></td>
	      	<td align="center">v<br><span class="badge bg-light text-primary">02</span></td>
	      	<td><input type="text" id="score4_p" name="score4_p" class="form-control" /></td>
					<td><img src="<?php echo $A4img; ?>" alt="<?php echo $A4; ?>" title="<?php echo $A4; ?>"></td>
	      	<td><?php echo $A4; ?></td>
	      	<td class="date-venue small text-muted"><?php echo "$_16, $_21Nov, $year <br> $venue3"; ?></td>
      	</tr>
				<tr id="match3">
					<td class="small text-muted">Grp B</td>
		    	<td><?php echo $B1; ?></td>
	        <td><img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"></td>
	      	<td><input type="text" id="score5_p" name="score5_p" class="form-control" /></td>
	      	<td align="center">v<br><span class="badge bg-light text-primary">03</span></td>
	      	<td><input type="text" id="score6_p" name="score6_p" class="form-control" /></td>
					<td><img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"></td>
	      	<td><?php echo $B2; ?></td>
	      	<td class="date-venue small text-muted"><?php echo "$_13, $_21Nov, $year <br> $venue2"; ?></td>
      	</tr>
				<tr id="match4">
					<td class="small text-muted">Grp B</td>
		    	<td><?php echo $B3; ?></td>
	        <td><img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"></td>
	      	<td><input type="text" id="score7_p" name="score7_p" class="form-control" /></td>
	      	<td align="center">v<br><span class="badge bg-light text-primary">03</span></td>
	      	<td><input type="text" id="score8_p" name="score8_p" class="form-control" /></td>
					<td><img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"></td>
	      	<td><?php echo $B4; ?></td>
	      	<td class="date-venue small text-muted"><?php echo "$_19, $_21Nov, $year <br> $venue4"; ?></td>
      	</tr>
				<!--
				<tr id="match5">
					<td class="small text-muted">Grp B</td>
		    	<td><?php echo $B1; ?></td>
	        <td><img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"></td>
	      	<td><input type="text" id="score5_p" name="score5_p" class="form-control" /></td>
	      	<td align="center">v<br><span class="badge bg-light text-primary">03</span></td>
	      	<td><input type="text" id="score6_p" name="score6_p" class="form-control" /></td>
					<td><img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"></td>
	      	<td><?php echo $B2; ?></td>
	      	<td class="date-venue small text-muted"><?php echo "$_13, $_21Nov, $year <br> $venue2"; ?></td>
      	</tr>
				<tr id="match6">
					<td class="small text-muted">Grp B</td>
		    	<td><?php echo $B3; ?></td>
	        <td><img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"></td>
	      	<td><input type="text" id="score7_p" name="score7_p" class="form-control" /></td>
	      	<td align="center">v<br><span class="badge bg-light text-primary">03</span></td>
	      	<td><input type="text" id="score8_p" name="score8_p" class="form-control" /></td>
					<td><img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"></td>
	      	<td><?php echo $B4; ?></td>
	      	<td class="date-venue small text-muted"><?php echo "$_19, $_21Nov, $year <br> $venue4"; ?></td>
      	</tr>
				<tr id="match7">
					<td class="small text-muted">Grp B</td>
		    	<td><?php echo $B1; ?></td>
	        <td><img src="<?php echo $B1img; ?>" alt="<?php echo $B1; ?>" title="<?php echo $B1; ?>"></td>
	      	<td><input type="text" id="score5_p" name="score5_p" class="form-control" /></td>
	      	<td align="center">v<br><span class="badge bg-light text-primary">03</span></td>
	      	<td><input type="text" id="score6_p" name="score6_p" class="form-control" /></td>
					<td><img src="<?php echo $B2img; ?>" alt="<?php echo $B2; ?>" title="<?php echo $B2; ?>"></td>
	      	<td><?php echo $B2; ?></td>
	      	<td class="date-venue small text-muted"><?php echo "$_13, $_21Nov, $year <br> $venue2"; ?></td>
      	</tr>
				<tr id="match8">
					<td class="small text-muted">Grp B</td>
		    	<td><?php echo $B3; ?></td>
	        <td><img src="<?php echo $B3img; ?>" alt="<?php echo $B3; ?>" title="<?php echo $B3; ?>"></td>
	      	<td><input type="text" id="score7_p" name="score7_p" class="form-control" /></td>
	      	<td align="center">v<br><span class="badge bg-light text-primary">03</span></td>
	      	<td><input type="text" id="score8_p" name="score8_p" class="form-control" /></td>
					<td><img src="<?php echo $B4img; ?>" alt="<?php echo $B4; ?>" title="<?php echo $B4; ?>"></td>
	      	<td><?php echo $B4; ?></td>
	      	<td class="date-venue small text-muted"><?php echo "$_19, $_21Nov, $year <br> $venue4"; ?></td>
      	</tr>
			</table>-->

        <div id="submit-footer" class="navbar navbar-default navbar-fixed-bottom col-md-10 col-md-offset-1">
            <div class="pull-right">
            <!-- Results being processed - updating temporarily unavailable... -->
            <input type="submit" class="navbar-btn btn btn-primary" value="Update my predictions" name="predictionsSubmitted" />
            <a class="navbar-btn btn btn-default" href="#top" role="button">Return to top</a>
            </div>
        </div>

        </div><!--col-md-12-->
      </div><!--row-->
   	</form>

      <!-- Site footer -->
      <div class="footer">
      <?php include "includes/footer.php" ?>
      </div>


    </div><!-- /.main-section -->


    <script type="text/javascript">

		function parseDate(str) {
			var s = str.split(" "),
				d = s[0].split("-"),
				t = s[1].replace(/:/g, "");
			return d[2] + d[1] + d[0] + t;
		}
/*
		$(document).ready(function(e) {

			// Create an array of 'lockdown' dates and times to disable specific fields
			var lockdown = ["14-06-2018 16:00:00", <!--Fixture 1-->
							"15-06-2018 12:00:00", <!--Fixture 2-->
							"15-06-2018 15:00:00", <!--Fixture 3-->
							"15-06-2018 18:00:00", <!--Fixture 4-->
							"16-06-2018 10:00:00", <!--Fixture 5-->
							"16-06-2018 13:00:00", <!--Fixture 6-->
							"16-06-2018 16:00:00", <!--Fixture 7-->
							"16-06-2018 19:00:00", <!--Fixture 8-->
							"17-06-2018 12:00:00", <!--Fixture 9-->
							"17-06-2018 15:00:00", <!--Fixture 10-->
							"17-06-2018 18:00:00", <!--Fixture 11-->
							"18-06-2018 12:00:00", <!--Fixture 12-->
							"18-06-2018 15:00:00", <!--Fixture 13-->
							"18-06-2018 18:00:00", <!--Fixture 14-->
							"19-06-2018 12:00:00", <!--Fixture 15-->
							"19-06-2018 15:00:00", <!--Fixture 16-->
							"19-06-2018 18:00:00", <!--Fixture 17-->
							"20-06-2018 12:00:00", <!--Fixture 18-->
							"20-06-2018 15:00:00", <!--Fixture 19-->
							"20-06-2018 18:00:00", <!--Fixture 20-->
							"21-06-2018 12:00:00", <!--Fixture 21-->
							"21-06-2018 15:00:00", <!--Fixture 22-->
							"21-06-2018 18:00:00", <!--Fixture 23-->
							"22-06-2018 12:00:00", <!--Fixture 24-->
							"22-06-2018 15:00:00", <!--Fixture 25-->
							"22-06-2018 18:00:00", <!--Fixture 26-->
							"23-06-2018 12:00:00", <!--Fixture 27-->
							"23-06-2018 15:00:00", <!--Fixture 28-->
							"23-06-2018 18:00:00", <!--Fixture 29-->
							"24-06-2018 12:00:00", <!--Fixture 30-->
							"24-06-2018 15:00:00", <!--Fixture 31-->
							"24-06-2018 18:00:00", <!--Fixture 32-->
							"25-06-2018 14:00:00", <!--Fixture 33-->
							"25-06-2018 14:00:00", <!--Fixture 34-->
							"25-06-2018 18:00:00", <!--Fixture 35-->
							"25-06-2018 18:00:00", <!--Fixture 36-->
							"26-06-2018 14:00:00", <!--Fixture 37-->
							"26-06-2018 14:00:00", <!--Fixture 38-->
							"26-06-2018 18:00:00", <!--Fixture 39-->
							"26-06-2018 18:00:00", <!--Fixture 40-->
							"27-06-2018 14:00:00", <!--Fixture 41-->
							"27-06-2018 14:00:00", <!--Fixture 42-->
							"27-06-2018 18:00:00", <!--Fixture 43-->
							"27-06-2018 18:00:00", <!--Fixture 44-->
							"28-06-2018 14:00:00", <!--Fixture 45-->
							"28-06-2018 14:00:00", <!--Fixture 46-->
							"28-06-2018 18:00:00", <!--Fixture 47-->
							"28-06-2018 18:00:00", <!--Fixture 48-->
							"30-06-2018 14:00:00", <!--Fixture 49-->
							"30-06-2018 18:00:00", <!--Fixture 50-->
							"01-07-2018 14:00:00", <!--Fixture 51-->
							"01-07-2018 18:00:00", <!--Fixture 52-->
							"02-07-2018 14:00:00", <!--Fixture 53-->
							"02-07-2018 18:00:00", <!--Fixture 54-->
							"03-07-2018 14:00:00", <!--Fixture 55-->
							"03-07-2018 18:00:00", <!--Fixture 56-->
							"06-07-2018 14:00:00", <!--Fixture 57-->
							"06-07-2018 18:00:00", <!--Fixture 58-->
							"07-07-2018 14:00:00", <!--Fixture 59-->
							"07-07-2018 18:00:00", <!--Fixture 60-->
							"10-07-2018 18:00:00", <!--Fixture 61-->
							"11-07-2018 18:00:00", <!--Fixture 62-->
							"14-07-2018 14:00:00", <!--Fixture 63-->
							"15-07-2018 15:00:00"  <!--Fixture 64-->
							];

			for (i=1, j=2, k=0; i<129, j<129; i+=2, j+=2, k++) {
				/// i and j < values = (number of matches * 2) + 1

				// Port current server date/time to JS variable
				var currTime = "<?php print date("d-m-Y H:i:s") ?>";
				var x = "#score"+[i]+"_p";  // i = 1 3 5 7
				var y = "#score"+[j]+"_p";  // j = 2 4 6 8
				var z = "#updateBtn"+[k+1];
				//alert(currTime);

				if (parseDate(currTime) >= parseDate(lockdown[k])) {
					// Use alert to step through sequence
					//alert("Yes, current time has passed set value!");
					//$(x).prop("disabled", true);
					$(x).prop("readOnly", true);
					$(y).prop("readOnly", true);
					$(z).hide();
				}
				else {
					// Use alert to step through sequence
					//alert("No, current time has not passed set value!");
					/*$(x).prop("enabled", true);
					$(y).prop("enabled", true);
					$(z).prop("enabled", true);
					$(xFixed).hide();
					$(yFixed).hide();
				}
			}
        });
*/
		function showOnly(GoS) {
			/* GoS = Group or Stage
			if (GoS == "All") {
				$("#groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #qf, #sf, #po, #final").removeClass("active");
				$("#all").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Match 63')), tr:has(td:contains('Final'))").show();
			}
			if (GoS == "Groups") {
				$("#groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #qf, #sf, #po, #final").removeClass("active");
				$("#groups").addClass("active");
				$("tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Match 63')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H'))").show();
			}/*
			if (GoS == 'A') {
				$("#all, #groups, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupA").addClass("active");
				$("tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group A'))").show();
			}
			if (GoS == 'B') {
				$("#all, #groups, #groupA, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupB").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group B'))").show();
			}
			if (GoS == 'C') {
				$("#all, #groups, #groupA, #groupB, #groupD, #groupE, #groupF, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupC").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group C'))").show();
			}
			if (GoS == 'D') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupE, #groupF, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupD").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('QF')), tr:has(td:contains('SF')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group D'))").show();
			}
			if (GoS == 'E') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupF, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupE").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group E'))").show();
			}
			if (GoS == 'F') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #ro16, #qf, #sf, #final").removeClass("active");
				$("#groupF").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Group F'))").show();
			}

			if (GoS == 'RO16') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #qf, #sf, #po, #final").removeClass("active");
				$("#ro16").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Match 63')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('RO16'))").show();
			}
			if (GoS == 'QF') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #sf, #po, #final").removeClass("active");
				$("#qf").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('RO16')), tr:has(td:contains('Semi')), tr:has(td:contains('Match 63')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Quarter'))").show();
			}
			if (GoS == 'SF') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #qf, #po, #final").removeClass("active");
				$("#sf").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Match 63')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Semi'))").show();
			}
			if (GoS == 'PO') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #qf, #sf, #final").removeClass("active");
				$("#po").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('Match 63'))").show();
			}
			if (GoS == 'Final') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #groupG, #groupH, #ro16, #qf, #sf, #po").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('Group G')), tr:has(td:contains('Group H')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Match 63'))").hide();
				$("tr:has(td:contains('Match 64'))").show();
			}
			/*
			if (GoS == '10Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('10 June'))").show();
			}
			if (GoS == '11Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('11 June'))").show();
			}
			if (GoS == '12Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('12 June'))").show();
			}
			if (GoS == '13Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('13 June'))").show();
			}
			if (GoS == '14Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('14 June'))").show();
			}
			if (GoS == '15Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('15 June'))").show();
			}
			if (GoS == '16Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('16 June'))").show();
			}
			if (GoS == '17Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('17 June'))").show();
			}
			if (GoS == '18Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('18 June'))").show();
			}
			if (GoS == '19Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('19 June'))").show();
			}
			if (GoS == '20Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('20 June'))").show();
			}
			if (GoS == '21Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('21 June'))").show();
			}
			if (GoS == '22Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('22 June'))").show();
			}
			if (GoS == '25Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('25 June'))").show();
			}
			if (GoS == '26Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('26 June'))").show();
			}
			if (GoS == '27Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('27 June'))").show();
			}
			if (GoS == '30Jun') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('30 June'))").show();
			}
			if (GoS == '01Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('01 July'))").show();
			}
			if (GoS == '02Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('02 July'))").show();
			}
			if (GoS == '03Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('03 July'))").show();
			}
			if (GoS == '06Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('06 July'))").show();
			}
			if (GoS == '07Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('07 July'))").show();
			}
			if (GoS == '10Jul') {
				$("#all, #groups, #groupA, #groupB, #groupC, #groupD, #groupE, #groupF, #ro16, #qf, #sf").removeClass("active");
				$("#final").addClass("active");
				$("tr:has(td:contains('Group A')), tr:has(td:contains('Group B')), tr:has(td:contains('Group C')), tr:has(td:contains('Group D')), tr:has(td:contains('Group E')), tr:has(td:contains('Group F')), tr:has(td:contains('RO16')), tr:has(td:contains('Quarter')), tr:has(td:contains('Semi')), tr:has(td:contains('Final'))").hide();
				$("tr:has(td:contains('10 July'))").show();
			}*/
		}
	</script>
  </body>
</html>
