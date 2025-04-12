<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "usersdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$userData = array();

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_POST['nutritionist_id'])) {
    $comment = $_POST['comment'];
    $nutritionist_id = $_POST['nutritionist_id'];
    
    $stmt = $conn->prepare("INSERT INTO comments (user_id, nutritionist_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $nutritionist_id, $comment);
    $stmt->execute();
    $stmt->close();
}

// Fetch user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $userData['user'] = $result->fetch_assoc();
} else {
    echo "<!-- User not found in the database -->";
}
$stmt->close();

// Fetch user's nutritionist
$sql = "SELECT id, name FROM users WHERE role = 'nutritionist' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$nutritionist = $result->fetch_assoc();
$stmt->close();

// Fetch comments
$sql = "
    SELECT c.*, u.name as author, u.role as author_role 
    FROM comments c 
    JOIN users u ON (u.id = c.nutritionist_id OR u.id = c.user_id)
    WHERE c.user_id = ? OR c.nutritionist_id = ?
    ORDER BY c.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
}
$stmt->close();

// Fetch user's exercise logs
$sql = "SELECT id, exercise_type, duration, intensity, sets, reps, weight, date FROM exercises WHERE user_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userData['exercises'] = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userData['exercises'][] = $row;
    }
}
$stmt->close();

// Fetch user's goals
$sql = "SELECT * FROM goals WHERE user_id = ? ORDER BY date_created DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userData['goals'] = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userData['goals'][] = $row;
    }
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All info</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>

<ul class="nav-bar">
        <li><a href="index.php">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php">Contact Us</a></li>
    </ul>


    <div class="profile-container">
        <h2>Welcome, <?php echo htmlspecialchars($userData['user']['name'] ?? 'User'); ?></h2>
        
        <div class="exercises-section">
            <h3>Your Exercise Progress</h3>
            <?php if (!empty($userData['exercises'])): ?>
                <div class="exercise-list">
                    <?php foreach ($userData['exercises'] as $exercise): ?>
                        <div class="exercise-item">
                            <div class="exercise-type"><?php echo htmlspecialchars($exercise['exercise_type'] ?? ''); ?></div>
                            <div class="exercise-stats">
                                <?php if (isset($exercise['duration'])): ?>
                                    <div class="stat-item">
                                        <strong>Duration:</strong> <?php echo htmlspecialchars($exercise['duration']); ?> min
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($exercise['intensity'])): ?>
                                    <div class="stat-item">
                                        <strong>Intensity:</strong> <?php echo htmlspecialchars($exercise['intensity']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($exercise['sets']) && isset($exercise['reps'])): ?>
                                    <div class="stat-item">
                                        <strong>Sets × Reps:</strong> <?php echo htmlspecialchars($exercise['sets']); ?> × <?php echo htmlspecialchars($exercise['reps']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($exercise['weight']) && $exercise['weight'] > 0): ?>
                                    <div class="stat-item">
                                        <strong>Weight:</strong> <?php echo htmlspecialchars($exercise['weight']); ?> kg
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($exercise['date'])): ?>
                                <div class="exercise-date"><?php echo date('F j, Y', strtotime($exercise['date'])); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No exercise logs yet.</p>
            <?php endif; ?>
        </div>

        <div class="goals-section">
            <h3>Your Goals</h3>
            <?php if (!empty($userData['goals'])): ?>
                <?php foreach ($userData['goals'] as $goal): ?>
                    <div class="goal-item">
                        <h4><?php echo htmlspecialchars($goal['goal_type'] ?? ''); ?></h4>
                        <div class="goal-details">
                            <p><strong>Target:</strong> <?php echo htmlspecialchars($goal['goal_target'] ?? ''); ?></p>
                            <p><strong>Duration:</strong> <?php echo htmlspecialchars($goal['goal_duration'] ?? ''); ?></p>
                            <p><strong>Activity Level:</strong> <?php echo htmlspecialchars($goal['activity_level'] ?? ''); ?></p>
                            <?php if (isset($goal['date_created'])): ?>
                                <span class="goal-date">Created: <?php echo date('M d, Y', strtotime($goal['date_created'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No goals set yet.</p>
            <?php endif; ?>
            <a href="goal.php" class="btn">Set New Goal</a>
        </div>

        <div class="communication-section">
            <h3>Communication with Nutritionist</h3>
            <?php if ($nutritionist): ?>
                <div class="nutritionist-info">
                    <h4>Your Nutritionist: <?php echo htmlspecialchars($nutritionist['name']); ?></h4>
                </div>
                
                <div class="comments-section">
                    <div class="comments-list">
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <?php 
                                    $isUserMessage = $comment['user_id'] === $_SESSION['user_id'];
                                    $messageClass = $isUserMessage ? 'message-sent' : 'message-received';
                                    $senderName = $isUserMessage ? 'You' : htmlspecialchars($nutritionist['name']);
                                ?>
                                <div class="comment-bubble <?php echo $messageClass; ?>">
                                    <div class="message-content">
                                        <div class="message-text"><?php echo htmlspecialchars($comment['comment']); ?></div>
                                        <div class="message-time">
                                            <span class="sender"><?php echo $senderName; ?></span> • 
                                            <span class="time"><?php echo date('M d, g:i A', strtotime($comment['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-comments">No messages yet. Start a conversation with your nutritionist!</p>
                        <?php endif; ?>
                    </div>
                    
                    <form class="comment-form" method="POST">
                        <input type="hidden" name="nutritionist_id" value="<?php echo $nutritionist['id']; ?>">
                        <textarea name="comment" placeholder="Type your message here..." required></textarea>
                        <button type="submit">
                            Send
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <p class="no-nutritionist">No nutritionist assigned yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Profile page loaded.");
        });
    </script>

</body>
</html>
