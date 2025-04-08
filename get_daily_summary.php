<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not logged in']));
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Get today's meals
$sql = "SELECT 
            COALESCE(SUM(calories), 0) as calories, 
            COALESCE(SUM(protein), 0) as protein, 
            COALESCE(SUM(carbs), 0) as carbs, 
            COALESCE(SUM(fat), 0) as fat 
        FROM meals 
        WHERE user_id = ? 
        AND DATE(created_at) = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$totals = $result->fetch_assoc();

// Get user's nutrition goals
$sql = "SELECT * FROM user_nutrition_goals WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$goals = $result->fetch_assoc();

// If no goals set, insert default goals
if (!$goals) {
    $sql = "INSERT INTO user_nutrition_goals 
            (user_id, daily_calories, daily_protein, daily_carbs, daily_fat) 
            VALUES (?, 2000, 50, 250, 70)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $goals = [
        'daily_calories' => 2000,
        'daily_protein' => 50,
        'daily_carbs' => 250,
        'daily_fat' => 70
    ];
}

// Format response with explicit type casting
$response = [
    'status' => 'success',
    'calories' => (float) $totals['calories'] ?: 0,
    'protein' => (float) $totals['protein'] ?: 0,
    'carbs' => (float) $totals['carbs'] ?: 0,
    'fat' => (float) $totals['fat'] ?: 0,
    'goals' => [
        'calories' => (float) $goals['daily_calories'],
        'protein' => (float) $goals['daily_protein'],
        'carbs' => (float) $goals['daily_carbs'],
        'fat' => (float) $goals['daily_fat']
    ]
];

header('Content-Type: application/json');
echo json_encode($response);
$conn->close();
?>
