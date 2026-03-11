<?php
// Secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.cookie_lifetime', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../auth/db_connect.php';

// SESSION FUNCTIONS
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin';
}
function isEmployee()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Employee';
}
function getCurrentUser()
{
    return isLoggedIn() ? ['id' => $_SESSION['user_id'], 'name' => $_SESSION['user_name'], 'email' => $_SESSION['user_email'], 'role' => $_SESSION['user_role']] : null;
}

// Set session
function setUserSession($userData, $remember = false, $skipOTP = false)
{
    $_SESSION['user_id'] = $userData['UserID'];
    $_SESSION['user_name'] = $userData['FullName'];
    $_SESSION['user_email'] = $userData['Email'];
    $_SESSION['user_role'] = $userData['Role'];
    $_SESSION['logged_in_time'] = time();

    if ($remember) {
        setcookie("remember_user", $userData['UserID'], time() + (86400 * 15), "/", "", false, true);
        if ($skipOTP) {
            $_SESSION['skip_otp'] = true;
        }
    }
}

// Destroy
function destroySession()
{
    session_unset();
    session_destroy();

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    if (isset($_COOKIE['remember_user'])) {
        setcookie('remember_user', '', time() - 3600, '/');
    }
}

// Force role
function requireRole($roles)
{
    if (!isLoggedIn()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    if (!in_array($_SESSION['user_role'], $roles)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}

// Regenerate ID every 30 min
if (isset($_SESSION['logged_in_time']) && time() - $_SESSION['logged_in_time'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['logged_in_time'] = time();
}
