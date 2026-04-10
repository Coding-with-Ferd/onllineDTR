<?php
require_once '../config/session.php';
include '../auth/db_connect.php';

// Only allow Admins
requireRole(['Admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['employee_id'] ?? null;

    if (!$emp_id) {
        $_SESSION['notif'] = ['message' => 'Invalid Employee ID.', 'icon' => 'error'];
        header("Location: ../pages/employees.php");
        exit;
    }

    // Fetch the employee's details
    $stmt = $conn->prepare("SELECT first_name, last_name, employee_code, status FROM employees WHERE id = ?");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['notif'] = ['message' => 'Employee not found.', 'icon' => 'error'];
        header("Location: ../pages/employees.php");
        exit;
    }

    $emp = $result->fetch_assoc();


    $email = $emp['employee_code'];
    $fullName = trim($emp['first_name'] . ' ' . $emp['last_name']);

    // Create password
    $surnamePrefix = ucfirst(strtolower(substr($emp['last_name'], 0, 2)));
    $rawPassword = $surnamePrefix . '8888';

    // Check if user already exists
    $checkStmt = $conn->prepare("SELECT UserID FROM user WHERE Email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $_SESSION['notif'] = [
            'message' => 'An account for this employee already exists.',
            'icon' => 'error'
        ];
    } else {
        // Hash password and insert
        $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);
        $role = 'Employee';
        $status = 'Active';

        $insertStmt = $conn->prepare("INSERT INTO user (FullName, Email, PasswordHash, Status, Role) VALUES (?, ?, ?, ?, ?)");
        $insertStmt->bind_param("sssss", $fullName, $email, $hashedPassword, $status, $role);

        if ($insertStmt->execute()) {
            $_SESSION['notif'] = [
                'message' => "Account generated!<br>Email: {$email}<br>Password: {$rawPassword}",
                'icon' => 'success'
            ];
        } else {
            $_SESSION['notif'] = [
                'message' => 'Failed to generate account due to a database error.',
                'icon' => 'error'
            ];
        }
    }

    header("Location: ../pages/profile.php?id=" . $emp_id);
    exit;
} else {
    header("Location: ../pages/employees.php");
    exit;
}
