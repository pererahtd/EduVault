<?php
// htdocs/projectest/forum_api.php
// JSON API used by discussion-qa.php (student page).

error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

// Same session and login check as the rest of the site
require_once __DIR__ . '/bootstrap_2.php';
require_once __DIR__ . '/forum_db.php';

const NEW_POST_STATUS = 'Active';   // 'Pending' = admin must approve first. Use 'Active' to publish instantly.
$uploadDir = __DIR__ . '/uploads/forum/';

/** Sends a JSON response and stops the script. */
function out($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Trims a value and cuts it to a maximum length. */
function clean($v, $max) {
    return mb_substr(trim((string) $v), 0, $max);
}

/**
 * Returns [ownerKey, displayName] for the logged-in student.
 * When nobody is logged in it returns a JSON 401 error (it never redirects).
 */
function ev_me() {
    $user = current_user();   // from bootstrap_2.php
    if (!$user) {
        out(['ok' => false, 'error' => 'Please log in first.'], 401);
    }
    $key  = 'u-' . (int) $user['id'];
    $name = (string) ($user['full_name'] ?? 'Student');
    return [$key, $name];
}

$db = ev_db();
if (!$db) out(['ok' => false, 'error' => 'Database connection not found.'], 500);

try {
    ev_forum_tables($db);
    [$meKey, $meName] = ev_me();
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

    $getPost = function ($id) use ($db) {
        $r = ev_run($db, "SELECT * FROM forum_posts WHERE id = ?", [(int)$id]);
        return $r[0] ?? null;
    };
    $getReply = function ($id) use ($db) {
        $r = ev_run($db, "SELECT * FROM forum_replies WHERE id = ?", [(int)$id]);
        return $r[0] ?? null;
    };

    /* ---------------- LIST ---------------- */
    if ($action === 'list') {
        $rows = ev_run($db, "SELECT id, type, title, author, owner_key, category, description, answer, photo, status,
                                    UNIX_TIMESTAMP(created_at) * 1000 AS ts, UNIX_TIMESTAMP(edited_at) * 1000 AS ets
                             FROM forum_posts ORDER BY created_at DESC, id DESC");
        $reps = ev_run($db, "SELECT r.id, r.post_id, r.author, r.owner_key, r.body, r.best,
                                    UNIX_TIMESTAMP(r.created_at) * 1000 AS ts,
                                    (SELECT COUNT(*) FROM forum_reply_likes l WHERE l.reply_id = r.id) AS likes,
                                    (SELECT COUNT(*) FROM forum_reply_likes l WHERE l.reply_id = r.id AND l.owner_key = ?) AS liked
                             FROM forum_replies r ORDER BY r.created_at, r.id", [$meKey]);
        $byPost = [];
        foreach ($reps as $r) {
            $byPost[$r['post_id']][] = [
                'id'        => (string)$r['id'],
                'author'    => $r['author'],
                'text'      => $r['body'],
                'ts'        => (int)round((float)$r['ts']),
                'likeCount' => (int)$r['likes'],
                'liked'     => (int)$r['liked'] > 0,
                'best'      => (int)$r['best'] === 1,
                'mine'      => $r['owner_key'] === $meKey,
            ];
        }
        $posts = [];
        foreach ($rows as $p) {
            $mine = ($p['owner_key'] !== null && $p['owner_key'] === $meKey);
            if ($p['status'] === 'Pending' && !$mine) continue;      // pending posts are only visible to their author
            $posts[] = [
                'id'          => (string)$p['id'],
                'type'        => $p['type'],
                'title'       => $p['title'],
                'user'        => $p['author'],
                'category'    => $p['category'],
                'description' => $p['description'],
                'answer'      => (string)$p['answer'],
                'photo'       => $p['photo'] ? 'uploads/forum/' . rawurlencode($p['photo']) : '',
                'status'      => $p['status'],
                'createdAt'   => (int)round((float)$p['ts']),
                'editedAt'    => $p['ets'] ? (int)round((float)$p['ets']) : null,
                'mine'        => $mine,
                'replies'     => $byPost[$p['id']] ?? [],
            ];
        }
        out(['ok' => true, 'me' => ['name' => $meName], 'posts' => $posts]);
    }

    /* ---------------- CREATE POST ---------------- */
    if ($action === 'create_post') {
        $type  = (($_POST['type'] ?? '') === 'qa') ? 'qa' : 'discussion';
        $title = clean($_POST['title'] ?? '', 160);
        $cat   = clean($_POST['category'] ?? '', 100);
        $desc  = clean($_POST['description'] ?? '', 2000);
        if ($title === '' || $cat === '' || $desc === '') throw new Exception('Please fill in the title, category and details.');
        $photo = ($type === 'qa') ? ev_save_photo('photo', $uploadDir) : null;
        ev_run($db, "INSERT INTO forum_posts (type, title, author, owner_key, category, description, answer, photo, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
               [$type, $title, $meName, $meKey, $cat, $desc, '', $photo, NEW_POST_STATUS]);
        out(['ok' => true, 'status' => NEW_POST_STATUS]);
    }

    /* ---------------- UPDATE POST (owner only) ---------------- */
    if ($action === 'update_post') {
        $post = $getPost($_POST['id'] ?? 0);
        if (!$post) throw new Exception('Post not found.');
        if ($post['owner_key'] !== $meKey) throw new Exception('You can only edit your own posts.');
        $title = clean($_POST['title'] ?? '', 160);
        $cat   = clean($_POST['category'] ?? '', 100);
        $desc  = clean($_POST['description'] ?? '', 2000);
        if ($title === '' || $cat === '' || $desc === '') throw new Exception('Please fill in the title, category and details.');
        $photo = $post['photo'];
        if ($post['type'] === 'qa') {
            if (!empty($_POST['remove_photo']) && $photo) { ev_delete_photo($photo, $uploadDir); $photo = null; }
            $new = ev_save_photo('photo', $uploadDir);
            if ($new) { if ($photo) ev_delete_photo($photo, $uploadDir); $photo = $new; }
        }
        ev_run($db, "UPDATE forum_posts SET title = ?, category = ?, description = ?, photo = ?, edited_at = NOW() WHERE id = ?",
               [$title, $cat, $desc, $photo, (int)$post['id']]);
        out(['ok' => true]);
    }

    /* ---------------- DELETE POST (owner only) ---------------- */
    if ($action === 'delete_post') {
        $post = $getPost($_POST['id'] ?? 0);
        if (!$post) throw new Exception('Post not found.');
        if ($post['owner_key'] !== $meKey) throw new Exception('You can only delete your own posts.');
        ev_delete_photo($post['photo'], $uploadDir);
        ev_run($db, "DELETE FROM forum_reply_likes WHERE reply_id IN (SELECT id FROM forum_replies WHERE post_id = ?)", [(int)$post['id']]);
        ev_run($db, "DELETE FROM forum_replies WHERE post_id = ?", [(int)$post['id']]);
        ev_run($db, "DELETE FROM forum_posts WHERE id = ?", [(int)$post['id']]);
        out(['ok' => true]);
    }

    /* ---------------- REPORT POST ---------------- */
    if ($action === 'report_post') {
        ev_run($db, "UPDATE forum_posts SET status = 'Reported' WHERE id = ? AND status = 'Active'", [(int)($_POST['id'] ?? 0)]);
        out(['ok' => true]);
    }

    /* ---------------- ADD REPLY ---------------- */
    if ($action === 'add_reply') {
        $post = $getPost($_POST['post_id'] ?? 0);
        if (!$post || $post['status'] === 'Pending') throw new Exception('You cannot reply to this post.');
        $text = clean($_POST['text'] ?? '', 1000);
        if ($text === '') throw new Exception('Please write something first.');
        ev_run($db, "INSERT INTO forum_replies (post_id, author, owner_key, body) VALUES (?, ?, ?, ?)",
               [(int)$post['id'], $meName, $meKey, $text]);
        out(['ok' => true]);
    }

    /* ---------------- LIKE / UNLIKE A REPLY ---------------- */
    if ($action === 'toggle_like') {
        $reply = $getReply($_POST['reply_id'] ?? 0);
        if (!$reply) throw new Exception('Reply not found.');
        $has = ev_run($db, "SELECT 1 AS x FROM forum_reply_likes WHERE reply_id = ? AND owner_key = ?", [(int)$reply['id'], $meKey]);
        if ($has) ev_run($db, "DELETE FROM forum_reply_likes WHERE reply_id = ? AND owner_key = ?", [(int)$reply['id'], $meKey]);
        else      ev_run($db, "INSERT INTO forum_reply_likes (reply_id, owner_key) VALUES (?, ?)", [(int)$reply['id'], $meKey]);
        out(['ok' => true]);
    }

    /* ---------------- MARK BEST ANSWER (question owner only) ---------------- */
    if ($action === 'toggle_best') {
        $reply = $getReply($_POST['reply_id'] ?? 0);
        if (!$reply) throw new Exception('Reply not found.');
        $post = $getPost($reply['post_id']);
        if (!$post || $post['type'] !== 'qa' || $post['owner_key'] !== $meKey) throw new Exception('Only the person who asked can pick the best answer.');
        $was = (int)$reply['best'] === 1;
        ev_run($db, "UPDATE forum_replies SET best = 0 WHERE post_id = ?", [(int)$post['id']]);
        if (!$was) ev_run($db, "UPDATE forum_replies SET best = 1 WHERE id = ?", [(int)$reply['id']]);
        out(['ok' => true, 'best' => !$was]);
    }

    /* ---------------- DELETE REPLY (author only) ---------------- */
    if ($action === 'delete_reply') {
        $reply = $getReply($_POST['reply_id'] ?? 0);
        if (!$reply) throw new Exception('Reply not found.');
        if ($reply['owner_key'] !== $meKey) throw new Exception('You can only delete your own replies.');
        ev_run($db, "DELETE FROM forum_reply_likes WHERE reply_id = ?", [(int)$reply['id']]);
        ev_run($db, "DELETE FROM forum_replies WHERE id = ?", [(int)$reply['id']]);
        out(['ok' => true]);
    }

    out(['ok' => false, 'error' => 'Unknown action.'], 400);

} catch (Throwable $e) {
    out(['ok' => false, 'error' => $e->getMessage()], 200);
}