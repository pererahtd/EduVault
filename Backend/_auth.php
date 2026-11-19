<?php
/**
 * EduVault Backend guard.  Every Backend page starts with:
 *     require __DIR__ . '/_auth.php';
 *
 *  - not signed in            -> ../login.php
 *  - signed in, not an admin  -> ../index.php
 *  - idle too long            -> session destroyed -> ../login.php?expired=idle
 *  - browser was closed/restarted -> session destroyed -> ../login.php?expired=browser
 */

// ===== SETTINGS (edit these if needed) =====================================
const EV_IDLE_TIMEOUT = 900;   // seconds of inactivity before auto sign-out (900 = 15 min)

// Session keys your login.php stores. The first one found is used.
const EV_ROLE_KEYS = ['role', 'user_role', 'usertype', 'user_type', 'type'];
const EV_NAME_KEYS = ['name', 'full_name', 'fullname', 'username', 'user_name', 'email'];
// Role values that count as admin (case-insensitive)
const EV_ADMIN_ROLES = ['admin', 'administrator'];

const EV_LOGIN_URL   = '../login.php';
const EV_STUDENT_URL = '../index.php';
// ===========================================================================

if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,            // cookie dies when the browser is closed
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Never let the browser cache admin pages (back button after sign-out)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function ev_first_session_value(array $keys) {
    foreach ($keys as $k) {
        if (isset($_SESSION[$k]) && $_SESSION[$k] !== '') return $_SESSION[$k];
    }
    return null;
}

function ev_is_admin(): bool {
    $role = ev_first_session_value(EV_ROLE_KEYS);
    if ($role === null) return false;
    return in_array(strtolower(trim((string)$role)), EV_ADMIN_ROLES, true);
}

function ev_admin_name(): string {
    return trim((string)(ev_first_session_value(EV_NAME_KEYS) ?? 'Admin'));
}

function ev_destroy_session(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    setcookie('ev_admin_alive', '', time() - 42000, '/');
    session_destroy();
}

function ev_redirect(string $url): void {
    if (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json' || ($_SERVER['HTTP_X_EV_AJAX'] ?? '') === '1') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'redirect' => $url]);
        exit;
    }
    header('Location: ' . $url);
    exit;
}

function ev_expire(string $why): void {
    ev_destroy_session();
    ev_redirect(EV_LOGIN_URL . '?expired=' . $why);
}

// ---- 1. must be signed in -------------------------------------------------
if (ev_first_session_value(EV_ROLE_KEYS) === null && ev_first_session_value(EV_NAME_KEYS) === null) {
    ev_redirect(EV_LOGIN_URL);
}

// ---- 2. must be an admin --------------------------------------------------
if (!ev_is_admin()) {
    ev_redirect(EV_STUDENT_URL);
}

// ---- 3. idle timeout ------------------------------------------------------
$now = time();
if (isset($_SESSION['ev_last_activity']) && ($now - (int)$_SESSION['ev_last_activity']) > EV_IDLE_TIMEOUT) {
    ev_expire('idle');
}

// ---- 4. browser restart check --------------------------------------------
// A cookie with no expiry is removed when the browser closes. If the PHP session
// is still alive but that cookie is gone, the browser was restarted.
if (empty($_SESSION['ev_admin_token'])) {
    $token = bin2hex(random_bytes(16));
    $_SESSION['ev_admin_token'] = $token;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('ev_admin_alive', $token, [
        'expires' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax',
    ]);
} elseif (!hash_equals($_SESSION['ev_admin_token'], (string)($_COOKIE['ev_admin_alive'] ?? ''))) {
    ev_expire('browser');
}

// Every guarded request (including keepalive.php, which session-guard.js only
// calls while the admin is really active) refreshes the idle timer.
$_SESSION['ev_last_activity'] = $now;
if (empty($_SESSION['ev_csrf'])) $_SESSION['ev_csrf'] = bin2hex(random_bytes(16));

/** Echo into <head> of every Backend page: admin name + idle-timeout script. */
function ev_head(): void {
    $cfg = ['name' => ev_admin_name(), 'idle' => EV_IDLE_TIMEOUT];
    echo '<script>window.eduVaultCurrentAdmin=' . json_encode(['name' => $cfg['name']], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) . ';'
       . 'window.eduVaultSession={idle:' . (int)$cfg['idle'] . ',csrf:' . json_encode($_SESSION['ev_csrf']) . '};</script>' . "\n"
       . '<script src="session-guard.js?v=1" defer></script>' . "\n";
}
