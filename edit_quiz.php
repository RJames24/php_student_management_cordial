<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['instructor_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

if (!isset($_GET['id'])) {
    header("Location: create_quiz.php");
    exit();
}

$question_id = $_GET['id'];

// Fetch the question details
$stmt = $conn->prepare("SELECT qq.*, s.subject_name FROM quiz_questions qq JOIN subjects s ON qq.subject_id = s.id WHERE qq.id = ? AND s.instructor_id = ?");
$stmt->bind_param("ii", $question_id, $_SESSION['instructor_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: create_quiz.php");
    exit();
}

$question = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_text = $_POST['question_text'];
    $answer_a = $_POST['answer_a'];
    $answer_b = $_POST['answer_b'];
    $answer_c = $_POST['answer_c'];
    $correct_answer = $_POST['correct_answer'];

    $stmt = $conn->prepare("UPDATE quiz_questions SET question_text = ?, answer_a = ?, answer_b = ?, answer_c = ?, correct_answer = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $question_text, $answer_a, $answer_b, $answer_c, $correct_answer, $question_id);

    if ($stmt->execute()) {
        $success = "Question updated successfully!";
        // Refresh the question data
        $stmt = $conn->prepare("SELECT qq.*, s.subject_name FROM quiz_questions qq JOIN subjects s ON qq.subject_id = s.id WHERE qq.id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $question = $result->fetch_assoc();
    } else {
        $error = "Error updating question: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz Question</title>
</head>
<body>
    <h2>Edit Quiz Question</h2>
    <?php
    if (isset($success)) echo "<p style='color: green;'>$success</p>";
    if (isset($error)) echo "<p style='color: red;'>$error</p>";
    ?>
    <form method="POST" action="">
        <p><label>Subject: <?php echo htmlspecialchars($question['subject_name']); ?></label></p>

        <p><label>Question:</label></p>
        <textarea name="question_text" required><?php echo htmlspecialchars($question['question_text']); ?></textarea><br>

        <p><label>Option A:</label></p>
        <input type="text" name="answer_a" value="<?php echo htmlspecialchars($question['answer_a']); ?>" required><br>

        <p><label>Option B:</label></p>
        <input type="text" name="answer_b" value="<?php echo htmlspecialchars($question['answer_b']); ?>" required><br>

        <p><label>Option C:</label></p>
        <input type="text" name="answer_c" value="<?php echo htmlspecialchars($question['answer_c']); ?>" required><br>

        <p><label>Correct Answer:</label></p>
        <select name="correct_answer" required>
            <option value="A" <?php echo ($question['correct_answer'] == 'A') ? 'selected' : ''; ?>>A</option>
            <option value="B" <?php echo ($question['correct_answer'] == 'B') ? 'selected' : ''; ?>>B</option>
            <option value="C" <?php echo ($question['correct_answer'] == 'C') ? 'selected' : ''; ?>>C</option>
        </select><br><br>

        <input type="submit" value="Update Question">
    </form>

    <br>
    <a href="settings.php">Back to Settings</a>
</body>
</html>