<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services</title>
    <link rel="stylesheet" href="services.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <script>
        function toggleDropdown(event) {
            event.preventDefault();
            document.getElementById("myDropdown").classList.toggle("show");
        }

        // Close dropdown if clicked outside
        window.onclick = function(event) {
            if (!event.target.closest('.join-btn')) {
                document.getElementById("myDropdown").classList.remove("show");
            }
        };
    </script>

</head>
<body>

    <!-- Navigation Bar -->
    <div class="nav-bar">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact Us</a></li>
        </ul>

        <div class="join-btn">
            <a href="#" class="nav-btn" onclick="toggleDropdown(event)">Account <i class="fa fa-caret-down"></i></a>
            <div class="dropdown-content" id="myDropdown">
                <a href="profile.php"><i class="fa fa-user"></i>All info</a>
                <a href="index.php"><i class="fa fa-sign-out"></i> Log out</a>
            </div>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="service">
        <h2>WELCOME TO OUR SERVICES</h2>
    </div>

    <!-- Services Container -->
    <div class="container">
        <div class="features">
            <h3>GOAL SETTING</h3>
            <p>Set, monitor, and achieve SMART fitness goals with our fitness tracker. Track weight, muscle, endurance, and activity levels.</p>
            <a href="./goal.php"><button>GET STARTED</button></a>
        </div>

        <div class="features">
            <h3>EXERCISE TRACKING</h3>
            <p>Log your exercises, monitor progress, and stay accountable with detailed workout tracking and analytics.</p>
            <a href="./exercise.php"><button>GET STARTED</button></a>
        </div>

        <div class="features">
            <h3>DIET TRACKING</h3>
            <p>Maintain a balanced diet by tracking your food intake, calories, macronutrients, and meal plans.</p>
            <a href="./diet.php"><button>GET STARTED</button></a>
        </div>

        <div class="features">
            <h3>PROGRESS INSIGHTS</h3>
            <p>Visualize your fitness journey with detailed progress tracking, graphs, and performance insights.</p>
            <a href="./progress.php"><button>GET STARTED</button></a>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="footer-section">
        <div class="footer">
            <div class="footer-features">
                <h3>OUR MISSION</h3>
                <p>We empower individuals to take control of their health and fitness by providing a seamless and effective goal-tracking system.</p>
            </div>

            <div class="footer-features">
                <h3>Privacy Policy & Terms</h3>
                <p>We are committed to protecting your privacy. Your data is secured and never shared without consent. Read our full policy for details.</p>
            </div>

            <div class="footer-features">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><i class="fa fa-facebook"></i> Facebook</a>
                    <a href="#"><i class="fa fa-twitter"></i> Twitter</a>
                    <a href="#"><i class="fa fa-instagram"></i> Instagram</a>
                    <a href="#"><i class="fa fa-linkedin"></i> LinkedIn</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
