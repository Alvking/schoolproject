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

// Handle comment submission via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_comment') {
        $user_id = $_POST['user_id'];
        $nutritionist_id = $_SESSION['user_id']; // Assuming nutritionist's ID is stored in session
        $comment = $_POST['comment'];
        
        $stmt = $conn->prepare("INSERT INTO comments (user_id, nutritionist_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $nutritionist_id, $comment);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        exit;
    } elseif ($_POST['action'] === 'get_comments') {
        $user_id = $_POST['user_id'];
        
        $stmt = $conn->prepare("
            SELECT c.*, u.name as nutritionist_name 
            FROM comments c 
            JOIN users u ON c.nutritionist_id = u.id 
            WHERE c.user_id = ? 
            ORDER BY c.created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = [];
        
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        
        echo json_encode($comments);
        exit;
    }
}

// Initialize search query
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Base query
$query = "SELECT 
            u.id,
            u.name,
            u.email,
            (SELECT COUNT(*) FROM exercises WHERE user_id = u.id) AS exercises_count,
            (SELECT COUNT(*) FROM goals WHERE user_id = u.id) AS goals_count,
            (SELECT GROUP_CONCAT(goal_type SEPARATOR ', ') 
             FROM goals 
             WHERE user_id = u.id 
             ORDER BY date_created DESC LIMIT 3) AS recent_goals,
            (SELECT COUNT(DISTINCT date) FROM exercises
             WHERE user_id = u.id 
             AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)) AS active_days_week
          FROM users u";

// WHERE conditions
$where_clauses = [];
$params = [];
$param_types = '';

// Search filter
if (!empty($search)) {
    $where_clauses[] = "(u.name LIKE ? OR u.email LIKE ? OR CAST(u.id AS CHAR) LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $param_types = 'sss';
}

// Status filter
if ($status_filter !== 'all') {
    $activity_check = $status_filter === 'active' ? '> 0' : '= 0';
    $where_clauses[] = "(SELECT COUNT(DISTINCT date) FROM exercises 
                        WHERE user_id = u.id 
                        AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)) " . $activity_check;
}

// Append WHERE clause
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Execute query
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query execution failed: " . mysqli_error($conn));
}

$users = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get counts
$total_users = count($users);
$active_users = array_filter($users, fn($user) => isset($user['active_days_week']) && $user['active_days_week'] > 0);
$inactive_users = array_filter($users, fn($user) => !isset($user['active_days_week']) || $user['active_days_week'] == 0);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutritionist Dashboard</title>
    <link rel="stylesheet" href="nutritionist-dashboard.css">
    
</head>
<body>
<ul class="nav-bar">
        <li><a href="index.php">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php">Contact Us</a></li>
    </ul>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Nutritionist Dashboard</h1>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Active Users</h3>
                    <p><?php echo count($active_users); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Inactive Users</h3>
                    <p><?php echo count($inactive_users); ?></p>
                </div>
            </div>
        </header>

      
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Exercises</th>
                    <th>Goals</th>
                    <th>Recent Goals</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['exercises_count']; ?></td>
                    <td><?php echo $user['goals_count']; ?></td>
                    <td><?php echo htmlspecialchars($user['recent_goals'] ?: 'None'); ?></td>
                    <td>
                        <span class="status-badge <?php echo isset($user['active_days_week']) && $user['active_days_week'] > 0 ? 'active' : 'inactive'; ?>">
                            <?php echo isset($user['active_days_week']) && $user['active_days_week'] > 0 ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td class="dropdown">
                        <button class="dropbtn" onclick="toggleDropdown(this)">Actions</button>
                        <div class="dropdown-content">
                            <a href="user-goals.php?id=<?php echo $user['id']; ?>">Goals</a>
                            <a href="user-exercises.php?id=<?php echo $user['id']; ?>">Exercises</a>
                            <a href="user-diet.php?id=<?php echo $user['id']; ?>">Diet</a>
                            <a href="user-progress.php?id=<?php echo $user['id']; ?>">Progress</a>
                            <a href="#" class="comment-btn" onclick="showComments(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">Comment</a>
                        </div>
                      
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Comments Modal -->
    <div id="commentsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Comments for <span id="userName"></span></h2>
            <div class="comments-container" id="commentsContainer">
                <!-- Comments will be dynamically inserted here -->
            </div>
            <form id="commentForm" class="comment-form">
                <input type="hidden" id="currentUserId" name="user_id">
                <textarea name="comment" placeholder="Write your comment..." required></textarea>
                <button type="submit">Send Message</button>
            </form>
        </div>
    </div>

    <script>
    function toggleDropdown(button) {
        let dropdown = button.nextElementSibling;
        let allDropdowns = document.querySelectorAll('.dropdown-content');

        allDropdowns.forEach(menu => {
            if (menu !== dropdown) {
                menu.style.display = 'none';
            }
        });

        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('click', function(event) {
        let isClickInside = event.target.matches('.dropbtn') || event.target.closest('.dropdown-content');

        if (!isClickInside) {
            document.querySelectorAll('.dropdown-content').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });

    // Comments functionality
    const modal = document.getElementById('commentsModal');
    const closeBtn = document.querySelector('.close');
    const commentsContainer = document.getElementById('commentsContainer');
    const commentForm = document.getElementById('commentForm');
    const userNameSpan = document.getElementById('userName');
    let currentUserId = null;

    function showComments(userId, userName) {
        currentUserId = userId;
        userNameSpan.textContent = userName;
        document.getElementById('currentUserId').value = userId;
        modal.style.display = 'block';
        loadComments(userId);
    }

    function loadComments(userId) {
        fetch('nutritionist-dashboard.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=get_comments&user_id=${userId}`
        })
        .then(response => response.json())
        .then(comments => {
            commentsContainer.innerHTML = comments.map(comment => {
                const isNutritionist = comment.nutritionist_id === '<?php echo $_SESSION['user_id']; ?>';
                return `
                    <div class="comment">
                        <div class="message-${isNutritionist ? 'sent' : 'received'}">
                            <div class="message-header">
                                <strong>${isNutritionist ? 'You' : comment.nutritionist_name}</strong>
                                <small>${new Date(comment.created_at).toLocaleString()}</small>
                            </div>
                            <div class="message-content">
                                <p>${comment.comment}</p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            commentsContainer.scrollTop = commentsContainer.scrollHeight;
        })
        .catch(error => console.error('Error:', error));
    }

    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'add_comment');

        fetch('nutritionist-dashboard.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.reset();
                loadComments(currentUserId);
            } else {
                alert('Error adding comment: ' + data.error);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>
</body>
</html>
