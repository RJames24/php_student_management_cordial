<?php
session_start();
require_once 'config/db_connect.php';

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

// Fetch students for a specific class
$students_result = null;
$total_students = 0;
$class_id = null;
$current_date = date('Y-m-d');

if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];
    $stmt = $conn->prepare("SELECT s.id, s.name, s.email, s.absences, s.lates FROM students s JOIN class_students cs ON s.id = cs.student_id WHERE cs.class_id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $students_result = $stmt->get_result();

    // Total students
    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total_students FROM class_students WHERE class_id = ?");
    $count_stmt->bind_param("i", $class_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_students = $count_result->fetch_assoc()['total_students'];
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    foreach ($_POST['attendance'] as $student_id => $status) {
        $stmt = $conn->prepare("INSERT INTO attendance (class_id, student_id, date, status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
        $stmt->bind_param("iisss", $class_id, $student_id, $current_date, $status, $status);
        $stmt->execute();

        // Update student's absence and late counters
        if ($status === 'absent') {
            $conn->query("UPDATE students SET absences = absences + 1 WHERE id = $student_id");
        } elseif ($status === 'late') {
            $conn->query("UPDATE students SET lates = lates + 1 WHERE id = $student_id");
        }
    }
}

// Fetch today's attendance
$attendance = [];
if ($class_id) {
    $stmt = $conn->prepare("SELECT student_id, status FROM attendance WHERE class_id = ? AND date = ?");
    $stmt->bind_param("is", $class_id, $current_date);
    $stmt->execute();
    $attendance_result = $stmt->get_result();
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance[$row['student_id']] = $row['status'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class List and Attendance</title>
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
    <h2>Class List and Attendance</h2>
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
        <h3>Students in the Selected Class</h3>
        <p>Total Students: <strong><?php echo $total_students; ?></strong></p>
        <p>Date: <?php echo $current_date; ?></p>
        <form method="post">
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Absences</th>
                        <th>Lates</th>
                        <th>Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $students_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo $student['absences']; ?></td>
                            <td><?php echo $student['lates']; ?></td>
                            <td>
                                <label><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" <?php echo (isset($attendance[$student['id']]) && $attendance[$student['id']] == 'present') ? 'checked' : ''; ?>> Present</label>
                                <label><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="late" <?php echo (isset($attendance[$student['id']]) && $attendance[$student['id']] == 'late') ? 'checked' : ''; ?>> Late</label>
                                <label><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent" <?php echo (isset($attendance[$student['id']]) && $attendance[$student['id']] == 'absent') ? 'checked' : ''; ?>> Absent</label>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <br>
            <input type="submit" name="submit_attendance" value="Submit Attendance">
        </form>
    <?php elseif (isset($_GET['class_id'])): ?>
        <p>No students found in this class.</p>
        <p>Total Students: <strong>0</strong></p>
    <?php endif; ?>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>