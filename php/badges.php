<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/process.php';

function hh_badge_table_name(): string
{
    return 'live_user_badges';
}

function hh_badge_definitions(): array
{
    return [
        'IN' => ['token' => 'IN', 'image' => 'img/badges/predictions-in.png', 'title' => 'Predictions In', 'description' => 'Save your first stage of predictions.'],
        'PT' => ['token' => 'PT', 'image' => 'img/badges/on-the-move.png', 'title' => 'On The Move', 'description' => 'Pick up your first points.'],
        '7' => ['token' => '7', 'image' => 'img/badges/perfect-seven.png', 'title' => 'Perfect Seven', 'description' => 'Land your first exact 7-pointer.'],
        'CR' => ['token' => 'CR', 'image' => 'img/badges/crowd-rebel.png', 'title' => 'Crowd Rebel', 'description' => 'Beat the crowd with a different scoreline and still score 3+ points.'],
        'HS' => ['token' => 'HS', 'image' => 'img/badges/hot-streak.png', 'title' => 'Hot Streak', 'description' => 'Score points in three consecutive recorded fixtures.'],
        'AC' => ['token' => 'AC', 'image' => 'img/badges/avid-climber.png', 'title' => 'Avid Climber', 'description' => 'Climb 5 or more places in a single results update.'],
        'DH' => ['token' => 'DH', 'image' => 'img/badges/dizzy-heights.png', 'title' => 'Dizzy Heights', 'description' => 'Reach the top three once the knockout rounds begin.'],
        'TFT' => ['token' => 'TFT', 'image' => 'img/badges/top-form-turkey.png', 'title' => 'Top Form Turkey', 'description' => 'Hit three straight 7-pointers in consecutive scored fixtures.'],
        'TH' => ['token' => 'TH', 'image' => 'img/badges/thread-starter.png', 'title' => 'Thread Starter', 'description' => 'Start a message thread in the Fan Zone.'],
        'RP' => ['token' => 'RP', 'image' => 'img/badges/in-the-replies.png', 'title' => 'In The Replies', 'description' => 'Reply to a thread in the Fan Zone.'],
        'PV' => ['token' => 'PV', 'image' => 'img/badges/poll-voter.png', 'title' => 'Poll Voter', 'description' => 'Cast your first vote in a live poll.'],
        '50' => ['token' => '50', 'image' => 'img/badges/50-club.png', 'title' => '50 Club', 'description' => 'Reach 50 total points.'],
        '100' => ['token' => '100', 'image' => 'img/badges/100-club.png', 'title' => '100 Club', 'description' => 'Reach 100 total points.'],
        '150' => ['token' => '150', 'image' => 'img/badges/150-club.png', 'title' => '150 Club', 'description' => 'Reach 150 total points.'],
        '200' => ['token' => '200', 'image' => 'img/badges/200-club.png', 'title' => '200 Club', 'description' => 'Reach 200 total points.'],
        '250' => ['token' => '250', 'image' => 'img/badges/250-club.png', 'title' => '250 Club', 'description' => 'Reach 250 total points.'],
    ];
}

function hh_badge_table_exists(mysqli $con): bool
{
    $escaped = mysqli_real_escape_string($con, hh_badge_table_name());
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$escaped}'");
    if (!($result instanceof mysqli_result)) {
        return false;
    }

    $exists = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);

    return $exists;
}

function hh_ensure_badge_table(mysqli $con): bool
{
    if (hh_badge_table_exists($con)) {
        return true;
    }

    $sql = @file_get_contents(__DIR__ . '/../sql/setup-user-badges-table.sql');
    if ($sql === false || trim($sql) === '') {
        return false;
    }

    if (!mysqli_multi_query($con, $sql)) {
        return false;
    }

    while (mysqli_more_results($con) && mysqli_next_result($con)) {
        // consume remaining results
    }

    return hh_badge_table_exists($con);
}

