<?php
session_start();
$page_title = 'Setup Wizard';
$errors = [];
$messages = [];
$sql_output = [];
$fixture_preview = [];

if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
    header("Location: index.php");
    exit();
}

include '../php/config.php';
$con = null;
$db_path = '../php/db-connect.php';
if (file_exists($db_path)) {
    include $db_path;
} else {
    $errors[] = 'Database connection file not found. SQL generation will still work, but applying changes is disabled.';
}

function normalize_team_entry(string $entry): array {
    $parts = array_map('trim', explode('|', $entry));
    $team = $parts[0] ?? '';
    $flag = $parts[1] ?? '';

    return [$team, $flag];
}

function build_group_labels(int $num_groups): array {
    $labels = [];
    for ($i = 0; $i < $num_groups; $i++) {
        $labels[] = chr(65 + $i);
    }
    return $labels;
}

function parse_teams(string $teams_input, int $num_groups, int $teams_per_group): array {
    $groups = [];
    $labels = build_group_labels($num_groups);

    if (trim($teams_input) === '') {
        $counter = 1;
        foreach ($labels as $label) {
            $group = [];
            for ($i = 0; $i < $teams_per_group; $i++) {
                $group[] = ["Team {$counter}", ''];
                $counter++;
            }
            $groups[$label] = $group;
        }
        return $groups;
    }

    $lines = preg_split('/\r\n|\r|\n/', trim($teams_input));
    foreach ($lines as $index => $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        if (str_contains($line, ':')) {
            [$group_label, $entries] = array_map('trim', explode(':', $line, 2));
            $group_label = strtoupper(str_replace('Group ', '', $group_label));
        } else {
            $group_label = $labels[$index] ?? chr(65 + $index);
            $entries = $line;
        }

        $teams = array_filter(array_map('trim', explode(',', $entries)));
        $group = [];
        foreach ($teams as $team_entry) {
            [$team, $flag] = normalize_team_entry($team_entry);
            if ($team !== '') {
                $group[] = [$team, $flag];
            }
        }

        $groups[$group_label] = $group;
    }

    return $groups;
}

function round_robin(array $teams): array {
    $participants = $teams;
    $bye = ['BYE', ''];

    if (count($participants) % 2 !== 0) {
        $participants[] = $bye;
    }

    $num_teams = count($participants);
    $half = $num_teams / 2;
    $rounds = $num_teams - 1;
    $fixtures = [];

    for ($round = 0; $round < $rounds; $round++) {
        for ($i = 0; $i < $half; $i++) {
            $home = $participants[$i];
            $away = $participants[$num_teams - 1 - $i];

            if ($home[0] !== 'BYE' && $away[0] !== 'BYE') {
                $fixtures[] = [$home, $away];
            }
        }

        $fixed = array_shift($participants);
        $rotated = array_splice($participants, -1);
        $participants = array_merge([$fixed], $rotated, $participants);
    }

    return $fixtures;
}

function build_fixture_rows(array $groups, string $start_date, int $matches_per_day, string $default_time, string $default_venue): array {
    $rows = [];
    $date = new DateTime($start_date);
    $match_index = 0;

    foreach ($groups as $label => $teams) {
        $fixtures = round_robin($teams);
        foreach ($fixtures as $fixture) {
            $day_offset = intdiv($match_index, $matches_per_day);
            $scheduled_date = (clone $date)->modify("+{$day_offset} days");
            $rows[] = [
                'hometeamimg' => $fixture[0][1],
                'hometeam' => $fixture[0][0],
                'homescore' => null,
                'awayscore' => null,
                'awayteam' => $fixture[1][0],
                'awayteamimg' => $fixture[1][1],
                'venue' => $default_venue,
                'kotime' => $default_time,
                'date' => $scheduled_date->format('Y-m-d'),
                'group' => $label,
            ];
            $match_index++;
        }
    }

    return $rows;
}

