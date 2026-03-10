<?php
ob_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../Includes/PHPMailer/src/Exception.php';
require '../Includes/PHPMailer/src/PHPMailer.php';
require '../Includes/PHPMailer/src/SMTP.php';

session_start();
include '../auth/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'status' => 'error', 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // Initial Login
    if ($action === 'login') {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $response['message'] = "Please fill in all fields.";
        } else {
            $stmt = $conn->prepare("SELECT UserID, FullName, PasswordHash, Status FROM user WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['PasswordHash'])) {
                    if ($user['Status'] == 'Active') {
                        $otp = rand(100000, 999999);
                        
                        $mail = new PHPMailer(true);
                        try {
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
                                'expires' => time() + 60
                            ];

                            $response['success'] = true;
                            $response['status'] = 'success'; 
                            $response['message'] = "OTP Sent";
                        } catch (Exception $e) {
                            $response['message'] = "Email failed: " . $mail->ErrorInfo;
                        }
                    } else { $response['message'] = "Account inactive."; }
                } else { $response['message'] = "Invalid credentials."; }
            } else { $response['message'] = "User not found."; }
            $stmt->close();
        }
    }

    // Verify OTP
    elseif ($action === 'verify_otp') {
        if (!isset($_SESSION['otp_data'])) {
            $response['message'] = "Session expired. Try login again.";
        } else {
            $data = $_SESSION['otp_data'];
            $entered_otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

            if (time() > $data['expires']) {
                $response['message'] = "OTP Expired.";
                unset($_SESSION['otp_data']);
            } elseif ((int)$entered_otp === (int)$data['otp']) {
                $_SESSION['user_id'] = $data['user_id'];
                $_SESSION['user_name'] = $data['user_name'];
                unset($_SESSION['otp_data']);
                
                $response['success'] = true;
                $response['status'] = 'success';
                $response['redirect'] = "../pages/index.php";
            } else {
                $response['message'] = "Incorrect OTP.";
            }
        }
    }
}

ob_clean();
echo json_encode($response);
exit();