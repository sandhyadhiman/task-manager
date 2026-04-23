<?php
session_start();
include("db.php");

/** * 1. 🔐 Security Guard (Sirf Admin allowed hai)
 */
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

/** * 2. 🔄 [UPDATE] User Role Logic
 */
if(isset($_POST['update_role'])){
    $u_id = (int)$_POST['u_id'];
    $new_role = mysqli_real_escape_string($conn, $_POST['new_role']);
    
    mysqli_query($conn, "UPDATE users SET role='$new_role' WHERE id=$u_id");
    echo "<script>alert('User role updated!'); window.location.href='admin.php';</script>";
    exit();
}

/** * 3. 💬 [UPDATE] Feedback Logic
 */
if(isset($_POST['submit_feedback'])){
    $task_id = (int)$_POST['task_id']; 
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    
    $update_query = "UPDATE tasks SET admin_feedback='$feedback' WHERE id=$task_id";
    if(mysqli_query($conn, $update_query)){
        echo "<script>alert('Feedback updated successfully!'); window.location.href='admin.php';</script>";
        exit();
    }
}

/** * 4. ❌ [DELETE] Task or User Logic
 */
if(isset($_GET['delete_task'])){
    $id = (int)$_GET['delete_task'];
    mysqli_query($conn, "DELETE FROM tasks WHERE id=$id");
    header("Location: admin.php");
    exit();
}

if(isset($_GET['delete_user'])){
    $uid = (int)$_GET['delete_user'];
    // Admin khud ko delete na kar paye check
    if($uid != $_SESSION['user_id']){
        mysqli_query($conn, "DELETE FROM users WHERE id=$uid");
        mysqli_query($conn, "DELETE FROM tasks WHERE user_id=$uid"); // User ke tasks bhi delete
    }
    header("Location: admin.php");
    exit();
}

/** * 5.  [READ] Data Fetching 
 */
$users_res = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
$tasks_query = "SELECT tasks.*, users.username 
                FROM tasks 
                LEFT JOIN users ON tasks.user_id = users.id 
                ORDER BY tasks.id DESC";
$tasks_res = mysqli_query($conn, $tasks_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Management</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #f1f5f9; color: #1e293b; }
        .header { background: #0f172a; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .logout { background: #ef4444; color: white; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .container { width: 95%; max-width: 1300px; margin: 30px auto; }
        .card { background: white; padding: 20px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow-x: auto; }
        h3 { color: #334155; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #1e293b; color: white; padding: 12px; text-align: left; font-size: 13px; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .pending { background: #fef3c7; color: #d97706; }
        .completed { background: #d1fae5; color: #059669; }
        .in-progress { background: #dbeafe; color: #2563eb; }
        .delete { color: #ef4444; text-decoration: none; font-weight: bold; font-size: 13px; }
        textarea { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #cbd5e1; box-sizing: border-box; }
        .btn-sm { background: #2563eb; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        select { padding: 5px; border-radius: 4px; border: 1px solid #cbd5e1; }
    </style>
</head>
<body>

<div class="header">
    <h2> Admin Portal</h2>
    <a class="logout" href="logout.php">Logout</a>
</div>

<div class="container">

    <div class="card">
        <h3> User Management</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Current Role</th>
                    <th>Change Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = mysqli_fetch_assoc($users_res)) { ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><b><?php echo htmlspecialchars($u['username']); ?></b></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span style="color:#6366f1; font-weight:bold;"><?php echo strtoupper($u['role']); ?></span></td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="u_id" value="<?php echo $u['id']; ?>">
                            <select name="new_role">
                                <option value="user" <?php if($u['role']=='user') echo 'selected'; ?>>User</option>
                                <option value="admin" <?php if($u['role']=='admin') echo 'selected'; ?>>Admin</option>
                            </select>
                            <button type="submit" name="update_role" class="btn-sm">Update</button>
                        </form>
                    </td>
                    <td>
                        <?php if($u['id'] != $_SESSION['user_id']) { ?>
                            <a class="delete" href="?delete_user=<?php echo $u['id']; ?>" onclick="return confirm('Pakka user aur uske saare tasks delete karne hain?')">Remove User</a>
                        <?php } else { echo "<b>System (You)</b>"; } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3> Global Task Oversight</h3>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Task Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Feedback</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(mysqli_num_rows($tasks_res) > 0) {
                    while($t = mysqli_fetch_assoc($tasks_res)) { 
                        $st = strtolower(str_replace(' ', '-', $t['status']));
                    ?>
                <tr>
                    <td><b><?php echo htmlspecialchars($t['username'] ?? 'Deleted User'); ?></b></td>
                    <td><?php echo htmlspecialchars($t['title']); ?></td>
                    <td><?php echo htmlspecialchars($t['category'] ?? '-'); ?></td>
                    <td><?php echo $t['priority']; ?></td>
                    <td>
                        <span class="status-badge <?php echo $st; ?>">
                            <?php echo $t['status']; ?>
                        </span>
                    </td>
                    <td width="30%">
                        <form method="POST" style="display:flex; flex-direction: column; gap:5px;">
                            <input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">
                            <textarea name="feedback" rows="2" placeholder="Write feedback..."><?php echo htmlspecialchars($t['admin_feedback'] ?? ''); ?></textarea>
                            <button type="submit" name="submit_feedback" class="btn-sm">Save Feedback</button>
                        </form>
                    </td>
                    <td>
                        <a class="delete" href="?delete_task=<?php echo $t['id']; ?>" onclick="return confirm('Delete this task?')">🗑️ Delete</a>
                    </td>
                </tr>
                <?php } 
                } else { echo "<tr><td colspan='7' style='text-align:center;'>No tasks found in system.</td></tr>"; } ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>