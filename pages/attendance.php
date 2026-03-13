<?php
include '../backend/attendance.php';

// Block access if not logged in
if (!isLoggedIn()) {
    header('Location: ../auth/signin.php');
    exit();
}

if (!function_exists('formatTime')) {
    function formatTime($time)
    {
        return $time ? date('h:i A', strtotime($time)) : '-';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrimeHealth Clinic</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon-32x32.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/header.css">
    <link rel="stylesheet" href="../assets/attendance.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="dashboard">
        <?php include '../components/sidebar.php'; ?>
        <div class="main">
            <?php include '../components/header.php'; ?>
            <div class="main-layer">
                <div class="attendance-header">
                    <div class="attendance-text">
                        <h1>Attendance Records</h1>
                        <p>Review and export overall employee attendance data.</p>
                    </div>

                    <div style="display: flex; gap: 12px; align-items: flex-end;">
                        <form method="GET" class="filter-card">
                            <div class="input-group">
                                <label>From</label>
                                <input type="date" name="from" value="<?= htmlspecialchars($from_date) ?>" required>
                            </div>
                            <div class="input-group">
                                <label>To</label>
                                <input type="date" name="to" value="<?= htmlspecialchars($to_date) ?>" required onchange="this.form.submit()">
                            </div>
                            <button class="btn-view" type="submit">View</button>
                        </form>

                        <button class="btn-excel" onclick="window.location.href='../backend/download_attendance.php?from=<?= $from_date ?>&to=<?= $to_date ?>'">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Export
                        </button>
                        <button class="btn-print-dark" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button class="btn-add-attendance" onclick="toggleModal(true)">
                            <i class="bi bi-plus-circle"></i> Add Attendance
                        </button>
                    </div>
                </div>

                <div class="table-container print-area">
                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th style="text-align:right;">Total Hrs</th>
                                <th style="text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp): ?>
                                <?php
                                $fullName = htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']);
                                $initial = strtoupper(substr($emp['first_name'], 0, 1));
                                ?>

                                <?php if (isset($attendance_records[$emp['id']])): ?>
                                    <?php foreach ($attendance_records[$emp['id']] as $rec_date => $record):
                                        $t_in = $record['time_in'] ?? null;
                                        $t_out = $record['time_out'] ?? null;
                                        $hrs = totalHours($t_in, $t_out);
                                        $stat = $record['status'] ?? 'Present';
                                        $stat_class = (strtolower($stat) == 'present') ? 'status-present' : 'status-halfday';
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <div class="user-avatar"><?= $initial ?></div>
                                                    <div class="user-details">
                                                        <span class="user-fname"><?= $fullName ?></span>
                                                        <span class="position-tag"><?= htmlspecialchars($emp['position']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="date-col"><?= date('M d, Y', strtotime($rec_date)) ?></td>
                                            <td class="time-col"><?= formatTime($t_in) ?></td>
                                            <td class="time-col"><?= formatTime($t_out) ?></td>
                                            <td class="hours-col" style="text-align:right;">
                                                <?= is_numeric($hrs) ? number_format($hrs, 2) : '-' ?>
                                            </td>
                                            <td style="text-align:center;">
                                                <span class="status-badge <?= $stat_class ?>"><?= $stat ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="attendanceModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="bi bi-clock"></i> Manual Attendance</h2>
                <button class="close-btn" onclick="toggleModal(false)">&times;</button>
            </div>

            <form action="../backend/attendance.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="date" value="<?= date('Y-m-d') ?>">

                    <div class="input-group">
                        <label>Select Employee</label>
                        <select name="employee_id" required style="text-transform: uppercase;">
                            <option value="" disabled selected>Choose an employee...</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>">
                                    <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Status</label>
                        <select name="status" id="statusSelect" style="text-transform: uppercase;" required onchange="updateAttendanceUI()">
                            <option value="Present" selected>Present</option>
                            <option value="Absent">Absent</option>
                            <option value="SNW Holiday">SNW Holiday</option>
                            <option value="Holiday">Holiday</option>
                            <option value="Leave">Leave</option>
                        </select>
                    </div>

                    <div class="action-buttons-modal">
                        <div id="timeInContainer" style="display: flex; gap: 10px; flex: 2;">
                            <button type="submit" name="timein" class="btn-timein">
                                <i class="bi bi-box-arrow-in-right"></i> TIME IN
                            </button>
                            <button type="submit" name="timeout" class="btn-timeout">
                                <i class="bi bi-box-arrow-left"></i> TIME OUT
                            </button>
                        </div>

                        <button type="submit" name="save_status" id="btnSaveStatus"
                            style="display: none; background-color: #f0f4f8; color: #555; border: 1px solid #d1d9e0; flex: 1; border-radius: 8px; font-weight: 700; padding: 10px;">
                            <i class="bi bi-check-circle"></i> SAVE STATUS
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php if (isset($_SESSION['notif'])): ?>
        <script>
            Swal.fire({
                title: '<?= $_SESSION['notif']['icon'] === "success" ? "Success!" : "Notice" ?>',
                text: '<?= $_SESSION['notif']['message'] ?>',
                icon: '<?= $_SESSION['notif']['icon'] ?>',
                confirmButtonColor: '#1a6d18',
                timer: 3000,
                timerProgressBar: true
            });
        </script>
        <?php unset($_SESSION['notif']);
        ?>
    <?php endif; ?>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/modal-att.js"></script>
</body>

</html>