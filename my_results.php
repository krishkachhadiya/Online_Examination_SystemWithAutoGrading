<?php
session_start();
include("../includes/db.php");

// Ensure student login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo "<p style='color:red;'>❌ You must login as student to view results.</p>";
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch student info
$student_query = $conn->query("SELECT * FROM students WHERE id = $student_id");
$student = $student_query->fetch_assoc();

// Fetch results
$query = "
    SELECT r.score, r.total_questions, 
           e.title, e.subject, e.start_time
    FROM results r
    JOIN exams e ON r.exam_id = e.exam_id
    WHERE r.student_id = $student_id
    ORDER BY e.start_time DESC
";
$results = $conn->query($query);

// Calculate overall stats
$total_score = 0;
$total_questions = 0;
while ($row = $results->fetch_assoc()) {
    $total_score += $row['score'];
    $total_questions += $row['total_questions'];
    $data[] = $row;
}
$overall_percentage = ($total_questions > 0) ? round(($total_score / $total_questions) * 100, 2) : 0;

function getGrade($percentage) {
    if ($percentage >= 90) return "A+";
    elseif ($percentage >= 75) return "A";
    elseif ($percentage >= 60) return "B";
    elseif ($percentage >= 50) return "C";
    else return "F";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Results</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 3px 6px rgba(0,0,0,0.1); }
        h1 { color: #1e3a8a; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #f1f5f9; }
        .summary { margin-top: 20px; padding: 15px; background: #f9fafb; border-radius: 8px; }
        .btn { background: #1e3a8a; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; margin-top: 15px; }
        .btn:hover { background: #3748ac; }
    </style>
</head>
<body>
<div class="container" id="resultContent">
    <h1>📊 Student Results</h1>
    <p><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
    <p><strong>Student ID:</strong> <?= htmlspecialchars($student['id']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>

    <?php if (!empty($data)): ?>
        <table>
            <tr>
                <th>Exam Title</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Score</th>
                <th>Percentage</th>
                <th>Grade</th>
            </tr>
            <?php foreach ($data as $row): 
                $percentage = round(($row['score'] / $row['total_questions']) * 100, 2);
                $grade = getGrade($percentage);
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td><?= date("d M Y, h:i A", strtotime($row['start_time'])) ?></td>
                    <td><?= $row['score'] ?> / <?= $row['total_questions'] ?></td>
                    <td><?= $percentage ?>%</td>
                    <td><?= $grade ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="summary">
            <h3>📌 Overall Performance</h3>
            <p><strong>Total Marks:</strong> <?= $total_score ?> / <?= $total_questions ?></p>
            <p><strong>Overall Percentage:</strong> <?= $overall_percentage ?>%</p>
            <p><strong>Final Grade:</strong> <?= getGrade($overall_percentage) ?></p>
        </div>

    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
</div>

<!-- Download Button -->
<button class="btn" onclick="downloadPDF()">⬇️ Download Result (PDF)</button>

<script>
function downloadPDF() {
    const element = document.getElementById("resultContent");
    html2pdf().from(element).save("Student_Result.pdf");
}
</script>
</body>
</html>
