<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "usersdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $exercise_type = $_POST['exercise_type'];
    $duration = $_POST['duration'];
    $intensity = $_POST['intensity'];
    $sets = !empty($_POST['sets']) ? $_POST['sets'] : NULL;
    $reps = !empty($_POST['reps']) ? $_POST['reps'] : NULL;
    $weight = !empty($_POST['weight']) ? $_POST['weight'] : NULL;
    $date = $_POST['date'];

    // Validate inputs
    if (empty($exercise_type) || empty($duration) || empty($intensity) || empty($date)) {
        $error = "Please fill in all required fields.";
    } elseif (!in_array($intensity, ['Low', 'Medium', 'High'])) {
        $error = "Invalid intensity level selected.";
    } elseif ($duration <= 0) {
        $error = "Duration must be greater than 0.";
    } elseif (!empty($sets) && $sets <= 0) {
        $error = "Sets must be greater than 0.";
    } elseif (!empty($reps) && $reps <= 0) {
        $error = "Reps must be greater than 0.";
    } elseif (!empty($weight) && $weight < 0) {
        $error = "Weight cannot be negative.";
    } else {
        $sql = "INSERT INTO exercises (user_id, exercise_type, duration, intensity, sets, reps, weight, date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isisiiis", $user_id, $exercise_type, $duration, $intensity, $sets, $reps, $weight, $date);
        
        if ($stmt->execute()) {
            $message = "Exercise logged successfully!";
           
            $_POST = array();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}


$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM exercises WHERE user_id = ? ORDER BY date DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_exercises = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Exercise</title>
    <link rel="stylesheet" href="exercise.css?v=<?php echo time(); ?>">
</head>
<body>

<ul class="nav-bar">
        <li><a href="index.php">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php">Contact Us</a></li>
    </ul>

    <div class="container">


        <h2 class="section-title">Exercise Tracker</h2>
        
        <div class="exercise-grid">
            <!-- Exercise Form -->
            <div class="exercise-form-container">
                <h3>Log Exercise</h3>
                
                <?php if ($message): ?>
                    <div class="success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="exercise_type">Exercise Type *</label>
                        <input type="text" id="exercise_type" name="exercise_type" 
                               value="<?php echo htmlspecialchars($_POST['exercise_type'] ?? ''); ?>" 
                               placeholder="e.g., Running, Swimming, Weight Training" required>
                    </div>

                    <div class="form-group">
                        <label for="duration">Duration (minutes) *</label>
                        <input type="number" id="duration" name="duration" min="1" 
                               value="<?php echo htmlspecialchars($_POST['duration'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="intensity">Intensity *</label>
                        <select id="intensity" name="intensity" required>
                            <option value="">Select Intensity</option>
                            <option value="Low" <?php echo (isset($_POST['intensity']) && $_POST['intensity'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                            <option value="Medium" <?php echo (isset($_POST['intensity']) && $_POST['intensity'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="High" <?php echo (isset($_POST['intensity']) && $_POST['intensity'] == 'High') ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date">Date *</label>
                        <input type="date" id="date" name="date" 
                               value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d')); ?>" required>
                    </div>

                    <div class="optional-fields">
                        <h3>Strength Training Details (Optional)</h3>
                        
                        <div class="form-group">
                            <label for="sets">Number of Sets</label>
                            <input type="number" id="sets" name="sets" min="1" 
                                   value="<?php echo htmlspecialchars($_POST['sets'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="reps">Number of Reps</label>
                            <input type="number" id="reps" name="reps" min="1" 
                                   value="<?php echo htmlspecialchars($_POST['reps'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="weight">Weight (kg)</label>
                            <input type="number" id="weight" name="weight" min="0" 
                                   value="<?php echo htmlspecialchars($_POST['weight'] ?? ''); ?>">
                        </div>
                    </div>

                    <button type="submit">Log Exercise</button>
                </form>
            </div>

            <!-- Recent Exercises -->
            <div class="exercise-history">
                <h3>Recent Exercises</h3>
                <?php if (!empty($recent_exercises)): ?>
                    <div class="exercise-list">
                        <?php foreach ($recent_exercises as $exercise): ?>
                            <div class="exercise-item">
                                <div class="exercise-type"><?php echo htmlspecialchars($exercise['exercise_type']); ?></div>
                                <div class="exercise-stats">
                                    <div class="stat-item">
                                        <strong>Duration:</strong> <?php echo htmlspecialchars($exercise['duration']); ?> min
                                    </div>
                                    <div class="stat-item">
                                        <strong>Intensity:</strong> <?php echo htmlspecialchars($exercise['intensity']); ?>
                                    </div>
                                    <?php if ($exercise['sets'] && $exercise['reps']): ?>
                                        <div class="stat-item">
                                            <strong>Sets × Reps:</strong> 
                                            <?php echo htmlspecialchars($exercise['sets']); ?> × 
                                            <?php echo htmlspecialchars($exercise['reps']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($exercise['weight']): ?>
                                        <div class="stat-item">
                                            <strong>Weight:</strong> <?php echo htmlspecialchars($exercise['weight']); ?> kg
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="exercise-date">
                                    <?php echo date('F j, Y', strtotime($exercise['date'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No exercises logged yet.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <p class="back-link">
            <a href="profile.php">Back to Profile</a>
        </p>
    </div>
</body>
</html>
