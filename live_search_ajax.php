<?php
session_start();
include("db.php");

if(!isset($_SESSION['user_id'])) exit();

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// Query to find matching tasks
$sql = "SELECT * FROM tasks WHERE user_id='$user_id' 
        AND (title LIKE '%$search%' OR category LIKE '%$search%') 
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $status_cls = str_replace(' ', '-', strtolower($row['status']));
        $feedback = (!empty($row['admin_feedback'])) ? htmlspecialchars($row['admin_feedback']) : "No feedback";
        
        echo "<tr id='row-".$row['id']."'>
                <td>
                    <b>".htmlspecialchars($row['title'])."</b>";
        if(!empty($row['file'])) {
            echo " <a href='uploads/".$row['file']."' target='_blank' class='view-file'>📎 View Image</a>";
        }
        echo "</td>
                <td><span class='status-badge $status_cls'>".$row['status']."</span></td>
                <td>".$row['priority']."</td>
                <td>".htmlspecialchars($row['category'])."</td>
                <td style='font-size: 13px; color: #94a3b8;'>$feedback</td>
                <td>
                    <a href='edit_task.php?id=".$row['id']."' style='color: #38bdf8; text-decoration:none;'>Edit</a> | 
                    <span class='delete-btn' onclick='deleteTask(".$row['id'].")'>Delete</span>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>No Match Found</td></tr>";
}
?>