
<?php

require_once "config.php";

// ---------- DATABASE CONNECTION ----------
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("❌ Database connection failed.");
}

// ---------- FIXED SERVER TIMEZONE (Production Standard) ----------
date_default_timezone_set("Asia/Riyadh");

// ---------- BACKUP DIRECTORY ----------
$backupFolder = __DIR__ . "/backups";

if (!file_exists($backupFolder)) {
    mkdir($backupFolder, 0777, true);
}

// ---------- BACKUP FILE NAME ----------
$date = date("Y-m-d");
$fileName = "backup_$date.sql";
$filePath = $backupFolder . "/" . $fileName;

// ---------- MYSQLDUMP PATH (Windows / MAMP) ----------
// NOTE:
// - This path works only in local MAMP environment.
// - When moving to hosting/server, update mysqldump path.
// - Example server path could be: /usr/bin/mysqldump or /usr/local/mysql/bin/mysqldump
// - Do NOT remove this comment — final deployment step requires updating this value.
$mysqldumpPath = '"C:\MAMP\bin\mysql\bin\mysqldump.exe"';

// ---------- BACKUP COMMAND ----------
$command = "$mysqldumpPath --user=" . DB_USER . " --password=" . DB_PASS
         . " --host=" . DB_HOST . " --no-tablespaces " . DB_NAME . " > \"$filePath\"";

// ---------- EXECUTE BACKUP ----------
exec($command, $output, $result);


// ---------- RESULT FEEDBACK ----------
if ($result === 0) {
    echo "✅ Backup successfully created: <strong>$fileName</strong>";
} else {
    echo "❌ Backup failed. Please check mysqldump path and permissions.";
}

?>