<?php
session_start();
include '../auth/db_connect.php';

// Fetch all employees
$query = $conn->query("SELECT * FROM employees ORDER BY last_name ASC");
$employees = $query->fetch_all(MYSQLI_ASSOC);

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?? '';
    $last_name = $_POST['last_name'];
    $position = $_POST['position'];
    $position_type = $_POST['position_type'];
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $hire_date = $_POST['hire_date'];
    $status = $_POST['status'];

    // Generate Employee Code
    $date_part = date('ymd', strtotime($hire_date)); // YYMMDD

    // Count how many employees exist in the table
    $result = $conn->query("SELECT COUNT(*) as total FROM employees");
    $row = $result->fetch_assoc();
    $sequence = $row['total'] + 1;

    $employee_code = $date_part . str_pad($sequence, 2, '0', STR_PAD_LEFT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO employees (employee_code, first_name, middle_name, last_name, position, position_type, email, phone, hire_date, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("ssssssssss", $employee_code, $first_name, $middle_name, $last_name, $position, $position_type, $email, $phone, $hire_date, $status);

    if($stmt->execute()){
        $_SESSION['success'] = "Employee added successfully. Employee Code: $employee_code";
    } else {
        $_SESSION['error'] = "Failed to add employee.";
    }

    header("Location: ../pages/employees.php");
    exit;
}

// Delete employee
if(isset($_GET['id'])){
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        $_SESSION['success'] = "Employee deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete employee.";
    }

} else {
    $_SESSION['error'] = "No employee ID provided.";
}

// Redirect back to employees page
header("Location: ../pages/employees.php");
exit;