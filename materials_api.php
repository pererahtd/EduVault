<?php
require_once __DIR__ . '/bootstrap_2.php';
require_once __DIR__ . '/materials_store.php';
mat_boot();

$user = function_exists('current_user') ? current_user() : null;
if (!$user) mat_json(['ok' => false, 'error' => 'Please log in first.'], 401);

$owner = mat_owner_key($user);
if ($owner === '') mat_json(['ok' => false, 'error' => 'Could not identify your account.'], 403);
$ownerName = (string)($user['full_name'] ?? $user['name'] ?? $user['username'] ?? 'Student');

$action = (string)($_GET['action'] ?? 'list');
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

// ---------- read-only ----------
if (!$isPost) {
    $items = mat_load();
    if ($action === 'list') {                        // this student's own uploads (all statuses)
        $mine = array_values(array_filter($items, fn($m) => ($m['owner'] ?? '') === $owner));
        usort($mine, fn($a, $b) => ($b['created'] ?? 0) <=> ($a['created'] ?? 0));
        mat_json(['ok' => true, 'items' => array_map('mat_for_student', $mine)]);
    }
    if ($action === 'approved') {                    // everything approved by admin (for study-material pages)
        $ok = array_values(array_filter($items, fn($m) => ($m['status'] ?? '') === 'approved'));
        usort($ok, fn($a, $b) => ($b['reviewedAt'] ?? 0) <=> ($a['reviewedAt'] ?? 0));
        mat_json(['ok' => true, 'items' => array_map('mat_for_student', $ok)]);
    }
    mat_json(['ok' => false, 'error' => 'Unknown action.'], 400);
}


mat_check_csrf('mat_csrf');

$title       = mat_clean((string)($_POST['name'] ?? ''), 150);
$contributor = mat_clean((string)($_POST['contributor'] ?? ''), 100);
$batch       = mat_clean((string)($_POST['batch'] ?? ''), 75);
$module      = mat_clean((string)($_POST['module'] ?? ''), 100);

if ($action === 'upload' || $action === 'update') {
    if ($title === '' || $contributor === '' || $batch === '' || $module === '') {
        mat_json(['ok' => false, 'error' => 'Please fill in all fields.'], 422);
    }
}

if ($action === 'upload') {
    $f = $_FILES['file'] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $e = $f['error'] ?? UPLOAD_ERR_NO_FILE;
        $msg = ($e === UPLOAD_ERR_INI_SIZE || $e === UPLOAD_ERR_FORM_SIZE)
            ? 'The file is too large for the server.' : 'File upload failed. Please try again.';
        mat_json(['ok' => false, 'error' => $msg], 422);
    }
    if ($f['size'] > EV_MAT_MAX_BYTES) {
        mat_json(['ok' => false, 'error' => 'PDF must be 10 MB or smaller.'], 422);
    }
    if (strtolower(pathinfo((string)$f['name'], PATHINFO_EXTENSION)) !== 'pdf') {
        mat_json(['ok' => false, 'error' => 'Only PDF files are allowed.'], 422);
    }
    $head = (string)@file_get_contents($f['tmp_name'], false, null, 0, 5);
    $mime = function_exists('finfo_open') ? (string)finfo_file(finfo_open(FILEINFO_MIME_TYPE), $f['tmp_name']) : 'application/pdf';
    if ($head !== '%PDF-' || $mime !== 'application/pdf') {
        mat_json(['ok' => false, 'error' => 'That file is not a valid PDF.'], 422);
    }

    $pending = count(array_filter(mat_load(), fn($m) => ($m['owner'] ?? '') === $owner && ($m['status'] ?? '') === 'pending'));
    if ($pending >= EV_MAT_MAX_PENDING_PER_USER) {
        mat_json(['ok' => false, 'error' => 'You have too many pending uploads. Wait for approval or remove some.'], 429);
    }

    mat_boot();
    $stored = bin2hex(random_bytes(16)) . '.pdf';
    if (!move_uploaded_file($f['tmp_name'], EV_MAT_DIR . '/' . $stored)) {
        mat_json(['ok' => false, 'error' => 'Could not save the file on the server.'], 500);
    }
    $id = bin2hex(random_bytes(16));
    mat_mutate(function (array &$items) use ($id, $stored, $f, $title, $contributor, $batch, $module, $owner, $ownerName) {
        $items[] = [
            'id' => $id, 'title' => $title, 'contributor' => $contributor, 'batch' => $batch, 'module' => $module,
            'status' => 'pending', 'owner' => $owner, 'ownerName' => $ownerName,
            'file' => $stored, 'origName' => mat_clean((string)$f['name'], 150), 'size' => (int)$f['size'],
            'created' => time(), 'updated' => time(),
        ];
    });
    mat_json(['ok' => true, 'id' => $id]);
}

$id = (string)($_POST['id'] ?? '');
if (!mat_valid_id($id)) mat_json(['ok' => false, 'error' => 'Invalid item.'], 400);

if ($action === 'update') {                           // edit details of a PENDING upload
    $res = mat_mutate(function (array &$items) use ($id, $owner, $title, $contributor, $batch, $module) {
        foreach ($items as &$m) {
            if ($m['id'] !== $id) continue;
            if (($m['owner'] ?? '') !== $owner) return 'forbidden';
            if (($m['status'] ?? '') !== 'pending') return 'locked';
            $m['title'] = $title; $m['contributor'] = $contributor; $m['batch'] = $batch; $m['module'] = $module;
            $m['updated'] = time();
            return 'ok';
        }
        return 'missing';
    });
} elseif ($action === 'delete') {                     // remove own pending / rejected upload
    $res = mat_mutate(function (array &$items) use ($id, $owner) {
        foreach ($items as $i => $m) {
            if ($m['id'] !== $id) continue;
            if (($m['owner'] ?? '') !== $owner) return 'forbidden';
            if (($m['status'] ?? '') === 'approved') return 'locked';
            mat_delete_file($m);
            array_splice($items, $i, 1);
            return 'ok';
        }
        return 'missing';
    });
} else {
    mat_json(['ok' => false, 'error' => 'Unknown action.'], 400);
}

$errors = [
    'forbidden' => ['You can only change your own uploads.', 403],
    'locked'    => ['This file has already been reviewed and can no longer be changed.', 409],
    'missing'   => ['File not found.', 404],
];
if ($res !== 'ok') mat_json(['ok' => false, 'error' => $errors[$res][0]], $errors[$res][1]);
mat_json(['ok' => true]);