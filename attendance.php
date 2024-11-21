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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .menu {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .menu a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        form {
            margin-bottom: 20px;
        }
        select {
            padding: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h1>Attendance Management</h1>
    
    <form method="get" id="classForm">
        <label for="class_id">Select Class:</label>
        <select id="class_id" name="class_id">
            <option value="">Select a class</option>
            <?php while ($class = $classes_result->fetch_assoc()): ?>
                <option value="<?php echo $class['id']; ?>">
                    <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <div class="menu">
        <ul>
            <a href="#" onclick="navigateTo('take_attendance.php')">Take Attendance</a>
            <br>
            <a href="#" onclick="navigateTo('view_attendance_details.php')">View Attendance Records</a>
        </ul>
        
    </div>

    <a href="dashboard.php">Back to Dashboard</a>

<!-- simple js echo script -->
    <script>
        function navigateTo(page) {
            var classId = document.getElementById('class_id').value;
            if (classId) {
                window.location.href = page + '?class_id=' + classId;
            } else {
                alert('Please select a class first.');
            }
        }
    </script>
</body>
</html>