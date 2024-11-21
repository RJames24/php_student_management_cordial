<?php
session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Fetch all scholars
$stmt = $conn->prepare("
    SELECT s.id, s.student_number, s.name, s.course, s.year_level, s.section, s.email, s.contact_number, s.address,
           sd.certificate_of_registration, sd.scholarship_type,
           AVG(g.grade) as average_grade,
           a.scholarship_status
    FROM students s
    JOIN scholarship_details sd ON s.id = sd.student_id
    LEFT JOIN grades g ON s.id = g.student_id
    LEFT JOIN averages a ON s.id = a.student_id
    WHERE s.is_scholar = TRUE
    GROUP BY s.id
    ORDER BY s.course, s.year_level, s.section, s.name
");
$stmt->execute();
$scholars = $stmt->get_result();

// Count total scholars and failed scholars
$total_scholars = $scholars->num_rows;
$failed_scholars = 0;

// Function to determine scholarship status based on average grade
function getScholarshipStatus($average_grade) {
    if ($average_grade >= 1.5) {
        return 'warning';
    } elseif ($average_grade > 3.0) {
        return 'lost';
    }
    return 'maintaining';
}

// Update scholarship status for all scholars
while ($scholar = $scholars->fetch_assoc()) {
    $status = getScholarshipStatus($scholar['average_grade']);
    $stmt = $conn->prepare("UPDATE averages SET scholarship_status = ? WHERE student_id = ?");
    $stmt->bind_param("si", $status, $scholar['id']);
    $stmt->execute();
    
    if ($status === 'lost') {
        $failed_scholars++;
    }
}

// Rewind the result set
$scholars->data_seek(0);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Scholarship Monitoring</h1>
            <a href="dashboard.php" class="hover:underline">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mx-auto mt-8 p-4">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">Scholarship Statistics</h2>
            <p>Total Scholars: <?php echo $total_scholars; ?></p>
            <p>Failed Scholars: <?php echo $failed_scholars; ?></p>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($scholar = $scholars->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($scholar['student_number']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($scholar['name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($scholar['course']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($scholar['year_level']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($scholar['section']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo number_format($scholar['average_grade'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    echo match ($scholar['scholarship_status']) {
                                        'maintaining' => 'bg-green-100 text-green-800',
                                        'warning' => 'bg-yellow-100 text-yellow-800',
                                        'lost' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    ?>">
                                    <?php echo ucfirst($scholar['scholarship_status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>