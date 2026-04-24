<?php
session_start();
include("db.php");

if(!isset($_SESSION['email_verified'])) {
    header("Location: registration.php");
    exit();
}

if(isset($_POST['register_final'])){
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_SESSION['temp_reg']['name'];
    $email = $_SESSION['temp_reg']['email'];

    $query = "INSERT INTO users (username, email, password) VALUES ('$name', '$email', '$password')";
    
    if(mysqli_query($conn, $query)){
        // Registration complete, session clear karein
        session_unset();
        session_destroy();
        echo "<script>alert('Account Created Successfully!'); window.location='login.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Password | Task Manager</title>
    <style>
        /* Styles same as above */
        :root { --primary: #6366f1; --bg-dark: #0f172a; --card-bg: rgba(30, 41, 59, 0.7); }
        body { font-family: sans-serif; background: var(--bg-dark); color: white; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .container { width: 380px; background: var(--card-bg); padding: 40px; border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.1); text-align: center; }
        input { width: 100%; padding: 14px; margin: 10px 0; background: #1e293b; border: 1px solid #334155; border-radius: 12px; color: white; box-sizing: border-box; }
        button { width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>🛡️ Set Password</h2>
    <p style="color: #94a3b8;">Create a strong password for your account.</p>
    <form method="POST">
        <input type="password" name="password" placeholder="Enter Password" required minlength="6">
        <button type="submit" name="register_final">Create Account</button>
    </form>
</div>
</body>
</html>