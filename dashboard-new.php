<?php
session_start();
$page_title = 'Dashboard Concept';

require_once __DIR__ . '/php/auth.php';
hh_require_login('index.php');

include "php/header.php";
$nav_logo_variant = 'concept';
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

$miniLeague = [
    ['rank' => 1, 'name' => 'Sarah', 'relationship' => 'Family', 'points' => 78, 'movement' => '+2', 'class' => 'concept-up', 'avatar' => 'football-kits/green-white-hoops.png'],
    ['rank' => 2, 'name' => 'Ketan', 'relationship' => 'Work', 'points' => 75, 'movement' => '+7', 'class' => 'concept-up', 'avatar' => 'football-kits/red-white-blue.png'],
    ['rank' => 3, 'name' => 'You', 'relationship' => 'Me', 'points' => 71, 'movement' => '+3', 'class' => 'concept-up', 'avatar' => 'football-kits/charcoal-gold.png'],
    ['rank' => 4, 'name' => 'Paul', 'relationship' => 'Friend', 'points' => 68, 'movement' => '-4', 'class' => 'concept-down', 'avatar' => 'football-kits/blue-yellow.png'],
    ['rank' => 5, 'name' => 'EJ', 'relationship' => 'Family', 'points' => 66, 'movement' => '0', 'class' => 'concept-neutral', 'avatar' => 'football-kits/claret-lightblue.png'],
];

