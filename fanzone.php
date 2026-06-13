<?php
session_start();
$page_title = 'Fan Zone';

require_once __DIR__ . '/php/auth.php';
hh_require_login('index.php');
require_once __DIR__ . '/php/config.php';
include 'php/db-connect.php';

function hh_fanzone_table_exists(mysqli $con, string $table): bool
{
    $escapedTable = mysqli_real_escape_string($con, $table);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$escapedTable}'");

    if (!$result) {
        return false;
    }

    $exists = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);

    return $exists;
}

function hh_fanzone_display_name(): string
{
    $fullName = trim(($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['surname'] ?? ''));
    if ($fullName !== '') {
        return $fullName;
    }

    return (string) ($_SESSION['username'] ?? 'Unknown player');
}

function hh_fanzone_normalize(string $value): string
{
    return strtolower(trim(preg_replace('/\s+/', ' ', $value)));
}

function hh_fanzone_is_admin(): bool
{
    global $developer;

    $username = (string) ($_SESSION['username'] ?? '');
    $displayName = hh_fanzone_display_name();
    $configuredAdmins = $GLOBALS['fanzone_admin_usernames'] ?? [];

    if ($username === 'developer-preview') {
        return true;
    }

    if (is_array($configuredAdmins) && in_array($username, $configuredAdmins, true)) {
        return true;
    }

    return hh_fanzone_normalize($displayName) === hh_fanzone_normalize((string) $developer);
}

function hh_fanzone_can_manage_post(array $post): bool
{
    $username = (string) ($_SESSION['username'] ?? '');

    return hh_fanzone_is_admin() || ($username !== '' && $username === (string) ($post['username'] ?? ''));
}

function hh_fanzone_format_datetime(string $value): string
{
    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return $value;
    }

    return date('j M Y \a\t H:i', $timestamp);
}

function hh_fanzone_team_key(string $value): string
{
    return strtolower(trim($value));
}

function hh_fanzone_build_team_flag_map(mysqli $con): array
{
    if (!hh_fanzone_table_exists($con, 'live_match_schedule')) {
        return [];
    }

    $map = [];
    $result = mysqli_query(
        $con,
        "SELECT hometeam, hometeamimg, awayteam, awayteamimg
         FROM live_match_schedule"
    );

    if (!($result instanceof mysqli_result)) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $homeTeam = trim((string) ($row['hometeam'] ?? ''));
        $homeFlag = trim((string) ($row['hometeamimg'] ?? ''));
        $awayTeam = trim((string) ($row['awayteam'] ?? ''));
        $awayFlag = trim((string) ($row['awayteamimg'] ?? ''));

        if ($homeTeam !== '' && $homeFlag !== '') {
            $map[hh_fanzone_team_key($homeTeam)] = $homeFlag;
        }

        if ($awayTeam !== '' && $awayFlag !== '') {
            $map[hh_fanzone_team_key($awayTeam)] = $awayFlag;
        }
    }

    mysqli_free_result($result);

    return $map;
}

function hh_fanzone_option_flag(array $teamFlagMap, string $label): string
{
    $teamKey = hh_fanzone_team_key($label);
    return (string) ($teamFlagMap[$teamKey] ?? '');
}

function hh_fanzone_pick_quiz_questions(array $questionBank, int $limit = 10): array
{
    $questionBank = array_values(array_filter($questionBank, static function ($question): bool {
        return is_array($question)
            && trim((string) ($question['question'] ?? '')) !== ''
            && is_array($question['options'] ?? null)
            && count((array) ($question['options'] ?? [])) >= 2
            && trim((string) ($question['answer'] ?? '')) !== '';
    }));

    if ($questionBank === []) {
        return [];
    }

    shuffle($questionBank);

    return array_slice($questionBank, 0, max(1, $limit));
}

function hh_fanzone_pick_trivia_item(array $items): ?array
{
    $items = array_values(array_filter($items, static function ($item): bool {
        return is_array($item)
            && trim((string) ($item['label'] ?? '')) !== ''
            && trim((string) ($item['title'] ?? '')) !== ''
            && trim((string) ($item['body'] ?? '')) !== '';
    }));

    if ($items === []) {
        return null;
    }

    shuffle($items);

    return $items[0] ?? null;
}

function hh_fanzone_schema_ready(mysqli $con, string $table): array
{
    $requiredColumns = [
        'id',
        'parent_id',
        'username',
        'display_name',
        'message_body',
        'is_deleted',
        'is_pinned',
        'is_announcement',
        'created_at',
        'updated_at',
    ];

    $result = mysqli_query($con, "SHOW COLUMNS FROM {$table}");
    if (!$result) {
        return ['ready' => false, 'missing' => $requiredColumns];
    }

    $columns = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $columns[] = $row['Field'];
    }
    mysqli_free_result($result);

    $missing = array_values(array_diff($requiredColumns, $columns));

    return ['ready' => $missing === [], 'missing' => $missing];
}

function hh_fanzone_poll_schema_ready(mysqli $con): array
{
    $definitions = [
        'live_polls' => ['id', 'question', 'created_by', 'is_active', 'created_at', 'closed_at'],
        'live_poll_options' => ['id', 'poll_id', 'option_label', 'sort_order'],
        'live_poll_votes' => ['id', 'poll_id', 'option_id', 'user_id', 'created_at'],
    ];

    $missing = [];
    foreach ($definitions as $table => $requiredColumns) {
        if (!hh_fanzone_table_exists($con, $table)) {
            $missing[$table] = $requiredColumns;
            continue;
        }

        $result = mysqli_query($con, "SHOW COLUMNS FROM {$table}");
        if (!$result) {
            $missing[$table] = $requiredColumns;
            continue;
        }

        $columns = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $columns[] = $row['Field'];
        }
        mysqli_free_result($result);

        $missingColumns = array_values(array_diff($requiredColumns, $columns));
        if ($missingColumns !== []) {
            $missing[$table] = $missingColumns;
        }
    }

    return ['ready' => $missing === [], 'missing' => $missing];
}

function hh_fanzone_fetch_poll_with_results(mysqli $con, int $pollId, int $sessionUserId = 0): ?array
{
    if ($pollId <= 0) {
        return null;
    }

    $pollStmt = mysqli_prepare(
        $con,
        "SELECT id, question, is_active, created_at, closed_at FROM live_polls WHERE id = ? LIMIT 1"
    );
    if (!$pollStmt) {
        return null;
    }

    mysqli_stmt_bind_param($pollStmt, 'i', $pollId);
    mysqli_stmt_execute($pollStmt);
    $pollResult = mysqli_stmt_get_result($pollStmt);
    $poll = $pollResult ? mysqli_fetch_assoc($pollResult) : null;
    mysqli_stmt_close($pollStmt);

    if ($poll === null) {
        return null;
    }

    $options = [];
    $totalVotes = 0;
    $userVoteOptionId = null;
    $sessionIdSql = $sessionUserId > 0 ? (string) $sessionUserId : '0';

    $optionSql = "
        SELECT
            o.id,
            o.option_label,
            o.sort_order,
            COUNT(v.id) AS vote_total,
            MAX(CASE WHEN v.user_id = {$sessionIdSql} THEN v.option_id ELSE NULL END) AS user_vote_option_id
        FROM live_poll_options o
        LEFT JOIN live_poll_votes v ON v.option_id = o.id AND v.poll_id = o.poll_id
        WHERE o.poll_id = " . (int) $pollId . "
        GROUP BY o.id, o.option_label, o.sort_order
        ORDER BY o.sort_order ASC, o.id ASC
    ";
    $optionResult = mysqli_query($con, $optionSql);
    if ($optionResult instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($optionResult)) {
            $voteTotal = (int) ($row['vote_total'] ?? 0);
            $totalVotes += $voteTotal;
            if ($userVoteOptionId === null && !empty($row['user_vote_option_id'])) {
                $userVoteOptionId = (int) $row['user_vote_option_id'];
            }
            $options[] = [
                'id' => (int) $row['id'],
                'label' => (string) $row['option_label'],
                'sort_order' => (int) $row['sort_order'],
                'votes' => $voteTotal,
            ];
        }
        mysqli_free_result($optionResult);
    }

    foreach ($options as &$option) {
        $option['percent'] = $totalVotes > 0 ? (int) round(($option['votes'] / $totalVotes) * 100) : 0;
        $option['selected_by_user'] = $userVoteOptionId !== null && $userVoteOptionId === $option['id'];
    }
    unset($option);

    $poll['id'] = (int) $poll['id'];
    $poll['is_active'] = (int) $poll['is_active'] === 1;
    $poll['options'] = $options;
    $poll['total_votes'] = $totalVotes;
    $poll['user_voted'] = $userVoteOptionId !== null;
    $poll['user_vote_option_id'] = $userVoteOptionId;

    return $poll;
}

