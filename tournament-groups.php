<?php
session_start();
$page_title = 'Tournament Groups';

if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
    header("Location: index.php");
    exit();
}

include "php/header.php";
include "php/navigation.php";

?>

<!-- Main Content Section -->
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between">
    <nav>
      <h1>Tournament Groups</h1>
        <!-- <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
          <li class="breadcrumb-item active">Part #3 - 11.30</li>
        </ol> -->
      </nav> 
    </div><!-- End Page Title -->

    <section class="section">
		<p class="lead">Current display of <?= $GLOBALS['competition'] ?> groups. This is not live but updated after each fixture.</p>

        <?php
            // Connect to the database
            include 'php/db-connect.php';

            // Query to get all matches and their results
            $sql = "SELECT stage, hometeam, homescore, awayteam, awayscore, hometeamimg, awayteamimg FROM live_match_schedule";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));

            // Initialize an array to store the standings
            $groups = [];

            // Process the match results
            while ($row = mysqli_fetch_assoc($result)) {
                $stage = $row['stage'];
                $homeTeam = $row['hometeam'];
                $awayTeam = $row['awayteam'];
                $homeScore = $row['homescore'];
                $awayScore = $row['awayscore'];
                $homeTeamImg = $row['hometeamimg'];
                $awayTeamImg = $row['awayteamimg'];

                // Initialize the group if not already
                if (!isset($groups[$stage])) {
                    $groups[$stage] = [];
                }

                // Initialize the teams if not already
                if (!isset($groups[$stage][$homeTeam])) {
                    $groups[$stage][$homeTeam] = [
                        'name' => $homeTeam,
                        'img' => $homeTeamImg,
                        'played' => 0,
                        'won' => 0,
                        'drawn' => 0,
                        'lost' => 0,
                        'goalsFor' => 0,
                        'goalsAgainst' => 0,
                        'points' => 0
                    ];
                }
                if (!isset($groups[$stage][$awayTeam])) {
                    $groups[$stage][$awayTeam] = [
                        'name' => $awayTeam,
                        'img' => $awayTeamImg,
                        'played' => 0,
                        'won' => 0,
                        'drawn' => 0,
                        'lost' => 0,
                        'goalsFor' => 0,
                        'goalsAgainst' => 0,
                        'points' => 0
                    ];
                }

                // Update the teams' stats
                if (!is_null($homeScore) && !is_null($awayScore)) {
                    $groups[$stage][$homeTeam]['played']++;
                    $groups[$stage][$awayTeam]['played']++;
                    $groups[$stage][$homeTeam]['goalsFor'] += $homeScore;
                    $groups[$stage][$homeTeam]['goalsAgainst'] += $awayScore;
                    $groups[$stage][$awayTeam]['goalsFor'] += $awayScore;
                    $groups[$stage][$awayTeam]['goalsAgainst'] += $homeScore;

                    if ($homeScore > $awayScore) {
                        $groups[$stage][$homeTeam]['won']++;
                        $groups[$stage][$homeTeam]['points'] += 3;
                        $groups[$stage][$awayTeam]['lost']++;
                    } elseif ($homeScore < $awayScore) {
                        $groups[$stage][$awayTeam]['won']++;
                        $groups[$stage][$awayTeam]['points'] += 3;
                        $groups[$stage][$homeTeam]['lost']++;
                    } else {
                        $groups[$stage][$homeTeam]['drawn']++;
                        $groups[$stage][$awayTeam]['drawn']++;
                        $groups[$stage][$homeTeam]['points']++;
                        $groups[$stage][$awayTeam]['points']++;
                    }
                }
            }

            // Close the database connection
            mysqli_close($con);

            // Function to sort teams by points, then goal difference, then goals scored
            function sortTeams($a, $b) {
                if ($a['points'] == $b['points']) {
                    $goalDifferenceA = $a['goalsFor'] - $a['goalsAgainst'];
                    $goalDifferenceB = $b['goalsFor'] - $b['goalsAgainst'];
                    if ($goalDifferenceA == $goalDifferenceB) {
                        return $b['goalsFor'] - $a['goalsFor'];
                    }
                    return $goalDifferenceB - $goalDifferenceA;
                }
                return $b['points'] - $a['points'];
            }

            // Sort the groups by alphabetical order
            ksort($groups);

            // Display the standings
            foreach ($groups as $stage => $teams) {
                echo "<h4 class='mt-2' style='color: #012970'>$stage</h4>";
                echo "<table class='table table-bordered table-striped'>
                        <thead class='table-dark'>
                            <tr>
                                <th width='40%'>Team</th>
                                <th>P</th>
                                <th>W</th>
                                <th>D</th>
                                <th>L</th>
                                <th>F</th>
                                <th>A</th>
                                <th>+/-</th>
                                <th>Pts</th>
                            </tr>
                        </thead>
                        <tbody>";

                // Sort teams
                usort($teams, 'sortTeams');

                // Display each team
                foreach ($teams as $team) {
                    $goalDifference = $team['goalsFor'] - $team['goalsAgainst'];

                    // Determine the row class based on the criteria
                    $rowClass = '';
                    if ($team['played'] == 2) {
                        if ($team['points'] >= 6) {
                            $rowClass = 'table-success';
                        // } elseif ($team['points'] == 0) {
                        //     $rowClass = 'table-danger';
                        }
                        elseif ($team['name'] == "Poland") {
                            $rowClass = 'table-danger';
                        }
                        elseif ($team['name'] == "England") {
                            $rowClass = 'table-success';
                        }
                    }
                    if ($team['played'] == 3) {
                        if ($team['points'] >= 5) {
                            $rowClass = 'table-success';
                        } 
                        elseif ($team['points'] < 3) {
                             $rowClass = 'table-danger';
                        }
                        elseif ( ($team['name'] == "Poland") || ($team['name'] == "Ukraine") || ($team['name'] == "Hungary") ) {
                            $rowClass = 'table-danger';
                        }
                        elseif ( ($team['name'] == "Slovenia") || ($team['name'] == "Georgia") || ($team['name'] == "Denmark") || ($team['name'] == "Netherlands")  || ($team['name'] == "Italy")  || ($team['name'] == "Romania")  || ($team['name'] == "Belgium")  || ($team['name'] == "Slovakia")) {
                            $rowClass = 'table-success';
                        }
                    }                    

                    echo "<tr class='$rowClass'>
                            <td><img src='{$team['img']}' alt='{$team['name']}' style='width: 36px; border-radius: 50%; margin-right: 10px;' /> {$team['name']}</td>
                            <td>{$team['played']}</td>
                            <td>{$team['won']}</td>
                            <td>{$team['drawn']}</td>
                            <td>{$team['lost']}</td>
                            <td>{$team['goalsFor']}</td>
                            <td>{$team['goalsAgainst']}</td>
                            <td>{$goalDifference}</td>
                            <td>{$team['points']}</td>
                        </tr>";
                }
                echo "</tbody></table><br>";
            }
            ?>	
    </section>

</main>

<!-- Footer -->
<?php include "php/footer.php" ?>   