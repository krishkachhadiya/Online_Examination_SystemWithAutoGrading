<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


// Add Exam
if (isset($_POST['add_exam'])) {
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $teacher_id = $_POST['teacher_id'];
    $duration = $_POST['duration'];
    $start_time = $_POST['start_time'];

    $stmt = $conn->prepare("INSERT INTO exams (title, subject, teacher_id, duration, start_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $title, $subject, $teacher_id, $duration, $start_time);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_exams.php");
    exit;
}

// Delete Exam
if (isset($_GET['delete'])) {
    $exam_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM exams WHERE exam_id=?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_exams.php");
    exit;
}

// Edit Exam
$edit_exam = null;
if (isset($_GET['edit'])) {
    $exam_id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id");
    $edit_exam = $result->fetch_assoc();
}

if (isset($_POST['update_exam'])) {
    $exam_id = $_POST['exam_id'];
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $teacher_id = $_POST['teacher_id'];
    $duration = $_POST['duration'];
    $start_time = $_POST['start_time'];

    $stmt = $conn->prepare("UPDATE exams SET title=?, subject=?, teacher_id=?, duration=?, start_time=? WHERE exam_id=?");
    $stmt->bind_param("ssissi", $title, $subject, $teacher_id, $duration, $start_time, $exam_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_exams.php");
    exit;
}

// Fetch Exams & Teachers
$exam_result = $conn->query("SELECT * FROM exams");
$teachers = $conn->query("SELECT id, name FROM teachers");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Exams - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }

        body { display: flex; height: 100vh; background: linear-gradient(135deg, #eef2ff, #f0f9ff); color: #1e293b; }

        
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e3a8a, #2563eb);
            color: white;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            border-top-right-radius: 30px;
            border-bottom-right-radius: 30px;
            box-shadow: 6px 0 18px rgba(0, 0, 0, 0.2);
        }
        .sidebar h2 {
            font-size: 26px;
            margin-bottom: 45px;
            text-align: center;
            color: #bfdbfe;
            font-weight: bold;
        }
        .sidebar a {
            text-decoration: none;
            color: white;
            padding: 14px 18px;
            margin: 8px 0;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: block;
        }
        .sidebar a:hover { background: rgba(255,255,255,0.25); transform: translateX(8px); }
        .sidebar a.active { background: rgba(255,255,255,0.3); font-weight: bold; }

        
        .main { flex: 1; padding: 40px; overflow-y: auto; }

        
        .topbar {
            background: white;
            padding: 18px 30px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .topbar h1 { font-size: 26px; color: #1d4ed8; font-weight: bold; }
        .logout-btn {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
        }
        .logout-btn:hover { background: linear-gradient(90deg, #dc2626, #b91c1c); }

        .card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .card h3 { margin-bottom: 20px; color: #1e3a8a; }

        input, select {
            padding: 12px;
            margin: 8px 0;
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
        }
        button {
            background: #2563eb;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover { background: #1e40af; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; }
        th { background: #1e3a8a; color: white; }
        tr:nth-child(even) { background: #f9fafb; }
        td a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            margin-right: 10px;
        }
        td a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="manage_teachers.php">👨‍🏫 Manage Teachers</a>
        <a href="manage_students.php">👨‍🎓 Manage Students</a>
        <a href="manage_exams.php" class="active">📝 Manage Exams</a>
        <a href="view_results.php">📊 View Results</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <div class="main">
        <div class="topbar">
            <h1>Manage Exams</h1>
            <a class="logout-btn" href="../logout.php">Logout</a>
        </div>

        <div class="card">
            <h3><?= $edit_exam ? "✏️ Edit Exam" : "➕ Add Exam" ?></h3>
            <form method="POST">
                <input type="hidden" name="exam_id" value="<?= $edit_exam['exam_id'] ?? '' ?>">
                <label>Title</label>
                <input type="text" name="title" value="<?= $edit_exam['title'] ?? '' ?>" required>
                <label>Subject</label>
                <input type="text" name="subject" value="<?= $edit_exam['subject'] ?? '' ?>" required>
                <label>Teacher</label>
                <select name="teacher_id" required>
                    <option value="">Select Teacher</option>
                    <?php while ($teacher = $teachers->fetch_assoc()): ?>
                        <option value="<?= $teacher['id'] ?>" <?= ($edit_exam && $edit_exam['teacher_id'] == $teacher['id']) ? 'selected' : '' ?>>
                            <?= $teacher['name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <label>Duration (minutes)</label>
                <input type="number" name="duration" value="<?= $edit_exam['duration'] ?? '' ?>" required>
                <label>Start Time</label>
                <input type="datetime-local" name="start_time" value="<?= $edit_exam ? date('Y-m-d\TH:i', strtotime($edit_exam['start_time'])) : '' ?>" required>
                <button type="submit" name="<?= $edit_exam ? 'update_exam' : 'add_exam' ?>">
                    <?= $edit_exam ? 'Update Exam' : 'Add Exam' ?>
                </button>
            </form>
        </div>

        <div class="card">
            <h3>📋 Exam List</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Teacher ID</th>
                    <th>Duration</th>
                    <th>Start Time</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $exam_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['exam_id'] ?></td>
                    <td><?= $row['title'] ?></td>
                    <td><?= $row['subject'] ?></td>
                    <td><?= $row['teacher_id'] ?></td>
                    <td><?= $row['duration'] ?> mins</td>
                    <td><?= $row['start_time'] ?></td>
                    <td>
                        <a href="manage_exams.php?edit=<?= $row['exam_id'] ?>">✏️ Edit</a>
                        <a href="manage_exams.php?delete=<?= $row['exam_id'] ?>" onclick="return confirm('Are you sure?')">🗑 Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
