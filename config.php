<?php
/*
- EduVault settings.
- This is the ONLY file that holds database and error display settings.
 */

// Read the host name without the port (e.g. "localhost:80" becomes "localhost")
$requestHost = explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0];
$isLocal     = in_array($requestHost, ['localhost', '127.0.0.1'], true);

// Show errors only on your own PC. Online visitors see a clean page, and errors go to the log.
error_reporting(E_ALL);
ini_set('display_errors', $isLocal ? '1' : '0');
ini_set('log_errors', '1');

define('DB_HOST',    $isLocal ? 'localhost'                : 'sql201.infinityfree.com');
define('DB_NAME',    $isLocal ? 'if0_43016137_eduvault_db' : 'if0_43138244_eduvault');
define('DB_USER',    $isLocal ? 'root'                     : 'if0_43138244');
define('DB_PASS',    $isLocal ? ''                         : 'uV28nysk1JBxoN');
define('DB_PORT',    3306);
define('DB_CHARSET', 'utf8mb4');
define('GEMINI_API_KEY', 'AQ.Ab8RN6I-PfzP3hlwzYKxU5nYA5JBeLado7_86waayQONb8cUhA');
define('GEMINI_MODEL', 'gemini-3.5-flash');