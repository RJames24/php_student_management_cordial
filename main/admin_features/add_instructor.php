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

    // Insert the data into the `instructors` table
    $sql = "INSERT INTO instructors (fname, lname, mname, gender, phone_number, address, email, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $fname, $lname, $mname, $gender, $phone_number, $address, $email, $password);

    if ($stmt->execute()) {
        echo "Instructor account added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
// Fetch all instructor for the table
$instructor_sql = "SELECT instructor_id, fname, lname, mname, gender, phone_number, address, email FROM instructors";
$instructor_result = $conn->query($instructor_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Instructor</title>
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
    <h1>Add Instructor</h1>
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

        <button type="submit">Add Instructor</button>
    </form>
    <h2>All Students</h2>
    <?php if ($instructor_result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th> Gender</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $instructor_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['instructor_id']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['lname'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['fname']) ?></td>
                        <td><?= htmlspecialchars($row['mname'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['gender'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['phone_number']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No instructor found.</p>
    <?php endif; ?>
    <a href="../admin_dashboard.php">Back to Dashboard</a>
</body>
</html>