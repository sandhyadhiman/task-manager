<?php
include("db.php");

$msg = "";
$msg_type = "";

if(isset($_POST['register'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';

    // ⚠️ Check: SQL mein column 'username' hai ya 'name'
    $query = "INSERT INTO users (username, email, password, role) VALUES ('$name', '$email', '$pass', '$role')";

    if(mysqli_query($conn, $query)){
        $msg = "✅ Account Created Successfully! Now you can Login.";
        $msg_type = "success";
    } else {
        $msg = "❌ Error: " . mysqli_error($conn);
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Task Manager</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #141e30; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .container { width: 350px; background: white; padding: 30px; border-radius: 12px; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
        input { width: 90%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid #ccc; outline: none; }
        button { width: 100%; padding: 12px; background: #243b55; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .alert { padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
        .success { color: #15803d; background: #dcfce7; }
        .error { color: #b91c1c; background: #fee2e2; }
        a { text-decoration: none; color: #243b55; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2> Register</h2>

    <?php if($msg != ""): ?>
        <div class="alert <?php echo $msg_type; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="POST" action="registration.php">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Create Password" required>
        <button type="submit" name="register">Register</button>
    </form>

    <br>
    <a href="login.php"> Already have account? Login</a>
</div>

</body>
</html>