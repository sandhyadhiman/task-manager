<?php
session_start();
include("db.php");

// 1. Session & Security Check
if(!isset($_SESSION['user_id'])){
    echo "Unauthorized access";
    exit();
}

$user_id = $_SESSION['user_id'];
// Catching 'action' from POST (for Add) or GET (for Search/Delete)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* ================= ACTION: ADD TASK ================= */
if($action == "add") {

    // Sanitize all inputs
    $title       = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $category    = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
    $priority    = mysqli_real_escape_string($conn, $_POST['priority'] ?? '');
    $due_date    = mysqli_real_escape_string($conn, $_POST['due_date'] ?? '');
    $status      = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Pending');

    // File Upload Logic
    $file_name = "";
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = time() . "_" . basename($_FILES['file']['name']);
        if (!is_dir('uploads')) { 
            mkdir('uploads', 0755, true); 
        }
        move_uploaded_file($_FILES['file']['tmp_name'], "uploads/" . $file_name);
    }

    // Include 'description' and set 'is_deleted' to 0 (Active)
    $sql = "INSERT INTO tasks (user_id, title, description, category, priority, status, due_date, file, is_deleted) 
            VALUES ('$user_id', '$title', '$description', '$category', '$priority', '$status', '$due_date', '$file_name', 0)";

    if (mysqli_query($conn, $sql)) {
        echo "Success";
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
}

/* ================= ACTION: LIVE SEARCH (Synced with Dashboard Box UI) ================= */
elseif($action == "search") {
    
    $search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');

    // Note: Filtered by 'is_deleted = 0' so deleted tasks don't show up
    $query = "SELECT * FROM tasks 
              WHERE user_id='$user_id' 
              AND is_deleted = 0
              AND (title LIKE '%$search%' OR category LIKE '%$search%') 
              ORDER BY id DESC";
              
    $res = mysqli_query($conn, $query);

    if(mysqli_num_rows($res) > 0) {
        while($row = mysqli_fetch_assoc($res)){
            $status_cls = str_replace(' ', '-', strtolower($row['status']));
            $desc_display = !empty($row['description']) ? nl2br(htmlspecialchars($row['description'])) : "<small style='opacity:0.5;'>No details provided.</small>";

            echo "
            <tr id='row-".$row['id']."'>
                <td><b style='color:#f8fafc; font-size: 15px;'>".htmlspecialchars($row['title'])."</b></td>
                <td>
                    <div class='desc-box' style='background: #0f172a; padding: 10px; border-radius: 8px; font-size: 13px; color: #cbd5e1; border: 1px solid #334155;'>
                        $desc_display
                    </div>";
                    if(!empty($row['file'])){
                        echo "<a href='uploads/".$row['file']."' target='_blank' class='view-file' style='color:#38bdf8; font-size:12px; text-decoration:none; display:inline-block; margin-top:8px;'>📎 View Attachment</a>";
                    }
            echo "</td>
                <td><span class='status-badge $status_cls'>".$row['status']."</span></td>
                <td><span style='background:#0f172a; padding:4px 8px; border-radius:4px; border:1px solid #334155; font-size: 12px;'>".ucfirst($row['priority'])."</span></td>
                <td>".htmlspecialchars($row['category'])."</td>
                <td>
                    <a href='edit_task.php?id=".$row['id']."' style='color:#3b82f6; text-decoration:none; font-weight:bold;'>Edit</a> | 
                    <span class='delete-btn' onclick='deleteTask(".$row['id'].")' style='color:#ef4444; cursor:pointer; font-weight:bold;'>Delete</span>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>No tasks found matching your search.</td></tr>";
    }
}

/* ================= ACTION: SECURE SOFT DELETE WITH PASSWORD ================= */
elseif($action == "delete") {
    $id = intval($_GET['id'] ?? 0);
    $user_pass = $_GET['pass'] ?? ''; // User ne jo password dala prompt mein

    if($id > 0 && !empty($user_pass)) {
        
        // 1. Database se user ka asli password (hashed) nikaalein
        $user_query = "SELECT password FROM users WHERE id = '$user_id'";
        $user_res = mysqli_query($conn, $user_query);
        $user_data = mysqli_fetch_assoc($user_res);

        // 2. Password verify karein (password_hash use kar rahe hain toh password_verify)
        if(password_verify($user_pass, $user_data['password'])) {
            
            // 3. Agar password sahi hai, toh Soft Delete karein
            $soft_del_query = "UPDATE tasks 
                               SET is_deleted = 1, 
                                   deleted_at = NOW() 
                               WHERE id='$id' AND user_id='$user_id'";
            
            if(mysqli_query($conn, $soft_del_query)) {
                echo "Deleted";
            } else {
                echo "Error: Database issue.";
            }
        } else {
            // Agar password galat hai
            echo "Invalid password! Delete failed.";
        }
    } else {
        echo "Password required.";
    }
}