<?php
session_start();
if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
</head>
<body>
    <h2>Subjects</h2>
    <ul>
        <!-- <li><a href="create_task.php">Add Task</a></li>
        <li><a href="create_quiz.php">Add Quiz</a></li>
        <li><a href="create_exam.php">Add Exam</a></li>
        <li><a href="create_project.php">Add Project</a></li> -->
    </ul>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>