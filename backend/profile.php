<?php
session_start();
include '../auth/db_connect.php';

// 1. Get and Validate Employee ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: employees.php");
    exit;
}

$id = intval($_GET['id']);
date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');

// 2. Fetch Employee Data
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$emp = $result->fetch_assoc();

if (!$emp) {
    $_SESSION['error'] = "Employee not found.";
    header("Location: employees.php");
    exit;
}

// 3. Pagination & Date Filter Logic (Updated)
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Capture filter dates
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

// Build the dynamic WHERE clause for filtering
$filter_sql = "";
if ($start_date && $end_date) {
    $filter_sql = " AND attendance_date BETWEEN '$start_date' AND '$end_date' ";
}

// 4. Fetch Attendance History (Modified to include filters)
$sql_history = "SELECT * FROM attendance WHERE employee_id = ? $filter_sql ORDER BY attendance_date DESC LIMIT ? OFFSET ?";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("iii", $id, $limit, $offset);
$stmt_history->execute();
$attendance_history = $stmt_history->get_result();

// 5. Calculate Total Pages (Modified to include filters)
$sql_total = "SELECT COUNT(*) as total FROM attendance WHERE employee_id = ? $filter_sql";
$total_stmt = $conn->prepare($sql_total);
$total_stmt->bind_param("i", $id);
$total_stmt->execute();
$total_rows = $total_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// 6. Fetch Today's Attendance (For the Time In/Out buttons)
$stmt_today = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?");
$stmt_today->bind_param("is", $id, $today);
$stmt_today->execute();
$attendance = $stmt_today->get_result()->fetch_assoc();

// 7. Helper to format time
if (!function_exists('formatTime')) {
    function formatTime($time) {
        return $time ? date('h:i A', strtotime($time)) : '-';
    }
}
?>