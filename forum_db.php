<?php
// forum_db.php  ->  htdocs/forum_db.php
// Shared database helpers for the Discussion Forum & Q&A (used by the admin page and the student API).
// Works with both PDO ($pdo) and MySQLi ($conn) coming from db.php. The tables are created automatically.

require_once __DIR__ . '/db.php';

function ev_db() {
    global $pdo, $conn;
    if (isset($pdo) && $pdo instanceof PDO) return $pdo;
    if (isset($conn) && $conn instanceof mysqli) return $conn;
    return null;
}

// Run one prepared statement. Returns rows for SELECT, [] for everything else.
function ev_run($db, $sql, $params = []) {
    if ($db instanceof PDO) {
        $st = $db->prepare($sql);
        $st->execute($params);
        return $st->columnCount() ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    $st = $db->prepare($sql);
    if (!$st) throw new Exception('SQL error: ' . $db->error);
    if ($params) {
        $types = '';
        foreach ($params as $p) { $types .= is_int($p) ? 'i' : 's'; }
        $st->bind_param($types, ...$params);
    }
    $st->execute();
    $res = $st->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function ev_last_id($db) {
    return $db instanceof PDO ? (int)$db->lastInsertId() : (int)$db->insert_id;
}

function ev_forum_tables($db) {
    static $done = false;
    if ($done) return;
    $done = true;
    $sqls = [
        "CREATE TABLE IF NOT EXISTS forum_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(20) NOT NULL DEFAULT 'discussion',
            title VARCHAR(160) NOT NULL,
            author VARCHAR(100) NOT NULL,
            owner_key VARCHAR(100) NULL,
            category VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            answer TEXT NULL,
            photo VARCHAR(255) NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'Active',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            edited_at TIMESTAMP NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS forum_replies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            author VARCHAR(100) NOT NULL,
            owner_key VARCHAR(100) NULL,
            body TEXT NOT NULL,
            best TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_post (post_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS forum_reply_likes (
            reply_id INT NOT NULL,
            owner_key VARCHAR(100) NOT NULL,
            PRIMARY KEY (reply_id, owner_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];
    foreach ($sqls as $sql) {
        if ($db instanceof PDO) $db->exec($sql); else $db->query($sql);
    }
}

// Saves an uploaded image (JPG/PNG/WebP/GIF, max 800 KB). Returns the stored file name, or null if nothing was uploaded.
function ev_save_photo($field, $dir) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new Exception('Photo upload failed.');
    if ($f['size'] > 800 * 1024) throw new Exception('Photo must be smaller than 800 KB.');
    $info = @getimagesize($f['tmp_name']);
    $map = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($map[$info[2]])) throw new Exception('Photo must be a JPG, PNG, WebP or GIF image.');
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }
    $name = bin2hex(random_bytes(8)) . '.' . $map[$info[2]];
    if (!move_uploaded_file($f['tmp_name'], $dir . $name)) throw new Exception('Could not save the photo.');
    return $name;
}

function ev_delete_photo($name, $dir) {
    if ($name) {
        $p = $dir . basename($name);
        if (is_file($p)) { @unlink($p); }
    }
}