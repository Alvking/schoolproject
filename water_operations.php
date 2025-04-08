<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation = $_POST['operation'] ?? '';
    $user_id = $_POST['user_id'] ?? 0;
    $response = ['success' => false, 'message' => ''];

    switch ($operation) {
        case 'add':
            $glasses = $_POST['glasses'] ?? 0;
            $date = $_POST['date'] ?? date('Y-m-d');

            // Check if entry exists for today
            $check_query = "SELECT id, glasses FROM water_intake WHERE user_id = ? AND DATE(date) = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("is", $user_id, $date);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update existing entry
                $row = $result->fetch_assoc();
                $new_glasses = $row['glasses'] + $glasses;
                $update_query = "UPDATE water_intake SET glasses = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ii", $new_glasses, $row['id']);
                
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Water intake updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Error updating water intake'];
                }
            } else {
                // Insert new entry
                $insert_query = "INSERT INTO water_intake (user_id, date, glasses) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("isi", $user_id, $date, $glasses);
                
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Water intake added successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Error adding water intake'];
                }
            }
            break;

        case 'get':
            $date = $_POST['date'] ?? date('Y-m-d');
            $query = "SELECT glasses FROM water_intake WHERE user_id = ? AND DATE(date) = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $user_id, $date);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $response = ['success' => true, 'data' => $row];
            } else {
                $response = ['success' => true, 'data' => ['glasses' => 0]];
            }
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
