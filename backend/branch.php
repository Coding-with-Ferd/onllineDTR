<?php
require_once '../config/session.php';
include '../auth/db_connect.php';

if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}

date_default_timezone_set('Asia/Manila');

/* ADD / UPDATE / DELETE BRANCH */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action      = $_POST['action'] ?? '';
    $branch_id   = (int) ($_POST['branch_id'] ?? 0);
    $branch_name = trim($_POST['branch_name'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $open_time   = $_POST['open_time'] ?? '09:00';
    $close_time  = $_POST['close_time'] ?? '18:00';
    $is_open     = isset($_POST['is_open']) ? (int) $_POST['is_open'] : 1;

    /* DELETE */
    if ($action === 'delete' && $branch_id > 0) {

        $check = $conn->prepare("SELECT COUNT(*) as total FROM employees WHERE branch_id = ?");
        $check->bind_param("i", $branch_id);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();

        if ($result['total'] > 0) {
            $_SESSION['notif'] = [
                'icon' => 'warning',
                'message' => 'Cannot delete branch with existing employees.'
            ];
        } else {

            $stmt = $conn->prepare("DELETE FROM branches WHERE id = ?");
            $stmt->bind_param("i", $branch_id);

            if ($stmt->execute()) {
                $_SESSION['notif'] = [
                    'icon' => 'success',
                    'message' => 'Branch deleted successfully.'
                ];
            } else {
                $_SESSION['notif'] = [
                    'icon' => 'error',
                    'message' => 'Failed to delete branch.'
                ];
            }
        }

        /* UPDATE */
    } elseif ($action === 'update' && $branch_id > 0) {

        if ($branch_name !== '') {
            $stmt = $conn->prepare("
                UPDATE branches
                SET branch_name = ?, address = ?, open_time = ?, close_time = ?, is_open = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssssii", $branch_name, $address, $open_time, $close_time, $is_open, $branch_id);

            if ($stmt->execute()) {
                $_SESSION['notif'] = [
                    'icon' => 'success',
                    'message' => 'Branch updated successfully.'
                ];
            } else {
                $_SESSION['notif'] = [
                    'icon' => 'error',
                    'message' => 'Failed to update branch.'
                ];
            }
        } else {
            $_SESSION['notif'] = [
                'icon' => 'warning',
                'message' => 'Branch name is required.'
            ];
        }

        /* ADD */
    } elseif ($action === 'add' || $action === '') {

        if ($branch_name !== '') {
            $stmt = $conn->prepare("
                INSERT INTO branches (branch_name, address, open_time, close_time, is_open, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("ssssi", $branch_name, $address, $open_time, $close_time, $is_open);

            if ($stmt->execute()) {
                $_SESSION['notif'] = [
                    'icon' => 'success',
                    'message' => 'Branch added successfully.'
                ];
            } else {
                $_SESSION['notif'] = [
                    'icon' => 'error',
                    'message' => 'Failed to add branch.'
                ];
            }
        } else {
            $_SESSION['notif'] = [
                'icon' => 'warning',
                'message' => 'Branch name is required.'
            ];
        }
    }

    header("Location: ../pages/branch_status.php");
    exit;
}

/* FETCH BRANCH DATA */
$today = date('Y-m-d');

$sql = "
    SELECT 
        b.*,

        (
            SELECT COUNT(*)
            FROM employees e
            INNER JOIN attendance a ON a.employee_id = e.id
            WHERE e.branch_id = b.id
              AND a.attendance_date = ?
              AND a.time_in IS NOT NULL
              AND LOWER(COALESCE(a.status, 'present')) = 'present'
        ) AS staff_on_duty,

        (
            SELECT COUNT(*)
            FROM employees e
            INNER JOIN attendance a ON a.employee_id = e.id
            WHERE e.branch_id = b.id
              AND a.attendance_date = ?
              AND LOWER(COALESCE(a.status, '')) = 'absent'
        ) AS absent_today,

        (
            SELECT COUNT(*)
            FROM employees e
            INNER JOIN attendance a ON a.employee_id = e.id
            WHERE e.branch_id = b.id
              AND a.attendance_date = ?
              AND LOWER(COALESCE(a.status, '')) IN ('leave', 'on leave', 'holiday', 'snw holiday')
        ) AS on_leave
    FROM branches b
    ORDER BY b.branch_name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $today, $today, $today);
$stmt->execute();
$branches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
