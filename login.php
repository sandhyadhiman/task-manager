<?php
session_start();
include("db.php");

if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // 🔄 UPDATE: Humne status='active' wali condition add kar di hai
    $query = "SELECT * FROM users WHERE email='$email' AND status='active'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['password'])){
            
            // ✅ SESSION SET
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            // 🔥 ROLE BASED REDIRECT
            if($user['role'] == 'admin'){
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();

        } else {
            $error = "❌ Wrong Password!";
        }
    } else {
        // Agar user ka status 'deleted' hai, toh wo yahan aayega
        $error = "❌ Account not found or deactivated!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Task Manager</title>
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-light: #f8fafc;
        }

        body {
            margin: 0;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            width: 300px; height: 300px;
            background: var(--primary);
            filter: blur(100px);
            border-radius: 50%;
            top: 10%; left: 10%;
            opacity: 0.2;
            z-index: -1;
        }

        .box {
            width: 380px;
            background: var(--card-bg);
            padding: 40px;
            text-align: center;
            border-radius: 24px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 { 
            color: white; 
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        p.subtitle {
            color: #94a3b8;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .input-group {
            text-align: left;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 6px;
            margin-left: 4px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid #334155;
            border-radius: 12px;
            color: white;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
            background: rgba(15, 23, 42, 0.8);
        }

        button {
            padding: 14px;
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            margin-top: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4);
        }

        button:hover { 
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.5);
        }

        button:active { transform: translateY(0); }

        .error {
            color: #fca5a5;
            background: rgba(127, 29, 29, 0.3);
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            border: 1px solid rgba(220, 38, 38, 0.2);
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .footer-link {
            margin-top: 25px;
            display: inline-block;
            text-decoration: none;
            color: #94a3b8;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s;
        }

        .footer-link:hover {
            color: var(--primary);
        }

        b { color: var(--primary); }
    </style>
</head>
<body>

<div class="box">
    <h2>🔐 Login</h2>
    <p class="subtitle">Please enter your credentials to continue</p>

    <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST">
        <div class="input-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="name@company.com" required>
        </div>
        
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" name="login">Sign In</button>
    </form>

    <a href="registration.php" class="footer-link">Don't have an account? <b>Create Account</b></a>
</div>

</body>
</html>