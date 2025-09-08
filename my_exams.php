<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}


// Delete exam if requested
if (isset($_POST['delete_exam'])) {
    $exam_id = $_POST['exam_id'];
    $stmt = $conn->prepare("DELETE FROM exams WHERE exam_id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
}

// Update exam if requested
if (isset($_POST['update_exam'])) {
    $exam_id   = $_POST['exam_id'];
    $title     = $_POST['title'];
    $subject   = $_POST['subject'];
    $start_time = $_POST['start_time'];
    $duration  = $_POST['duration'];

    $stmt = $conn->prepare("UPDATE exams SET title=?, subject=?, start_time=?, duration=? WHERE exam_id=?");
    $stmt->bind_param("sssii", $title, $subject, $start_time, $duration, $exam_id);
    $stmt->execute();
}

// Get teacher ID
$teacher_id = $_SESSION['teacher_id'] ?? null;

// Fetch exams
$stmt = $conn->prepare("SELECT exam_id, title, subject, start_time, duration FROM exams WHERE teacher_id = ? ORDER BY exam_id DESC");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Exams</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9fafb;
            padding: 40px;
        }

        h1 {
            color: #1e3a8a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background-color: #1e3a8a;
            color: white;
        }

        tr:hover {
            background-color: #f3f4f6;
        }

        form {
            display: inline;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
        }

        .delete-btn {
            background-color: #ef4444;
        }

        .delete-btn:hover {
            background-color: #dc2626;
        }

        .edit-btn {
            background-color: #3b82f6;
        }

        .edit-btn:hover {
            background-color: #2563eb;
        }

        .edit-form {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .edit-form input {
            display: block;
            margin-bottom: 12px;
            padding: 8px;
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }

        .save-btn {
            background-color: #10b981;
        }

        .save-btn:hover {
            background-color: #059669;
        }
    </style>
</head>
<body>

    <h1>My Exams</h1>

    <table>
        <tr>
            <th>Exam ID</th>
            <th>Title</th>
            <th>Subject</th>
            <th>Start Time</th>
            <th>Duration</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['exam_id']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td><?= htmlspecialchars($row['start_time']) ?></td>
                <td><?= htmlspecialchars($row['duration']) ?> mins</td>
                <td>
                    <!-- Edit button -->
                    <form method="post">
                        <input type="hidden" name="edit_exam" value="<?= $row['exam_id'] ?>">
                        <button type="submit" class="btn edit-btn">Edit</button>
                    </form>

                    <!-- Delete button -->
                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this exam?');">
                        <input type="hidden" name="exam_id" value="<?= $row['exam_id'] ?>">
                        <button type="submit" name="delete_exam" class="btn delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <?php
    // Show edit form if requested
    if (isset($_POST['edit_exam'])) {
        $edit_id = $_POST['edit_exam'];
        $stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $exam = $stmt->get_result()->fetch_assoc();
    ?>
        <div class="edit-form">
            <h2>Edit Exam</h2>
            <form method="post">
                <input type="hidden" name="exam_id" value="<?= $exam['exam_id'] ?>">
                <input type="text" name="title" value="<?= htmlspecialchars($exam['title']) ?>" required>
                <input type="text" name="subject" value="<?= htmlspecialchars($exam['subject']) ?>" required>
                <input type="datetime-local" name="start_time" value="<?= date('Y-m-d\TH:i', strtotime($exam['start_time'])) ?>" required>
                <input type="number" name="duration" value="<?= htmlspecialchars($exam['duration']) ?>" required>
                <button type="submit" name="update_exam" class="btn save-btn">Save Changes</button>
            </form>
        </div>
    <?php } ?>

</body>
</html>