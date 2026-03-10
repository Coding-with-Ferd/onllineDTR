<?php
session_start();
include '../auth/db_connect.php';

// Get employee ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: employees.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch employee data
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

// Fetch today's attendance for this employee
date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?");
$stmt->bind_param("is", $id, $today);
$stmt->execute();
$attendance = $stmt->get_result()->fetch_assoc();

// Helper to format time
function formatTime($time)
{
    return $time ? date('h:i A', strtotime($time)) : '-';
}
?>