function normalize_fixture_row(array $row, string $default_time, string $default_venue): array {
    $home = trim($row['hometeam'] ?? $row['home'] ?? $row['HomeTeam'] ?? '');
    $away = trim($row['awayteam'] ?? $row['away'] ?? $row['AwayTeam'] ?? '');
    if ($home === '' || $away === '') {
        return [];
    }

    $date = trim($row['date'] ?? $row['Date'] ?? '');
    $time = trim($row['time'] ?? $row['Time'] ?? $default_time);
    $venue = trim($row['venue'] ?? $row['Venue'] ?? $default_venue);
    $group = trim($row['group'] ?? $row['Group'] ?? '');
    $home_flag = trim($row['hometeamimg'] ?? $row['home_flag'] ?? $row['HomeFlag'] ?? $row['HomeTeamFlag'] ?? '');
    $away_flag = trim($row['awayteamimg'] ?? $row['away_flag'] ?? $row['AwayFlag'] ?? $row['AwayTeamFlag'] ?? '');

    if ($date !== '') {
        $date = date('Y-m-d', strtotime($date));
    } else {
        $date = null;
    }

    return [
        'hometeamimg' => $home_flag,
        'hometeam' => $home,
        'homescore' => null,
        'awayscore' => null,
        'awayteam' => $away,
        'awayteamimg' => $away_flag,
        'venue' => $venue,
        'kotime' => $time,
        'date' => $date,
        'group' => $group,
    ];
}

function parse_fixture_csv(string $payload, string $default_time, string $default_venue): array {
    $rows = [];
    $lines = preg_split('/\r\n|\r|\n/', trim($payload));
    if (count($lines) === 0) {
        return $rows;
    }

    $header = str_getcsv(array_shift($lines));
    foreach ($lines as $line) {
        if (trim($line) === '') {
            continue;
        }
        $data = str_getcsv($line);
        $row = [];
        foreach ($header as $index => $column) {
            $row[trim($column)] = $data[$index] ?? '';
        }
        $normalized = normalize_fixture_row($row, $default_time, $default_venue);
        if (!empty($normalized)) {
            $rows[] = $normalized;
        }
    }

    return $rows;
}

