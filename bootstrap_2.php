<?php

/*
 * EduVault shared bootstrap.
 */

// Database settings come from config.php
require_once __DIR__ . '/config.php';

define('LOGIN_PAGE', 'login.php');
// ---------- Session ----------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------- Database Connection ----------
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s;port=3306', DB_HOST, DB_NAME, DB_CHARSET);
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('EduVault DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            exit('Service temporarily unavailable.');
        }
    }

    return $pdo;
}

// ---------- Helpers ----------

/** Escapes text for HTML output. */
function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** "Kasun Perera" -> "Kasun" (used for greeting). */
function first_name(?string $fullName): string
{
    $parts = preg_split('/\s+/', trim((string) $fullName), -1, PREG_SPLIT_NO_EMPTY);
    return $parts[0] ?? '';
}
/**
 * Profile picture URL for a user.
 * Uses the uploaded picture when the file exists, otherwise a generated avatar.
 */
function ev_avatar_url(?array $user): string
{
    $name   = trim((string) ($user['full_name'] ?? '')) ?: ($user ? 'Student' : 'Guest');
    $letter = strtoupper(substr($name, 0, 1));

    $fallback = 'https://ui-avatars.com/api/?name=' . urlencode($letter)
              . '&background=00a859&color=fff&size=128&font-size=0.5&bold=true';

    $path = (string) ($user['profile_picture'] ?? '');
    if ($path !== '' && is_file(__DIR__ . '/' . $path)) {
        return $path . '?v=' . filemtime(__DIR__ . '/' . $path);
    }
    return $fallback;
}

// ---------- Auth ----------

function login_user(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => 'Lax',
        ]);
    }

    session_destroy();
}

function current_user(): ?array
{
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) {
        return null;
    }

       $stmt = db()->prepare(
        'SELECT id, name AS full_name, email, role, created_at, profile_picture
         FROM users WHERE id = ? LIMIT 1'
    );
    $stmt->execute([(int) $id]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_login(?string $role = null): array
{
    $user = current_user();

    if ($user === null) {
        header('Location: ' . LOGIN_PAGE . '?msg=login_required');
        exit;
    }

    if ($role !== null && $user['role'] !== $role) {
        http_response_code(403);
        exit('You do not have access to this page.');
    }

    return $user;
}
?>