function hh_badge_user_identity(mysqli $con, int $userId): ?array
{
    if ($userId <= 0) {
        return null;
    }

    $statement = mysqli_prepare(
        $con,
        "SELECT id, username, lastpos, currpos
         FROM live_user_information
         WHERE id = ?
         LIMIT 1"
    );

    if (!$statement) {
        return null;
    }

    mysqli_stmt_bind_param($statement, 'i', $userId);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $row = $result instanceof mysqli_result ? (mysqli_fetch_assoc($result) ?: null) : null;
    if ($result instanceof mysqli_result) {
        mysqli_free_result($result);
    }
    mysqli_stmt_close($statement);

    return $row ?: null;
}

function hh_badge_prediction_row(mysqli $con, string $tableName, int $userId): array
{
    $statement = mysqli_prepare($con, "SELECT * FROM {$tableName} WHERE id = ? LIMIT 1");
    if (!$statement) {
        return [];
    }

    mysqli_stmt_bind_param($statement, 'i', $userId);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $row = $result instanceof mysqli_result ? (mysqli_fetch_assoc($result) ?: []) : [];
    if ($result instanceof mysqli_result) {
        mysqli_free_result($result);
    }
    mysqli_stmt_close($statement);

    return is_array($row) ? $row : [];
}

function hh_badge_latest_result_row(mysqli $con): array
{
    $result = mysqli_query($con, "SELECT * FROM live_match_results ORDER BY match_id DESC LIMIT 1");
    $row = $result instanceof mysqli_result ? (mysqli_fetch_assoc($result) ?: []) : [];
    if ($result instanceof mysqli_result) {
        mysqli_free_result($result);
    }

    return is_array($row) ? $row : [];
}

function hh_badge_is_exact_prediction(array $predictionRow, array $resultRow, int $homeIndex, int $awayIndex): bool
{
    $predHome = $predictionRow['score' . $homeIndex . '_p'] ?? null;
    $predAway = $predictionRow['score' . $awayIndex . '_p'] ?? null;
    $actualHome = $resultRow['score' . $homeIndex . '_r'] ?? null;
    $actualAway = $resultRow['score' . $awayIndex . '_r'] ?? null;

    return is_numeric($predHome)
        && is_numeric($predAway)
        && is_numeric($actualHome)
        && is_numeric($actualAway)
        && (int) $predHome === (int) $actualHome
        && (int) $predAway === (int) $actualAway;
}

function hh_badge_fixture_score_key($homeScore, $awayScore): string
{
    if (!is_numeric($homeScore) || !is_numeric($awayScore)) {
        return '';
    }

    return (string) ((int) $homeScore) . ':' . (string) ((int) $awayScore);
}

function hh_badge_popular_prediction_keys_for_fixture(mysqli $con, string $tableName, int $homeIndex, int $awayIndex): array
{
    static $cache = [];

    $cacheKey = $tableName . ':' . $homeIndex . ':' . $awayIndex;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $homeColumn = 'score' . $homeIndex . '_p';
    $awayColumn = 'score' . $awayIndex . '_p';
    $result = mysqli_query(
        $con,
        "SELECT
            stage.{$homeColumn} AS predicted_home,
            stage.{$awayColumn} AS predicted_away
         FROM live_user_information users
         LEFT JOIN {$tableName} stage ON stage.id = users.id
         ORDER BY users.surname ASC, users.firstname ASC, users.id ASC"
    );

    if (!($result instanceof mysqli_result)) {
        $cache[$cacheKey] = [];
        return $cache[$cacheKey];
    }

    $counts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $scoreKey = hh_badge_fixture_score_key($row['predicted_home'] ?? null, $row['predicted_away'] ?? null);
        if ($scoreKey === '') {
            continue;
        }

        if (!isset($counts[$scoreKey])) {
            [$homeScore, $awayScore] = array_map('intval', explode(':', $scoreKey, 2));
            $counts[$scoreKey] = [
                'score_key' => $scoreKey,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'prediction_count' => 0,
            ];
        }
        $counts[$scoreKey]['prediction_count']++;
    }

    mysqli_free_result($result);

    if ($counts === []) {
        $cache[$cacheKey] = [];
        return $cache[$cacheKey];
    }

    uasort(
        $counts,
        static function (array $left, array $right): int {
            $countComparison = ((int) ($right['prediction_count'] ?? 0)) <=> ((int) ($left['prediction_count'] ?? 0));
            if ($countComparison !== 0) {
                return $countComparison;
            }

            $homeComparison = ((int) ($left['home_score'] ?? 0)) <=> ((int) ($right['home_score'] ?? 0));
            if ($homeComparison !== 0) {
                return $homeComparison;
            }

            return ((int) ($left['away_score'] ?? 0)) <=> ((int) ($right['away_score'] ?? 0));
        }
    );

    $topCount = null;
    $popularKeys = [];
    foreach ($counts as $row) {
        $count = (int) ($row['prediction_count'] ?? 0);
        if ($topCount === null) {
            $topCount = $count;
        }

        if ($count !== $topCount) {
            break;
        }

        $popularKeys[] = (string) ($row['score_key'] ?? '');
    }

    $cache[$cacheKey] = array_values(array_filter(array_unique($popularKeys), static fn(string $key): bool => $key !== ''));

    return $cache[$cacheKey];
}

