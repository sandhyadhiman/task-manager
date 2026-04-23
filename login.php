<?php
session_start();
include("db.php");

if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['password'])){
            
            // ✅ SESSION SET
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username']; // Name display karne ke liye

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
        $error = "❌ User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Task Manager</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #141e30, #243b55);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .box {
            width: 350px;
            background: white;
            padding: 35px;
            text-align: center;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }

        h2 { color: #243b55; margin-bottom: 25px; }

        input {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
            outline: none;
        }

        button {
            padding: 12px;
            width: 100%;
            background: #243b55;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 15px;
        }

        button:hover { background: #1b2a40; }

        .error {
            color: #b91c1c;
            background: #fee2e2;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .footer-link {
            margin-top: 20px;
            display: block;
            text-decoration: none;
            color: #243b55;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>🔐 Login</h2>

    <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <a href="registration.php" class="footer-link">🆕 Create New Account</a>
</div>

</body>
</html>