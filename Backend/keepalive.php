<?php
// Called by session-guard.js while the admin is actually active.
// _auth.php refreshes the idle timer; if the session is dead it answers 401.
require __DIR__ . '/_auth.php';
header('Content-Type: application/json');
echo json_encode(['ok' => true, 'idle' => EV_IDLE_TIMEOUT]);
