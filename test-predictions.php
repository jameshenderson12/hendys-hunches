<?php
session_start();
$page_title = 'Submit Predictions';

if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
    header("Location: index.php");
    exit();
}

include "php/header.php";
include "php/navigation.php";

?>

<style>
td img {
	width: 36px;
	border-radius: 50%;
	vertical-align: middle;
}
td:nth-child(2), td:nth-child(7) {
	text-align: right;
}
input {
	font-size: larger !important;
	text-align: center !important;
	border: 1px solid #AAA !important;
	width: 55px !important;
}
</style>

<!-- Main Content Section -->
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between">
    <nav>
      <h1>Submit your predictions</h1>
        <!-- <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
          <li class="breadcrumb-item active">Part #3 - 11.30</li>
        </ol> -->
      </nav> 
    </div><!-- End Page Title -->

    <section class="section">
		<p class="lead">Turn your predictions into points and prizes!</p>
		<p>Predict the results of each of the following 36 group stage fixtures and then 'submit' to lock in your predictions. Predictions will not be submitted unless you have completed for all 36 fixtures. Once submitted, predictions cannot be changed. Forms for the knockout fixtures will be available from <?= $GLOBALS['group_fixtures_end_date'] ?>.
		<!-- <p class="alert alert-warning" id="submitMsg"><strong>Note:</strong> You can predict a draw as predictions are for 90 mins only (do not include extra time and penalties).</p> -->
		<a name="matches"></a><!--anchor point for filters-->				
		<form id="predictionForm" name="predictionForm" class="form-horizontal" action="submit.php" method="POST">
		<!-- <button type="button" class="btn btn-secondary mb-3 populate-scores"><i class="bi bi-magic"></i> Populate for me</button>
		<button type="submit" class="btn btn-primary mb-3" name="predictionsSubmitted"><i class="bi bi-send-check-fill"></i> Submit predictions</button>		 -->
		<div class="row">		
		<!-- Placeholder for JSON table construction -->
		<table id="table" class="table table-sm table-striped">
			<thead>
				<tr>
					<th class="d-none d-md-table-cell">Stage</th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th class="d-none d-md-table-cell">Details</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
		</div><!--row-->
		<button type="button" class="btn btn-secondary mt-3 mb-2 populate-scores"><i class="bi bi-magic"></i> Populate for me</button>
		<button type="submit" class="btn btn-primary mt-3 mb-2" name="predictionsSubmitted"><i class="bi bi-send-check-fill"></i> Submit my predictions</button>
		</form>
    </section>
        
</main>

<script>
    $(document).ready(function () {
        // Fetch data from JSON file
        $.getJSON("json/uefa-euro-2024-fixtures-ro16.json", function (data) {
            let fixture = '';
            let x = 73, y = 74;

            // Iterate through objects
            $.each(data, function (key, value) {
                const homeTeam = value.HomeTeam;
                const awayTeam = value.AwayTeam;
                const homeTeamFlag = `flag-icons/24/${homeTeam.toLowerCase().replaceAll(' ', '-')}.png`;
                const awayTeamFlag = `flag-icons/24/${awayTeam.toLowerCase().replaceAll(' ', '-')}.png`;
                const dateStr = value.DateUtc;
                const [dateValues, timeValues] = dateStr.split(' ');
                const [year, month, day] = dateValues.split('-');
                const [hours, minutes] = timeValues.split(':');
                const date = new Date(+year, +month - 1, +day, +hours, +minutes).toLocaleString().slice(0, -3);

                fixture += `
                    <tr>
                        <td class="small text-muted d-none d-md-table-cell">${value.RoundNumber}</td>
                        <td><img src="${homeTeamFlag}" alt="Flag of ${homeTeam}" title="Flag of ${homeTeam}" class="img-fluid"></td>
						<td>${homeTeam}</td>                        
                        <td><input type="text" id="score${x}_p" name="score${x}_p" class="form-control" required /></td>
                        <td align="center"><strong>V</strong></td><!--<br><span class="badge bg-light text-primary">${value.MatchNumber}</span>-->
                        <td><input type="text" id="score${y}_p" name="score${y}_p" class="form-control" style="float:right" required /></td>                        
                        <td>${awayTeam}</td>
						<td><img src="${awayTeamFlag}" alt="Flag of ${awayTeam}" title="Flag of ${awayTeam}" class="img-fluid"></td>
                        <td class="small text-muted d-none d-md-table-cell">
                            <span data-bs-toggle="tooltip" title="Match Number: ${value.MatchNumber}, Round: ${value.RoundNumber}, Date: ${date}, Location: ${value.Location}">${date}<br>${value.Location}</span>
                        </td>
                    </tr>
                `;
                x += 2;
                y += 2;
            });

            // Insert rows into table
            $('#table tbody').append(fixture);

			// Add click event to populate scores
			$('.populate-scores').click(function() {
				populateScores();
			});
		});
	});

	function populateScores() {
		function getRandomScore() {
			const rand = Math.random();
			// if (rand < 0.4) return 0;  // 40% chance of 0
			// else if (rand < 0.7) return 1;  // 30% chance of 1
			// else if (rand < 0.9) return 2;  // 20% chance of 2
			// else if (rand < 0.97) return 3;  // 7% chance of 3
			// else return 4;  // 3% chance of 4
			if (rand < 0.37) return 0;  // 37% chance of 0
			else if (rand < 0.67) return 1;  // 30% chance of 1
			else if (rand < 0.88) return 2;  // 21% chance of 2
			else if (rand < 0.95) return 3;  // 7% chance of 3
			else if (rand < 0.99) return 4;  // 4% chance of 4
			else return 5;  // 1% chance of 5			
		}

		for (let i = 73; i <= 88; i += 2) {
			const homeScore = getRandomScore();
			let awayScore = getRandomScore();

			// Avoid high-scoring draws
			if (homeScore >= 3 && awayScore >= 3) {
				// Reduce the likelihood of both scores being high
				if (Math.random() < 0.5) {
					// homeScore = getRandomScore();  // Regenerate home score
					awayScore = getRandomScore();  // Regenerate away score
				}
			}

			$('#score' + i + '_p').val(homeScore);  // Home score
			$('#score' + (i + 1) + '_p').val(awayScore);  // Away score
		}
	}	

	function parseDate(str) {
		var s = str.split(" "),
			d = s[0].split("-"),
			t = s[1].replace(/:/g, "");
		return d[2] + d[1] + d[0] + t;
	}

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

<!-- Footer -->
<?php include "php/footer.php" ?>