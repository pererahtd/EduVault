<?php
session_start();
require_once 'db.php';
require_once 'materials_store.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['user_role'] ?? '')) !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit();
}

if (!isset($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'CSRF validation failed']);
    exit();
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid resource ID']);
    exit();
}

$title = '';
$q = $conn->prepare("SELECT title FROM resources WHERE id = ?");
$q->bind_param("i", $id);
if ($q->execute()) {
    $res = $q->get_result()->fetch_assoc();
    $title = trim((string)($res['title'] ?? ''));
}

$stmt = $conn->prepare("UPDATE resources SET status = 'approved' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($title !== '') {
        mat_mutate(function (array &$items) use ($title) {
            foreach ($items as &$m) {
                if (strcasecmp(trim((string)($m['title'] ?? '')), $title) === 0 && ($m['status'] ?? '') !== 'approved') {
                    $m['status'] = 'approved';
                    $m['reviewedAt'] = time();
                }
            }
        });
    }
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}
