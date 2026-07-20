<?php

require_once "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) die("❌ DB connection error");

// ===== City → Timezone mapping =====
$timezoneMap = [
    "riyadh" => "Asia/Riyadh",
    "jeddah" => "Asia/Riyadh",
    "dammam" => "Asia/Riyadh",
    "khobar" => "Asia/Riyadh",
    "abha" => "Asia/Riyadh",
    "medina" => "Asia/Riyadh",
    "mecca" => "Asia/Riyadh",
    "dubai" => "Asia/Dubai",
    "abu dhabi" => "Asia/Dubai",
    "doha" => "Asia/Qatar",
    "manama" => "Asia/Bahrain",
    "kuwait city" => "Asia/Kuwait",
    "muscat" => "Asia/Muscat",
    "cairo" => "Africa/Cairo",
    "alexandria" => "Africa/Cairo",
    "london" => "Europe/London",
    "paris" => "Europe/Paris",
    "berlin" => "Europe/Berlin",
    "rome" => "Europe/Rome",
    "new york" => "America/New_York",
    "los angeles" => "America/Los_Angeles",
    "chicago" => "America/Chicago",
    "seattle" => "America/Los_Angeles",
    "tokyo" => "Asia/Tokyo",
    "osaka" => "Asia/Tokyo",
    "seoul" => "Asia/Seoul",
    "singapore" => "Asia/Singapore",
    "bangkok" => "Asia/Bangkok",
    "hong kong" => "Asia/Hong_Kong"
];



// ===== Fetch Users =====
$userQuery = $conn->query("SELECT userid, name, email, city FROM users");

while ($user = $userQuery->fetch_assoc()) {

    $userID = $user["userid"];
    $email = $user["email"];
    $username = $user["name"];
    $city = strtolower(trim($user["city"]));

    // 1) Apply timezone based on user's stored city
    $timezone = $timezoneMap[$city] ?? "UTC";
    date_default_timezone_set($timezone);

    // 2) Get today based on user's timezone
    $today = date("Y-m-d");


    // 3) Get plants due today
    $plantQuery = $conn->prepare("
        SELECT user_plant_id, nickname 
        FROM userplants 
        WHERE user_id = ? AND next_watered_date = ?
    ");
    $plantQuery->bind_param("is", $userID, $today);
    $plantQuery->execute();
    $plants = $plantQuery->get_result();

    $plantList = "";
    $sendEmail = false;

    while ($plant = $plants->fetch_assoc()) {

        $sendEmail = true;
        $plantList .= "🌿 <strong>{$plant['nickname']}</strong><br>";

    }

    if (!$sendEmail) continue;

    // ===== Send Email using Gmail SMTP =====
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER; // set in secrets.php
        $mail->Password = SMTP_PASS; // set in secrets.php (Gmail App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";

        $mail->setFrom($mail->Username, "BloomLog Reminder");
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "💧 Watering Reminder — $today";

        $mail->Body = "
            <div style='font-family:Arial;padding:20px;background:#f6fff6;border-radius:10px;'>
                <h2>Hello $username 👋</h2>
                <p>The following plants need watering today based on your region (<strong>$city</strong>):</p>
                <p style='font-size:18px;'>$plantList</p>
                <p style='margin-top:20px'>💚 Keep your plants hydrated!</p>
            </div>
        ";

        $mail->send();

    } catch (Exception $e) {
        echo "❌ Failed: {$mail->ErrorInfo}<br>";
    }
}


echo "📩 Reminder check complete.";

?>
