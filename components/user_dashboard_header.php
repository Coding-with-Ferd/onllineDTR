<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userName = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Guest';
$userPhoto = isset($_SESSION['user_photo']) ? $_SESSION['user_photo'] : '';
$userInitial = strtoupper(substr(trim($userName), 0, 1));
?>

<header class="user-dashboard-header">
    <div class="user-dashboard-header__brand">
        <div class="user-dashboard-avatar" aria-label="User avatar">
            <?php if ($userPhoto): ?>
                <img src="<?php echo htmlspecialchars($userPhoto); ?>" alt="<?php echo $userName; ?>" loading="lazy">
            <?php else: ?>
                <span><?php echo $userInitial ?: 'U'; ?></span>
            <?php endif; ?>
        </div>
        <div class="brand-title-wrapper">
            <div class="brand-title"><?php echo $userName; ?></div>
            <div class="brand-subtitle">My Daily Dashboard</div>
        </div>
    </div>

    <button class="user-dashboard-header__toggle" id="userDashboardHeaderToggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <nav class="user-dashboard-header__nav" id="userDashboardHeaderNav">
        <a href="../userpages/user_dashboard.php">Dashboard</a>
        <a href="../userpages/user_profile.php">Profile</a>
        <a href="../userpages/user_appointment.php">Appointments</a>
        <a href="#" id="userDashboardLogoutLink">Logout</a>
    </nav>
</header>

<script>
    function updateUserDashboardDateTime() {
        const now = new Date();
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        const dateTimeStr = now.toLocaleString('en-US', options);
        const dtElement = document.getElementById('userDashboardDateTime');
        if (dtElement) dtElement.textContent = dateTimeStr;
    }

    updateUserDashboardDateTime();
    setInterval(updateUserDashboardDateTime, 1000);

    const headerToggle = document.getElementById('userDashboardHeaderToggle');
    const headerNav = document.getElementById('userDashboardHeaderNav');

    if (headerToggle && headerNav) {
        headerToggle.addEventListener('click', function () {
            headerNav.classList.toggle('active');
        });
    }

    const userDashboardLogoutLink = document.getElementById('userDashboardLogoutLink');
    if (userDashboardLogoutLink) {
        userDashboardLogoutLink.addEventListener('click', function (event) {
            event.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Sign Out?',
                    text: 'Are you sure you want to end your session?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Logout',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '?action=logout';
                    }
                });
            } else {
                if (confirm('Sign Out? Are you sure?')) {
                    window.location.href = '?action=logout';
                }
            }
        });
    }
</script>
