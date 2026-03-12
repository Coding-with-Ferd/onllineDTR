document.querySelectorAll('.delete-confirm').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.getAttribute('href');

        Swal.fire({
            title: 'Delete Employee?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});