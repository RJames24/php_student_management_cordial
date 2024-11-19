<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Fetch scholarship students
$stmt = $conn->prepare("SELECT s.name, s.email, sch.scholarship_name FROM students s JOIN scholarships sch ON s.id = sch.student_id");
$stmt->execute();
$scholarships_result = $stmt->get_result();

// Fetch student memberships
$stmt = $conn->prepare("SELECT s.name, s.email, c.club_name FROM students s JOIN club_memberships cm ON s.id = cm.student_id JOIN clubs c ON cm.club_id = c.id");
$stmt->execute();
$memberships_result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Programs</title>
</head>
<body>
    <h2>Student Programs</h2>
    <h3>Scholarship Monitoring</h3>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Scholarship</th>
        </tr>
        <?php while ($scholarship = $scholarships_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($scholarship['name']); ?></td>
                <td><?php echo htmlspecialchars($scholarship['email']); ?></td>
                <td><?php echo htmlspecialchars($scholarship['scholarship_name']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <h3>Student Memberships</h3>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Club</th>
        </tr>
        <?php while ($membership = $memberships_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($membership['name']); ?></td>
                <td><?php echo htmlspecialchars($membership['email']); ?></td>
                <td><?php echo htmlspecialchars($membership['club_name']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>