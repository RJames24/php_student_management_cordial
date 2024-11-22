<?php
session_start();
require_once '../../config/db_connect.php';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch data from the form
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $mname = !empty($_POST['mname']) ? $_POST['mname'] : null; // Middle name is optional
    $gender = $_POST['gender'];
    $phone_number = !empty($_POST['phone_number']) ? $_POST['phone_number'] : null; // Optional
    $address = !empty($_POST['address']) ? $_POST['address'] : null; // Optional
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $student_number = generateStudentNumber(); // You need to implement this function
    $course = !empty($_POST['course']) ? $_POST['course'] : null; // Optional
    $year_level = !empty($_POST['year_level']) ? $_POST['year_level'] : null; // Optional
    $section = !empty($_POST['section']) ? $_POST['section'] : null; // Optional

    // Insert the data into the `students` table
    $sql = "INSERT INTO students (fname, lname, mname, gender, phone_number, address, email, password, student_number, course, year_level, section) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssss", $fname, $lname, $mname, $gender, $phone_number, $address, $email, $password, $student_number, $course, $year_level, $section);

    if ($stmt->execute()) {
        echo "<p>Student account added successfully!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Fetch all students for the table
$students_sql = "SELECT student_id, fname, lname, mname, gender, phone_number, address, email, student_number, course, year_level, section FROM students";
$students_result = $conn->query($students_sql);

$conn->close();

function generateStudentNumber() {
    // Implement your logic to generate a unique student number
    // This is just a placeholder implementation
    return 'S' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Add Student</h1>
    <form action="" method="POST">
        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname" required><br><br>

        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" required><br><br>

        <label for="mname">Middle Name:</label>
        <input type="text" id="mname" name="mname"><br><br>

        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="">--Select Gender--</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select><br><br>

        <label for="phone_number">Phone Number:</label>
        <input type="number" id="phone_number" name="phone_number"><br><br>

        <label for="address">Address:</label>
        <textarea id="address" name="address" rows="3"></textarea><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="course">Course:</label>
        <input type="text" id="course" name="course"><br><br>

        <label for="year_level">Year Level:</label>
        <input type="number" id="year_level" name="year_level" min="1" max="5"><br><br>

        <label for="section">Section:</label>
        <input type="text" id="section" name="section"><br><br>

        <button type="submit">Add Student</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>

    <h2>All Students</h2>
    <?php if ($students_result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Number</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Middle Name</th>
                    <th>Gender</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Year Level</th>
                    <th>Section</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['student_number']) ?></td>
                        <td><?= htmlspecialchars($row['fname']) ?></td>
                        <td><?= htmlspecialchars($row['lname']) ?></td>
                        <td><?= htmlspecialchars($row['mname'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['gender']) ?></td>
                        <td><?= htmlspecialchars($row['phone_number'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['address'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['course'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['year_level'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['section'] ?? 'N/A') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No students found.</p>
    <?php endif; ?>
    <a href="../admin_dashboard.php">Back to Dashboard</a>
</body>
</html>