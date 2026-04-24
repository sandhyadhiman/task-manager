<?php
session_start();
if(!isset($_SESSION['temp_reg'])) {
    header("Location: registration.php");
    exit();
}

$msg = "";
if(isset($_POST['verify'])){
    $user_otp = $_POST['otp'];
    if($user_otp == $_SESSION['temp_reg']['otp']){
        $_SESSION['email_verified'] = true;
        header("Location: create_password.php");
        exit();
    } else {
        $msg = "❌ Invalid OTP! Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP | Task Manager</title>
    <style>
        :root { --primary: #6366f1; --bg-dark: #0f172a; --card-bg: rgba(30, 41, 59, 0.7); }
        body { font-family: sans-serif; background: var(--bg-dark); color: white; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .container { width: 380px; background: var(--card-bg); padding: 40px; border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.1); text-align: center; }
        input { width: 100%; padding: 14px; margin: 10px 0; background: #1e293b; border: 1px solid #334155; border-radius: 12px; color: white; text-align: center; font-size: 20px; letter-spacing: 5px; }
        button { width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>
<div class="container">
    <h2>🔑 Verify OTP</h2>
    <p style="color: #94a3b8;">Enter code sent to: <br> <b><?php echo $_SESSION['temp_reg']['email']; ?></b></p>
    <?php if($msg != "") echo "<p style='color:#fca5a5;'>$msg</p>"; ?>
    <form method="POST">
        <input type="text" name="otp" placeholder="000000" maxlength="6" required>
        <button type="submit" name="verify">Verify & Continue</button>
    </form>
</div>
</body>
</html>