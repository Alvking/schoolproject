<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="contact.css">
</head>
<body>


<ul class="nav-bar">
        <li><a href="index.php">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php">Contact Us</a></li>
    </ul>
    <div class="container">
        <h2>Contact Us</h2>
        <p>Leave a message and we will get back to you shortly</p>
        <form action="">
            <div>
                <span>
                    <label for="name">Name</label><br>
                    <input type="text" id="name" name="name" placeholder="Full name" required>

                </span>

                <span>
                    <label for="email">Email</label><br>
                    <input type="email" id="email" placeholder="Email" required>
                    
                </span>
            </div>

            <label for="message">Your Message</label><br>
            <textarea name="" id="message" rows="10" placeholder="Your Message"></textarea>
            <button onclick="sendToWhatsapp()">Submit</button>
        </form>
    </div>
    <script src="whatsapp.js"></script>
</body>
</html>