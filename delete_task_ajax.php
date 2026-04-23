<?php
session_start();
include("db.php");

// Check agar user login hai aur ID mili hai
if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Sirf wahi task delete ho jo us user ka ho
    $sql = "DELETE FROM tasks WHERE id = '$id' AND user_id = '$user_id'";

    if (mysqli_query($conn, $sql)) {
        echo "Deleted";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Unauthorized access";
}
?>