<?php
session_start();
require_once '../../config/db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: ../account_pages/login.php");
    exit();
}

$conn = getDBConnection();

// // Fetch classes for the current instructor
// $stmt = $conn->prepare("SELECT class_id, class_name, section FROM classes WHERE instructor_id = ?");
// $stmt->bind_param("i", $_SESSION['instructor_id']);
// $stmt->execute();
// $classes_result = $stmt->get_result();

// Fetch classes for the current instructor
$stmt = $conn->prepare("
    SELECT 
        class_id, 
        CONCAT(course_name, ' ', year_level, '-', section) AS full_class_name, 
        course_name,
        year_level,
        section 
    FROM classes 
    WHERE instructor_id = ?
");
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

    $average = ($prelim + $midterm + $finals) / 3;

    // Updated to use the new grades table structure
    $stmt = $conn->prepare("INSERT INTO grades (class_id, student_id, exam_type, grade) 
                            VALUES (?, ?, 'prelim', ?), (?, ?, 'midterm', ?), (?, ?, 'finals', ?)
                            ON DUPLICATE KEY UPDATE grade = VALUES(grade)");
    $stmt->bind_param("iidiidiid", $class_id, $student_id, $prelim, $class_id, $student_id, $midterm, $class_id, $student_id, $finals);
    $stmt->execute();

    // Updated to use the new averages table structure
    $stmt = $conn->prepare("INSERT INTO averages (class_id, student_id, average_grade) 
                            VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE average_grade = ?");
    $stmt->bind_param("iidd", $class_id, $student_id, $average, $average);
    $stmt->execute();

    $success = "Grades updated successfully";

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?class_id=" . $class_id . "&success=1");
    exit();
}

// Fetch students and their grades for a specific class
$students_result = null;
if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];
    // Updated to use the new table structure and column names
    $stmt = $conn->prepare("SELECT s.student_id, s.fname, s.lname, s.mname, s.email, 
                            MAX(CASE WHEN g.exam_type = 'prelim' THEN g.grade END) as prelim,
                            MAX(CASE WHEN g.exam_type = 'midterm' THEN g.grade END) as midterm,
                            MAX(CASE WHEN g.exam_type = 'finals' THEN g.grade END) as finals,
                            a.average_grade
                            FROM students s 
                            JOIN class_students cs ON s.student_id = cs.student_id 
                            LEFT JOIN grades g ON s.student_id = g.student_id AND g.class_id = cs.class_id
                            LEFT JOIN averages a ON s.student_id = a.student_id AND cs.class_id = a.class_id
                            WHERE cs.class_id = ?
                            GROUP BY s.student_id, s.fname, s.lname, s.mname, s.email, a.average_grade");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $students_result = $stmt->get_result();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head content remains unchanged -->
</head>
<body class="bg-light text-dark">
    <!-- Navigation bar remains unchanged -->

    <div class="container mt-5">
    <a href="../dashboard.php" class="text-blue bg-blue-500 px-4 py-2 rounded hover:bg-blue-700">Back to Dashboard</a>
        <h2 class="text-center mb-4">Student Grades</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success mb-4" role="alert">
                Grades updated successfully
            </div>
        <?php endif; ?>

        <form method="get" class="mb-5">
            <label for="class_id" class="form-label">Select Class:</label>
            <select id="class_id" name="class_id" class="form-select" onchange="this.form.submit()">
                <option value="" disabled selected>Select a class</option>
                    <?php while ($class = $classes_result->fetch_assoc()): ?>
                    <option value="<?php echo $class['class_id']; ?>" <?php echo (isset($_GET['class_id']) && $_GET['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($class['full_class_name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if (isset($students_result) && $students_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Prelim</th>
                            <th>Midterm</th>
                            <th>Finals</th>
                            <th>Average</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $students_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['fname'] . ' ' . $student['mname'] . ' ' . $student['lname']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo $student['prelim'] !== null ? htmlspecialchars($student['prelim']) : 'N/A'; ?></td>
                                <td><?php echo $student['midterm'] !== null ? htmlspecialchars($student['midterm']) : 'N/A'; ?></td>
                                <td><?php echo $student['finals'] !== null ? htmlspecialchars($student['finals']) : 'N/A'; ?></td>
                                <td><?php echo $student['average_grade'] !== null ? htmlspecialchars(number_format($student['average_grade'], 2)) : 'N/A'; ?></td>
                                <td>
                                    <form method="post" class="d-flex justify-content-center">
                                        <input type="hidden" name="update_grades" value="1">
                                        <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                        <input type="number" name="prelim" value="<?php echo $student['prelim']; ?>" min="0" max="100" step="0.01" class="form-control form-control-sm me-1" placeholder="Prelim">
                                        <input type="number" name="midterm" value="<?php echo $student['midterm']; ?>" min="0" max="100" step="0.01" class="form-control form-control-sm me-1" placeholder="Midterm">
                                        <input type="number" name="finals" value="<?php echo $student['finals']; ?>" min="0" max="100" step="0.01" class="form-control form-control-sm me-1" placeholder="Finals">
                                        <button type="submit" class="btn btn-sm btn-success">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (isset($_GET['class_id'])): ?>
            <p class="text-center">No students found in this class.</p>
        <?php endif; ?>
    </div>

    <script>
        // JavaScript remains unchanged
    </script>
</body>
</html>