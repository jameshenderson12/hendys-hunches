<?php
session_start();
$page_title = 'Admin Functions';

require_once dirname(__DIR__) . '/php/auth.php';
require_once dirname(__DIR__) . '/php/config.php';
require_once dirname(__DIR__) . '/php/process.php';

hh_require_admin('../dashboard.php');

include '../php/db-connect.php';

function hh_admin_table_exists(mysqli $con, string $table): bool
{
    $escaped = mysqli_real_escape_string($con, $table);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$escaped}'");

    if (!$result) {
        return false;
    }

    $exists = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);

    return $exists;
}

function hh_admin_fetch_all(mysqli $con, string $sql): array
{
    $result = mysqli_query($con, $sql);
    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_free_result($result);

    return $rows;
}

function hh_admin_preview_table(mysqli $con, string $table, int $limit = 30): array
{
    $rows = [];
    $columns = [];

    $result = mysqli_query($con, "SELECT * FROM {$table} LIMIT {$limit}");
    if (!$result) {
        return ['columns' => $columns, 'rows' => $rows];
    }

    $fieldInfo = mysqli_fetch_fields($result);
    foreach ($fieldInfo as $field) {
        $columns[] = $field->name;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_free_result($result);

    return ['columns' => $columns, 'rows' => $rows];
}

$messages = [];
$errors = [];

$tableOptions = [
    'live_user_information' => 'User information',
    'live_match_schedule' => 'Match schedule',
    'live_match_results' => 'Match results',
    'live_user_predictions_groups' => 'Group predictions',
    'live_user_predictions_ro16' => 'Round of 16 predictions',
    'live_user_predictions_qf' => 'Quarter-final predictions',
    'live_user_predictions_sf' => 'Semi-final predictions',
    'live_user_predictions_final' => 'Final predictions',
    'live_fanzone_posts' => 'Fan Zone posts',
];

$stageOptions = [
    'live_user_predictions_groups' => 'Group stage points',
    'live_user_predictions_ro16' => 'Round of 16 points',
    'live_user_predictions_qf' => 'Quarter-final points',
    'live_user_predictions_sf' => 'Semi-final points',
    'live_user_predictions_final' => 'Final points',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['admin_action'] ?? '');

    if ($action === 'recalculate_all') {
        compareValues();
        compareRO16Values();
        compareQFValues();
        compareSFValues();
        compareFinalValues();
        $messages[] = 'All prediction points and ranking movement have been recalculated.';
    } elseif ($action === 'reset_game_data') {
        $queries = [
            "TRUNCATE TABLE live_match_results",
            "UPDATE live_match_schedule SET homescore = NULL, awayscore = NULL",
            "UPDATE live_user_predictions_groups SET points_total = 0",
            "UPDATE live_user_predictions_ro16 SET points_total = 0",
            "UPDATE live_user_predictions_qf SET points_total = 0",
            "UPDATE live_user_predictions_sf SET points_total = 0",
            "UPDATE live_user_predictions_final SET points_total = 0",
            "UPDATE live_user_information SET lastpos = startpos, currpos = startpos",
        ];

        mysqli_begin_transaction($con);
        try {
            foreach ($queries as $query) {
                if (!mysqli_query($con, $query)) {
                    throw new RuntimeException(mysqli_error($con));
                }
            }
            mysqli_commit($con);
            $messages[] = 'Match results, schedule scores and player points have been reset.';
        } catch (Throwable $exception) {
            mysqli_rollback($con);
            $errors[] = 'Reset failed: ' . $exception->getMessage();
        }
    } elseif ($action === 'adjust_points') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $stageTable = (string) ($_POST['stage_table'] ?? '');
        $pointsDelta = (int) ($_POST['points_delta'] ?? 0);

        if ($username === '' || !isset($stageOptions[$stageTable])) {
            $errors[] = 'Please choose a player and a valid scoring stage.';
        } elseif ($pointsDelta === 0) {
            $errors[] = 'Please enter a points adjustment other than zero.';
        } else {
            $stmt = mysqli_prepare($con, "UPDATE {$stageTable} SET points_total = points_total + ? WHERE username = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'is', $pointsDelta, $username);
                mysqli_stmt_execute($stmt);
                $affected = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);

                if ($affected > 0) {
                    updateMoveStatus();
                    $messages[] = 'Points updated for ' . htmlspecialchars($username, ENT_QUOTES) . '.';
                } else {
                    $errors[] = 'No matching player row was found in that stage table.';
                }
            } else {
                $errors[] = 'Could not prepare the points adjustment.';
            }
        }
    } elseif ($action === 'set_payment_status') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $paymentStatus = (string) ($_POST['haspaid'] ?? '');

        if ($userId <= 0 || !in_array($paymentStatus, ['Yes', 'No'], true)) {
            $errors[] = 'Please choose a valid user and payment status.';
        } else {
            $stmt = mysqli_prepare($con, "UPDATE live_user_information SET haspaid = ? WHERE id = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $paymentStatus, $userId);
                mysqli_stmt_execute($stmt);
                $affected = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);

                if ($affected >= 0) {
                    $messages[] = 'Payment status updated.';
                } else {
                    $errors[] = 'Payment status could not be updated.';
                }
            } else {
                $errors[] = 'Could not prepare the payment status update.';
            }
        }
    } elseif ($action === 'clear_fanzone') {
        if (!hh_admin_table_exists($con, 'live_fanzone_posts')) {
            $errors[] = 'The Fan Zone table does not exist in this database.';
        } elseif (mysqli_query($con, "DELETE FROM live_fanzone_posts")) {
            $messages[] = 'The Fan Zone board has been cleared.';
        } else {
            $errors[] = 'The Fan Zone board could not be cleared.';
        }
    }
}

