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

    $average = ($prelim + $midterm + $finals) / 3;

    $stmt = $conn->prepare("INSERT INTO grades (class_id, student_id, exam_type, grade) 
                            VALUES (?, ?, 'prelim', ?), (?, ?, 'midterm', ?), (?, ?, 'finals', ?)
                            ON DUPLICATE KEY UPDATE grade = VALUES(grade)");
    $stmt->bind_param("iidiidiid", $class_id, $student_id, $prelim, $class_id, $student_id, $midterm, $class_id, $student_id, $finals);
    $stmt->execute();

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
    $stmt = $conn->prepare("SELECT s.id, s.name, s.email, 
                            MAX(CASE WHEN g.exam_type = 'prelim' THEN g.grade END) as prelim,
                            MAX(CASE WHEN g.exam_type = 'midterm' THEN g.grade END) as midterm,
                            MAX(CASE WHEN g.exam_type = 'finals' THEN g.grade END) as finals,
                            a.average_grade
                            FROM students s 
                            JOIN class_students cs ON s.id = cs.student_id 
                            LEFT JOIN grades g ON s.id = g.student_id AND g.class_id = cs.class_id
                            LEFT JOIN averages a ON s.id = a.student_id AND cs.class_id = a.class_id
                            WHERE cs.class_id = ?
                            GROUP BY s.id, s.name, s.email, a.average_grade");
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
    .form-select, .select2-container .select2-selection--single {
        border: 0.7px solid #000;
        padding: 8px 12px;  /* Adjust padding for better alignment */
        font-size: 1rem;
        border-radius: 8px;
        width: 100%;
        text-align: left;  /* Ensures text is aligned to the left */
        display: flex;
        align-items: center;  /* Vertically aligns text */
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-select:focus, .select2-container .select2-selection--single:focus {
        border-color: #0056b3;
        box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
    }

    .select2-container .select2-selection__rendered {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .select2-container .select2-results__option:hover {
        background-color: #e9ecef;
        color: #000;
    }
</style>

</head>
<body class="bg-light text-dark">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Instructor Dashboard</a>
            <a class="btn btn-light btn-sm" href="dashboard.php">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mt-5">
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
                    <option value="<?php echo $class['id']; ?>" <?php echo (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section']); ?>
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
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo $student['prelim'] !== null ? htmlspecialchars($student['prelim']) : 'N/A'; ?></td>
                                <td><?php echo $student['midterm'] !== null ? htmlspecialchars($student['midterm']) : 'N/A'; ?></td>
                                <td><?php echo $student['finals'] !== null ? htmlspecialchars($student['finals']) : 'N/A'; ?></td>
                                <td><?php echo $student['average_grade'] !== null ? htmlspecialchars(number_format($student['average_grade'], 2)) : 'N/A'; ?></td>
                                <td>
                                    <form method="post" class="d-flex justify-content-center">
                                        <input type="hidden" name="update_grades" value="1">
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
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
        $(document).ready(function() {
            $('#class_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: "Search for a class", // Placeholder text for search bar
                allowClear: true // Allows clearing the selection
            });
        });
    </script>
</body>
</html>
