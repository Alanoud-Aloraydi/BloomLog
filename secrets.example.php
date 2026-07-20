<?php
// ============================================================
//  BloomLog — secrets template
//  Copy this file to "secrets.php" and fill in real values.
//  secrets.php is git-ignored and must NEVER be committed.
// ============================================================

// ---------- Database (for hosting, e.g. InfinityFree) ----------
// Leave these commented out for local development to use the
// defaults in config.php (localhost / root / root / bloomlog).
// define('DB_HOST', 'sqlXXX.infinityfree.com');
// define('DB_USER', 'if0_XXXXXXXX');
// define('DB_PASS', 'your_db_password');
// define('DB_NAME', 'if0_XXXXXXXX_bloomlog');

// ---------- OpenWeatherMap API key ----------
// https://openweathermap.org/api
define('WEATHER_API_KEY', 'your_openweathermap_api_key');

// ---------- Gmail (watering reminder emails) ----------
define('SMTP_USER', 'your_email@gmail.com');
// Gmail App Password (NOT your normal password):
// https://myaccount.google.com/apppasswords
define('SMTP_PASS', 'your_gmail_app_password');
?>
