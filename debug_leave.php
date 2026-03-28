<?php
include 'auth/db_connect.php';

// Check leave_requests table
echo "<h2>Leave Requests Table:</h2>";
$result = $conn->query("SELECT lr.*, e.first_name, e.last_name FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id ORDER BY lr.created_at DESC");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Employee</th><th>Type</th><th>Start</th><th>End</th><th>Status</th><th>Created</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['first_name']} {$row['last_name']}</td>";
    echo "<td>{$row['leave_type']}</td>";
    echo "<td>{$row['start_date']}</td>";
    echo "<td>{$row['end_date']}</td>";
    echo "<td>{$row['status']}</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check current session
echo "<h2>Current Session:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if user can see their own requests
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    echo "<h2>Leave Requests for User ID: $user_id</h2>";
    $result = $conn->query("SELECT lr.*, e.first_name, e.last_name FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id WHERE lr.employee_id = $user_id ORDER BY lr.created_at DESC");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Employee</th><th>Type</th><th>Start</th><th>End</th><th>Status</th><th>Created</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['first_name']} {$row['last_name']}</td>";
        echo "<td>{$row['leave_type']}</td>";
        echo "<td>{$row['start_date']}</td>";
        echo "<td>{$row['end_date']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>