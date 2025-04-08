document.addEventListener("DOMContentLoaded", () => {
    // Function to add hover effect to a button
    const addHoverEffect = (button) => {
        if (button) {
            button.addEventListener("mouseover", () => {
                button.style.transform = "scale(1.1)";
                button.style.transition = "all 0.3s ease";
            });

            button.addEventListener("mouseleave", () => {
                button.style.transform = "scale(1)";
            });
        }
    };

    // Get both buttons
    const navJoinButton = document.getElementById("navJoinButton");
    const mainJoinButton = document.getElementById("mainJoinButton");

    // Add hover effects to both buttons
    addHoverEffect(navJoinButton);
    addHoverEffect(mainJoinButton);
});

    function checkAuth(event, page) {
        event.preventDefault(); // Prevent the default navigation behavior

        if (!localStorage.getItem("token")) {
            alert("You need to log in first!");
            window.location.href = "login.php"; 
        } else {
            window.location.href = page; // Redirect to services if logged in
        }
    }
