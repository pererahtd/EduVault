<?php
session_start();
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
setcookie('ev_admin_alive', '', time() - 42000, '/');

session_destroy();

header('Cache-Control: no-store');
// This file lives in /Backend, login.php is in the parent folder
$reason = (($_GET['reason'] ?? '') === 'idle') ? '?expired=idle' : '';
header("Location: ../login.php" . $reason);
exit();
