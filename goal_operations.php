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

// Handle goal operations
$operation = $_POST['operation'] ?? '';
$goal_id = $_POST['goal_id'] ?? 0;
$user_id = $_POST['user_id'] ?? 0;

switch ($operation) {
    case 'delete':
        if ($goal_id > 0) {
            $stmt = $conn->prepare("DELETE FROM goals WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $goal_id, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Goal deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting goal']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid goal ID']);
        }
        break;

    case 'get':
        if ($goal_id > 0) {
            $stmt = $conn->prepare("SELECT * FROM goals WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $goal_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $goal = $result->fetch_assoc();
            
            if ($goal) {
                echo json_encode(['success' => true, 'data' => $goal]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Goal not found']);
            }
            $stmt->close();
        }
        break;

    case 'update':
        if ($goal_id > 0) {
            $goal_type = $_POST['goal_type'] ?? '';
            $goal_target = $_POST['goal_target'] ?? '';
            $goal_duration = $_POST['goal_duration'] ?? '';
            $activity_level = $_POST['activity_level'] ?? '';

            $stmt = $conn->prepare("UPDATE goals SET goal_type = ?, goal_target = ?, goal_duration = ?, 
                                  activity_level = ? WHERE id = ? AND user_id = ?");
            
            $stmt->bind_param("ssssii", $goal_type, $goal_target, $goal_duration, 
                            $activity_level, $goal_id, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Goal updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating goal']);
            }
            $stmt->close();
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid operation']);
}

mysqli_close($conn);
?>
