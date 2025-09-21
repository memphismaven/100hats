<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Todoist-style Task Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <h2>Projects</h2>
        <ul>
            <?php
            $res = $conn->query("SELECT IID, ITM FROM tbl_ITM WHERE ITID = 2 ORDER BY ITM");
            while ($proj = $res->fetch_assoc()) {
                echo "<li><a href='?project={$proj['IID']}'>{$proj['ITM']}</a></li>";
            }
            ?>
        </ul>
        <form method="POST" action="add_project.php" class="add-form">
            <input type="text" name="project_name" placeholder="New project..." required>
            <button type="submit">+</button>
        </form>
    </aside>

    <main class="main-content">
        <h1>Tasks</h1>
        <form method="POST" action="add_task.php" class="add-form">
            <input type="hidden" name="PIID" value="<?php echo $_GET['project'] ?? 'NULL'; ?>">
            <input type="text" name="task_name" placeholder="New task..." required>
            <button type="submit">Add</button>
        </form>
        <ul class="tasks">
            <?php
            $project = $_GET['project'] ?? 'NULL';
            $filter = is_numeric($project) ? "PIID = $project" : "PIID IS NULL";
            $result = $conn->query("SELECT * FROM tbl_ITM WHERE ITID = 4 AND $filter AND (Completed IS NULL OR Completed = '') ORDER BY IID DESC");
            while ($row = $result->fetch_assoc()) {
                echo "<li class='task-item'>
                    <form method='POST' action='toggle_complete.php'>
                        <input type='hidden' name='IID' value='{$row['IID']}'>
                        <button type='submit' class='circle'></button>
                    </form>
                    <span><a href='edit_task.php?IID={$row['IID']}'>{$row['ITM']}</a></span>
                </li>";
            }
            $conn->close();
            ?>
        </ul>
    </main>
</div>
</body>
</html>