function hh_badge_stats_for_user(mysqli $con, int $userId): array
{
    $stats = [
        'has_predictions' => false,
        'points_total' => 0,
        'recorded_fixtures' => 0,
        'perfect_predictions' => 0,
        'crowd_rebel' => false,
        'hot_streak' => false,
        'avid_climber' => false,
        'dizzy_heights' => false,
        'top_form_turkey' => false,
        'thread_count' => 0,
        'reply_count' => 0,
        'poll_votes' => 0,
    ];

    $identity = hh_badge_user_identity($con, $userId);
    if (!$identity) {
        return $stats;
    }

    $latestResults = hh_badge_latest_result_row($con);
    $stageDefinitions = hh_prediction_stage_definitions();
    $scoringStreak = 0;
    $perfectStreak = 0;
    $lastPosition = (int) ($identity['lastpos'] ?? 0);
    $currentPosition = (int) ($identity['currpos'] ?? 0);
    $knockoutsStarted = false;

    foreach ($stageDefinitions as $stageKey => $definition) {
        $tableName = (string) ($definition['table'] ?? '');
        if ($tableName === '') {
            continue;
        }

        $predictionRow = hh_badge_prediction_row($con, $tableName, $userId);
        if ($predictionRow === []) {
            continue;
        }

        if ($stageKey === 'groups') {
            $stats['has_predictions'] = true;
        }

        $stats['points_total'] += (int) ($predictionRow['points_total'] ?? 0);

        $startIndex = (int) ($definition['start'] ?? 0);
        $endIndex = (int) ($definition['end'] ?? -1);
        for ($homeIndex = $startIndex, $awayIndex = $startIndex + 1; $homeIndex <= $endIndex && $awayIndex <= $endIndex; $homeIndex += 2, $awayIndex += 2) {
            if ($stageKey === 'ro32' && !$knockoutsStarted) {
                $actualHome = $latestResults['score' . $homeIndex . '_r'] ?? null;
                $actualAway = $latestResults['score' . $awayIndex . '_r'] ?? null;
                if (is_numeric($actualHome) && is_numeric($actualAway)) {
                    $knockoutsStarted = true;
                }
            }

            if (hh_badge_is_exact_prediction($predictionRow, $latestResults, $homeIndex, $awayIndex)) {
                $stats['perfect_predictions']++;
            }

            $detail = hh_prediction_fixture_score_detail(
                $predictionRow['score' . $homeIndex . '_p'] ?? null,
                $predictionRow['score' . $awayIndex . '_p'] ?? null,
                $latestResults['score' . $homeIndex . '_r'] ?? null,
                $latestResults['score' . $awayIndex . '_r'] ?? null
            );

            if (!empty($detail['recorded'])) {
                $stats['recorded_fixtures']++;

                if ((int) ($detail['points'] ?? 0) > 0) {
                    $scoringStreak++;
                    if ($scoringStreak >= 3) {
                        $stats['hot_streak'] = true;
                    }
                } else {
                    $scoringStreak = 0;
                }

                if ((int) ($detail['points'] ?? 0) === 7) {
                    $perfectStreak++;
                    if ($perfectStreak >= 3) {
                        $stats['top_form_turkey'] = true;
                    }
                } else {
                    $perfectStreak = 0;
                }
            }

            if ((int) ($detail['points'] ?? 0) < 3) {
                continue;
            }

            $playerScoreKey = hh_badge_fixture_score_key(
                $predictionRow['score' . $homeIndex . '_p'] ?? null,
                $predictionRow['score' . $awayIndex . '_p'] ?? null
            );

            if ($playerScoreKey === '') {
                continue;
            }

            $popularScoreKeys = hh_badge_popular_prediction_keys_for_fixture($con, $tableName, $homeIndex, $awayIndex);
            if ($popularScoreKeys !== [] && !in_array($playerScoreKey, $popularScoreKeys, true)) {
                $stats['crowd_rebel'] = true;
            }
        }
    }

    if ((int) ($stats['recorded_fixtures'] ?? 0) > 0 && $lastPosition > 0 && $currentPosition > 0 && ($lastPosition - $currentPosition) >= 5) {
        $stats['avid_climber'] = true;
    }

    if ($knockoutsStarted && $currentPosition > 0 && $currentPosition <= 3) {
        $stats['dizzy_heights'] = true;
    }

    $username = trim((string) ($identity['username'] ?? ''));
    if ($username !== '' && hh_badge_table_exists_for_name($con, 'live_fanzone_posts')) {
        $statement = mysqli_prepare(
            $con,
            "SELECT
                SUM(CASE WHEN parent_id IS NULL THEN 1 ELSE 0 END) AS thread_total,
                SUM(CASE WHEN parent_id IS NOT NULL THEN 1 ELSE 0 END) AS reply_total
             FROM live_fanzone_posts
             WHERE username = ? AND is_deleted = 0"
        );

        if ($statement) {
            mysqli_stmt_bind_param($statement, 's', $username);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $row = $result instanceof mysqli_result ? (mysqli_fetch_assoc($result) ?: []) : [];
            if ($result instanceof mysqli_result) {
                mysqli_free_result($result);
            }
            mysqli_stmt_close($statement);

            $stats['thread_count'] = (int) ($row['thread_total'] ?? 0);
            $stats['reply_count'] = (int) ($row['reply_total'] ?? 0);
        }
    }

    if (hh_badge_table_exists_for_name($con, 'live_poll_votes')) {
        $statement = mysqli_prepare(
            $con,
            "SELECT COUNT(*) AS total_votes
             FROM live_poll_votes
             WHERE user_id = ?"
        );

        if ($statement) {
            mysqli_stmt_bind_param($statement, 'i', $userId);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $row = $result instanceof mysqli_result ? (mysqli_fetch_assoc($result) ?: []) : [];
            if ($result instanceof mysqli_result) {
                mysqli_free_result($result);
            }
            mysqli_stmt_close($statement);

            $stats['poll_votes'] = (int) ($row['total_votes'] ?? 0);
        }
    }

    return $stats;
}

