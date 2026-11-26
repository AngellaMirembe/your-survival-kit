<?php
/**
 * Your Survival Kit — core configuration & database connection.
 * Include this file first on every page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------------------------------------------------------------
   Automatic base-path detection.
   Works when the folder is copied into htdocs/ as a sub-folder
   AND when its contents are deployed at a domain root.
----------------------------------------------------------------*/
$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__);
$appDir  = realpath(dirname(__DIR__));
$base    = '';
if ($docRoot && $appDir) {
    $docRoot = rtrim(str_replace('\\', '/', $docRoot), '/');
    $appDir  = rtrim(str_replace('\\', '/', $appDir), '/');
    $isWin   = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    $pos     = $isWin ? stripos($appDir, $docRoot) : strpos($appDir, $docRoot);
    if ($pos === 0) {
        $base = substr($appDir, strlen($docRoot));
    }
}
define('BASE_PATH', $base);

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
define('BASE_URL', ($isHttps ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_PATH . '/');

/* ---------------------------------------------------------------
   Database credentials — edit these for your hosting account.
----------------------------------------------------------------*/
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_survival_kit');
define('DB_USER', 'ysk_user');
define('DB_PASS', 'ysk_local_2026');
define('DB_CHARSET', 'utf8mb4');

/* ---------------------------------------------------------------
   Site constants
----------------------------------------------------------------*/
define('SITE_NAME', 'Your Survival Kit');
define('SITE_TAGLINE', 'Young people. Real hope. Brighter futures.');
define('MAIL_LOG_ONLY', getenv('YS_MAIL_LOG') === '1');
define('CURRENCY', 'UGX');
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');
define('MAX_UPLOAD', 5 * 1024 * 1024); // 5 MB

date_default_timezone_set('Africa/Kampala');

/* ---------------------------------------------------------------
   PDO connection (prepared statements enabled everywhere)
----------------------------------------------------------------*/
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die('<!doctype html><html><head><meta charset="utf-8"><title>Site unavailable</title></head>'
        . '<body style="font-family:sans-serif;text-align:center;padding:5rem">'
        . '<h1>We will be right back</h1><p>The site is temporarily unavailable. Please try again shortly.</p>'
        . '<!-- DB error: ' . htmlspecialchars($e->getMessage()) . ' --></body></html>');
}

/* ---------------------------------------------------------------
   Load editable site settings from the database
----------------------------------------------------------------*/
$settings = [];
try {
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
    foreach ($stmt as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    // Schema not imported yet — continue with defaults.
}

function setting(string $key, string $default = ''): string
{
    global $settings;
    return $settings[$key] ?? $default;
}

function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}
