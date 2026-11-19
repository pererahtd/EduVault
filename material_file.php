<?php
require_once __DIR__ . '/bootstrap_2.php';
require_once __DIR__ . '/materials_store.php';
mat_boot();

$user  = function_exists('current_user') ? current_user() : null;
$admin = mat_is_admin();
if (!$user && !$admin) { http_response_code(403); exit('Please log in to open this file.'); }

$id = (string)($_GET['id'] ?? '');
if (!mat_valid_id($id)) { http_response_code(400); exit('Invalid file.'); }

$found = null;
foreach (mat_load() as $m) { if ($m['id'] === $id) { $found = $m; break; } }
if (!$found) { http_response_code(404); exit('File not found.'); }

$isOwner = $user && mat_owner_key($user) !== '' && mat_owner_key($user) === ($found['owner'] ?? '');
// admin: everything | owner: own files | any logged-in student: approved files only
if (!($admin || $isOwner || ($found['status'] ?? '') === 'approved')) { http_response_code(403); exit('Not allowed.'); }

$path = EV_MAT_DIR . '/' . basename((string)$found['file']);
if (!is_file($path)) { http_response_code(404); exit('File missing on server.'); }

session_write_close();
$base = preg_replace('/\.pdf$/i', '', (string)$found['title']);
$ascii = trim(preg_replace('/[^A-Za-z0-9._ -]+/', '_', $base), ' ._') ?: 'material';
$disp  = (isset($_GET['dl']) ? 'attachment' : 'inline');

header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($path));
header("Content-Disposition: $disp; filename=\"$ascii.pdf\"; filename*=UTF-8''" . rawurlencode($base . '.pdf'));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');
readfile($path);