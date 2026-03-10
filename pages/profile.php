<?php
include '../backend/profile.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details - <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/header.css">

    <style>
        .employee-container {
            display: flex;
            gap: 20px;
            max-width: 1800px;
            margin: 30px auto;
            flex-wrap: wrap;
            height: 93%;
        }

        /* Attendance section takes most space */
        .time-section {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            flex: 3;
            /* majority width */
            min-width: 700px;
        }

        /* Employee info smaller on right */
        .info-section {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            flex: 1;
            /* smaller width */
            min-width: 300px;
            height: fit-content;
        }

        .time-table,
        .employee-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .time-table th,
        .time-table td,
        .employee-details table td {
            padding: 10px;
            border: 1px solid #1a8e4a;
            text-align: center;
        }

        .time-table th {
            background: linear-gradient(135deg, #0b7a3b 0%, #1a8e4a 50%, #0b7a3b 100%);
            color: #fff;
        }

        .employee-details table td.label {
            font-weight: bold;
            width: 150px;
            color: #1a6d18;
            text-align: left;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #1a6d18;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }

        .back-btn:hover {
            opacity: 0.9;
        }

        /* Buttons inside action column */
        .btn-timein {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-timeout {
            background-color: #f44336;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="dashboard">

        <?php include '../components/sidebar.php'; ?>
        <div class="main">
            <?php include '../components/header.php'; ?>

                <div class="content">
                    <div class="employee-container">

                        <!-- Left: Attendance -->
                        <div class="time-section">
                            <h2>Attendance Today</h2>
                            <table class="time-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= date('M d, Y') ?></td>
                                        <td><?= formatTime($attendance['time_in'] ?? null) ?></td>
                                        <td><?= formatTime($attendance['time_out'] ?? null) ?></td>
                                        <td>
                                            <form action="../backend/attendance.php" method="POST" style="display:flex; gap:5px; justify-content:center;">
                                                <input type="hidden" name="employee_id" value="<?= $id ?>">
                                                <input type="hidden" name="date" value="<?= $today ?>">
                                                <button type="submit" name="timein" class="btn-timein" <?= isset($attendance['time_in']) ? 'disabled' : '' ?>>Time In</button>
                                                <button type="submit" name="timeout" class="btn-timeout" <?= !isset($attendance['time_in']) || isset($attendance['time_out']) ? 'disabled' : '' ?>>Time Out</button>
                                            </form>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Right: Employee Info -->
                        <div class="info-section employee-details">
                            <h2>Employee Details</h2>
                            <table>
                                <tr>
                                    <td class="label">Employee Number</td>
                                    <td><?= htmlspecialchars($emp['employee_code']) ?></td>
                                </tr>
                                <tr>
                                    <td class="label">Full Name</td>
                                    <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['middle_name'] . ' ' . $emp['last_name']) ?></td>
                                </tr>
                                <tr>
                                    <td class="label">Position</td>
                                    <td><?= htmlspecialchars($emp['position']) ?></td>
                                </tr>
                                <tr>
                                    <td class="label">Position Type</td>
                                    <td><?= htmlspecialchars($emp['position_type'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="label">Email</td>
                                    <td><?= htmlspecialchars($emp['email']) ?></td>
                                </tr>
                                <tr>
                                    <td class="label">Phone</td>
                                    <td><?= htmlspecialchars($emp['phone']) ?></td>
                                </tr>
                                <tr>
                                    <td class="label">Hire Date</td>
                                    <td><?= htmlspecialchars(date('M d, Y', strtotime($emp['hire_date']))) ?></td>
                                </tr>
                                <tr>
                                    <td class="label">Status</td>
                                    <td><?= ucfirst($emp['status']) ?></td>
                                </tr>
                            </table>

                            <a href="employees.php" class="back-btn"><i class="bi bi-arrow-left"></i> Back to Employees</a>
                        </div>

                    </div>
                </div>
        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
</body>

</html>