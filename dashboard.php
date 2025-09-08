    <?php
    session_start();
    include("../includes/db.php");

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }

include("../includes/db.php");

    // Count from existing tables (do not rename)
    $teacherCount = $conn->query("SELECT COUNT(*) as total FROM teachers")->fetch_assoc()['total'];
    $studentCount = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
    $examCount = $conn->query("SELECT COUNT(*) as total FROM exams")->fetch_assoc()['total'];
    $resultCount = $conn->query("SELECT COUNT(*) as total FROM results")->fetch_assoc()['total'];
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Admin Dashboard</title>
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

            /* Cards Grid */
            .cards {
                margin-top: 50px;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 28px;
            }

            .card {
                padding: 35px 25px;
                border-radius: 20px;
                text-align: center;
                color: white;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                animation: fadeUp 0.6s ease;
            }

            .card:hover {
                transform: translateY(-10px) scale(1.02);
                box-shadow: 0 14px 32px rgba(0,0,0,0.2);
            }

            .card h3 {
                font-size: 18px;
                margin-bottom: 16px;
                font-weight: 600;
            }

            .card p {
                font-size: 36px;
                font-weight: bold;
            }

            /* Colorful Cards */
            .teachers { background: linear-gradient(135deg, #3b82f6, #1e40af); }
            .students { background: linear-gradient(135deg, #10b981, #065f46); }
            .exams { background: linear-gradient(135deg, #f59e0b, #b45309); }
            .results { background: linear-gradient(135deg, #8b5cf6, #5b21b6); }

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
                .sidebar {
                    display: none;
                }
                body {
                    flex-direction: column;
                }
                .main {
                    padding: 20px;
                }
            }
        </style>
    </head>
    <body>

        <div class="sidebar">
            <h2>Admin Panel</h2>
            <a href="dashboard.php" class="active">🏠 Dashboard</a>
            <a href="manage_teachers.php">👨‍🏫 Manage Teachers</a>
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

            <div class="cards">
                <div class="card teachers">
                    <h3>Total Teachers</h3>
                    <p><?= $teacherCount ?></p>
                </div>
                <div class="card students">
                    <h3>Total Students</h3>
                    <p><?= $studentCount ?></p>
                </div>
                <div class="card exams">
                    <h3>Total Exams</h3>
                    <p><?= $examCount ?></p>
                </div>
                <div class="card results">
                    <h3>Total Results</h3>
                    <p><?= $resultCount ?></p>
                </div>
            </div>
        </div>

    </body>
    </html>
