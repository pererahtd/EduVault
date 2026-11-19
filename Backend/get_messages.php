<?php
require 'dp.php';

header('Content-Type: application/json');

if (isset($_GET['group_id'])) {
    $group_id = $_GET['group_id'];

    $stmt = $conn->prepare("
        SELECT m.id, m.group_id, m.user_id, m.system, m.message, m.created_at, u.name 
        FROM messages m 
        LEFT JOIN users u ON m.user_id = u.id 
        WHERE m.group_id = ? 
        ORDER BY m.created_at ASC
    ");
    
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = array();
    while($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode($messages);
    $stmt->close();
} else {
    echo json_encode(["error" => "Group ID is missing"]);
}

$conn->close();
?>