<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$dashboardLink = $isLoggedIn ? "services.php" : "login.php";
$joinText = $isLoggedIn ? "Dashboard" : "Join Us";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health and Fitness Goal Tracker</title>
    <link rel="stylesheet" href="hom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

    <header class="header-navbar">
        <ul class="nav-bar">
            <li><a href="index.php">Home</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact Us</a></li>

            <div class="join-btn">
                <a id="navJoinButton" href="login.php" class="nav-btn">login</a>
            </div>
        </ul>
    </header>
    
    <div class="content-wrapper">
        <div class="main-section">
            <h2>THE FITNESS CLUB</h2>
            <p>IT'S TIME TO BE HEALTHY AND IN GREAT SHAPE<br>Track your fitness. Achieve your goals. Stay motivated.</p>
            <p>Set, monitor, and achieve SMART fitness goals with our fitness<br> Track weight, muscle, endurance, and activity levels.</p>
            <div class="joinus-btn">
                <a id="mainJoinButton" href="login.php" class="nav-btn">login</a>
            </div>
        </div>

        <div class="footer">
            <footer>
                <div class="footer-container">
                    <div class="footer-section">
                        <h2>Health & Fitness Tracker</h2>
                        <p>Track, Improve, Succeed.</p>
                    </div>
                    <div class="footer-section">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="services.php">Services</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="contact.php">Contact</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h3>Follow Us</h3>
                        <div class="social-icons">
                            <a href="#"><i class="fa fa-facebook"></i> Facebook</a>
                            <a href="#"><i class="fa fa-twitter"></i> X</a>
                            <a href="#"><i class="fa fa-instagram"></i> Instagram</a>
                        </div>
                    </div>
                    <div class="footer-section">
                        <h3>Contact</h3>
                        <p>Email: kipronoalvin4@gmail.com</p>
                        <p>Phone: +254 797619684</p>
                    </div>
                </div>
                <div class="footer-bottom">
                    &copy; 2025 Health & Fitness Tracker. All rights reserved.
                </div>
            </footer>
        </div>

        <script src="hom.js"></script>
    </div>
    
</body>
</html>
