<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../Includes/PHPMailer/src/Exception.php';
require '../Includes/PHPMailer/src/PHPMailer.php';
require '../Includes/PHPMailer/src/SMTP.php';

session_start();
include 'db_connect.php';

// SMTP Configuration
$smtp_host = 'smtp.gmail.com';
$smtp_username = 'nakedbeautyaesthetic@gmail.com';
$smtp_password = 'oatm xkfq zaky yqcv';
$smtp_port = 587;

$error = "";
$success = "";

if (isset($_GET['resend']) && isset($_SESSION['signup_data'])) {
    $data = $_SESSION['signup_data'];
    $subject = "Email Confirmation for PrimeHealth Clinic";
    $message = "Your confirmation code is: " . $data['confirm_code'] . "\n\nPlease enter this code to complete your registration.";

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp_port;

        // Recipients
        $mail->setFrom('noreply@primehealth.com', 'PrimeHealth Clinic');
        $mail->addAddress($data['email']);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        $success = "Confirmation code resent to your email.";
    } catch (Exception $e) {
        $error = "Failed to resend confirmation email. Mailer Error: {$mail->ErrorInfo}";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['signup'])) {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "Please fill in all fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT UserID FROM user WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email already registered.";
            } else {
                // Generate confirmation code
                $confirm_code = rand(100000, 999999);

                // Send email
                $subject = "Email Confirmation for PrimeHealth Clinic";
                $message = "Your confirmation code is: $confirm_code\n\nPlease enter this code to complete your registration.";

                $email_sent = false;
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = $smtp_host;
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp_username;
                    $mail->Password = $smtp_password;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = $smtp_port;

                    // Recipients
                    $mail->setFrom('noreply@primehealth.com', 'PrimeHealth Clinic');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(false);
                    $mail->Subject = $subject;
                    $mail->Body = $message;

                    $mail->send();
                    $success = "Confirmation code sent to your email. Please check and enter the code below.";
                    $email_sent = true;
                } catch (Exception $e) {
                    $error = "Failed to send confirmation email. Mailer Error: {$mail->ErrorInfo}";
                }

                if ($email_sent) {
                    // Store data in session
                    $_SESSION['signup_data'] = [
                        'fullname' => $fullname,
                        'email' => $email,
                        'password' => $password,
                        'confirm_code' => $confirm_code
                    ];
                }
            }
            $stmt->close();
        }
    } elseif (isset($_POST['confirm'])) {
        $entered_code = trim($_POST['code']);

        if (isset($_SESSION['signup_data'])) {
            $data = $_SESSION['signup_data'];
            if ($entered_code == $data['confirm_code']) {
                // Create account
                $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO user (FullName, Email, PasswordHash) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $data['fullname'], $data['email'], $hashed_password);

                if ($stmt->execute()) {
                    unset($_SESSION['signup_data']);
                    $success = "Account created successfully. You can now log in.";
                } else {
                    $error = "Error creating account. Please try again.";
                }
                $stmt->close();
            } else {
                $error = "Invalid confirmation code.";
            }
        } else {
            $error = "Session expired. Please start over.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>PrimeHealth Clinic</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon-32x32.png">
    <link rel="stylesheet" href="../auth/auth-style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="login-scene">
        <div class="top">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 50 1440 320">
            <defs>
                <linearGradient id="wave-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="10%" style="stop-color:#000000; stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#35f15c; stop-opacity:1" />
                </linearGradient>
            </defs>
            <path fill="url(#wave-gradient)" fill-opacity="1" d="M0,192L60,165.3C120,139,240,85,360,90.7C480,96,600,160,720,197.3C840,235,960,245,1080,224C1200,203,1320,149,1380,122.7L1440,96L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path>
        </svg>
        </div>
        <div class="left-panel">
            <div class="image-collage"></div>
            <div class="clinic-text">
                <h1>PRIMEHEALTH CLINIC</h1>
                <p>Service Beyond Health</p>
            </div>
        </div>

        <div class="login-panel">
            <div class="yakap-text">
                <h1>PhilHealth</h1>
                <h2>YAKAP</h2>
            </div>
            <?php if (isset($_SESSION['signup_data'])): ?>
                <form class="login-form" method="post" action="">
                    <h2><i class="bi bi-envelope-check"></i> Confirm Email</h2>
                    <?php if ($error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <p class="success"><?php echo htmlspecialchars($success); ?></p>
                    <?php endif; ?>
                    <p>Please enter the 6-digit confirmation code.</p>
                    <div class="input-group">
                        <input type="text" name="code" placeholder="Confirmation Code" required maxlength="6">
                        <i class="bi bi-key"></i>
                    </div>
                    <button type="submit" name="confirm"><i class="bi bi-check-circle"></i> Confirm</button>
                    <p class="center-text"><a href="signup.php?resend=1">Resend Code</a></p>
                </form>
            <?php else: ?>
                <form class="login-form" method="post" action="">
                    <?php if ($error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <p class="success"><?php echo htmlspecialchars($success); ?></p>
                    <?php endif; ?>
                    <div class="input-group">
                        <input type="text" name="fullname" placeholder="Full Name" required>
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="input-group">
                        <input type="email" name="email" placeholder="Email" required>
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" placeholder="Password" required>
                        <i class="bi bi-lock"></i>
                    </div>
                    <div class="input-group">
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <button type="submit" name="signup"><i class="bi bi-person-plus"></i> Sign Up</button>
                    <p class="center-text">Already have an account? <a href="signin.php">Sign in</a></p>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <footer class="auth-footer">
        <img src="../assets/images/footer-logo.png" alt="PhilHealth and Bagong Pilipinas Logos">
    </footer>
</body>

</html>