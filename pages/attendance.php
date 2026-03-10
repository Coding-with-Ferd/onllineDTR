<?php
include '../backend/attendance.php';
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

    <style>
        /* Header */
        .attendance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .attendance-header .actions {
            display: flex;
            gap: 10px;
        }

        .attendance-header input[type="date"] {
            padding: 5px 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .attendance-header button {
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            color: #fff;
            font-size: 14px;
        }

        .btn-view {
            background-color: #3895ec;
        }

        .btn-excel {
            background-color: #1976d2;
        }

        .btn-print {
            background-color: #455a64;
        }

        /* Table */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 10px;
            border: 1px solid #1a8e4a;
            text-align: left;
        }

        .attendance-table th {
            background: linear-gradient(135deg, #0b7a3b 0%, #1a8e4a 50%, #0b7a3b 100%);
            color: #fff;
        }

        /* Status highlight */
        .status-present {
            color: green;
            font-weight: bold;
        }

        .status-absent {
            color: red;
            font-weight: bold;
        }

        .status-halfday {
            color: orange;
            font-weight: bold;
        }

        /* Print styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            /* hide everything first */
            body * {
                visibility: hidden;
            }

            /* show only print-area */
            .print-area,
            .print-area * {
                visibility: visible;
            }

            /* place print-area at top-left and full width */
            .print-area {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                padding: 0;
            }

            /* table styling */
            .attendance-table {
                width: 100%;
                border-collapse: collapse;
                margin: 0;
            }

            .attendance-table th,
            .attendance-table td {
                border: 1px solid #000;
                padding: 8px;
                font-size: 12pt;
                text-align: left;
            }

            /* centered title */
            .print-title {
                text-align: center;
                margin-bottom: 20px;
                font-size: 16pt;
                font-weight: bold;
            }

            /* hide buttons or actions */
            .actions,
            .attendance-header,
            .sidebar,
            .header {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <?php include '../components/sidebar.php'; ?>
        <div class="main">
            <?php include '../components/header.php'; ?>

            <div class="main-layer">
                <div class="content">

                    <!-- Header with date range & actions -->
                    <div class="attendance-header">
                        <h1>Attendance</h1>
                        <div class="actions">
                            <form method="GET" style="display:flex; gap:5px; align-items:center;">
                                From: <input type="date" name="from" value="<?= htmlspecialchars($from_date) ?>" required>
                                To: <input type="date" name="to" value="<?= htmlspecialchars($to_date) ?>" required onchange="this.form.submit()">
                                <button class="btn-view" type="submit">View</button>
                            </form>
                            <button class="btn-excel" onclick="window.location.href='../backend/download_attendance.php?from=<?= $from_date ?>&to=<?= $to_date ?>'">
                                <i class="bi bi-file-earmark-excel"></i> Download Excel
                            </button>
                            <button class="btn-print" onclick="window.print()">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>

                    <!-- Attendance table -->
                    <div class="print-area">
                        <h1 class="print-title" style="text-align:center">DTR Record</h1>
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Position</th>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Total Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $emp): ?>
                                    <?php
                                    if (isset($attendance_records[$emp['id']])) {
                                        foreach ($attendance_records[$emp['id']] as $date => $record):
                                            $time_in = $record['time_in'] ?? null;
                                            $time_out = $record['time_out'] ?? null;
                                            $total_hours = totalHours($time_in, $time_out);
                                            $status = $record['status'] ?? 'Absent';
                                            $status_class = match (strtolower($status)) {
                                                'present' => 'status-present',
                                                'half day' => 'status-halfday',
                                                default => 'status-absent'
                                            };
                                    ?>
                                            <tr>
                                                <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                                                <td><?= htmlspecialchars($emp['position']) ?></td>
                                                <td><?= date('M d, Y', strtotime($date)) ?></td>
                                                <td><?= formatTime($time_in) ?></td>
                                                <td><?= formatTime($time_out) ?></td>
                                                <td><?= is_numeric($total_hours) ? number_format($total_hours, 2) : '-' ?></td>
                                                <td class="<?= $status_class ?>"><?= $status ?></td>
                                            </tr>
                                        <?php endforeach;
                                    } else { ?>
                                        <tr>
                                            <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                                            <td><?= htmlspecialchars($emp['position']) ?></td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td class="status-absent">Absent</td>
                                        </tr>
                                    <?php } ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
</body>

</html>