$selectedTable = (string) ($_GET['table'] ?? 'live_user_information');
if (!isset($tableOptions[$selectedTable])) {
    $selectedTable = 'live_user_information';
}

$users = hh_admin_fetch_all(
    $con,
    "SELECT id, username, firstname, surname, haspaid FROM live_user_information ORDER BY surname ASC, firstname ASC"
);

$snapshot = [
    'users' => 0,
    'fixtures' => 0,
    'results' => 0,
    'fanzone' => 0,
];

$countQueries = [
    'users' => "SELECT COUNT(*) AS total FROM live_user_information",
    'fixtures' => "SELECT COUNT(*) AS total FROM live_match_schedule",
    'results' => "SELECT COUNT(*) AS total FROM live_match_results",
];

foreach ($countQueries as $key => $query) {
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $snapshot[$key] = (int) ($row['total'] ?? 0);
        mysqli_free_result($result);
    }
}

if (hh_admin_table_exists($con, 'live_fanzone_posts')) {
    $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM live_fanzone_posts WHERE is_deleted = 0");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $snapshot['fanzone'] = (int) ($row['total'] ?? 0);
        mysqli_free_result($result);
    }
}

$fixturePreview = hh_admin_fetch_all(
    $con,
    "SELECT id, hometeam, awayteam, homescore, awayscore, date, kotime, stage FROM live_match_schedule ORDER BY date ASC, kotime ASC LIMIT 8"
);

$tablePreview = hh_admin_preview_table($con, $selectedTable, 30);

mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html">
    <meta name="description" content="Hendy's Hunches administration hub">
    <meta name="author" content="James Henderson">
    <title><?= $page_title ?> - Hendy's Hunches</title>
    <link href="../ico/favicon.ico" rel="icon">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <style>
      .admin-shell {
        width: min(1320px, calc(100% - 32px));
        margin: 18px auto 28px;
      }
      .admin-grid {
        display: grid;
        gap: 18px;
      }
      .admin-grid--two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
      .admin-grid--three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
      .admin-card {
        padding: 22px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(251, 252, 248, 0.96);
        box-shadow: 0 18px 38px rgba(0, 0, 0, 0.14);
      }
      .admin-card h2,
      .admin-card h3 {
        margin: 0 0 12px;
        font-weight: 900;
      }
      .admin-kpi {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }
      .admin-kpi__item {
        padding: 16px;
        border: 1px solid var(--hh-line);
        border-radius: 8px;
        background: rgba(143, 102, 216, 0.08);
      }
      .admin-kpi__item strong,
      .admin-kpi__item span {
        display: block;
      }
      .admin-kpi__item strong {
        font-size: 1.6rem;
        line-height: 1;
      }
      .admin-kpi__item span {
        margin-top: 6px;
        color: var(--hh-muted);
        font-weight: 700;
      }
      .admin-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
      }
      .admin-card form {
        display: grid;
        gap: 14px;
      }
      .admin-danger {
        border-color: rgba(214, 64, 69, 0.18);        
        /* background: rgba(214, 64, 69, 0.04); */
      }
      .admin-note {
        margin: 0;
        color: var(--hh-muted);
        font-size: 0.92rem;
      }
      .admin-table-preview {
        overflow-x: auto;
      }
      .admin-table-preview table {
        margin-bottom: 0;
        white-space: nowrap;
      }
      @media (max-width: 991px) {
        .admin-grid--two,
        .admin-grid--three,
        .admin-kpi {
          grid-template-columns: 1fr;
        }
      }
    </style>
</head>
<body>
<?php hh_render_dev_banner('../php/logout.php'); ?>

