// app.js
document.addEventListener("DOMContentLoaded", function () {
    // JavaScript to be executed after the DOM is ready

    // Function to populate the to-do items
    function populateToDoItems() {
        const todoItems = ["Task 1", "Task 2", "Task 3", "Task 4", "Task 5"];
        const listContainer = document.getElementById("list-container");

        todoItems.forEach((item) => {
            const listItem = document.createElement("li");
            listItem.classList.add("task-item");

            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";

            const label = document.createElement("label");
            label.innerText = item;

            label.addEventListener("click", function () {
                // Show a popup with the task name
                const popup = document.getElementById("popup");
                const popupContent = document.getElementById("popup-content");
                popupContent.innerText = item;
                popup.style.display = "block";
            });

            const hr = document.createElement("hr");

            listItem.appendChild(checkbox);
            listItem.appendChild(label);
            listContainer.appendChild(listItem);
            listContainer.appendChild(hr);
        });
    }

    // Function to handle link clicks
    function handleLinkClick(link) {
        const listHead = document.getElementById("list-head");
        listHead.innerText = link;
    }

    // Event listeners
    document.getElementById("today-link").addEventListener("click", function () {
        handleLinkClick("Today");
    });

    document.getElementById("inbox-link").addEventListener("click", function () {
        handleLinkClick("Inbox");
    });

    document.getElementById("add-task-link").addEventListener("click", function () {
        const popup = document.getElementById("popup");
        popup.style.display = "block";
    });

    document.getElementById("save-task").addEventListener("click", function () {
        // Handle saving the task (you can implement this part)
        const popup = document.getElementById("popup");
        popup.style.display = "none";
    });

    document.getElementById("cancel-task").addEventListener("click", function () {
        const popup = document.getElementById("popup");
        popup.style.display = "none";
    });

    // Initial setup
    populateToDoItems();
});
