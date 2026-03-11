<?php
// Ensure the session is started so we can access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: ../auth/signin.php');
    exit;
}

// Get user name from session, fallback to 'Guest' if not set
$display_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Example</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
</head>

<body>

    <div class="sidebar" id="sidebar">
        <a href="../pages/profile.php" class="user-profile-link"></a>
        <div class="user-info">
            <div class="top-icon"><i class="bi bi-person-circle"></i></div>
            <span class="user-name"><?php echo htmlspecialchars($display_name); ?></span>
        </div>

        <div class="toggle-btn" id="toggleBtn"><i class="bi bi-chevron-left"></i></div>

        <nav>
            <a href="../pages/index.php"><i class="bi bi-house-door"></i> <span>Dashboard</span></a>
            <a href="../pages/employees.php"><i class="bi bi-people"></i> <span>Employees</span></a>
            <a href="../pages/attendance.php"><i class="bi bi-person-check-fill"></i> <span>Attendance</span></a>
            <a href="../pages/appointment.php"><i class="bi bi-journal-plus"></i> <span>Appointment</span></a>
            <a href="../pages/daily_status.php"><i class="bi bi-geo-alt-fill"></i> <span>Branch Status</span></a>
            <a href="../pages/calendar.php"><i class="bi bi-calendar3"></i> <span>Calendar</span></a>
            <a href="?action=logout"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
        </nav>
    </div>

</body>

</html>