$boardTable = 'live_fanzone_posts';
$boardReady = isset($con) && $con instanceof mysqli && hh_fanzone_table_exists($con, $boardTable);
$boardSchema = $boardReady ? hh_fanzone_schema_ready($con, $boardTable) : ['ready' => false, 'missing' => []];
$boardSchemaReady = $boardReady && $boardSchema['ready'];
$pollSchema = isset($con) && $con instanceof mysqli ? hh_fanzone_poll_schema_ready($con) : ['ready' => false, 'missing' => []];
$pollSchemaReady = $pollSchema['ready'];
$boardError = null;
$boardNotice = null;
$pollError = null;
$pollNotice = null;
$composerDraft = '';
$editingPost = null;
$threads = [];
$replyMap = [];
$currentPoll = null;
$previousPolls = [];
$pollDraftQuestion = '';
$pollDraftOptions = array_fill(0, 6, '');
$sessionUserId = (int) ($_SESSION['id'] ?? 0);
$sessionUsername = (string) ($_SESSION['username'] ?? '');
$yourThreadCount = 0;
$yourReplyCount = 0;
$activePollCount = 0;
$yourPollVoteCount = 0;
$teamFlagMap = [];
$quickFireQuizBank = [
    [
        'question' => 'Who captains Portugal at the 2026 World Cup?',
        'options' => ['Cristiano Ronaldo', 'Rafael Leao', 'Bruno Fernandes', 'Ruben Dias'],
        'answer' => 'Cristiano Ronaldo',
    ],
    [
        'question' => 'Which of these opponents were not in Scotland\'s group for the 1998 World Cup?',
        'options' => ['Brazil', 'Haiti', 'Morocco'],
        'answer' => 'Haiti',
    ],
    [
        'question' => 'How many times have Uruguay won the World Cup?',
        'options' => ['0', '1', '2', '3'],
        'answer' => '2',
    ],
    [
        'question' => 'Who captains Croatia at the 2026 World Cup?',
        'options' => ['Kevin De Bruyne', 'Virgil van Dijk', 'Lionel Messi', 'Luka Modric'],
        'answer' => 'Luka Modric',
    ],
    [
        'question' => 'England have won the World Cup once. In which year did they win?',
        'options' => ['1950', '1954', '1966', '1990'],
        'answer' => '1966',
    ],
    [
        'question' => 'Which city hosts the final of the 2026 World Cup?',
        'options' => ['New York / New Jersey', 'Los Angeles', 'Dallas', 'Miami'],
        'answer' => 'New York / New Jersey',
    ],
    [
        'question' => 'Which nation won the very first FIFA World Cup in 1930?',
        'options' => ['Argentina', 'Brazil', 'Italy', 'Uruguay'],
        'answer' => 'Uruguay',
    ],
    [
        'question' => 'Who captains England at the 2026 World Cup?',
        'options' => ['Harry Kane', 'Jude Bellingham', 'Declan Rice', 'John Stones'],
        'answer' => 'Harry Kane',
    ],
    [
        'question' => 'How many host nations are there for the 2026 World Cup?',
        'options' => ['1', '2', '3', '4'],
        'answer' => '3',
    ],
    [
        'question' => 'Which country lifted the World Cup in 2010?',
        'options' => ['Brazil', 'Germany', 'Spain', 'Netherlands'],
        'answer' => 'Spain',
    ],
    [
        'question' => 'Which of these cities is in Canada?',
        'options' => ['Guadalajara', 'Vancouver', 'Monterrey', 'Houston'],
        'answer' => 'Vancouver',
    ],
    [
        'question' => 'Who captains Argentina at the 2026 World Cup?',
        'options' => ['Lautaro Martinez', 'Lionel Messi', 'Rodrigo De Paul', 'Julian Alvarez'],
        'answer' => 'Lionel Messi',
    ],
    [
        'question' => 'Which African nation reached the semi-finals of the 2022 World Cup?',
        'options' => ['Morocco', 'Senegal', 'Ghana', 'Cameroon'],
        'answer' => 'Morocco',
    ],
    [
        'question' => 'How many stars appear above Uruguay’s badge for World Cup titles?',
        'options' => ['1', '2', '3', '4'],
        'answer' => '2',
    ],
    [
        'question' => 'Which country hosted the 1994 FIFA World Cup?',
        'options' => ['Mexico', 'France', 'United States', 'Italy'],
        'answer' => 'United States',
    ],
    [
        'question' => 'Who captains the Netherlands at the 2026 World Cup?',
        'options' => ['Virgil van Dijk', 'Memphis Depay', 'Frenkie de Jong', 'Nathan Ake'],
        'answer' => 'Virgil van Dijk',
    ],
    [
        'question' => 'Which nation beat England in the 2022 World Cup quarter-finals?',
        'options' => ['France', 'Croatia', 'Argentina', 'Morocco'],
        'answer' => 'France',
    ],
    [
        'question' => 'Which of these teams is a 2026 host nation?',
        'options' => ['Costa Rica', 'Mexico', 'Jamaica', 'Panama'],
        'answer' => 'Mexico',
    ],
];
$quickFireQuiz = hh_fanzone_pick_quiz_questions($quickFireQuizBank, 10);
$fanzoneTriviaBank = [
    [
        'label' => 'Did you know?',
        'title' => 'Uruguay set the opening marker',
        'body' => 'Uruguay won the very first World Cup in 1930 and then lifted it again in 1950, giving them one of the game’s earliest tournament legacies.',
    ],
    [
        'label' => 'On this day',
        'title' => 'South Africa made history in 2010',
        'body' => 'On 11 June 2010, South Africa became the first African nation to host a FIFA World Cup as the tournament opened in Johannesburg.',
    ],
    [
        'label' => 'World Cup archive',
        'title' => 'England’s one triumph came in 1966',
        'body' => 'England’s only World Cup title arrived at Wembley in 1966, when they beat West Germany 4-2 after extra time in the final.',
    ],
    [
        'label' => 'Did you know?',
        'title' => 'The 2026 tournament is the biggest yet',
        'body' => 'The 2026 World Cup is the first to feature 48 nations, expanding the field and stretching the tournament across the United States, Canada and Mexico.',
    ],
    [
        'label' => 'On this day',
        'title' => 'Spain crowned a new champion in 2010',
        'body' => 'On 11 July 2010, Spain won their first World Cup title thanks to Andres Iniesta’s extra-time goal against the Netherlands.',
    ],
    [
        'label' => 'World Cup archive',
        'title' => 'Brazil remain the benchmark',
        'body' => 'Brazil are still the most successful men’s World Cup nation, with five titles spanning from 1958 to 2002.',
    ],
    [
        'label' => 'Did you know?',
        'title' => 'Three hosts share the stage in 2026',
        'body' => 'Canada, Mexico and the United States are sharing hosting duties, making this the first men’s World Cup staged by three nations together.',
    ],
    [
        'label' => 'On this day',
        'title' => 'France lifted their first World Cup in 1998',
        'body' => 'On 12 July 1998, France beat Brazil 3-0 in Paris to win the World Cup for the first time on home soil.',
    ],
];
$fanzoneTrivia = hh_fanzone_pick_trivia_item($fanzoneTriviaBank);
$spotTheBallRounds = [
    [
        'title' => 'Penalty-box scramble',
        'image' => 'img/fanzone-games/spot-the-ball-1.png',
        'rows' => 5,
        'cols' => 6,
        'answer' => 4,
    ],
    [
        'title' => 'Passing play',
        'image' => 'img/fanzone-games/spot-the-ball-2.png',
        'rows' => 5,
        'cols' => 6,
        'answer' => 19,
    ],
    // [
    //     'title' => 'Calming things down',
    //     'image' => 'img/fanzone-games/spot-the-ball-3.png',
    //     'rows' => 5,
    //     'cols' => 6,
    //     'answer' => 24,
    // ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fanzone_action'])) {
    $action = (string) $_POST['fanzone_action'];

    if ($action === 'create_poll') {
        $pollDraftQuestion = trim((string) ($_POST['poll_question'] ?? ''));
        $pollDraftOptions = [];
        foreach ((array) ($_POST['poll_options'] ?? []) as $optionValue) {
            $pollDraftOptions[] = trim((string) $optionValue);
        }
        $pollDraftOptions = array_pad(array_slice($pollDraftOptions, 0, 6), 6, '');

        if (!$pollSchemaReady) {
            $pollError = 'The Fan Zone poll tables need their latest database setup before polls can be managed here.';
        } elseif (!hh_fanzone_is_admin()) {
            $pollError = 'Only admins can launch a new Fan Zone poll.';
        } elseif ($pollDraftQuestion === '') {
            $pollError = 'Please write the poll question first.';
        } else {
            $options = array_values(array_filter($pollDraftOptions, static fn(string $option): bool => $option !== ''));
            if (count($options) < 2) {
                $pollError = 'Please include at least two answer options.';
            } else {
                mysqli_begin_transaction($con);
                try {
                    mysqli_query($con, "UPDATE live_polls SET is_active = 0, closed_at = NOW() WHERE is_active = 1");

                    $pollStmt = mysqli_prepare($con, "INSERT INTO live_polls (question, created_by, is_active) VALUES (?, ?, 1)");
                    if (!$pollStmt) {
                        throw new RuntimeException(mysqli_error($con));
                    }

                    mysqli_stmt_bind_param($pollStmt, 'si', $pollDraftQuestion, $sessionUserId);
                    mysqli_stmt_execute($pollStmt);
                    $pollId = (int) mysqli_insert_id($con);
                    mysqli_stmt_close($pollStmt);

                    $optionStmt = mysqli_prepare($con, "INSERT INTO live_poll_options (poll_id, option_label, sort_order) VALUES (?, ?, ?)");
                    if (!$optionStmt) {
                        throw new RuntimeException(mysqli_error($con));
                    }

                    foreach ($options as $index => $optionLabel) {
                        $sortOrder = $index + 1;
                        mysqli_stmt_bind_param($optionStmt, 'isi', $pollId, $optionLabel, $sortOrder);
                        mysqli_stmt_execute($optionStmt);
                    }
                    mysqli_stmt_close($optionStmt);

                    mysqli_commit($con);
                    header('Location: fanzone.php?poll=created#fanzonePoll');
                    exit();
                } catch (Throwable $exception) {
                    mysqli_rollback($con);
                    $pollError = 'The poll could not be created just now.';
                }
            }
        }
    } elseif ($action === 'vote_poll') {
        $pollId = (int) ($_POST['poll_id'] ?? 0);
        $optionId = (int) ($_POST['option_id'] ?? 0);

        if (!$pollSchemaReady) {
            $pollError = 'The Fan Zone poll tables need their latest database setup before players can vote.';
        } elseif ($pollId <= 0 || $optionId <= 0 || $sessionUserId <= 0) {
            $pollError = 'That vote could not be understood.';
        } else {
            $activePoll = hh_fanzone_fetch_poll_with_results($con, $pollId, $sessionUserId);
            if ($activePoll === null || !$activePoll['is_active']) {
                $pollError = 'That poll is no longer live.';
            } elseif ($activePoll['user_voted']) {
                $pollNotice = 'You have already voted in this poll, so here are the live results.';
            } else {
                $validOptionIds = array_column($activePoll['options'], 'id');
                if (!in_array($optionId, $validOptionIds, true)) {
                    $pollError = 'Please choose one of the listed answers.';
                } else {
                    $voteStmt = mysqli_prepare($con, "INSERT INTO live_poll_votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
                    if ($voteStmt) {
                        mysqli_stmt_bind_param($voteStmt, 'iii', $pollId, $optionId, $sessionUserId);
                        if (mysqli_stmt_execute($voteStmt)) {
                            mysqli_stmt_close($voteStmt);
                            header('Location: fanzone.php?poll=voted#fanzonePoll');
                            exit();
                        }
                        mysqli_stmt_close($voteStmt);
                    }

                    $pollError = 'Your vote could not be saved just now.';
                }
            }
        }
    } elseif (!$boardReady || !$boardSchemaReady) {
        $boardError = 'The Fan Zone board needs its latest database setup before posts can be managed here.';
    } else {
        $body = trim((string) ($_POST['message_body'] ?? ''));
        $displayName = hh_fanzone_display_name();
        $username = (string) ($_SESSION['username'] ?? '');
        $postId = isset($_POST['post_id']) && $_POST['post_id'] !== '' ? (int) $_POST['post_id'] : null;
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int) $_POST['parent_id'] : null;
        $composerDraft = $action === 'thread' ? $body : '';

        if ($action === 'thread' || $action === 'reply') {
            if ($body === '') {
                $boardError = 'Please write a message before posting.';
            } elseif (mb_strlen($body) > 1200) {
                $boardError = 'Please keep messages to 1200 characters or fewer.';
            } else {
                $validatedParentId = null;
                $isPinned = 0;
                $isAnnouncement = 0;

                if ($parentId !== null && $parentId > 0) {
                    $checkParent = mysqli_prepare($con, "SELECT id FROM {$boardTable} WHERE id = ? AND is_deleted = 0 LIMIT 1");
                    if ($checkParent) {
                        mysqli_stmt_bind_param($checkParent, 'i', $parentId);
                        mysqli_stmt_execute($checkParent);
                        mysqli_stmt_store_result($checkParent);
                        if (mysqli_stmt_num_rows($checkParent) === 1) {
                            $validatedParentId = $parentId;
                        } else {
                            $boardError = 'That thread reply target could not be found.';
                        }
                        mysqli_stmt_close($checkParent);
                    }
                } elseif (hh_fanzone_is_admin()) {
                    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
                    $isAnnouncement = isset($_POST['is_announcement']) ? 1 : 0;
                }

                if ($boardError === null) {
                    $insert = mysqli_prepare(
                        $con,
                        "INSERT INTO {$boardTable} (parent_id, username, display_name, message_body, is_pinned, is_announcement) VALUES (?, ?, ?, ?, ?, ?)"
                    );

                    if ($insert) {
                        mysqli_stmt_bind_param($insert, 'isssii', $validatedParentId, $username, $displayName, $body, $isPinned, $isAnnouncement);
                        if (mysqli_stmt_execute($insert)) {
                            mysqli_stmt_close($insert);
                            $postedState = $validatedParentId === null ? 'thread' : 'reply';
                            header('Location: fanzone.php?posted=' . $postedState . '#fanzoneBoard');
                            exit();
                        }

                        $boardError = 'The message could not be saved just now.';
                        mysqli_stmt_close($insert);
                    } else {
                        $boardError = 'The board is not ready to accept posts yet.';
                    }
                }
            }
        } elseif (($action === 'edit' || $action === 'delete') && $postId !== null && $postId > 0) {
            $postResult = mysqli_prepare(
                $con,
                "SELECT id, parent_id, username, display_name, message_body, is_pinned, is_announcement FROM {$boardTable} WHERE id = ? AND is_deleted = 0 LIMIT 1"
            );

            if ($postResult) {
                mysqli_stmt_bind_param($postResult, 'i', $postId);
                mysqli_stmt_execute($postResult);
                $result = mysqli_stmt_get_result($postResult);
                $targetPost = $result ? mysqli_fetch_assoc($result) : null;
                mysqli_stmt_close($postResult);

                if ($targetPost === null) {
                    $boardError = 'That post could not be found.';
                } elseif (!hh_fanzone_can_manage_post($targetPost)) {
                    $boardError = 'You can only edit or delete your own posts.';
                } elseif ($action === 'delete') {
                    if ((int) $targetPost['parent_id'] === 0) {
                        $delete = mysqli_prepare($con, "UPDATE {$boardTable} SET is_deleted = 1, updated_at = NOW() WHERE (id = ? OR parent_id = ?) AND is_deleted = 0");
                        if ($delete) {
                            mysqli_stmt_bind_param($delete, 'ii', $postId, $postId);
                            mysqli_stmt_execute($delete);
                            mysqli_stmt_close($delete);
                            header('Location: fanzone.php?posted=delete#fanzoneBoard');
                            exit();
                        }
                    } else {
                        $delete = mysqli_prepare($con, "UPDATE {$boardTable} SET is_deleted = 1, updated_at = NOW() WHERE id = ? AND is_deleted = 0");
                        if ($delete) {
                            mysqli_stmt_bind_param($delete, 'i', $postId);
                            mysqli_stmt_execute($delete);
                            mysqli_stmt_close($delete);
                            header('Location: fanzone.php?posted=delete#fanzoneBoard');
                            exit();
                        }
                    }

                    $boardError = 'The post could not be deleted just now.';
                } else {
                    if ($body === '') {
                        $boardError = 'Please write a message before saving your edit.';
                    } elseif (mb_strlen($body) > 1200) {
                        $boardError = 'Please keep messages to 1200 characters or fewer.';
                    } else {
                        $isPinned = (int) $targetPost['is_pinned'];
                        $isAnnouncement = (int) $targetPost['is_announcement'];

                        if ((int) $targetPost['parent_id'] === 0 && hh_fanzone_is_admin()) {
                            $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
                            $isAnnouncement = isset($_POST['is_announcement']) ? 1 : 0;
                        }

                        $update = mysqli_prepare(
                            $con,
                            "UPDATE {$boardTable} SET message_body = ?, is_pinned = ?, is_announcement = ?, updated_at = NOW() WHERE id = ? LIMIT 1"
                        );

                        if ($update) {
                            mysqli_stmt_bind_param($update, 'siii', $body, $isPinned, $isAnnouncement, $postId);
                            if (mysqli_stmt_execute($update)) {
                                mysqli_stmt_close($update);
                                header('Location: fanzone.php?posted=edit#post-' . $postId);
                                exit();
                            }
                            mysqli_stmt_close($update);
                        }

                        $boardError = 'Your edit could not be saved just now.';
                    }
                }
            }
        }
    }
}

