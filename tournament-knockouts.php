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

// Get the latest standings date
$latest_date_query = "SELECT MAX(standings_date) as latest_date FROM group_standings";
$latest_date_result = mysqli_query($con, $latest_date_query);
$latest_date_row = mysqli_fetch_assoc($latest_date_result);
$latest_date = $latest_date_row['latest_date'];

// Query to get the top 2 teams from each group
$sql = "SELECT group_stage, team_name, team_img FROM group_standings WHERE standings_date = '$latest_date' ORDER BY group_stage, points DESC, goal_difference DESC, goals_for DESC";
$result = mysqli_query($con, $sql);

$group_winners = [];
$group_runners_up = [];

// Process the query results
$current_group = '';
$position = 1;
while ($row = mysqli_fetch_assoc($result)) {
    if ($current_group !== $row['group_stage']) {
        $current_group = $row['group_stage'];
        $position = 1;
    }
    if ($position == 1) {
        $group_winners[$current_group] = $row;
    } elseif ($position == 2) {
        $group_runners_up[$current_group] = $row;
    }
    $position++;
}

// Define the knockout matches based on group positions
$knockout_matches = [
    ["match" => 38, "team1" => $group_runners_up['A'], "team2" => $group_winners['B'], "venue" => "Berlin"],
    ["match" => 37, "team1" => $group_winners['A'], "team2" => $group_runners_up['C'], "venue" => "Dortmund"],
    ["match" => 40, "team1" => $group_winners['C'], "team2" => "3D/E/F", "venue" => "Gelsenkirchen"],
    ["match" => 39, "team1" => $group_runners_up['B'], "team2" => "3A/D/E/F", "venue" => "Cologne"],
    ["match" => 42, "team1" => $group_winners['D'], "team2" => $group_runners_up['E'], "venue" => "Dusseldorf"],
    ["match" => 41, "team1" => $group_winners['F'], "team2" => "3A/B/C", "venue" => "Frankfurt"],
    ["match" => 43, "team1" => $group_winners['E'], "team2" => "3A/B/C/D", "venue" => "Munich"],
    ["match" => 44, "team1" => $group_winners['D'], "team2" => $group_runners_up['F'], "venue" => "Leipzig"]
];

// Display the knockout matches
echo "<table class='table table-bordered table-striped'>
        <thead class='table-dark'>
            <tr>
                <th>Kick-Off</th>
                <th>Match</th>
                <th>Venue</th>
            </tr>
        </thead>
        <tbody>";

foreach ($knockout_matches as $match) {
    echo "<tr>
            <td>{$match['match']}</td>
            <td><img src='{$match['team1']['team_img']}' alt='{$match['team1']['team_name']}' style='width: 36px; border-radius: 50%; margin-right: 10px;' /> {$match['team1']['team_name']} vs <img src='{$match['team2']['team_img']}' alt='{$match['team2']['team_name']}' style='width: 36px; border-radius: 50%; margin-right: 10px;' /> {$match['team2']['team_name']}</td>
            <td>{$match['venue']}</td>
        </tr>";
}

echo "</tbody></table>";

// Close the database connection
mysqli_close($con);
?>


    <!-- Round of 16 -->
    <div class="row mb-4">
      <div class="col-12">
        <h4 class='mt-2' style='color: #012970'>Round of 16</h4>
      </div>
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="table-dark">
              <tr>
                <th>Kick-Off</th>
                <th width="25%">Team 1</th>
                <th>Match</th>
                <th width="25%">Team 2</th>
                <th>Venue</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="small">Sat, 29th June<br>17:00</td>
                <td>A2</td>
                <td>38</td>
                <td>B2</td>
                <td class="small">Berlin</td>
              </tr>
              <tr>
                <td class="small">Sat, 29th June<br>20:00</td>
                <td>A1</td>
                <td>37</td>
                <td>C2</td>
                <td class="small">Dortmund</td>
              </tr>
              <tr>
                <td class="small">Sun, 30th June<br>17:00</td>
                <td>C1</td>
                <td>40</td>
                <td>D3/E3/F3</td>
                <td class="small">Gelsenkirchen</td>
              </tr>
              <tr>
                <td class="small">Sun, 30th June<br>20:00</td>
                <td>B1</td>
                <td>39</td>
                <td>A3/D3/E3/F3</td>
                <td class="small">Cologne</td>
              </tr>
              <tr>
                <td class="small">Mon, 1st July<br>17:00</td>
                <td>D2</td>
                <td>42</td>
                <td>E2</td>
                <td class="small">Dusseldorf</td>
              </tr>
              <tr>
                <td class="small">Mon, 1st July<br>20:00</td>
                <td>F1</td>
                <td>41</td>
                <td>A3/B3/C3</td>
                <td class="small">Frankfurt</td>
              </tr>
              <tr>
                <td class="small">Tue, 2nd July<br>17:00</td>
                <td>E1</td>
                <td>43</td>
                <td>A3/B3/C3/D3</td>
                <td class="small">Munich</td>
              </tr>
              <tr>
                <td class="small">Tue, 2nd July<br>20:00</td>
                <td>D1</td>
                <td>44</td>
                <td>F2</td>
                <td class="small">Leipzig</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Quarter Finals -->
    <div class="row mb-4">
      <div class="col-12">
        <h4 class='mt-2' style='color: #012970'>Quarter Finals</h4>
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
                <td class="small">Fri, 5th July<br>17:00</td>
                <td>W39</td>
                <td>45</td>
                <td>W37</td>
                <td class="small">Stuttgart</td>
              </tr>
              <tr>
                <td class="small">Fri, 5th July<br>20:00</td>
                <td>W41</td>
                <td>46</td>
                <td>W42</td>
                <td class="small">Hamburg</td>
              </tr>
              <tr>
                <td class="small">Sat, 6th July<br>17:00</td>
                <td>W40</td>
                <td>48</td>
                <td>W38</td>
                <td class="small">Dusseldorf</td>
              </tr>
              <tr>
                <td class="small">Sat, 6th July<br>20:00</td>
                <td>W43</td>
                <td>47</td>
                <td>W44</td>
                <td class="small">Berlin</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

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