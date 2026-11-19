<?php

session_start();

require_once 'db.php';

header('Content-Type: application/json');


// Check login
if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        "logged_in" => false
    ]);

    exit();

}


$user_id = $_SESSION['user_id'];


// Get user
$stmt = $conn->prepare(
    "SELECT id, name, email, role
     FROM users
     WHERE id = ?"
);


if (!$stmt) {

    echo json_encode([
        "logged_in" => false,
        "error" => "Database prepare failed"
    ]);

    exit();

}


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$result = $stmt->get_result();


if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();

    $user["logged_in"] = true;

    echo json_encode($user);

}

else {

    echo json_encode([
        "logged_in" => false
    ]);

}


$stmt->close();

$conn->close();

?>