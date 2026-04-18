<?php
require_once '../config/session.php';
include '../auth/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Manila');

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/signin.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Fetch admin data
$stmt = $conn->prepare("SELECT userid, fullname, email, status, role, photo FROM user WHERE userid = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    die("Admin account not found.");
}

// Handle photo display
$photo_path = (!empty($admin['photo']) && file_exists('../uploads/profiles/' . $admin['photo']))
    ? '../uploads/profiles/' . $admin['photo']
    : '../assets/images/default-avatar.jpg';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/header.css">
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/admin_profile.css">
</head>

<body>

    <div class="dashboard">

        <div class="main">
            <?php if (file_exists('../components/header.php')) include '../components/header.php'; ?>

            <div class="main-layer">
                <div class="profile-header">
                    <h1>Admin Profile</h1>
                    <p>Manage your profile photo, account details, and password.</p>
                </div>

                <div class="profile-container">
                    <div class="profile-grid">
                        <div class="profile-card">
                            <img src="<?= htmlspecialchars($photo_path) ?>" alt="Profile Photo" class="profile-photo">

                            <div class="admin-name"><?= htmlspecialchars($admin['fullname']) ?></div>
                            <div class="admin-role"><?= htmlspecialchars($admin['role']) ?></div>
                            <div class="admin-status"><?= htmlspecialchars($admin['status']) ?></div>

                            <form method="POST" enctype="multipart/form-data" class="upload-form" action="../backend/admin_profile.php">
                                <input type="hidden" name="action" value="upload_photo">

                                <label for="photo">Upload New Photo</label>
                                <input type="file" name="photo" id="photo" accept=".jpg,.jpeg,.png,.webp" required>

                                <div style="margin-top:14px;">
                                    <button type="submit" class="btn btn-primary" style="width:100%;">
                                        <i class="bi bi-camera"></i> Upload Photo
                                    </button>
                                </div>
                            </form>
                            <a href="index.php" class="back-btn">
                                <i class="bi bi-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>

                        <div class="forms-wrapper">
                            <div class="form-card">
                                <h2><i class="bi bi-pencil-square"></i> Edit Information</h2>

                                <form method="POST" id="editInfoForm" action="../backend/admin_profile.php">
                                    <input type="hidden" name="action" value="update_info">

                                    <div class="form-row">
                                        <div class="form-group full-width">
                                            <label>Full Name</label>
                                            <input
                                                type="text"
                                                name="fullname"
                                                id="fullname"
                                                value="<?= htmlspecialchars($admin['fullname']) ?>"
                                                data-original="<?= htmlspecialchars($admin['fullname'], ENT_QUOTES) ?>"
                                                readonly
                                                required>
                                        </div>

                                        <div class="form-group full-width">
                                            <label>Email</label>
                                            <input
                                                type="email"
                                                name="email"
                                                id="email"
                                                value="<?= htmlspecialchars($admin['email']) ?>"
                                                data-original="<?= htmlspecialchars($admin['email'], ENT_QUOTES) ?>"
                                                readonly
                                                required>
                                        </div>
                                    </div>

                                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                        <button type="button" id="editInfoBtn" class="btn btn-dark">
                                            <i class="bi bi-pencil"></i> Edit Info
                                        </button>

                                        <button type="submit" id="saveChangesBtn" class="btn btn-primary" style="display:none;">
                                            <i class="bi bi-check-circle"></i> Save Changes
                                        </button>

                                        <button type="button" id="cancelEditBtn" class="btn btn-secondary" style="display:none;">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="form-card">
                                <h2><i class="bi bi-shield-lock"></i> Change Password</h2>

                                <form method="POST" id="changePasswordForm" action="../backend/admin_profile.php">
                                    <input type="hidden" name="action" value="change_password">

                                    <div class="form-row">
                                        <div class="form-group full-width">
                                            <label>Current Password</label>
                                            <input type="password" name="current_password" required>
                                        </div>

                                        <div class="form-group">
                                            <label>New Password</label>
                                            <input type="password" name="new_password" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Confirm New Password</label>
                                            <input type="password" name="confirm_password" required>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-dark">
                                        <i class="bi bi-key"></i> Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/admin_profile.js"></script>
    <?php if (isset($_SESSION['notif'])): ?>
        <script>
            Swal.fire({
                title: '<?= $_SESSION['notif']['icon'] === "success" ? "Success!" : ($_SESSION['notif']['icon'] === "warning" ? "Warning!" : "Error!") ?>',
                text: '<?= htmlspecialchars($_SESSION['notif']['message'], ENT_QUOTES) ?>',
                icon: '<?= $_SESSION['notif']['icon'] ?>',
                confirmButtonColor: '#166534'
            });
        </script>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

</body>

</html>