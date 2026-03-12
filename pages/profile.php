<?php
include '../backend/profile.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?= htmlspecialchars($emp['first_name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/sidebar.css">
    <link rel="stylesheet" href="../assets/header.css">
    <link rel="stylesheet" href="../assets/profile.css">
    <style>

    </style>
</head>

<body>
    <div class="dashboard">
        <?php include '../components/sidebar.php'; ?>

        <div class="main">
            <?php include '../components/header.php'; ?>

            <div class="content">
                <div class="employee-container">
                    <div class="print-only-header">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <h1 style="margin: 0; font-size: 24px;">PRIMEHEALTH CLINIC</h1>
                            <h2 style="margin: 0; font-size: 18px; text-transform: uppercase;">Daily Time Record</h2>
                            <p style="margin: 5px 0; font-size: 14px;">Employee Name: <strong><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></strong></p>
                            <p style="margin: 0; font-size: 12px;">ID Code: <?= htmlspecialchars($emp['employee_code']) ?> | Position: <?= htmlspecialchars($emp['position']) ?></p>
                        </div>
                    </div>
                    <div class="time-section">
                        <div class="table-header-actions">
                            <h2><i class="bi bi-clock-history"></i> Attendance History</h2>

                            <div style="display: flex; gap: 10px; align-items: flex-end;">
                                <form method="GET" style="display: flex; gap: 10px; align-items: flex-end; background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #e9ecef;">
                                    <input type="hidden" name="id" value="<?= $id ?>">

                                    <div style="display: flex; flex-direction: column;">
                                        <label style="font-size: 10px; font-weight: bold; color: #666;">FROM</label>
                                        <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>" style="padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                                    </div>

                                    <div style="display: flex; flex-direction: column;">
                                        <label style="font-size: 10px; font-weight: bold; color: #666;">TO</label>
                                        <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>" style="padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                                    </div>

                                    <button type="submit" class="btn-timein" style="padding: 7px 15px; height: 35px;">Filter</button>

                                    <?php if (isset($_GET['start_date'])): ?>
                                        <a href="profile.php?id=<?= $id ?>" style="font-size: 17px; height: 25px align-self: center; background: #dc3545; color: white; padding: 7px 15px; border-radius: 4px; text-decoration: none;">Clear</a>
                                    <?php endif; ?>
                                </form>


                                <a href="javascript:window.print()" class="btn-print" style="height: 45px; display: flex; align-items: center;"><i class="bi bi-printer"></i></a>
                            </div>
                        </div>

                        <table class="time-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($attendance_history->num_rows > 0): ?>
                                    <?php while ($row = $attendance_history->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?= date('M d, Y', strtotime($row['attendance_date'])) ?></strong></td>
                                            <td><?= formatTime($row['time_in']) ?></td>
                                            <td><?= formatTime($row['time_out']) ?></td>
                                            <td><span class="status-badge status-active">Present</span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center; padding: 20px;">No records found for this range.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <div class="pagination">
                            <?php
                            // Build query string for pagination links so filter stays active
                            $query_str = "&start_date=" . ($_GET['start_date'] ?? '') . "&end_date=" . ($_GET['end_date'] ?? '');
                            ?>

                            <?php if ($page > 1): ?>
                                <a href="?id=<?= $id ?>&page=<?= $page - 1 ?><?= $query_str ?>">Prev</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= min($total_pages, 15); $i++): ?>
                                <a href="?id=<?= $id ?>&page=<?= $i ?><?= $query_str ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?id=<?= $id ?>&page=<?= $page + 1 ?><?= $query_str ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="info-section">
                        <h2><i class="bi bi-person-badge"></i> Employee Details</h2>
                        <table class="details-table">
                            <tr>
                                <td class="label">ID Code</td>
                                <td class="value">#<?= htmlspecialchars($emp['employee_code']) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Position</td>
                                <td class="value"><?= htmlspecialchars($emp['position']) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Type</td>
                                <td class="value"><?= htmlspecialchars($emp['position_type'] ?? 'Regular') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Email</td>
                                <td class="value" style="font-size: 0.85rem;"><?= htmlspecialchars($emp['email']) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Hire Date</td>
                                <td class="value"><?= date('M d, Y', strtotime($emp['hire_date'])) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Status</td>
                                <td class="value">
                                    <span class="status-badge <?= ($emp['status'] == 'active') ? 'status-active' : 'status-inactive' ?>">
                                        <?= ucfirst($emp['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <div class="footer-container">
                            <a href="employees.php" class="back-btn">
                                <i class="bi bi-arrow-left"></i> Back to Employee List
                            </a>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
</body>

</html>