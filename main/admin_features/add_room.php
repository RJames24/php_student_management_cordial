<?php
session_start();
require_once '../../config/db_connect.php';

$conn = getDBConnection();

// Variables for form data
$room_number = isset($_POST['room_number']) ? $_POST['room_number'] : null;
$description = isset($_POST['description']) ? $_POST['description'] : null;
$room_added = false;

// Add Room to the rooms table
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($room_number) && isset($description)) {
    // Prepare SQL to insert the room into the rooms table
    $insert_sql = "INSERT INTO rooms (room_number, description) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ss", $room_number, $description);
    if ($stmt->execute()) {
        $room_added = true;
    }
    $stmt->close();
}

// Fetch all rooms from the database
$select_sql = "SELECT room_number, description FROM rooms";
$room_result = $conn->query($select_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room</title>
</head>
<body>
    <h1>Add New Room</h1>

    <!-- Room Addition Form -->
    <form method="POST">
        <label for="room_number">Room Number:</label>
        <input type="text" id="room_number" name="room_number" required><br><br>

        <label for="description">Room Type:</label>
        <select id="description" name="description" required>
            <option value="Lab">Lab</option>
            <option value="Lecture Room">Lecture Room</option>
            <option value="Conference Room">Conference Room</option>
            <option value="Auditorium">Auditorium</option>
            <option value="Seminar Room">Seminar Room</option>
            <option value="Others">Others</option>
        </select><br><br>

        <button type="submit">Add Room</button>
    </form>

    <h2>All Rooms</h2>
    <!-- Display the list of added rooms -->
    <table border="1">
        <tr>
            <th>Room Number</th>
            <th>Room Type</th>
        </tr>
        <?php
        // Check if there are any rooms to display
        if ($room_result->num_rows > 0) {
            while ($row = $room_result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['room_number']) . "</td>
                        <td>" . htmlspecialchars($row['description']) . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No rooms found.</td></tr>";
        }
        ?>
    </table>

    <!-- Confirmation Message -->
    <?php if ($room_added) { ?>
        <p>Room added successfully!</p>
    <?php } ?>

    <a href="../admin_dashboard.php">Back to Dashboard</a>
</body>
</html>