<?php
session_start();
$page_title = 'Dashboard Concept';

require_once __DIR__ . '/php/auth.php';
hh_require_login('index.php');

include "php/header.php";
include "php/navigation.php";

$stagePoints = [
    ['label' => 'Groups', 'points' => 42, 'max' => 42],
    ['label' => 'Round of 16', 'points' => 14, 'max' => 42],
    ['label' => 'Quarter-finals', 'points' => 7, 'max' => 42],
    ['label' => 'Semi-finals', 'points' => 1, 'max' => 42],
    ['label' => 'Final', 'points' => 7, 'max' => 42],
];

$winnerPicks = [
    ['team' => 'England', 'count' => 18, 'percent' => 100],
    ['team' => 'France', 'count' => 13, 'percent' => 72],
    ['team' => 'Spain', 'count' => 8, 'percent' => 44],
    ['team' => 'Germany', 'count' => 6, 'percent' => 33],
    ['team' => 'Portugal', 'count' => 4, 'percent' => 22],
];

$accuracy = [
    ['label' => 'Exact scores', 'value' => 6],
    ['label' => 'Right outcome', 'value' => 18],
    ['label' => 'One-score hits', 'value' => 9],
    ['label' => 'Misses', 'value' => 18],
];

$todayFixtures = [
    [
        'time' => '13:00',
        'home' => 'Mexico',
        'home_flag' => 'img/flags/mx.svg',
        'home_avg' => '1.21 avg',
        'away' => 'Japan',
        'away_flag' => 'img/flags/jp.svg',
        'away_avg' => '1.08 avg',
        'pick' => 'Most common pick: Mexico 1 - 0 Japan',
    ],
    [
        'time' => '17:00',
        'home' => 'Spain',
        'home_flag' => 'img/flags/es.svg',
        'home_avg' => '1.79 avg',
        'away' => 'England',
        'away_flag' => 'img/flags/gb-eng.svg',
        'away_avg' => '1.43 avg',
        'pick' => 'Most common pick: Spain 2 - 1 England',
    ],
    [
        'time' => '20:00',
        'home' => 'USA',
        'home_flag' => 'img/flags/us.svg',
        'home_avg' => '1.36 avg',
        'away' => 'Brazil',
        'away_flag' => 'img/flags/br.svg',
        'away_avg' => '1.92 avg',
        'pick' => 'Most common pick: USA 1 - 2 Brazil',
    ],
    [
        'time' => '23:00',
        'home' => 'Canada',
        'home_flag' => 'img/flags/ca.svg',
        'home_avg' => '0.88 avg',
        'away' => 'France',
        'away_flag' => 'img/flags/fr.svg',
        'away_avg' => '1.67 avg',
        'pick' => 'Most common pick: Canada 0 - 2 France',
    ],
];

$miniLeague = [
    ['rank' => 1, 'name' => 'Sarah', 'relationship' => 'Family', 'points' => 78, 'movement' => '+2', 'class' => 'concept-up', 'avatar' => 'img/football-kits/green-white-hoops.png'],
    ['rank' => 2, 'name' => 'Ketan', 'relationship' => 'Work', 'points' => 75, 'movement' => '+7', 'class' => 'concept-up', 'avatar' => 'img/football-kits/red-white-blue.png'],
    ['rank' => 3, 'name' => 'You', 'relationship' => 'Me', 'points' => 71, 'movement' => '+3', 'class' => 'concept-up', 'avatar' => 'img/football-kits/charcoal-gold.png'],
    ['rank' => 4, 'name' => 'Paul', 'relationship' => 'Friend', 'points' => 68, 'movement' => '-4', 'class' => 'concept-down', 'avatar' => 'img/football-kits/blue-yellow.png'],
    ['rank' => 5, 'name' => 'EJ', 'relationship' => 'Family', 'points' => 66, 'movement' => '0', 'class' => 'concept-neutral', 'avatar' => 'img/football-kits/claret-lightblue.png'],
];

?>

