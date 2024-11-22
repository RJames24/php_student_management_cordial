<?php
session_start();
require_once '../../config/db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: ../../account_pages/login.php");
    exit();
}

$conn = getDBConnection();

// Fetch classes for the current instructor
$stmt = $conn->prepare("SELECT class_id, CONCAT(course_name, ' ', year_level, '-', section) AS full_class_name, course_name, year_level, section FROM classes WHERE instructor_id = ?");
$stmt->bind_param("i", $_SESSION['instructor_id']);
$stmt->execute();
$classes_result = $stmt->get_result();

// Fetch students for a specific class
$students_result = null;
$total_students = 0;
if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];
    $stmt = $conn->prepare("SELECT s.student_id, CONCAT(s.fname, ' ', s.mname, ' ', s.lname) AS full_name, s.email FROM students s JOIN class_students cs ON s.student_id = cs.student_id WHERE cs.class_id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $students_result = $stmt->get_result();

    //total students
    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total_students FROM class_students WHERE class_id = ?");
    $count_stmt->bind_param("i", $class_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_students = $count_result->fetch_assoc()['total_students'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class List</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Class List</h2>
    <form method="get">
        <label for="class_id">Select Class:</label>
        <select id="class_id" name="class_id" onchange="this.form.submit()">
            <option value="">Select a class</option>
            <?php while ($class = $classes_result->fetch_assoc()): ?>
                <option value="<?php echo $class['class_id']; ?>" <?php echo (isset($_GET['class_id']) && $_GET['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($class['full_class_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if (isset($students_result) && $students_result->num_rows > 0): ?>
        <h3>Students in the Selected Class</h3>
        <p>Total Students: <strong><?php echo $total_students; ?></strong></p>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $students_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php elseif (isset($_GET['class_id'])): ?>
        <p>No students found in this class.</p>
        <p>Total Students: <strong>0</strong></p>
    <?php endif; ?>
    <br>
    <a href="../submenus/student_management.php">Back to Student Management</a>
</body>
</html>