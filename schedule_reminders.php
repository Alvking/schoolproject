<?php
include 'db_connect.php'; // Connect to database

// Get current time
$current_time = date("H:i:s");

// Fetch pending reminders for this time
$sql = "SELECT * FROM reminders WHERE reminder_time = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_time);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $message = $row['message'];

    // Call function to send notification (Email, SMS, Web Notification)
    sendNotification($user_id, $message);

    // Update reminder status to 'sent'
    $update_sql = "UPDATE reminders SET status = 'sent' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $row['id']);
    $update_stmt->execute();
}

// Function to send notifications
function sendNotification($user_id, $message) {
    // Fetch user email
    global $conn;
    $user_query = "SELECT email FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();

    // Send email
    if ($user) {
        $to = $user['email'];
        $subject = "Fitness Reminder!";
        $headers = "From: no-reply@fitnessapp.com";
        mail($to, $subject, $message, $headers);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <script>// Ask for Notification Permission
if (Notification.permission !== "granted") {
    Notification.requestPermission();
}

// Function to Show Notifications
function showNotification(title, message) {
    if (Notification.permission === "granted") {
        new Notification(title, {
            body: message,
            icon: "icon.png"
        });
    }
}

// Example: Show a notification
showNotification("Hydration Reminder", "Drink a glass of water now!");
</script>
</body>
</html>