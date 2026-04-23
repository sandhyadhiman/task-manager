<?php
session_start();
include("db.php");

// 1. Login Check
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? "User"; 

// 2. Initial Data Fetch (Page load par display ke liye)
$query = "SELECT * FROM tasks WHERE user_id='$user_id' ORDER BY id DESC";
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
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); color: white; margin: 0; padding-bottom: 50px; }
        
        /* Header Styling */
        .header { background: #020617; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        
        .container { width: 90%; max-width: 1200px; margin: 2rem auto; }
        
        /* Card Styling */
        .card { background: var(--card); padding: 25px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #334155; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        
        /* Form & Inputs */
        input, select, button, textarea { padding: 12px; border-radius: 8px; border: none; margin: 8px 0; }
        input, select, textarea { background: #334155; color: white; border: 1px solid #475569; width: 100%; box-sizing: border-box; }
        textarea { resize: vertical; min-height: 80px; font-family: inherit; }
        
        button { background: var(--accent); color: white; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; font-size: 16px; }
        button:hover { filter: brightness(1.1); transform: translateY(-1px); }
        
        /* Search Bar */
        #live_search { border: 2px solid var(--blue); padding: 15px; font-size: 16px; margin-bottom: 0; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: transparent; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; }
        th { color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 13px; }
        
        /* Status Badges */
        .status-badge { padding: 5px 10px; border-radius: 6px; font-size: 11px; text-transform: uppercase; font-weight: 800; display: inline-block; }
        .completed { background: #14532d; color: #4ade80; }
        .pending { background: #78350f; color: #fbbf24; }
        .in-progress { background: #1e3a8a; color: #93c5fd; }
        
        /* Helper Classes */
        .task-desc { display: block; font-size: 13px; color: #94a3b8; margin-top: 5px; line-height: 1.4; font-style: italic; }
        .delete-btn { color: var(--danger); cursor: pointer; font-weight: bold; transition: 0.2s; }
        .delete-btn:hover { text-decoration: underline; }
        .edit-link { color: var(--blue); text-decoration: none; font-weight: bold; }
        .view-file { color: #38bdf8; font-size: 12px; text-decoration: none; display: block; margin-top: 5px; }
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
        <h3 style="margin-top:0;">🔍 Live Task Search</h3>
        <input type="text" id="live_search" placeholder="Type title or category..." oninput="liveSearch()">
    </div>

    <div class="card">
        <h3>➕ Add New Task</h3>
        <form id="taskForm" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <label>Title</label>
                    <input type="text" name="title" id="title" placeholder="What needs to be done?" required>
                </div>
                <div>
                    <label>Category</label>
                    <input type="text" name="category" placeholder="Work, Personal, etc.">
                </div>
                <div>
                    <label>Priority</label>
                    <select name="priority">
                        <option>Low</option>
                        <option selected>Medium</option>
                        <option>High</option>
                    </select>
                </div>
                <div>
                    <label>Due Date</label>
                    <input type="date" name="due_date" id="due_date" required>
                </div>
            </div>

            <div style="margin-top: 15px;">
                <label>Description (Optional)</label>
                <textarea name="description" placeholder="Write more details about this task here..."></textarea>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end; margin-top: 10px;">
                <div>
                    <label>Initial Status</label>
                    <select name="status">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div>
                    <label>Attachment</label>
                    <input type="file" name="file">
                </div>
                <div style="grid-column: span 1;">
                    <button type="button" onclick="addTask()">Save Task</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card" style="overflow-x: auto;">
        <h3>📋 My Tasks</h3>
        <table>
            <thead>
                <tr>
                    <th width="40%">Task Detail</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <th>Feedback</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="taskTableBody">
                <?php while($row = mysqli_fetch_assoc($result)) { 
                    $status_cls = str_replace(' ', '-', strtolower($row['status']));
                ?>
                <tr id="row-<?php echo $row['id']; ?>">
                    <td>
                        <b><?php echo htmlspecialchars($row['title']); ?></b>
                        <span class="task-desc">
                            <?php echo !empty($row['description']) ? htmlspecialchars($row['description']) : "No description provided."; ?>
                        </span>
                        
                        <?php if(!empty($row['file'])): ?>
                            <a href="uploads/<?php echo $row['file']; ?>" target="_blank" class="view-file">📎 View File</a>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge <?php echo $status_cls; ?>"><?php echo $row['status']; ?></span></td>
                    <td><?php echo $row['priority']; ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td style="font-size: 13px; color: #94a3b8; font-style: italic;">
                        <?php echo (!empty($row['admin_feedback'])) ? htmlspecialchars($row['admin_feedback']) : "No feedback"; ?>
                    </td>
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
// --- LIVE SEARCH FUNCTION ---
function liveSearch() {
    let query = document.getElementById('live_search').value;
    let tableBody = document.getElementById('taskTableBody');

    fetch('live_search_ajax.php?search=' + encodeURIComponent(query))
    .then(res => res.text())
    .then(data => {
        tableBody.innerHTML = data;
    })
    .catch(err => console.error('Search Error:', err));
}

// --- ADD TASK FUNCTION ---
function addTask() {
    let title = document.getElementById('title').value;
    let dueDate = document.getElementById('due_date').value;

    if(title.trim() === "" || dueDate === "") {
        alert("Title and Due Date are mandatory!");
        return;
    }

    let form = document.getElementById('taskForm');
    let formData = new FormData(form);
    
    fetch('add_task_ajax.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(data => {
        if(data.trim() === "Success") {
            alert("Task saved successfully!");
            location.reload(); 
        } else {
            alert("Error: " + data);
        }
    });
}

// --- DELETE TASK FUNCTION ---
function deleteTask(id) {
    if(confirm('Are you sure you want to delete this task?')) {
        fetch('delete_task_ajax.php?id=' + id)
        .then(res => res.text())
        .then(data => {
            if(data.trim() === "Deleted") {
                let row = document.getElementById('row-' + id);
                row.style.transition = "0.3s";
                row.style.opacity = "0";
                setTimeout(() => { row.remove(); }, 300);
            } else {
                alert("Error deleting task: " + data);
            }
        })
        .catch(err => alert("Connection error!"));
    }
}
</script>

</body>
</html>