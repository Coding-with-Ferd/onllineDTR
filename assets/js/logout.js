document.getElementById('logoutLink').addEventListener('click', function(e) {
            e.preventDefault(); // Prevent immediate navigation

            Swal.fire({
                title: 'Sign Out?',
                text: "Are you sure you want to end your session?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to the actual logout logic in PHP
                    window.location.href = "?action=logout";
                }
            });
        });

        // Sidebar toggle logic (keeping your existing functionality)
        const toggleBtn = document.getElementById('toggleBtn');
        const sidebar = document.getElementById('sidebar');
        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('close');
            });
        }