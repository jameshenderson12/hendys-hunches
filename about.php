<?php
session_start();
$page_title = 'About';

require_once __DIR__ . '/php/auth.php';
hh_require_login('index.php');

include "php/header.php";
include "php/navigation.php";

$timeline = [
    [
        'tournament' => 'FIFA World Cup 2006',
        'strapline' => 'No name, just spreadsheet pain',
        'image' => 'img/wc2006-ss.png',
        'story' => [
            'Hendy\'s Hunches started as a shared spreadsheet, emailed around before kick-off and scored by hand every day.',
            'It was clunky and time-heavy, but the banter landed immediately and made the whole thing worth bringing back.',
        ],
        'pro' => 'Kick-started the tradition with plenty of banter.',
        'con' => 'Manual score updates swallowed hours every evening.',
        'charity_name' => 'N/A',
        'charity_copy' => 'No charity supported with this tournament.',
        'charity_logo' => 'img/charity-logos/football-placeholder.jpg',
        'charity_bg' => '#ffffff',
        'podium' => [
            ['rank' => '1st', 'name' => 'Steven Lough, James Henderson'],
            ['rank' => '2nd', 'name' => 'Kirsty Yarnold'],
            ['rank' => '3rd', 'name' => 'Julien Alégre, Andrew Lough'],
        ],
    ],
    [
        'tournament' => 'FIFA World Cup 2014',
        'strapline' => 'The online leap',
        'image' => 'img/wc2014-site.png',
        'story' => [
            'This was the first proper web version, built to revive the game and make entering predictions far less painful.',
            'It looked simple, but automatic scoring and live rankings made it feel like a real tournament experience for the first time.',
        ],
        'pro' => 'Online submissions made joining the game far easier.',
        'con' => 'Predictions only covered the group stage.',
        'charity_name' => 'N/A',
        'charity_copy' => 'No charity supported with this tournament.',
        'charity_logo' => 'img/charity-logos/football-placeholder.jpg',
        'charity_bg' => '#ffffff',
        'podium' => [
            ['rank' => '1st', 'name' => 'Andrew Booth'],
            ['rank' => '2nd', 'name' => 'Nigel Plant'],
            ['rank' => '3rd', 'name' => 'Luke Fecowycz'],
        ],
    ],
    [
        'tournament' => 'UEFA Euro 2016',
        'strapline' => 'Feature-rich fan favorite',
        'image' => 'img/euro2016-site-v3.png',
        'story' => [
            'The 2016 build pushed the game forward with proper logins, better rankings, richer stats, and room to make late changes before kick-off.',
            'It was the moment the site started to feel like a hobby project with real staying power rather than a one-off experiment.',
        ],
        'pro' => 'Richer stats and flexible edits kept people engaged.',
        'con' => 'More features meant more upkeep behind the scenes.',
        'charity_name' => 'Ballboys',
        'charity_copy' => 'Ballboys is a charity that raises awareness and educates the population (not just the guys) on the facts, figures and issues of testicular cancer.',
        'charity_logo' => 'img/charity-logos/ballboys-logo-bl.png',
        'charity_bg' => '#ffffff',
        'podium' => [
            ['rank' => '1st', 'name' => 'Jonathan Lamley'],
            ['rank' => '2nd', 'name' => 'Sam McGuigan'],
            ['rank' => '3rd', 'name' => 'Steve Butt, Kirsty Yarnold'],
        ],
    ],
    [
        'tournament' => 'FIFA World Cup 2018',
        'strapline' => 'The community grows',
        'image' => 'img/hh-logo-2018.jpg',
        'story' => [
            'By 2018 the player list was bigger, the leaderboard was tighter, and every update felt like it mattered a little more.',
            'It became less about proving the site worked and more about building a tournament ritual people looked forward to.',
        ],
        'pro' => 'A bigger player pool made the table feel alive.',
        'con' => 'The competition became much less forgiving.',
        'charity_name' => 'CALM (Campaign Against Living Miserably)',
        'charity_copy' => 'A placeholder for the chosen cause, the fundraising total, and a quick thank-you note.',
        'charity_logo' => 'img/charity-logos/CALM-Logo-Blue-647x1024.png',
        'charity_bg' => '#ffffff',
        'podium' => [
            ['rank' => '1st', 'name' => 'Nick Chandler'],
            ['rank' => '2nd', 'name' => 'Snigdha Dutta, Sonia Fernandez'],
            ['rank' => '3rd', 'name' => 'Daniel Waite'],
        ],
    ],
    [
        'tournament' => 'FIFA World Cup 2022',
        'strapline' => 'Global spotlight',
        'image' => 'img/qatar-2022-logo.png',
        'story' => [
            'Qatar 2022 brought a fast, intense tournament where every late goal seemed to rattle the rankings.',
            'The pace of the match schedule made the game feel urgent, which only added to the fun once the predictions were in.',
        ],
        'pro' => 'Rapid results kept the leaderboard moving constantly.',
        'con' => 'Quick turnarounds left little room for second thoughts.',
        'charity_name' => 'Sands',
        'charity_copy' => 'Sands provides a safe, understanding and caring community for anyone touched by pregnancy or baby loss.',
        'charity_logo' => 'img/charity-logos/sands-logo.jpg',
        'charity_bg' => '#ffffff',
        'podium' => [
            ['rank' => '1st', 'name' => 'Chloe McCandlish'],
            ['rank' => '2nd', 'name' => 'Howard Kilbourn'],
            ['rank' => '3rd', 'name' => 'Andrew Lough'],
        ],
    ],
    [
        'tournament' => 'UEFA Euro 2024',
        'strapline' => 'Fresh ideas, same nerves',
        'image' => 'img/germany-2024-logo-md.png',
        'story' => [
            'Euro 2024 became another chance to refine the experience, test new layout ideas, and keep the competition feeling fresh.',
            'It also proved again that no matter how polished the site gets, the final standings still come down to a few nervy scorelines.',
        ],
        'pro' => 'New format ideas kept the game feeling fresh.',
        'con' => 'Some scoring and UX ideas still needed another pass.',
        'charity_name' => 'Notts County Foundation',
        'charity_copy' => 'The Notts County Foundation harness the power of sport and physical activity to improve the physical and mental wellbeing of participants.',
        'charity_logo' => 'img/charity-logos/notts-county-foundation-logo.png',
        'charity_bg' => '#000000',
        'podium' => [
            ['rank' => '1st', 'name' => 'Jonathan Lamley'],
            ['rank' => '2nd', 'name' => 'Paul Hendrick'],
            ['rank' => '3rd', 'name' => 'David Holmes'],
        ],
    ],
    [
        'tournament' => 'FIFA World Cup 2026',
        'strapline' => 'The next horizon',
        'image' => 'img/hh-logo-2026-purple.png',
        'story' => [
            'The 2026 edition is shaping up to be the biggest version yet, with more teams, more fixtures, and a stronger site underneath it.',
            'The aim now is simple: keep the fun, reduce the panic, and make the whole thing feel effortless for players from day one.',
        ],
        'pro' => 'The expanded format should create more drama and variety.',
        'con' => 'There is still fine-tuning to do before the opening day.',
        'charity_name' => 'Charity placeholder',
        'charity_copy' => 'A future slot for the next supported charity, impact note, and fundraising story.',
        'charity_logo' => 'img/charity-logos/notts-county-foundation-logo.png',
        'charity_bg' => '#ffffff',
        'podium' => [
            ['rank' => '1st', 'name' => 'Pending'],
            ['rank' => '2nd', 'name' => 'TBC'],
            ['rank' => '3rd', 'name' => 'TBC'],
            'pending' => true,
        ],
    ],
];

