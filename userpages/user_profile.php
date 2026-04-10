<?php
require_once '../config/session.php';
include '../auth/db_connect.php';

if (!isLoggedIn() || !isEmployee()) {
    header("Location: ../auth/signin.php");
    exit;
}

$employee_code = $_SESSION['user_email'];

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');

// Handle Photo Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    $stmt = $conn->prepare("SELECT id FROM employees WHERE employee_code = ?");
    $stmt->bind_param("s", $employee_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $emp_data = $result->fetch_assoc();
    $id = $emp_data['id'];

    $uploadEmployeeId = $id;
    if ($uploadEmployeeId !== $id) {
        $_SESSION['notif'] = ['message' => 'Mismatched employee ID.', 'icon' => 'error'];
        header("Location: user_profile.php");
        exit;
    }

    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['notif'] = ['message' => 'Please select a valid file.', 'icon' => 'error'];
        header("Location: user_profile.php");
        exit;
    }

    $fileTmp = $_FILES['profile_photo']['tmp_name'];
    $fileName = basename($_FILES['profile_photo']['name']);
    $fileSize = $_FILES['profile_photo']['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileExt, $allowedExt)) {
        $_SESSION['notif'] = ['message' => 'Only JPG, PNG, and GIF files are allowed.', 'icon' => 'error'];
        header("Location: user_profile.php");
        exit;
    }

    if ($fileSize > 5 * 1024 * 1024) {
        $_SESSION['notif'] = ['message' => 'File too large. Max 5MB.', 'icon' => 'error'];
        header("Location: user_profile.php");
        exit;
    }

    $uploadDir = realpath(__DIR__ . '/../assets/uploads');
    if (!$uploadDir) {
        mkdir(__DIR__ . '/../assets/uploads', 0755, true);
        $uploadDir = __DIR__ . '/../assets/uploads';
    }

    // Delete old photo
    $oldPhotoStmt = $conn->prepare("SELECT photo FROM employees WHERE id = ?");
    $oldPhotoStmt->bind_param("i", $id);
    $oldPhotoStmt->execute();
    $oldPhotoResult = $oldPhotoStmt->get_result();
    $oldPhoto = $oldPhotoResult->fetch_assoc()['photo'];
    if ($oldPhoto && file_exists(__DIR__ . '/../' . $oldPhoto)) {
        unlink(__DIR__ . '/../' . $oldPhoto);
    }
    $oldPhotoStmt->close();

    $newFileName = 'employee_' . $id . '_' . time() . '.' . $fileExt;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;

    if (!move_uploaded_file($fileTmp, $targetPath)) {
        $_SESSION['notif'] = ['message' => 'Failed to move the uploaded file.', 'icon' => 'error'];
        header("Location: user_profile.php");
        exit;
    }

    $photoPath = 'assets/uploads/' . $newFileName;
    $updateStmt = $conn->prepare("UPDATE employees SET photo = ? WHERE id = ?");
    $updateStmt->bind_param('si', $photoPath, $id);
    $updateStmt->execute();
    $updateStmt->close();
    header("Location: user_profile.php");
    exit;
}

// Fetch Employee Data
$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_code = ?");
$stmt->bind_param("s", $employee_code);
$stmt->execute();
$result = $stmt->get_result();
$emp = $result->fetch_assoc();

if (!$emp) {
    $_SESSION['error'] = "Employee not found.";
    header("Location: user_dashboard.php");
    exit;
}

$id = $emp['id'];

// Pagination 
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

$filter_sql = "";
if ($start_date && $end_date) {
    $filter_sql = " AND attendance_date BETWEEN '$start_date' AND '$end_date' ";
}

$sql_history = "SELECT * FROM attendance WHERE employee_id = ? $filter_sql ORDER BY attendance_date DESC LIMIT ? OFFSET ?";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("iii", $id, $limit, $offset);
$stmt_history->execute();
$attendance_history = $stmt_history->get_result();

$sql_total = "SELECT COUNT(*) as total FROM attendance WHERE employee_id = ? $filter_sql";
$total_stmt = $conn->prepare($sql_total);
$total_stmt->bind_param("i", $id);
$total_stmt->execute();
$total_rows = $total_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

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
    <link rel="stylesheet" href="../assets/user_profile.css">
    <link rel="stylesheet" href="../assets/user_dashboard_header.css">
</head>

<body>
    <div class="dashboard">

        <div class="main">
            <?php include '../components/user_dashboard_header.php'; ?>

            <div class="content">
                <div class="employee-container">

                    <?php if (isset($_SESSION['notif'])): ?>
                        <div class="alert <?= $_SESSION['notif']['icon'] === 'success' ? 'alert-success' : 'alert-error' ?>" style="margin-bottom: 16px; padding: 12px; border-radius: 8px;">
                            <?= htmlspecialchars($_SESSION['notif']['message']) ?>
                        </div>
                        <?php unset($_SESSION['notif']); ?>
                    <?php endif; ?>

                    <div class="print-only-header">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <h1 style="margin: 0; font-size: 24px;">PRIMEHEALTH CLINIC</h1>
                            <h2 style="margin: 0; font-size: 18px; text-transform: uppercase;">Daily Time Record</h2>
                            <p style="margin: 5px 0; font-size: 14px;">Employee Name: <strong><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></strong></p>
                            <p style="margin: 0; font-size: 12px;">ID Code: <?= htmlspecialchars($emp['employee_code']) ?> | Position: <?= htmlspecialchars($emp['position']) ?></p>
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
                                <td class="value">
                                    <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                                </td>
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
                                    <span class="status-badge <?= ($emp['status'] == 'active') ? 'status-active' : 'status-inactive' ?>">
                                        <?= ucfirst($emp['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="time-section">
                        <div class="table-header-actions">
                            <h2><i class="bi bi-clock-history"></i> Attendance History</h2>

                            <div style="display: flex; gap: 10px; align-items: flex-end;">
                                <form method="GET" style="display: flex; gap: 10px; align-items: flex-end; background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #e9ecef;">

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
                                        <a href="user_profile.php" style="font-size: 17px; height: 25px; align-self: center; background: #dc3545; color: white; padding: 7px 15px; border-radius: 4px; text-decoration: none;">Clear</a>
                                    <?php endif; ?>
                                </form>

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
                                            <td>
                                                <?php
                                                $statusText = $row['status'] ?? 'Present';
                                                $statusClass = strtolower($statusText) === 'active' ? 'status-active' : 'status-inactive';

                                                if (in_array($statusText, ['Present', 'Holiday', 'SNW Holiday', 'Leave'])) {
                                                    $statusClass = 'status-active';
                                                } elseif ($statusText === 'Absent') {
                                                    $statusClass = 'status-inactive';
                                                }
                                                ?>
                                                <span class="status-badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars($statusText) ?>
                                                </span>
                                            </td>
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

                </div>
            </div>
        </div>
    </div>
</body>

</html>