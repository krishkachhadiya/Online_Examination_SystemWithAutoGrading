<?php
session_start();
include("../includes/db.php");

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p style='color:red;'>❌ You must login as admin to view results.</p>";
    exit;
}

// Fetch all results with student and exam details
$query = "
    SELECT r.id AS result_id, r.score, r.total_questions, 
           e.title, e.subject, e.start_time,
           s.id AS student_id, s.name AS student_name, s.email
    FROM results r
    JOIN exams e ON r.exam_id = e.exam_id
    JOIN students s ON r.student_id = s.id
    ORDER BY e.start_time DESC
";
$results = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Student Results</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
        }
        h1 { color: #1e3a8a; margin-bottom: 20px; }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 6px rgba(0,0,0,0.08);
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 600;
            color: #334155;
        }
        td { color: #1e293b; }
        .no-results { color: #d32f2f; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1>📊 All Exam Results</h1>

    <?php if ($results->num_rows > 0): ?>
        <table>
            <tr>
                <th>Student Name</th>
                <th>Email</th>
                <th>Exam Title</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Score</th>
            </tr>
            <?php while ($row = $results->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td><?= date("d M Y, h:i A", strtotime($row['start_time'])) ?></td>
                    <td><?= $row['score'] ?> / <?= $row['total_questions'] ?></td>
                    
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="no-results">No results found.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php
$conn->close();
?>
