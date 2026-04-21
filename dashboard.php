<?php
session_start();
include("db.php");

// 🔐 Check Login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Safe Session Handling
$username = $_SESSION['username'] ?? "User"; 

// PAGINATION SETTINGS
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// SEARCH & FILTER LOGIC
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : "";

$where_clause = "WHERE user_id='$user_id'";
if($search) $where_clause .= " AND (title LIKE '%$search%' OR category LIKE '%$search%')";
if($filter_status) $where_clause .= " AND status='$filter_status'";

// FETCH TASKS
$query = "SELECT * FROM tasks $where_clause ORDER BY id DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

// TOTAL PAGES
$total_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks $where_clause");
$row_count = mysqli_fetch_assoc($total_res);
$total_pages = ceil(($row_count['count'] ?? 0) / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Manager | Dashboard</title>
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --accent: #22c55e; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: white; margin: 0; padding-bottom: 50px; }
        .header { background: #020617; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .container { width: 90%; margin: 2rem auto; }
        .card { background: var(--card); padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #334155; }
        input, select, button { padding: 10px; border-radius: 6px; border: none; margin: 5px; }
        input, select { background: #334155; color: white; border: 1px solid #475569; }
        button { background: var(--accent); color: white; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #334155; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; text-transform: uppercase; }
        .completed { background: #14532d; color: #4ade80; }
        .pending { background: #78350f; color: #fbbf24; }
        .delete-btn { color: #ef4444; cursor: pointer; font-weight: bold; }
        .pagination a { padding: 8px 12px; background: var(--card); color: white; text-decoration: none; border-radius: 4px; margin: 2px; display: inline-block; }
        .active { background: var(--accent) !important; }
        .view-file { color: #38bdf8; font-size: 12px; text-decoration: none; display: block; margin-top: 5px; }
        .view-file:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="header">
    <h2>✅ Task Manager</h2>
    <div>
        <span>Welcome, <b><?php echo htmlspecialchars($username); ?></b></span>
        <a href="logout.php" style="color: #ef4444; margin-left: 15px; text-decoration: none;">Logout</a>
    </div>
</div>

<div class="container">
    
    <div class="card">
        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 10px;">
            <input type="text" name="search" placeholder="Search by title/category..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
            <select name="status">
                <option value="">All Status</option>
                <option value="Pending" <?php if($filter_status=='Pending') echo 'selected'; ?>>Pending</option>
                <option value="Completed" <?php if($filter_status=='Completed') echo 'selected'; ?>>Completed</option>
            </select>
            <button type="submit" style="background: #3b82f6;">Filter</button>
            <a href="dashboard.php" style="color: #94a3b8; padding-top: 15px; text-decoration:none;">Clear</a>
        </form>
    </div>

    <div class="card">
        <h3>Add New Task</h3>
        <form id="taskForm" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Task Title" required>
            <input type="text" name="category" placeholder="Category (e.g. Study)">
            <select name="priority">
                <option>Low</option><option>Medium</option><option>High</option>
            </select>
            <input type="date" name="due_date" required>
            <input type="file" name="file">
            <button type="button" onclick="addTask()">+ Add Task</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <th>Admin Feedback</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="taskTableBody">
                <?php 
                if(mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr id="row-<?php echo $row['id']; ?>">
                        <td>
                            <b><?php echo htmlspecialchars($row['title']); ?></b>
                            <?php if(!empty($row['file'])): ?>
                                <a href="uploads/<?php echo $row['file']; ?>" target="_blank" class="view-file">📎 View Image</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td><?php echo $row['priority']; ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td style="font-size: 13px; color: #94a3b8; font-style: italic;">
                            <?php echo (!empty($row['admin_feedback'])) ? htmlspecialchars($row['admin_feedback']) : "No feedback yet"; ?>
                        </td>
                        <td>
                            <a href="edit_task.php?id=<?php echo $row['id']; ?>" style="color: #38bdf8; text-decoration:none;">Edit</a> | 
                            <span class="delete-btn" onclick="deleteTask(<?php echo $row['id']; ?>)">Delete</span>
                        </td>
                    </tr>
                    <?php } 
                } else { ?>
                    <tr><td colspan="6" style="text-align:center;">No tasks found.</td></tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="pagination" style="margin-top: 20px; text-align: center;">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $filter_status; ?>" 
                   class="<?php if($page==$i) echo 'active'; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<script>
function addTask() {
    let form = document.getElementById('taskForm');
    let formData = new FormData(form);
    
    fetch('add_task_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        alert("Action Processed!");
        location.reload(); 
    })
    .catch(err => console.error(err));
}

function deleteTask(id) {
    if(confirm('Are you sure you want to delete this task?')) {
        fetch('delete_task_ajax.php?id=' + id)
        .then(res => res.text())
        .then(data => {
            let row = document.getElementById('row-' + id);
            if(row) row.remove();
        });
    }
}
</script>

</body>
</html>