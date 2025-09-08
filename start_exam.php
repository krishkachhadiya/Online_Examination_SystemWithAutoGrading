<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student id from session
$student_id = $_SESSION['student_id'] ?? null;

// Get exam id from URL
$exam_id = $_GET['exam_id'] ?? null;

if (!$exam_id) {
    die("Invalid exam!");
}

// ✅ Fetch exam details using exam_id
$exam_stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$exam_stmt->bind_param("i", $exam_id);
$exam_stmt->execute();
$exam = $exam_stmt->get_result()->fetch_assoc();

if (!$exam) {
    die("Exam not found!");
}

// ✅ Fetch questions for this exam
$q_stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ?");
$q_stmt->bind_param("i", $exam_id);
$q_stmt->execute();
$questions = $q_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Start Exam</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
        .timer {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: red;
        }
        .question {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .question h4 {
            margin-bottom: 10px;
        }
        label {
            display: block;
            margin-bottom: 6px;
        }
        .submit-btn {
            display: block;
            width: 100%;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><?= htmlspecialchars($exam['title']) ?> (<?= htmlspecialchars($exam['subject']) ?>)</h2>
    <div class="timer" id="timer">Time Remaining: --:--</div>

    <form method="POST" action="submit_exam.php">
        <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
        <input type="hidden" name="student_id" value="<?= $student_id ?>">

        <?php $qno = 1; ?>
        <?php while ($row = $questions->fetch_assoc()): ?>
            <div class="question">
                <h4>Q<?= $qno++ ?>. <?= htmlspecialchars($row['question_text']) ?></h4>
                <label><input type="radio" name="answers[<?= $row['question_id'] ?>]" value="A"> <?= htmlspecialchars($row['option_a']) ?></label>
                <label><input type="radio" name="answers[<?= $row['question_id'] ?>]" value="B"> <?= htmlspecialchars($row['option_b']) ?></label>
                <label><input type="radio" name="answers[<?= $row['question_id'] ?>]" value="C"> <?= htmlspecialchars($row['option_c']) ?></label>
                <label><input type="radio" name="answers[<?= $row['question_id'] ?>]" value="D"> <?= htmlspecialchars($row['option_d']) ?></label>
            </div>
        <?php endwhile; ?>

        <button type="submit" class="submit-btn">Submit Exam</button>
    </form>
</div>

<script>
    // Countdown timer
    var duration = <?= (int)$exam['duration'] ?> * 60; // in seconds
    var timerDisplay = document.getElementById("timer");

    function startCountdown() {
        var interval = setInterval(function() {
            var minutes = Math.floor(duration / 60);
            var seconds = duration % 60;
            timerDisplay.textContent = "Time Remaining: " + minutes + "m " + seconds + "s";

            duration--;

            if (duration < 0) {
                clearInterval(interval);
                document.querySelector("form").submit();
            }
        }, 1000);
    }

    startCountdown();
</script>
</body>
</html>
