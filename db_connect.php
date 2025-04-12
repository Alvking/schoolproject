<?php
error_reporting(0); // Disable error reporting
ini_set('display_errors', 0); // Don't display errors

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'usersdb';  // Changed to match the correct database name

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        header('Content-Type: application/json');
        die(json_encode(['status' => 'error', 'message' => "Connection failed: " . $conn->connect_error]));
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => "Database connection error: " . $e->getMessage()]));
}
?>
