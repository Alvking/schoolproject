<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();


if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in. <a href='login.php'>Login</a>");
}


$user_id = $_SESSION['user_id'];


$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "usersdb"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $goalType = isset($_POST['goalType']) ? trim($_POST['goalType']) : '';
    $goalTarget = isset($_POST['goalTarget']) ? floatval($_POST['goalTarget']) : 0;
    $goalDuration = isset($_POST['goalDuration']) ? intval($_POST['goalDuration']) : 0;
    $activityLevel = isset($_POST['activityLevel']) ? trim($_POST['activityLevel']) : '';

    
    if (empty($goalType) || empty($goalTarget) || empty($goalDuration) || empty($activityLevel)) {
        echo "<script>alert('Error: All fields are required!');</script>";
    } else {
       
        $stmt = $conn->prepare("INSERT INTO goals (user_id, goal_type, goal_target, goal_duration, activity_level) 
                                VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }
        
        $stmt->bind_param("isdis", $user_id, $goalType, $goalTarget, $goalDuration, $activityLevel);

        if ($stmt->execute()) {
            echo "<script>alert('Goal saved successfully!'); window.location.href='goal.php';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Goals</title>
    <link rel="stylesheet" href="goal.css">
</head>
<body>

    <ul class="nav-bar">
        <li><a href="index.php">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php">Contact Us</a></li>
    </ul>

    <div class="goal-container">
        <h2>Set Your Fitness Goal</h2>
        <form id="goalForm" action="goal.php" method="POST">
            <label for="goalType">Select Goal Type:</label>
            <select id="goalType" name="goalType" required>
                <option value="weightLoss">Weight Loss</option>
                <option value="muscleGain">Muscle Gain</option>
                <option value="endurance">Endurance</option>
            </select>

            <label for="goalTarget">Target (Weight in kg or other metric):</label>
            <input type="number" id="goalTarget" name="goalTarget" step="0.1" placeholder="Enter your target weight" required>

            <label for="goalDuration">Timeframe (weeks):</label>
            <input type="number" id="goalDuration" name="goalDuration" placeholder="Enter number of weeks" required>

            <label for="activityLevel">Select Activity Level:</label>
            <select id="activityLevel" name="activityLevel" required>
                <option value="low">Low</option>
                <option value="moderate">Moderate</option>
                <option value="high">High</option>
            </select>

            <button type="submit">Save Goal</button>
        </form>
    </div>

</body>
</html>
