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
                    <linearGradient id="wave-gradient-top" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="10%" style="stop-color:#000000; stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#35f15c; stop-opacity:1" />
                    </linearGradient>
                </defs>
                <path fill="url(#wave-gradient-top)" fill-opacity="1" d="M0,192L60,165.3C120,139,240,85,360,90.7C480,96,600,160,720,197.3C840,235,960,245,1080,224C1200,203,1320,149,1380,122.7L1440,96L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path>
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
                        </label>
                        <a href="#" class="forgot-pw" onclick="showForgot()">Forgot Password?</a>
                    </div>
                    <button type="submit">Login</button>
                    <p class="center-text">Don't have an account? <a href="signup.php">Sign up</a></p>
                </form>
            </div>

            <div id="forgot-section" style="display:none;">
                <form class="login-form" id="forgot-form">
                    <h2><i class="bi bi-envelope"></i> Forgot Password</h2>

                    <p class="error" id="forgot-error" style="color:red; display:none;"></p>

                    <div class="input-group">
                        <input type="email" name="email" placeholder="Enter your registered email" required>
                        <i class="bi bi-envelope"></i>
                    </div>

                    <button type="submit">Send OTP</button>

                    <p class="center-text">
                        Remembered your password?
                        <a href="#" onclick="showLogin()">Back to Login</a>
                    </p>
                </form>
            </div>

            <div id="reset-section" style="display:none;">
                <form class="login-form" id="reset-form">

                    <h2><i class="bi bi-shield-lock"></i> Reset Password</h2>

                    <p class="error" id="reset-error" style="color:red; display:none;"></p>

                    <div class="input-group">
                        <input type="text" name="otp" placeholder="Enter OTP" required>
                        <i class="bi bi-key"></i>
                    </div>

                    <div class="input-group">
                        <input type="password" name="password" placeholder="New Password" required>
                        <i class="bi bi-lock"></i>
                    </div>

                    <button type="submit">Reset Password</button>

                    <p class="center-text">
                        <a href="#" onclick="showLogin()">Back to Login</a>
                    </p>

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