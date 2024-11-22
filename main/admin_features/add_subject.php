<?php
session_start();
require_once '../../config/db_connect.php';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = $_POST['subject_name'];
    $subject_code = $_POST['subject_code'];

    // Insert the new subject into the database
    $sql = "INSERT INTO subjects (subject_name, subject_code) 
            VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $subject_name, $subject_code);

    if ($stmt->execute()) {
        echo "Subject added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all subjects to display in the list
$subjects_sql = "SELECT subject_id, subject_name, subject_code FROM subjects";
$subject_result = $conn->query($subjects_sql);

if ($subject_result === false) {
    // Handle the error if the query failed
    echo "Error fetching subjects: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject</title>
</head>
<body>
    <h1>Add Subject</h1>
    <form action="" method="POST">
        <label for="subject_name">Subject Name:</label>
        <input type="text" id="subject_name" name="subject_name" required><br><br>

        <label for="subject_code">Subject Code:</label>
        <input type="text" id="subject_code" name="subject_code" required><br><br>

        <button type="submit">Add Subject</button>
    </form>

    <h2>All Subjects</h2>
    <!-- Display the list of added subjects -->
    <table border="1">
        <tr>
            <th>Subject Name</th>
            <th>Subject Code</th>
        </tr>
        <?php
        // Check if there are any subjects to display
        if (isset($subject_result) && $subject_result->num_rows > 0) {
            while ($row = $subject_result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['subject_name']) . "</td>
                        <td>" . htmlspecialchars($row['subject_code']) . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No subjects found.</td></tr>";
        }
        ?>
    </table>

    <a href="../admin_dashboard.php">Back to Dashboard</a>
</body>
</html>