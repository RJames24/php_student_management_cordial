<?php
session_start();
require_once '../../config/db_connect.php';

if (!isset($_SESSION['instructor_id']) || !isset($_GET['class_id'])) {
    header("Location: attendance.php");
    exit();
}

$conn = getDBConnection();
$class_id = $_GET['class_id'];

// Fetch attendance records for the selected class
$stmt = $conn->prepare("SELECT DISTINCT date, is_locked FROM attendance WHERE class_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$attendance_records = $stmt->get_result();

// Handle password confirmation and locking/unlocking attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_password'])) {
    $lock_date = $_POST['lock_date'];
    $current_lock_status = $_POST['current_lock_status'];
    $new_lock_status = $current_lock_status == 1 ? 0 : 1;
    $password = $_POST['password'];

    // Verify instructor's password
    $stmt = $conn->prepare("SELECT password FROM instructors WHERE instructor_id = ?");
    $stmt->bind_param("i", $_SESSION['instructor_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $instructor = $result->fetch_assoc();

    if ($instructor && $password === $instructor['password']) {
        $stmt = $conn->prepare("UPDATE attendance SET is_locked = ? WHERE class_id = ? AND date = ?");
        $stmt->bind_param("iis", $new_lock_status, $class_id, $lock_date);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $success_message = "Attendance lock status updated successfully.";
        } else {
            $error_message = "Failed to update attendance lock status.";
        }
    } else {
        $error_message = "Invalid password. Please try again.";
    }

    // Refresh the attendance records
    $stmt = $conn->prepare("SELECT DISTINCT date, is_locked FROM attendance WHERE class_id = ? ORDER BY date DESC");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $attendance_records = $stmt->get_result();
}

// Fetch attendance details for a specific date
$attendance_details = [];
if (isset($_GET['view_date'])) {
    $view_date = $_GET['view_date'];
    $stmt = $conn->prepare("
        SELECT CONCAT(s.fname, ' ', s.mname, ' ', s.lname) AS full_name, a.status
        FROM attendance a
        JOIN students s ON a.student_id = s.student_id
        WHERE a.class_id = ? AND a.date = ?
        ORDER BY s.lname, s.fname
    ");
    $stmt->bind_param("is", $class_id, $view_date);
    $stmt->execute();
    $details_result = $stmt->get_result();
    while ($row = $details_result->fetch_assoc()) {
        $attendance_details[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance Records</title>
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
    <h1>View Attendance Records</h1>

    <?php if (isset($success_message)): ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <h2>Attendance Records</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
                <th>View Details</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($record = $attendance_records->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $record['date']; ?></td>
                    <td><?php echo $record['is_locked'] ? 'Locked' : 'Unlocked'; ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="lock_date" value="<?php echo $record['date']; ?>">
                            <input type="hidden" name="current_lock_status" value="<?php echo $record['is_locked']; ?>">
                            <input type="submit" name="toggle_lock" value="<?php echo $record['is_locked'] ? 'Unlock' : 'Lock'; ?>">
                        </form>
                    </td>
                    <td>
                        <a href="?class_id=<?php echo $class_id; ?>&view_date=<?php echo $record['date']; ?>">View Details</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php if (isset($_POST['toggle_lock'])): ?>
        <h2>Confirm Password</h2>
        <form method="post" action="">
            <input type="hidden" name="lock_date" value="<?php echo $_POST['lock_date']; ?>">
            <input type="hidden" name="current_lock_status" value="<?php echo $_POST['current_lock_status']; ?>">
            <label for="password">Enter your password to confirm:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" name="confirm_password" value="Confirm">
        </form>
    <?php endif; ?>

    <?php if (isset($_GET['view_date'])): ?>
        <h2>Attendance Details for <?php echo $_GET['view_date']; ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_details as $detail): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detail['full_name']); ?></td>
                        <td><?php echo ucfirst($detail['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="../submenus/attendance.php">Back to Attendance Management</a>
</body>
</html>