<?php
// Ensure the session is started so we can access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../auth/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
$photo = null;

if ($user_id) {
    $stmt = $conn->prepare("SELECT photo FROM user WHERE userid = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!empty($user['photo'])) {
        $photo = '../uploads/profiles/' . $user['photo'];
    }
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
    <title>Sidebar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/sidebar.css">
</head>

<body>

    <div class="sidebar" id="sidebar">
        <a href="../pages/admin_profile.php" class="user-profile-link">
            <div class="user-info">
                <div class="top-icon">
                    <?php if ($photo && file_exists($photo)): ?>
                        <img src="<?= htmlspecialchars($photo) ?>" class="profile-img">
                    <?php else: ?>
                        <i class="bi bi-person-circle"></i>
                    <?php endif; ?>
                </div>
                <span class="user-name">
                    <?php echo htmlspecialchars($display_name); ?>
                </span>
            </div>
        </a>

        <div class="toggle-btn" id="toggleBtn"><i class="bi bi-chevron-left"></i></div>

        <nav>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
                <a href="../pages/index.php"><i class="bi bi-house-door"></i> <span>Dashboard</span></a>
                <a href="../pages/employees.php"><i class="bi bi-people"></i> <span>Employees</span></a>
                <a href="../pages/attendance.php"><i class="bi bi-person-check-fill"></i> <span>Attendance</span></a>
                <a href="../pages/appointment.php"><i class="bi bi-calendar-x"></i> <span>Leave </span></a>
                <a href="../pages/branch_status.php"><i class="bi bi-geo-alt-fill"></i> <span>Branch Status</span></a>
                <a href="../pages/calendar.php"><i class="bi bi-calendar3"></i> <span>Calendar</span></a>
            <?php else: ?>
                <a href="../pages/user_dashboard.php"><i class="bi bi-clock-history"></i> <span>My Daily Record</span></a>
                <a href="../pages/appointment.php"><i class="bi bi-calendar-x"></i> <span>Leave Requests</span></a>
            <?php endif; ?>

            <a href="#" id="logoutLink"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
        </nav>
    </div>
    <script src="../assets/js/logout.js"></script>
</body>

</html>