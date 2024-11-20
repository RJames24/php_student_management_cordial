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

// Handle grade updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_grades'])) {
    $student_id = $_POST['student_id'];
    $class_id = $_POST['class_id'];
    $prelim = $_POST['prelim'];
    $midterm = $_POST['midterm'];
    $finals = $_POST['finals'];

    $stmt = $conn->prepare("INSERT INTO grades (class_id, student_id, exam_type, grade) 
                            VALUES (?, ?, 'prelim', ?), (?, ?, 'midterm', ?), (?, ?, 'finals', ?)
                            ON DUPLICATE KEY UPDATE grade = VALUES(grade)");
    $stmt->bind_param("iidiidiid", $class_id, $student_id, $prelim, $class_id, $student_id, $midterm, $class_id, $student_id, $finals);
    $stmt->execute();

    $success = "Grades updated successfully";
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?class_id=" . $class_id);
    exit();
}

// Fetch students and their grades for a specific class
$students_result = null;
if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];
    $stmt = $conn->prepare("SELECT s.id, s.name, s.email, 
                            MAX(CASE WHEN g.exam_type = 'prelim' THEN g.grade END) as prelim,
                            MAX(CASE WHEN g.exam_type = 'midterm' THEN g.grade END) as midterm,
                            MAX(CASE WHEN g.exam_type = 'finals' THEN g.grade END) as finals
                            FROM students s 
                            JOIN class_students cs ON s.id = cs.student_id 
                            LEFT JOIN grades g ON s.id = g.student_id AND g.class_id = cs.class_id
                            WHERE cs.class_id = ?
                            GROUP BY s.id, s.name, s.email");
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
    <title>Student Grades</title>
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
        .edit-form {
            display: inline;
        }
        .edit-form input[type="number"] {
            width: 60px;
        }
    </style>
</head>
<body>
    <h2>Student Grades</h2>
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
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Prelim</th>
                    <th>Midterm</th>
                    <th>Finals</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $students_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo $student['prelim'] !== null ? htmlspecialchars($student['prelim']) : 'N/A'; ?></td>
                        <td><?php echo $student['midterm'] !== null ? htmlspecialchars($student['midterm']) : 'N/A'; ?></td>
                        <td><?php echo $student['finals'] !== null ? htmlspecialchars($student['finals']) : 'N/A'; ?></td>
                        <td>
                            <form method="post" class="edit-form">
                                <input type="hidden" name="update_grades" value="1">
                                <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                <input type="number" name="prelim" value="<?php echo $student['prelim']; ?>" min="0" max="100" step="0.01">
                                <input type="number" name="midterm" value="<?php echo $student['midterm']; ?>" min="0" max="100" step="0.01">
                                <input type="number" name="finals" value="<?php echo $student['finals']; ?>" min="0" max="100" step="0.01">
                                <input type="submit" value="Update">
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php elseif (isset($_GET['class_id'])): ?>
        <p>No students found in this class.</p>
    <?php endif; ?>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>