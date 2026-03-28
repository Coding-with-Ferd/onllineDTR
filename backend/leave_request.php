<?php
include '../auth/db_connect.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $employee_id = $_POST['employee_id'];
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'] ?? '';

    // Insert leave request
    $stmt = $conn->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("issss", $employee_id, $leave_type, $start_date, $end_date, $reason);

    if($stmt->execute()){
        $_SESSION['success'] = "Leave request submitted successfully.";
    } else {
        $_SESSION['error'] = "Failed to submit leave request.";
    }

    header("Location: ../pages/appointment.php");
    exit;
}

// Handle approval/rejection
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    if($action === 'approve') {
        // First, get the leave request details
        $stmt = $conn->prepare("SELECT employee_id, start_date, end_date FROM leave_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $leave_request = $result->fetch_assoc();

        if($leave_request) {
            // Update leave request status
            $stmt = $conn->prepare("UPDATE leave_requests SET status = 'Approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->bind_param("ii", $_SESSION['user_id'], $id);
            $leave_updated = $stmt->execute();

            // Update employee status to 'on leave'
            $stmt = $conn->prepare("UPDATE employees SET status = 'on leave', updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $leave_request['employee_id']);
            $employee_updated = $stmt->execute();

            // Create attendance and daily_status records for each day of leave
            $start = new DateTime($leave_request['start_date']);
            $end = new DateTime($leave_request['end_date']);
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

            $record_created = true;
            foreach ($period as $date) {
                $current_date = $date->format('Y-m-d');
                $employee_id = $leave_request['employee_id'];

                // Insert or update attendance record
                $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE employee_id = ? AND attendance_date = ?");
                $check_stmt->bind_param("is", $employee_id, $current_date);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    // Update existing attendance record
                    $update_stmt = $conn->prepare("UPDATE attendance SET status = 'on leave', updated_at = NOW() WHERE employee_id = ? AND attendance_date = ?");
                    $update_stmt->bind_param("is", $employee_id, $current_date);
                    $update_stmt->execute();
                } else {
                    // Insert new attendance record
                    $insert_stmt = $conn->prepare("INSERT INTO attendance (employee_id, attendance_date, status, created_at, updated_at) VALUES (?, ?, 'on leave', NOW(), NOW())");
                    $insert_stmt->bind_param("is", $employee_id, $current_date);
                    if (!$insert_stmt->execute()) {
                        $record_created = false;
                    }
                }

                // Insert or update daily_status record
                $check_stmt = $conn->prepare("SELECT id FROM daily_status WHERE employee_id = ? AND status_date = ?");
                $check_stmt->bind_param("is", $employee_id, $current_date);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    // Update existing daily_status record
                    $update_stmt = $conn->prepare("UPDATE daily_status SET status = 'on leave', updated_at = NOW() WHERE employee_id = ? AND status_date = ?");
                    $update_stmt->bind_param("is", $employee_id, $current_date);
                    $update_stmt->execute();
                } else {
                    // Insert new daily_status record
                    $insert_stmt = $conn->prepare("INSERT INTO daily_status (employee_id, status_date, status, created_at, updated_at) VALUES (?, ?, 'on leave', NOW(), NOW())");
                    $insert_stmt->bind_param("is", $employee_id, $current_date);
                    if (!$insert_stmt->execute()) {
                        $record_created = false;
                    }
                }
            }

            if($leave_updated && $employee_updated && $record_created) {
                $_SESSION['success'] = "Leave request approved successfully. Employee status changed to 'on leave' and attendance records created for all leave dates.";
            } elseif($leave_updated && $employee_updated) {
                $_SESSION['success'] = "Leave request approved and employee status updated, but some attendance records could not be created.";
            } else {
                $_SESSION['error'] = "Failed to approve leave request.";
            }
        } else {
            $_SESSION['error'] = "Leave request not found.";
        }
    } elseif($action === 'reject') {
        // Get leave request details before rejecting
        $stmt = $conn->prepare("SELECT employee_id, start_date, end_date FROM leave_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $leave_request = $result->fetch_assoc();

        if($leave_request) {
            // Update leave request status
            $stmt = $conn->prepare("UPDATE leave_requests SET status = 'Rejected', approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->bind_param("ii", $_SESSION['user_id'], $id);
            if($stmt->execute()){
                $_SESSION['success'] = "Leave request rejected.";
            } else {
                $_SESSION['error'] = "Failed to reject leave request.";
            }
        } else {
            $_SESSION['error'] = "Leave request not found.";
        }
    }

    header("Location: ../pages/appointment.php");
    exit;
}

// Delete leave request
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Get leave request details before deleting
    $stmt = $conn->prepare("SELECT employee_id, start_date, end_date, status FROM leave_requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave_request = $result->fetch_assoc();

    if($leave_request) {
        // If leave was approved, remove the attendance records
        if($leave_request['status'] === 'Approved') {
            $start = new DateTime($leave_request['start_date']);
            $end = new DateTime($leave_request['end_date']);
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

            foreach ($period as $date) {
                $current_date = $date->format('Y-m-d');
                $employee_id = $leave_request['employee_id'];

                // Delete attendance records created for this leave
                $del_stmt = $conn->prepare("DELETE FROM attendance WHERE employee_id = ? AND attendance_date = ? AND status = 'on leave'");
                $del_stmt->bind_param("is", $employee_id, $current_date);
                $del_stmt->execute();

                // Delete daily_status records created for this leave
                $del_stmt = $conn->prepare("DELETE FROM daily_status WHERE employee_id = ? AND status_date = ? AND status = 'on leave'");
                $del_stmt->bind_param("is", $employee_id, $current_date);
                $del_stmt->execute();
            }

            // Update employee status back to active if no other approved leaves
            $check_stmt = $conn->prepare("
                SELECT COUNT(*) as count FROM leave_requests 
                WHERE employee_id = ? AND status = 'Approved' AND id != ?
            ");
            $check_stmt->bind_param("ii", $leave_request['employee_id'], $id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $count_row = $check_result->fetch_assoc();

            if($count_row['count'] == 0) {
                $update_stmt = $conn->prepare("UPDATE employees SET status = 'active', updated_at = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $leave_request['employee_id']);
                $update_stmt->execute();
            }
        }

        // Delete the leave request
        $stmt = $conn->prepare("DELETE FROM leave_requests WHERE id = ?");
        $stmt->bind_param("i", $id);

        if($stmt->execute()){
            $_SESSION['success'] = "Leave request deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete leave request.";
        }
    } else {
        $_SESSION['error'] = "Leave request not found.";
    }

    header("Location: ../pages/appointment.php");
    exit;
}
?>