<?php
// ============================================================
//  remove_plant.php — AJAX endpoint used by homepage.php to
//  delete one of the logged-in user's plants. Returns JSON.
// ============================================================

require_once "config.php";
session_start();

header('Content-Type: application/json');

// Must be logged in.
if (!isset($_SESSION['userid'])) {
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit();
}

// Must be a POST with a plant id.
$plantId = $_POST['plant_id'] ?? null;
if (!$plantId || !ctype_digit((string)$plantId)) {
    echo json_encode(["success" => false, "message" => "Invalid plant id."]);
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit();
}

$userId = $_SESSION['userid'];

// Delete only if the plant belongs to this user (ownership check).
$stmt = $conn->prepare("DELETE FROM userplants WHERE user_plant_id = ? AND user_id = ?");
$stmt->bind_param("ii", $plantId, $userId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Plant removed."]);
    } else {
        echo json_encode(["success" => false, "message" => "Plant not found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Could not delete plant."]);
}
?>
