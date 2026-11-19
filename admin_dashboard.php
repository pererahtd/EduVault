<?php
// Session Cookie Security Enhancements
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Enable Error Reporting for Debugging (Optional)
mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

// Connection Check
if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Session authentication check (Admin verification)
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['user_role'] ?? '')) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// Helper function to check if table exists
function tableExists($conn, $tableName) {
    $res = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($tableName) . "'");
    return ($res && $res->num_rows > 0);
}

// ==========================================
// 1. User Delete Handler (POST Request)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $delete_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($delete_id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $delete_id);
            if ($stmt->execute()) {
                $stmt->close();
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                exit();
            }
            $stmt->close();
        }
    }
    
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete user from database']);
    exit();
}

// ==========================================
// 2. User Update Handler (POST Request) - 
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_user') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
    $role    = isset($_POST['role']) ? trim($_POST['role']) : '';

    if ($user_id > 0 && !empty($name) && !empty($role)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, role = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $name, $role, $user_id);
            if ($stmt->execute()) {
                $stmt->close();
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
                exit();
            }
            $stmt->close();
        }
    }

    http_response_code(500);
    echo json_encode(['error' => 'Failed to update user in database']);
    exit();
}

// ==========================================
// 3. Fetch Dashboard Data (GET Request)
// ==========================================
$response = [
    'chart_labels' => [],
    'metrics' => [
        'logins' => [],
        'uploads' => [],
        'downloads' => [],
        'messages' => [],
        'discussions' => []
    ],
    'pending_notes' => [],
    'users' => [],
    'logs' => []
];

// Generate last 7 days chart labels
for ($i = 6; $i >= 0; $i--) {
    $response['chart_labels'][] = date('D', strtotime("-$i days"));
}

$hasActivityLog = tableExists($conn, 'activity_logs');
$hasResources = tableExists($conn, 'resources');
$hasForumPosts = tableExists($conn, 'forum_posts');

// Uploads per day count
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $cnt = 0;
    if ($hasResources) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM resources WHERE DATE(uploaded_at) = ?");
        if ($stmt) {
            $stmt->bind_param("s", $day);
            $stmt->execute();
            $stmt->bind_result($cnt_val);
            if ($stmt->fetch()) {
                $cnt = $cnt_val;
            }
            $stmt->close();
        }
    }
    $response['metrics']['uploads'][] = (int)$cnt;
}

// Discussions per day count
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $cnt = 0;
    if ($hasForumPosts) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM forum_posts WHERE DATE(created_at) = ?");
        if ($stmt) {
            $stmt->bind_param("s", $day);
            $stmt->execute();
            $stmt->bind_result($cnt_val);
            if ($stmt->fetch()) {
                $cnt = $cnt_val;
            }
            $stmt->close();
        }
    }
    $response['metrics']['discussions'][] = (int)$cnt;
}

// Activity logs metric parsing
$actionMap = ['logins' => 'login', 'downloads' => 'download', 'messages' => 'message'];
foreach ($actionMap as $key => $action) {
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-$i days"));
        $cnt = 0;
        if ($hasActivityLog) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM activity_logs WHERE action = ? AND DATE(timestamp) = ?");
            if ($stmt) {
                $stmt->bind_param("ss", $action, $day);
                $stmt->execute();
                $stmt->bind_result($cnt_val);
                if ($stmt->fetch()) {
                    $cnt = $cnt_val;
                }
                $stmt->close();
            }
        }
        $response['metrics'][$key][] = (int)$cnt;
    }
}

// Fetch pending resources waiting for approval
if ($hasResources) {
    $stmt = $conn->prepare(
        "SELECT r.id, r.title, r.file_path, u.name AS uploader
         FROM resources r
         JOIN users u ON r.uploader_id = u.id
         WHERE r.status = 'pending'
         ORDER BY r.uploaded_at DESC"
    );
    if ($stmt) {
        $stmt->execute();
        $stmt->bind_result($r_id, $r_title, $r_file_path, $u_uploader);
        while ($stmt->fetch()) {
            $response['pending_notes'][] = [
                'id' => $r_id,
                'title' => htmlspecialchars($r_title ?? '', ENT_QUOTES, 'UTF-8'),
                'file_path' => htmlspecialchars($r_file_path ?? '', ENT_QUOTES, 'UTF-8'),
                'uploader' => htmlspecialchars($u_uploader ?? '', ENT_QUOTES, 'UTF-8')
            ];
        }
        $stmt->close();
    }
}

// Fetch registered users with XSS Sanitization
if (tableExists($conn, 'users')) {
    $stmt = $conn->prepare("SELECT id, name, email, role FROM users ORDER BY id DESC");
    if ($stmt) {
        $stmt->execute();
        $stmt->bind_result($u_id, $u_name, $u_email, $u_role);
        while ($stmt->fetch()) {
            $response['users'][] = [
                'id' => $u_id,
                'name' => htmlspecialchars($u_name ?? '', ENT_QUOTES, 'UTF-8'),
                'email' => htmlspecialchars($u_email ?? '', ENT_QUOTES, 'UTF-8'),
                'role' => htmlspecialchars($u_role ?? '', ENT_QUOTES, 'UTF-8')
            ];
        }
        $stmt->close();
    }
}

// Fetch recent audit logs
if ($hasActivityLog) {
    $stmt = $conn->prepare(
        "SELECT u.email AS user_email, a.action, a.timestamp
         FROM activity_logs a
         JOIN users u ON a.user_id = u.id
         ORDER BY a.timestamp DESC
         LIMIT 20"
    );
    if ($stmt) {
        $stmt->execute();
        $stmt->bind_result($l_email, $l_action, $l_timestamp);
        while ($stmt->fetch()) {
            $response['logs'][] = [
                'user_email' => htmlspecialchars($l_email ?? '', ENT_QUOTES, 'UTF-8'),
                'action' => htmlspecialchars($l_action ?? '', ENT_QUOTES, 'UTF-8'),
                'timestamp' => $l_timestamp
            ];
        }
        $stmt->close();
    }
}

echo json_encode($response);
?>