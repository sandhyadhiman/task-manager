<?php
session_start();
include("db.php");

// PHPMailer files include karein
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = "";
$msg_type = "";

if(isset($_POST['send_otp'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Check karein email pehle se toh nahi hai
    $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check_email) > 0){
        $msg = "❌ Email already registered!";
        $msg_type = "error";
    } else {
        // 1. Generate 6-digit OTP
        $otp = rand(100000, 999999);
        
        // 2. Data ko Session mein save karein
        $_SESSION['temp_reg'] = [
            'name' => $name,
            'email' => $email,
            'otp' => $otp
        ];

        // 3. SMTP Configuration
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sandhyadhimancba2003@gmail.com'; // Aapka Email
            $mail->Password   = 'shthcwtovoknjtro';          // Aapka App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender aur Receiver details
            $mail->setFrom('sandhyadhimancba2003@gmail.com', 'Task Manager');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification - OTP';
            $mail->Body    = "<h3>Hello $name,</h3>
                              <p>Your verification code for Task Manager is: <b style='font-size:20px; color:#6366f1;'>$otp</b></p>
                              <p>Please enter this code to verify your email.</p>";

            $mail->send();
            
            // Success! Redirection to next step
            header("Location: verify_otp.php");
            exit();

        } catch (Exception $e) {
            $msg = "❌ Mail Error: " . $mail->ErrorInfo;
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 1: Verify Email | Task Manager</title>
    <style>
        :root { --primary: #6366f1; --bg-dark: #0f172a; --card-bg: rgba(30, 41, 59, 0.7); }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg-dark); color: white; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .container { width: 380px; background: var(--card-bg); padding: 40px; border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.1); text-align: center; backdrop-filter: blur(10px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        h2 { margin-bottom: 20px; font-size: 24px; }
        input { width: 100%; padding: 14px; margin: 10px 0; background: #1e293b; border: 1px solid #334155; border-radius: 12px; color: white; box-sizing: border-box; outline: none; transition: 0.3s; }
        input:focus { border-color: var(--primary); }
        button { width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: bold; margin-top: 15px; font-size: 16px; transition: 0.3s; }
        button:hover { background: #4f46e5; transform: translateY(-2px); }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 10px; font-size: 14px; background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220, 38, 38, 0.3); }
    </style>
</head>
<body>
<div class="container">
    <h2>🚀 Register</h2>
    <?php if($msg != ""): ?>
        <div class="alert"><?php echo $msg; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <button type="submit" name="send_otp">Get OTP</button>
    </form>
    <p style="font-size: 13px; color: #94a3b8; margin-top: 20px;">We will send a 6-digit code to your email.</p>
</div>
</body>
</html>