<div class="admin-shell">
    <div class="page-hero page-hero--admin">
        <div>
            <p class="eyebrow">Admin control room</p>
            <h1>Game Functions</h1>
            <p class="lead mb-0">A central place to run the core admin actions that steer the game and keep the data in shape.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="results.php"><i class="bi bi-trophy"></i> Record results</a>
            <a class="btn btn-outline-dark" href="../dashboard.php"><i class="bi bi-grid"></i> Back to dashboard</a>
        </div>
    </div>

    <?php foreach ($messages as $message) : ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($message, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $error) : ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <section class="admin-grid">
        <div class="admin-card">
            <h2>System Snapshot</h2>
            <div class="admin-kpi">
                <div class="admin-kpi__item"><strong><?= $snapshot['users'] ?></strong><span>registered players</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['fixtures'] ?></strong><span>scheduled fixtures</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['results'] ?></strong><span>result rows recorded</span></div>
                <div class="admin-kpi__item"><strong><?= $snapshot['fanzone'] ?></strong><span>live Fan Zone posts</span></div>
            </div>
        </div>

        <div class="admin-grid admin-grid--three">
            <div class="admin-card">
                <h3>Record Football Scores</h3>
                <p class="admin-note">Open the existing results capture page and record the actual match results against the fixture list.</p>
                <div class="admin-actions mt-3">
                    <a class="btn btn-primary" href="results.php"><i class="bi bi-plus-circle"></i> Open results page</a>
                </div>
            </div>

            <div class="admin-card">
                <h3>Recalculate Game</h3>
                <p class="admin-note">Re-run the scoring logic across all prediction stages and refresh ranking movement.</p>
                <form method="post" class="mt-3">
                    <input type="hidden" name="admin_action" value="recalculate_all">
                    <button type="submit" class="btn btn-outline-success"><i class="bi bi-arrow-repeat"></i> Recalculate all points</button>
                </form>
            </div>

            <div class="admin-card admin-danger">
                <h3>Reset Game Data</h3>
                <p class="admin-note">Clear recorded results and fixture scores, zero all stage points and reset all positions back to their starting rank.</p>
                <form method="post" class="mt-3" onsubmit="return confirm('Reset all recorded results and points? This cannot be undone easily.');">
                    <input type="hidden" name="admin_action" value="reset_game_data">
                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-exclamation-triangle"></i> Reset game data</button>
                </form>
            </div>
        </div>

        <div class="admin-grid admin-grid--two">
            <div class="admin-card">
                <h3>Add or Deduct Points</h3>
                <form method="post">
                    <input type="hidden" name="admin_action" value="adjust_points">
                    <div>
                        <label class="form-label" for="username">Player</label>
                        <select class="form-select" id="username" name="username" required>
                            <option value="">Choose a player</option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>">
                                    <?= htmlspecialchars($user['firstname'] . ' ' . $user['surname'] . ' (' . $user['username'] . ')', ENT_QUOTES) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="stage_table">Scoring stage</label>
                        <select class="form-select" id="stage_table" name="stage_table" required>
                            <?php foreach ($stageOptions as $tableName => $label) : ?>
                                <option value="<?= htmlspecialchars($tableName, ENT_QUOTES) ?>"><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="points_delta">Points adjustment</label>
                        <input class="form-control" type="number" id="points_delta" name="points_delta" min="-50" max="50" step="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-slash-minus"></i> Apply points change</button>
                </form>
            </div>

            <div class="admin-card">
                <h3>Update Player Status</h3>
                <form method="post">
                    <input type="hidden" name="admin_action" value="set_payment_status">
                    <div>
                        <label class="form-label" for="user_id">Player</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Choose a player</option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?= (int) $user['id'] ?>">
                                    <?= htmlspecialchars($user['firstname'] . ' ' . $user['surname'] . ' - paid: ' . $user['haspaid'], ENT_QUOTES) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="haspaid">Payment status</label>
                        <select class="form-select" id="haspaid" name="haspaid" required>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-success"><i class="bi bi-cash-coin"></i> Save payment status</button>
                </form>
            </div>
        </div>

        <div class="admin-grid admin-grid--two">
            <div class="admin-card">
                <h3>Quick Table Browser</h3>
                <form method="get" class="mb-3">
                    <div>
                        <label class="form-label" for="table">Database table</label>
                        <select class="form-select" id="table" name="table" onchange="this.form.submit()">
                            <?php foreach ($tableOptions as $tableName => $label) : ?>
                                <option value="<?= htmlspecialchars($tableName, ENT_QUOTES) ?>"<?= $selectedTable === $tableName ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($label, ENT_QUOTES) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <div class="admin-table-preview">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <?php foreach ($tablePreview['columns'] as $column) : ?>
                                    <th><?= htmlspecialchars($column, ENT_QUOTES) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($tablePreview['rows'] === []) : ?>
                                <tr><td colspan="<?= max(1, count($tablePreview['columns'])) ?>">No rows found.</td></tr>
                            <?php else : ?>
                                <?php foreach ($tablePreview['rows'] as $row) : ?>
                                    <tr>
                                        <?php foreach ($tablePreview['columns'] as $column) : ?>
                                            <td><?= htmlspecialchars((string) ($row[$column] ?? ''), ENT_QUOTES) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-card admin-danger">
                <h3>Other High-Risk Actions</h3>
                <p class="admin-note">These are the sort of controls that belong in this hub, but they’re best kept explicit and deliberate.</p>
                <ul class="mb-3">
                    <li>Clear the Fan Zone board</li>
                    <li>Blank prediction tables for a fresh tournament setup</li>
                    <li>Edit schedule details or rescore a specific match</li>
                    <li>Open a fuller table editor for key datasets</li>
                </ul>
                <form method="post" onsubmit="return confirm('Clear every Fan Zone post?');">
                    <input type="hidden" name="admin_action" value="clear_fanzone">
                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash3"></i> Clear Fan Zone board</button>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <h3>Upcoming Fixtures</h3>
            <div class="admin-table-preview">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Home</th>
                            <th>Away</th>
                            <th>Score</th>
                            <th>Date</th>
                            <th>KO</th>
                            <th>Stage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fixturePreview as $fixture) : ?>
                            <tr>
                                <td><?= (int) $fixture['id'] ?></td>
                                <td><?= htmlspecialchars($fixture['hometeam'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($fixture['awayteam'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars((string) ($fixture['homescore'] ?? '-'), ENT_QUOTES) ?> - <?= htmlspecialchars((string) ($fixture['awayscore'] ?? '-'), ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars((string) $fixture['date'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars((string) $fixture['kotime'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars((string) $fixture['stage'], ENT_QUOTES) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<?php include "../php/footer.php"; ?>

<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
