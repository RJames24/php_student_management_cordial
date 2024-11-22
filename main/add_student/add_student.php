<?php
session_start();
require_once '../../config/db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: ../account_pages/login.php");
    exit();
}

$conn = getDBConnection();

// Fetch classes for the current instructor
// $stmt = $conn->prepare("SELECT class_id, course_name, section FROM classes WHERE instructor_id = ?");
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

// Fetch all students
$students_result = $conn->query("SELECT student_id, CONCAT(fname, ' ', lname, ' (', email, ')') AS student_info FROM students");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_id = $_POST['class_id'];
    $student_id = $_POST['student_id'];

    // Add the student to the class
    $stmt = $conn->prepare("INSERT IGNORE INTO class_students (class_id, student_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $class_id, $student_id);

    if ($stmt->execute()) {
        $success = "Student added successfully";
    } else {
        $error = "Error adding student: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student to Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-centers">
            <h1 class="text-2xl font-bold">Instructor Dashboard</h1>
            <a href="../dashboard.php" class="text-white bg-blue-500 px-4 py-2 rounded hover:bg-blue-700">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mx-auto mt-8 p-8 bg-white rounded-lg shadow-md">
        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Add Student to Class</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success; ?>
            </div>
        <?php endif; ?> 

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
            <div class="form-group">
                <label for="class_id" class="block text-sm font-medium text-gray-700">Select Class:</label>
                <select id="class_id" name="class_id" class="form-select" onchange="this.form.submit()">
                <option value="" disabled selected>Select a class</option>
                    <?php while ($class = $classes_result->fetch_assoc()): ?>
                <option value="<?php echo $class['class_id']; ?>" <?php echo (isset($_GET['class_id']) && $_GET['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($class['full_class_name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
            </div>

            <div class="form-group">
                <label for="student_id" class="block text-sm font-medium text-gray-700">Select Student:</label>
                <select id="student_id" name="student_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <?php while ($student = $students_result->fetch_assoc()): ?>
                        <option value="<?php echo $student['student_id']; ?>">
                            <?php echo htmlspecialchars($student['student_info']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full transition duration-300 ease-in-out transform hover:scale-105">
                    Add Student
                </button>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('#class_id, #student_id').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        });
    </script>
</body>
</html>