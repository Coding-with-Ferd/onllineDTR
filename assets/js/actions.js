function confirmAction(selector, title, text, icon, confirmText, confirmColor) {
    document.querySelectorAll(selector).forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const url = this.getAttribute('href');

            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
}

confirmAction(
    '.approve',
    'Approve Request?',
    'Are you sure you want to approve this leave request?',
    'question',
    'Yes, Approve',
    '#28a745'
);

confirmAction(
    '.reject',
    'Reject Request?',
    'Are you sure you want to reject this leave request?',
    'warning',
    'Yes, Reject',
    '#ffc107'
);

confirmAction(
    '.delete',
    'Delete Request?',
    'This action cannot be undone.',
    'error',
    'Yes, Delete',
    '#d33'
);