function hh_badge_table_exists_for_name(mysqli $con, string $tableName): bool
{
    $escaped = mysqli_real_escape_string($con, $tableName);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$escaped}'");
    if (!($result instanceof mysqli_result)) {
        return false;
    }

    $exists = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);

    return $exists;
}

function hh_badge_is_earned(string $token, array $stats): bool
{
    return match ($token) {
        'IN' => !empty($stats['has_predictions']),
        'PT' => (int) ($stats['points_total'] ?? 0) > 0,
        '7' => (int) ($stats['perfect_predictions'] ?? 0) > 0,
        'CR' => !empty($stats['crowd_rebel']),
        'HS' => !empty($stats['hot_streak']),
        'AC' => !empty($stats['avid_climber']),
        'DH' => !empty($stats['dizzy_heights']),
        'TFT' => !empty($stats['top_form_turkey']),
        'TH' => (int) ($stats['thread_count'] ?? 0) > 0,
        'RP' => (int) ($stats['reply_count'] ?? 0) > 0,
        'PV' => (int) ($stats['poll_votes'] ?? 0) > 0,
        '50' => (int) ($stats['points_total'] ?? 0) >= 50,
        '100' => (int) ($stats['points_total'] ?? 0) >= 100,
        '150' => (int) ($stats['points_total'] ?? 0) >= 150,
        '200' => (int) ($stats['points_total'] ?? 0) >= 200,
        '250' => (int) ($stats['points_total'] ?? 0) >= 250,
        default => false,
    };
}

