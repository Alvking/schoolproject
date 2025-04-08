<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Not logged in']));
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Get today's water intake
$sql = "SELECT glasses FROM water_intake 
        WHERE user_id = ? AND date = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'glasses' => $row ? $row['glasses'] : 0
]);

$conn->close();
?>
