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

// Initialize meals array
$meals = [];

if ($user_id > 0) {
    $query = "SELECT id, meal_type, food_item, serving_size, calories, protein, carbs, fat, created_at 
              FROM meals 
              WHERE user_id = ? 
              ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $meals = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Group meals by date and meal type
$mealsByDate = [];
foreach ($meals as $meal) {
    $date = date('Y-m-d', strtotime($meal['created_at']));
    $mealType = $meal['meal_type'];
    
    if (!isset($mealsByDate[$date])) {
        $mealsByDate[$date] = [];
    }
    if (!isset($mealsByDate[$date][$mealType])) {
        $mealsByDate[$date][$mealType] = [];
    }
    $mealsByDate[$date][$mealType][] = $meal;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Diet Tracker</title>
    <link rel="stylesheet" href="user-diet.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Edit Meal Modal -->
    <div id="editMealModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Meal</h2>
            <form id="editMealForm">
                <input type="hidden" id="meal_id" name="meal_id">
                <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">
                
                <div class="form-group">
                    <label for="food_item">Food Item</label>
                    <input type="text" id="food_item" name="food_item" required>
                </div>
                
                <div class="form-group">
                    <label for="meal_type">Meal Type</label>
                    <select id="meal_type" name="meal_type" required>
                        <option value="breakfast">Breakfast</option>
                        <option value="lunch">Lunch</option>
                        <option value="dinner">Dinner</option>
                        <option value="snack">Snack</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="serving_size">Serving Size</label>
                    <input type="text" id="serving_size" name="serving_size" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="calories">Calories</label>
                        <input type="number" id="calories" name="calories" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="protein">Protein (g)</label>
                        <input type="number" id="protein" name="protein" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="carbs">Carbs (g)</label>
                        <input type="number" id="carbs" name="carbs" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fat">Fat (g)</label>
                        <input type="number" id="fat" name="fat" required>
                    </div>
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
                Dashboard
            </a>
        </nav>

        <div class="diet-container">
            <div class="header">
                <h1>Diet Tracker</h1>
                <a href="diet.php" class="add-meal-btn">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                    </svg>
                    Log Meal
                </a>
            </div>
        </div>

        <?php if (empty($meals)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <svg viewBox="0 0 24 24" width="48" height="48">
                    <path fill="currentColor" d="M18.06 23H19.72C20.56 23 21.25 22.35 21.35 21.53L23 5.05H18V1H16.03V5.05H11.06L11.36 7.39C13.07 7.86 14.67 8.71 15.63 9.65C17.07 11.07 18.06 12.54 18.06 14.94V23M1 22V21H16.03V22C16.03 22.54 15.58 23 15 23H2C1.45 23 1 22.54 1 22M16.03 15C16.03 7 1 7 1 15H16.03M1 17H16V19H1V17M15 2A8 8 0 0 0 7 10C7 11.77 7.53 13.42 8.43 14.82L15 8.25L21.57 14.82C22.47 13.42 23 11.77 23 10A8 8 0 0 0 15 2M15 4A6 6 0 0 1 21 10C21 11.29 20.62 12.49 20 13.5L15 8.5L10 13.5C9.38 12.49 9 11.29 9 10A6 6 0 0 1 15 4M15 4A6 6 0 0 1 21 10C21 11.29 20.62 12.49 20 13.5L15 8.5L10 13.5C9.38 12.49 9 11.29 9 10A6 6 0 0 1 15 4"/>
                </svg>
            </div>
            <h2>No Meals Logged Yet</h2>
            <p>Start tracking your nutrition by logging your first meal!</p>
        </div>
        <?php else: ?>
        <div class="diet-timeline">
            <?php foreach ($mealsByDate as $date => $mealTypes): ?>
            <div class="timeline-day">
                <div class="date-header">
                    <span class="date"><?= date('F j, Y', strtotime($date)) ?></span>
                    <div class="nutrition-summary">
                        <?php
                        $dailyTotals = ['calories' => 0, 'protein' => 0, 'carbs' => 0, 'fat' => 0];
                        foreach ($mealTypes as $meals) {
                            foreach ($meals as $meal) {
                                $dailyTotals['calories'] += $meal['calories'];
                                $dailyTotals['protein'] += $meal['protein'];
                                $dailyTotals['carbs'] += $meal['carbs'];
                                $dailyTotals['fat'] += $meal['fat'];
                            }
                        }
                        ?>
                        <span class="macro calories" title="Total Calories">
                            <svg viewBox="0 0 24 24" width="16" height="16">
                                <path fill="currentColor" d="M11.71,19C9.93,19 8.5,17.59 8.5,15.86C8.5,14.24 9.53,13.1 11.3,12.74C13.07,12.38 14.9,11.53 15.92,10.16C16.31,11.45 16.5,12.81 16.5,14.2C16.5,16.84 14.36,19 11.71,19M12,4A8,8 0 0 1 20,12A8,8 0 0 1 12,20A8,8 0 0 1 4,12A8,8 0 0 1 12,4M12,9L16,15H8L12,9Z"/>
                            </svg>
                            <?= number_format($dailyTotals['calories']) ?>
                        </span>
                        <span class="macro protein" title="Total Protein">
                            <svg viewBox="0 0 24 24" width="16" height="16">
                                <path fill="currentColor" d="M15,12C13.89,12 13,11.1 13,10A2,2 0 0,1 15,8A2,2 0 0,1 17,10C17,11.1 16.1,12 15,12M12,20L15.5,16.5L13,14L15.5,11.5L12,8L8.5,11.5L11,14L8.5,16.5L12,20M15,2A8,8 0 0,0 7,10C7,11.77 7.53,13.42 8.43,14.82L15,8.25L21.57,14.82C22.47,13.42 23,11.77 23,10A8,8 0 0,0 15,2M15,4A6,6 0 0,1 21,10C21,11.29 20.62,12.49 20,13.5L15,8.5L10,13.5C9.38,12.49 9,11.29 9,10A6,6 0 0,1 15,4Z"/>
                            </svg>
                            <?= number_format($dailyTotals['protein']) ?>g
                        </span>
                        <span class="macro carbs" title="Total Carbs">
                            <svg viewBox="0 0 24 24" width="16" height="16">
                                <path fill="currentColor" d="M12,6C8.67,6 6,8.67 6,12C6,15.33 8.67,18 12,18C15.33,18 18,15.33 18,12C18,8.67 15.33,6 12,6M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,9L16,15H8L12,9Z"/>
                            </svg>
                            <?= number_format($dailyTotals['carbs']) ?>g
                        </span>
                        <span class="macro fat" title="Total Fat">
                            <svg viewBox="0 0 24 24" width="16" height="16">
                                <path fill="currentColor" d="M13.5,0.67C13.5,0.67 14.24,3.32 14.24,5.47C14.24,7.53 12.89,9.2 10.83,9.2C8.76,9.2 7.2,7.53 7.2,5.47L7.23,5.1C5.21,7.5 4,10.61 4,14A8,8 0 0 0 12,22A8,8 0 0 0 20,14C20,8.6 17.41,3.8 13.5,0.67Z"/>
                            </svg>
                            <?= number_format($dailyTotals['fat']) ?>g
                        </span>
                    </div>
                </div>

                <div class="meal-types">
                    <?php foreach ($mealTypes as $mealType => $meals): ?>
                    <div class="meal-section">
                        <h3 class="meal-type">
                            <svg viewBox="0 0 24 24" width="20" height="20">
                                <?php if (stripos($mealType, 'breakfast') !== false): ?>
                                <path fill="currentColor" d="M2,21H20V19H2M20,8H18V5H20M20,3H4V13A4,4 0 0,0 8,17H14A4,4 0 0,0 18,13V10H20A2,2 0 0,0 22,8V5C22,3.89 21.1,3 20,3Z"/>
                                <?php elseif (stripos($mealType, 'lunch') !== false): ?>
                                <path fill="currentColor" d="M8.1,13.34L3.91,9.16C2.35,7.59 2.35,5.06 3.91,3.5L10.93,10.5L8.1,13.34M13.41,13L20.29,19.88L18.88,21.29L12,14.41L5.12,21.29L3.71,19.88L13.36,10.22L13.16,10C12.38,9.23 12.38,7.97 13.16,7.19L17.5,2.82L18.43,3.74L15.19,7L16.15,7.94L19.39,4.69L20.31,5.61L17.06,8.85L18,9.81L21.26,6.56L22.18,7.5L17.81,11.84C17.03,12.62 15.77,12.62 15,11.84L14.78,11.64L13.41,13Z"/>
                                <?php elseif (stripos($mealType, 'dinner') !== false): ?>
                                <path fill="currentColor" d="M8.1,13.34L3.91,9.16C2.35,7.59 2.35,5.06 3.91,3.5L10.93,10.5L8.1,13.34M14.88,11.53L13.41,13L20.29,19.88L18.88,21.29L12,14.41L5.12,21.29L3.71,19.88L13.47,10.12C12.76,8.59 13.26,6.44 14.85,4.85C16.76,2.93 19.5,2.57 20.96,4.03C22.43,5.5 22.07,8.24 20.15,10.15C18.56,11.74 16.41,12.24 14.88,11.53Z"/>
                                <?php else: ?>
                                <path fill="currentColor" d="M12,6C8.67,6 6,8.67 6,12C6,15.33 8.67,18 12,18C15.33,18 18,15.33 18,12C18,8.67 15.33,6 12,6M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,9L16,15H8L12,9Z"/>
                                <?php endif; ?>
                            </svg>
                            <?= htmlspecialchars($mealType) ?>
                        </h3>
                        <div class="meal-cards">
                            <?php foreach ($meals as $meal): ?>
                            <div class="meal-card">
                                <div class="meal-info">
                                    <h4><?= htmlspecialchars($meal['food_item']) ?></h4>
                                    <p class="serving-size"><?= htmlspecialchars($meal['serving_size']) ?></p>
                                </div>
                                <div class="nutrition-info">
                                    <div class="macro-item calories">
                                        <span class="value"><?= number_format($meal['calories']) ?></span>
                                        <span class="label">cal</span>
                                    </div>
                                    <div class="macro-item protein">
                                        <span class="value"><?= number_format($meal['protein']) ?>g</span>
                                        <span class="label">protein</span>
                                    </div>
                                    <div class="macro-item carbs">
                                        <span class="value"><?= number_format($meal['carbs']) ?>g</span>
                                        <span class="label">carbs</span>
                                    </div>
                                    <div class="macro-item fat">
                                        <span class="value"><?= number_format($meal['fat']) ?>g</span>
                                        <span class="label">fat</span>
                                    </div>
                                </div>
                                <div class="meal-actions">
                                    <button class="edit-btn" onclick="editMeal(<?php echo $meal['id']; ?>)">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                                        </svg>
                                    </button>
                                    <button class="delete-btn" onclick="deleteMeal(<?php echo $meal['id']; ?>)">
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
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
    const modal = document.getElementById('editMealModal');
    const closeBtn = document.getElementsByClassName('close')[0];
    const editForm = document.getElementById('editMealForm');
    
    closeBtn.onclick = function() {
        modal.style.display = "none";
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    
    function editMeal(mealId) {
        fetch('meal_operations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `operation=get&meal_id=${mealId}&user_id=${document.getElementById('user_id').value}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const meal = data.data;
                document.getElementById('meal_id').value = meal.id;
                document.getElementById('food_item').value = meal.food_item;
                document.getElementById('meal_type').value = meal.meal_type;
                document.getElementById('serving_size').value = meal.serving_size;
                document.getElementById('calories').value = meal.calories;
                document.getElementById('protein').value = meal.protein;
                document.getElementById('carbs').value = meal.carbs;
                document.getElementById('fat').value = meal.fat;
                modal.style.display = "block";
            } else {
                alert('Error loading meal data');
            }
        });
    }
    
    function deleteMeal(mealId) {
        if (confirm('Are you sure you want to delete this meal?')) {
            fetch('meal_operations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `operation=delete&meal_id=${mealId}&user_id=${document.getElementById('user_id').value}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting meal');
                }
            });
        }
    }
    
    editForm.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(editForm);
        formData.append('operation', 'update');
        
        fetch('meal_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.style.display = "none";
                location.reload();
            } else {
                alert('Error updating meal');
            }
        });
    }
    </script>
</body>
</html>
