<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "usersdb";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Database connection error: " . mysqli_connect_error());
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Initialize progress array
$progress = [];

if ($user_id > 0) {
    $query = "SELECT date, weight, chest, waist, hips, biceps, thighs, pushups, running_time, plank_time, progress_notes, logged_at 
              FROM progress 
              WHERE user_id = ? 
              ORDER BY date DESC";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $progress = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}



// Retrieve progress photos
$photo_query = $conn->prepare("SELECT * FROM progress_photos WHERE user_id = ? ORDER BY date DESC");
$photo_query->bind_param("i", $user_id);
$photo_query->execute();
$photo_result = $photo_query->get_result();

echo "<h2>Progress Photos</h2>";
while ($photo = $photo_result->fetch_assoc()) {
    echo "<div class='photo-entry'>";
    echo "<strong>Date:</strong> " . $photo['date'] . "<br>";
    if (!empty($photo['front_photo'])) {
        echo "<img src='" . htmlspecialchars($photo['front_photo']) . "' alt='Front Photo' style='max-width:200px;'><br>";
    }
    if (!empty($photo['side_photo'])) {
        echo "<img src='" . htmlspecialchars($photo['side_photo']) . "' alt='Side Photo' style='max-width:200px;'><br>";
    }
    if (!empty($photo['back_photo'])) {
        echo "<img src='" . htmlspecialchars($photo['back_photo']) . "' alt='Back Photo' style='max-width:200px;'><br>";
    }
   
}
$photo_query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Progress</title>
    <link rel="stylesheet" href="user-progress.css">
</head>
<body>

    <h2>Progress for User ID: <?php echo htmlspecialchars($user_id); ?></h2>

    <?php if (empty($progress)): ?>
        <p>No progress records found for this user.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Date</th>
                <th>Weight (kg)</th>
                <th>Chest (cm)</th>
                <th>Waist (cm)</th>
                <th>Hips (cm)</th>
                <th>Biceps (cm)</th>
                <th>Thighs (cm)</th>
                <th>Pushups</th>
                <th>Running Time (min)</th>
                <th>Plank Time (sec)</th>
                <th>Progress Notes</th>
                <th>Logged At</th>
            </tr>
            <?php foreach ($progress as $entry): ?>
            <tr>
                <td><?php echo date('F j, Y', strtotime($entry['date'])); ?></td>
                <td><?php echo htmlspecialchars($entry['weight']); ?></td>
                <td><?php echo htmlspecialchars($entry['chest']); ?></td>
                <td><?php echo htmlspecialchars($entry['waist']); ?></td>
                <td><?php echo htmlspecialchars($entry['hips']); ?></td>
                <td><?php echo htmlspecialchars($entry['biceps']); ?></td>
                <td><?php echo htmlspecialchars($entry['thighs']); ?></td>
                <td><?php echo htmlspecialchars($entry['pushups']); ?></td>
                <td><?php echo htmlspecialchars($entry['running_time']); ?></td>
                <td><?php echo htmlspecialchars($entry['plank_time']); ?></td>
                <td><?php echo htmlspecialchars($entry['progress_notes'] ?: 'N/A'); ?></td>
                <td><?php echo date('F j, Y, g:i A', strtotime($entry['logged_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

</body>
</html>
