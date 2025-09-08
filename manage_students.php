<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


// Add student
if (isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // plain text

    $stmt = $conn->prepare("INSERT INTO students (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);
    $stmt->execute();
    $stmt->close();
}

// Update student
if (isset($_POST['update_student'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // still plain text per your design

    $stmt = $conn->prepare("UPDATE students SET name=?, email=?, password=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $password, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_students.php");
    exit;
}

// Delete student
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM students WHERE id = $id");
    header("Location: manage_students.php");
    exit;
}

// Edit student (load for form)
$edit_student = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = $conn->query("SELECT * FROM students WHERE id=$id");
    $edit_student = $res->fetch_assoc();
}

// Fetch all students
$result = $conn->query("SELECT * FROM students");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <style>
        /* Same styling as before */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; min-height: 100vh; background: linear-gradient(135deg, #eef2ff, #f0f9ff); color: #1e293b; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #1e3a8a, #2563eb); color: white; padding: 30px 20px; display: flex; flex-direction: column; border-top-right-radius: 30px; border-bottom-right-radius: 30px; box-shadow: 6px 0 18px rgba(0, 0, 0, 0.2); }
        .sidebar h2 { font-size: 26px; margin-bottom: 45px; text-align: center; color: #bfdbfe; font-weight: bold; }
        .sidebar a { text-decoration: none; color: white; padding: 14px 18px; margin: 8px 0; border-radius: 12px; font-size: 16px; font-weight: 500; display: flex; align-items: center; transition: all 0.3s ease; }
        .sidebar a:hover { background: rgba(255, 255, 255, 0.25); transform: translateX(8px) scale(1.03); }
        .sidebar a.active { background: rgba(255, 255, 255, 0.3); font-weight: bold; }
        .main { flex: 1; padding: 40px; overflow-y: auto; }
        .topbar { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); padding: 18px 30px; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; }
        .topbar h1 { font-size: 26px; color: #1d4ed8; font-weight: bold; }
        .logout { background: linear-gradient(90deg, #ef4444, #dc2626); color: white; text-decoration: none; padding: 10px 18px; border-radius: 10px; font-weight: bold; box-shadow: 0 6px 12px rgba(239,68,68,0.4); transition: all 0.3s ease; }
        .logout:hover { background: linear-gradient(90deg, #dc2626, #b91c1c); transform: scale(1.07); }
        h2 { margin: 30px 0 15px; color: #1d4ed8; font-size: 22px; font-weight: bold; }
        form { background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); max-width: 600px; }
        label { display: block; margin: 12px 0 6px; font-weight: 500; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; }
        input[type="submit"] { margin-top: 20px; background: linear-gradient(90deg, #2563eb, #1e40af); color: #fff; border: none; padding: 12px 24px; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.3s ease; }
        input[type="submit"]:hover { background: linear-gradient(90deg, #1e40af, #1e3a8a); transform: scale(1.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #e0e7ff; color: #1e3a8a; font-weight: 600; }
        tr:hover { background: #f9fafb; }
        .delete-btn, .edit-btn { padding: 8px 14px; border-radius: 8px; font-size: 14px; text-decoration: none; transition: all 0.3s ease; color: #fff; }
        .delete-btn { background: linear-gradient(90deg, #ef4444, #dc2626); }
        .delete-btn:hover { background: linear-gradient(90deg, #dc2626, #b91c1c); transform: scale(1.05); }
        .edit-btn { background: linear-gradient(90deg, #3b82f6, #1d4ed8); margin-right: 6px; }
        .edit-btn:hover { background: linear-gradient(90deg, #1d4ed8, #1e3a8a); transform: scale(1.05); }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="manage_teachers.php">👨‍🏫 Manage Teachers</a>
        <a href="manage_students.php" class="active">👨‍🎓 Manage Students</a>
        <a href="manage_exams.php">📝 Manage Exams</a>
        <a href="view_results.php">📊 View Results</a>
        <a href="../logout.php">🚪 Logout</a>
    </div>

    <!-- Main -->
    <div class="main">
        <div class="topbar">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
            <a class="logout" href="../logout.php">Logout</a>
        </div>

        <h2><?= $edit_student ? "Edit Student (ID: {$edit_student['id']})" : "Add New Student" ?></h2>
        <form method="POST">
            <?php if ($edit_student): ?>
                <input type="hidden" name="id" value="<?= $edit_student['id'] ?>">
            <?php endif; ?>

            <label for="name">Student Name</label>
            <input type="text" name="name" id="name" required value="<?= $edit_student['name'] ?? '' ?>">

            <label for="email">Email ID</label>
            <input type="email" name="email" id="email" required value="<?= $edit_student['email'] ?? '' ?>">

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required value="<?= $edit_student['password'] ?? '' ?>">

            <?php if ($edit_student): ?>
                <input type="submit" name="update_student" value="Update Student">
                <a href="manage_students.php" style="margin-left:10px; color:#ef4444; font-weight:bold;">Cancel</a>
            <?php else: ?>
                <input type="submit" name="add_student" value="Add Student">
            <?php endif; ?>
        </form>

        <h2>All Students</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Password</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['password']) ?></td>
                <td>
                    <a class="edit-btn" href="?edit=<?= $row['id'] ?>">Edit</a>
                    <a class="delete-btn" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this student?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>
</html>
