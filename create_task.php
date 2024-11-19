<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = $_POST['subject_id'];
    $task_description = $_POST['task_description'];
    $due_date = $_POST['due_date'];

    $stmt = $conn->prepare("INSERT INTO tasks (subject_id, description, due_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $subject_id, $task_description, $due_date);

    if ($stmt->execute()) {
        $success = "Task added successfully";
    } else {
        $error = "Error adding task: " . $conn->error;
    }
}

// Fetch subjects for the current instructor
$stmt = $conn->prepare("SELECT id, subject_name FROM subjects WHERE instructor_id = ?");
$stmt->bind_param("i", $_SESSION['instructor_id']);
$stmt->execute();
$subjects_result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects</title>
</head>
<body>
    <h2>Subjects</h2>
    <?php
    if (isset($success)) echo "<p style='color: green;'>$success</p>";
    if (isset($error)) echo "<p style='color: red;'>$error</p>";
    ?>
    <h3>Add Task</h3>
    <form method="post">
        <label for="subject_id">Select Subject:</label>
        <select id="subject_id" name="subject_id" required>
            <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                <option value="<?php echo $subject['id']; ?>">
                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>
        <label for="task_description">Task Description:</label>
        <textarea id="task_description" name="task_description" required></textarea><br><br>
        <label for="due_date">Due Date:</label>
        <input type="date" id="due_date" name="due_date" required><br><br>
        <input type="submit" value="Add Task">
    </form>
    <br>
    <a href="subjects.php">Back to Subjects</a>
</body>
</html>