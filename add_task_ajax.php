<?php
session_start();
include("db.php");

// 1. Check if user is logged in
if(!isset($_SESSION['user_id'])){
    echo "Error: Session expired. Please login again.";
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Get data from POST
$title = mysqli_real_escape_string($conn, $_POST['title']);
$category = mysqli_real_escape_string($conn, $_POST['category']);
$priority = mysqli_real_escape_string($conn, $_POST['priority']);
$due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
$status = "Pending"; // Default status for new tasks

// 3. File Upload Logic
$file_name = "";
if(isset($_FILES['file']) && $_FILES['file']['error'] == 0){
    // Uploads folder check
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    $file_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $file_name = time() . "_" . uniqid() . "." . $file_ext; // Unique name
    $target_path = "uploads/" . $file_name;
    
    move_uploaded_file($_FILES['file']['tmp_name'], $target_path);
}

// 4. INSERT Query (Sabse important: user_id zaroor hona chahiye)
$query = "INSERT INTO tasks (user_id, title, category, priority, due_date, status, file) 
          VALUES ('$user_id', '$title', '$category', '$priority', '$due_date', '$status', '$file_name')";

if(mysqli_query($conn, $query)){
    echo "Success"; 
} else {
    // Agar database mein error aaye toh ye error dikhayega
    echo "Database Error: " . mysqli_error($conn);
}
?>