<?php
session_start();
if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!-- HTML AREA -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard/port.8080</title>
    <link rel="shortcut icon" href="logo.png" type="image/x-icon">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>

<body>
   
<div class="d-flex align-items-center bg-primary 
"style="height: 100vh ; width: 230px;" >
     <ul style="list-style: none;"> 
         <button type="button" class="btn btn-primary">
         <a class="text-decoration-none text-white" href="create_class.php">Create a New Class</a>
         </button>

         <button type="button" class="btn btn-primary">
         <a class="text-decoration-none text-white" href="add_student.php">Add Student</a>
         </button>

         <button type="button" class="btn btn-primary">
         <a class="text-decoration-none text-white" href="grade_students.php">Student Grades</a>
         </button>

         <button type="button" class="btn btn-primary">
         <a class="text-decoration-none text-white" href="subjects.php">Subject</a>
         </button>

         <button type="button" class="btn btn-primary">
         <a class="text-decoration-none text-white" href="student_managment.php">Student Management</a>
         </button>

         <button type="button" class="btn btn-primary">
         <a class="text-decoration-none text-white" href="student_programs.php">Student Programs</a>
         </button>

         <button type="button" class="btn btn-primary">
         <a class="text-decoration-none text-white" href="attendance.php">Attendance</a>
         </button><br>
        
         <button type="button" class="btn btn-primary">
         <a class="text-decoration-none text-white" href="grades.php">Grades</a>
         </button><br>

         <button type="button" class="btn btn-primary">
         <a class="text-decoration-none text-white" href="settings.php">Settings</a>
         </button><br>

         <button type="button" class="btn btn-primary">
         <a class="text-decoration-none text-white" href="logout.php">Logout</a>
         </button>
    </ul> 
    <div class="position-relative">
        <div class="position-absolute top-0 start-0">
        <h1>Hello Admin</h1>
        </div>
    </div>
    
</div>


    <style>
       .hover-effect a:hover {
            color: #7bffc7 !important;
            width: 200px;
            height: 30px;
            background-color: white;
            display: flex;

        }
    </style>
    
    <!-- Bootstrap Script Modal Ver.. -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>