<?php
session_start();
$page_title = 'Tournament Knockouts';

require_once __DIR__ . '/php/auth.php';
hh_require_login('index.php');

include "php/header.php";
include "php/navigation.php";

?>

<!-- Main Content Section -->
<main id="main" class="main">

    <div class="page-hero page-hero--competition">
    <div>
      <p class="eyebrow">Competition</p>
      <h1>Tournament Knockouts</h1>
      <p class="lead mb-0">The knockout path for <?= $GLOBALS['competition'] ?>, updated after each fixture.</p>
    </div>
    <div class="page-hero__actions">
      <a class="btn btn-primary" href="tournament-groups.php"><i class="bi bi-grid-3x3-gap"></i> Groups</a>
      <a class="btn btn-outline-dark" href="rankings.php"><i class="bi bi-list-ol"></i> Rankings</a>
    </div>
    </div><!-- End Page Title -->

    <section class="section competition-page">

    <?php
    // Connect to the database
    include 'php/db-connect.php';

    // SQL query to get the Round of 16 fixtures and results
    $sql_fixtures = "SELECT hometeamimg, hometeam, homescore, awayscore, awayteam, awayteamimg, venue, kotime, DATE_FORMAT(date, '%a, %D %b') as formatted_date
                    FROM live_match_schedule
                    WHERE stage = 'Round of 16'
                    ORDER BY date, kotime";

    $result = mysqli_query($con, $sql_fixtures) or die(mysqli_error($con));

    // Array of teams to highlight
    $highlightTeamsRO16 = ['Switzerland', 'Germany', 'England', 'Spain', 'France', 'Portugal', 'Netherlands', 'Türkiye'];

    echo "<!-- Round of 16 -->";
    echo "<div class='competition-panel'>";
    echo "<div class='row mb-4'>";
    echo "<div class='col-12'>";
    echo "<h4 class='competition-panel__title'>Round of 16</h4>";
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
    $highlightTeamsQF = ['Spain', 'France', 'England', 'Netherlands'];

    echo "<!-- Quarter-Finals -->";
    echo "<div class='competition-panel'>";
    echo "<div class='row mb-4'>";
    echo "<div class='col-12'>";
    echo "<h4 class='competition-panel__title'>Quarter-Finals</h4>";
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
                    WHERE stage = 'Semi-Finals'
                    ORDER BY date, kotime";

    $result = mysqli_query($con, $sql_fixtures) or die(mysqli_error($con));

    // Array of teams to highlight
    $highlightTeamsSF = ['Spain', 'England'];

    echo "<!-- Semi-Finals -->";
    echo "<div class='competition-panel'>";
    echo "<div class='row mb-4'>";
    echo "<div class='col-12'>";
    echo "<h4 class='competition-panel__title'>Semi-Finals</h4>";
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
        $homeTeamClass = in_array($row['hometeam'], $highlightTeamsSF) ? 'table-success' : '';
        $team1 = "<img src='" . $row['hometeamimg'] . "' width='24px' style='width: 36px; border-radius: 50%; margin-right: 10px;' class='$homeTeamClass'>" . $row['hometeam'];

        // Check if the away team needs to be highlighted
        $awayTeamClass = in_array($row['awayteam'], $highlightTeamsSF) ? 'table-success' : '';
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
                    WHERE stage = 'Final'
                    ORDER BY date, kotime";

    $result = mysqli_query($con, $sql_fixtures) or die(mysqli_error($con));

    // Array of teams to highlight
    $highlightTeamsFi = [];

    echo "<!-- Final -->";
    echo "<div class='competition-panel'>";
    echo "<div class='row mb-4'>";
    echo "<div class='col-12'>";
    echo "<h4 class='competition-panel__title'>Final</h4>";
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
        $homeTeamClass = in_array($row['hometeam'], $highlightTeamsFi) ? 'table-success' : '';
        $team1 = "<img src='" . $row['hometeamimg'] . "' width='24px' style='width: 36px; border-radius: 50%; margin-right: 10px;' class='$homeTeamClass'>" . $row['hometeam'];

        // Check if the away team needs to be highlighted
        $awayTeamClass = in_array($row['awayteam'], $highlightTeamsFi) ? 'table-success' : '';
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
    echo "</div>";

        // Close the database connection
        mysqli_close($con);
    ?>

    </section>

</main>

<!-- Footer -->
<?php include "php/footer.php" ?>   
