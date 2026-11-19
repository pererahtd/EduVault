<?php
require __DIR__ . '/_auth.php';
require_once __DIR__ . '/../db.php'; 

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Authenticated, read-only listing for restoring events and announcements
// after refresh. Uses the same tables as the existing save/edit/delete APIs.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'list') {
    $events = [];
    $announcements = [];
    $sqls = [
        'events' => 'SELECT id, title, event_date AS date, event_time AS time, description FROM events ORDER BY event_date DESC, id DESC',
        'announcements' => 'SELECT id, title, publish_date AS date, category, description FROM announcements ORDER BY publish_date DESC, id DESC'
    ];
    foreach ($sqls as $kind => $sql) {
        $result = $conn->query($sql);
        if ($result === false) {
            error_log('EduVault failed to load ' . $kind . ': ' . $conn->error);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to load saved records.']);
            exit;
        }
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        if ($kind === 'events') $events = $rows;
        else $announcements = $rows;
    }
    echo json_encode(['success' => true, 'events' => $events, 'announcements' => $announcements]);
    exit;
}



if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $type = $_GET['type'];
    $id = intval($_GET['id']);
    $table = ($type === 'event') ? 'events' : 'announcements';
    
    if ($conn->query("DELETE FROM $table WHERE id=$id")) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $conn->error]);
    }
    exit;
}

// Insert / Update Request එකක් ආවොත්
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit;
}

$type = $data['type'] ?? '';
$id = isset($data['id']) ? intval($data['id']) : null;
$title = $conn->real_escape_string($data['title']);
$date = $conn->real_escape_string($data['date']);
$description = $conn->real_escape_string($data['description']);

if ($type === 'event') {
    $time = $conn->real_escape_string($data['time']);
    if ($id) {
        $sql = "UPDATE events SET title='$title', event_date='$date', event_time='$time', description='$description' WHERE id=$id";
    } else {
        $sql = "INSERT INTO events (title, event_date, event_time, description) VALUES ('$title', '$date', '$time', '$description')";
    }
} elseif ($type === 'announcement') {
    $category = $conn->real_escape_string($data['category']);
    if ($id) {
        $sql = "UPDATE announcements SET title='$title', category='$category', publish_date='$date', description='$description' WHERE id=$id";
    } else {
        $sql = "INSERT INTO announcements (title, category, publish_date, description) VALUES ('$title', '$category', '$date', '$description')";
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid Request"]);
    exit;
}

if ($conn->query($sql)) {
    $inserted_id = $id ? $id : $conn->insert_id;
    echo json_encode(["success" => true, "message" => "Saved successfully", "inserted_id" => $inserted_id]);
} else {
    echo json_encode(["success" => false, "message" => "Database Error: " . $conn->error]);
}
?>