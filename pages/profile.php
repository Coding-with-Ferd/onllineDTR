<?php
include '../backend/profile.php';
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
    <link rel="stylesheet" href="../assets/header.css">
    <link rel="stylesheet" href="../assets/profile.css">
</head>

<body>
    <div class="dashboard">

        <div class="main">
            <?php include '../components/header.php'; ?>

            <div class="content">
                <div class="employee-container">

                    <?php
                    $attendanceRows = [];

                    if ($attendance_history && $attendance_history->num_rows > 0) {
                        mysqli_data_seek($attendance_history, 0);
                        while ($row = $attendance_history->fetch_assoc()) {
                            $attendanceRows[] = $row;
                        }
                    }
                    ?>

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
                                        <a href="profile.php?id=<?= $id ?>" style="font-size: 17px; background: #dc3545; color: white; padding: 7px 15px; border-radius: 4px; text-decoration: none;">Clear</a>
                                    <?php endif; ?>
                                </form>
                                <a href="../components/export_excel.php?id=<?= $id ?>&start_date=<?= urlencode($_GET['start_date'] ?? '') ?>&end_date=<?= urlencode($_GET['end_date'] ?? '') ?>"
                                    class="btn-export-excel">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Export
                                </a>

                                <a href="../components/print_preview.php?id=<?= $id ?>&start_date=<?= urlencode($_GET['start_date'] ?? '') ?>&end_date=<?= urlencode($_GET['end_date'] ?? '') ?>"
                                    class="btn-print"
                                    style="height: 45px; display: flex; align-items: center;">
                                    <i class="bi bi-printer"></i>
                                </a>
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
                                <?php if (!empty($attendanceRows)): ?>
                                    <?php foreach ($attendanceRows as $row): ?>
                                        <tr>
                                            <td><strong><?= date('M d, Y', strtotime($row['attendance_date'])) ?></strong></td>
                                            <td><?= formatTime($row['time_in']) ?></td>
                                            <td><?= formatTime($row['time_out']) ?></td>
                                            <td>
                                                <?php
                                                $status = $row['status'] ?? 'Present';

                                                $statusClass = 'status-active';
                                                if ($status === 'Present') {
                                                    $statusClass = 'status-active';
                                                } elseif ($status === 'Absent') {
                                                    $statusClass = 'status-inactive';
                                                } else {
                                                    $statusClass = 'status-onleave';
                                                }
                                                ?>
                                                <span class="status-badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars($status) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center; padding: 20px;">No records found for this range.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <div class="pagination">
                            <?php
                            $query_str = "&start_date=" . ($_GET['start_date'] ?? '') . "&end_date=" . ($_GET['end_date'] ?? '');
                            ?>

                            <?php if ($page > 1): ?>
                                <a href="?id=<?= $id ?>&page=<?= $page - 1 ?><?= $query_str ?>">Prev</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= min($total_pages, 8); $i++): ?>
                                <a href="?id=<?= $id ?>&page=<?= $i ?><?= $query_str ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?id=<?= $id ?>&page=<?= $page + 1 ?><?= $query_str ?>">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="info-section" style="text-transform: uppercase;">
                        <div class="photo-uploader">
                            <form method="post" enctype="multipart/form-data" id="photoUploadForm">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <input type="hidden" name="upload_photo" value="1">
                                <input type="file" name="profile_photo" id="profile_photo" accept="image/*" style="display:none;" onchange="this.form.submit();">
                                <button type="button" class="profile-avatar" onclick="document.getElementById('profile_photo').click();" aria-label="Upload profile image">
                                    <?php if (!empty($emp['photo'])): ?>
                                        <img src="../<?= htmlspecialchars($emp['photo']) ?>" alt="Profile photo">
                                    <?php else: ?>
                                        <span><?= htmlspecialchars(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>

                        <h2><i class="bi bi-person-badge"></i> Employee Details</h2>
                        <table class="details-table">
                            <tr>
                                <td class="label">Employee Number</td>
                                <td class="value">#<?= htmlspecialchars($emp['employee_code']) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Fullname</td>
                                <td class="value"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Position</td>
                                <td class="value"><?= htmlspecialchars($emp['position']) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Type</td>
                                <td class="value"><?= htmlspecialchars($emp['position_type'] ?? 'Regular') ?></td>
                            </tr>
                            <tr style="text-transform: lowercase;">
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
                                    <span class="status-badge <?= ($emp['status'] == 'active') ? 'status-active' : (($emp['status'] == 'on leave') ? 'status-on-leave' : 'status-inactive') ?>">
                                        <?= ucfirst($emp['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <div class="footer-container">
                            <a href="employees.php" class="back-btn">
                                <i class="bi bi-arrow-left"></i> Back to Employee List
                            </a>

                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
                                <form action="../backend/generate_account.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="employee_id" value="<?= $id ?>">
                                    <?php if ($hasUserAccount): ?>
                                        <button type="button" class="btn-generate-acc" disabled style="background: #e0e0e0; color: #9e9e9e; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; cursor: not-allowed; display: flex; align-items: center; gap: 8px;" title="Account already exists for this employee.">
                                            <i class="bi bi-person-check-fill"></i> Account Exists
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn-generate-acc" onclick="confirmGenerateAcc(this.form)" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                            <i class="bi bi-person-plus-fill"></i> Generate Acc
                                        </button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php include '../components/notif.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmGenerateAcc(form) {
            Swal.fire({
                title: 'Generate Account?',
                text: "This will create a new user account for this employee using their Employee Code as the email.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, generate it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</body>

</html>