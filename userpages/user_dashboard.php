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

    $hasTimeIn = $attendanceRecord && !empty($attendanceRecord['time_in']);
    $hasTimeOut = $attendanceRecord && !empty($attendanceRecord['time_out']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrimeHealth Clinic</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/user_dashboard.css">
    <link rel="stylesheet" href="../assets/user_dashboard_header.css">
</head>

<body>
    <div class="dashboard">
        <div class="main">

            <?php include '../components/user_dashboard_header.php'; ?>

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
                        <div class="welcome-text">
                            <h1>Welcome back, <?= htmlspecialchars($employee['first_name']) ?>!</h1>
                            <p>Here is your daily attendance status at PrimeHealth Clinic.</p>
                        </div>

                        <div class="user-details">
                            <span><i class="bi bi-person-badge"></i> <?= htmlspecialchars($emp_position) ?></span>
                            <span><i class="bi bi-calendar-event"></i> <?= date("F d, Y") ?></span>
                        </div>
                    </div>

                    <div class="time-card-container">
                        <div class="time-card">
                            <div class="card-status-header">
                                <span class="status-label">
                                    <?php if (!$hasTimeIn): ?>
                                        <i class="bi bi-clock-history"></i> Not Timed In Yet
                                    <?php elseif ($hasTimeIn && !$hasTimeOut): ?>
                                        <i class="bi bi-hourglass-split"></i> Currently Timed In
                                    <?php else: ?>
                                        <i class="bi bi-check2-circle"></i> Attendance Completed
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="clock-display" id="liveClock">00:00:00</div>
                            <div class="date-display" id="liveDate">Loading...</div>
                            <div class="position-badge"><?= htmlspecialchars($emp_position) ?></div>

                            <form action="../backend/attendance.php" method="POST" id="attendanceForm" class="attendance-actions">
                                <input type="hidden" name="employee_id" value="<?= $emp_id ?>">
                                <input type="hidden" name="date" value="<?= $dateToday ?>">
                                <input type="hidden" name="user_dashboard_redirect" value="1">

                                <?php if (!$hasTimeIn): ?>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="statusSelect"><i class="bi bi-ui-checks-grid"></i> Status</label>
                                            <select name="status" id="statusSelect" class="form-input" required onchange="updateAttendanceUI()">
                                                <option value="Present" selected>Present</option>
                                                <option value="Absent">Absent</option>
                                                <option value="SNW Holiday">SNW Holiday</option>
                                                <option value="Holiday">Holiday</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="remarks"><i class="bi bi-chat-left-text"></i> Remarks</label>
                                            <input
                                                type="text"
                                                id="remarks"
                                                name="remarks"
                                                class="form-input"
                                                placeholder="Enter remarks (e.g. Late, Half-day, Off-site...)"
                                                style="text-transform: uppercase;">
                                        </div>
                                    </div>

                                    <div class="action-buttons-modal employee-actions">
                                        <div id="timeInContainer" class="dual-action-buttons">
                                            <button type="submit" name="timein" class="btn-time-in">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                                <span>TIME IN</span>
                                            </button>

                                            <button type="button" class="btn-time-out disabled tooltip" data-tooltip="You must Time In first" disabled>
                                                <i class="bi bi-box-arrow-left"></i>
                                                <span>TIME OUT</span>
                                            </button>
                                        </div>

                                        <button type="submit" name="save_status" id="btnSaveStatus" class="btn-save-status" style="display: none;">
                                            <i class="bi bi-check-circle"></i>
                                            <span>SAVE STATUS</span>
                                        </button>
                                    </div>

                                <?php elseif ($hasTimeIn && !$hasTimeOut): ?>
                                    <div class="attendance-summary">
                                        <div class="summary-item">
                                            <span class="summary-label">Status</span>
                                            <span class="summary-value"><?= htmlspecialchars($attendanceRecord['status'] ?? 'Present') ?></span>
                                        </div>

                                        <div class="summary-item">
                                            <span class="summary-label">Remarks</span>
                                            <span class="summary-value"><?= !empty($attendanceRecord['remarks']) ? htmlspecialchars($attendanceRecord['remarks']) : '-' ?></span>
                                        </div>

                                        <div class="summary-item">
                                            <span class="summary-label">Time In</span>
                                            <span class="summary-value"><?= !empty($attendanceRecord['time_in']) ? date("h:i A", strtotime($attendanceRecord['time_in'])) : '-' ?></span>
                                        </div>
                                    </div>

                                    <div class="action-buttons-modal employee-actions">
                                        <button type="button" class="btn-time-in disabled" disabled>
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span>Timed In at <?= date("h:i A", strtotime($attendanceRecord['time_in'])) ?></span>
                                        </button>

                                        <button type="submit" name="timeout" class="btn-time-out">
                                            <i class="bi bi-box-arrow-left"></i>
                                            <span>TIME OUT</span>
                                        </button>
                                    </div>

                                <?php else: ?>
                                    <div class="attendance-summary">
                                        <div class="summary-item">
                                            <span class="summary-label">Status</span>
                                            <span class="summary-value"><?= htmlspecialchars($attendanceRecord['status'] ?? 'Present') ?></span>
                                        </div>

                                        <div class="summary-item">
                                            <span class="summary-label">Remarks</span>
                                            <span class="summary-value"><?= !empty($attendanceRecord['remarks']) ? htmlspecialchars($attendanceRecord['remarks']) : '-' ?></span>
                                        </div>

                                        <div class="summary-item">
                                            <span class="summary-label">Time In</span>
                                            <span class="summary-value"><?= !empty($attendanceRecord['time_in']) ? date("h:i A", strtotime($attendanceRecord['time_in'])) : '-' ?></span>
                                        </div>

                                        <div class="summary-item">
                                            <span class="summary-label">Time Out</span>
                                            <span class="summary-value"><?= !empty($attendanceRecord['time_out']) ? date("h:i A", strtotime($attendanceRecord['time_out'])) : '-' ?></span>
                                        </div>
                                    </div>

                                    <div class="action-buttons-modal employee-actions">
                                        <button type="button" class="btn-time-in disabled" disabled>
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span>Timed In at <?= date("h:i A", strtotime($attendanceRecord['time_in'])) ?></span>
                                        </button>

                                        <button type="button" class="btn-time-out disabled" disabled>
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span>Timed Out at <?= date("h:i A", strtotime($attendanceRecord['time_out'])) ?></span>
                                        </button>
                                    </div>

                                    <div class="success-message">
                                        <i class="bi bi-info-circle-fill"></i>
                                        Attendance completed for today.
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
        function updateClock() {
            const now = new Date();

            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12;
            hours = hours ? hours : 12;

            hours = hours.toString().padStart(2, '0');
            minutes = minutes.toString().padStart(2, '0');
            seconds = seconds.toString().padStart(2, '0');

            const liveClock = document.getElementById('liveClock');
            const liveDate = document.getElementById('liveDate');

            if (liveClock) {
                liveClock.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
            }

            if (liveDate) {
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                liveDate.textContent = now.toLocaleDateString('en-US', options);
            }
        }

        function updateAttendanceUI() {
            const status = document.getElementById('statusSelect');
            const timeInContainer = document.getElementById('timeInContainer');
            const btnSaveStatus = document.getElementById('btnSaveStatus');

            if (!status || !timeInContainer || !btnSaveStatus) return;

            if (status.value === 'Present') {
                timeInContainer.style.display = 'flex';
                btnSaveStatus.style.display = 'none';
            } else {
                timeInContainer.style.display = 'none';
                btnSaveStatus.style.display = 'flex';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateClock();
            updateAttendanceUI();
            setInterval(updateClock, 1000);
        });

        <?php
        if (isset($_SESSION['notif'])) {
            $notif = $_SESSION['notif'];
            echo "
            Swal.fire({
                icon: '{$notif['icon']}',
                title: " . json_encode($notif['message']) . ",
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