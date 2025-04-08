<?php
require_once 'db_connection.php';

// Check if table exists
$sql = "SHOW TABLES LIKE 'user_nutrition_goals'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Create table if it doesn't exist
    $sql = "CREATE TABLE user_nutrition_goals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        daily_calories FLOAT DEFAULT 2000,
        daily_protein FLOAT DEFAULT 50,
        daily_carbs FLOAT DEFAULT 250,
        daily_fat FLOAT DEFAULT 70,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql)) {
        echo "Created user_nutrition_goals table\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
        exit;
    }
}

// Check table structure
$sql = "DESCRIBE user_nutrition_goals";
$result = $conn->query($sql);
echo "\nTable structure:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

// Check existing goals
$sql = "SELECT * FROM user_nutrition_goals";
$result = $conn->query($sql);
echo "\nExisting goals:\n";
while ($row = $result->fetch_assoc()) {
    echo "User ID: " . $row['user_id'] . "\n";
    echo "Calories: " . $row['daily_calories'] . "\n";
    echo "Protein: " . $row['daily_protein'] . "g\n";
    echo "Carbs: " . $row['daily_carbs'] . "g\n";
    echo "Fat: " . $row['daily_fat'] . "g\n\n";
}

$conn->close();
?>
