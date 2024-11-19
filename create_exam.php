<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Fetch subjects for the current instructor
$stmt = $conn->prepare("SELECT id, subject_name FROM subjects WHERE instructor_id = ?");
$stmt->bind_param("i", $_SESSION['instructor_id']);
$stmt->execute();
$subjects_result = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_id = $_POST['subject_id'];
    $question_text = $_POST['question_text'];
    $answer_a = $_POST['answer_a'];
    $answer_b = $_POST['answer_b'];
    $answer_c = $_POST['answer_c'];
    $correct_answer = $_POST['correct_answer'];

    $stmt = $conn->prepare("INSERT INTO exam_questions (subject_id, question_text, answer_a, answer_b, answer_c, correct_answer) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $subject_id, $question_text, $answer_a, $answer_b, $answer_c, $correct_answer);

    if ($stmt->execute()) {
        $success = "Question added successfully!";
    } else {
        $error = "Error adding question: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch existing exam questions
$exam_questions = [];
$stmt = $conn->prepare("SELECT eq.*, s.subject_name FROM exam_questions eq JOIN subjects s ON eq.subject_id = s.id WHERE s.instructor_id = ?");
$stmt->bind_param("i", $_SESSION['instructor_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $exam_questions[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam</title>
</head>
<body>
    <h2>Add Exam Question</h2>
    <?php
    if (isset($success)) echo "<p style='color: green;'>$success</p>";
    if (isset($error)) echo "<p style='color: red;'>$error</p>";
    ?>
    <form method="POST" action="">
        <p><label>Subject:</label></p>
        <select name="subject_id" required>
            <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></option>
            <?php endwhile; ?>
        </select><br>

        <p><label>Question:</label></p>
        <textarea name="question_text" required></textarea><br>

        <p><label>Option A:</label></p>
        <input type="text" name="answer_a" required><br>

        <p><label>Option B:</label></p>
        <input type="text" name="answer_b" required><br>

        <p><label>Option C:</label></p>
        <input type="text" name="answer_c" required><br>

        <p><label>Correct Answer:</label></p>
        <select name="correct_answer" required>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
        </select><br><br>

        <input type="submit" value="Add Question">
    </form>

    <h3>Existing Exam Questions</h3>
    <?php if (!empty($exam_questions)): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Subject</th>
                <th>Question</th>
                <th>Answer A</th>
                <th>Answer B</th>
                <th>Answer C</th>
                <th>Correct Answer</th>
                <th>Action</th>
            </tr>
            <?php foreach ($exam_questions as $question): ?>
                <tr>
                    <td><?php echo $question['id']; ?></td>
                    <td><?php echo htmlspecialchars($question['subject_name']); ?></td>
                    <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                    <td><?php echo htmlspecialchars($question['answer_a']); ?></td>
                    <td><?php echo htmlspecialchars($question['answer_b']); ?></td>
                    <td><?php echo htmlspecialchars($question['answer_c']); ?></td>
                    <td><?php echo $question['correct_answer']; ?></td>
                    <td><a href="edit_exam_question.php?id=<?php echo $question['id']; ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No exam questions found.</p>
    <?php endif; ?>

    <br>
    <a href="settings.php">Back to Settings</a>
</body>
</html>