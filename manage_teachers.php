<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


// Add teacher
if (isset($_POST['add_teacher'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("INSERT INTO teachers (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);
    $stmt->execute();
    $stmt->close();
}

// Update teacher
if (isset($_POST['update_teacher'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("UPDATE teachers SET name=?, email=?, password=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $password, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_teachers.php");
    exit;
}

// Delete teacher
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM teachers WHERE id = $id");
    header("Location: manage_teachers.php");
    exit;
}

// Fetch teacher to edit
$edit_teacher = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result_edit = $conn->query("SELECT * FROM teachers WHERE id=$id");
    $edit_teacher = $result_edit->fetch_assoc();
}

// Get all teachers
$result = $conn->query("SELECT * FROM teachers");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Teachers</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            display: flex;
            height: 100vh;
            background: linear-gradient(135deg, #eef2ff, #f0f9ff);
            color: #1e293b;
        }

        /* Sidebar */
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
            letter-spacing: 1px;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            padding: 14px 18px;
            margin: 8px 0;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(8px) scale(1.03);
        }

        .sidebar a.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: bold;
        }

        /* Main */
        .main {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        /* Topbar */
        .topbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            padding: 18px 30px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: fadeDown 0.6s ease;
        }

        .topbar h1 {
            font-size: 26px;
            color: #1d4ed8;
            font-weight: bold;
        }

        .logout-btn {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: bold;
            box-shadow: 0 6px 12px rgba(239,68,68,0.4);
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: linear-gradient(90deg, #dc2626, #b91c1c);
            transform: scale(1.07);
        }

        /* Section Title */
        h2 {
            margin: 40px 0 20px;
            font-size: 22px;
            font-weight: bold;
            color: #1d4ed8;
        }

        /* Form */
        form {
            background: white;
            padding: 25px 30px;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            max-width: 600px;
            animation: fadeUp 0.6s ease;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: 500;
            color: #334155;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
        }

        input[type="submit"] {
            margin-top: 25px;
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        input[type="submit"]:hover {
            transform: scale(1.05);
            background: linear-gradient(90deg, #1d4ed8, #1e40af);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            animation: fadeUp 0.6s ease;
        }

        th, td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #e0e7ff;
            color: #1e3a8a;
            font-size: 15px;
        }

        tr:hover {
            background: #f9fafb;
        }

        .delete-btn {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            color: white;
            padding: 8px 14px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background: linear-gradient(90deg, #dc2626, #b91c1c);
            transform: scale(1.05);
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            body { flex-direction: column; }
            .main { padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="manage_teachers.php" class="active">👨‍🏫 Manage Teachers</a>
    <a href="manage_students.php">👨‍🎓 Manage Students</a>
    <a href="manage_exams.php">📝 Manage Exams</a>
    <a href="view_results.php">📊 View Results</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">
    <div class="topbar">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
        <a class="logout-btn" href="../logout.php">Logout</a>
    </div>

    <?php if ($edit_teacher): ?>
        <h2>Edit Teacher</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit_teacher['id'] ?>">

            <label for="name">Teacher Name:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($edit_teacher['name']) ?>" required>

            <label for="email">Email ID:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($edit_teacher['email']) ?>" required>

            <label for="password">Password:</label>
            <input type="text" name="password" id="password" value="<?= htmlspecialchars($edit_teacher['password']) ?>" required>

            <input type="submit" name="update_teacher" value="Update Teacher">
            <a href="manage_teachers.php" style="margin-left:15px; color:#2563eb;">Cancel</a>
        </form>
    <?php else: ?>
        <h2>Add New Teacher</h2>
        <form method="POST">
            <label for="name">Teacher Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="email">Email ID:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <input type="submit" name="add_teacher" value="Add Teacher">
        </form>
    <?php endif; ?>

    <h2>All Teachers</h2>
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
                <a class="delete-btn" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this teacher?')">Delete</a>
                <a class="delete-btn" style="background:#2563eb;" href="?edit=<?= $row['id'] ?>">Edit</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