function hh_badge_fetch_user_awards_with_connection(mysqli $con, int $userId): array
{
    if (!hh_badge_table_exists($con) || $userId <= 0) {
        return [];
    }

    $statement = mysqli_prepare(
        $con,
        "SELECT badge_token, awarded_at, notified_at
         FROM " . hh_badge_table_name() . "
         WHERE user_id = ?
         ORDER BY awarded_at ASC, id ASC"
    );

    if (!$statement) {
        return [];
    }

    mysqli_stmt_bind_param($statement, 'i', $userId);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $rows = [];
    if ($result instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);
    }
    mysqli_stmt_close($statement);

    return $rows;
}

function hh_badge_fetch_awarded_tokens_with_connection(mysqli $con, int $userId): array
{
    $rows = hh_badge_fetch_user_awards_with_connection($con, $userId);
    $tokens = [];
    foreach ($rows as $row) {
        $token = trim((string) ($row['badge_token'] ?? ''));
        if ($token !== '') {
            $tokens[] = $token;
        }
    }

    return array_values(array_unique($tokens));
}

function hh_sync_badges_for_user_with_connection(mysqli $con, int $userId, bool $seedSilentlyOnFirstSync = false): array
{
    if ($userId <= 0 || !hh_ensure_badge_table($con)) {
        return ['seeded' => 0, 'new' => 0];
    }

    $existingAwards = hh_badge_fetch_user_awards_with_connection($con, $userId);
    $existingTokens = [];
    foreach ($existingAwards as $row) {
        $token = trim((string) ($row['badge_token'] ?? ''));
        if ($token !== '') {
            $existingTokens[$token] = true;
        }
    }

    $seedSilently = empty($existingTokens) && $seedSilentlyOnFirstSync;
    $stats = hh_badge_stats_for_user($con, $userId);
    $seeded = 0;
    $new = 0;

    foreach (hh_badge_definitions() as $token => $definition) {
        if (!hh_badge_is_earned($token, $stats) || isset($existingTokens[$token])) {
            continue;
        }

        $statement = mysqli_prepare(
            $con,
            "INSERT INTO " . hh_badge_table_name() . " (user_id, badge_token, notified_at) VALUES (?, ?, " . ($seedSilently ? 'CURRENT_TIMESTAMP' : 'NULL') . ")"
        );

        if (!$statement) {
            continue;
        }

        mysqli_stmt_bind_param($statement, 'is', $userId, $token);
        if (mysqli_stmt_execute($statement)) {
            if ($seedSilently) {
                $seeded++;
            } else {
                $new++;
            }
            $existingTokens[$token] = true;
        }
        mysqli_stmt_close($statement);
    }

    return ['seeded' => $seeded, 'new' => $new];
}

function hh_badge_fetch_pending_notifications_with_connection(mysqli $con, int $userId): array
{
    if (!hh_badge_table_exists($con) || $userId <= 0) {
        return [];
    }

    $definitions = hh_badge_definitions();
    $statement = mysqli_prepare(
        $con,
        "SELECT badge_token, awarded_at
         FROM " . hh_badge_table_name() . "
         WHERE user_id = ? AND notified_at IS NULL
         ORDER BY awarded_at ASC, id ASC"
    );

    if (!$statement) {
        return [];
    }

    mysqli_stmt_bind_param($statement, 'i', $userId);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $rows = [];

    if ($result instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $token = trim((string) ($row['badge_token'] ?? ''));
            if ($token === '' || !isset($definitions[$token])) {
                continue;
            }

            $rows[] = array_merge($definitions[$token], [
                'awarded_at' => (string) ($row['awarded_at'] ?? ''),
            ]);
        }
        mysqli_free_result($result);
    }
    mysqli_stmt_close($statement);

    return $rows;
}