if (isset($_GET['posted'])) {
    if ($_GET['posted'] === 'thread') {
        $boardNotice = 'Your new thread has been posted.';
    } elseif ($_GET['posted'] === 'reply') {
        $boardNotice = 'Your reply has been added to the thread.';
    } elseif ($_GET['posted'] === 'edit') {
        $boardNotice = 'Your post has been updated.';
    } elseif ($_GET['posted'] === 'delete') {
        $boardNotice = 'The post has been removed.';
    }
}

if (isset($_GET['poll'])) {
    if ($_GET['poll'] === 'created') {
        $pollNotice = 'The new live poll is up and ready for players.';
    } elseif ($_GET['poll'] === 'voted') {
        $pollNotice = 'Vote saved. Here are the live results so far.';
    }
}

if ($boardSchemaReady && isset($_GET['edit_post']) && $_GET['edit_post'] !== '') {
    $editPostId = (int) $_GET['edit_post'];
    if ($editPostId > 0) {
        $editStmt = mysqli_prepare(
            $con,
            "SELECT id, parent_id, username, display_name, message_body, is_pinned, is_announcement FROM {$boardTable} WHERE id = ? AND is_deleted = 0 LIMIT 1"
        );
        if ($editStmt) {
            mysqli_stmt_bind_param($editStmt, 'i', $editPostId);
            mysqli_stmt_execute($editStmt);
            $editResult = mysqli_stmt_get_result($editStmt);
            $candidateEditPost = $editResult ? mysqli_fetch_assoc($editResult) : null;
            mysqli_stmt_close($editStmt);

            if ($candidateEditPost !== null && hh_fanzone_can_manage_post($candidateEditPost)) {
                $editingPost = $candidateEditPost;
            }
        }
    }
}

