<?php
session_start();
if (!isset($_SESSION['instructor_id'])) {
    header("Location: ../../account_pages/login.php");
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
    <h2>Student Programs</h2>
    <ul>
        <li><a href="scholar_monitoring.php">Scholar Monitoring</a></li>
        <li><a href="student_membership.php">Student Membership</a></li>

    </ul>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>