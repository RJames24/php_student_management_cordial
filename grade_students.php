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
    $exam_type = $_POST['exam_type'];
    $grades = $_POST['grades'];

    $stmt = $conn->prepare("INSERT INTO grades (class_id, student_id, exam_type, grade) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE grade = ?");

    foreach ($grades as $student_id => $grade) {
        $stmt->bind_param("iisdd", $class_id, $student_id, $exam_type, $grade, $grade);
        $stmt->execute();
    }

    $success = "Grades submitted successfully";
}

// Fetch students for a specific class
if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];
    $stmt = $conn->prepare("SELECT s.id, s.name, s.email FROM students s JOIN class_students cs ON s.id = cs.student_id WHERE cs.class_id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $students_result = $stmt->get_result();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Students</title>
</head>
<body>
    <h2>Grade Students</h2>
    <?php
    if (isset($success)) echo "<p style='color: green;'>$success</p>";
    if (isset($error)) echo "<p style='color: red;'>$error</p>";
    ?>
    <form method="get">
        <label for="class_id">Select Class:</label>
        <select id="class_id" name="class_id" onchange="this.form.submit()">
            <option value="">Select a class</option>
            <?php while ($class = $classes_result->fetch_assoc()): ?>
                <option value="<?php echo $class['id']; ?>" <?php echo (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if (isset($students_result) && $students_result->num_rows > 0): ?>
        <form method="post">
            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
            <label for="exam_type">Exam Type:</label>
            <select id="exam_type" name="exam_type" required>
                <option value="prelim">Prelim</option>
                <option value="midterm">Midterm</option>
                <option value="finals">Finals</option>
            </select><br><br>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Grade</th>
                </tr>
                <?php while ($student = $students_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td>
                            <input type="number" name="grades[<?php echo $student['id']; ?>]" min="0" max="100" step="0.01" required>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <br>
            <input type="submit" value="Submit Grades">
        </form>
    <?php elseif (isset($_GET['class_id'])): ?>
        <p>No students found in this class.</p>
    <?php endif; ?>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>