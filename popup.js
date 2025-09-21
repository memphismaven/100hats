// app.js
document.addEventListener("DOMContentLoaded", function () {
    // Function to handle "Add Item" click
    document.getElementById("add-item-link").addEventListener("click", function () {
        const popup = document.getElementById("popup");
        popup.style.display = "block";
    });

    // Function to handle "Save Task" click
    document.getElementById("save-task").addEventListener("click", function () {
        // Implement your save task logic here
        const taskName = document.getElementById("task-name").value;
        const taskDescription = document.getElementById("task-description").value;

        // For demonstration purposes, log the values to the console
        console.log("Task Name:", taskName);
        console.log("Task Description:", taskDescription);

        // Close the popup
        const popup = document.getElementById("popup");
        popup.style.display = "none";
    });

    // Function to handle "Cancel" click
    document.getElementById("cancel-task").addEventListener("click", function () {
        const popup = document.getElementById("popup");
        popup.style.display = "none";
    });
});
