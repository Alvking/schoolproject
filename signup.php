<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to the correct database
$conn = new mysqli("localhost", "root", "", "usersdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user'; // Default role

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$check_stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<script>alert('Error: Email already registered! Please login.'); window.location.href='login.php';</script>";
        exit();
    }
    $check_stmt->close();

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssss", $name, $email, $password, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! You can now login.'); window.location.href='login.php';</script>";
    } else {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <div class="container-signup">
        <h2>Create an Account</h2>
        <img src="weblogo.png" alt="web-logo" width="70%" height="35%">
        <form action="signup.php" method="POST">
            <label for="name">Name</label>
            <input type="text" name="name" placeholder="Enter your name" required>

            <label for="email">Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html>
