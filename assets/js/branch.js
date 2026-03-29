function openAddBranchModal() {
    document.getElementById('addBranchModal').style.display = 'flex';
}

function closeAddBranchModal() {
    document.getElementById('addBranchModal').style.display = 'none';
}

function openEditBranchModal(id, name, address, openTime, closeTime, isOpen) {
    document.getElementById('edit_branch_id').value = id;
    document.getElementById('edit_branch_name').value = name;
    document.getElementById('edit_branch_address').value = address;
    document.getElementById('edit_open_time').value = openTime;
    document.getElementById('edit_close_time').value = closeTime;
    document.getElementById('edit_is_open').value = isOpen;
    document.getElementById('editBranchModal').style.display = 'flex';
}

function closeEditBranchModal() {
    document.getElementById('editBranchModal').style.display = 'none';
}

window.addEventListener('click', function(e) {
    const addModal = document.getElementById('addBranchModal');
    const editModal = document.getElementById('editBranchModal');

    if (e.target === addModal) closeAddBranchModal();
    if (e.target === editModal) closeEditBranchModal();
});
