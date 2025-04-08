document.addEventListener('DOMContentLoaded', function() {
    const exerciseForm = document.getElementById('exerciseForm');
    const exerciseCards = document.getElementById('exerciseCards');

    // Fetch and display stored exercises from the database when the page loads
    fetchExercises();

    exerciseForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Get form values
        const formData = new FormData(exerciseForm);

        // Send data to PHP via AJAX
        fetch('save_exercise.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                exerciseForm.reset();
                fetchExercises(); // Reload exercises from database
            }
        })
        .catch(error => console.error('Error:', error));
    });

    function fetchExercises() {
        fetch('get_exercises.php') // PHP script to retrieve exercises
        .then(response => response.json())
        .then(data => {
            exerciseCards.innerHTML = ''; // Clear previous entries
            data.forEach(exercise => {
                const card = createExerciseCard(exercise);
                exerciseCards.appendChild(card);
            });
        })
        .catch(error => console.error('Error fetching exercises:', error));
    }

    function createExerciseCard(exercise) {
        const card = document.createElement('div');
        card.className = 'exercise-card';

        card.innerHTML = `
            <h4>${exercise.exercise_type}</h4>
            <p>Date: ${exercise.date}</p>
            <p>Duration: ${exercise.duration} minutes</p>
            <p>Intensity: ${exercise.intensity}</p>
            ${exercise.sets ? `<p>Sets: ${exercise.sets}</p>` : ''}
            ${exercise.reps ? `<p>Reps: ${exercise.reps}</p>` : ''}
            ${exercise.weight ? `<p>Weight: ${exercise.weight} kg</p>` : ''}
        `;

        return card;
    }
});
