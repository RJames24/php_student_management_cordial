<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Fetch classes for the current instructor
$stmt = $conn->prepare("SELECT id, class_name, section FROM classes WHERE instructor_id = ?");
$stmt->bind_param("i", $_SESSION['instructor_id']);
$stmt->execute();
$classes_result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
</head>
<body>
    <h2>Student Management</h2>
    <h3>Select a Class</h3>
    <form method="get" action="class_details.php">
        <select name="class_id" required>
            <?php while ($class = $classes_result->fetch_assoc()): ?>
                <option value="<?php echo $class['id']; ?>">
                    <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="submit" value="View Class Details">
    </form>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>