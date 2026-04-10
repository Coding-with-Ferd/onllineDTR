let timer;

/* SHOW FORMS */

function showForgot(){
    document.getElementById('login-section').style.display='none';
    document.getElementById('forgot-section').style.display='block';
}

function showLogin(){
    document.getElementById('forgot-section').style.display='none';
    document.getElementById('reset-section').style.display='none';
    document.getElementById('login-section').style.display='block';
}

/* LOGIN FORM */

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
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                document.getElementById('login-section').style.display = 'none';
                document.getElementById('otp-section').style.display = 'block';
                startTimer(60);
            }
        } else {

            const err = document.getElementById('login-error');
            err.innerText = data.message;
            err.style.display = 'block';

        }

    })

    .catch(error => console.error('Error:', error));

});


/* OTP VERIFICATION */

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


/* TIMER */

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


/* RESEND OTP */

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


/* FORGOT PASSWORD */

document.getElementById('forgot-form').addEventListener('submit', function(e){

    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action','forgot_password');

    fetch('../backend/auth_handler.php',{

        method:'POST',
        body:formData

    })

    .then(res=>res.json())

    .then(data=>{

        if(data.success){

            document.getElementById('forgot-section').style.display='none';
            document.getElementById('reset-section').style.display='block';

        }

        else{

            const err = document.getElementById('forgot-error');
            err.innerText=data.message;
            err.style.display='block';

        }

    });

});


/* RESET PASSWORD */

document.getElementById('reset-form').addEventListener('submit',function(e){

    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action','reset_password');

    fetch('../backend/auth_handler.php',{

        method:'POST',
        body:formData

    })

    .then(res=>res.json())

    .then(data=>{

        if(data.success){

            alert("Password updated. Please login.");

            showLogin();

        }

        else{

            const err = document.getElementById('reset-error');
            err.innerText=data.message;
            err.style.display='block';

        }

    });

});