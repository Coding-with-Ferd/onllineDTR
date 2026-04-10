<?php
include '../backend/attendance.php';

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
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/header.css">
    <link rel="stylesheet" href="../assets/attendance.css">

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

                        <button class="btn-excel" onclick="confirmExport()">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Export
                        </button>
                        <button onclick="openPrintPreview()" class="btn-print-dark">
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
                                <th>Remarks</th>
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
                                        $stat_lower = strtolower($stat);

                                        if ($stat_lower === 'present') {
                                            $stat_class = 'status-present';
                                        } elseif ($stat_lower === 'absent') {
                                            $stat_class = 'status-absent';
                                        } else {
                                            $stat_class = 'status-onleave';
                                        }
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
                                            <td class="remarks-col">
                                                <?php if (!empty($record['remarks'])): ?>
                                                    <button
                                                        class="btn-remarks"
                                                        onclick="showRemarks(`<?= htmlspecialchars(addslashes($record['remarks'])) ?>`)">
                                                        View
                                                    </button>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
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
    <!-- Remarks Modal -->
    <div id="remarksModal" class="remarks-modal">
        <div class="remarks-modal-content">
            <span class="remarks-close" onclick="closeRemarksModal()">&times;</span>
            <h3>Remarks</h3>
            <p id="remarksText"></p>
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
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Remarks (Optional)</label>
                        <input
                            type="text"
                            name="remarks"
                            placeholder="Enter remarks (e.g. Late, Half-day, Off-site...)"
                            style="text-transform: uppercase;">
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
    <?php include '../components/notif.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/modal-att.js"></script>
    <script>
        function openPrintPreview() {
            const url = "../components/print_attendance.php?from=<?= urlencode($from_date) ?>&to=<?= urlencode($to_date) ?>";
            window.location.href = url;
        }

        function showRemarks(remarks) {
            document.getElementById("remarksText").innerText = remarks;
            const modal = document.getElementById("remarksModal");

            modal.style.display = "flex";
            setTimeout(() => modal.classList.add("show"), 10);
        }

        function closeRemarksModal() {
            const modal = document.getElementById("remarksModal");
            modal.classList.remove("show");
            setTimeout(() => modal.style.display = "none", 300);
        }

        function confirmExport() {
            Swal.fire({
                title: 'Export attendance?',
                text: 'Do you want to save the attendance file?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a6d18',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, export it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../backend/download_attendance.php?from=<?= urlencode($from_date) ?>&to=<?= urlencode($to_date) ?>";
                }
            });
        }
    </script>
</body>

</html>