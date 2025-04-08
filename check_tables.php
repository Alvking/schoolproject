<?php
require_once 'db_connection.php';

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to get table structure
function getTableStructure($conn, $tableName) {
    $result = $conn->query("DESCRIBE $tableName");
    $structure = [];
    while ($row = $result->fetch_assoc()) {
        $structure[] = $row;
    }
    return $structure;
}

// Check meals table
if (!tableExists($conn, 'meals')) {
    $sql = "CREATE TABLE meals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        meal_type VARCHAR(50) NOT NULL,
        food_item VARCHAR(100) NOT NULL,
        serving_size FLOAT NOT NULL,
        calories FLOAT NOT NULL,
        protein FLOAT NOT NULL,
        carbs FLOAT NOT NULL,
        fat FLOAT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if ($conn->query($sql)) {
        echo "meals table created successfully\n";
    } else {
        echo "Error creating meals table: " . $conn->error . "\n";
    }
} else {
    echo "meals table exists\n";
    print_r(getTableStructure($conn, 'meals'));
}

// Check user_nutrition_goals table
if (!tableExists($conn, 'user_nutrition_goals')) {
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
        echo "user_nutrition_goals table created successfully\n";
    } else {
        echo "Error creating user_nutrition_goals table: " . $conn->error . "\n";
    }
} else {
    echo "user_nutrition_goals table exists\n";
    print_r(getTableStructure($conn, 'user_nutrition_goals'));
}

// Insert default nutrition goals for current user if not exists
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM user_nutrition_goals WHERE user_id = $user_id");
    
    if ($result->num_rows === 0) {
        $sql = "INSERT INTO user_nutrition_goals 
                (user_id, daily_calories, daily_protein, daily_carbs, daily_fat) 
                VALUES ($user_id, 2000, 50, 250, 70)";
        
        if ($conn->query($sql)) {
            echo "Default nutrition goals added for user $user_id\n";
        } else {
            echo "Error adding default nutrition goals: " . $conn->error . "\n";
        }
    }
}

$conn->close();
?>
