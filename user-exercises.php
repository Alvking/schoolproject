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

// Initialize exercises array
$exercises = [];

if ($user_id > 0) {
    $query = "SELECT id, date, exercise_type, duration, intensity, sets, reps, weight 
              FROM exercises
              WHERE user_id = ? 
              ORDER BY date DESC";
    
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exercises = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        die("Error preparing SQL statement: " . $conn->error);
    }
}

mysqli_close($conn);

// Group exercises by date
$exercisesByDate = [];
foreach ($exercises as $exercise) {
    $date = $exercise['date'];
    if (!isset($exercisesByDate[$date])) {
        $exercisesByDate[$date] = [];
    }
    $exercisesByDate[$date][] = $exercise;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Exercise Tracker</title>
    <link rel="stylesheet" href="user-exercise.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Edit Exercise Modal -->
    <div id="editExerciseModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Exercise</h2>
            <form id="editExerciseForm">
                <input type="hidden" id="exercise_id" name="exercise_id">
                <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">
                
                <div class="form-group">
                    <label for="exercise_type">Exercise Type</label>
                    <input type="text" id="exercise_type" name="exercise_type" required>
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration (minutes)</label>
                    <input type="number" id="duration" name="duration" required>
                </div>
                
                <div class="form-group">
                    <label for="intensity">Intensity</label>
                    <select id="intensity" name="intensity" required>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sets">Sets</label>
                        <input type="number" id="sets" name="sets" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reps">Reps</label>
                        <input type="number" id="reps" name="reps" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" step="0.1" required>
                    </div>
                </div>
                
                <button type="submit" class="save-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="dashboard">
        <nav class="nav-bar">
            <a href="nutritionist-dashboard.php" class="nav-link">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <path fill="currentColor" d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z"/>
                </svg>
                Back to Dashboard
            </a>
        </nav>

        <div class="exercise-container">
            <div class="header">
                <h1>Exercise Tracker</h1>
                <a href="exercise.php" class="add-exercise-btn">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                    </svg>
                    Log Exercise
                </a>
            </div>

            <?php if (empty($exercises)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" width="48" height="48">
                        <path fill="currentColor" d="M20.57,14.86L22,13.43L20.57,12L17,15.57L8.43,7L12,3.43L10.57,2L9.14,3.43L7.71,2L5.57,4.14L4.14,2.71L2.71,4.14L4.14,5.57L2,7.71L3.43,9.14L2,10.57L3.43,12L7,8.43L15.57,17L12,20.57L13.43,22L14.86,20.57L16.29,22L18.43,19.86L19.86,21.29L21.29,19.86L19.86,18.43L22,16.29L20.57,14.86Z"/>
                    </svg>
                </div>
                <h2>No Exercises Logged Yet</h2>
                <p>Start tracking your fitness journey by logging your first exercise!</p>
            </div>
            <?php else: ?>
            <div class="exercise-timeline">
                <?php foreach ($exercisesByDate as $date => $dayExercises): ?>
                <div class="timeline-day">
                    <div class="date-header">
                        <span class="date"><?= date('F j, Y', strtotime($date)) ?></span>
                        <span class="exercise-count"><?= count($dayExercises) ?> exercises</span>
                    </div>
                    <div class="exercise-cards">
                        <?php foreach ($dayExercises as $exercise): ?>
                        <div class="exercise-card">
                            <div class="exercise-type <?= strtolower(str_replace(' ', '-', $exercise['exercise_type'])) ?>">
                                <svg viewBox="0 0 24 24" width="24" height="24">
                                    <?php if (stripos($exercise['exercise_type'], 'cardio') !== false): ?>
                                    <path fill="currentColor" d="M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/>
                                    <?php elseif (stripos($exercise['exercise_type'], 'strength') !== false): ?>
                                    <path fill="currentColor" d="M12,5A2,2 0 0,1 14,7C14,7.24 13.96,7.47 13.88,7.69C17.95,8.5 21,11.91 21,16H3C3,11.91 6.05,8.5 10.12,7.69C10.04,7.47 10,7.24 10,7A2,2 0 0,1 12,5M22,19H2V17H22V19Z"/>
                                    <?php else: ?>
                                    <path fill="currentColor" d="M7,7H5A2,2 0 0,0 3,9V17H5V13L9,17H11L6,12L11,7H9L5,11V9A2,2 0 0,1 7,7M13,7H15A2,2 0 0,1 17,9V11L19,7H21L16,12L21,17H19L15,13V17H13V7Z"/>
                                    <?php endif; ?>
                                </svg>
                                <span><?= htmlspecialchars($exercise['exercise_type']) ?></span>
                            </div>
                            <div class="exercise-details">
                                <?php if ($exercise['duration']): ?>
                                <div class="detail">
                                    <strong>Duration:</strong>
                                    <span><?= htmlspecialchars($exercise['duration']) ?> min</span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($exercise['intensity']): ?>
                                <div class="detail">
                                    <strong>Intensity:</strong>
                                    <span><?= htmlspecialchars($exercise['intensity']) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($exercise['sets'] && $exercise['reps']): ?>
                                <div class="detail">
                                    <strong>Sets × Reps:</strong>
                                    <span><?= htmlspecialchars($exercise['sets']) ?> × <?= htmlspecialchars($exercise['reps']) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($exercise['weight']): ?>
                                <div class="detail">
                                    <strong>Weight:</strong>
                                    <span><?= htmlspecialchars($exercise['weight']) ?> kg</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="exercise-actions">
                                <button class="edit-btn" onclick="editExercise(<?php echo $exercise['id']; ?>)">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                                    </svg>
                                </button>
                                <button class="delete-btn" onclick="deleteExercise(<?php echo $exercise['id']; ?>)">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const modal = document.getElementById('editExerciseModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        const editForm = document.getElementById('editExerciseForm');
        
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        
        function editExercise(exerciseId) {
            fetch('exercise_operations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `operation=get&exercise_id=${exerciseId}&user_id=${document.getElementById('user_id').value}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const exercise = data.data;
                    document.getElementById('exercise_id').value = exercise.id;
                    document.getElementById('exercise_type').value = exercise.exercise_type;
                    document.getElementById('duration').value = exercise.duration;
                    document.getElementById('intensity').value = exercise.intensity;
                    document.getElementById('sets').value = exercise.sets;
                    document.getElementById('reps').value = exercise.reps;
                    document.getElementById('weight').value = exercise.weight;
                    modal.style.display = "block";
                } else {
                    alert('Error loading exercise data');
                }
            });
        }
        
        function deleteExercise(exerciseId) {
            if (confirm('Are you sure you want to delete this exercise?')) {
                fetch('exercise_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `operation=delete&exercise_id=${exerciseId}&user_id=${document.getElementById('user_id').value}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting exercise');
                    }
                });
            }
        }
        
        editForm.onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(editForm);
            formData.append('operation', 'update');
            
            fetch('exercise_operations.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modal.style.display = "none";
                    location.reload();
                } else {
                    alert('Error updating exercise');
                }
            });
        }
    </script>
</body>
</html>
