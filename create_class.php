<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = $_POST['class_name'];
    $section = $_POST['section'];
    $instructor_id = $_SESSION['instructor_id'];

    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO classes (instructor_id, class_name, section) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $instructor_id, $class_name, $section);

    if ($stmt->execute()) {
        $success = "Class created successfully";
    } else {
        $error = "Error creating class: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Class</title>
</head>
<body>
    <h2>Create Class</h2>
    <?php
    if (isset($success)) echo "<p style='color: green;'>$success</p>";
    if (isset($error)) echo "<p style='color: red;'>$error</p>";
    ?>
    <form method="post">
        <label for="class_name">Class Name:</label>
        <input type="text" id="class_name" name="class_name" required><br><br>
        <label for="section">Section:</label>
        <input type="text" id="section" name="section" required><br><br>
        <input type="submit" value="Create Class">
    </form>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>