if ($boardSchemaReady) {
    if ($sessionUsername !== '') {
        $playerTotalsStmt = mysqli_prepare(
            $con,
            "SELECT
                COALESCE(SUM(CASE WHEN parent_id IS NULL THEN 1 ELSE 0 END), 0) AS thread_total,
                COALESCE(SUM(CASE WHEN parent_id IS NOT NULL THEN 1 ELSE 0 END), 0) AS reply_total
             FROM {$boardTable}
             WHERE username = ? AND is_deleted = 0"
        );

        if ($playerTotalsStmt) {
            mysqli_stmt_bind_param($playerTotalsStmt, 's', $sessionUsername);
            mysqli_stmt_execute($playerTotalsStmt);
            $playerTotalsResult = mysqli_stmt_get_result($playerTotalsStmt);
            if ($playerTotalsResult instanceof mysqli_result) {
                $playerTotals = mysqli_fetch_assoc($playerTotalsResult) ?: [];
                $yourThreadCount = (int) ($playerTotals['thread_total'] ?? 0);
                $yourReplyCount = (int) ($playerTotals['reply_total'] ?? 0);
                mysqli_free_result($playerTotalsResult);
            }
            mysqli_stmt_close($playerTotalsStmt);
        }
    }

    $threadSql = "
        SELECT
            p.id,
            p.parent_id,
            p.username,
            p.display_name,
            p.message_body,
            p.created_at,
            p.updated_at,
            p.is_pinned,
            p.is_announcement,
            COALESCE(rc.reply_total, 0) AS reply_total
        FROM {$boardTable} p
        LEFT JOIN (
            SELECT parent_id, COUNT(*) AS reply_total
            FROM {$boardTable}
            WHERE parent_id IS NOT NULL AND is_deleted = 0
            GROUP BY parent_id
        ) rc ON rc.parent_id = p.id
        WHERE p.parent_id IS NULL AND p.is_deleted = 0
        ORDER BY p.is_pinned DESC, p.is_announcement DESC, p.created_at DESC
        LIMIT 40
    ";

    $threadResult = mysqli_query($con, $threadSql);
    if ($threadResult) {
        while ($row = mysqli_fetch_assoc($threadResult)) {
            $row['id'] = (int) $row['id'];
            $row['reply_total'] = (int) $row['reply_total'];
            $threads[] = $row;
        }
        mysqli_free_result($threadResult);
    }

    if ($threads !== []) {
        $threadIds = array_map(static fn(array $thread): int => (int) $thread['id'], $threads);
        $threadIds = array_values(array_unique($threadIds));
        $replySql = "
            SELECT id, parent_id, username, display_name, message_body, created_at, updated_at
            FROM {$boardTable}
            WHERE parent_id IN (" . implode(',', $threadIds) . ") AND is_deleted = 0
            ORDER BY created_at ASC
        ";
        $replyResult = mysqli_query($con, $replySql);
        if ($replyResult) {
            while ($row = mysqli_fetch_assoc($replyResult)) {
                $parent = (int) $row['parent_id'];
                $replyMap[$parent][] = $row;
            }
            mysqli_free_result($replyResult);
        }
    }
}

