<?php
session_start();
require_once '../../config/db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: ../../account_pages/login.php");
    exit();
}

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $mname = $_POST['mname'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];
        $address = $_POST['address'];

        $stmt = $conn->prepare("UPDATE instructors SET fname = ?, lname = ?, mname = ?, email = ?, phone_number = ?, address = ? WHERE instructor_id = ?");
        $stmt->bind_param("ssssssi", $fname, $lname, $mname, $email, $phone_number, $address, $_SESSION['instructor_id']);

        if ($stmt->execute()) {
            $success = "Profile updated successfully";
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        } else {
            $stmt = $conn->prepare("SELECT password FROM instructors WHERE instructor_id = ?");
            $stmt->bind_param("i", $_SESSION['instructor_id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result['password'] === $current_password) {
                $stmt = $conn->prepare("UPDATE instructors SET password = ? WHERE instructor_id = ?");
                $stmt->bind_param("si", $new_password, $_SESSION['instructor_id']);

                if ($stmt->execute()) {
                    $success = "Password changed successfully";
                } else {
                    $error = "Error changing password: " . $conn->error;
                }
            } else {
                $error = "Current password is incorrect";
            }
        }
    }
}

// Fetch instructor details
$stmt = $conn->prepare("SELECT fname, lname, mname, email, phone_number, address FROM instructors WHERE instructor_id = ?");
$stmt->bind_param("i", $_SESSION['instructor_id']);
$stmt->execute();
$instructor = $stmt->get_result()->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h2, h3 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: inline-block;
            width: 150px;
            margin-bottom: 10px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 250px;
            padding: 5px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        a {
            color: #1a73e8;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Settings</h2>
    <?php
    if (isset($success)) echo "<p class='success'>$success</p>";
    if (isset($error)) echo "<p class='error'>$error</p>";
    ?>
    <h3>Edit Profile</h3>
    <form method="post">
        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($instructor['fname']); ?>" required><br>
        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($instructor['lname']); ?>" required><br>
        <label for="mname">Middle Name:</label>
        <input type="text" id="mname" name="mname" value="<?php echo htmlspecialchars($instructor['mname']); ?>"><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($instructor['email']); ?>" required><br>
        <label for="phone_number">Phone Number:</label>
        <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($instructor['phone_number']); ?>"><br>
        <label for="address">Address:</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($instructor['address']); ?>"><br>
        <input type="submit" name="update_profile" value="Update Profile">
    </form>
    <h3>Change Password</h3>
    <form method="post">
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required><br>
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br>
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br>
        <input type="submit" name="change_password" value="Change Password">
    </form>
    <br>
    <a href="../dashboard.php">Back to Dashboard</a>
    <br>
    <a href="../logout.php">Logout</a>
</body>
</html>