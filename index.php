<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$destination = '/auth/signin.php';
if (!empty($_SERVER['QUERY_STRING'])) {
    $destination .= '?' . $_SERVER['QUERY_STRING'];
}

header('Location: ' . $destination, true, 302);
exit;
