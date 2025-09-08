<?php
session_start();
include("includes/db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check for existing email
    if ($role == 'teacher') {
        $check = mysqli_query($conn, "SELECT * FROM teachers WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $message = "<p class='error'>Email already exists for teacher.</p>";
        } else {
            $query = "INSERT INTO teachers (name, email, password) VALUES ('$name', '$email', '$password')";
            if (mysqli_query($conn, $query)) {
                $message = "<p class='success'>Teacher registered successfully! <a href='index.php'>Login</a></p>";
            } else {
                $message = "<p class='error'>Error in registration.</p>";
            }
        }
    } elseif ($role == 'student') {
        $check = mysqli_query($conn, "SELECT * FROM students WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $message = "<p class='error'>Email already exists for student.</p>";
        } else {
            $query = "INSERT INTO students (name, email, password) VALUES ('$name', '$email', '$password')";
            if (mysqli_query($conn, $query)) {
                $message = "<p class='success'>Student registered successfully! <a href='index.php'>Login</a></p>";
            } else {
                $message = "<p class='error'>Error in registration.</p>";
            }
        }
    } else {
        $message = "<p class='error'>Invalid role selected.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register - Online Exam System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- match login typography -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    /* ====== Global / Matches the login page you liked ====== */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

    body {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #667eea, #764ba2); /* same bg as login */
      overflow: hidden;
    }

    /* Glass container (same as login) */
    .container {
      display: flex;
      width: 80%;
      max-width: 1100px;
      background: rgba(255,255,255,0.1);
      border-radius: 18px;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.2);
      overflow: hidden;
    }

    /* Left branding panel (same gradient/colors as login) */
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
      max-width: 420px;
      line-height: 1.6;
      opacity: 0.9;
    }

    /* Right form panel (same spacing as login) */
    .right-panel {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }

    /* Inner card (same as login-box) */
    .form-card {
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
      to   { opacity: 1; transform: translateY(0); }
    }

    .form-card h2 {
      text-align: center;
      color: #1e3c72; /* same heading color as login */
      margin-bottom: 25px;
      font-size: 26px;
      font-weight: 600;
    }

    .form-control { margin-bottom: 18px; }

    select,
    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      background: #f9f9f9;
      transition: 0.3s;
    }
    select:focus,
    input:focus {
      border-color: #667eea; /* same focus as login */
      box-shadow: 0 0 6px rgba(102,126,234,0.4);
      background: #fff;
      outline: none;
    }

    button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #667eea, #764ba2); /* same button gradient */
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
      color: #5a67d8; /* same link color as login */
      text-decoration: none;
      font-weight: 600;
    }
    .link a:hover { text-decoration: underline; }

    /* Messages (match login styles) */
    .error {
      color: #d32f2f;
      background: #ffcdd2;
      padding: 12px;
      text-align: center;
      border-radius: 6px;
      margin-bottom: 18px;
      font-size: 14px;
      font-weight: 600;
    }
    .success {
      color: #2e7d32;
      background: #c8e6c9;
      padding: 12px;
      text-align: center;
      border-radius: 6px;
      margin-bottom: 18px;
      font-size: 14px;
      font-weight: 600;
    }

    /* Responsive (same breakpoint/behavior as login) */
    @media (max-width: 900px) {
      .container { flex-direction: column; width: 90%; }
      .left-panel { padding: 30px; }
      .left-panel h1 { font-size: 30px; }
      .right-panel { padding: 20px; }
    }
  </style>
</head>
<body>

  <div class="container">
    <!-- Left branding (mirrors login) -->
    <div class="left-panel">
      <h1>📘 Online Examination</h1>
      <p>Create your account to start taking exams, track scores, and access your dashboard anytime.</p>
    </div>

    <!-- Right form (mirrors login layout & spacing) -->
    <div class="right-panel">
      <div class="form-card">
        <h2>Create Your Account</h2>
        <?= $message ?>
        <form method="POST">
          <div class="form-control">
            <select name="role" required>
              <option value="">👤 Select Role</option>
              <option value="teacher">📘 Teacher</option>
              <option value="student">🎓 Student</option>
            </select>
          </div>
          <div class="form-control">
            <input type="text" name="name" placeholder="Full Name" required>
          </div>
          <div class="form-control">
            <input type="email" name="email" placeholder="Email Address" required>
          </div>
          <div class="form-control">
            <input type="password" name="password" placeholder="Password" required>
          </div>
          <button type="submit">Register</button>
          <div class="link">
            Already registered? <a href="index.php">Login here</a>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
