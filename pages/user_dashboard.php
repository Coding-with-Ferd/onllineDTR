<?php
require_once '../config/session.php';
require_once '../auth/db_connect.php';

// Block access if not logged in
if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}

date_default_timezone_set('Asia/Manila');

// Ensure user has an associated employee record
$userEmail = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT id, first_name, last_name, position FROM employees WHERE email = ? OR employee_code = ?");
$stmt->bind_param("ss", $userEmail, $userEmail);
$stmt->execute();
$employeeResult = $stmt->get_result();

if ($employeeResult->num_rows === 0) {
    // Cannot proceed if no linked employee profile
    $noEmployeeProfile = true;
} else {
    $noEmployeeProfile = false;
    $employee = $employeeResult->fetch_assoc();
    $emp_id = $employee['id'];
    $emp_name = $employee['first_name'] . ' ' . $employee['last_name'];
    $emp_position = $employee['position'];

    // Check today's attendance status
    $dateToday = date('Y-m-d');
    $checkStmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?");
    $checkStmt->bind_param("is", $emp_id, $dateToday);
    $checkStmt->execute();
    $attendanceRecord = $checkStmt->get_result()->fetch_assoc();

    $hasTimeIn = $attendanceRecord && $attendanceRecord['time_in'];
    $hasTimeOut = $attendanceRecord && $attendanceRecord['time_out'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - PrimeHealth Clinic</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon-32x32.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/user_dashboard.css">
    <link rel="stylesheet" href="../assets/user_dashboard_header.css">
</head>

<body>
    <div class="dashboard">

        <div class="main">

            <!-- User Dashboard Header -->
            <?php include '../components/user_dashboard_header.php'; ?>

            <!-- Main content -->
            <div class="content">

                <?php if ($noEmployeeProfile): ?>
                    <div class="error-state">
                        <i class="bi bi-exclamation-triangle-fill error-icon"></i>
                        <h2>No Employee Profile Found</h2>
                        <p>We couldn't find an employee record associated with your email (<?= htmlspecialchars($userEmail) ?>).</p>
                        <p>Please contact HR or your administrator to link your account to an employee profile.</p>
                    </div>
                <?php else: ?>
                    <div class="welcome-header">
                        <h1>Welcome back, <?= htmlspecialchars($employee['first_name']) ?>!</h1>
                        <p>Here is your daily attendance status at PrimeHealth Clinic.</p>
                    </div>

                    <div class="time-card-container">
                        <div class="time-card">
                            <div class="clock-display" id="liveClock">00:00:00</div>
                            <div class="date-display" id="liveDate">Loading...</div>
                            <div class="position-badge"><?= htmlspecialchars($emp_position) ?></div>

                            <form action="../backend/attendance.php" method="POST" id="attendanceForm" class="attendance-actions">
                                <input type="hidden" name="employee_id" value="<?= $emp_id ?>">
                                <input type="hidden" name="date" value="<?= $dateToday ?>">
                                <input type="hidden" name="status" value="Present">
                                <input type="hidden" name="user_dashboard_redirect" value="1">

                                <?php if (!$hasTimeIn): ?>
                                    <button type="submit" name="timein" class="btn-time-in">
                                        <i class="bi bi-box-arrow-in-right"></i> Time In
                                    </button>
                                    <button type="button" class="btn-time-out disabled tooltip" data-tooltip="You must Time In first" disabled>
                                        <i class="bi bi-box-arrow-right"></i> Time Out
                                    </button>
                                <?php elseif ($hasTimeIn && !$hasTimeOut): ?>
                                    <button type="button" class="btn-time-in disabled" disabled>
                                        <i class="bi bi-check-circle-fill"></i> Timed In at <?= date("h:i A", strtotime($attendanceRecord['time_in'])) ?>
                                    </button>
                                    <button type="submit" name="timeout" class="btn-time-out">
                                        <i class="bi bi-box-arrow-right"></i> Time Out
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn-time-in disabled" disabled>
                                        <i class="bi bi-check-circle-fill"></i> Timed In at <?= date("h:i A", strtotime($attendanceRecord['time_in'])) ?>
                                    </button>
                                    <button type="button" class="btn-time-out disabled" disabled>
                                        <i class="bi bi-check-circle-fill"></i> Timed Out at <?= date("h:i A", strtotime($attendanceRecord['time_out'])) ?>
                                    </button>
                                    <div class="success-message mt-3 text-success font-weight-bold">
                                        <i class="bi bi-info-circle-fill"></i> Attendance completed for today.
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>
    <script>
        // Live Clock JavaScript
        function updateClock() {
            const now = new Date();

            // Format time: HH:MM:SS AM/PM
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'

            hours = hours.toString().padStart(2, '0');
            minutes = minutes.toString().padStart(2, '0');
            seconds = seconds.toString().padStart(2, '0');

            document.getElementById('liveClock').textContent = `${hours}:${minutes}:${seconds} ${ampm}`;

            // Format Date: Monday, January 1, 2024
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            document.getElementById('liveDate').textContent = now.toLocaleDateString('en-US', options);
        }

        setInterval(updateClock, 1000);
        updateClock(); // Initial call

        <?php
        // Display notification if any
        if (isset($_SESSION['notif'])) {
            $notif = $_SESSION['notif'];
            echo "
            Swal.fire({
                icon: '{$notif['icon']}',
                title: '{$notif['message']}',
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end'
            });
            ";
            unset($_SESSION['notif']);
        }
        ?>
    </script>
</body>

</html>