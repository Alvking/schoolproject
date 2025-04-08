<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not logged in']));
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$glasses = $data['glasses'];
$today = date('Y-m-d');

// Update or insert water intake for today
$sql = "INSERT INTO water_intake (user_id, glasses, date) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE glasses = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisi", $user_id, $glasses, $today, $glasses);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}

$conn->close();
?>
