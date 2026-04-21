<?php
session_start();
include("db.php");

/** * 1. 🔐 Security Guard 
 * Check if the user is logged in and if their role is exactly 'admin'
 */
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

/** * 2. 💬 Update Feedback Logic
 * This handles the "Save Feedback" button click
 */
if(isset($_POST['submit_feedback'])){
    $task_id = (int)$_POST['task_id']; 
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    
    $update_query = "UPDATE tasks SET admin_feedback='$feedback' WHERE id=$task_id";
    if(mysqli_query($conn, $update_query)){
        // Explicitly redirecting back to admin.php to avoid 404 errors
        echo "<script>alert('Feedback updated successfully!'); window.location.href='admin.php';</script>";
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

/** * 3. ❌ Delete Task Logic
 */
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM tasks WHERE id=$id");
    header("Location: admin.php");
    exit();
}

/** * 4. 👥 Data Fetching 
 * LEFT JOIN helps prevent errors if a task exists but its user was deleted
 */
$users_res = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
$tasks_query = "SELECT tasks.*, users.username 
                FROM tasks 
                LEFT JOIN users ON tasks.user_id = users.id 
                ORDER BY tasks.id DESC";
$tasks_res = mysqli_query($conn, $tasks_query);

if (!$tasks_res) {
    die("Database Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Task Manager</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: #f1f5f9; color: #1e293b; }
        .header { background: #0f172a; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .logout { background: #ef4444; color: white; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; transition: 0.3s; }
        .logout:hover { background: #dc2626; }
        .container { width: 95%; max-width: 1200px; margin: 30px auto; }
        .card { background: white; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; }
        h3 { margin-top: 0; color: #334155; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #1e293b; color: white; padding: 14px; text-align: left; font-size: 14px; }
        td { padding: 14px; border-bottom: 1px solid #f1f5f9; font-size: 15px; }
        .pending { color: #d97706; background: #fef3c7; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        .completed { color: #059669; background: #d1fae5; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        .delete { color: #ef4444; text-decoration: none; font-weight: bold; }
        textarea { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-family: inherit; resize: vertical; }
        .btn-sm { background: #2563eb; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 5px; }
        .btn-sm:hover { background: #1d4ed8; }
    </style>
</head>
<body>

<div class="header">
    <h2>👑 Admin Portal</h2>
    <div>
        <span style="margin-right: 20px;">Administrator Mode</span>
        <a class="logout" href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <div class="card">
        <h3>👥 Registered Users</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = mysqli_fetch_assoc($users_res)) { ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['username'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><b style="color:#6366f1;"><?php echo strtoupper($u['role']); ?></b></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>📋 Manage Tasks & Provide Feedback</h3>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Task Title</th>
                    <th>Status</th>
                    <th>Feedback Management</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(mysqli_num_rows($tasks_res) > 0) {
                    while($t = mysqli_fetch_assoc($tasks_res)) { ?>
                <tr>
                    <td><b><?php echo htmlspecialchars($t['username'] ?? 'Unknown User'); ?></b></td>
                    <td><?php echo htmlspecialchars($t['title']); ?></td>
                    <td>
                        <span class="<?php echo ($t['status']=='Completed') ? 'completed' : 'pending'; ?>">
                            <?php echo $t['status']; ?>
                        </span>
                    </td>
                    <td width="35%">
                        <form method="POST" action="admin.php" style="display:flex; flex-direction: column; gap:5px;">
                            <input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">
                            <textarea name="feedback" rows="2" placeholder="Write feedback..."><?php echo htmlspecialchars($t['admin_feedback'] ?? ''); ?></textarea>
                            <button type="submit" name="submit_feedback" class="btn-sm">Save Feedback</button>
                        </form>
                    </td>
                    <td>
                        <a class="delete" href="?delete=<?php echo $t['id']; ?>" 
                           onclick="return confirm('Delete this task permanently?')">🗑️ Delete</a>
                    </td>
                </tr>
                <?php } 
                } else { 
                    echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>No tasks found in system.</td></tr>"; 
                } ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>