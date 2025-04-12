<?php
error_reporting(0); // Disable error reporting
ini_set('display_errors', 0); // Don't display errors
header('Content-Type: application/json');
ob_clean(); // Clear any previous output

session_start();
require_once 'db_connect.php';  

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
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
    if (!$stmt) {
        throw new Exception("Failed to prepare meals statement: " . $conn->error);
    }

    $stmt->bind_param("is", $user_id, $today);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute meals statement: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $totals = $result->fetch_assoc();
    $stmt->close();

    // Get user's nutrition goals
    $sql = "SELECT * FROM user_nutrition_goals WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare goals statement: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute goals statement: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $goals = $result->fetch_assoc();
    $stmt->close();

    // If no goals set, insert default goals
    if (!$goals) {
        $sql = "INSERT INTO user_nutrition_goals 
                (user_id, daily_calories, daily_protein, daily_carbs, daily_fat) 
                VALUES (?, 2000, 50, 250, 70)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare default goals statement: " . $conn->error);
        }

        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute default goals statement: " . $stmt->error);
        }
        $stmt->close();
        
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

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
