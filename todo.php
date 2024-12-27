<?php
// Database connection
$host = 'localhost';
$dbname = 'todo_app';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        // Add a new task
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        if (!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO tasks (title, description) VALUES (:title, :description)");
            $stmt->execute([':title' => $title, ':description' => $description]);
        }
    } elseif ($action === 'delete') {
        // Delete a task
        $id = (int) $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } elseif ($action === 'update') {
        // Update a task
        $id = (int) $_POST['id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        if (!empty($title)) {
            $stmt = $conn->prepare("UPDATE tasks SET title = :title, description = :description WHERE id = :id");
            $stmt->execute([':title' => $title, ':description' => $description, ':id' => $id]);
        }
    }
}

// Fetch all tasks
$stmt = $conn->prepare("SELECT * FROM tasks ORDER BY created_at DESC");
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List App</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        form { margin-bottom: 20px; }
        input, textarea { padding: 10px; margin-bottom: 10px; width: 100%; }
        button { padding: 10px; cursor: pointer; }
        .actions { display: flex; gap: 10px; }
    </style>
</head>
<body>
    <h1>To-Do List</h1>

    <!-- Add Task Form -->
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <input type="text" name="title" placeholder="Task Title" required>
        <textarea name="description" placeholder="Task Description"></textarea>
        <button type="submit">Add Task</button>
    </form>

    <!-- Task List Table -->
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <?php if (isset($_GET['edit']) && $_GET['edit'] == $task['id']): ?>
                    <!-- Edit Task Form -->
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= $task['id']; ?>">
                            <td>
                                <input type="text" name="title" value="<?= htmlspecialchars($task['title']); ?>" required>
                            </td>
                            <td>
                                <textarea name="description"><?= htmlspecialchars($task['description']); ?></textarea>
                            </td>
                            <td>
                                <button type="submit">Save</button>
                                <a href="todo.php">Cancel</a>
                            </td>
                        </form>
                    </tr>
                <?php else: ?>
                    <!-- Display Task -->
                    <tr>
                        <td><?= htmlspecialchars($task['title']); ?></td>
                        <td><?= htmlspecialchars($task['description']); ?></td>
                        <td class="actions">
                            <a href="?edit=<?= $task['id']; ?>">Edit</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $task['id']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
