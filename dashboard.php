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
                                <p><strong>14/07/2024 22:37 Update:</strong></p><p class="alert alert-success">Congratulations to our winners Jonathan (1st), Paul (2nd), David (3rd), Ketan (4th) and Romina (5th).</span></p><p>I hope everyone enjoyed playing the game and thank you all for taking part and raising money for charity.</p>
                                <hr>
                                <p><strong>14/07/2024 19:33 Update:</strong><br>Tonight's final average score prediction is:<br>Spain <span class="badge bg-danger">1.79</span> vs <span class="badge bg-primary">1.43</span> England</p>
                                <!-- <p><strong>10/07/2024 22:16 Update:</strong><br>You can now <a href="predictions.php" title="Submit predictions">submit your prediction</a> for the Final! Please do so before 7pm on <?= $GLOBALS['final_start_date'] ?> so you don't miss out!</p> -->
                                <!-- <p><strong>02/07/2024 22:59 Update:</strong><br>You can now <a href="predictions.php" title="Submit predictions">submit your predictions</a> for the Semi-Finals. Please do so before 7pm on <?= $GLOBALS['semi_final_start_date'] ?> so you don't miss out!</p> -->
                                <hr>
                                <!-- <p><strong>02/07/2024 22:59 Update:</strong><br>You can now <a href="predictions.php" title="Submit predictions">submit your predictions</a> for the Quarter-Finals. Please do so before 16.00 on <?= $GLOBALS['quarter_final_start_date'] ?> so you don't miss out!</p> -->
                                <!-- <hr> -->
                                <!-- <p><strong>30/06/2024 12:10 Update:</strong><br>As you may have noticed, I've had a slight hiccup in processing the first knockout results. Rest assured all your predictions are retained in the database and hopefully things will be resolved shortly.</p>                                                                
                                <hr> -->
                                <p><strong>18/06/2024 22:03 Update:</strong><br>Prizes will be awarded as:</p>
                                <table class="table table-striped table-sm">
                                    <tbody>
                                        <tr>
                                            <td>1st</td>
                                            <td>£60 (40% of the prize fund)</td>
                                        </tr>
                                        <tr>
                                            <td>2nd</td>
                                            <td>£40 (27% of the prize fund)</td>
                                        </tr>
                                        <tr>
                                            <td>3rd</td>
                                            <td>£25 (17% of the prize fund)</td>
                                        </tr>
                                        <tr>
                                            <td>4th</td>
                                            <td>£15 (10% of the prize fund)</td>
                                        </tr>
                                        <tr>
                                            <td>5th</td>
                                            <td>£10 (6% of the prize fund)</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p>Any positions shared will have prizes split equally.</p>
                                <hr>
                                <!-- <p><strong>15/06/2024 14:11 Update:</strong><br>Many apologies for the delay in updating the first result for the current rankings. This is done now. I must admit I was slightly scarred from a late night of drowning my sorrows! Good luck everyone.</p> -->
                                <!-- <hr> -->
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
                        <?php displayTodaysFixtures() ?>                                                                                                                         
                        <!-- <?php checkSubmitted() ?> -->
                        <?php displayMatchesRecorded() ?>
                        <div class="alert alert-light" role="alert">
                            <?php displayGroupMatchesPlayed() ?>
                            <?php displayRO16MatchesPlayed() ?>
                            <?php displayQFMatchesPlayed() ?>
                            <?php displaySFMatchesPlayed() ?>
                            <?php displayFinalMatchPlayed() ?>
                        </div>
                        <?php displayPayStatus() ?>
                        <!-- <p><i class="bi bi-envelope"></i> If you experience any issues, simply reply to your welcome email.</p> -->
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-xxl-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Winners</h5>
                                <?php displayTopRankings() ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-6 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Bottom 5</h5>
                                <?php displayBottomRankings() ?>
                            </div>
                        </div>
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
                    <div class="col-xxl-12 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <!-- <h5 class="card-title">Anonymous Poll #04</h5>
                                <div id="poll">
                                     <h6 id="question"></h6> class="p-3 mb-2 border border-danger border-2"
                                     <div id="answers">
                                          Answers will be dynamically added here
                                    </div>
                                    <div id="results">
                                          Results will be dynamically updated here
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- End Right Column -->
        </div><!-- End Row -->

    </section>

      <!-- Modal for Badge Earning Congratulations -->
  <div class="modal fade" id="congratsModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="congratsModalLabel" aria-hidden="true">
        <div class="confetti-container">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="congratsModalLabel">Hendy's Hunches 2024 Winners!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center mt-3">
                    <i class="bi bi-check-circle-fill" style="font-size: 60px; color: green;"></i>
                    <h1>Congratulations!</h1>
                    <p class="fs-5"></p>
                    <p>Well played to our winners, the top 5, of Hendy's Hunches 2024.</p>
                    <img src="" class="w-25 img-fluid" alt="...">
                    <!--
                    <div class="row g-0 bg-body-secondary position-relative">
                        <div class="col-md-4 mb-md-0 p-md-4">
                            <img src="images/logos/vp-logo-sq.png" class="w-100" alt="...">
                        </div>
                        <div class="col-md-8 p-4 ps-md-0">
                            <i class="bi bi-check-circle-fill" style="font-size: 60px; color: green;"></i>
                            <h1>Congratulations!</h1>
                            <p class="fs-5">You've just earned yourself a badge for completing this episode!</p>
                        </div>
                    </div>-->
                    <p class="mt-3">I hope you all enjoyed this game and thank you once again for taking part and raising money for charity.</p>
                </div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button> -->
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Close" id=""><i class="bi bi-arrow-right"></i> Continue</button>
                </div>
                </div>
            </div>
        </div>
    </div>
        
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    fetchPollData();

    document.addEventListener("change", function(event) {
        if (event.target.matches('input[type="radio"][name="answer"]')) {
            const answerId = parseInt(event.target.value);
            vote(answerId);
        }
    });
});

