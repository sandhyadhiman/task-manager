<?php
session_start();
include("db.php");

/** * 1. 🔐 Security Guard 
 * Check karein ki user logged in hai aur uska role 'admin' hai.
 */
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

/** * 2. 🔄 [UPDATE] User Role Logic with Safety Check
 */
if(isset($_POST['update_role'])){
    $u_id = (int)$_POST['u_id'];
    $new_role = mysqli_real_escape_string($conn, $_POST['new_role']);
    
    if($u_id == $_SESSION['user_id'] && $new_role != 'admin'){
        $admin_check_query = "SELECT COUNT(*) as total_admins FROM users WHERE role='admin' AND status='active'";
        $admin_check_res = mysqli_query($conn, $admin_check_query);
        $admin_data = mysqli_fetch_assoc($admin_check_res);
        
        if($admin_data['total_admins'] <= 1){
            echo "<script>alert('Atleast one admin required!'); window.location.href='admin.php';</script>";
            exit();
        }
    }
    
    mysqli_query($conn, "UPDATE users SET role='$new_role', updated_at=NOW() WHERE id=$u_id");
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

/** * 4. ❌ [SOFT DELETE] Logic
 */
if(isset($_GET['delete_task'])){
    $id = (int)$_GET['delete_task'];
    mysqli_query($conn, "DELETE FROM tasks WHERE id=$id");
    header("Location: admin.php");
    exit();
}

if(isset($_GET['delete_user'])){
    $uid = (int)$_GET['delete_user'];
    if($uid != $_SESSION['user_id']){
        mysqli_query($conn, "UPDATE users SET status='deleted', deleted_at=NOW() WHERE id=$uid");
    }
    header("Location: admin.php");
    exit();
}

/** * 5. [READ] Data Fetching
 */
$users_res = mysqli_query($conn, "SELECT * FROM users WHERE status='active' ORDER BY id ASC");
$tasks_query = "SELECT tasks.*, users.username 
                FROM tasks 
                LEFT JOIN users ON tasks.user_id = users.id 
                WHERE users.status='active' OR users.id IS NULL 
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
        :root {
            --primary: #4f46e5;
            --dark: #0f172a;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --danger: #ef4444;
            --success: #22c55e;
        }

        body { 
            font-family: 'Inter', 'Segoe UI', sans-serif; 
            margin: 0; 
            background: var(--light-bg); 
            color: var(--text-main); 
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Modern Header */
        .header { 
            background: var(--dark); 
            color: white; 
            padding: 1rem 2rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header h2 { margin: 0; font-size: 1.5rem; font-weight: 700; letter-spacing: -0.5px; }
        
        .logout { 
            background: rgba(239, 68, 68, 0.1); 
            color: var(--danger); 
            padding: 8px 20px; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: 600; 
            border: 1px solid var(--danger);
            transition: all 0.3s ease;
        }
        .logout:hover { background: var(--danger); color: white; }

        .container { 
            width: 95%; 
            max-width: 1400px; 
            margin: 2rem auto; 
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Card Styling */
        .card { 
            background: var(--card-bg); 
            padding: 24px; 
            border-radius: 16px; 
            margin-bottom: 2rem; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 10px 15px -3px rgba(0,0,0,0.05); 
            border: 1px solid #e2e8f0;
            overflow-x: auto; 
        }

        h3 { 
            color: var(--dark); 
            margin-top: 0;
            padding-bottom: 12px; 
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Table Design */
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 1rem; }
        
        th { 
            background: #f8fafc; 
            color: var(--text-muted); 
            padding: 14px; 
            text-align: left; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e2e8f0;
        }
        
        td { 
            padding: 16px 14px; 
            border-bottom: 1px solid #f1f5f9; 
            font-size: 0.9rem; 
            transition: background 0.2s;
        }

        tr:hover td { background: #fdfdfd; }

        /* Badges & UI Elements */
        .status-badge { 
            padding: 4px 10px; 
            border-radius: 99px; 
            font-size: 0.7rem; 
            font-weight: 700; 
            display: inline-block;
        }
        .pending { background: #fffbeb; color: #92400e; }
        .completed { background: #f0fdf4; color: #166534; }
        .in-progress { background: #eff6ff; color: #1e40af; }

        .delete { color: var(--danger); text-decoration: none; font-weight: 600; transition: 0.2s; }
        .delete:hover { text-decoration: underline; }

        textarea { 
            width: 100%; 
            padding: 10px; 
            border-radius: 8px; 
            border: 1px solid #cbd5e1; 
            background: #f8fafc;
            resize: none;
            font-family: inherit;
            transition: border 0.3s;
        }
        textarea:focus { outline: none; border-color: var(--primary); background: white; }

        .btn-sm { 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 8px 14px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600; 
            transition: 0.3s;
        }
        .btn-sm:hover { opacity: 0.9; transform: translateY(-1px); }

        select { 
            padding: 6px 10px; 
            border-radius: 6px; 
            border: 1px solid #cbd5e1; 
            background: white; 
        }

        .user-name { font-weight: 600; color: var(--dark); }
        .update-form { display: flex; gap: 8px; align-items: center; }

    </style>
</head>
<body>

<div class="header">
    <h2> Admin Dashboard</h2>
    <a class="logout" href="logout.php">Logout</a>
</div>

<div class="container">

    <div class="card">
        <h3>👥 Active User Management</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Identity</th>
                    <th>Email Address</th>
                    <th>Current Role</th>
                    <th>Activity Log</th>
                    <th>Role Action</th>
                    <th>Account Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = mysqli_fetch_assoc($users_res)) { ?>
                <tr>
                    <td>#<?php echo $u['id']; ?></td>
                    <td><span class="user-name"><?php echo htmlspecialchars($u['username']); ?></span></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span style="color:var(--primary); font-weight:700; font-size: 11px;">● <?php echo strtoupper($u['role']); ?></span></td>
                    <td><small style="color: var(--text-muted);"><?php echo $u['updated_at'] ? $u['updated_at'] : 'No recent updates'; ?></small></td>
                    <td>
                        <form method="POST" class="update-form">
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
                            <a class="delete" href="?delete_user=<?php echo $u['id']; ?>" onclick="return confirm('Archive this user? Login will be disabled.')">Deactivate</a>
                        <?php } else { echo "<span style='color:var(--success); font-weight:bold;'>Active (You)</span>"; } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>📋 Global Task Oversight</h3>
        <table>
            <thead>
                <tr>
                    <th>Assigned To</th>
                    <th>Task Information</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Admin Feedback</th>
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
                    <td><b class="user-name"><?php echo htmlspecialchars($t['username'] ?? 'Former User'); ?></b></td>
                    <td>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($t['title']); ?></div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;"><?php echo htmlspecialchars($t['description']); ?></div>
                    </td>
                    <td><small style="background:#f1f5f9; padding:2px 6px; border-radius:4px;"><?php echo htmlspecialchars($t['category'] ?? 'General'); ?></small></td>
                    <td>
                        <span style="font-size: 11px; font-weight: bold; color: <?php echo ($t['priority'] == 'high') ? 'var(--danger)' : 'var(--text-muted)'; ?>">
                            <?php echo strtoupper($t['priority']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $st; ?>">
                            <?php echo htmlspecialchars($t['status']); ?>
                        </span>
                    </td>
                    <td width="300">
                        <form method="POST" style="display:flex; flex-direction: column; gap:8px;">
                            <input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">
                            <textarea name="feedback" rows="2" placeholder="Provide feedback to user..."><?php echo htmlspecialchars($t['admin_feedback'] ?? ''); ?></textarea>
                            <button type="submit" name="submit_feedback" class="btn-sm" style="align-self: flex-end;">Save Note</button>
                        </form>
                    </td>
                    <td>
                        <a class="delete" href="?delete_task=<?php echo $t['id']; ?>" onclick="return confirm('Permanently delete this task?')">🗑️ Remove</a>
                    </td>
                </tr>
                <?php } 
                } else { echo "<tr><td colspan='7' style='text-align:center; padding: 40px; color: var(--text-muted);'>No tasks currently active in the system.</td></tr>"; } ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>