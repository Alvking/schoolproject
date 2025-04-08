<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$username = "root";
$password = "";
$database = "usersdb";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get user ID from session (Replace with actual authentication)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST['date'];
    $weight = $_POST['weight'];
    $chest = $_POST['chest'];
    $waist = $_POST['waist'];
    $hips = $_POST['hips'];
    $biceps = $_POST['biceps'];
    $thighs = $_POST['thighs'];
    $pushups = $_POST['pushups'];
    $running_time = $_POST['running'];
    $plank_time = $_POST['plank'];
    $lifting_max = $_POST['lifting_max'];
    $progress_notes = $_POST['notes'];

    // Handle Photo Uploads
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    function uploadImage($fileInput, $target_dir) {
        if (!empty($_FILES[$fileInput]["name"])) {
            $target_file = $target_dir . basename($_FILES[$fileInput]["name"]);
            if (move_uploaded_file($_FILES[$fileInput]["tmp_name"], $target_file)) {
                return $target_file;
            }
        }
        return null;
    }

    $front_photo = uploadImage("front_photo", $target_dir);
    $side_photo = uploadImage("side_photo", $target_dir);
    $back_photo = uploadImage("back_photo", $target_dir);

    // Insert Progress Data
    $stmt = $conn->prepare("INSERT INTO progress (user_id, date, weight, chest, waist, hips, biceps, thighs, pushups, running_time, plank_time, progress_notes, logged_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isddddddidid", $user_id, $date, $weight, $chest, $waist, $hips, $biceps, $thighs, $pushups, $running_time, $plank_time, $progress_notes);
    $stmt->execute();
    $stmt->close();

    // Insert Progress Photos
    if ($front_photo || $side_photo || $back_photo) {
        $stmt = $conn->prepare("INSERT INTO progress_photo (user_id, date, front_photo, side_photo, back_photo, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issss", $user_id, $date, $front_photo, $side_photo, $back_photo);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: progress.php");
    exit();
}

