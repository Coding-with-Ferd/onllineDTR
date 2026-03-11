<?php
include '../backend/attendance.php';

if (!function_exists('formatTime')) {
    function formatTime($time) {
        return $time ? date('h:i A', strtotime($time)) : '-';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - PrimeHealth</title>
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
                    <div>
                        <h1 style="margin:0; color:#1e293b;">DTR Records</h1>
                        <p style="margin:0; color:#64748b; font-size:14px;">Review and export employee attendance data.</p>
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
                            <i class="bi bi-printer"></i>
                        </button>
                    </div>
                </div>

                <div class="table-container print-area">
                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Position</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Total Hrs</th>
                                <th style="text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp): ?>
                                <?php if (isset($attendance_records[$emp['id']])): ?>
                                    <?php foreach ($attendance_records[$emp['id']] as $rec_date => $record): 
                                        $t_in = $record['time_in'] ?? null;
                                        $t_out = $record['time_out'] ?? null;
                                        $hrs = totalHours($t_in, $t_out);
                                        $stat = $record['status'] ?? 'Present';
                                        $stat_class = (strtolower($stat) == 'present') ? 'status-present' : 'status-halfday';
                                    ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($emp['first_name'].' '.$emp['last_name']) ?></strong></td>
                                            <td style="color:#64748b; font-size:12px;"><?= htmlspecialchars($emp['position']) ?></td>
                                            <td><?= date('M d, Y', strtotime($rec_date)) ?></td>
                                            <td><?= formatTime($t_in) ?></td>
                                            <td><?= formatTime($t_out) ?></td>
                                            <td style="font-weight:600;"><?= is_numeric($hrs) ? number_format($hrs, 2) : '-' ?></td>
                                            <td style="text-align:center;"><span class="status-badge <?= $stat_class ?>"><?= $stat ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($emp['first_name'].' '.$emp['last_name']) ?></strong></td>
                                        <td style="color:#64748b; font-size:12px;"><?= htmlspecialchars($emp['position']) ?></td>
                                        <td style="color:#cbd5e1;">-</td>
                                        <td style="color:#cbd5e1;">-</td>
                                        <td style="color:#cbd5e1;">-</td>
                                        <td style="color:#cbd5e1;">-</td>
                                        <td style="text-align:center;"><span class="status-badge status-absent">Absent</span></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
</body>

</html>