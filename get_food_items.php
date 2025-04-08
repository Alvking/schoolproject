<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not logged in']));
}

$sql = "SELECT * FROM food_items";
$result = $conn->query($sql);

$foods = [];
while ($row = $result->fetch_assoc()) {
    $foods[] = $row;
}

echo json_encode($foods);
$conn->close();
?>
