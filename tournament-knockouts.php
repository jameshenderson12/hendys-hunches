<?php
session_start();
$page_title = 'Tournament Knockouts';

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
      <h1>Tournament Knockouts</h1>
        <!-- <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
          <li class="breadcrumb-item active">Part #3 - 11.30</li>
        </ol> -->
      </nav> 
    </div><!-- End Page Title -->

    <section class="section">
		<p class="lead">Current display of <?= $GLOBALS['competition'] ?> knockouts. This is not live but updated after each fixture.</p>

    <?php
    // Connect to the database
    include 'php/db-connect.php';

    // SQL query to get the Round of 16 fixtures and results
    $sql_fixtures = "SELECT hometeamimg, hometeam, homescore, awayscore, awayteam, awayteamimg, venue, kotime, DATE_FORMAT(date, '%a, %D %b') as formatted_date
                    FROM live_match_schedule
                    WHERE stage = 'RO16'
                    ORDER BY date, kotime";

    $result = mysqli_query($con, $sql_fixtures) or die(mysqli_error($con));

    // Array of teams to highlight
    $highlightTeamsRO16 = ['Switzerland', 'Germany', 'England', 'Spain', 'France', 'Portugal', 'Netherlands', 'Türkiye'];

    echo "<!-- Round of 16 -->";
    echo "<div class='row mb-4'>";
    echo "<div class='col-12'>";
    echo "<h4 class='mt-2' style='color: #012970'>Round of 16</h4>";
    echo "</div>";
    echo "<div class='col-12'>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered table-striped'>";
    echo "<thead class='table-dark'>";
    echo "<tr>";
    echo "<th class='d-none d-sm-table-cell'>Kick-Off</th>";
    echo "<th></th>";
    echo "<th></th>";
    echo "<th></th>";
    echo "<th class='d-none d-sm-table-cell'>Venue</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    while ($row = mysqli_fetch_assoc($result)) {
        $kickoff = $row['formatted_date'] . "<br>" . $row['kotime'];

        // Check if the home team needs to be highlighted
        $homeTeamClass = in_array($row['hometeam'], $highlightTeamsRO16) ? 'table-success' : '';
        $team1 = "<img src='" . $row['hometeamimg'] . "' width='24px' style='width: 36px; border-radius: 50%; margin-right: 10px;' class='$homeTeamClass'>" . $row['hometeam'];

        // Check if the away team needs to be highlighted
        $awayTeamClass = in_array($row['awayteam'], $highlightTeamsRO16) ? 'table-success' : '';
        $team2 = $row['awayteam'] . " <img src='" . $row['awayteamimg'] . "' width='24px' style='width: 36px; border-radius: 50%;' class='$awayTeamClass'>";

        $venue = $row['venue'];
        $match = $row['homescore'] !== NULL && $row['awayscore'] !== NULL ? $row['homescore'] . " - " . $row['awayscore'] : "vs";

        echo "<tr>";
        echo "<td class='small d-none d-sm-table-cell'>{$kickoff}</td>";
        echo "<td class='{$homeTeamClass}'>{$team1}</td>";
        echo "<td class='text-center'>{$match}</td>";
        echo "<td class='text-end {$awayTeamClass}'>{$team2}</td>";
        echo "<td class='small d-none d-sm-table-cell'>{$venue}</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

        // Close the database connection
        mysqli_close($con);
    ?>

<?php
    // Connect to the database
    include 'php/db-connect.php';

    // SQL query to get the Round of 16 fixtures and results
    $sql_fixtures = "SELECT hometeamimg, hometeam, homescore, awayscore, awayteam, awayteamimg, venue, kotime, DATE_FORMAT(date, '%a, %D %b') as formatted_date
                    FROM live_match_schedule
                    WHERE stage = 'Quarter-Finals'
                    ORDER BY date, kotime";

    $result = mysqli_query($con, $sql_fixtures) or die(mysqli_error($con));

    // Array of teams to highlight
    $highlightTeamsQF = [];

    echo "<!-- Round of 16 -->";
    echo "<div class='row mb-4'>";
    echo "<div class='col-12'>";
    echo "<h4 class='mt-2' style='color: #012970'>Quarter-Finals</h4>";
    echo "</div>";
    echo "<div class='col-12'>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered table-striped'>";
    echo "<thead class='table-dark'>";
    echo "<tr>";
    echo "<th class='d-none d-sm-table-cell'>Kick-Off</th>";
    echo "<th></th>";
    echo "<th></th>";
    echo "<th></th>";
    echo "<th class='d-none d-sm-table-cell'>Venue</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    while ($row = mysqli_fetch_assoc($result)) {
        $kickoff = $row['formatted_date'] . "<br>" . $row['kotime'];

        // Check if the home team needs to be highlighted
        $homeTeamClass = in_array($row['hometeam'], $highlightTeamsQF) ? 'table-success' : '';
        $team1 = "<img src='" . $row['hometeamimg'] . "' width='24px' style='width: 36px; border-radius: 50%; margin-right: 10px;' class='$homeTeamClass'>" . $row['hometeam'];

        // Check if the away team needs to be highlighted
        $awayTeamClass = in_array($row['awayteam'], $highlightTeamsQF) ? 'table-success' : '';
        $team2 = $row['awayteam'] . " <img src='" . $row['awayteamimg'] . "' width='24px' style='width: 36px; border-radius: 50%;' class='$awayTeamClass'>";

        $venue = $row['venue'];
        $match = $row['homescore'] !== NULL && $row['awayscore'] !== NULL ? $row['homescore'] . " - " . $row['awayscore'] : "vs";

        echo "<tr>";
        echo "<td class='small d-none d-sm-table-cell'>{$kickoff}</td>";
        echo "<td class='{$homeTeamClass}'>{$team1}</td>";
        echo "<td class='text-center'>{$match}</td>";
        echo "<td class='text-end {$awayTeamClass}'>{$team2}</td>";
        echo "<td class='small d-none d-sm-table-cell'>{$venue}</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

        // Close the database connection
        mysqli_close($con);
    ?>


    <!-- Semi-Finals -->
    <div class="row mb-4">
      <div class="col-12">
        <h4 class='mt-2' style='color: #012970'>Semi-Finals</h4>
      </div>
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="table-dark">
              <tr>
                <th>Kick-Off</th>
                <th>Team 1</th>
                <th>Match</th>
                <th>Team 2</th>
                <th>Venue</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="small">Tue, 9th July<br>17:00</td>
                <td>W45</td>
                <td>49</td>
                <td>W46</td>
                <td class="small">Munich</td>
              </tr>
              <tr>
                <td class="small">Wed, 10th July<br>20:00</td>
                <td>W47</td>
                <td>50</td>
                <td>W48</td>
                <td class="small">Dortmund</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Final -->
    <div class="row mb-4">
      <div class="col-12">
        <h4 class='mt-2' style='color: #012970'>Final</h4>
      </div>
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="table-dark">
              <tr>
                <th>Kick-Off</th>
                <th>Team 1</th>
                <th>Match</th>
                <th>Team 2</th>
                <th>Venue</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="small">Sun, 14th July<br>20:00</td>
                <td>W49</td>
                <td>51</td>
                <td>W50</td>
                <td class="small">Berlin</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    </section>

</main>

<!-- Footer -->
<?php include "php/footer.php" ?>   