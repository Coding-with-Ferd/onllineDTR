<?php
require_once '../config/session.php';
include '../auth/db_connect.php';

date_default_timezone_set('Asia/Manila');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$from_date = $_GET['from'] ?? '';
$to_date   = $_GET['to'] ?? '';

// Helper
function formatTime($time)
{
    return $time ? date('h:i A', strtotime($time)) : '-';
}

function totalHours($time_in, $time_out, $status = 'Present')
{
    $status = strtolower(trim($status));

    if (in_array($status, ['absent', 'holiday', 'snw holiday', 'leave'])) {
        return 0;
    }

    if (!$time_in || !$time_out) {
        return 0;
    }

    $in  = strtotime($time_in);
    $out = strtotime($time_out);

    if ($out <= $in) {
        return 0;
    }

    $hours = ($out - $in) / 3600;
    return round($hours, 2);
}

// Filename
$filename = 'attendance_records_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Employee Code',
    'Employee Name',
    'Position',
    'Date',
    'Time In',
    'Time Out',
    'Total Hours',
    'Status',
    'Remarks'
]);

// Query
if (!empty($from_date) && !empty($to_date)) {
    $stmt = $conn->prepare("
        SELECT 
            e.employee_code,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.position,
            a.attendance_date,
            a.time_in,
            a.time_out,
            a.status,
            a.remarks
        FROM attendance a
        INNER JOIN employees e ON a.employee_id = e.id
        WHERE a.attendance_date BETWEEN ? AND ?
        ORDER BY a.attendance_date DESC, e.last_name ASC, e.first_name ASC
    ");
    $stmt->bind_param("ss", $from_date, $to_date);
} else {
    $stmt = $conn->prepare("
        SELECT 
            e.employee_code,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.position,
            a.attendance_date,
            a.time_in,
            a.time_out,
            a.status,
            a.remarks
        FROM attendance a
        INNER JOIN employees e ON a.employee_id = e.id
        ORDER BY a.attendance_date DESC, e.last_name ASC, e.first_name ASC
    ");
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $full_name = trim(
        $row['last_name'] . ', ' .
        $row['first_name'] .
        (!empty($row['middle_name']) ? ' ' . $row['middle_name'] : '')
    );

    $hours = totalHours($row['time_in'], $row['time_out'], $row['status']);

    fputcsv($output, [
        $row['employee_code'],
        $full_name,
        $row['position'],
        date('Y-m-d', strtotime($row['attendance_date'])),
        formatTime($row['time_in']),
        formatTime($row['time_out']),
        number_format($hours, 2),
        $row['status'],
        $row['remarks'] ?? ''
    ]);
}

fclose($output);
exit;
?>