// Fetch user progress data
$progress_query = $conn->query("SELECT * FROM progress WHERE user_id = $user_id ORDER BY date DESC");
$progress_data = [];
while ($row = $progress_query->fetch_assoc()) {
    $progress_data[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracker</title>
    <link rel="stylesheet" href="progress.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<ul class="nav-bar">
        <li><a href="index.php">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php">Contact Us</a></li>
    </ul>

    <div class="container">
        <h1>Progress Monitoring</h1>

        <div class="insights-container">
            <div class="insight-button weight">
                <div class="insight-title">Current Weight</div>
                <div class="insight-value"><?= !empty($progress_data[0]['weight']) ? $progress_data[0]['weight'] : '0' ?> kg</div>
                <?php if (count($progress_data) > 1) : 
                    $weight_change = $progress_data[0]['weight'] - $progress_data[1]['weight'];
                    $change_class = $weight_change <= 0 ? 'positive' : 'negative';
                ?>
                <div class="insight-change <?= $change_class ?>">
                    <?= abs($weight_change) ?> kg <?= $weight_change <= 0 ? '↓' : '↑' ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="insight-button strength">
                <div class="insight-title">Push-ups</div>
                <div class="insight-value"><?= !empty($progress_data[0]['pushups']) ? $progress_data[0]['pushups'] : '0' ?></div>
                <?php if (count($progress_data) > 1) : 
                    $pushup_change = $progress_data[0]['pushups'] - $progress_data[1]['pushups'];
                    $change_class = $pushup_change >= 0 ? 'positive' : 'negative';
                ?>
                <div class="insight-change <?= $change_class ?>">
                    <?= abs($pushup_change) ?> reps <?= $pushup_change >= 0 ? '↑' : '↓' ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="insight-button cardio">
                <div class="insight-title">Running Time</div>
                <div class="insight-value"><?= !empty($progress_data[0]['running_time']) ? $progress_data[0]['running_time'] : '0' ?> min</div>
                <?php if (count($progress_data) > 1) : 
                    $running_change = $progress_data[0]['running_time'] - $progress_data[1]['running_time'];
                    $change_class = $running_change >= 0 ? 'positive' : 'negative';
                ?>
                <div class="insight-change <?= $change_class ?>">
                    <?= abs($running_change) ?> min <?= $running_change >= 0 ? '↑' : '↓' ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="insight-button measurements">
                <div class="insight-title">Waist Size</div>
                <div class="insight-value"><?= !empty($progress_data[0]['waist']) ? $progress_data[0]['waist'] : '0' ?> cm</div>
                <?php if (count($progress_data) > 1) : 
                    $waist_change = $progress_data[0]['waist'] - $progress_data[1]['waist'];
                    $change_class = $waist_change <= 0 ? 'positive' : 'negative';
                ?>
                <div class="insight-change <?= $change_class ?>">
                    <?= abs($waist_change) ?> cm <?= $waist_change <= 0 ? '↓' : '↑' ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <form class="progress-form" method="POST" enctype="multipart/form-data">
            <div class="form-section">
                <h2>Log Progress</h2>
                <label>Date: <input type="date" name="date" required></label>
                <label>Weight (kg): <input type="number" name="weight" step="0.1" required></label>
                <label>Progress Notes: <textarea name="notes"></textarea></label>
            </div>

            <div class="form-section">
                <h2>Body Measurements</h2>
                <label>Chest (cm): <input type="number" name="chest"></label>
                <label>Waist (cm): <input type="number" name="waist"></label>
                <label>Hips (cm): <input type="number" name="hips"></label>
                <label>Biceps (cm): <input type="number" name="biceps"></label>
                <label>Thighs (cm): <input type="number" name="thighs"></label>
            </div>

            <div class="form-section">
                <h2>Fitness Test</h2>
                <label>Push-ups: <input type="number" name="pushups"></label>
                <label>Running Time (min): <input type="number" name="running" step="0.1"></label>
                <label>Plank Hold (sec): <input type="number" name="plank"></label>
                <label>Lifting Max (kg): <input type="number" name="lifting_max"></label>
            </div>

            <div class="form-section photo-section">
                <h2>Upload Progress Photos</h2>
                <div class="photo-upload-grid">
                    <div class="photo-upload-container">
                        <label for="front_photo" class="photo-upload-label">
                            <div class="upload-icon">
                                <svg viewBox="0 0 24 24" width="24" height="24">
                                    <path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                            </div>
                            <span class="upload-title">Front View</span>
                            <span class="upload-hint">Click to choose file</span>
                        </label>
                        <input type="file" id="front_photo" name="front_photo" accept="image/*" class="photo-input">
                        <div class="file-name" id="front_photo_name">No file chosen</div>
                    </div>

                    <div class="photo-upload-container">
                        <label for="side_photo" class="photo-upload-label">
                            <div class="upload-icon">
                                <svg viewBox="0 0 24 24" width="24" height="24">
                                    <path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                            </div>
                            <span class="upload-title">Side View</span>
                            <span class="upload-hint">Click to choose file</span>
                        </label>
                        <input type="file" id="side_photo" name="side_photo" accept="image/*" class="photo-input">
                        <div class="file-name" id="side_photo_name">No file chosen</div>
                    </div>

                    <div class="photo-upload-container">
                        <label for="back_photo" class="photo-upload-label">
                            <div class="upload-icon">
                                <svg viewBox="0 0 24 24" width="24" height="24">
                                    <path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                            </div>
                            <span class="upload-title">Back View</span>
                            <span class="upload-hint">Click to choose file</span>
                        </label>
                        <input type="file" id="back_photo" name="back_photo" accept="image/*" class="photo-input">
                        <div class="file-name" id="back_photo_name">No file chosen</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="save-progress-btn">
                <span class="btn-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M17 3H5C3.89 3 3 3.9 3 5V19C3 20.1 3.89 21 5 21H19C20.1 21 21 20.1 21 19V7L17 3M19 19H5V5H16.17L19 7.83V19M12 12C10.34 12 9 13.34 9 15S10.34 18 12 18 15 16.66 15 15 13.66 12 12 12M6 6H15V10H6V6Z"/>
                    </svg>
                </span>
                Save Progress
            </button>
        </form>

        <h2>Weight Tracking Graph</h2>
        <canvas id="weightChart"></canvas>

        <h2>History</h2>
        <div class="history">
            <?php foreach ($progress_data as $progress) : ?>
                <div class="history-card">
                    <p><strong>Date:</strong> <?= $progress['date'] ?></p>
                    <p><strong>Weight:</strong> <?= $progress['weight'] ?> kg</p>
                    <p><strong>Push-ups:</strong> <?= $progress['pushups'] ?></p>
                    <p><strong>Running Time:</strong> <?= $progress['running_time'] ?> min</p>
                    <p><strong>Plank Time:</strong> <?= $progress['plank_time'] ?> sec</p>
                    <p><strong>Notes:</strong> <?= $progress['progress_notes'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        const weightChart = new Chart(document.getElementById('weightChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($progress_data, 'date')) ?>,
                datasets: [{
                    label: 'Weight (kg)',
                    data: <?= json_encode(array_column($progress_data, 'weight')) ?>,
                    borderColor: '#4CAF50',
                    fill: false,
                    tension: 0.4
                }]
            }
        });
    </script>

    <script>
        // Add file name display functionality
        document.querySelectorAll('.photo-input').forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
                document.getElementById(this.id + '_name').textContent = fileName;
                
                // Add 'has-file' class to container if file is selected
                const container = this.closest('.photo-upload-container');
                if (this.files[0]) {
                    container.classList.add('has-file');
                } else {
                    container.classList.remove('has-file');
                }
            });
        });
    </script>

</body>
</html>
