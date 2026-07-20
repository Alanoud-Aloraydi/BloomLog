<?php
// ============================================================
//  BloomLog — central configuration
//  Every page includes this file, so database credentials and
//  secrets live in ONE place instead of being copy-pasted around.
//
//  Real credentials live in secrets.php (NOT committed to git).
//  Copy secrets.example.php to secrets.php and fill it in.
// ============================================================

// ---------- Maintenance mode ----------
$maintenanceMode = false; // set to true to show maintenance.php to visitors

// ---------- Load secrets first ----------
// secrets.php may define DB_* constants (for hosting) and the API/SMTP
// secrets. It is git-ignored, so real credentials never reach GitHub.
if (file_exists(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
}

// ---------- Database credentials ----------
// If secrets.php did not define them, fall back to environment
// variables and then to local development defaults.
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: 'root');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'bloomlog');

// ---------- Secret fallbacks ----------
// So the app still loads even if secrets.php is missing.
if (!defined('WEATHER_API_KEY')) define('WEATHER_API_KEY', '');
if (!defined('SMTP_USER'))       define('SMTP_USER', '');
if (!defined('SMTP_PASS'))       define('SMTP_PASS', '');

// ---------- Enforce maintenance mode ----------
if ($maintenanceMode && basename($_SERVER['PHP_SELF']) !== 'maintenance.php') {
    header('Location: maintenance.php');
    exit();
}
?>
