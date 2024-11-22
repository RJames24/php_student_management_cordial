<?php

session_start();
require_once '../../config/db_connect.php';

$conn = getDBConnection();
//
$message = "";

// Handle form submission for adding a new class (Year and Section)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year_level = $_POST['year_level'];
    $section = $_POST['section'];

    // Insert into the year_and_section table
    $sql = "INSERT INTO year_and_section (year_level, section) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $year_level, $section);

    if ($stmt->execute()) {
        $message = "Year and Section added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Year and Section</title>
</head>
<body>
    <h1>Add Year and Section</h1>

    <!-- Display Success or Error Message -->
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <!-- Form to Add Year and Section -->
    <form method="POST">
        <label for="year_level">Year Level:</label>
        <select id="year_level" name="year_level" required>
            <option value="1st Year">1st Year</option>
            <option value="2nd Year">2nd Year</option>
            <option value="3rd Year">3rd Year</option>
            <option value="4th Year">4th Year</option>
        </select><br><br>

        <label for="section">Section:</label>
        <input type="text" id="section" name="section" placeholder="e.g., Section A, Section B" required><br><br>
        <button type="submit">Add Year and Section</button>
    </form>
    <a href="../admin_dashboard.php">Back to Dashboard</a>
</body>
</html>