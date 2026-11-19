<?php
session_start();
require 'dp.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Unauthorized: Please login first"]);
        exit();
    }

    $group_id = $_POST['group_id'];
    $message = $_POST['message'];
    $user_id = $_SESSION['user_id']; 
    $system = isset($_POST['system']) ? $_POST['system'] : 0; 

   
    $stmt = $conn->prepare("INSERT INTO messages (group_id, user_id, system, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $group_id, $user_id, $system, $message);
    
    if($stmt->execute()){
        echo json_encode(["status" => "success"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to insert message"]);
    }
    
    $stmt->close();
}
$conn->close();
?>