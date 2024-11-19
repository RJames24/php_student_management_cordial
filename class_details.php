<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['instructor_id']) || !isset($_GET['class_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$class_id = $_GET['class_id'];

// Fetch class details
$stmt = $conn->prepare("SELECT class_name, section FROM classes WHERE id = ? AND instructor_id = ?");
$stmt->bind_param("ii", $class_id, $_SESSION['instructor_id']);
$stmt->execute();
$class_result = $stmt->get_result()->fetch_assoc();

// Fetch students in the class
$stmt = $conn->prepare("SELECT s.id, s.name, s.email FROM students s JOIN class_students cs ON s.id = cs.student_id WHERE cs.class_id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$students_result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Details</title>
</head>
<body>
    <h2>Class Details: <?php echo htmlspecialchars($class_result['class_name'] . ' - ' . $class_result['section']); ?></h2>
    <h3>Class List</h3>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Email</th>
        </tr>
        <?php while ($student = $students_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo htmlspecialchars($student['email']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="student_management.php">Back to Student Management</a>
</body>
</html>