<?php
session_start();
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? "User"; 

$query = "SELECT * FROM tasks WHERE user_id='$user_id' AND is_deleted = 0 ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager | Final Dashboard</title>
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --accent: #22c55e; --blue: #3b82f6; --danger: #ef4444; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: white; margin: 0; padding-bottom: 50px; }
        .header { background: #020617; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        .container { width: 95%; max-width: 1300px; margin: 2rem auto; }
        .card { background: var(--card); padding: 25px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #334155; }
        
        input, select, button, textarea { padding: 12px; border-radius: 8px; border: none; margin: 8px 0; }
        input, select, textarea { background: #334155; color: white; border: 1px solid #475569; width: 100%; box-sizing: border-box; }
        
        button { background: var(--accent); color: white; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; font-size: 16px; }
        button:hover { filter: brightness(1.1); transform: translateY(-1px); }
        
        /* Table Layout Fix */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; table-layout: fixed; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; vertical-align: top; overflow: hidden; }
        th { color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 12px; }
        
        /* Fixed Description Box */
        .desc-box { 
            background: #0f172a; 
            padding: 10px; 
            border-radius: 8px; 
            font-size: 13px; 
            color: #cbd5e1; 
            border: 1px solid #334155;
            min-height: 45px;
            white-space: pre-wrap; /* Preserve line breaks */
            word-wrap: break-word; /* Prevent text overflow */
            line-height: 1.5;
        }

        .feedback-box {
            background: rgba(59, 130, 246, 0.1);
            color: #93c5fd;
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            border-left: 4px solid var(--blue);
            word-wrap: break-word;
        }

        .status-badge { padding: 5px 10px; border-radius: 6px; font-size: 11px; text-transform: uppercase; font-weight: 800; display: inline-block; }
        .completed { background: #14532d; color: #4ade80; }
        .pending { background: #78350f; color: #fbbf24; }
        .in-progress { background: #1e3a8a; color: #93c5fd; }
        
        .delete-btn { color: var(--danger); cursor: pointer; font-weight: bold; }
        .view-file { color: #38bdf8; font-size: 12px; text-decoration: none; display: inline-block; margin-top: 8px; }
        .edit-link { color: var(--blue); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="header">
    <h2 style="margin:0;">✅ Task Manager</h2>
    <div>
        <span>Welcome, <b style="color:var(--blue);"><?php echo htmlspecialchars($username); ?></b></span>
        <a href="logout.php" style="color: var(--danger); margin-left: 20px; text-decoration: none; font-weight: bold;">Logout</a>
    </div>
</div>

<div class="container">
    <div class="card">
        <h3>🔍 Live Task Search</h3>
        <input type="text" id="live_search" placeholder="Type title or category..." oninput="liveSearch()">
    </div>

    <div class="card">
        <h3>➕ Add New Task</h3>
        <form id="taskForm" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div><label>Title</label><input type="text" name="title" id="title" required></div>
                <div><label>Category</label><input type="text" name="category"></div>
                <div>
                    <label>Priority</label>
                    <select name="priority" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div><label>Due Date</label><input type="date" name="due_date" required></div>
            </div>
            <div style="margin-top: 15px;">
                <label>Description</label>
                <textarea name="description" id="description" placeholder="Enter task details..."></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: end; margin-top: 10px;">
                <div>
                    <label>Initial Status</label>
                    <select name="status">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div><label>Attachment</label><input type="file" name="file"></div>
                <button type="button" onclick="addTask()">Save Task</button>
            </div>
        </form>
    </div>

    <div class="card" style="overflow-x: auto;">
        <h3>📋 My Tasks</h3>
        <table>
            <thead>
                <tr>
                    <th width="15%">Title</th>
                    <th width="30%">Description</th>
                    <th width="20%">Admin Feedback</th>
                    <th width="12%">Status</th>
                    <th width="10%">Priority</th>
                    <th width="13%">Actions</th>
                </tr>
            </thead>
            <tbody id="taskTableBody">
                <?php while($row = mysqli_fetch_assoc($result)) { 
                    $status_cls = str_replace(' ', '-', strtolower($row['status']));
                ?>
                <tr id="row-<?php echo $row['id']; ?>">
                    <td><b style="color:#f8fafc;"><?php echo htmlspecialchars($row['title']); ?></b><br><small><?php echo htmlspecialchars($row['category']); ?></small></td>
                    <td>
                        <div class="desc-box"><?php echo !empty($row['description']) ? nl2br(htmlspecialchars($row['description'])) : "No details."; ?></div>
                        <?php if(!empty($row['file'])): ?>
                            <a href="uploads/<?php echo $row['file']; ?>" target="_blank" class="view-file">📎 Attachment</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($row['admin_feedback'])): ?>
                            <div class="feedback-box">
                                <strong>Admin Note:</strong><br>
                                <?php echo nl2br(htmlspecialchars($row['admin_feedback'])); ?>
                            </div>
                        <?php else: ?>
                            <span style="opacity:0.3; font-size:11px;">Waiting for review...</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge <?php echo $status_cls; ?>"><?php echo $row['status']; ?></span></td>
                    <td><span style="font-size: 12px;"><?php echo ucfirst($row['priority']); ?></span></td>
                    <td>
                        <a href="edit_task.php?id=<?php echo $row['id']; ?>" class="edit-link">Edit</a> | 
                        <span class="delete-btn" onclick="deleteTask(<?php echo $row['id']; ?>)">Delete</span>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Logic is same as your previous code
function liveSearch() {
    let query = document.getElementById('live_search').value;
    fetch('add_task_ajax.php?action=search&search=' + encodeURIComponent(query))
    .then(res => res.text())
    .then(data => { document.getElementById('taskTableBody').innerHTML = data; });
}

function addTask() {
    let title = document.getElementById('title').value;
    if(title.trim() === "") { alert("Title is mandatory!"); return; }
    let form = document.getElementById('taskForm');
    let formData = new FormData(form);
    formData.append('action', 'add');
    fetch('add_task_ajax.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(data => { if(data.trim() === "Success") { location.reload(); } else { alert("Error: " + data); } });
}

function deleteTask(id) {
    let password = prompt("Enter password to delete:");
    if (!password) return;
    if(confirm('Delete this task?')) {
        fetch('add_task_ajax.php?action=delete&id=' + id + '&pass=' + encodeURIComponent(password))
        .then(res => res.text())
        .then(data => {
            if(data.trim() === "Deleted") {
                document.getElementById('row-' + id).style.display = 'none';
            } else { alert(data); }
        });
    }
}
</script>
</body>
</html>