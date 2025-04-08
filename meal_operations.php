<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "usersdb";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection error']));
}

// Handle meal operations
$operation = $_POST['operation'] ?? '';
$meal_id = $_POST['meal_id'] ?? 0;
$user_id = $_POST['user_id'] ?? 0;

switch ($operation) {
    case 'delete':
        if ($meal_id > 0) {
            $stmt = $conn->prepare("DELETE FROM meals WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $meal_id, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Meal deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting meal']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid meal ID']);
        }
        break;

    case 'get':
        if ($meal_id > 0) {
            $stmt = $conn->prepare("SELECT * FROM meals WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $meal_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $meal = $result->fetch_assoc();
            
            if ($meal) {
                echo json_encode(['success' => true, 'data' => $meal]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Meal not found']);
            }
            $stmt->close();
        }
        break;

    case 'update':
        if ($meal_id > 0) {
            $food_item = $_POST['food_item'] ?? '';
            $meal_type = $_POST['meal_type'] ?? '';
            $serving_size = $_POST['serving_size'] ?? '';
            $calories = $_POST['calories'] ?? 0;
            $protein = $_POST['protein'] ?? 0;
            $carbs = $_POST['carbs'] ?? 0;
            $fat = $_POST['fat'] ?? 0;

            $stmt = $conn->prepare("UPDATE meals SET food_item = ?, meal_type = ?, serving_size = ?, 
                                  calories = ?, protein = ?, carbs = ?, fat = ? 
                                  WHERE id = ? AND user_id = ?");
            
            $stmt->bind_param("sssddddii", $food_item, $meal_type, $serving_size, 
                            $calories, $protein, $carbs, $fat, $meal_id, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Meal updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating meal']);
            }
            $stmt->close();
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid operation']);
}

mysqli_close($conn);
?>
