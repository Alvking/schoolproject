<?php
session_start();

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "usersdb";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Water Intake Update
if (isset($_POST['action']) && $_POST['action'] === 'updateWater') {
    header('Content-Type: application/json');
    $water_glasses = intval($_POST['glasses']); // 

    try {
        // First check if there's an entry for today
        $check_sql = "SELECT id FROM water_intake WHERE user_id = ? AND DATE(created_at) = CURDATE() LIMIT 1";
        $check_stmt = $conn->prepare($check_sql);
        if (!$check_stmt) {
            throw new Exception("Failed to prepare check statement: " . $conn->error);
        }

        $check_stmt->bind_param("i", $user_id);
        if (!$check_stmt->execute()) {
            throw new Exception("Failed to execute check statement: " . $check_stmt->error);
        }

        $result = $check_stmt->get_result();
        $check_stmt->close();

        if ($result->num_rows > 0) {
            // Update existing record
            $sql = "UPDATE water_intake SET glasses = ? WHERE user_id = ? AND DATE(created_at) = CURDATE()"; // 
        } else {
            // Create new record
            $sql = "INSERT INTO water_intake (user_id, glasses, created_at) VALUES (?, ?, NOW())"; // 
        }

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("ii", $water_glasses, $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }

        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Water intake updated', 'glasses' => $water_glasses]); // 

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle Get Water Glasses
if (isset($_GET['action']) && $_GET['action'] === 'getWater') {
    header('Content-Type: application/json');
    try {
        $sql = "SELECT glasses FROM water_intake WHERE user_id = ? AND DATE(created_at) = CURDATE()"; // 
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $stmt->close();

        if ($row = $result->fetch_assoc()) {
            echo json_encode(['status' => 'success', 'glasses' => (int)$row['glasses']]); // 
        } else {
            echo json_encode(['status' => 'success', 'glasses' => 0]); // 
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle Meal Logging (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['action'])) {
    if (empty($_POST['mealType']) || empty($_POST['foodItem']) || empty($_POST['servingSize'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    // Sanitize input
    $meal_type = $conn->real_escape_string($_POST['mealType']);
    $food_item = $conn->real_escape_string($_POST['foodItem']);
    $serving_size = floatval($_POST['servingSize']);
    $calories = floatval($_POST['calories']);
    $protein = floatval($_POST['protein']);
    $carbs = floatval($_POST['carbs']);
    $fat = floatval($_POST['fat']);

    // Insert meal data into the meals table
    $sql = "INSERT INTO meals (user_id, meal_type, food_item, serving_size, calories, protein, carbs, fat, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("issddddd", $user_id, $meal_type, $food_item, $serving_size, $calories, $protein, $carbs, $fat);

    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Meal logged successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Could not log the meal']);
    }

    $stmt->close();
    exit;
}

// Get current water glasses
$water_glasses = 0;
$sql = "SELECT glasses FROM water_intake WHERE user_id = ? AND DATE(created_at) = CURDATE()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($row = $result->fetch_assoc()) {
    $water_glasses = (int)$row['glasses'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diet Tracking</title>
    <link rel="stylesheet" href="diet.css">
</head>
<body>

<ul class="nav-bar">
        <li><a href="index.php">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php">Contact Us</a></li>
    </ul>


    <div class="container">
        <h2>Diet Tracking</h2>
        
        <div class="daily-summary">
            <h3>Daily Summary</h3>
            <div class="summary-stats">
                <div class="stat-card">
                    <h4>Calories</h4>
                    <p><span id="totalCalories">0</span> kcal</p>
                    <div class="progress-bar">
                        <div class="progress-fill" id="caloriesBar"></div>
                    </div>
                </div>
                <div class="stat-card">
                    <h4>Protein</h4>
                    <p><span id="totalProtein">0</span>g</p>
                    <div class="progress-bar">
                        <div class="progress-fill" id="proteinBar"></div>
                    </div>
                </div>
                <div class="stat-card">
                    <h4>Carbs</h4>
                    <p><span id="totalCarbs">0</span>g</p>
                    <div class="progress-bar">
                        <div class="progress-fill" id="carbsBar"></div>
                    </div>
                </div>
                <div class="stat-card">
                    <h4>Fat</h4>
                    <p><span id="totalFat">0</span>g</p>
                    <div class="progress-bar">
                        <div class="progress-fill" id="fatBar"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="meal-form-container">
            <form id="mealForm" class="meal-form">
                <h3>Log a Meal</h3>
                <div class="form-group">
                    <label for="mealType">Meal Type</label>
                    <select id="mealType" name="mealType" required>
                        <option value="">Select meal type</option>
                        <option value="Breakfast">Breakfast</option>
                        <option value="Lunch">Lunch</option>
                        <option value="Dinner">Dinner</option>
                        <option value="Snack">Snack</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="foodItem">Food Item</label>
                    <input type="text" id="foodItem" name="foodItem" list="foodList" required>
                    <datalist id="foodList"></datalist>
                </div>
                
                <div class="form-group">
                    <label for="servingSize">Serving Size (g)</label>
                    <input type="number" id="servingSize" name="servingSize" min="0" step="1" required>
                </div>
                
                <div class="nutrients-info">
                    <h4>Nutrients</h4>
                    <div class="nutrients-grid">
                        <div>
                            <span>Calories:</span>
                            <span id="caloriesValue">0</span>
                        </div>
                        <div>
                            <span>Protein:</span>
                            <span id="proteinValue">0</span>g
                        </div>
                        <div>
                            <span>Carbs:</span>
                            <span id="carbsValue">0</span>g
                        </div>
                        <div>
                            <span>Fat:</span>
                            <span id="fatValue">0</span>g
                        </div>
                    </div>
                </div>
                
                <!-- Hidden inputs for form submission -->
                <input type="hidden" id="calories" name="calories">
                <input type="hidden" id="protein" name="protein">
                <input type="hidden" id="carbs" name="carbs">
                <input type="hidden" id="fat" name="fat">
                
                <button type="submit" class="submit-button">Log Meal</button>
            </form>
        </div>
        
        <div class="water-tracking">
            <h3>Water Intake</h3>
            <p>Glasses of water today:</p>
            <div class="water-controls">
                <button id="decreaseWater" class="water-button">-</button>
                <span id="glassesCount" class="water-display">0</span>
                <button id="increaseWater" class="water-button">+</button>
            </div>
        </div>
        
        <div id="mealHistory" class="meal-history"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mealForm = document.getElementById('mealForm');
            const foodItemInput = document.getElementById('foodItem');
            const servingSizeInput = document.getElementById('servingSize');
            const decreaseWaterBtn = document.getElementById('decreaseWater');
            const increaseWaterBtn = document.getElementById('increaseWater');
            const waterAmountDisplay = document.getElementById('glassesCount');
            let waterAmount = 0;

            // Load initial water amount
            fetch('diet.php?action=getWater')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        waterAmount = parseInt(data.glasses) || 0;
                        waterAmountDisplay.textContent = waterAmount;
                    } else {
                        throw new Error(data.message || 'Failed to load water intake');
                    }
                })
                .catch(error => {
                    console.error('Error loading water intake:', error);
                    showMessage('Error loading water intake: ' + error.message, true);
                });

            function updateWaterIntake(newAmount) {
                const formData = new FormData();
                formData.append('action', 'updateWater');
                formData.append('glasses', newAmount);

                fetch('diet.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        waterAmount = parseInt(data.glasses) || newAmount;
                        waterAmountDisplay.textContent = waterAmount;
                        showMessage('Water intake updated successfully!');
                    } else {
                        throw new Error(data.message || 'Failed to update water intake');
                    }
                })
                .catch(error => {
                    console.error('Water intake error:', error);
                    showMessage('Error: ' + error.message, true);
                });
            }

            // Water intake controls
            decreaseWaterBtn.addEventListener('click', () => {
                if (waterAmount > 0) {
                    updateWaterIntake(waterAmount - 1);
                }
            });

            increaseWaterBtn.addEventListener('click', () => {
                updateWaterIntake(waterAmount + 1);
            });

            function showMessage(message, isError = false) {
                const messageDiv = document.createElement('div');
                messageDiv.textContent = message;
                messageDiv.style.padding = '10px';
                messageDiv.style.marginBottom = '10px';
                messageDiv.style.borderRadius = '4px';
                messageDiv.style.backgroundColor = isError ? '#ffebee' : '#e8f5e9';
                messageDiv.style.color = isError ? '#c62828' : '#2e7d32';
                
                const container = document.querySelector('.container');
                container.insertBefore(messageDiv, container.firstChild);
                
                setTimeout(() => messageDiv.remove(), 5000);
            }

            // Food item autocomplete
            foodItemInput.addEventListener('input', function() {
                if (this.value.length >= 2) {
                    fetch(`get_food_items.php?search=${encodeURIComponent(this.value)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            const foodList = document.getElementById('foodList');
                            foodList.innerHTML = '';
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.name;
                                foodList.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching food items:', error);
                            showMessage('Error fetching food items: ' + error.message, true);
                        });
                }
            });

            // Update nutrients when food item is selected
            foodItemInput.addEventListener('change', function() {
                if (this.value && servingSizeInput.value) {
                    updateNutrients(this.value, servingSizeInput.value);
                }
            });

            servingSizeInput.addEventListener('input', function() {
                if (this.value && foodItemInput.value) {
                    updateNutrients(foodItemInput.value, this.value);
                }
            });

            function updateNutrients(foodItem, servingSize) {
                fetch(`get_food_items.php?item=${encodeURIComponent(foodItem)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            const serving = parseFloat(servingSize);
                            const multiplier = serving / 100;
                            
                            document.getElementById('calories').value = (data.calories_per_100g * multiplier).toFixed(1);
                            document.getElementById('protein').value = (data.protein_per_100g * multiplier).toFixed(1);
                            document.getElementById('carbs').value = (data.carbs_per_100g * multiplier).toFixed(1);
                            document.getElementById('fat').value = (data.fat_per_100g * multiplier).toFixed(1);
                            
                            document.getElementById('caloriesValue').textContent = document.getElementById('calories').value;
                            document.getElementById('proteinValue').textContent = document.getElementById('protein').value;
                            document.getElementById('carbsValue').textContent = document.getElementById('carbs').value;
                            document.getElementById('fatValue').textContent = document.getElementById('fat').value;
                        } else {
                            throw new Error(data.message || 'Failed to fetch food item data');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating nutrients:', error);
                        showMessage('Error updating nutrients: ' + error.message, true);
                    });
            }

            mealForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate form data
                const mealType = document.getElementById('mealType').value;
                const foodItem = foodItemInput.value;
                const servingSize = servingSizeInput.value;
                const calories = document.getElementById('calories').value;
                const protein = document.getElementById('protein').value;
                const carbs = document.getElementById('carbs').value;
                const fat = document.getElementById('fat').value;

                if (!mealType || !foodItem || !servingSize) {
                    showMessage('Please fill in all required fields', true);
                    return;
                }

                const formData = new FormData();
                formData.append('mealType', mealType);
                formData.append('foodItem', foodItem);
                formData.append('servingSize', servingSize);
                formData.append('calories', calories);
                formData.append('protein', protein);
                formData.append('carbs', carbs);
                formData.append('fat', fat);

                fetch('diet.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('Meal has been successfully logged to the database!');
                        showMessage('Meal logged successfully!');
                        mealForm.reset();
                        updateDailySummary();
                    } else {
                        throw new Error(data.message || 'Failed to log meal');
                    }
                })
                .catch(error => {
                    console.error('Error logging meal:', error);
                    showMessage('Error logging meal: ' + error.message, true);
                });
            });

            // Add input validation for numeric fields
            const numericInputs = document.querySelectorAll('input[type="number"]');
            numericInputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value < 0) this.value = 0;
                });
            });

            // Initial daily summary update
            updateDailySummary();
        });

        function updateDailySummary() {
            fetch('get_daily_summary.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('totalCalories').textContent = data.calories.toFixed(0);
                        document.getElementById('totalProtein').textContent = data.protein.toFixed(1);
                        document.getElementById('totalCarbs').textContent = data.carbs.toFixed(1);
                        document.getElementById('totalFat').textContent = data.fat.toFixed(1);
                        
                        // Update progress bars (assuming 2000 cal, 50g protein, 275g carbs, 55g fat as daily goals)
                        document.getElementById('caloriesBar').style.width = `${Math.min((data.calories / 2000) * 100, 100)}%`;
                        document.getElementById('proteinBar').style.width = `${Math.min((data.protein / 50) * 100, 100)}%`;
                        document.getElementById('carbsBar').style.width = `${Math.min((data.carbs / 275) * 100, 100)}%`;
                        document.getElementById('fatBar').style.width = `${Math.min((data.fat / 55) * 100, 100)}%`;
                    } else {
                        throw new Error(data.message || 'Failed to fetch daily summary');
                    }
                })
                .catch(error => {
                    console.error('Error updating daily summary:', error);
                    showMessage('Error updating daily summary: ' + error.message, true);
                });
        }

        // Water Intake Functions
        let currentGlasses = <?= $water_glasses ?? 0 ?>;

        function updateWaterIntake(change) {
            currentGlasses = Math.max(0, currentGlasses + change);
            document.getElementById('glassesCount').textContent = currentGlasses;
        }

        function saveWaterIntake() {
            const formData = new FormData();
            formData.append('glasses', currentGlasses);

            fetch('diet.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showMessage('Water intake updated successfully!');
                } else {
                    showMessage(data.message || 'Error updating water intake', true);
                }
            })
            .catch(error => {
                console.error('Error saving water intake:', error);
                showMessage('Error saving water intake', true);
            });
        }

        // Load current water intake
        fetch('diet.php?action=getWater')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    currentGlasses = data.glasses;
                    document.getElementById('glassesCount').textContent = currentGlasses;
                }
            })
            .catch(error => {
                console.error('Error loading water intake:', error);
                showMessage('Error loading water intake', true);
            });
    </script>
</body>
</html>