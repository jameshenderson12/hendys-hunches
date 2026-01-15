<?php
session_start();
$page_title = 'How It Works';

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
      <h1>How it all works</h1>
        <!-- <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
          <li class="breadcrumb-item active">Part #3 - 11.30</li>
        </ol> -->
      </nav> 
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row align-items-center mb-4">
            <div class="col-lg-7">
                <p class="lead mb-2">A friendly walkthrough for new players and a quick refresher for veterans.</p>
                <p class="text-muted mb-0">Follow the steps below to get set up, understand the flow, and learn how the scoring works.</p>
            </div>
            <div class="col-lg-5 mt-3 mt-lg-0">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><span class="bi bi-lightning-charge text-warning me-2"></span>Quick start</h5>
                        <ol class="mb-0 ps-3">
                            <li>Register to play <span class="badge bg-success ms-2">Done</span></li>
                            <li>Login with your username and password <span class="badge bg-success ms-2">Done</span></li>
                            <li>Submit predictions before kick-off</li>
                            <li>Track your progress in the rankings</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title"><span class="bi bi-house-door text-primary me-2"></span>Home</h5>
                        <p class="mb-0">A central dashboard for the message board, Twitter feed, and daily stats. It fills up fast once matches kick off.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title"><span class="bi bi-pencil-square text-success me-2"></span>My Predictions</h5>
                        <p class="mb-0">Enter scorelines for every match. You can edit until 1 hour before kick-off, then scores lock in.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title"><span class="bi bi-trophy text-warning me-2"></span>Rankings</h5>
                        <p class="mb-0">Scores update after each match. You’ll appear once at least one prediction is submitted.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title"><span class="bi bi-info-circle text-info me-2"></span>How It Works</h5>
                        <p class="mb-0">This guide: gameplay flow, scoring details, and quick tips for max points.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title"><span class="bi bi-emoji-smile text-danger me-2"></span>About</h5>
                        <p class="mb-0">A light-hearted look at the background to the game and how it started.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title"><span class="bi bi-clock-history text-secondary me-2"></span>Timing</h5>
                        <p class="mb-0">Predictions lock 1 hour before kick-off. Update early to avoid missing points.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-3">Scoring</h2>
                <p class="mb-3">For any match, you can be awarded either 0, 1, 2, 3 or 7 points.</p>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <p class="mb-2"><span class="badge bg-secondary me-2">1 point</span>Correct home <em>or</em> away score.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <p class="mb-2"><span class="badge bg-primary me-2">2 points</span>Correct match outcome.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <p class="mb-2"><span class="badge bg-warning text-dark me-2">3 points</span>Correct outcome + one score.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <p class="mb-2"><span class="badge bg-success me-2">7 points</span>Exact scoreline.</p>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0">Examples of what you would be awarded, for a given prediction and result, are shown below.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr><th>You Predict</th><th>Match Result</th><th>Description</th><th>Points Awarded</th></tr>
                </thead>
                <tbody>
                    <tr class="table-success"><td>1 - 0</td><td>1 - 0</td><td>Home win, both correct scores and identical result predicted</td><td><span class="badge bg-success">7</span></td></tr>
                    <tr class="table-success"><td>1 - 2</td><td>1 - 2</td><td>Away win, both correct scores and identical result predicted</td><td><span class="badge bg-success">7</span></td></tr>
                    <tr class="table-success"><td>1 - 1</td><td>1 - 1</td><td>Draw, both correct scores and identical result predicted</td><td><span class="badge bg-success">7</span></td></tr>
                    <tr class="table-warning"><td>3 - 1</td><td>3 - 0</td><td>Home win and correct home score predicted</td><td><span class="badge bg-warning text-dark">3</span></td></tr>
                    <tr class="table-warning"><td>3 - 2</td><td>4 - 2</td><td>Home win and correct away score predicted</td><td><span class="badge bg-warning text-dark">3</span></td></tr>
                    <tr class="table-warning"><td>0 - 2</td><td>0 - 3</td><td>Away win and correct home score predicted</td><td><span class="badge bg-warning text-dark">3</span></td></tr>
                    <tr class="table-warning"><td>1 - 2</td><td>0 - 2</td><td>Away win and correct away score predicted</td><td><span class="badge bg-warning text-dark">3</span></td></tr>
                    <tr class="table-warning"><td>1 - 0</td><td>2 - 1</td><td>Home win predicted</td><td><span class="badge bg-primary">2</span></td></tr>
                    <tr class="table-warning"><td>0 - 3</td><td>1 - 2</td><td>Away win predicted</td><td><span class="badge bg-primary">2</span></td></tr>
                    <tr class="table-warning"><td>3 - 3</td><td>1 - 1</td><td>Draw predicted</td><td><span class="badge bg-primary">2</span></td></tr>
                    <tr class="table-warning"><td>0 - 0</td><td>0 - 1</td><td>Home score predicted</td><td><span class="badge bg-secondary">1</span></td></tr>
                    <tr class="table-warning"><td>1 - 1</td><td>0 - 1</td><td>Away score predicted</td><td><span class="badge bg-secondary">1</span></td></tr>
                    <tr class="table-danger"><td>1 - 0</td><td>0 - 2</td><td>Incorrect outcome and no scores predicted</td><td><span class="badge bg-dark">0</span></td></tr>
                    <tr class="table-danger"><td>0 - 2</td><td>1 - 1</td><td>Incorrect outcome and no scores predicted</td><td><span class="badge bg-dark">0</span></td></tr>
                    <tr class="table-danger"><td>3 - 3</td><td>2 - 1</td><td>Incorrect outcome and no scores predicted</td><td><span class="badge bg-dark">0</span></td></tr>
                </tbody>
            </table>
        </div>
    </section>
        
    </div>
  </div>
</main>

<!-- Footer -->
<?php include "php/footer.php" ?>
