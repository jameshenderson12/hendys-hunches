<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
$page_title = 'Dashboard';

if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
    header("Location: index.php");
    exit();
}

include "php/header.php";
include "php/navigation.php";
include "php/dashboard-items.php";

?>


<!-- Main Content Section -->
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between">
    <nav>
      <h1>Dashboard</h1>
        <!-- <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
          <li class="breadcrumb-item active">Part #3 - 11.30</li>
        </ol> -->
      </nav> 
    </div><!-- End Page Title -->

	<section class="section dashboard">
        <p class="lead">Use the dashboard to track your progress.</p>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-xxl-12 col-md-12">
                        <div class="card">
                            <!-- <div class="filter">
                                <a class="mx-3" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" data-bs-title="This measures this amount of time you've spent on...">
                                    <i class="bi bi-question-circle"></i>
                                </a>
                            </div> -->
                            <div class="card-body">
                                <h5 class="card-title">Profile Details</h5>
                                <?php displayPersonalInfo() ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12 col-md-12 mt-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Announcements</h5>
                                <?php displayCharityInformation() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Left Column -->

            <!-- Right Column -->
            <div class="col-lg-6">
                <div class="card">
                    <!-- <div class="filter">
                        <a class="mx-3" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" data-bs-title="This measures this amount of time you've spent on...">
                            <i class="bi bi-question-circle"></i>
                        </a>
                    </div> -->
                    <div class="card-body">
                        <h5 class="card-title">Game Status</h5>                        
                        <?php //displayPayStatus() ?>
                        
                        <p>If you've yet to do so, please pay £<?=$GLOBALS['signup_fee_formatted']?> to play before <?=$GLOBALS['competition_start_date']?>. <a class='btn btn-sm btn-primary' href='https://monzo.me/jamescolinhenderson/5.00?d=Hendy%27s%20Hunches%20-%20%5BYour%20Name%5D' role='button' target='_blank'><i class='bi bi-credit-card-fill'></i> Pay sign-up fee</a></p>
                        <?php checkSubmitted() ?>
                        <?php displayMatchesRecorded() ?>
                        <div class="alert alert-light" role="alert">
                            <?php displayGroupMatchesPlayed() ?>
                            <?php displayRO16MatchesPlayed() ?>
                            <?php displayQFMatchesPlayed() ?>
                            <?php displaySFMatchesPlayed() ?>
                        </div>
                        <p><i class="bi bi-envelope"></i> If you experience any issues, simply reply to your welcome email.</p>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-xxl-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Biggest Climbers</h5>
                                <?php displayBestMovers() ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Biggest Droppers</h5>
                                <?php displayWorstMovers() ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-xxl-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Current Top 5</h5>
                                <?php displayTopRankings() ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Current Bottom 5</h5>
                                <?php displayBottomRankings() ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- End Right Column -->
        </div><!-- End Row -->

    </section>
        
</main>

<!-- Footer -->
<?php include "php/footer.php" ?>