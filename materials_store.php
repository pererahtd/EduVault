<?php
/**
 * EduVault study-material storage (shared by the student pages and the Backend).
 * PDFs + materials.json live in /material_data (blocked from the web by .htaccess).
 */
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'materials_store.php') {
    http_response_code(403);
    exit;
}

const EV_MAT_DIR                  = __DIR__ . '/material_data';
const EV_MAT_DB                   = EV_MAT_DIR . '/materials.json';
const EV_MAT_MAX_BYTES            = 10 * 1024 * 1024;   // 10 MB per PDF
const EV_MAT_MAX_PENDING_PER_USER = 25;

const EV_MAT_ROLE_KEYS   = ['role', 'user_role', 'usertype', 'user_type', 'type'];
const EV_MAT_ADMIN_ROLES = ['admin', 'administrator'];

function mat_boot(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (!is_dir(EV_MAT_DIR)) {
        @mkdir(EV_MAT_DIR, 0755, true);
    }
    $ht = EV_MAT_DIR . '/.htaccess';
    if (!is_file($ht)) {
        @file_put_contents(
            $ht,
            "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n" .
            "<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n"
        );
    }
}

function mat_json(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function mat_csrf(): string {
    if (empty($_SESSION['mat_csrf'])) {
        $_SESSION['mat_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['mat_csrf'];
}

function mat_check_csrf(string $sessionKey = 'mat_csrf'): void {
    $sent = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $real = (string)($_SESSION[$sessionKey] ?? '');
    if ($real === '' || $sent === '' || !hash_equals($real, $sent)) {
        mat_json(['ok' => false, 'error' => 'Security check failed. Please refresh the page and try again.'], 403);
    }
}

function mat_is_admin(): bool {
    foreach (EV_MAT_ROLE_KEYS as $k) {
        if (isset($_SESSION[$k]) && $_SESSION[$k] !== '') {
            return in_array(strtolower(trim((string)$_SESSION[$k])), EV_MAT_ADMIN_ROLES, true);
        }
    }
    return false;
}

function mat_owner_key(array $user): string {
    foreach (['id', 'user_id', 'student_id', 'email', 'username', 'full_name'] as $k) {
        if (!empty($user[$k])) {
            return (string)$user[$k];
        }
    }
    return '';
}

function mat_clean(string $s, int $max): string {
    $s = trim(strip_tags($s));
    $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
    return function_exists('mb_substr') ? mb_substr($s, 0, $max) : substr($s, 0, $max);
}

function mat_valid_id(string $id): bool {
    return (bool)preg_match('/^[a-f0-9]{32}$/', $id);
}

function mat_load(): array {
    clearstatcache(true, EV_MAT_DB);
    if (!is_file(EV_MAT_DB)) {
        return [];
    }
    $d = json_decode((string)@file_get_contents(EV_MAT_DB), true);
    return is_array($d) ? $d : [];
}

function mat_mutate(callable $fn) {
    mat_boot();
    $lock = fopen(EV_MAT_DIR . '/.lock', 'c');
    if (!$lock) {
        throw new RuntimeException('Storage unavailable');
    }
    flock($lock, LOCK_EX);
    try {
        $items  = mat_load();
        $result = $fn($items);
        $tmp    = EV_MAT_DB . '.tmp';
        file_put_contents($tmp, json_encode(array_values($items), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        rename($tmp, EV_MAT_DB);
        clearstatcache(true, EV_MAT_DB);
        return $result;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function mat_delete_file(array $m): void {
    $f = EV_MAT_DIR . '/' . basename((string)($m['file'] ?? ''));
    if (is_file($f)) {
        @unlink($f);
    }
}

/**
 * Converts one stored material into the shape youruploads.php expects.
 * Internal fields (owner key, stored file name) are not sent to the browser.
 */
function mat_for_student(array $m): array {
    $id   = (string)($m['id'] ?? '');
    $view = 'material_file.php?id=' . rawurlencode($id);

    return [
        'id'          => $id,
        'name'        => (string)($m['title'] ?? ''),
        'contributor' => (string)($m['contributor'] ?? ''),
        'batch'       => (string)($m['batch'] ?? ''),
        'module'      => (string)($m['module'] ?? ''),
        'status'      => strtolower((string)($m['status'] ?? 'pending')),
        'created'     => (int)($m['created'] ?? 0),
        'url'         => $view,             // inline PDF view (used by the viewer modal)
        'dl'          => $view . '&dl=1',   // forces a download
    ];
}
