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

// Handle exercise operations
$operation = $_POST['operation'] ?? '';
$exercise_id = $_POST['exercise_id'] ?? 0;
$user_id = $_POST['user_id'] ?? 0;

switch ($operation) {
    case 'delete':
        if ($exercise_id > 0) {
            $stmt = $conn->prepare("DELETE FROM exercises WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $exercise_id, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Exercise deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting exercise']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid exercise ID']);
        }
        break;

    case 'get':
        if ($exercise_id > 0) {
            $stmt = $conn->prepare("SELECT * FROM exercises WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $exercise_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $exercise = $result->fetch_assoc();
            
            if ($exercise) {
                echo json_encode(['success' => true, 'data' => $exercise]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Exercise not found']);
            }
            $stmt->close();
        }
        break;

    case 'update':
        if ($exercise_id > 0) {
            $exercise_type = $_POST['exercise_type'] ?? '';
            $duration = $_POST['duration'] ?? '';
            $intensity = $_POST['intensity'] ?? '';
            $sets = $_POST['sets'] ?? 0;
            $reps = $_POST['reps'] ?? 0;
            $weight = $_POST['weight'] ?? 0;

            $stmt = $conn->prepare("UPDATE exercises SET exercise_type = ?, duration = ?, intensity = ?, 
                                  sets = ?, reps = ?, weight = ? WHERE id = ? AND user_id = ?");
            
            $stmt->bind_param("sssiidii", $exercise_type, $duration, $intensity, 
                            $sets, $reps, $weight, $exercise_id, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Exercise updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating exercise']);
            }
            $stmt->close();
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid operation']);
}

mysqli_close($conn);
?>