<main id="main" class="main">
    <div class="page-hero page-hero--dashboard dashboard-concept-hero">
        <div>
            <p class="eyebrow">Concept dashboard</p>
            <h1>Matchday Intelligence</h1>
            <p class="lead mb-0">A visual sketch of what the dashboard could become once the next tournament data is wired in.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="user.php?id=<?= $_SESSION['id'] ?>"><i class="bi bi-person-lines-fill"></i> My predictions</a>
            <a class="btn btn-outline-dark" href="rankings.php"><i class="bi bi-list-ol"></i> Rankings</a>
        </div>
    </div>

    <section class="section dashboard-concept" id="dashboardConcept">
        <div class="concept-grid concept-grid--top">
            <article class="concept-profile-card">
                <div class="concept-profile-card__kit">
                    <img src="img/football-kits/charcoal-gold.png" alt="Mock football strip avatar">
                </div>
                <div class="concept-profile-card__body">
                    <p class="eyebrow">Player card</p>
                    <h2>James Henderson</h2>
                    <p class="concept-subtle">Charcoal & gold kit · signed up 14 June 2024</p>
                    <div class="concept-profile-stats">
                        <span><strong>12th</strong>Rank</span>
                        <span><strong>71</strong>Points</span>
                        <span><strong>+3</strong>Move</span>
                    </div>
                    <dl class="concept-profile-details">
                        <div>
                            <dt>Favourite club</dt>
                            <dd>Notts County</dd>
                        </div>
                        <div>
                            <dt>Tournament winner</dt>
                            <dd>Scotland</dd>
                        </div>
                        <div>
                            <dt>Location</dt>
                            <dd>Nottingham</dd>
                        </div>
                        <div>
                            <dt>Field</dt>
                            <dd>eLearning Developer</dd>
                        </div>
                    </dl>
                </div>
            </article>

            <article class="concept-panel concept-panel--wide">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Matchday card</p>
                        <h2>Today's Fixtures</h2>
                    </div>
                    <span class="concept-pill">4 games</span>
                </div>
                <div class="fixture-stack">
                    <?php foreach ($todayFixtures as $fixture) : ?>
                        <article class="fixture-card-row">
                            <div class="fixture-card-row__meta">
                                <span class="fixture-card-row__time"><?= $fixture['time'] ?></span>
                            </div>
                            <div class="fixture-card-row__match">
                                <div class="fixture-card-row__team">
                                    <img src="<?= $fixture['home_flag'] ?>" alt="<?= $fixture['home'] ?> flag">
                                    <div>
                                        <strong><?= $fixture['home'] ?></strong>
                                        <span><?= $fixture['home_avg'] ?></span>
                                    </div>
                                </div>
                                <div class="fixture-card-row__divider">vs</div>
                                <div class="fixture-card-row__team fixture-card-row__team--away">
                                    <img src="<?= $fixture['away_flag'] ?>" alt="<?= $fixture['away'] ?> flag">
                                    <div>
                                        <strong><?= $fixture['away'] ?></strong>
                                        <span><?= $fixture['away_avg'] ?></span>
                                    </div>
                                </div>
                            </div>
                            <p class="fixture-card-row__pick"><?= $fixture['pick'] ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
                <p class="concept-subtle mb-0">A fuller version could add your own locked-in prediction beside the crowd pick for each fixture.</p>
            </article>
        </div>

        <div class="concept-grid concept-grid--insights">
            <article class="concept-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Prize race</p>
                        <h2>Chasing 5th</h2>
                    </div>
                    <span class="concept-pill concept-pill--gold">4 pts</span>
                </div>
                <div class="race-card">
                    <div>
                        <span>5th place</span>
                        <strong>Romina</strong>
                        <small>75 pts</small>
                    </div>
                    <i class="bi bi-arrow-down"></i>
                    <div>
                        <span>You</span>
                        <strong>12th</strong>
                        <small>71 pts</small>
                    </div>
                </div>
            </article>

            <article class="concept-panel concept-panel--wide">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Personal form</p>
                        <h2>Points by Stage</h2>
                    </div>
                    <span class="concept-pill">71 pts</span>
                </div>
                <div class="stage-bars">
                    <?php foreach ($stagePoints as $stage) : ?>
                        <?php $width = round(($stage['points'] / $stage['max']) * 100); ?>
                        <div class="stage-bar">
                            <div class="stage-bar__meta">
                                <span><?= $stage['label'] ?></span>
                                <strong><?= $stage['points'] ?> pts</strong>
                            </div>
                            <div class="stage-bar__track">
                                <span style="width: <?= $width ?>%"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="concept-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Prediction quality</p>
                        <h2>Accuracy Breakdown</h2>
                    </div>
                </div>
                <div class="accuracy-donut" aria-label="Accuracy chart mockup">
                    <div class="accuracy-donut__ring">
                        <div>
                            <strong>33</strong>
                            <span>scoring picks</span>
                        </div>
                    </div>
                    <ul class="accuracy-list">
                        <?php foreach ($accuracy as $item) : ?>
                            <li><span><?= $item['label'] ?></span><strong><?= $item['value'] ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </article>

            <article class="concept-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Crowd read</p>
                        <h2>Who Everyone Backed</h2>
                    </div>
                </div>
                <div class="crowd-bars">
                    <?php foreach ($winnerPicks as $pick) : ?>
                        <div class="crowd-bar">
                            <div class="crowd-bar__meta">
                                <span><?= $pick['team'] ?></span>
                                <strong><?= $pick['count'] ?></strong>
                            </div>
                            <div class="crowd-bar__track"><span style="width: <?= $pick['percent'] ?>%"></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="concept-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Momentum</p>
                        <h2>Biggest Movers</h2>
                    </div>
                </div>
                <ol class="movement-list">
                    <li><span class="concept-up"><i class="bi bi-caret-up-fill"></i> Ketan</span><strong>+7</strong></li>
                    <li><span class="concept-up"><i class="bi bi-caret-up-fill"></i> Sarah</span><strong>+5</strong></li>
                    <li><span class="concept-down"><i class="bi bi-caret-down-fill"></i> James</span><strong>-6</strong></li>
                    <li><span class="concept-down"><i class="bi bi-caret-down-fill"></i> Paul</span><strong>-4</strong></li>
                </ol>
            </article>
        </div>

        <div class="concept-grid concept-grid--mini-league">
            <article class="concept-panel concept-panel--wide">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Selected opponents</p>
                        <h2>My Mini-League</h2>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success"><i class="bi bi-sliders"></i> Manage list</button>
                </div>
                <div class="mini-league-table">
                    <?php foreach ($miniLeague as $player) : ?>
                        <div class="mini-league-row<?= $player['name'] === 'You' ? ' mini-league-row--me' : '' ?>">
                            <span class="mini-league-rank"><?= $player['rank'] ?></span>
                            <img class="mini-league-avatar" src="<?= $player['avatar'] ?>" alt="<?= $player['name'] ?> kit avatar">
                            <span class="mini-league-player">
                                <strong><?= $player['name'] ?></strong>
                                <small><?= $player['relationship'] ?></small>
                            </span>
                            <span class="mini-league-points"><?= $player['points'] ?> pts</span>
                            <span class="mini-league-move <?= $player['class'] ?>"><?= $player['movement'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="concept-panel mini-league-summary">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Rival watch</p>
                        <h2>Close Calls</h2>
                    </div>
                </div>
                <div class="rival-watch-card">
                    <p><strong>4 pts</strong><span>behind Ketan</span></p>
                    <p><strong>3 pts</strong><span>ahead of Paul</span></p>
                    <p><strong>5</strong><span>chosen opponents</span></p>
                </div>
                <p class="concept-subtle mb-0">A future version could let each player pin friends, family or colleagues here without affecting the main leaderboard.</p>
            </article>
        </div>

        <div class="concept-grid concept-grid--single">
            <article class="concept-panel">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Community</p>
                        <h2>Game Pulse</h2>
                    </div>
                </div>
                <div class="pulse-list">
                    <p><strong>52</strong><span>players registered</span></p>
                    <p><strong>47</strong><span>paid entries</span></p>
                    <p><strong>18</strong><span>backed England</span></p>
                    <p><strong>£150</strong><span>prize fund</span></p>
                </div>
            </article>
        </div>
    </section>
</main>

<?php include "php/footer.php" ?>
