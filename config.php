<?php
// ============================================================
//  BloomLog — central configuration
//  Every page includes this file, so database credentials and
//  secrets live in ONE place instead of being copy-pasted around.
//
//  For deployment (e.g. InfinityFree), either set environment
//  variables or edit the fallback values below.
// ============================================================

// ---------- Maintenance mode ----------
$maintenanceMode = false; // set to true to show maintenance.php to visitors

// ---------- Database credentials ----------
// Reads from environment variables first (good for hosting), then
// falls back to local development defaults.
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: 'root');
    define('DB_NAME', getenv('DB_NAME') ?: 'bloomlog');
}

// ---------- Secrets (weather API key, email/SMTP) ----------
// Real secrets are kept in secrets.php, which is NOT committed to
// git. Copy secrets.example.php to secrets.php and fill it in.
if (file_exists(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
}
// Safe fallbacks so the app still loads if secrets.php is missing.
if (!defined('WEATHER_API_KEY')) define('WEATHER_API_KEY', '');
if (!defined('SMTP_USER'))       define('SMTP_USER', '');
if (!defined('SMTP_PASS'))       define('SMTP_PASS', '');

// ---------- Enforce maintenance mode ----------
if ($maintenanceMode && basename($_SERVER['PHP_SELF']) !== 'maintenance.php') {
    header('Location: maintenance.php');
    exit();
}
?>
