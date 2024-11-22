<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .menu {
            list-style-type: none;
            padding: 0;
        }
        .menu > li {
            margin: 10px 0;
        }
        .submenu {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: none; /* Hidden by default */
        }
        .submenu li {
            margin: 5px 0;
        }
        .submenu li a {
            text-decoration: none;
            color: #007BFF;
        }
        .submenu li a:hover {
            text-decoration: underline;
        }
        .settings:hover {
            cursor: pointer;
        }
    </style>
    <script>
        // Toggle dropdown visibility
        function toggleDropdown() {
            const submenu = document.querySelector('.submenu');
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
            } else {
                submenu.style.display = 'block';
            }
        }
    </script>
</head>
<body>
<h1>Admin Dashboard</h1>
<ul class="menu">
    <li><a href="dashboard.php">Dashboard</a></li>
    
    <li>
       <h4 class="settings" onclick="toggleDropdown()">Settings</h4>
        <ul class="submenu">
            <li><a href="admin_features/upload_csv.php">Upload CSV File</a></li>
            <li><a href="admin_features/add_student.php">Add Student</a></li>
            <li><a href="admin_features/add_instructor.php">Add Instructor</a></li>
            <li><a href="admin_features/add_class.php">Add Class/Section</a></li>
            <li><a href="admin_features/add_subject.php">Add Subject</a></li>
            <li><a href="admin_features/add_room.php">Add Room</a></li>
            <li><a href="admin_features/add_schedule.php">Add Schedule and Assign Room</a></li>
            <li><a href="admin_features/edit_profile.php">Edit Profile</a></li>
            <li><a href="admin_features/change_password.php">Change Password</a></li>
            <li><a href="../account_pages/admin_logout.php">Logout</a></li>
        </ul>
    </li>
</ul>
</body>
</html>