$testimonials = [
    [
        'quote' => 'I still end up checking the rankings far more often than I should.',
        'name' => 'Player testimonial',
    ],
    [
        'quote' => 'It somehow manages to be competitive, chaotic, and very funny all at once.',
        'name' => 'Player testimonial',
    ],
    [
        'quote' => 'You think you know football until this game starts humbling you in public.',
        'name' => 'Player testimonial',
    ],
];
?>

<main id="main" class="main">

    <div class="page-hero page-hero--about">
		<div>
			<p class="eyebrow">Since 2006</p>
			<h1>About this game</h1>
			<p class="lead mb-0">The story of Hendy's Hunches, from spreadsheet slog to tournament tradition.</p>
		</div>
    </div>

    <section class="section about-page">
        <section class="about-intro">
            <div class="about-intro__media">
                <img src="img/james-scotland-edited-lg.png" alt="James Henderson in a football shirt with arms folded">
            </div>
            <div class="about-intro__copy">
                <p class="eyebrow">From James</p>
                <h2>A small football game that got a bit out of hand</h2>
                <p>Hendy's Hunches began as a simple way to make tournament football more fun for friends, family, and colleagues, and it has somehow kept growing ever since. It is still a hobby project, still evolving, and still built around the same idea: make the big competitions feel even more memorable together.</p>
                <p class="mb-0">I know the site is never truly finished, but that is part of the charm. If it adds a bit of tension, laughter, and conversation to a tournament, then it is doing its job.</p>
            </div>
        </section>

        <section class="about-section-card about-testimonials">
            <div class="about-section-heading">
                <p class="eyebrow">What players say</p>
                <h2>Small quotes, big tournament energy</h2>
            </div>
            <div class="about-testimonials__grid">
                <?php foreach ($testimonials as $testimonial) : ?>
                    <article class="about-testimonial">
                        <p class="about-testimonial__quote">“<?= htmlspecialchars($testimonial['quote']) ?>”</p>
                        <span class="about-testimonial__name"><?= htmlspecialchars($testimonial['name']) ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="about-section-card about-timeline">
            <div class="about-section-heading">
                <p class="eyebrow">Tournament archive</p>
                <h2>Each edition, one card at a time</h2>
            </div>
            <div class="about-timeline__grid">
                <?php foreach ($timeline as $entry) : ?>
                    <?php $isPending = !empty($entry['podium']['pending']); ?>
                    <article class="about-season-card">
                        <div class="about-season-card__visual">
                            <img src="<?= htmlspecialchars($entry['image']) ?>" alt="<?= htmlspecialchars($entry['tournament']) ?> visual">
                        </div>
                        <div class="about-season-card__body">
                            <div class="about-season-card__header">
                                <p class="about-season-card__tournament"><?= htmlspecialchars($entry['tournament']) ?></p>
                                <h3><?= htmlspecialchars($entry['strapline']) ?></h3>
                            </div>
                            <div class="about-season-card__story">
                                <?php foreach ($entry['story'] as $sentence) : ?>
                                    <p><?= htmlspecialchars($sentence) ?></p>
                                <?php endforeach; ?>
                            </div>
                            <div class="about-season-card__footer">
                                <div class="about-meta-block about-season-card__pros">
                                    <p class="about-meta-block__label">Pros and Cons</p>
                                    <div class="about-season-card__insights">
                                        <div class="about-season-insight about-season-insight--pro">
                                            <i class="bi bi-plus-circle-fill" aria-hidden="true"></i>
                                            <?= htmlspecialchars($entry['pro']) ?>
                                        </div>
                                        <div class="about-season-insight about-season-insight--con">
                                            <i class="bi bi-dash-circle-fill" aria-hidden="true"></i>
                                            <?= htmlspecialchars($entry['con']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="about-podium-block">
                                    <p class="about-podium-block__label">Top 3</p>
                                    <ol class="about-podium-list">
                                        <?php foreach ($entry['podium'] as $podiumIndex => $podiumEntry) : ?>
                                            <?php if (!is_array($podiumEntry)) { continue; } ?>
                                            <li class="<?= $isPending ? 'is-pending' : '' ?>">
                                                <span class="about-podium-list__rank about-podium-list__rank--<?= $podiumIndex + 1 ?>"><?= htmlspecialchars($podiumEntry['rank']) ?></span>
                                                <strong><?= htmlspecialchars($podiumEntry['name']) ?></strong>
                                            </li>
                                        <?php endforeach; ?>
                                    </ol>
                                </div>
                                <div class="about-meta-block about-charity-block">
                                    <p class="about-meta-block__label">Charity Focus</p>
                                    <div class="about-charity-block__content">
                                        <div class="about-charity-block__logo" style="background: <?= htmlspecialchars((string) ($entry['charity_bg'] ?? '#ffffff')) ?>;">
                                            <img src="<?= htmlspecialchars($entry['charity_logo']) ?>" alt="<?= htmlspecialchars($entry['charity_name']) ?>">
                                        </div>
                                        <div class="about-charity-block__copy">
                                            <p class="about-charity-block__name"><?= htmlspecialchars($entry['charity_name']) ?></p>
                                            <p><?= htmlspecialchars($entry['charity_copy']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </section>
</main>

<?php include "php/footer.php" ?>
