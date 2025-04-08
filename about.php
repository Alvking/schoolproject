<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Health & Fitness Tracker</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="about.css">
</head>
<body>

    <!-- Navigation Bar -->
    <header>
        <nav>
            <div class="logo">Health & Fitness Goal Tracker</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php" class="active">About</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="login.php" class="btn">Login</a></li>
            </ul>
        </nav>
    </header>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <h1>About Our Health & Fitness Tracker</h1>
            <p>Our platform helps users set, track, and achieve their health and fitness goals by monitoring exercise, diet, and progress.</p>

            <div class="features">
                <div class="feature">
                    <i class="fas fa-dumbbell"></i>
                    <h3>Track Your Workouts</h3>
                    <p>Log your exercises, sets, reps, and duration to monitor your progress over time.</p>
                </div>

                <div class="feature">
                    <i class="fas fa-apple-alt"></i>
                    <h3>Monitor Your Diet</h3>
                    <p>Record meals and track calories to maintain a balanced diet.</p>
                </div>

                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <h3>Analyze Your Progress</h3>
                    <p>View insights, progress charts, and reports to stay on track towards your fitness goals.</p>
                </div>

                <div class="feature">
                    <i class="fas fa-user-md"></i>
                    <h3>Expert Guidance</h3>
                    <p>Nutritionists and trainers provide feedback and suggestions to help you improve.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Health & Fitness Tracker. All Rights Reserved.</p>
    </footer>

</body>
</html>
