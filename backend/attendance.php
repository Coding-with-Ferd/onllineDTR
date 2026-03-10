<?php
session_start();
include '../auth/db_connect.php';

date_default_timezone_set('Asia/Manila'); // Set timezone

// Get date range from GET parameters or default to today
$from_date = $_GET['from'] ?? date('Y-m-d');
$to_date   = $_GET['to'] ?? date('Y-m-d');

// Fetch employees
$employees = $conn->query("SELECT * FROM employees ORDER BY last_name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch attendance within range
$attendance_records = [];
$stmt = $conn->prepare("SELECT * FROM attendance WHERE attendance_date BETWEEN ? AND ?");
$stmt->bind_param("ss", $from_date, $to_date);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $attendance_records[$row['employee_id']][$row['attendance_date']] = $row;
}

// Helper to format time in 12-hour format
function formatTime($time)
{
    return $time ? date("h:i A", strtotime($time)) : '-';
}

// Helper to calculate total hours
function totalHours($time_in, $time_out)
{
    if (!$time_in || !$time_out) return '-';
    $in = new DateTime($time_in);
    $out = new DateTime($time_out);
    $diff = $in->diff($out);
    return $diff->h + $diff->i / 60; // decimal hours
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['employee_id'];
    $date = $_POST['date'];

    // Fetch existing attendance record for today
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?");
    $stmt->bind_param("is", $emp_id, $date);
    $stmt->execute();
    $res = $stmt->get_result();
    $record = $res->fetch_assoc();

    // Use 24-hour format for database
    $current_time = date('H:i:s');

    if (isset($_POST['timein'])) {
        if (!$record) {
            // Insert new record with time_in
            $stmt = $conn->prepare("
                INSERT INTO attendance (employee_id, attendance_date, time_in, status, created_at, updated_at) 
                VALUES (?, ?, ?, 'Present', NOW(), NOW())
            ");
            $stmt->bind_param("iss", $emp_id, $date, $current_time);
            $stmt->execute();
        }
    } elseif (isset($_POST['timeout'])) {
        if ($record && !$record['time_out']) {
            // Update record with time_out
            $stmt = $conn->prepare("UPDATE attendance SET time_out = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $current_time, $record['id']);
            $stmt->execute();
        }
    }

    header("Location: ../pages/attendance.php");
    exit;
}
