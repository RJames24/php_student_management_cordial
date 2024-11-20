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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Instructor Dashboard</h1>
            <a href="dashboard.php" class="hover:underline">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mx-auto mt-8 p-8 bg-white rounded-lg shadow-md">
        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Student Grades</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success mb-4" role="alert">
                Grades updated successfully
            </div>
        <?php endif; ?>

        <form method="get" class="mb-6">
            <div class="form-group">
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Select Class:</label>
                <select id="class_id" name="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Select a class</option>
                    <?php while ($class = $classes_result->fetch_assoc()): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>

        <?php if (isset($students_result) && $students_result->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="table table-striped table-hover">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2">Student Name</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Prelim</th>
                            <th class="px-4 py-2">Midterm</th>
                            <th class="px-4 py-2">Finals</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $students_result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($student['name']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($student['email']); ?></td>
                                <td class="px-4 py-2"><?php echo $student['prelim'] !== null ? htmlspecialchars($student['prelim']) : 'N/A'; ?></td>
                                <td class="px-4 py-2"><?php echo $student['midterm'] !== null ? htmlspecialchars($student['midterm']) : 'N/A'; ?></td>
                                <td class="px-4 py-2"><?php echo $student['finals'] !== null ? htmlspecialchars($student['finals']) : 'N/A'; ?></td>
                                <td class="px-4 py-2">
                                    <form method="post" class="flex items-center space-x-2">
                                        <input type="hidden" name="update_grades" value="1">
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                        <input type="number" name="prelim" value="<?php echo $student['prelim']; ?>" min="0" max="100" step="0.01" class="form-control w-20">
                                        <input type="number" name="midterm" value="<?php echo $student['midterm']; ?>" min="0" max="100" step="0.01" class="form-control w-20">
                                        <input type="number" name="finals" value="<?php echo $student['finals']; ?>" min="0" max="100" step="0.01" class="form-control w-20">
                                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (isset($_GET['class_id'])): ?>
            <p class="text-center text-gray-600">No students found in this class.</p>
        <?php endif; ?>
    </div>

    <script>
        $(document).ready(function() {
            $('#class_id').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        });
    </script>
</body>
</html>