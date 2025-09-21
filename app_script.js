document.addEventListener("DOMContentLoaded", function () {
    // Sample to-do items
    const todoItems = ["Task 1", "Task 2", "Task 3", "Task 4", "Task 5"];

    // Populate to-do items in the center pane
    const todoList = document.getElementById("today-list");

    todoItems.forEach(function (item) {
        const listItem = document.createElement("li");
        listItem.classList.add("todo-item");

        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";

        const label = document.createElement("label");
        label.textContent = item;

        const hr = document.createElement("hr");

        listItem.appendChild(checkbox);
        listItem.appendChild(label);
        listItem.appendChild(hr);

        label.addEventListener("click", function () {
            // Open a popup with the to-do item name
            alert(item);
        });

        todoList.appendChild(listItem);
    });
});

function changeListHead(newText) {
    const listHead = document.getElementById("list_head");
    listHead.innerText = newText;
  }

 