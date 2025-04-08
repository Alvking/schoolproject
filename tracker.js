document.addEventListener("DOMContentLoaded", () => {
    const exerciseTypeInput = document.getElementById("exercise-type");
    const exerciseDurationInput = document.getElementById("exercise-duration");
    const exerciseIntensityInput = document.getElementById("exercise-intensity");
    const logExerciseButton = document.getElementById("log-exercise-btn");
    const exerciseLogList = document.getElementById("exercise-log-list");

    const strengthSetsInput = document.getElementById("strength-sets");
    const strengthRepsInput = document.getElementById("strength-reps");
    const strengthWeightInput = document.getElementById("strength-weight");
    const logStrengthButton = document.getElementById("log-strength-btn");

    // Example suggested workouts
    const suggestedWorkouts = [
        "Morning Yoga (30 mins)",
        "HIIT Full Body (40 mins)",
        "Strength Training (Chest and Arms)",
        "Running (5 km)",
        "Swimming (30 mins)"
    ];

    // Function to populate suggested workouts
    function populateSuggestedWorkouts() {
        const suggestedList = document.getElementById("suggested-workouts-list");
        suggestedWorkouts.forEach((workout) => {
            const listItem = document.createElement("li");
            listItem.textContent = workout;
            suggestedList.appendChild(listItem);
        });
    }

    // Log Exercise Function
    function logExercise() {
        const type = exerciseTypeInput.value;
        const duration = exerciseDurationInput.value;
        const intensity = exerciseIntensityInput.value;

        if (type && duration && intensity) {
            const exerciseLog = document.createElement("li");
            exerciseLog.textContent = `Exercise: ${type}, Duration: ${duration} mins, Intensity: ${intensity}/10`;
            exerciseLogList.appendChild(exerciseLog);

            // Clear input fields
            exerciseTypeInput.value = '';
            exerciseDurationInput.value = '';
            exerciseIntensityInput.value = '';
        } else {
            alert("Please fill in all fields");
        }
    }

    // Log Strength Training Function
    function logStrengthTraining() {
        const sets = strengthSetsInput.value;
        const reps = strengthRepsInput.value;
        const weight = strengthWeightInput.value;

        if (sets && reps && weight) {
            const strengthLog = document.createElement("li");
            strengthLog.textContent = `Strength Training: Sets: ${sets}, Reps: ${reps}, Weight: ${weight} kg`;
            exerciseLogList.appendChild(strengthLog);

            // Clear input fields
            strengthSetsInput.value = '';
            strengthRepsInput.value = '';
            strengthWeightInput.value = '';
        } else {
            alert("Please fill in all fields");
        }
    }

    // Event Listeners for Buttons
    logExerciseButton.addEventListener("click", logExercise);
    logStrengthButton.addEventListener("click", logStrengthTraining);

    // Populate the suggested workouts on page load
    populateSuggestedWorkouts();
});
