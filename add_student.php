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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_id = $_POST['class_id'];
    $student_name = $_POST['student_name'];
    $student_email = $_POST['student_email'];

    // First, insert or get the student
    $stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
    $stmt->bind_param("ss", $student_name, $student_email);
    $stmt->execute();
    $student_id = $stmt->insert_id;

    // Then, add the student to the class
    $stmt = $conn->prepare("INSERT IGNORE INTO class_students (class_id, student_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $class_id, $student_id);

    if ($stmt->execute()) {
        $success = "Student added successfully";
    } else {
        $error = "Error adding student: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student to Class</title>
</head>
<body>
    <h2>Add Student to Class</h2>
    <?php
    if (isset($success)) echo "<p style='color: green;'>$success</p>";
    if (isset($error)) echo "<p style='color: red;'>$error</p>";
    ?>
    <form method="post">
        <label for="class_id">Select Class:</label>
        <select id="class_id" name="class_id" required>
            <?php while ($class = $classes_result->fetch_assoc()): ?>
                <option value="<?php echo $class['id']; ?>">
                    <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>
        <label for="student_name">Student Name:</label>
        <input type="text" id="student_name" name="student_name" required><br><br>
        <label for="student_email">Student Email:</label>
        <input type="email" id="student_email" name="student_email" required><br><br>
        <input type="submit" value="Add Student">
    </form>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
