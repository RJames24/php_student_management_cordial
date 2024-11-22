<?php
session_start();
require_once 'dbcon.php';

$conn = getDBConnection();
// upload_csv.php

// Check if the form is submitted
if(isset($_POST['submit'])) {
    // Define the target directory to save the uploaded CSV
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["csvFile"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if the uploaded file is a CSV
    if($fileType != "csv") {
        echo "Sorry, only CSV files are allowed.";
        $uploadOk = 0;
    }

    // Check if the file already exists
    if(file_exists($targetFile)) {
        echo "Sorry, the file already exists.";
        $uploadOk = 0;
    }

    // Check file size (limit to 5MB)
    if ($_FILES["csvFile"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // If no errors, try to upload the file
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["csvFile"]["tmp_name"], $targetFile)) {
            echo "The file " . basename($_FILES["csvFile"]["name"]) . " has been uploaded.";

            // Now you can process the CSV and import into the database
            processCSV($targetFile);
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

function processCSV($filePath) {
    // Open the CSV file
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        // Skip the header row if present
        fgetcsv($handle);

        // Loop through the rows of the CSV file
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Example: Assuming the CSV contains: Name, Age, Grade
            $name = $data[0];
            $age = $data[1];
            $grade = $data[2];

            $conn = getDBConnection();

            // Prepare an SQL statement
            $stmt = $conn->prepare("INSERT INTO students (name, age, grade) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $name, $age, $grade); // 's' for string, 'i' for integer

            // Execute the query
            $stmt->execute();
            $conn->close();
        }

        // Close the file
        fclose($handle);
        echo "CSV data has been successfully imported into the database.";
    } else {
        echo "Failed to open the CSV file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CSV</title>
</head>
<body>
    <h1>Upload Student Data (CSV)</h1>
    <form action="upload_csv.php" method="POST" enctype="multipart/form-data">
        <label for="csvFile">Choose CSV File:</label>
        <input type="file" name="csvFile" id="csvFile" required>
        <br><br>
        <input type="submit" name="submit" value="Upload">
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
