<!DOCTYPE html>
<html lang="en">

<head>
    <title>PrimeHealth Login</title>
    <link rel="stylesheet" href="../auth/auth-style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="login-scene">
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
            <div id="login-section">
                <form class="login-form" id="login-form">
                    <p class="error" id="login-error" style="color:red; display:none;"></p>
                    <div class="input-group">
                        <input type="email" name="email" placeholder="Email" required>
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" placeholder="Password" required>
                        <i class="bi bi-lock"></i>
                    </div>
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox"> Remember Me
                        </label>
                        <a href="#" class="forgot-pw">Forgot Password?</a>
                    </div>
                    <button type="submit">Login</button>
                    <p class="center-text">Don't have an account? <a href="signup.php">Sign up</a></p>
                </form>
            </div>

            <div id="otp-section" style="display:none;">
                <form class="otp-form" id="otp-form">
                    <h2><i class="bi bi-key"></i> Enter OTP</h2>
                    <p class="error" id="otp-error" style="color:red; display:none;"></p>
                    <div class="input-group">
                        <input type="text" name="otp" placeholder="Enter OTP" required maxlength="6">
                        <i class="bi bi-key"></i>
                    </div>
                    <button type="submit">Verify OTP</button>

                    <div class="timer-container">
                        <div id="timer-status">
                            <span id="countdown">60</span>
                            <p>seconds remaining</p>
                        </div>

                        <div id="resend-container" style="display:none; margin-top: 15px;">
                            <p>Didn't receive the code?</p>
                            <a href="javascript:void(0)" onclick="resendOTP()" style="color:#2E86C1; text-decoration:underline; font-weight:bold;">Resend OTP</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer class="auth-footer">
        <img src="../assets/images/footer-logo.png" alt="PhilHealth and Bagong Pilipinas Logos">
    </footer>
    <script src="../assets/js/auth.js"></script>
</body>

</html>