if ($pollSchemaReady) {
    $teamFlagMap = hh_fanzone_build_team_flag_map($con);

    $activePollCountResult = mysqli_query($con, "SELECT COUNT(*) AS total FROM live_polls WHERE is_active = 1");
    if ($activePollCountResult instanceof mysqli_result) {
        $activePollCountRow = mysqli_fetch_assoc($activePollCountResult) ?: [];
        $activePollCount = (int) ($activePollCountRow['total'] ?? 0);
        mysqli_free_result($activePollCountResult);
    }

    if ($sessionUserId > 0) {
        $voteCountStmt = mysqli_prepare(
            $con,
            "SELECT COUNT(*) AS total_votes
             FROM live_poll_votes
             WHERE user_id = ?"
        );

        if ($voteCountStmt) {
            mysqli_stmt_bind_param($voteCountStmt, 'i', $sessionUserId);
            mysqli_stmt_execute($voteCountStmt);
            $voteCountResult = mysqli_stmt_get_result($voteCountStmt);
            if ($voteCountResult instanceof mysqli_result) {
                $voteCountRow = mysqli_fetch_assoc($voteCountResult) ?: [];
                $yourPollVoteCount = (int) ($voteCountRow['total_votes'] ?? 0);
                mysqli_free_result($voteCountResult);
            }
            mysqli_stmt_close($voteCountStmt);
        }
    }

    $activePollResult = mysqli_query($con, "SELECT id FROM live_polls WHERE is_active = 1 ORDER BY created_at DESC, id DESC LIMIT 1");
    if ($activePollResult instanceof mysqli_result) {
        $activePollRow = mysqli_fetch_assoc($activePollResult) ?: null;
        mysqli_free_result($activePollResult);
        if ($activePollRow !== null) {
            $currentPoll = hh_fanzone_fetch_poll_with_results($con, (int) $activePollRow['id'], $sessionUserId);
        }
    }

    $previousResult = mysqli_query($con, "SELECT id FROM live_polls WHERE is_active = 0 ORDER BY COALESCE(closed_at, created_at) DESC, id DESC LIMIT 5");
    if ($previousResult instanceof mysqli_result) {
        while ($previousRow = mysqli_fetch_assoc($previousResult)) {
            $poll = hh_fanzone_fetch_poll_with_results($con, (int) $previousRow['id'], $sessionUserId);
            if ($poll !== null) {
                $previousPolls[] = $poll;
            }
        }
        mysqli_free_result($previousResult);
    }
}

include "php/header.php";
include "php/navigation.php";
?>

