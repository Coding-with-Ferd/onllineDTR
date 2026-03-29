<?php
require_once '../config/session.php';
include '../auth/db_connect.php';

date_default_timezone_set('Asia/Manila');

// 1. GET LOGIC (For displaying the table)
$from_date = $_GET['from'] ?? date('Y-m-d');
$to_date   = $_GET['to'] ?? date('Y-m-d');

$employees = $conn->query("SELECT * FROM employees ORDER BY last_name ASC")->fetch_all(MYSQLI_ASSOC);

$attendance_records = [];
$stmt = $conn->prepare("SELECT * FROM attendance WHERE attendance_date BETWEEN ? AND ?");
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $attendance_records[$row['employee_id']][$row['attendance_date']] = $row;
}

// 2. HELPERS
function formatTime($time)
{
    return $time ? date("h:i A", strtotime($time)) : '-';
}

function totalHours($time_in, $time_out, $status = 'Present')
{
    if (in_array($status, ['Holiday', 'SNW Holiday', 'Leave'])) return '8.00';
    if ($status === 'Absent') return '0.00';

    if (!$time_in || !$time_out) return '-';

    $in = new DateTime($time_in);
    $out = new DateTime($time_out);
    $diff = $in->diff($out);
    return number_format($diff->h + ($diff->i / 60), 2);
}

// 3. POST LOGIC (For the Modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['employee_id'] ?? null;
    $date   = $_POST['date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'Present';
    $remarks = trim($_POST['remarks'] ?? '');
    $remarks = ($remarks === '') ? null : strtoupper($remarks);
    $current_time = date('H:i:s');

    $message = "";
    $icon = "success";

    if (!$emp_id) {
        $icon = "error";
        $message = "Please select an employee.";
    } else {
        $checkStmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?");
        $checkStmt->bind_param("is", $emp_id, $date);
        $checkStmt->execute();
        $record = $checkStmt->get_result()->fetch_assoc();

        if (isset($_POST['save_status'])) {
            if (!$record) {
                $stmt = $conn->prepare("
                    INSERT INTO attendance
                    (employee_id, attendance_date, time_in, time_out, status, remarks, created_at, updated_at)
                    VALUES (?, ?, NULL, NULL, ?, ?, NOW(), NOW())
                ");
                $stmt->bind_param("isss", $emp_id, $date, $status, $remarks);
                $stmt->execute();

                $message = "Status '$status' recorded successfully!";
            } else {
                $icon = "error";
                $message = "Employee already has an attendance record for today.";
            }

        } elseif (isset($_POST['timein'])) {
            if (!$record) {
                $stmt = $conn->prepare("
                    INSERT INTO attendance
                    (employee_id, attendance_date, time_in, status, remarks, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->bind_param("issss", $emp_id, $date, $current_time, $status, $remarks);
                $stmt->execute();

                $message = "Time In recorded successfully!";
            } else {
                $icon = "error";
                $message = "Employee already has an attendance record for today.";
            }

        } elseif (isset($_POST['timeout'])) {
            if ($record && !$record['time_out'] && $record['time_in']) {
                $stmt = $conn->prepare("
                    UPDATE attendance
                    SET time_out = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("si", $current_time, $record['id']);
                $stmt->execute();

                $message = "Time Out recorded successfully!";
            } else {
                $icon = "warning";
                $message = ($record && $record['time_out'])
                    ? "Already timed out today."
                    : "No Time In record found for today.";
            }
        }
    }

    $_SESSION['notif'] = [
        'message' => $message,
        'icon' => $icon
    ];

    if (isset($_POST['user_dashboard_redirect'])) {
        $referer = '../userpages/user_dashboard.php';
    } else {
        $referer = $_SERVER['HTTP_REFERER'] ?? '../pages/attendance_list.php';
    }

    header("Location: " . $referer);
    exit;
}
