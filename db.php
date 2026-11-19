<?php
/*
 * mysqli connection used by pages such as notice board.
 * Database settings come from config.php.
 */

require_once __DIR__ . '/config.php';

// Turn off automatic mysqli exceptions so we can handle errors ourselves
mysqli_report(MYSQLI_REPORT_OFF);

// The @ hides warnings. The connect_error check below handles failures.
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    // Write the real error to the log only. Do not show it to visitors.
    error_log('EduVault mysqli connection failed: ' . $conn->connect_error);
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit();
}

$conn->set_charset(DB_CHARSET);