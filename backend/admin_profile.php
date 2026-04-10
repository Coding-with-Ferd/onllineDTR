<?php
require_once '../config/session.php';
include '../auth/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/signin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/admin_profile.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$table_name = "user";

function setNotif($icon, $message)
{
    $_SESSION['notif'] = [
        'icon' => $icon,
        'message' => $message
    ];
}

$action = $_POST['action'] ?? '';

/* UPDATE INFO */
if ($action === 'update_info') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if ($fullname === '' || $email === '') {
        setNotif('warning', 'Full name and email are required.');
        header('Location: ../pages/admin_profile.php');
        exit;
    }

    $stmt = $conn->prepare("UPDATE {$table_name} SET fullname = ?, email = ?, updatedat = NOW() WHERE userid = ?");
    $stmt->bind_param("ssi", $fullname, $email, $user_id);

    if ($stmt->execute()) {
        $_SESSION['user_name'] = $fullname;
        setNotif('success', 'Profile updated successfully.');
    } else {
        setNotif('error', 'Failed to update profile.');
    }

    header('Location: ../pages/admin_profile.php');
    exit;
}

/* CHANGE PASSWORD */
if ($action === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        setNotif('warning', 'Please fill in all password fields.');
        header('Location: ../pages/admin_profile.php');
        exit;
    }

    if ($new_password !== $confirm_password) {
        setNotif('warning', 'New password and confirm password do not match.');
        header('Location: ../pages/admin_profile.php');
        exit;
    }

    if (strlen($new_password) < 8) {
        setNotif('warning', 'New password must be at least 8 characters.');
        header('Location: ../pages/admin_profile.php');
        exit;
    }

    $stmt = $conn->prepare("SELECT passwordhash FROM {$table_name} WHERE userid = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        setNotif('error', 'User not found.');
        header('Location: ../pages/admin_profile.php');
        exit;
    }

    if (!password_verify($current_password, $user['passwordhash'])) {
        setNotif('error', 'Current password is incorrect.');
        header('Location: ../pages/admin_profile.php');
        exit;
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE {$table_name} SET passwordhash = ?, updatedat = NOW() WHERE userid = ?");
    $stmt->bind_param("si", $new_hash, $user_id);

    if ($stmt->execute()) {
        setNotif('success', 'Password changed successfully.');
    } else {
        setNotif('error', 'Failed to change password.');
    }

    header('Location: ../pages/admin_profile.php');
    exit;
}

/* UPLOAD PHOTO */
if ($action === 'upload_photo') {
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        setNotif('warning', 'Please select a photo to upload.');
        header('Location: ../pages/admin_profile.php');
        exit;
    }

    $file = $_FILES['photo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        setNotif('warning', 'Only JPG, JPEG, PNG, and WEBP files are allowed.');
        header('Location: ../pages/admin_profile.php');
        exit;
    }

    if ($file['size'] > $max_size) {
        setNotif('warning', 'Photo must not exceed 2MB.');
        header('Location: ../pages/admin_profile.php');
        exit;
    }

    $upload_dir = '../uploads/profiles/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'admin_' . $user_id . '_' . time() . '.' . strtolower($ext);
    $target_path = $upload_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        setNotif('error', 'Failed to upload photo.');
        header('Location: ../pages/admin_profile.php');
        exit;
    }

    $old_stmt = $conn->prepare("SELECT photo FROM {$table_name} WHERE userid = ?");
    $old_stmt->bind_param("i", $user_id);
    $old_stmt->execute();
    $old_result = $old_stmt->get_result();
    $old_user = $old_result->fetch_assoc();

    $stmt = $conn->prepare("UPDATE {$table_name} SET photo = ?, updatedat = NOW() WHERE userid = ?");
    $stmt->bind_param("si", $new_filename, $user_id);

    if ($stmt->execute()) {
        if (!empty($old_user['photo'])) {
            $old_path = $upload_dir . $old_user['photo'];
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }
        setNotif('success', 'Profile photo updated successfully.');
    } else {
        setNotif('error', 'Photo uploaded but failed to save in database.');
    }

    header('Location: ../pages/admin_profile.php');
    exit;
}

/* INVALID ACTION */
setNotif('error', 'Invalid request.');
header('Location: ../pages/admin_profile.php');
exit;