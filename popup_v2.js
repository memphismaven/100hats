// app.js
document.addEventListener("DOMContentLoaded", function () {
    const addItemLink = document.getElementById("add-item-link");
    const popupContainer = document.getElementById("popup-container");
    const saveTaskButton = document.getElementById("save-task");
    const cancelTaskButton = document.getElementById("cancel-task");

    addItemLink.addEventListener("click", function () {
        // Show the popup when the "Add Item" link is clicked
        popupContainer.style.display = "block";
    });

    saveTaskButton.addEventListener("click", function () {
        // Handle saving the task (you can implement this part)
        // For demonstration purposes, we'll just close the popup
        popupContainer.style.display = "none";
    });

    cancelTaskButton.addEventListener("click", function () {
        // Close the popup without saving
        popupContainer.style.display = "none";
    });
});
