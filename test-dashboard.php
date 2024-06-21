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
                                </p>
                                <p><strong>18/06/2024 22:03 Update:</strong><br>We've reached the end of the 1st round of group matches. Still everything to play for so well done everyone so far!</p>
                                <p>Prizes will be awarded as:</p>
                                <table class="table table-striped table-sm table-info">
                                    <tbody>
                                        <tr>
                                            <td>1st:</td>
                                            <td>£60 (40% of the prize fund)</td>
                                        </tr>
                                        <tr>
                                            <td>2nd:</td>
                                            <td>£40 (27% of the prize fund)</td>
                                        </tr>
                                        <tr>
                                            <td>3rd:</td>
                                            <td>£25 (17% of the prize fund)</td>
                                        </tr>
                                        <tr>
                                            <td>4th:</td>
                                            <td>£15 (10% of the prize fund)</td>
                                        </tr>
                                        <tr>
                                            <td>5th:</td>
                                            <td>£10 (6% of the prize fund)</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p>Any positions shared will have prizes split equally.</p>
                                <hr>
                                <p><strong>15/06/2024 14:11 Update:</strong><br>Many apologies for the delay in updating the first result for the current rankings. This is done now. I must admit I was slightly scarred from a late night of drowning my sorrows! Good luck everyone.</p>
                                <hr>
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
                        <?php checkSubmitted() ?>
                        <?php displayMatchesRecorded() ?>
                        <div class="alert alert-light" role="alert">
                            <?php displayGroupMatchesPlayed() ?>
                            <?php displayRO16MatchesPlayed() ?>
                            <?php displayQFMatchesPlayed() ?>
                            <?php displaySFMatchesPlayed() ?>
                        </div>
                        <?php displayPayStatus() ?>
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

                <div class="row mt-4">
                    <div class="col-xxl-12 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Anonymous Player Poll</h5>
                                <div id="poll">
                                    <h6 id="question"></h6>
                                    <div id="answers">
                                        <!-- Answers will be dynamically added here -->
                                    </div>
                                    <div id="results">
                                        <!-- Results will be dynamically updated here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- End Right Column -->
        </div><!-- End Row -->

    </section>
        
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
        localStorage.setItem("hasVoted", true);
        disableVoting();
        fetchPollData(); // Refresh poll data after voting
    });
}

function renderPoll(data) {
    const question = data[0].question;
    document.getElementById("question").textContent = question;
    const answersDiv = document.getElementById("answers");
    const hasVoted = localStorage.getItem("hasVoted");
    answersDiv.innerHTML = ""; // Clear previous answers
    if (hasVoted) {
        answersDiv.innerHTML = "<p><i class='bi bi-check-circle-fill text-success'></i> You have voted on this poll.</p>";
    } else {
        data.forEach(answer => {
            const answerElem = document.createElement("div");
            answerElem.classList.add("form-check");
            answerElem.innerHTML = `
                <div class="my-4">
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
    const totalVotes = data.reduce((total, answer) => total + answer.count, 0);
    data.forEach(answer => {
        const resultElem = document.createElement("div");
        resultElem.innerHTML = `            
            <div class="progress" style="height: 50px">
                <div class="progress-bar bg-info" role="progressbar" style="width: ${(answer.count / totalVotes) * 100}%" aria-valuenow="${answer.count}" aria-valuemin="0" aria-valuemax="${totalVotes}"></div>
            </div>
            <p>${answer.answer}: ${answer.count}</p>
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