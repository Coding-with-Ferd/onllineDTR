<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // hide warnings/notices from breaking JSON
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../Includes/PHPMailer/src/Exception.php';
require '../Includes/PHPMailer/src/PHPMailer.php';
require '../Includes/PHPMailer/src/SMTP.php';

session_start();
include '../auth/db_connect.php';
include '../config/session.php';

$response = ['success' => false, 'status' => 'error', 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    // ------------------- LOGIN -------------------
    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $response['message'] = "Please fill in all fields.";
        } else {
            $stmt = $conn->prepare("SELECT UserID, FullName, PasswordHash, Status FROM user WHERE Email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user) {
                if (password_verify($password, $user['PasswordHash'])) {
                    if ($user['Status'] === 'Active') {
                        $otp = rand(100000, 999999);
                        $remember = isset($_POST['remember']) ? true : false;

                        try {
                            $mail = new PHPMailer(true);
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'nakedbeautyaesthetic@gmail.com';
                            $mail->Password = 'oatm xkfq zaky yqcv';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;
                            $mail->setFrom('noreply@primehealth.com', 'PrimeHealth Clinic');
                            $mail->addAddress($email);
                            $mail->isHTML(true);
                            $mail->Subject = "OTP for Login";
                            $mail->Body = "Your OTP is: <b>$otp</b>. Expires in 1 min.";
                            $mail->send();

                            $_SESSION['otp_data'] = [
                                'otp' => $otp,
                                'user_id' => $user['UserID'],
                                'user_name' => $user['FullName'],
                                'expires' => time() + 60,
                                'remember' => $remember
                            ];

                            $response = [
                                'success' => true,
                                'status' => 'success',
                                'message' => 'OTP Sent'
                            ];
                        } catch (Exception $e) {
                            $response['message'] = "Email failed: " . $mail->ErrorInfo;
                        }
                    } else {
                        $response['message'] = "Account inactive.";
                    }
                } else {
                    $response['message'] = "Invalid credentials.";
                }
            } else {
                $response['message'] = "User not found.";
            }
        }
    }

    // ------------------- VERIFY OTP -------------------
    elseif ($action === 'verify_otp') {
        if (!isset($_SESSION['otp_data'])) {
            $response['message'] = "Session expired. Try login again.";
        } else {
            $data = $_SESSION['otp_data'];
            $entered_otp = trim($_POST['otp'] ?? '');

            if (time() > $data['expires']) {
                unset($_SESSION['otp_data']);
                $response['message'] = "OTP expired.";
            } elseif ((int)$entered_otp === (int)$data['otp']) {

                // Fetch user email & role
                $stmt = $conn->prepare("SELECT Email, Role FROM user WHERE UserID=?");
                $stmt->bind_param("i", $data['user_id']);
                $stmt->execute();
                $userInfo = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                // Set session
                setUserSession([
                    'UserID' => $data['user_id'],
                    'FullName' => $data['user_name'],
                    'Email' => $userInfo['Email'],
                    'Role' => $userInfo['Role']
                ], $data['remember'], true);

                unset($_SESSION['otp_data']);

                $response = [
                    'success' => true,
                    'status' => 'success',
                    'redirect' => "../pages/index.php"
                ];
            } else {
                $response['message'] = "Incorrect OTP.";
            }
        }
    }

    // ------------------- FORGOT PASSWORD -------------------
    elseif ($action === 'forgot_password') {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            $response['message'] = "Enter your email.";
        } else {
            $stmt = $conn->prepare("SELECT UserID FROM user WHERE Email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $userExists = $stmt->get_result()->num_rows === 1;
            $stmt->close();

            if ($userExists) {
                $otp = rand(100000, 999999);
                $_SESSION['reset_otp'] = ['email' => $email, 'otp' => $otp, 'expires' => time() + 300];

                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'nakedbeautyaesthetic@gmail.com';
                    $mail->Password = 'oatm xkfq zaky yqcv';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('noreply@primehealth.com', 'PrimeHealth Clinic');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = "Password Reset OTP";
                    $mail->Body = "Your password reset OTP is <b>$otp</b>. Valid for 5 minutes.";
                    $mail->send();

                    $response = ['success' => true, 'status' => 'success', 'message' => 'OTP sent to email.'];
                } catch (Exception $e) {
                    $response['message'] = "Email failed.";
                }
            } else {
                $response['message'] = "Email not found.";
            }
        }
    }

    // ------------------- RESET PASSWORD -------------------
    elseif ($action === 'reset_password') {
        $otp = trim($_POST['otp'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!isset($_SESSION['reset_otp'])) {
            $response['message'] = "Session expired.";
        } else {
            $data = $_SESSION['reset_otp'];
            if (time() > $data['expires']) {
                unset($_SESSION['reset_otp']);
                $response['message'] = "OTP expired.";
            } elseif ((int)$otp === (int)$data['otp']) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE user SET PasswordHash=? WHERE Email=?");
                $stmt->bind_param("ss", $hash, $data['email']);
                $stmt->execute();
                $stmt->close();
                unset($_SESSION['reset_otp']);

                $response = ['success' => true, 'status' => 'success', 'message' => 'Password updated.'];
            } else {
                $response['message'] = "Invalid OTP.";
            }
        }
    }
}

// End of POST
ob_clean();
echo json_encode($response);
exit();
