<?php
session_start();
include("../includes/db.php");

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    echo "<p style='color:red;'>❌ You must login as a student to submit exam.</p>";
    exit;
}

$student_id = $_SESSION['student_id'];

// Validate exam_id and answers
if (!isset($_POST['exam_id']) || !isset($_POST['answers'])) {
    echo "<p style='color:red;'>❌ Invalid submission! Missing Exam ID or answers.</p>";
    echo "<br><a href='dashboard.php'>Go Back</a>";
    exit;
}

$exam_id = intval($_POST['exam_id']);
$answers = $_POST['answers'];

// Score calculation
$score = 0;
$total_questions = 0;

foreach ($answers as $question_id => $given_answer) {
    $stmt = $conn->prepare("SELECT correct_option FROM questions WHERE question_id = ? AND exam_id = ?");
    $stmt->bind_param("ii", $question_id, $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $total_questions++;
        if (strtolower(trim($row['correct_option'])) === strtolower(trim($given_answer))) {
            $score++;
        }
    }
    $stmt->close();
}

// Save result silently
$stmt = $conn->prepare("INSERT INTO results (student_id, exam_id, score, total_questions) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiii", $student_id, $exam_id, $score, $total_questions);
$stmt->execute();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam Submitted</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .message-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            text-align: center;
            width: 400px;
        }
        h2 { color: #28a745; margin-bottom: 15px; }
        a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 18px;
            background: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            transition: 0.3s;
        }
        a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h2>✅ Exam Submitted Successfully!</h2>
        <p>Your answers have been recorded.</p>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
