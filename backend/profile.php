<?php
require_once '../config/session.php';
include '../auth/db_connect.php';

// Get and Validate Employee ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: employees.php");
    exit;
}

$id = intval($_GET['id']);

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');

// Handle Photo Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    $uploadEmployeeId = intval($_POST['id'] ?? 0);
    if ($uploadEmployeeId !== $id) {
        $_SESSION['notif'] = ['message' => 'Mismatched employee ID.', 'icon' => 'error'];
        header("Location: profile.php?id={$id}");
        exit;
    }

    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['notif'] = ['message' => 'Please select a valid file.', 'icon' => 'error'];
        header("Location: profile.php?id={$id}");
        exit;
    }

    $fileTmp = $_FILES['profile_photo']['tmp_name'];
    $fileName = basename($_FILES['profile_photo']['name']);
    $fileSize = $_FILES['profile_photo']['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileExt, $allowedExt)) {
        $_SESSION['notif'] = ['message' => 'Only JPG, PNG, and GIF files are allowed.', 'icon' => 'error'];
        header("Location: profile.php?id={$id}");
        exit;
    }

    if ($fileSize > 5 * 1024 * 1024) {
        $_SESSION['notif'] = ['message' => 'File too large. Max 5MB.', 'icon' => 'error'];
        header("Location: profile.php?id={$id}");
        exit;
    }

    $uploadDir = realpath(__DIR__ . '/../assets/uploads');
    if (!$uploadDir) {
        $uploadDir = __DIR__ . '/../assets/uploads';
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = 'employee_' . $id . '_' . time() . '.' . $fileExt;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;

    if (!move_uploaded_file($fileTmp, $targetPath)) {
        $_SESSION['notif'] = ['message' => 'Failed to move the uploaded file.', 'icon' => 'error'];
        header("Location: profile.php?id={$id}");
        exit;
    }

    $photoPath = 'assets/uploads/' . $newFileName;
    $updateStmt = $conn->prepare("UPDATE employees SET photo = ? WHERE id = ?");
    $updateStmt->bind_param('si', $photoPath, $id);

    $updateStmt->close();
    header("Location: profile.php?id={$id}");
    exit;
}

// Fetch Employee Data
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

// Pagination & Date Filter Logic (Updated)
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

// Fetch Attendance History (Modified to include filters)
$sql_history = "SELECT * FROM attendance WHERE employee_id = ? $filter_sql ORDER BY attendance_date DESC LIMIT ? OFFSET ?";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("iii", $id, $limit, $offset);
$stmt_history->execute();
$attendance_history = $stmt_history->get_result();

// Calculate Total Pages (Modified to include filters)
$sql_total = "SELECT COUNT(*) as total FROM attendance WHERE employee_id = ? $filter_sql";
$total_stmt = $conn->prepare($sql_total);
$total_stmt->bind_param("i", $id);
$total_stmt->execute();
$total_rows = $total_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Today's Attendance (For the Time In/Out buttons)
$stmt_today = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?");
$stmt_today->bind_param("is", $id, $today);
$stmt_today->execute();
$attendance = $stmt_today->get_result()->fetch_assoc();

// Check if Employee already has a user account generated
$hasUserAccount = false;
if (isset($emp['employee_code'])) {
    $emp_code = $emp['employee_code'];
    $check_user = $conn->prepare("SELECT UserID FROM user WHERE Email = ?");
    $check_user->bind_param("s", $emp_code);
    $check_user->execute();
    if ($check_user->get_result()->num_rows > 0) {
        $hasUserAccount = true;
    }
}

// Helper to format time
if (!function_exists('formatTime')) {
    function formatTime($time) {
        return $time ? date('h:i A', strtotime($time)) : '-';
    }
}
?>