function hh_mark_badges_notified_with_connection(mysqli $con, int $userId, array $tokens): void
{
    if ($userId <= 0 || empty($tokens) || !hh_badge_table_exists($con)) {
        return;
    }

    $tokens = array_values(array_unique(array_filter(array_map('strval', $tokens), static fn(string $token): bool => $token !== '')));
    if (empty($tokens)) {
        return;
    }

    $placeholders = implode(',', array_fill(0, count($tokens), '?'));
    $types = 'i' . str_repeat('s', count($tokens));
    $values = array_merge([$userId], $tokens);

    $statement = mysqli_prepare(
        $con,
        "UPDATE " . hh_badge_table_name() . "
         SET notified_at = CURRENT_TIMESTAMP
         WHERE user_id = ? AND notified_at IS NULL AND badge_token IN ({$placeholders})"
    );

    if (!$statement) {
        return;
    }

    $params = [$types];
    foreach ($values as $index => $value) {
        $params[] = &$values[$index];
    }
    call_user_func_array([$statement, 'bind_param'], $params);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
}

function hh_sync_badges_for_all_with_connection(mysqli $con): array
{
    if (!hh_ensure_badge_table($con)) {
        throw new RuntimeException('The badge award table could not be prepared.');
    }

    $result = mysqli_query($con, "SELECT id FROM live_user_information ORDER BY id ASC");
    if (!($result instanceof mysqli_result)) {
        throw new RuntimeException(mysqli_error($con));
    }

    $summary = ['users' => 0, 'seeded' => 0, 'new' => 0];
    while ($row = mysqli_fetch_assoc($result)) {
        $userId = (int) ($row['id'] ?? 0);
        if ($userId <= 0) {
            continue;
        }

        $sync = hh_sync_badges_for_user_with_connection($con, $userId, true);
        $summary['users']++;
        $summary['seeded'] += (int) ($sync['seeded'] ?? 0);
        $summary['new'] += (int) ($sync['new'] ?? 0);
    }
    mysqli_free_result($result);

    return $summary;
}

function hh_badge_admin_summary(mysqli $con): array
{
    $definitions = hh_badge_definitions();
    $summary = [];

    foreach ($definitions as $token => $definition) {
        $summary[$token] = array_merge($definition, [
            'count' => 0,
            'latest_awarded' => null,
            'players' => [],
        ]);
    }

    if (!hh_badge_table_exists($con)) {
        return array_values($summary);
    }

    $rows = [];
    $result = mysqli_query(
        $con,
        "SELECT b.badge_token, b.awarded_at, lui.firstname, lui.surname, lui.username
         FROM " . hh_badge_table_name() . " b
         INNER JOIN live_user_information lui ON lui.id = b.user_id
         ORDER BY b.awarded_at DESC, b.id DESC"
    );

    if ($result instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);
    }

    foreach ($rows as $row) {
        $token = trim((string) ($row['badge_token'] ?? ''));
        if ($token === '' || !isset($summary[$token])) {
            continue;
        }

        $summary[$token]['count']++;
        if ($summary[$token]['latest_awarded'] === null) {
            $summary[$token]['latest_awarded'] = (string) ($row['awarded_at'] ?? '');
        }

        $displayName = trim((string) ($row['firstname'] ?? '') . ' ' . (string) ($row['surname'] ?? ''));
        if ($displayName === '') {
            $displayName = '@' . trim((string) ($row['username'] ?? 'player'));
        }

        if (!in_array($displayName, $summary[$token]['players'], true)) {
            $summary[$token]['players'][] = $displayName;
        }
    }

    return array_values($summary);
}
