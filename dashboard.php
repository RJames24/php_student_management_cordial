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
    <title>Dashboard/port.8080</title>
    <link rel="shortcut icon" href="logo.png" type="image/x-icon">
</head>
<body>
    <h2>Welcome, Instructor!</h2>
    <ul>
    <li><a href="create_class.php">Create a New Class</a></li>
        <li><a href="add_student.php">Add Student to Class</a></li>
        <li><a href="grade_students.php">Grade Students</a></li>
        <li><a href="subjects.php">Subjects</a></li>
        <li><a href="student_management.php">Student Management</a></li>
        <li><a href="student_programs.php">Student Programs</a></li>
        <li><a href="view_class.php">Class List</a></li>
        <li><a href="grades.php">Grades</a></li>
        <li><a href="settings.php">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</body>
</html>