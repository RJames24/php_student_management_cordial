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
    <h2>Student Management</h2>
    <ul>
        <li><a href="view_class.php">Class List</a></li>
        <li><a href="">View Groups</a></li>
        <li><a href="">Recitation</a></li>
        <li><a href="">Student Performance</a></li>
    </ul>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>