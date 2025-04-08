document.getElementById("goalForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const goalType = document.getElementById("goalType").value;
    const goalTarget = document.getElementById("goalTarget").value;
    const goalDuration = document.getElementById("goalDuration").value;
    const activityLevel = document.getElementById("activityLevel").value;

    const goalData = {
        type: goalType,
        target: goalTarget,
        duration: goalDuration,
        activity: activityLevel
    };

    // Store in local storage or send to backend
    localStorage.setItem("userGoal", JSON.stringify(goalData));

    alert("Goal saved successfully!");
});
