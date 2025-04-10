let clients = [
    { id: 1, name: "user 2", progress: "Completed 5 workouts this week." },
    { id: 2, name: "user 3", progress: "Improved diet and lost 2kg." }
];

function loadClients() {
    let list = document.getElementById("client-list");
    list.innerHTML = "";
    clients.forEach(client => {
        let button = document.createElement("button");
        button.textContent = client.name;
        button.onclick = () => viewClient(client);
        list.appendChild(button);
    });
}

function viewClient(client) {
    document.getElementById("client-name").textContent = client.name;
    document.getElementById("progress-details").textContent = client.progress;
    document.getElementById("client-progress").style.display = "block";
    document.getElementById("comment-list").innerHTML = localStorage.getItem("comments-" + client.id) || "<li>No comments yet.</li>";
}

function submitComment() {
    let comment = document.getElementById("comment").value;
    let clientName = document.getElementById("client-name").textContent;
    let client = clients.find(c => c.name === clientName);
    if (comment && client) {
        let comments = localStorage.getItem("comments-" + client.id) || "";
        comments += `<li>${comment}</li>`;
        localStorage.setItem("comments-" + client.id, comments);
        document.getElementById("comment-list").innerHTML = comments;
        document.getElementById("comment").value = "";
    }
}

function logout() {
    localStorage.removeItem("role");
    window.location.href = "login.html";
}

loadClients();