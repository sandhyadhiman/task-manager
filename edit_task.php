<?php
session_start();
include("db.php");

// 1. Login aur ID Check
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: dashboard.php");
    exit();
}

$task_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// 2. Task ka purana data nikalna
$query = "SELECT * FROM tasks WHERE id='$task_id' AND user_id='$user_id'";
$result = mysqli_query($conn, $query);
$task = mysqli_fetch_assoc($result);

if(!$task){
    die("Task nahi mila ya aapke paas permission nahi hai.");
}

// 3. Update Logic (Jab user 'Update' button dabaye)
if(isset($_POST['update_task'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);

    // Purani file ka naam backup rakhte hain agar nayi file nahi aayi toh
    $file_name = $task['file']; 

    if(!empty($_FILES['file']['name'])){
        $file_name = time() . "_" . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], "uploads/" . $file_name);
    }

    $update_query = "UPDATE tasks SET 
        title='$title', 
        description='$description', 
        category='$category', 
        priority='$priority', 
        status='$status', 
        due_date='$due_date', 
        file='$file_name' 
        WHERE id='$task_id' AND user_id='$user_id'";

    if(mysqli_query($conn, $update_query)){
        echo "<script>alert('Task updated successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task | Task Manager</title>
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --accent: #3b82f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: white; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin:0; }
        .edit-card { background: var(--card); padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; border: 1px solid #334155; }
        h2 { margin-top: 0; color: var(--accent); }
        label { display: block; margin: 10px 0 5px; font-size: 14px; color: #94a3b8; }
        input, select, textarea { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #475569; background: #334155; color: white; box-sizing: border-box; margin-bottom: 10px; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        button { flex: 1; padding: 12px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; }
        .save-btn { background: #22c55e; color: white; }
        .back-btn { background: #64748b; color: white; text-decoration: none; text-align: center; line-height: 40px; display: block; border-radius: 8px; }
    </style>
</head>
<body>

<div class="edit-card">
    <h2>✏️ Edit Task</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>

        <label>Description</label>
        <textarea name="description" rows="3"><?php echo htmlspecialchars($task['description']); ?></textarea>

        <div style="display: flex; gap: 10px;">
            <div style="flex: 1;">
                <label>Category</label>
                <input type="text" name="category" value="<?php echo htmlspecialchars($task['category']); ?>">
            </div>
            <div style="flex: 1;">
                <label>Priority</label>
                <select name="priority">
                    <option <?php if($task['priority']=='Low') echo 'selected'; ?>>Low</option>
                    <option <?php if($task['priority']=='Medium') echo 'selected'; ?>>Medium</option>
                    <option <?php if($task['priority']=='High') echo 'selected'; ?>>High</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <div style="flex: 1;">
                <label>Status</label>
                <select name="status">
                    <option <?php if($task['status']=='Pending') echo 'selected'; ?>>Pending</option>
                    <option <?php if($task['status']=='In Progress') echo 'selected'; ?>>In Progress</option>
                    <option <?php if($task['status']=='Completed') echo 'selected'; ?>>Completed</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label>Due Date</label>
                <input type="date" name="due_date" value="<?php echo $task['due_date']; ?>" required>
            </div>
        </div>

        <label>Attachment (Blank )</label>
        <input type="file" name="file">
        <?php if(!empty($task['file'])): ?>
            <small style="color: #38bdf8;">Current file: <?php echo $task['file']; ?></small>
        <?php endif; ?>

        <div class="btn-group">
            <button type="submit" name="update_task" class="save-btn">Update Changes</button>
            <a href="dashboard.php" class="back-btn">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>