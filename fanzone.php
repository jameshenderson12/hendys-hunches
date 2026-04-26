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

$boardTable = 'live_fanzone_posts';
$boardReady = isset($con) && $con instanceof mysqli && hh_fanzone_table_exists($con, $boardTable);
$boardSchema = $boardReady ? hh_fanzone_schema_ready($con, $boardTable) : ['ready' => false, 'missing' => []];
$boardSchemaReady = $boardReady && $boardSchema['ready'];
$boardError = null;
$boardNotice = null;
$composerDraft = '';
$editingPost = null;
$threads = [];
$replyMap = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fanzone_action'])) {
    if (!$boardReady || !$boardSchemaReady) {
        $boardError = 'The Fan Zone board needs its latest database setup before posts can be managed here.';
    } else {
        $action = (string) $_POST['fanzone_action'];
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

include "php/header.php";
include "php/navigation.php";
?>

<main id="main" class="main">
    <div class="page-hero page-hero--fanzone">
        <div>
            <p class="eyebrow">Supporters' corner</p>
            <h1>Fan Zone</h1>
            <p class="lead mb-0">A place for matchday chatter, hot takes, and the little bits of tournament fun that make the game feel alive.</p>
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
                        <p class="eyebrow">Coming alive</p>
                        <h2>Fan Zone nuggets</h2>
                    </div>
                </div>
                <div class="fanzone-nuggets">
                    <article class="fanzone-nugget">
                        <span class="fanzone-nugget__icon"><i class="bi bi-chat-heart"></i></span>
                        <div>
                            <h3>Message board</h3>
                            <p class="mb-0">Quick threads and replies for banter, reactions and questions during the tournament.</p>
                        </div>
                    </article>
                    <article class="fanzone-nugget fanzone-nugget--muted">
                        <span class="fanzone-nugget__icon"><i class="bi bi-emoji-sunglasses"></i></span>
                        <div>
                            <h3>More to come</h3>
                            <p class="mb-0">This page can also hold little extras later like fun facts, mini-polls, odd stats and supporter moments.</p>
                        </div>
                    </article>
                </div>
            </section>

            <aside class="fanzone-panel fanzone-panel--side">
                <div class="fanzone-panel__header">
                    <div>
                        <p class="eyebrow">At a glance</p>
                        <h2>Board snapshot</h2>
                    </div>
                </div>
                <div class="fanzone-snapshot">
                    <p><strong><?= count($threads) ?></strong><span>live threads</span></p>
                    <p><strong><?= array_sum(array_map(static fn(array $thread): int => (int) $thread['reply_total'], $threads)) ?></strong><span>replies so far</span></p>
                    <p><strong>Simple</strong><span>single-page board</span></p>
                </div>
                <p class="concept-subtle mb-0">Kept deliberately lightweight so players can dip in, post, reply, and get back to the football.</p>
            </aside>
        </div>

        <section class="fanzone-panel" id="fanzoneBoard">
            <div class="fanzone-panel__header">
                <div>
                    <p class="eyebrow">Supporters' board</p>
                    <h2>Message Board</h2>
                </div>
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
                    <span class="fanzone-chip"><?= htmlspecialchars(hh_fanzone_display_name(), ENT_QUOTES) ?></span>
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
include "php/footer.php";
?>
