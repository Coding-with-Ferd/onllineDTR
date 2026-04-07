<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<?php if (isset($_SESSION['notif'])): ?>
    <script>
        Swal.fire({
            title: '<?=
                $_SESSION['notif']['icon'] === "success" ? "Success!" :
                ($_SESSION['notif']['icon'] === "warning" ? "Warning!" : "Error!")
            ?>',
            html: <?= json_encode($_SESSION['notif']['message']) ?>,
            icon: '<?= $_SESSION['notif']['icon'] ?>',
            confirmButtonColor: '#1a6d18',
            timer: 5000,
            timerProgressBar: true
        });
    </script>
    <?php unset($_SESSION['notif']); ?>
<?php endif; ?>