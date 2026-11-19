<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Only admin can approve resources
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['user_role'] ?? '')) !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit();
}

// CSRF Token Validation - Prevents Cross-Site Request Forgery attacks
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

$stmt = $conn->prepare("UPDATE resources SET status = 'approved' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}
?>