<main id="main" class="main">
    <div class="page-hero page-hero--fanzone">
        <div>
            <p class="eyebrow">Supporters' corner</p>
            <h1>Fan Zone</h1>
            <p class="lead mb-0">A place for matchday chatter, hot takes, and the little bits of tournament fun.</p>
        </div>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="#fanzoneBoard"><i class="bi bi-chat-dots"></i> Message board</a>
            <a class="btn btn-outline-dark" href="dashboard.php"><i class="bi bi-grid"></i> Dashboard</a>
        </div>
    </div>

    <section class="section fanzone-page">
        <div class="fanzone-grid">
            <section class="fanzone-panel">
                <div class="fanzone-panel__header">
                    <div>
                        <p class="eyebrow">Live question</p>
                        <h2 id="fanzonePoll">Fan poll</h2>
                    </div>
                </div>
                <?php if ($pollNotice !== null) : ?>
                    <div class="alert alert-success" role="alert"><?= htmlspecialchars($pollNotice, ENT_QUOTES) ?></div>
                <?php endif; ?>

                <?php if ($pollError !== null) : ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($pollError, ENT_QUOTES) ?></div>
                <?php endif; ?>

                <?php if (!$pollSchemaReady) : ?>
                    <div class="fanzone-empty fanzone-empty--compact">
                        <i class="bi bi-bar-chart-line"></i>
                        <h3>Polls not ready yet</h3>
                        <p class="mb-0">Run the poll setup tables in the installation manager and this area can start carrying live player votes.</p>
                    </div>
                <?php elseif ($currentPoll !== null) : ?>
                    <div class="fanzone-poll-card">
                        <div class="fanzone-poll-card__header">
                            <div>
                                <span class="fanzone-chip fanzone-chip--soft"><?= $currentPoll['user_voted'] ? 'Results live' : 'Vote now' ?></span>
                                <h3><?= htmlspecialchars($currentPoll['question'], ENT_QUOTES) ?></h3>
                            </div>
                            <p class="concept-subtle mb-0"><?= (int) $currentPoll['total_votes'] ?> vote<?= (int) $currentPoll['total_votes'] === 1 ? '' : 's' ?> so far</p>
                        </div>

                        <?php if (!$currentPoll['user_voted']) : ?>
                            <form method="post" class="fanzone-poll-form">
                                <input type="hidden" name="fanzone_action" value="vote_poll">
                                <input type="hidden" name="poll_id" value="<?= (int) $currentPoll['id'] ?>">
                                <div class="fanzone-poll-options">
                                    <?php foreach ($currentPoll['options'] as $option) : ?>
                                        <?php $optionFlag = hh_fanzone_option_flag($teamFlagMap, (string) ($option['label'] ?? '')); ?>
                                        <label class="fanzone-poll-option">
                                            <input type="radio" name="option_id" value="<?= (int) $option['id'] ?>" required>
                                            <?php if ($optionFlag !== '') : ?>
                                                <img class="fanzone-poll-option__flag" src="<?= htmlspecialchars($optionFlag, ENT_QUOTES) ?>" alt="">
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($option['label'], ENT_QUOTES) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="fanzone-composer__actions">
                                    <span class="concept-subtle">Players see the results as soon as they vote.</span>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Submit vote</button>
                                </div>
                            </form>
                        <?php else : ?>
                            <div class="fanzone-poll-results">
                                <?php foreach ($currentPoll['options'] as $option) : ?>
                                    <?php $optionFlag = hh_fanzone_option_flag($teamFlagMap, (string) ($option['label'] ?? '')); ?>
                                    <div class="fanzone-poll-result<?= $option['selected_by_user'] ? ' is-selected' : '' ?>">
                                        <div class="fanzone-poll-result__meta">
                                            <strong>
                                                <?php if ($optionFlag !== '') : ?>
                                                    <img class="fanzone-poll-option__flag" src="<?= htmlspecialchars($optionFlag, ENT_QUOTES) ?>" alt="">
                                                <?php endif; ?>
                                                <?= htmlspecialchars($option['label'], ENT_QUOTES) ?>
                                            </strong>
                                            <span><?= (int) $option['votes'] ?> vote<?= (int) $option['votes'] === 1 ? '' : 's' ?> · <?= (int) $option['percent'] ?>%</span>
                                        </div>
                                        <div class="fanzone-poll-result__bar">
                                            <span style="width: <?= (int) $option['percent'] ?>%"></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($previousPolls !== []) : ?>
                            <details class="fanzone-poll-history">
                                <summary><i class="bi bi-clock-history"></i> Previous polls</summary>
                                <div class="fanzone-poll-history__list">
                                    <?php foreach ($previousPolls as $previousPoll) : ?>
                                        <article class="fanzone-poll-history__item">
                                            <div class="fanzone-poll-history__header">
                                                <h4><?= htmlspecialchars($previousPoll['question'], ENT_QUOTES) ?></h4>
                                                <span><?= (int) $previousPoll['total_votes'] ?> vote<?= (int) $previousPoll['total_votes'] === 1 ? '' : 's' ?></span>
                                            </div>
                                            <div class="fanzone-poll-results">
                                                <?php foreach ($previousPoll['options'] as $option) : ?>
                                                    <?php $optionFlag = hh_fanzone_option_flag($teamFlagMap, (string) ($option['label'] ?? '')); ?>
                                                    <div class="fanzone-poll-result">
                                                        <div class="fanzone-poll-result__meta">
                                                            <strong>
                                                                <?php if ($optionFlag !== '') : ?>
                                                                    <img class="fanzone-poll-option__flag" src="<?= htmlspecialchars($optionFlag, ENT_QUOTES) ?>" alt="">
                                                                <?php endif; ?>
                                                                <?= htmlspecialchars($option['label'], ENT_QUOTES) ?>
                                                            </strong>
                                                            <span><?= (int) $option['votes'] ?> · <?= (int) $option['percent'] ?>%</span>
                                                        </div>
                                                        <div class="fanzone-poll-result__bar">
                                                            <span style="width: <?= (int) $option['percent'] ?>%"></span>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <div class="fanzone-empty fanzone-empty--compact">
                        <i class="bi bi-megaphone"></i>
                        <h3>No live poll just now</h3>
                        <p class="mb-0">When there is something fun to vote on, the current poll will appear here and then flip to the live results once you answer.</p>
                    </div>
                <?php endif; ?>

                <?php if ($pollSchemaReady && hh_fanzone_is_admin()) : ?>
                    <details class="fanzone-admin-poll">
                        <summary><i class="bi bi-sliders2"></i> Create or replace the live poll</summary>
                        <form method="post" class="fanzone-poll-admin-form">
                            <input type="hidden" name="fanzone_action" value="create_poll">
                            <div>
                                <label class="form-label" for="pollQuestion">Question</label>
                                <input id="pollQuestion" type="text" class="form-control" name="poll_question" maxlength="255" value="<?= htmlspecialchars($pollDraftQuestion, ENT_QUOTES) ?>" placeholder="e.g. Which host nation will go the furthest?">
                            </div>
                            <div class="fanzone-poll-admin-form__grid">
                                <?php foreach ($pollDraftOptions as $index => $optionDraft) : ?>
                                    <div>
                                        <label class="form-label" for="pollOption<?= $index + 1 ?>">Option <?= $index + 1 ?></label>
                                        <input id="pollOption<?= $index + 1 ?>" type="text" class="form-control" name="poll_options[]" maxlength="120" value="<?= htmlspecialchars($optionDraft, ENT_QUOTES) ?>" placeholder="Answer option">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="fanzone-composer__actions">
                                <span class="concept-subtle">Launching a new poll closes the current one and keeps the older results below.</span>
                                <button type="submit" class="btn btn-outline-dark"><i class="bi bi-plus-circle"></i> Launch poll</button>
                            </div>
                        </form>
                    </details>
                <?php endif; ?>
            </section>

            <aside class="fanzone-panel fanzone-panel--side">
                <div class="fanzone-panel__header">
                    <div>
                        <p class="eyebrow"><?= htmlspecialchars((string) ($fanzoneTrivia['label'] ?? 'Did you know?'), ENT_QUOTES) ?></p>
                        <h2><?= htmlspecialchars((string) ($fanzoneTrivia['title'] ?? 'World Cup trivia'), ENT_QUOTES) ?></h2>
                    </div>
                </div>
                <div class="fanzone-trivia">
                    <p class="mb-0"><?= htmlspecialchars((string) ($fanzoneTrivia['body'] ?? 'More World Cup nuggets can live here over time.'), ENT_QUOTES) ?></p>
                </div>
            </aside>
        </div>

        <section class="fanzone-grid fanzone-grid--feature-row">
            <div class="fanzone-panel fanzone-quiz-card" id="fanzoneQuiz">
                <div class="fanzone-panel__header">
                    <div>
                        <p class="eyebrow">Quick fire</p>
                        <h2>Fan Zone Quiz</h2>
                    </div>
                    <span class="fanzone-chip fanzone-chip--soft"><span data-quiz-progress>1</span>/<?= count($quickFireQuiz) ?></span>
                </div>
                <p class="concept-subtle">Tap an answer, see if your correct, then onto the next question.</p>
                <div class="fanzone-quiz" data-fanzone-quiz>
                    <div class="fanzone-quiz__stage" data-quiz-stage></div>
                    <div class="fanzone-quiz__footer">
                        <span class="concept-subtle" data-quiz-status>Question 1 of <?= count($quickFireQuiz) ?></span>
                        <button type="button" class="btn btn-outline-dark btn-sm" data-quiz-restart hidden><i class="bi bi-arrow-repeat"></i> Play again</button>
                    </div>
                </div>
            </div>
            <div class="fanzone-panel fanzone-spot-card" id="fanzoneSpotTheBall">
                <div class="fanzone-panel__header">
                    <div>
                        <p class="eyebrow">Crowd game</p>
                        <h2>Spot The Ball</h2>
                    </div>
                    <span class="fanzone-chip fanzone-chip--soft"><span data-spot-progress>1</span>/<?= count($spotTheBallRounds) ?></span>
                </div>
                <p class="concept-subtle">Tap squares until you find the ball. Misses stay marked.</p>
                <div class="fanzone-spot" data-fanzone-spot>
                    <div class="fanzone-spot__stage" data-spot-stage></div>
                    <div class="fanzone-spot__footer">
                        <span class="concept-subtle" data-spot-status>Round 1 of <?= count($spotTheBallRounds) ?></span>
                        <button type="button" class="btn btn-outline-dark btn-sm" data-spot-restart hidden><i class="bi bi-arrow-repeat"></i> Play again</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="fanzone-panel" id="fanzoneBoard">
            <div class="fanzone-panel__header">
                <div>
                    <p class="eyebrow">Supporters' board</p>
                    <h2>Message Board</h2>
                </div>
            </div>
            <div class="fanzone-snapshot mb-4">
                <p><strong><?= count($threads) ?></strong><span>total live threads</span></p>
                <p><strong><?= array_sum(array_map(static fn(array $thread): int => (int) $thread['reply_total'], $threads)) ?></strong><span>total replies</span></p>
                <p><strong><?= $yourThreadCount ?></strong><span>your threads</span></p>
                <p><strong><?= $yourReplyCount ?></strong><span>your replies</span></p>
            </div>

            <?php if ($boardNotice !== null) : ?>
                <div class="alert alert-success" role="alert"><?= htmlspecialchars($boardNotice, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <?php if ($boardError !== null) : ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($boardError, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <?php if (!$boardReady) : ?>
                <div class="alert alert-warning mb-4" role="alert">
                    The new message board table is not in the database yet. Run the setup script at
                    <code>sql/setup-fanzone-board-table.sql</code> and refresh this page.
                </div>
            <?php elseif (!$boardSchemaReady) : ?>
                <div class="alert alert-warning mb-4" role="alert">
                    The Fan Zone board table needs the latest update. Run <code>sql/setup-fanzone-board-table.sql</code> to add pinned announcements and post management.
                </div>
            <?php endif; ?>

            <form method="post" class="fanzone-composer">
                <input type="hidden" name="fanzone_action" value="thread">
                <div class="fanzone-composer__header">
                    <div>
                        <h3>Start a new thread</h3>
                        <p class="mb-0">Ask a question, call your shot, or throw in a bit of banter.</p>
                    </div>
                    <p class="fanzone-composer__identity mb-0">Posting as <?= htmlspecialchars(hh_fanzone_display_name(), ENT_QUOTES) ?></p>
                </div>
                <?php if (hh_fanzone_is_admin()) : ?>
                    <div class="fanzone-composer__toggles">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_pinned" value="1">
                            <span class="form-check-label">Pin this thread</span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_announcement" value="1">
                            <span class="form-check-label">Mark as admin announcement</span>
                        </label>
                    </div>
                <?php endif; ?>
                <label class="visually-hidden" for="fanzoneNewMessage">Write a new message</label>
                <textarea id="fanzoneNewMessage" name="message_body" class="form-control" rows="4" maxlength="1200" placeholder="What's your hunch?"><?= htmlspecialchars($composerDraft, ENT_QUOTES) ?></textarea>
                <div class="fanzone-composer__actions">
                    <span class="concept-subtle">Keep it friendly and fun.</span>
                    <button type="submit" class="btn btn-primary"<?= $boardSchemaReady ? '' : ' disabled' ?>><i class="bi bi-send"></i> Post thread</button>
                </div>
            </form>

            <div class="fanzone-thread-list">
                <?php if ($boardReady && $threads === []) : ?>
                    <div class="fanzone-empty">
                        <i class="bi bi-chat-square-text"></i>
                        <h3>No threads yet</h3>
                        <p class="mb-0">The first bit of tournament chatter starts here.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($threads as $thread) : ?>
                    <?php $replies = $replyMap[$thread['id']] ?? []; ?>
                    <article class="fanzone-thread" id="post-<?= (int) $thread['id'] ?>">
                        <div class="fanzone-post<?= (int) $thread['is_announcement'] === 1 ? ' fanzone-post--announcement' : '' ?>">
                            <div class="fanzone-post__meta">
                                <div>
                                    <strong><?= htmlspecialchars($thread['display_name'], ENT_QUOTES) ?></strong>
                                    <span>
                                        <?= htmlspecialchars(hh_fanzone_format_datetime($thread['created_at']), ENT_QUOTES) ?>
                                        <?php if ($thread['updated_at'] !== $thread['created_at']) : ?>
                                            · edited
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="fanzone-post__badges">
                                    <?php if ((int) $thread['is_pinned'] === 1) : ?>
                                        <span class="fanzone-chip fanzone-chip--pin"><i class="bi bi-pin-angle-fill"></i> Pinned</span>
                                    <?php endif; ?>
                                    <?php if ((int) $thread['is_announcement'] === 1) : ?>
                                        <span class="fanzone-chip fanzone-chip--announcement"><i class="bi bi-megaphone-fill"></i> Admin update</span>
                                    <?php endif; ?>
                                    <span class="fanzone-chip fanzone-chip--soft"><?= (int) $thread['reply_total'] ?> replies</span>
                                </div>
                            </div>
                            <?php if ($editingPost !== null && (int) $editingPost['id'] === (int) $thread['id']) : ?>
                                <form method="post" class="fanzone-edit-form">
                                    <input type="hidden" name="fanzone_action" value="edit">
                                    <input type="hidden" name="post_id" value="<?= (int) $thread['id'] ?>">
                                    <?php if (hh_fanzone_is_admin()) : ?>
                                        <div class="fanzone-composer__toggles">
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_pinned" value="1"<?= (int) $thread['is_pinned'] === 1 ? ' checked' : '' ?>>
                                                <span class="form-check-label">Pinned</span>
                                            </label>
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_announcement" value="1"<?= (int) $thread['is_announcement'] === 1 ? ' checked' : '' ?>>
                                                <span class="form-check-label">Admin announcement</span>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                    <textarea name="message_body" class="form-control" rows="4" maxlength="1200"><?= htmlspecialchars($thread['message_body'], ENT_QUOTES) ?></textarea>
                                    <div class="fanzone-post__actions">
                                        <a class="btn btn-sm btn-outline-dark" href="fanzone.php#post-<?= (int) $thread['id'] ?>">Cancel</a>
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check2"></i> Save changes</button>
                                    </div>
                                </form>
                            <?php else : ?>
                                <p><?= nl2br(htmlspecialchars($thread['message_body'], ENT_QUOTES)) ?></p>
                                <?php if (hh_fanzone_can_manage_post($thread)) : ?>
                                    <div class="fanzone-post__actions">
                                        <a class="btn btn-sm btn-outline-dark" href="fanzone.php?edit_post=<?= (int) $thread['id'] ?>#post-<?= (int) $thread['id'] ?>"><i class="bi bi-pencil"></i> Edit</a>
                                        <form method="post" class="fanzone-inline-form" onsubmit="return confirm('Remove this thread and its replies?');">
                                            <input type="hidden" name="fanzone_action" value="delete">
                                            <input type="hidden" name="post_id" value="<?= (int) $thread['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i> Delete</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($replies !== []) : ?>
                            <div class="fanzone-replies">
                                <?php foreach ($replies as $reply) : ?>
                                    <div class="fanzone-post fanzone-post--reply" id="post-<?= (int) $reply['id'] ?>">
                                        <div class="fanzone-post__meta">
                                            <div>
                                                <strong><?= htmlspecialchars($reply['display_name'], ENT_QUOTES) ?></strong>
                                                <span>
                                                    <?= htmlspecialchars(hh_fanzone_format_datetime($reply['created_at']), ENT_QUOTES) ?>
                                                    <?php if ($reply['updated_at'] !== $reply['created_at']) : ?>
                                                        · edited
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if ($editingPost !== null && (int) $editingPost['id'] === (int) $reply['id']) : ?>
                                            <form method="post" class="fanzone-edit-form">
                                                <input type="hidden" name="fanzone_action" value="edit">
                                                <input type="hidden" name="post_id" value="<?= (int) $reply['id'] ?>">
                                                <textarea name="message_body" class="form-control" rows="3" maxlength="1200"><?= htmlspecialchars($reply['message_body'], ENT_QUOTES) ?></textarea>
                                                <div class="fanzone-post__actions">
                                                    <a class="btn btn-sm btn-outline-dark" href="fanzone.php#post-<?= (int) $reply['id'] ?>">Cancel</a>
                                                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check2"></i> Save changes</button>
                                                </div>
                                            </form>
                                        <?php else : ?>
                                            <p><?= nl2br(htmlspecialchars($reply['message_body'], ENT_QUOTES)) ?></p>
                                            <?php if (hh_fanzone_can_manage_post($reply)) : ?>
                                                <div class="fanzone-post__actions">
                                                    <a class="btn btn-sm btn-outline-dark" href="fanzone.php?edit_post=<?= (int) $reply['id'] ?>#post-<?= (int) $reply['id'] ?>"><i class="bi bi-pencil"></i> Edit</a>
                                                    <form method="post" class="fanzone-inline-form" onsubmit="return confirm('Remove this reply?');">
                                                        <input type="hidden" name="fanzone_action" value="delete">
                                                        <input type="hidden" name="post_id" value="<?= (int) $reply['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i> Delete</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="fanzone-reply-form">
                            <input type="hidden" name="fanzone_action" value="reply">
                            <input type="hidden" name="parent_id" value="<?= (int) $thread['id'] ?>">
                            <label class="visually-hidden" for="reply-<?= (int) $thread['id'] ?>">Reply to <?= htmlspecialchars($thread['display_name'], ENT_QUOTES) ?></label>
                            <textarea id="reply-<?= (int) $thread['id'] ?>" name="message_body" class="form-control" rows="2" maxlength="1200" placeholder="Reply to this thread"></textarea>
                            <div class="fanzone-composer__actions">
                                <span class="concept-subtle">Replying as <?= htmlspecialchars(hh_fanzone_display_name(), ENT_QUOTES) ?></span>
                                <button type="submit" class="btn btn-outline-success"<?= $boardSchemaReady ? '' : ' disabled' ?>><i class="bi bi-reply"></i> Post reply</button>
                            </div>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </section>
</main>

<?php
if (isset($con) && $con instanceof mysqli) {
    mysqli_close($con);
}
?>
<script>
window.hhFanzoneQuizQuestionBank = <?= json_encode($quickFireQuizBank, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
window.hhFanzoneQuizQuestions = <?= json_encode($quickFireQuiz, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
window.hhSpotTheBallRounds = <?= json_encode($spotTheBallRounds, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const quizRoot = document.querySelector('[data-fanzone-quiz]');
    const questionBank = Array.isArray(window.hhFanzoneQuizQuestionBank) ? window.hhFanzoneQuizQuestionBank : [];
    let questions = Array.isArray(window.hhFanzoneQuizQuestions) ? window.hhFanzoneQuizQuestions : [];

    if (!quizRoot || questionBank.length === 0 || questions.length === 0) {
        return;
    }

    const stage = quizRoot.querySelector('[data-quiz-stage]');
    const progress = document.querySelector('[data-quiz-progress]');
    const status = quizRoot.querySelector('[data-quiz-status]');
    const restartButton = quizRoot.querySelector('[data-quiz-restart]');

    let currentIndex = 0;
    let score = 0;
    let isLocked = false;

    function selectRandomQuestions() {
        const shuffled = questionBank
            .slice()
            .sort(() => Math.random() - 0.5);

        return shuffled.slice(0, Math.min(10, shuffled.length));
    }

    function createQuestionCard(question, index) {
        const card = document.createElement('article');
        card.className = 'fanzone-quiz__card';
        card.innerHTML = `
            <div class="fanzone-quiz__meta">
                <strong>Question ${index + 1}</strong>
            </div>
            <h3>${question.question}</h3>
            <div class="fanzone-quiz__options"></div>
        `;

        const options = card.querySelector('.fanzone-quiz__options');
        question.options.forEach((option) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'fanzone-quiz__option';
            button.textContent = option;
            button.dataset.correct = option === question.answer ? 'true' : 'false';
            button.addEventListener('click', function () {
                if (isLocked) {
                    return;
                }

                isLocked = true;
                const isCorrect = button.dataset.correct === 'true';
                if (isCorrect) {
                    score += 1;
                }

                options.querySelectorAll('.fanzone-quiz__option').forEach((optionButton) => {
                    optionButton.disabled = true;
                    if (optionButton.dataset.correct === 'true') {
                        optionButton.classList.add('is-correct');
                    } else if (optionButton === button) {
                        optionButton.classList.add('is-incorrect');
                    }
                });

                status.textContent = isCorrect
                    ? 'Correct — onto the next one.'
                    : `Not this time — the right answer was ${question.answer}.`;

                window.setTimeout(() => {
                    card.classList.add('is-leaving');
                    window.setTimeout(() => {
                        currentIndex += 1;
                        isLocked = false;
                        renderCurrentCard();
                    }, 280);
                }, 900);
            });
            options.appendChild(button);
        });

        return card;
    }

    function renderSummaryCard() {
        const card = document.createElement('article');
        card.className = 'fanzone-quiz__card fanzone-quiz__card--summary';
        card.innerHTML = `
            <div class="fanzone-quiz__summary-icon"><i class="bi bi-patch-check-fill"></i></div>
            <h3>Quiz complete</h3>
            <p>You got <strong>${score}</strong> out of <strong>${questions.length}</strong>.</p>
        `;
        stage.replaceChildren(card);
        if (progress) {
            progress.textContent = questions.length;
        }
        status.textContent = 'All done. Give it another go whenever you fancy.';
        restartButton.hidden = false;
    }

    function renderCurrentCard() {
        if (currentIndex >= questions.length) {
            renderSummaryCard();
            return;
        }

        const card = createQuestionCard(questions[currentIndex], currentIndex);
        card.classList.add('is-entering');
        stage.replaceChildren(card);

        requestAnimationFrame(() => {
            card.classList.remove('is-entering');
        });

        if (progress) {
            progress.textContent = String(currentIndex + 1);
        }
        status.textContent = `Question ${currentIndex + 1} of ${questions.length}`;
        restartButton.hidden = true;
    }

    restartButton.addEventListener('click', function () {
        questions = selectRandomQuestions();
        currentIndex = 0;
        score = 0;
        isLocked = false;
        renderCurrentCard();
    });

    renderCurrentCard();
});

document.addEventListener('DOMContentLoaded', function () {
    const gameRoot = document.querySelector('[data-fanzone-spot]');
    const rounds = Array.isArray(window.hhSpotTheBallRounds) ? window.hhSpotTheBallRounds : [];

    if (!gameRoot || rounds.length === 0) {
        return;
    }

    const stage = gameRoot.querySelector('[data-spot-stage]');
    const progress = document.querySelector('[data-spot-progress]');
    const status = gameRoot.querySelector('[data-spot-status]');
    const restartButton = gameRoot.querySelector('[data-spot-restart]');

    let currentIndex = 0;
    let solvedCount = 0;
    let isLocked = false;

    function createRoundCard(round, index) {
        const card = document.createElement('article');
        card.className = 'fanzone-spot__card';
        card.innerHTML = `
            <div class="fanzone-spot__meta">
                <strong>Round ${index + 1}</strong>
                <span>${round.title}</span>
            </div>
            <div class="fanzone-spot__board" style="--spot-cols:${round.cols}; --spot-rows:${round.rows};">
                <img src="${round.image}" alt="${round.title}">
                <div class="fanzone-spot__grid"></div>
            </div>
        `;

        const grid = card.querySelector('.fanzone-spot__grid');
        const totalCells = Math.max(1, Number(round.rows) * Number(round.cols));

        for (let cell = 1; cell <= totalCells; cell += 1) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'fanzone-spot__cell';
            button.setAttribute('aria-label', `Try square ${cell}`);

            button.addEventListener('click', function () {
                if (isLocked || button.classList.contains('is-tried')) {
                    return;
                }

                const isCorrect = cell === Number(round.answer);
                if (isCorrect) {
                    isLocked = true;
                    solvedCount += 1;
                    button.classList.add('is-correct');
                    status.textContent = 'Great, you found it — onto the next one.';

                    window.setTimeout(() => {
                        card.classList.add('is-leaving');
                        window.setTimeout(() => {
                            currentIndex += 1;
                            isLocked = false;
                            renderCurrentRound();
                        }, 280);
                    }, 900);
                } else {
                    button.classList.add('is-tried');
                    status.textContent = `Not there I'm afraid — keep hunting!`;
                }
            });

            grid.appendChild(button);
        }

        return card;
    }

    function renderSummaryCard() {
        const card = document.createElement('article');
        card.className = 'fanzone-spot__card fanzone-spot__card--summary';
        card.innerHTML = `
            <div class="fanzone-spot__summary-icon"><i class="bi bi-bullseye"></i></div>
            <h3>Spot the Ball complete</h3>
            <p>You found the ball in <strong>${solvedCount}</strong> out of <strong>${rounds.length}</strong> images.</p>
        `;

        stage.replaceChildren(card);
        if (progress) {
            progress.textContent = String(rounds.length);
        }
        status.textContent = 'All images cleared.';
        restartButton.hidden = false;
    }

    function renderCurrentRound() {
        if (currentIndex >= rounds.length) {
            renderSummaryCard();
            return;
        }

        const round = rounds[currentIndex];
        const card = createRoundCard(round, currentIndex);
        card.classList.add('is-entering');
        stage.replaceChildren(card);

        requestAnimationFrame(() => {
            card.classList.remove('is-entering');
        });

        if (progress) {
            progress.textContent = String(currentIndex + 1);
        }
        status.textContent = `Round ${currentIndex + 1} of ${rounds.length}`;
        restartButton.hidden = true;
    }

    restartButton.addEventListener('click', function () {
        currentIndex = 0;
        solvedCount = 0;
        isLocked = false;
        renderCurrentRound();
    });

    renderCurrentRound();
});
</script>
<?php
include "php/footer.php";
?>
