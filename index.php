<?php
// Ensure session is available for any pages that expect it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect root to login page
$destination = '/auth/signin.php';
// Preserve query string if present
if (!empty($_SERVER['QUERY_STRING'])) {
    $destination .= '?' . $_SERVER['QUERY_STRING'];
}

header('Location: ' . $destination, true, 302);
exit;
