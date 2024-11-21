<?php
session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['instructor_id']) || !isset($_GET['class_id'])) {
    header("Location: attendance.php");
    exit();
}

$conn = getDBConnection();
$class_id = $_GET['class_id'];
$current_date = date('Y-m-d');

// Fetch students for the selected class
$stmt = $conn->prepare("SELECT s.id, s.name, s.email, s.absences, s.lates FROM students s JOIN class_students cs ON s.id = cs.student_id WHERE cs.class_id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$students_result = $stmt->get_result();

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    $stmt = $conn->prepare("SELECT is_locked FROM attendance WHERE class_id = ? AND date = ? LIMIT 1");
    $stmt->bind_param("is", $class_id, $current_date);
    $stmt->execute();
    $lock_result = $stmt->get_result();
    $is_locked = $lock_result->num_rows > 0 ? $lock_result->fetch_assoc()['is_locked'] : 0;

    if (!$is_locked) {
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
        $success_message = "Attendance submitted successfully.";
    } else {
        $error_message = "Attendance for this date is locked and cannot be modified.";
    }
}

// Fetch today's attendance
$attendance = [];
$stmt = $conn->prepare("SELECT student_id, status FROM attendance WHERE class_id = ? AND date = ?");
$stmt->bind_param("is", $class_id, $current_date);
$stmt->execute();
$attendance_result = $stmt->get_result();
while ($row = $attendance_result->fetch_assoc()) {
    $attendance[$row['student_id']] = $row['status'];
}

// Check if the current date's attendance is locked
$stmt = $conn->prepare("SELECT is_locked FROM attendance WHERE class_id = ? AND date = ? LIMIT 1");
$stmt->bind_param("is", $class_id, $current_date);
$stmt->execute();
$lock_result = $stmt->get_result();
$is_locked = $lock_result->num_rows > 0 ? $lock_result->fetch_assoc()['is_locked'] : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <h1>Take Attendance</h1>
    
    <?php if (isset($success_message)): ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <h2>Date: <?php echo $current_date; ?></h2>

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
                            <label><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" <?php echo (isset($attendance[$student['id']]) && $attendance[$student['id']] == 'present') ? 'checked' : ''; ?> <?php echo $is_locked ? 'disabled' : ''; ?>> Present</label>
                            <label><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="late" <?php echo (isset($attendance[$student['id']]) && $attendance[$student['id']] == 'late') ? 'checked' : ''; ?> <?php echo $is_locked ? 'disabled' : ''; ?>> Late</label>
                            <label><input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent" <?php echo (isset($attendance[$student['id']]) && $attendance[$student['id']] == 'absent') ? 'checked' : ''; ?> <?php echo $is_locked ? 'disabled' : ''; ?>> Absent</label>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <input type="submit" name="submit_attendance" value="Submit Attendance" <?php echo $is_locked ? 'disabled' : ''; ?>>
    </form>

    <a href="attendance.php">Back to Attendance Management</a>
</body>
</html>