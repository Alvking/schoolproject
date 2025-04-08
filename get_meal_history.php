<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not logged in']));
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Get today's meals
$sql = "SELECT * FROM meals 
        WHERE user_id = ? 
        AND DATE(created_at) = ? 
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$result = $stmt->get_result();

$meals = [];
while ($row = $result->fetch_assoc()) {
    $meals[] = [
        'meal_type' => ucfirst($row['meal_type']),
        'food_item' => $row['food_item'],
        'serving_size' => $row['serving_size'],
        'calories' => $row['calories'],
        'protein' => $row['protein'],
        'carbs' => $row['carbs'],
        'fat' => $row['fat'],
        'time' => date('h:i A', strtotime($row['created_at']))
    ];
}

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'meals' => $meals]);
$conn->close();
?>
