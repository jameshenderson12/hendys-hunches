<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
$page_title = 'Dashboard';

require_once __DIR__ . '/php/auth.php';
hh_require_login('index.php');

include "php/header.php";
include "php/navigation.php";
include "php/dashboard-items.php";

?>


<!-- Main Content Section -->
<main id="main" class="main">

    <div class="page-hero page-hero--dashboard">
        <div>
            <p class="eyebrow">Matchday control room</p>
            <h1>Dashboard</h1>
            <p class="lead mb-0">Track your progress, scan the latest updates and keep an eye on the table.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="rankings.php"><i class="bi bi-list-ol"></i> Rankings</a>
            <a class="btn btn-outline-dark" href="user.php?id=<?php echo $_SESSION['id']; ?>"><i class="bi bi-person-lines-fill"></i> My predictions</a>
        </div>
    </div><!-- End Page Title -->

	<section class="section dashboard dashboard-refresh">
        <div class="dashboard-command-strip">
            <a class="dashboard-command" href="rankings.php">
                <span class="dashboard-command__icon"><i class="bi bi-list-ol"></i></span>
                <span>
                    <strong>Leaderboard</strong>
                    <small>See the table and prize places.</small>
                </span>
            </a>
            <a class="dashboard-command" href="tournament-groups.php">
                <span class="dashboard-command__icon"><i class="bi bi-grid-3x3-gap"></i></span>
                <span>
                    <strong>Competition</strong>
                    <small>Review groups and knockouts.</small>
                </span>
            </a>
            <a class="dashboard-command" href="how-it-works.php">
                <span class="dashboard-command__icon"><i class="bi bi-question-circle"></i></span>
                <span>
                    <strong>Scoring</strong>
                    <small>Check the points rules.</small>
                </span>
            </a>
        </div>

        <div class="dashboard-grid">
            <section class="dashboard-panel dashboard-panel--profile">
                <h2 class="card-title">Your Tournament Card</h2>
                <?php displayPersonalInfo() ?>
            </section>

            <section class="dashboard-panel dashboard-panel--status">
                <div class="dashboard-panel__header">
                    <div>
                        <p class="eyebrow">Live board</p>
                        <h2>Game Status</h2>
                    </div>
                    <a class="btn btn-sm btn-primary" href="predictions.php"><i class="bi bi-pencil-square"></i> Predict</a>
                </div>
                <div class="dashboard-status__fixture">
                    <?php displayTodaysFixtures() ?>
                </div>
                <?php displayMatchesRecorded() ?>
                <div class="dashboard-progress" role="region" aria-label="Competition progress">
                    <?php displayGroupMatchesPlayed() ?>
                    <?php displayRO16MatchesPlayed() ?>
                    <?php displayQFMatchesPlayed() ?>
                    <?php displaySFMatchesPlayed() ?>
                    <?php displayFinalMatchPlayed() ?>
                </div>
                <?php displayPayStatus() ?>
            </section>

            <section class="dashboard-panel dashboard-panel--announcements">
                <div class="dashboard-panel__header">
                    <div>
                        <p class="eyebrow">Noticeboard</p>
                        <h2>Announcements</h2>
                    </div>
                </div>
                <div class="dashboard-updates">
                    <article class="dashboard-update dashboard-update--winner">
                        <span class="dashboard-update__date">14/07/2024 22:37</span>
                        <h3>Winners confirmed</h3>
                        <p class="alert alert-success">Congratulations to our winners Jonathan (1st), Paul (2nd), David (3rd), Ketan (4th) and Romina (5th).</p>
                        <p>I hope everyone enjoyed playing the game and thank you all for taking part and raising money for charity.</p>
                    </article>

                    <article class="dashboard-update">
                        <span class="dashboard-update__date">14/07/2024 19:33</span>
                        <h3>Final average score prediction</h3>
                        <p>Spain <span class="badge bg-danger">1.79</span> vs <span class="badge bg-primary">1.43</span> England</p>
                    </article>

                    <article class="dashboard-update">
                        <span class="dashboard-update__date">18/06/2024 22:03</span>
                        <h3>Prize split</h3>
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
                    </article>
                </div>
                <div class="dashboard-charity">
                    <?php displayCharityInformation() ?>
                </div>
            </section>

            <aside class="dashboard-panel dashboard-panel--leaderboard">
                <div class="dashboard-panel__header">
                    <div>
                        <p class="eyebrow">Form guide</p>
                        <h2>Leaderboard Pulse</h2>
                    </div>
                    <a class="btn btn-sm btn-outline-success" href="rankings.php">Full table</a>
                </div>
                <div class="dashboard-leaderboard-grid">
                    <div>
                        <h3>Winners</h3>
                        <?php displayTopRankings() ?>
                    </div>
                    <div>
                        <h3>Bottom 5</h3>
                        <?php displayBottomRankings() ?>
                    </div>
                    <div>
                        <h3>Biggest Climbers</h3>
                        <?php displayBestMovers() ?>
                    </div>
                    <div>
                        <h3>Biggest Droppers</h3>
                        <?php displayWorstMovers() ?>
                    </div>
                </div>
            </aside>
        </div>
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
