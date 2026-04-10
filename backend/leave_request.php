<?php
include '../auth/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $employee_id = (int) $_POST['employee_id'];
    $leave_type  = trim($_POST['leave_type']);
    $start_date  = $_POST['start_date'];
    $end_date    = $_POST['end_date'];
    $reason      = $_POST['reason'] ?? '';

    if (empty($employee_id) || empty($leave_type) || empty($start_date) || empty($end_date)) {
        $_SESSION['error'] = "All required fields are required.";
        header("Location: ../pages/appointment.php");
        exit;
    }

    if ($start_date > $end_date) {
        $_SESSION['error'] = "Start date cannot be later than end date.";
        header("Location: ../pages/appointment.php");
        exit;
    }

    $check_stmt = $conn->prepare("
        SELECT id, start_date, end_date, status
        FROM leave_requests
        WHERE employee_id = ?
          AND status IN ('Pending', 'Approved')
          AND ? <= end_date
          AND ? >= start_date
        LIMIT 1
    ");
    $check_stmt->bind_param("iss", $employee_id, $start_date, $end_date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $existing_leave = $check_result->fetch_assoc();

        if ($existing_leave['status'] === 'Pending') {
            $_SESSION['notif'] = [
                'message' => "You already have a pending leave request from "
                    . date("M d, Y", strtotime($existing_leave['start_date']))
                    . " to "
                    . date("M d, Y", strtotime($existing_leave['end_date']))
                    . ". Please wait for the decision first.",
                'icon' => "warning"
            ];
        } else {
            $_SESSION['notif'] = [
                'message' => "You already have an approved leave from "
                    . date("M d, Y", strtotime($existing_leave['start_date']))
                    . " to "
                    . date("M d, Y", strtotime($existing_leave['end_date']))
                    . ". You may request again after that period ends.",
                'icon' => "warning"
            ];
        }

        header("Location: ../pages/appointment.php");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO leave_requests 
        (employee_id, leave_type, start_date, end_date, reason, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->bind_param("issss", $employee_id, $leave_type, $start_date, $end_date, $reason);

    if ($stmt->execute()) {
        $_SESSION['notif'] = [
            'message' => "Leave request submitted successfully.",
            'icon' => "success"
        ];
    } else {
        $_SESSION['notif'] = [
            'message' => "Failed to submit leave request.",
            'icon' => "error"
        ];
    }

    header("Location: ../pages/appointment.php");
    exit;
}


if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int) $_GET['id'];

    if ($action === 'approve') {

        $stmt = $conn->prepare("SELECT employee_id, start_date, end_date FROM leave_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $leave_request = $result->fetch_assoc();

        if ($leave_request) {
            $stmt = $conn->prepare("UPDATE leave_requests SET status = 'Approved', approved_by = NULL, approved_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            $leave_updated = $stmt->execute();

            $today = date('Y-m-d');

            if ($today >= $leave_request['start_date'] && $today <= $leave_request['end_date']) {
                $stmt = $conn->prepare("UPDATE employees SET status = 'on leave', updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $leave_request['employee_id']);
                $employee_updated = $stmt->execute();
            } else {
                $employee_updated = true;
            }

            $start = new DateTime($leave_request['start_date']);
            $end = new DateTime($leave_request['end_date']);
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($start, $interval, (clone $end)->modify('+1 day'));

            $record_created = true;

            $today = date('Y-m-d');

            foreach ($period as $date) {
                $current_date = $date->format('Y-m-d');
                $employee_id = $leave_request['employee_id'];

                // Attendance
                $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE employee_id = ? AND attendance_date = ?");
                $check_stmt->bind_param("is", $employee_id, $current_date);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $update_stmt = $conn->prepare("UPDATE attendance SET status = 'on leave', remarks = 'on leave', updated_at = NOW() WHERE employee_id = ? AND attendance_date = ?");
                    $update_stmt->bind_param("is", $employee_id, $current_date);
                    $update_stmt->execute();
                } else {
                    $insert_stmt = $conn->prepare("INSERT INTO attendance (employee_id, attendance_date, status, remarks, created_at, updated_at) VALUES (?, ?, 'on leave', 'on leave', NOW(), NOW())");
                    $insert_stmt->bind_param("is", $employee_id, $current_date);
                    if (!$insert_stmt->execute()) {
                        $record_created = false;
                    }
                }

                // Daily status
                $check_stmt = $conn->prepare("SELECT id FROM daily_status WHERE employee_id = ? AND status_date = ?");
                $check_stmt->bind_param("is", $employee_id, $current_date);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $update_stmt = $conn->prepare("UPDATE daily_status SET status = 'on leave', updated_at = NOW() WHERE employee_id = ? AND status_date = ?");
                    $update_stmt->bind_param("is", $employee_id, $current_date);
                    $update_stmt->execute();
                } else {
                    $insert_stmt = $conn->prepare("INSERT INTO daily_status (employee_id, status_date, status, created_at, updated_at) VALUES (?, ?, 'on leave', NOW(), NOW())");
                    $insert_stmt->bind_param("is", $employee_id, $current_date);
                    if (!$insert_stmt->execute()) {
                        $record_created = false;
                    }
                }
            }

            if ($leave_updated && $employee_updated && $record_created) {
                $_SESSION['notif'] = [
                    'message' => "Leave request approved successfully.",
                    'icon' => "success"
                ];
            } elseif ($leave_updated && $employee_updated) {
                $_SESSION['notif'] = [
                    'message' => "Leave request approved, but some records could not be created.",
                    'icon' => "warning"
                ];
            } else {
                $_SESSION['notif'] = [
                    'message' => "Failed to approve leave request.",
                    'icon' => "error"
                ];
            }
        } else {
            $_SESSION['notif'] = [
                'message' => "Leave request not found.",
                'icon' => "error"
            ];
        }
    } elseif ($action === 'reject') {

        $stmt = $conn->prepare("SELECT employee_id, start_date, end_date FROM leave_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $leave_request = $result->fetch_assoc();

        if ($leave_request) {
            $stmt = $conn->prepare("UPDATE leave_requests SET status = 'Rejected', approved_by = NULL, approved_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $_SESSION['notif'] = [
                    'message' => "Leave request rejected.",
                    'icon' => "success"
                ];
            } else {
                $_SESSION['notif'] = [
                    'message' => "Failed to reject leave request.",
                    'icon' => "error"
                ];
            }
        } else {
            $_SESSION['error'] = "Leave request not found.";
        }
    }

    header("Location: ../pages/appointment.php");
    exit;
}


// Delete leave request
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("SELECT employee_id, start_date, end_date, status FROM leave_requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave_request = $result->fetch_assoc();

    if ($leave_request) {
        if ($leave_request['status'] === 'Approved') {
            $start = new DateTime($leave_request['start_date']);
            $end = new DateTime($leave_request['end_date']);
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($start, $interval, (clone $end)->modify('+1 day'));

            foreach ($period as $date) {
                $current_date = $date->format('Y-m-d');
                $employee_id = $leave_request['employee_id'];

                $del_stmt = $conn->prepare("DELETE FROM attendance WHERE employee_id = ? AND attendance_date = ? AND status = 'on leave'");
                $del_stmt->bind_param("is", $employee_id, $current_date);
                $del_stmt->execute();

                $del_stmt = $conn->prepare("DELETE FROM daily_status WHERE employee_id = ? AND status_date = ? AND status = 'on leave'");
                $del_stmt->bind_param("is", $employee_id, $current_date);
                $del_stmt->execute();
            }

            $check_stmt = $conn->prepare("
                SELECT COUNT(*) AS count
                FROM leave_requests
                WHERE employee_id = ? AND status = 'Approved' AND id != ?
            ");
            $check_stmt->bind_param("ii", $leave_request['employee_id'], $id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $count_row = $check_result->fetch_assoc();

            if ($count_row['count'] == 0) {
                $update_stmt = $conn->prepare("UPDATE employees SET status = 'active', updated_at = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $leave_request['employee_id']);
                $update_stmt->execute();
            }
        }

        $stmt = $conn->prepare("DELETE FROM leave_requests WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['notif'] = [
                'message' => "Leave request deleted successfully.",
                'icon' => "success"
            ];
        } else {
            $_SESSION['notif'] = [
                'message' => "Failed to delete leave request.",
                'icon' => "error"
            ];
        }
    } else {
        $_SESSION['error'] = "Leave request not found.";
    }

    header("Location: ../pages/appointment.php");
    exit;
}
