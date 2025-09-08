<?php
session_start();
include("../includes/db.php");


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}


// Handle exam creation
if (isset($_POST['add_exam'])) {
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration'];
    $teacher_email = $_SESSION['username'];

    // Get teacher ID
    $stmt = $conn->prepare("SELECT id FROM teachers WHERE email = ?");
    $stmt->bind_param("s", $teacher_email);
    $stmt->execute();
    $stmt->bind_result($teacher_id);
    $stmt->fetch();
    $stmt->close();

    // Insert exam
    $stmt = $conn->prepare("INSERT INTO exams (title, subject, start_time, duration, teacher_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $title, $subject, $start_time, $duration, $teacher_id);
    $stmt->execute();
    $stmt->close();

    $message = "Exam created successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Exam</title>
<style>
* {margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', sans-serif;}
body {display:flex; min-height:100vh; background: linear-gradient(135deg, #eef2ff, #f0f9ff); color:#1e293b;}

/* Sidebar */
.sidebar {
    width:260px; background: linear-gradient(180deg, #1e3a8a, #2563eb); color:white;
    padding:30px 20px; display:flex; flex-direction:column; border-top-right-radius:30px;
    border-bottom-right-radius:30px; box-shadow:6px 0 18px rgba(0,0,0,0.2);
}
.sidebar h2 {font-size:26px; margin-bottom:45px; text-align:center; color:#bfdbfe;}
.sidebar a {text-decoration:none; color:white; padding:14px 18px; margin:8px 0; border-radius:12px;
    font-size:16px; font-weight:500; display:flex; align-items:center; transition:0.3s;}
.sidebar a:hover {background: rgba(255,255,255,0.25); transform: translateX(8px) scale(1.03);}
.sidebar a.active {background: rgba(255,255,255,0.3); font-weight:bold;}

/* Main */
.main {flex:1; padding:40px; overflow-y:auto;}

/* Topbar */
.topbar {background: rgba(255,255,255,0.9); backdrop-filter: blur(12px);
    padding:18px 30px; border-radius:16px; box-shadow:0 8px 24px rgba(0,0,0,0.08);
    display:flex; justify-content:space-between; align-items:center;}
.topbar h1 {font-size:26px; color:#1d4ed8; font-weight:bold;}
.logout-btn {background: linear-gradient(90deg, #ef4444, #dc2626); color:white;
    text-decoration:none; padding:10px 18px; border-radius:10px; font-weight:bold;
    box-shadow:0 6px 12px rgba(239,68,68,0.4); transition:0.3s;}
.logout-btn:hover {background: linear-gradient(90deg, #dc2626, #b91c1c); transform: scale(1.07);}

/* Form Card */
.form-container {
    margin-top:40px; background: linear-gradient(135deg, #ffffff, #e0f2fe);
    padding:35px 30px; border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.15);
    max-width:600px;
}
.form-container h2 {margin-bottom:25px; color:#1e40af;}
.form-container label {display:block; margin:12px 0 6px; font-weight:600;}
.form-container input {width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:10px;}
.form-container button {margin-top:20px; padding:12px 20px; background: linear-gradient(135deg,#2563eb,#1e40af);
    color:white; border:none; border-radius:10px; cursor:pointer; font-weight:bold; transition:0.3s;}
.form-container button:hover {transform: scale(1.05);}

/* Success Message */
.success {margin-top:15px; color:green; font-weight:bold;}

@media (max-width:768px) {
    .sidebar {display:none;}
    body {flex-direction:column;}
    .main {padding:20px;}
}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Teacher Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="create_exam.php" class="active">➕ Create Exam</a>
    <a href="my_exams.php">📋 My Exams</a>
    <a href="add_question.php">➕ Add Question</a>
    <a href="view_results.php">📊 View Results</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
        <a class="logout-btn" href="../logout.php">Logout</a>
    </div>

    <div class="form-container">
        <h2>Create New Exam</h2>
        <form method="post">
            <label>Title</label>
            <input type="text" name="title" required>

            <label>Subject</label>
            <input type="text" name="subject" required>

            <label>Start Time</label>
            <input type="datetime-local" name="start_time" required>

            <label>Duration (minutes)</label>
            <input type="number" name="duration" min="1" required>

            <button type="submit" name="add_exam">Create Exam</button>
        </form>

        <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>
    </div>
</div>

</body>
</html>
