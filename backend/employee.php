<?php
include '../auth/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $employee_id = $_POST['employee_id'];
        $first_name = $_POST['first_name'];
        $middle_name = $_POST['middle_name'] ?? '';
        $last_name = $_POST['last_name'];
        $branch_id = $_POST['branch_id'];
        $position = $_POST['position'];
        $position_type = $_POST['position_type'];
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $hire_date = $_POST['hire_date'];
        $status = $_POST['status'];

        // Update employee in database
        $stmt = $conn->prepare("UPDATE employees SET first_name = ?, middle_name = ?, last_name = ?, position = ?, position_type = ?, email = ?, phone = ?, hire_date = ?, branch_id = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssssssssisi", $first_name, $middle_name, $last_name, $position, $position_type, $email, $phone, $hire_date, $branch_id, $status, $employee_id);
        if ($stmt->execute()) {
            $_SESSION['notif'] = [
                'message' => "Employee updated successfully.",
                'icon' => 'success'
            ];
        } else {
            $_SESSION['notif'] = [
                'message' => "Failed to update employee.",
                'icon' => 'error'
            ];
        }

        header("Location: ../pages/employees.php");
        exit;
    }

    // Add new employee
    $first_name  = strtoupper(trim($_POST['first_name']));
    $middle_name = strtoupper(trim($_POST['middle_name'] ?? ''));
    $last_name   = strtoupper(trim($_POST['last_name']));
    $position    = strtoupper(trim($_POST['position']));
    $position_type = strtoupper(trim($_POST['position_type']));
    $branch_id = $_POST['branch_id'];
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $hire_date = $_POST['hire_date'];
    $status = $_POST['status'];

    // Generate Employee Code
    $date_part = date('ymd', strtotime($hire_date)); 

    $result = $conn->query("SELECT COUNT(*) as total FROM employees");
    $row = $result->fetch_assoc();
    $sequence = $row['total'] + 1;

    $employee_code = $date_part . str_pad($sequence, 2, '0', STR_PAD_LEFT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO employees (employee_code, first_name, middle_name, last_name, position, position_type, email, phone, hire_date, branch_id, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("sssssssssis", $employee_code, $first_name, $middle_name, $last_name, $position, $position_type, $email, $phone, $hire_date, $branch_id, $status);
    if ($stmt->execute()) {
        $_SESSION['notif'] = [
            'message' => "Employee added successfully. Employee Code: $employee_code",
            'icon' => 'success'
        ];
    } else {
        $_SESSION['notif'] = [
            'message' => "Failed to add employee.",
            'icon' => 'error'
        ];
    }

    header("Location: ../pages/employees.php");
    exit;
}

// Delete employee
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['notif'] = [
            'message' => "Employee deleted successfully.",
            'icon' => 'success'
        ];
    } else {
        $_SESSION['notif'] = [
            'message' => "Failed to delete employee.",
            'icon' => 'error'
        ];
    }
} else {
    $_SESSION['notif'] = [
        'message' => "No employee ID provided.",
        'icon' => 'error'
    ];
}

header("Location: ../pages/employees.php");
exit;
