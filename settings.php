<?php
session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("UPDATE instructors SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $_SESSION['instructor_id']);

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
            $stmt = $conn->prepare("SELECT password FROM instructors WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['instructor_id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result['password'] === $current_password) {
                $stmt = $conn->prepare("UPDATE instructors SET password = ? WHERE id = ?");
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
$stmt = $conn->prepare("SELECT name, email FROM instructors WHERE id = ?");
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
</head>
<body>
    <h2>Settings</h2>
    <?php
    if (isset($success)) echo "<p style='color: green;'>$success</p>";
    if (isset($error)) echo "<p style='color: red;'>$error</p>";
    ?>
    <h3>Edit Profile</h3>
    <form method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($instructor['name']); ?>" required><br><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($instructor['email']); ?>" required><br><br>
        <input type="submit" name="update_profile" value="Update Profile">
    </form>
    <h3>Change Password</h3>
    <form method="post">
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required><br><br>
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br><br>
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>
        <input type="submit" name="change_password" value="Change Password">
    </form>
    <!-- <br>
    <h2>Subjects Settings</h2>
    <ul>
        <li><a href="">Edit Quiz</a></li>
        <li><a href="">Edit Exams</a></li>

    </ul> -->
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
    <br>
    <a href="logout.php">Logout</a>
</body>
</html>