function fetchPollData() {
    fetch('php/poll.php')
        .then(response => response.json())
        .then(data => {
            renderPoll(data);
            updateResults(data);
        });
}

function vote(answerId) {
    fetch('php/poll.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'answerId=' + answerId
    }).then(() => {
        localStorage.setItem("hasVotedPoll04", true);
        disableVoting();
        fetchPollData(); // Refresh poll data after voting
    });
}

function renderPoll(data) {
    const question = data[0].question;
    document.getElementById("question").textContent = question;
    const answersDiv = document.getElementById("answers");
    const hasVoted = localStorage.getItem("hasVotedPoll04");
    answersDiv.innerHTML = ""; // Clear previous answers
    if (hasVoted) {
        answersDiv.innerHTML = "<p><i class='bi bi-check-circle-fill text-success'></i> You have voted on this poll.</p>";
    } else {
        data.forEach(answer => {
            const answerElem = document.createElement("div");
            answerElem.classList.add("form-check");
            answerElem.innerHTML = `
                <div class="my-3">
                <input class="form-check-input" type="radio" name="answer" id="answer${answer.id}" value="${answer.id}">
                <label class="form-check-label" for="answer${answer.id}">
                    ${answer.answer}
                </label>
                </div>
            `;
            answersDiv.appendChild(answerElem);
        });
    }
}

function updateResults(data) {
    const resultsDiv = document.getElementById("results");
    resultsDiv.innerHTML = ""; // Clear previous results
    //const totalVotes = data.reduce((total, answer) => total + answer.count, 0);
    const totalVotes = data.reduce((total, answer) => total + Number(answer.count), 0);
    data.forEach(answer => {
        const resultElem = document.createElement("div");
        resultElem.innerHTML = `            
            <div class="progress" style="height: 30px">
                <div class="progress-bar bg-info" role="progressbar" style="width: ${(answer.count / totalVotes) * 100}%" aria-valuenow="${answer.count}" aria-valuemin="0" aria-valuemax="${totalVotes}"></div>
            </div>
            <p style="text-align: right;">${answer.answer}: <strong>${answer.count}</strong></p>
        `;
        resultsDiv.appendChild(resultElem);
    });
}

function disableVoting() {
    const answersDiv = document.getElementById("answers");
    answersDiv.innerHTML = "<p><i class='bi bi-check-circle-fill text-success'></i> You have voted on this poll.</p>";
}    
</script>

<!-- Footer -->
<?php include "php/footer.php" ?>