function parse_fixture_json(string $payload, string $default_time, string $default_venue): array {
    $rows = [];
    $data = json_decode($payload, true);
    if (!is_array($data)) {
        return $rows;
    }

    $items = $data;
    if (isset($data['fixtures']) && is_array($data['fixtures'])) {
        $items = $data['fixtures'];
    }

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $normalized = normalize_fixture_row($item, $default_time, $default_venue);
        if (!empty($normalized)) {
            $rows[] = $normalized;
        }
    }

    return $rows;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tournament_name = trim($_POST['tournament_name'] ?? '');
    $num_groups = (int)($_POST['num_groups'] ?? 0);
    $teams_per_group = (int)($_POST['teams_per_group'] ?? 0);
    $start_date = trim($_POST['start_date'] ?? '');
    $default_time = trim($_POST['default_time'] ?? '20:00');
    $default_venue = trim($_POST['default_venue'] ?? 'TBD');
    $matches_per_day = (int)($_POST['matches_per_day'] ?? 1);
    $teams_input = trim($_POST['teams_input'] ?? '');
    $fixture_source = $_POST['fixture_source'] ?? 'generate';
    $fixtures_url = trim($_POST['fixtures_url'] ?? '');
    $apply_changes = isset($_POST['apply_changes']);
    $truncate_schedule = isset($_POST['truncate_schedule']);

    if ($tournament_name === '') {
        $errors[] = 'Tournament name is required.';
    }
    if ($num_groups <= 0) {
        $errors[] = 'Number of groups must be greater than zero.';
    }
    if ($teams_per_group <= 1) {
        $errors[] = 'Teams per group must be greater than one.';
    }
    if ($matches_per_day <= 0) {
        $errors[] = 'Matches per day must be at least 1.';
    }
    if ($start_date === '') {
        $errors[] = 'Start date is required.';
    }

    if (empty($errors) && $fixture_source === 'generate') {
        $groups = parse_teams($teams_input, $num_groups, $teams_per_group);
        foreach ($groups as $label => $teams) {
            if (count($teams) !== $teams_per_group) {
                $errors[] = "Group {$label} has " . count($teams) . " teams (expected {$teams_per_group}).";
            }
        }
    }

    $fixtures = [];
    if (empty($errors) && $fixture_source === 'import') {
        $payload = '';
        if ($fixtures_url !== '') {
            $payload = @file_get_contents($fixtures_url);
            if ($payload === false) {
                $errors[] = 'Unable to fetch fixture data from the provided URL.';
            }
        } elseif (!empty($_FILES['fixtures_file']['tmp_name'])) {
            $payload = file_get_contents($_FILES['fixtures_file']['tmp_name']);
        } else {
            $errors[] = 'Please provide a fixtures URL or upload a CSV/JSON file.';
        }

        if (empty($errors)) {
            $is_json = str_ends_with(strtolower($fixtures_url), '.json') || str_starts_with(ltrim($payload), '{') || str_starts_with(ltrim($payload), '[');
            if ($is_json) {
                $fixtures = parse_fixture_json($payload, $default_time, $default_venue);
            } else {
                $fixtures = parse_fixture_csv($payload, $default_time, $default_venue);
            }

            if (empty($fixtures)) {
                $errors[] = 'Fixture data could not be parsed. Ensure the CSV/JSON contains at least Home and Away columns.';
            }
        }
    }

    if (empty($errors) && $fixture_source === 'generate') {
        $fixtures = build_fixture_rows($groups, $start_date, $matches_per_day, $default_time, $default_venue);
    }

    if (empty($errors)) {
        $fixture_preview = array_slice($fixtures, 0, 10);

        $sql_output[] = "CREATE TABLE IF NOT EXISTS tournament_config (";
        $sql_output[] = "  id INT AUTO_INCREMENT PRIMARY KEY,";
        $sql_output[] = "  tournament_name VARCHAR(255) NOT NULL,";
        $sql_output[] = "  num_groups INT NOT NULL,";
        $sql_output[] = "  teams_per_group INT NOT NULL,";
        $sql_output[] = "  start_date DATE NOT NULL,";
        $sql_output[] = "  default_time VARCHAR(10) NOT NULL,";
        $sql_output[] = "  default_venue VARCHAR(100) NOT NULL,";
        $sql_output[] = "  matches_per_day INT NOT NULL,";
        $sql_output[] = "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $sql_output[] = ");";

        if ($truncate_schedule) {
            $sql_output[] = "TRUNCATE TABLE live_match_schedule;";
        }

        $sql_output[] = sprintf(
            "INSERT INTO tournament_config (tournament_name, num_groups, teams_per_group, start_date, default_time, default_venue, matches_per_day) VALUES ('%s', %d, %d, '%s', '%s', '%s', %d);",
            addslashes($tournament_name),
            $num_groups,
            $teams_per_group,
            addslashes($start_date),
            addslashes($default_time),
            addslashes($default_venue),
            $matches_per_day
        );

        foreach ($fixtures as $fixture) {
            $date_value = $fixture['date'] === null ? 'NULL' : "'" . addslashes($fixture['date']) . "'";
            $sql_output[] = sprintf(
                "INSERT INTO live_match_schedule (hometeamimg, hometeam, homescore, awayscore, awayteam, awayteamimg, venue, kotime, date) VALUES ('%s', '%s', NULL, NULL, '%s', '%s', '%s', '%s', %s);",
                addslashes($fixture['hometeamimg']),
                addslashes($fixture['hometeam']),
                addslashes($fixture['awayteam']),
                addslashes($fixture['awayteamimg']),
                addslashes($fixture['venue']),
                addslashes($fixture['kotime']),
                $date_value
            );
        }

        if ($apply_changes && $con) {
            $create_table_sql = "CREATE TABLE IF NOT EXISTS tournament_config (" .
                "id INT AUTO_INCREMENT PRIMARY KEY," .
                "tournament_name VARCHAR(255) NOT NULL," .
                "num_groups INT NOT NULL," .
                "teams_per_group INT NOT NULL," .
                "start_date DATE NOT NULL," .
                "default_time VARCHAR(10) NOT NULL," .
                "default_venue VARCHAR(100) NOT NULL," .
                "matches_per_day INT NOT NULL," .
                "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP" .
                ");";

            if (!mysqli_query($con, $create_table_sql)) {
                $errors[] = 'Failed to create tournament_config table: ' . mysqli_error($con);
            } else {
                if ($truncate_schedule) {
                    mysqli_query($con, 'TRUNCATE TABLE live_match_schedule;');
                }

                $stmt = mysqli_prepare(
                    $con,
                    'INSERT INTO tournament_config (tournament_name, num_groups, teams_per_group, start_date, default_time, default_venue, matches_per_day) VALUES (?, ?, ?, ?, ?, ?, ?)'
                );

                if ($stmt) {
                    mysqli_stmt_bind_param(
                        $stmt,
                        'siisssi',
                        $tournament_name,
                        $num_groups,
                        $teams_per_group,
                        $start_date,
                        $default_time,
                        $default_venue,
                        $matches_per_day
                    );
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                } else {
                    $errors[] = 'Failed to prepare tournament_config insert: ' . mysqli_error($con);
                }

                $fixture_stmt = mysqli_prepare(
                    $con,
                    'INSERT INTO live_match_schedule (hometeamimg, hometeam, homescore, awayscore, awayteam, awayteamimg, venue, kotime, date) VALUES (?, ?, NULL, NULL, ?, ?, ?, ?, ?)'
                );

                if ($fixture_stmt) {
                    foreach ($fixtures as $fixture) {
                        mysqli_stmt_bind_param(
                            $fixture_stmt,
                            'sssssss',
                            $fixture['hometeamimg'],
                            $fixture['hometeam'],
                            $fixture['awayteam'],
                            $fixture['awayteamimg'],
                            $fixture['venue'],
                            $fixture['kotime'],
                            $fixture['date']
                        );
                        mysqli_stmt_execute($fixture_stmt);
                    }
                    mysqli_stmt_close($fixture_stmt);
                    $messages[] = 'Setup applied successfully. Review the SQL output below for auditing.';
                } else {
                    $errors[] = 'Failed to prepare fixture inserts: ' . mysqli_error($con);
                }
            }
        } elseif ($apply_changes && !$con) {
            $errors[] = 'Cannot apply changes because the database connection is unavailable.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches Setup Wizard">
    <meta name="author" content="James Henderson">
    <title><?= $page_title ?> - Hendy's Hunches</title>
    <link href="../ico/favicon.ico" rel="icon">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <img src="../img/hh-icon-2024.png" class="img-fluid bg-light mx-2" style="--bs-bg-opacity: 0.80" width="50px" alt="Hendy's Hunches">
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
                        <a class="nav-link" href="../dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../predictions.php">Submit Predictions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../rankings.php">Rankings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../how-it-works.php">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../about.php">About</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Setup Wizard</h1>
            <p class="text-muted mb-0">Generate SQL for tournaments and optionally apply it to your database.</p>
        </div>
    </div>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error) : ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($messages)) : ?>
        <div class="alert alert-success">
            <ul class="mb-0">
                <?php foreach ($messages as $message) : ?>
                    <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="card shadow-sm mb-4" enctype="multipart/form-data">
        <div class="card-body">
            <h2 class="h5">Competition Details</h2>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="tournament_name">Tournament name</label>
                    <input type="text" class="form-control" id="tournament_name" name="tournament_name" value="<?= htmlspecialchars($_POST['tournament_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="num_groups">Number of groups</label>
                    <input type="number" class="form-control" id="num_groups" name="num_groups" min="1" value="<?= htmlspecialchars($_POST['num_groups'] ?? '6') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="teams_per_group">Teams per group</label>
                    <input type="number" class="form-control" id="teams_per_group" name="teams_per_group" min="2" value="<?= htmlspecialchars($_POST['teams_per_group'] ?? '4') ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label" for="start_date">Group start date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="default_time">Default kick-off time</label>
                    <input type="time" class="form-control" id="default_time" name="default_time" value="<?= htmlspecialchars($_POST['default_time'] ?? '20:00') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="matches_per_day">Matches per day</label>
                    <input type="number" class="form-control" id="matches_per_day" name="matches_per_day" min="1" value="<?= htmlspecialchars($_POST['matches_per_day'] ?? '3') ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="default_venue">Default venue label</label>
                    <input type="text" class="form-control" id="default_venue" name="default_venue" value="<?= htmlspecialchars($_POST['default_venue'] ?? 'TBD') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="teams_input">Teams input (optional)</label>
                    <textarea class="form-control" id="teams_input" name="teams_input" rows="4" placeholder="Group A: Germany|flag-icons/24/germany.png, Scotland|flag-icons/24/scotland.png, Hungary|flag-icons/24/hungary.png, Switzerland|flag-icons/24/switzerland.png"><?= htmlspecialchars($_POST['teams_input'] ?? '') ?></textarea>
                    <div class="form-text">Provide one group per line. Use "Group A:" prefix optionally. Add a flag path after a pipe to store the flag.</div>
                </div>
            </div>

            <h2 class="h5 mt-4">Fixture Source</h2>
            <p class="text-muted">Generate fixtures automatically or import an authoritative fixture list from FIFA/UEFA in CSV or JSON format.</p>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="fixture_source" id="fixture_source_generate" value="generate" <?= ($_POST['fixture_source'] ?? 'generate') === 'generate' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="fixture_source_generate">Generate fixtures (round-robin)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="fixture_source" id="fixture_source_import" value="import" <?= ($_POST['fixture_source'] ?? '') === 'import' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="fixture_source_import">Import fixtures (CSV/JSON)</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="fixtures_url">Fixtures URL</label>
                    <input type="url" class="form-control" id="fixtures_url" name="fixtures_url" placeholder="https://example.com/fixtures.csv" value="<?= htmlspecialchars($_POST['fixtures_url'] ?? '') ?>">
                    <div class="form-text">Paste a direct CSV or JSON file URL. For FIFA/UEFA, export their fixtures to CSV/JSON and paste the hosted link.</div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="fixtures_file">Upload fixtures file</label>
                    <input type="file" class="form-control" id="fixtures_file" name="fixtures_file" accept=".csv,.json">
                    <div class="form-text">CSV columns: Home, Away, Date, Time, Venue, HomeTeamFlag, AwayTeamFlag, Group (case-insensitive).</div>
                </div>
            </div>

            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="truncate_schedule" name="truncate_schedule" <?= isset($_POST['truncate_schedule']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="truncate_schedule">Clear existing fixture schedule before insert</label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="apply_changes" name="apply_changes" <?= isset($_POST['apply_changes']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="apply_changes">Apply changes to database now</label>
            </div>

            <button type="submit" class="btn btn-primary">Generate SQL</button>
        </div>
    </form>

    <?php if (!empty($fixture_preview)) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Fixture Preview (first 10 matches)</h2>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Group</th>
                                <th>Home</th>
                                <th>Away</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fixture_preview as $fixture) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($fixture['group']) ?></td>
                                    <td><?= htmlspecialchars($fixture['hometeam']) ?></td>
                                    <td><?= htmlspecialchars($fixture['awayteam']) ?></td>
                                    <td><?= htmlspecialchars($fixture['date']) ?></td>
                                    <td><?= htmlspecialchars($fixture['kotime']) ?></td>
                                    <td><?= htmlspecialchars($fixture['venue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($sql_output)) : ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Generated SQL</h2>
                <p class="text-muted">Review and copy this output for auditing or manual execution.</p>
                <pre class="bg-light p-3 border rounded" style="max-height: 400px; overflow: auto;"><?php echo htmlspecialchars(implode("\n", $sql_output)); ?></pre>
            </div>
        </div>
    <?php endif; ?>
</main>

<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
