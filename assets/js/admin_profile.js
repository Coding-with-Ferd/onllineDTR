document.addEventListener('DOMContentLoaded', function () {
    const editBtn = document.getElementById('editInfoBtn');
    const saveBtn = document.getElementById('saveChangesBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');

    const fullnameInput = document.getElementById('fullname');
    const emailInput = document.getElementById('email');

    const inputs = [fullnameInput, emailInput].filter(Boolean);

    function enableEditing() {
        inputs.forEach(input => input.removeAttribute('readonly'));
        if (editBtn) editBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'inline-block';
        checkForChanges();
    }

    function disableEditing() {
        inputs.forEach(input => {
            input.setAttribute('readonly', true);
            input.value = input.dataset.original;
        });

        if (editBtn) editBtn.style.display = 'inline-block';
        if (saveBtn) saveBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'none';
    }

    function checkForChanges() {
        const hasChanges = inputs.some(input => input.value !== input.dataset.original);

        if (fullnameInput && !fullnameInput.hasAttribute('readonly') && hasChanges) {
            if (saveBtn) saveBtn.style.display = 'inline-block';
        } else {
            if (saveBtn) saveBtn.style.display = 'none';
        }
    }

    if (editBtn && cancelBtn && inputs.length) {
        editBtn.addEventListener('click', enableEditing);
        cancelBtn.addEventListener('click', disableEditing);
        inputs.forEach(input => input.addEventListener('input', checkForChanges));
    }

    const changePasswordForm = document.getElementById('changePasswordForm');

    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Change password?',
                text: 'Are you sure you want to change your password?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#166534',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    changePasswordForm.submit();
                }
            });
        });
    }
});