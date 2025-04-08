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

// Initialize goals array
$goals = [];

if ($user_id > 0) {
    $query = "SELECT id, goal_type, goal_target, goal_duration, activity_level, date_created 
              FROM goals 
              WHERE user_id = ? 
              ORDER BY date_created DESC";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $goals = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Goals</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="user-goals.css">
</head>
<body>
    <!-- Edit Goal Modal -->
    <div id="editGoalModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Goal</h2>
            <form id="editGoalForm">
                <input type="hidden" id="goal_id" name="goal_id">
                <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">
                
                <div class="form-group">
                    <label for="goal_type">Goal Type</label>
                    <select id="goal_type" name="goal_type" required>
                        <option value="Weight Loss">Weight Loss</option>
                        <option value="Muscle Gain">Muscle Gain</option>
                        <option value="Endurance">Endurance</option>
                        <option value="Flexibility">Flexibility</option>
                        <option value="General Fitness">General Fitness</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="goal_target">Target</label>
                    <input type="text" id="goal_target" name="goal_target" required>
                </div>
                
                <div class="form-group">
                    <label for="goal_duration">Duration</label>
                    <input type="text" id="goal_duration" name="goal_duration" required>
                </div>
                
                <div class="form-group">
                    <label for="activity_level">Activity Level</label>
                    <select id="activity_level" name="activity_level" required>
                        <option value="Low">Low</option>
                        <option value="Moderate">Moderate</option>
                        <option value="High">High</option>
                        <option value="Very High">Very High</option>
                    </select>
                </div>
                
                <button type="submit" class="save-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="dashboard">
        <nav class="nav-bar">
            <a href="dashboard.php" class="nav-link">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <path fill="currentColor" d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z"/>
                </svg>
                Back to Dashboard
            </a>
        </nav>

        <div class="goals-container">
            <div class="header">
                <h1>Fitness Goals</h1>
                <a href="goals.php" class="add-goal-btn">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                    </svg>
                    Add New Goal
                </a>
            </div>

            <?php if (empty($goals)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" width="48" height="48">
                        <path fill="currentColor" d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19M17,17H7V7H17V17Z"/>
                    </svg>
                </div>
                <h2>No Goals Set Yet</h2>
                <p>Start by adding your first fitness goal!</p>
            </div>
            <?php else: ?>
            <div class="goals-grid">
                <?php foreach ($goals as $goal): ?>
                <div class="goal-card">
                    <div class="goal-type <?= strtolower($goal['goal_type']) ?>">
                        <svg viewBox="0 0 24 24" width="24" height="24">
                            <?php if ($goal['goal_type'] === 'Weight Loss'): ?>
                            <path fill="currentColor" d="M12,3A4,4 0 0,1 16,7C16,7.73 15.81,8.41 15.46,9H18C18.95,9 19.75,9.67 19.95,10.56C21.96,18.57 22,18.78 22,19A2,2 0 0,1 20,21H4A2,2 0 0,1 2,19C2,18.78 2.04,18.57 4.05,10.56C4.25,9.67 5.05,9 6,9H8.54C8.19,8.41 8,7.73 8,7A4,4 0 0,1 12,3M12,5A2,2 0 0,0 10,7A2,2 0 0,0 12,9A2,2 0 0,0 14,7A2,2 0 0,0 12,5Z"/>
                            <?php elseif ($goal['goal_type'] === 'Muscle Gain'): ?>
                            <path fill="currentColor" d="M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,1A7,7 0 0,0 5,8A7,7 0 0,0 12,15A7,7 0 0,0 19,8A7,7 0 0,0 12,1Z"/>
                            <?php else: ?>
                            <path fill="currentColor" d="M7,2V13H10V22L17,10H13L17,2H7Z"/>
                            <?php endif; ?>
                        </svg>
                        <span><?= htmlspecialchars($goal['goal_type']) ?></span>
                    </div>
                    <div class="goal-details">
                        <div class="goal-target">
                            <strong>Target:</strong> <?= htmlspecialchars($goal['goal_target']) ?>
                        </div>
                        <div class="goal-duration">
                            <strong>Duration:</strong> <?= htmlspecialchars($goal['goal_duration']) ?> weeks
                        </div>
                        <div class="goal-activity">
                            <strong>Activity Level:</strong> <?= htmlspecialchars($goal['activity_level']) ?>
                        </div>
                        <div class="goal-date">
                            <strong>Started:</strong> <?= date('M d, Y', strtotime($goal['date_created'])) ?>
                        </div>
                    </div>
                    <div class="goal-actions">
                        <button class="edit-btn" onclick="editGoal(<?php echo $goal['id']; ?>)">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                            </svg>
                        </button>
                        <button class="delete-btn" onclick="deleteGoal(<?php echo $goal['id']; ?>)">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const modal = document.getElementById('editGoalModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        const editForm = document.getElementById('editGoalForm');
        
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        
        function editGoal(goalId) {
            fetch('goal_operations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `operation=get&goal_id=${goalId}&user_id=${document.getElementById('user_id').value}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const goal = data.data;
                    document.getElementById('goal_id').value = goal.id;
                    document.getElementById('goal_type').value = goal.goal_type;
                    document.getElementById('goal_target').value = goal.goal_target;
                    document.getElementById('goal_duration').value = goal.goal_duration;
                    document.getElementById('activity_level').value = goal.activity_level;
                    modal.style.display = "block";
                } else {
                    alert('Error loading goal data');
                }
            });
        }
        
        function deleteGoal(goalId) {
            if (confirm('Are you sure you want to delete this goal?')) {
                fetch('goal_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `operation=delete&goal_id=${goalId}&user_id=${document.getElementById('user_id').value}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting goal');
                    }
                });
            }
        }
        
        editForm.onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(editForm);
            formData.append('operation', 'update');
            
            fetch('goal_operations.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.style.display = "none";
                    location.reload();
                } else {
                    alert('Error updating goal');
                }
            });
        }
    </script>
</body>
</html>
