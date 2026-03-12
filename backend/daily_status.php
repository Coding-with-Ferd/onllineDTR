<?php
include '../auth/db_connect.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $date = $_POST['status_date'];
    $statuses = $_POST['status']; // array: employee_id => status

    foreach($statuses as $emp_id => $status){
        // Check if record exists
        $stmt_check = $conn->prepare("SELECT id FROM daily_status WHERE employee_id = ? AND status_date = ?");
        $stmt_check->bind_param("is", $emp_id, $date);
        $stmt_check->execute();
        $res = $stmt_check->get_result();

        if($res->num_rows > 0){
            // Update existing
            $row = $res->fetch_assoc();
            $stmt_update = $conn->prepare("UPDATE daily_status SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt_update->bind_param("si", $status, $row['id']);
            $stmt_update->execute();
        } else {
            // Insert new
            $stmt_insert = $conn->prepare("INSERT INTO daily_status (employee_id, status_date, status, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt_insert->bind_param("iss", $emp_id, $date, $status);
            $stmt_insert->execute();
        }
    }

    $_SESSION['success'] = "Daily statuses saved successfully.";
    header("Location: daily_status.php?date=".$date);
    exit;
}