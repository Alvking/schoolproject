document.addEventListener("DOMContentLoaded", () => {
    const goalDisplay = document.getElementById("goalDisplay");
    const progressContainer = document.getElementById("progress-container");
    const commentContainer = document.getElementById("comment-container");

    const savedGoal = localStorage.getItem("userGoal");

    if (savedGoal) {
        goalDisplay.innerText = `Your Goal: ${savedGoal}`;
    } else {
        goalDisplay.innerText = "No goals set yet!";
    }

    // Fetch user progress and comments
    fetch("fetch_progress.php")
        .then(response => response.json())
        .then(data => {
            if (data.progress) {
                progressContainer.innerHTML = `
                    <p><strong>Exercise:</strong> ${data.progress.exercise}</p>
                    <p><strong>Diet:</strong> ${data.progress.diet}</p>
                `;
            } else {
                progressContainer.innerHTML = "<p>No progress recorded yet.</p>";
            }

            if (data.comments.length > 0) {
                commentContainer.innerHTML = data.comments.map(comment => 
                    `<p>${comment.comment} - <i>${comment.date}</i></p>`
                ).join("");
            } else {
                commentContainer.innerHTML = "<p>No comments yet.</p>";
            }
        })
        .catch(error => console.error("Error loading progress:", error));

    // Clear goal button
    document.getElementById("clearGoal").addEventListener("click", () => {
        localStorage.removeItem("userGoal");
        goalDisplay.innerText = "No goals set yet!";
        alert("Goal Cleared!");
    });
});
