<?php
session_start();
include("includes/db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($email) || empty($password) || empty($role)) {
        $message = "<p class='error'>All fields are required!</p>";
    } else {
        $table = "";

        if ($role === "admin") $table = "admins";
        elseif ($role === "teacher") $table = "teachers";
        elseif ($role === "student") $table = "students";

        if ($table) {
            $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ? AND password = ?");
            $stmt->bind_param("ss", $email, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $_SESSION['role'] = $role;

                if ($role === "teacher") {
                    $_SESSION['teacher_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'] ?? $user['email'];
                    header("Location: teachers/dashboard.php");
                } elseif ($role === "student") {
                    $_SESSION['student_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'] ?? $user['email'];
                    header("Location: students/dashboard.php");
                } elseif ($role === "admin") {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'] ?? $user['email'];
                    header("Location: admin/dashboard.php");
                }
                exit;
            } else {
                $message = "<p class='error'>Invalid credentials!</p>";
            }
            $stmt->close();
        } else {
            $message = "<p class='error'>Please select a valid role.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Online Examination System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            overflow: hidden;
        }

        /* Container split */
        .container {
            display: flex;
            width: 80%;
            max-width: 1100px;
            background: rgba(255,255,255,0.1);
            border-radius: 18px;
            backdrop-filter: blur(10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        /* Left branding */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 50px;
            text-align: center;
        }

        .left-panel h1 {
            font-size: 40px;
            margin-bottom: 20px;
        }

        .left-panel p {
            font-size: 16px;
            max-width: 400px;
            line-height: 1.6;
            opacity: 0.9;
        }

        /* Right panel (form) */
        .right-panel {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .login-box {
            background: rgba(255,255,255,0.85);
            border-radius: 14px;
            padding: 40px 35px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-box h2 {
            text-align: center;
            color: #1e3c72;
            margin-bottom: 25px;
            font-size: 26px;
            font-weight: 600;
        }

        .form-control { margin-bottom: 18px; }

        select, input[type="text"], input[type="password"] {
            width: 100%;
            padding: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            background: #f9f9f9;
            transition: 0.3s;
        }

        select:focus, input:focus {
            border-color: #667eea;
            box-shadow: 0 0 6px rgba(102,126,234,0.4);
            background: #fff;
            outline: none;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: linear-gradient(135deg, #5a67d8, #6b46c1);
            transform: translateY(-2px);
        }

        .link {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }

        .link a {
            color: #5a67d8;
            text-decoration: none;
            font-weight: 600;
        }

        .link a:hover { text-decoration: underline; }

        .error {
            color: #d32f2f;
            background: #ffcdd2;
            padding: 12px;
            text-align: center;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        /* Responsive */
        @media(max-width: 900px) {
            .container { flex-direction: column; width: 90%; }
            .left-panel { padding: 30px; }
            .left-panel h1 { font-size: 30px; }
            .right-panel { padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Branding Side -->
        <div class="left-panel">
            <h1>📘 Online Examination</h1>
            <p>Test your knowledge anytime, anywhere.  
            Secure, fast, and reliable platform for students, teachers, and admins.</p>
        </div>

        <!-- Login Form Side -->
        <div class="right-panel">
            <div class="login-box">
                <h2>Login to Continue</h2>
                <?= $message ?>
                <form method="POST">
                    <div class="form-control">
                        <select name="role" required>
                            <option value="">👤 Select Role</option>
                            <option value="admin">🛡 Admin</option>
                            <option value="teacher">📘 Teacher</option>
                            <option value="student">🎓 Student</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <input type="text" name="email" placeholder="Enter Email Address" required>
                    </div>
                    <div class="form-control">
                        <input type="password" name="password" placeholder="Enter Password" required>
                    </div>
                    <button type="submit">Login</button>
                    <div class="link">
                        Don’t have an account? <a href="register.php">Register here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
