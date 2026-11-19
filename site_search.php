<?php
/* site_search.php - live search for notices, groups and approved study material */
require_once __DIR__ . '/bootstrap_2.php';
require_once __DIR__ . '/materials_store.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function ss_out(array $items): void
{
    echo json_encode(['ok' => true, 'items' => $items], JSON_UNESCAPED_UNICODE);
    exit;
}

$user = current_user();
$q    = trim((string) ($_GET['q'] ?? ''));

// Logged-in students only, and only for 2 to 60 characters
if (!$user || mb_strlen($q) < 2 || mb_strlen($q) > 60) {
    ss_out([]);
}

$like  = '%' . addcslashes($q, '%_\\') . '%';
$items = [];

try {
    // Active notices (not expired)
    $st = db()->prepare(
        "SELECT title FROM notices
         WHERE (title LIKE ? OR body LIKE ?) AND (expires_at IS NULL OR expires_at >= CURDATE())
         ORDER BY created_at DESC LIMIT 4"
    );
    $st->execute([$like, $like]);
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $title) {
        $items[] = ['t' => $title, 's' => 'Notice', 'i' => 'fa-bullhorn', 'u' => 'notice-board_forst.php'];
    }

    // Groups
    $st = db()->prepare('SELECT name FROM groups WHERE name LIKE ? OR description LIKE ? ORDER BY name LIMIT 4');
    $st->execute([$like, $like]);
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $name) {
        $items[] = ['t' => $name, 's' => 'Group', 'i' => 'fa-users', 'u' => 'groups.php'];
    }
} catch (Throwable $e) {
    error_log('site_search database: ' . $e->getMessage());
}

// Approved study material (same rule as material_file.php: any logged-in student)
if (function_exists('mat_load')) {
    $n = 0;
    foreach (mat_load() as $m) {
        if (strtolower((string) ($m['status'] ?? '')) !== 'approved') {
            continue;
        }
        $hay = ($m['title'] ?? '') . ' ' . ($m['module'] ?? '') . ' ' . ($m['batch'] ?? '');
        if (stripos($hay, $q) === false) {
            continue;
        }
        $items[] = [
            't' => (string) ($m['title'] ?? 'Material'),
            's' => 'Study material · ' . ($m['module'] ?? ''),
            'i' => 'fa-file-pdf',
            'u' => 'material_file.php?id=' . rawurlencode((string) $m['id']),
        ];
        if (++$n >= 5) {
            break;
        }
    }
}

ss_out($items);