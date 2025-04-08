<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "exercisedb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check the current database name
$db_check = $conn->query("SELECT DATABASE() AS db_name");
$db_row = $db_check->fetch_assoc();
echo "Connected to database: " . $db_row['db_name'] . "<br>";

// Fetch all tables in the database
$table_check = $conn->query("SHOW TABLES");
echo "Tables in database: <br>";
while ($table_row = $table_check->fetch_array()) {
    echo $table_row[0] . "<br>";
}

// Now try to fetch exercises
$sql = "SELECT * FROM exercises ORDER BY date DESC";
$result = $conn->query($sql);

if ($result === FALSE) {
    die("Error in SQL query: " . $conn->error);
}

echo "<br>Query executed successfully.";
?>
