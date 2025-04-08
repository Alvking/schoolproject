<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "usersdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection error: " . $conn->connect_error);
}

// Ensure the nutritionist ID is set (avoiding undefined index error)
$nutritionist_id = $_SESSION['nutritionist_id'] ?? 0;

// Get user ID from URL
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Validate that the user exists (Checking in `goals` or another relevant table)
$user_check = $conn->prepare("SELECT DISTINCT user_id FROM goals WHERE user_id = ? LIMIT 1");
$user_check->bind_param("i", $user_id);
$user_check->execute();
$user_check->store_result();

if ($user_check->num_rows === 0) {
    echo "<script>alert('Error: The specified user does not exist.'); window.location.href='dashboard.php';</script>";
    exit();
}
$user_check->close();

// Fetch comments from database
$comments = [];
$stmt = $conn->prepare("SELECT id, user_id, nutritionist_id, comment, created_at FROM comments WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle adding a new comment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (user_id, nutritionist_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $nutritionist_id, $comment);
        if ($stmt->execute()) {
            header("Location: comments.php?user_id=$user_id"); // Refresh page
            exit();
        }
        $stmt->close();
    }
}

// Handle editing a comment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_comment'])) {
    $comment_id = intval($_POST['comment_id']);
    $new_comment = trim($_POST['new_comment']);
    if (!empty($new_comment)) {
        $stmt = $conn->prepare("UPDATE comments SET comment = ? WHERE id = ? AND nutritionist_id = ?");
        $stmt->bind_param("sii", $new_comment, $comment_id, $nutritionist_id);
        if ($stmt->execute()) {
            header("Location: comments.php?user_id=$user_id"); // Refresh page
            exit();
        }
        $stmt->close();
    }
}

// Handle deleting a comment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_comment'])) {
    $comment_id = intval($_POST['comment_id']);
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND nutritionist_id = ?");
    $stmt->bind_param("ii", $comment_id, $nutritionist_id);
    if ($stmt->execute()) {
        header("Location: comments.php?user_id=$user_id"); // Refresh page
        exit();
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
    <title>Comments</title>
    <link rel="stylesheet" href="contact.css">
</head>
<body>

    <h2>Comments for User ID: <?php echo htmlspecialchars($user_id); ?></h2>

    <!-- Add New Comment -->
    <form method="POST">
        <textarea name="comment" placeholder="Write a comment..." required></textarea>
        <button type="submit" name="add_comment">Add Comment</button>
    </form>

    <!-- Display Comments -->
    <?php if (empty($comments)): ?>
        <p>No comments yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                    <strong><?php echo ($comment['nutritionist_id'] == $nutritionist_id) ? 'You (Nutritionist)' : 'User'; ?>:</strong>
                    <?php echo htmlspecialchars($comment['comment']); ?>
                    <br>
                    <small><?php echo date('F j, Y H:i', strtotime($comment['created_at'])); ?></small>

                    <?php if ($comment['nutritionist_id'] == $nutritionist_id): ?>
                        <!-- Edit Comment -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <input type="text" name="new_comment" value="<?php echo htmlspecialchars($comment['comment']); ?>" required>
                            <button type="submit" name="edit_comment">Edit</button>
                        </form>

                        <!-- Delete Comment -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <button type="submit" name="delete_comment">Delete</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</body>
</html>
