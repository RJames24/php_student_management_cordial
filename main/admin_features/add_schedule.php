<?php

session_start();
require_once '../../config/db_connect.php';
//rovic triple x
$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'];
    $instructor_id = $_POST['instructor_id'];
    $class_id = $_POST['class_id'];
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time']; // 12-hour time with AM/PM input
    $end_time = $_POST['end_time']; // 12-hour time with AM/PM input
    $room_number = $_POST['room_number']; // Add room_number input

    // Convert start time and end time to 24-hour format
    $start_time_24hr = date("H:i", strtotime($start_time));
    $end_time_24hr = date("H:i", strtotime($end_time));

    // Insert into schedules table with room_number
    $sql = "INSERT INTO schedules (subject_id, instructor_id, class_id, day_of_week, start_time, end_time, room_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissss", $subject_id, $instructor_id, $class_id, $day_of_week, $start_time_24hr, $end_time_24hr, $room_number);

    if ($stmt->execute()) {
        echo "Schedule added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch subjects for the dropdown
$subjects_sql = "SELECT subject_id, subject_name FROM subjects";
$subjects_result = $conn->query($subjects_sql);

// Fetch instructors with full name concatenation
$instructors_sql = "SELECT instructor_id, CONCAT(fname, ' ', mname, ' ', lname) AS full_name FROM instructors";
$instructors_result = $conn->query($instructors_sql);

// Fetch classes (Year and Section)
$classes_sql = "SELECT id, CONCAT(year_level, ' - ', section) AS class_name FROM year_and_section";
$classes_result = $conn->query($classes_sql);

// Fetch available rooms for the dropdown
$rooms_sql = "SELECT room_number, description FROM rooms";
$rooms_result = $conn->query($rooms_sql);

// Fetch all added schedules from the database
$schedules_sql = "SELECT schedules.schedule_id, 
                         subjects.subject_name, 
                         CONCAT(instructors.fname, ' ', instructors.mname, ' ', instructors.lname) AS instructor_name, 
                         CONCAT(year_and_section.year_level, ' - ', year_and_section.section) AS class_name, 
                         schedules.day_of_week, 
                         schedules.start_time, 
                         schedules.end_time, 
                         rooms.room_number
                  FROM schedules
                  JOIN subjects ON schedules.subject_id = subjects.subject_id
                  JOIN instructors ON schedules.instructor_id = instructors.instructor_id
                  JOIN year_and_section ON schedules.class_id = year_and_section.id
                  JOIN rooms ON schedules.room_number = rooms.room_number";
$schedules_result = $conn->query($schedules_sql);

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Schedule</title>
</head>
<body>
    <h1>Add Schedule</h1>
    <form action="" method="POST">
        <!-- Subject Dropdown -->
        <label for="subject_id">Subject:</label>
        <select id="subject_id" name="subject_id" required>
            <?php while ($row = $subjects_result->fetch_assoc()) { ?>
                <option value="<?= $row['subject_id'] ?>"><?= htmlspecialchars($row['subject_name']) ?></option>
            <?php } ?>
        </select><br><br>

        <!-- Instructor Dropdown -->
        <label for="instructor_id">Instructor:</label>
        <select id="instructor_id" name="instructor_id" required>
            <?php while ($row = $instructors_result->fetch_assoc()) { ?>
                <option value="<?= $row['instructor_id'] ?>"><?= htmlspecialchars($row['full_name']) ?></option>
            <?php } ?>
        </select><br><br>

        <!-- Class (Year and Section) Dropdown -->
        <label for="class_id">Year and Section:</label>
        <select id="class_id" name="class_id" required>
            <?php while ($row = $classes_result->fetch_assoc()) { ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
            <?php } ?>
        </select><br><br>

        <!-- Day of the Week Dropdown -->
        <label for="day_of_week">Day of Week:</label>
        <select id="day_of_week" name="day_of_week" required>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
            <option value="Sunday">Sunday</option>
        </select><br><br>

        <!-- Start Time Input (AM/PM format) -->
        <label for="start_time">Start Time:</label>
        <input type="time" id="start_time" name="start_time" required><br><br>

        <!-- End Time Input (AM/PM format) -->
        <label for="end_time">End Time:</label>
        <input type="time" id="end_time" name="end_time" required><br><br>

        <!-- Room Number Dropdown -->
        <label for="room_number">Room:</label>
        <select id="room_number" name="room_number" required>
            <?php while ($row = $rooms_result->fetch_assoc()) { ?>
                <option value="<?= $row['room_number'] ?>"><?= htmlspecialchars($row['room_number']) ?> (<?= htmlspecialchars($row['description']) ?>)</option>
            <?php } ?>
        </select><br><br>

        <button type="submit">Add Schedule</button>
    </form>

    <h2>All Schedules</h2>
    <!-- Display the list of added schedules -->
    <table border="1">
        <tr>
            <th>Subject</th>
            <th>Instructor</th>
            <th>Class</th>
            <th>Day</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Room</th>
        </tr>
        <?php while ($schedule = $schedules_result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($schedule['subject_name']) ?></td>
                <td><?= htmlspecialchars($schedule['instructor_name']) ?></td>
                <td><?= htmlspecialchars($schedule['class_name']) ?></td>
                <td><?= htmlspecialchars($schedule['day_of_week']) ?></td>
                <td><?= date("g:i A", strtotime($schedule['start_time'])) ?></td>
                <td><?= date("g:i A", strtotime($schedule['end_time'])) ?></td>
                <td><?= htmlspecialchars($schedule['room_number']) ?></td>
            </tr>
        <?php } ?>
    </table>

    <a href="../admin_dashboard.php">Back to Dashboard</a>
</body>
</html>