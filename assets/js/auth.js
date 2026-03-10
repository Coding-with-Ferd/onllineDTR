let timer;

// HANDLE LOGIN FORM 
document.getElementById('login-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'login');

    fetch('../backend/auth_handler.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('login-section').style.display = 'none';
                document.getElementById('otp-section').style.display = 'block';
                startTimer(60);
            } else {
                const err = document.getElementById('login-error');
                err.innerText = data.message;
                err.style.display = 'block';
            }
        })
        .catch(error => console.error('Error:', error));
});

// HANDLE OTP FORM 
document.getElementById('otp-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'verify_otp');

    fetch('../backend/auth_handler.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                const err = document.getElementById('otp-error');
                err.innerText = data.message;
                err.style.display = 'block';
            }
        })
        .catch(error => console.error('Error:', error));
});

function startTimer(seconds) {
    let timeLeft = seconds;

    document.getElementById('timer-status').style.display = 'block';
    document.getElementById('resend-container').style.display = 'none';
    document.getElementById('countdown').innerText = timeLeft;

    clearInterval(timer);
    timer = setInterval(() => {
        timeLeft--;
        document.getElementById('countdown').innerText = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(timer);
            document.getElementById('timer-status').style.display = 'none';
            document.getElementById('resend-container').style.display = 'block';
        }
    }, 1000);
}

function resendOTP() {
    const loginForm = document.getElementById('login-form');
    const formData = new FormData(loginForm);
    formData.append('action', 'login');

    document.getElementById('otp-error').style.display = 'none';

    fetch('../backend/auth_handler.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                startTimer(60);
            } else {
                const err = document.getElementById('otp-error');
                err.innerText = "Error resending: " + data.message;
                err.style.display = 'block';
            }
        })
        .catch(error => console.error('Error:', error));
}