$themeOptions = [
    ['name' => 'Forest', 'theme' => 'forest'],
    ['name' => 'Atlantic', 'theme' => 'atlantic'],
    ['name' => 'Cherry', 'theme' => 'cherry'],
    ['name' => 'Gold Cup', 'theme' => 'gold'],
    ['name' => 'Mono', 'theme' => 'mono'],
    ['name' => 'Teal', 'theme' => 'teal'],
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
            <a class="btn btn-primary" href="dashboard.php"><i class="bi bi-speedometer2"></i> Current dashboard</a>
            <a class="btn btn-outline-dark" href="rankings.php"><i class="bi bi-list-ol"></i> Rankings</a>
        </div>
    </div>

    <section class="section dashboard-concept" data-concept-theme="forest" id="dashboardConcept">
        <div class="concept-tools">
            <p class="concept-note"><i class="bi bi-info-circle"></i> Static concept only. Values are sample data for layout and design review.</p>
            <div class="concept-theme-switcher" aria-label="Dashboard concept colour scheme">
                <?php foreach ($themeOptions as $index => $option) : ?>
                    <button type="button" class="concept-theme-button" data-theme="<?= $option['theme'] ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>">
                        <span class="concept-theme-button__swatch concept-theme-button__swatch--<?= $option['theme'] ?>"></span>
                        <?= $option['name'] ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <article class="concept-panel brand-direction-panel">
            <div class="concept-panel__header">
                <div>
                    <p class="eyebrow">Logo directions</p>
                    <h2>Alternative Hendy's Hunches Marks</h2>
                </div>
                <span class="concept-pill">Visual only</span>
            </div>
            <div class="brand-section-label">
                <h3>Football and trophy led</h3>
                <p>Built around simpler marks that should still read at navbar, favicon and avatar sizes.</p>
            </div>
            <div class="brand-direction-grid brand-direction-grid--icon-led">
                <div class="brand-logo-card brand-logo-card--icon-ball">
                    <div class="brand-scale-logo brand-scale-logo--stacked">
                        <span class="brand-symbol brand-symbol--football" aria-hidden="true"><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>HH Match Ball</h3>
                        <p>A very direct football mark, with the initials large enough to survive at small sizes.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-trophy">
                    <div class="brand-scale-logo">
                        <span class="brand-symbol brand-symbol--trophy" aria-hidden="true"><i class="bi bi-trophy-fill"></i><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>HH Trophy Cup</h3>
                        <p>Prize-focused and simple, with the letters sitting inside the cup shape.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-cupball">
                    <div class="brand-scale-logo">
                        <span class="brand-symbol brand-symbol--cupball" aria-hidden="true"><i class="bi bi-trophy-fill"></i><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Cup And Ball</h3>
                        <p>A hybrid trophy and football idea that feels more game-like than corporate.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-orbit">
                    <div class="brand-scale-logo brand-scale-logo--stacked">
                        <span class="brand-symbol brand-symbol--orbit" aria-hidden="true"><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Prediction Curve</h3>
                        <p>A ball with a curved flight line, suggesting the little hunch before kick-off.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-shieldball">
                    <div class="brand-scale-logo">
                        <span class="brand-symbol brand-symbol--shieldball" aria-hidden="true"><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Ball Shield</h3>
                        <p>Football club language, but still reduced enough for the navbar.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-medal">
                    <div class="brand-scale-logo">
                        <span class="brand-symbol brand-symbol--medal" aria-hidden="true"><i class="bi bi-trophy-fill"></i><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Winner Medal</h3>
                        <p>A reward-led mark that could also become a badge for leaderboard winners.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-net">
                    <div class="brand-scale-logo brand-scale-logo--stacked">
                        <span class="brand-symbol brand-symbol--netball" aria-hidden="true"><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Net Breaker</h3>
                        <p>A football over pitch/net lines, with the HH kept central and readable.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-crown">
                    <div class="brand-scale-logo">
                        <span class="brand-symbol brand-symbol--crowncup" aria-hidden="true"><i class="bi bi-trophy-fill"></i><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Champion Cup</h3>
                        <p>A bolder trophy option, useful if the prize and table race should lead the identity.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-minimal">
                    <div class="brand-scale-logo">
                        <span class="brand-symbol brand-symbol--minimal-ball" aria-hidden="true"><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Minimal Ball</h3>
                        <p>The simplest football icon here, probably the strongest candidate for very small sizes.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-badgecup">
                    <div class="brand-scale-logo">
                        <span class="brand-symbol brand-symbol--badgecup" aria-hidden="true"><i class="bi bi-trophy-fill"></i><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Cup Badge</h3>
                        <p>A contained trophy badge that would translate neatly to app icons and social avatars.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-scoreball">
                    <div class="brand-scale-logo brand-scale-logo--scoreball">
                        <span class="brand-symbol brand-symbol--scoreball" aria-hidden="true"><span>HH</span></span>
                        <strong>2 - 1</strong>
                    </div>
                    <div>
                        <h3>Score Ball</h3>
                        <p>Combines the football with a prediction score, so the mechanic is instantly visible.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--icon-laurel">
                    <div class="brand-scale-logo">
                        <span class="brand-symbol brand-symbol--laurelball" aria-hidden="true"><span>HH</span></span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Laurel Ball</h3>
                        <p>A small winner's wreath around the ball, more playful than a corporate crest.</p>
                    </div>
                </div>
            </div>
            <div class="brand-section-label">
                <h3>Other directions</h3>
                <p>Earlier routes for comparison, including wordmark, ticket, shield and scoreboard ideas.</p>
            </div>
            <div class="brand-direction-grid">
                <div class="brand-logo-card brand-logo-card--crest">
                    <div class="brand-logo-card__identity">
                        <span class="brand-preview-mark brand-preview-mark--crest" aria-hidden="true">
                            <span>HH</span>
                        </span>
                        <span class="brand-preview-wordmark">
                            <span>Hendy's</span>
                            <strong>Hunches</strong>
                        </span>
                    </div>
                    <div>
                        <h3>Modern Club Crest</h3>
                        <p>A compact football-badge direction for the navbar and app icons.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--wordmark">
                    <div class="brand-wordmark-logo">
                        <span>Hendy's</span>
                        <strong>Hunches</strong>
                        <small>football predictions</small>
                    </div>
                    <div>
                        <h3>Clean Wordmark</h3>
                        <p>More grown-up and less badge-led, with the name doing the work.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--ticket">
                    <div class="brand-ticket-logo" aria-hidden="true">
                        <span>HH</span>
                        <strong>Hendy's Hunches</strong>
                        <small>Matchday picks</small>
                    </div>
                    <div>
                        <h3>Match Ticket</h3>
                        <p>A fixture-ticket idea that leans into the game and tournament rhythm.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--monogram">
                    <div class="brand-monogram-logo">
                        <span aria-hidden="true">HH</span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Bold Monogram</h3>
                        <p>Simple, punchy and very usable at small sizes in the navigation.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--shield">
                    <div class="brand-shield-lockup">
                        <span class="brand-shield-logo" aria-hidden="true">
                            <span>HH</span>
                        </span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Supporters' Shield</h3>
                        <p>A more traditional football identity, but cleaner than the current mark.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--scorecard">
                    <div class="brand-score-logo" aria-hidden="true">
                        <div>
                            <span>Hendy's</span>
                            <strong>Hunches</strong>
                        </div>
                        <small>2 - 1</small>
                    </div>
                    <div>
                        <h3>Scorecard Mark</h3>
                        <p>A playful option that makes the prediction mechanic part of the logo.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--pennant">
                    <div class="brand-pennant-logo" aria-hidden="true">
                        <span>HH</span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Pennant Flag</h3>
                        <p>A supporters' banner approach with a simple matchday silhouette.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--odds">
                    <div class="brand-odds-logo" aria-hidden="true">
                        <span>H</span>
                        <strong>Hunches</strong>
                        <small>1 X 2</small>
                    </div>
                    <div>
                        <h3>Odds Slip</h3>
                        <p>Sharp and game-focused, with the prediction format built into the mark.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--pitch">
                    <div class="brand-pitch-logo" aria-hidden="true">
                        <span>HH</span>
                    </div>
                    <div>
                        <h3>Pitch Lines</h3>
                        <p>A football-pitch icon that feels app-like and works neatly as a favicon.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--tabloid">
                    <div class="brand-tabloid-logo">
                        <strong>Hendy's Hunches</strong>
                        <span>Fixture picks</span>
                    </div>
                    <div>
                        <h3>Fixture Headline</h3>
                        <p>Newspaper-column energy, as if every prediction is a talking point.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--cap">
                    <div class="brand-cap-logo" aria-hidden="true">
                        <span>HH</span>
                    </div>
                    <div>
                        <h3>Manager Cap</h3>
                        <p>A lighter, characterful option that hints at making your weekly picks.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--rosette">
                    <div class="brand-rosette-logo" aria-hidden="true">
                        <span>HH</span>
                    </div>
                    <div>
                        <h3>Winner Rosette</h3>
                        <p>Prize-table energy with a celebratory mark for winner screens and badges.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--bracket">
                    <div class="brand-bracket-logo" aria-hidden="true">
                        <span>HH</span>
                        <strong>Finals</strong>
                    </div>
                    <div>
                        <h3>Tournament Bracket</h3>
                        <p>A structured knockout-stage identity for a competition-first direction.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--chat">
                    <div class="brand-chat-logo" aria-hidden="true">
                        <span>HH?</span>
                    </div>
                    <div>
                        <h3>Pub Chat Bubble</h3>
                        <p>Friendlier and social, leaning into family, colleagues and mini-leagues.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--stamp">
                    <div class="brand-stamp-logo">
                        <span>Hendy's</span>
                        <strong>Hunches</strong>
                    </div>
                    <div>
                        <h3>Prediction Stamp</h3>
                        <p>A confident stamped mark that could work well on cards and result states.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--ribbon">
                    <div class="brand-ribbon-logo">
                        <span>HH</span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>League Ribbon</h3>
                        <p>A classic league-table look with a flatter, cleaner digital finish.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--initial">
                    <div class="brand-initial-logo">
                        <span>H</span>
                        <strong>Hendy's Hunches</strong>
                    </div>
                    <div>
                        <h3>Single Initial</h3>
                        <p>The simplest route: one memorable letter paired with a strong wordmark.</p>
                    </div>
                </div>

                <div class="brand-logo-card brand-logo-card--fixture">
                    <div class="brand-fixture-logo" aria-hidden="true">
                        <span>HEN</span>
                        <strong>vs</strong>
                        <span>HUN</span>
                    </div>
                    <div>
                        <h3>Fixture Lockup</h3>
                        <p>A playful scoreboard idea that makes the brand feel like a match listing.</p>
                    </div>
                </div>
            </div>
        </article>

        <div class="concept-metric-grid">
            <article class="concept-metric concept-metric--primary">
                <span class="concept-metric__label">Your position</span>
                <strong>12th</strong>
                <span class="concept-metric__detail concept-up"><i class="bi bi-arrow-up-right"></i> Up 3 places</span>
            </article>
            <article class="concept-metric">
                <span class="concept-metric__label">Total points</span>
                <strong>71</strong>
                <span class="concept-metric__detail">7 from the final</span>
            </article>
            <article class="concept-metric">
                <span class="concept-metric__label">Prize gap</span>
                <strong>4 pts</strong>
                <span class="concept-metric__detail">Behind 5th place</span>
            </article>
            <article class="concept-metric">
                <span class="concept-metric__label">Exact scores</span>
                <strong>6</strong>
                <span class="concept-metric__detail">11.8% of fixtures</span>
            </article>
        </div>

        <div class="concept-grid concept-grid--top">
            <article class="concept-profile-card">
                <div class="concept-profile-card__kit">
                    <img src="football-kits/charcoal-gold.png" alt="Mock football strip avatar">
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
                            <dd>England</dd>
                        </div>
                        <div>
                            <dt>Location</dt>
                            <dd>Nottingham</dd>
                        </div>
                        <div>
                            <dt>Field</dt>
                            <dd>Digital services</dd>
                        </div>
                    </dl>
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
        </div>

        <div class="concept-grid">
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

        <div class="concept-grid concept-grid--bottom">
            <article class="concept-panel concept-panel--wide">
                <div class="concept-panel__header">
                    <div>
                        <p class="eyebrow">Matchday card</p>
                        <h2>Tonight's Fixture</h2>
                    </div>
                    <span class="concept-pill">20:00</span>
                </div>
                <div class="fixture-concept">
                    <div class="fixture-concept__team">
                        <img src="img/flags/es.svg" alt="Spain flag">
                        <strong>Spain</strong>
                        <span>1.79 avg</span>
                    </div>
                    <div class="fixture-concept__score">vs</div>
                    <div class="fixture-concept__team">
                        <img src="img/flags/gb-eng.svg" alt="England flag">
                        <strong>England</strong>
                        <span>1.43 avg</span>
                    </div>
                </div>
                <p class="concept-subtle mb-0">42 predictions submitted. Most common pick: Spain 2 - 1 England.</p>
            </article>

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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const concept = document.getElementById('dashboardConcept');
    const buttons = document.querySelectorAll('.concept-theme-button');

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            concept.dataset.conceptTheme = button.dataset.theme;
            buttons.forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        });
    });
});
</script>

<?php include "php/footer.php" ?>
