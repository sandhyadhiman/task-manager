<?php
include("db.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Form se data nikalna
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    
    // IMPORTANT: Status ko $_POST se catch karna
    // Agar form se status nahi aaya toh hi 'Pending' default rakhenge
    $status = (isset($_POST['status']) && !empty($_POST['status'])) ? mysqli_real_escape_string($conn, $_POST['status']) : 'Pending';

    // File Upload logic
    $file_name = "";
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = time() . "_" . $_FILES['file']['name'];
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        move_uploaded_file($_FILES['file']['tmp_name'], "uploads/" . $file_name);
    }

    // QUERY MEIN STATUS COLUMN ADD KIYA HAI
    $sql = "INSERT INTO tasks (user_id, title, category, priority, status, due_date, file) 
            VALUES ('$user_id', '$title', '$category', '$priority', '$status', '$due_date', '$file_name')";

    if (mysqli_query($conn, $sql)) {
        echo "Success";
    } else {
        // Agar error aaye toh display karega
        echo "Database Error: " . mysqli_error($conn);
    }
} else {
    echo "Direct access not allowed or Session expired";
}
?>

