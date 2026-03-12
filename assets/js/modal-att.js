function toggleModal(show) {
    const modal = document.getElementById('attendanceModal');
    modal.style.display = show ? 'flex' : 'none';
}

// Close modal if user clicks outside of the box
window.onclick = function(event) {
    const modal = document.getElementById('attendanceModal');
    if (event.target == modal) {
        toggleModal(false);
    }
}

// This prevents double-clicking and provides a basic UI block
document.querySelector('form[action*="attendance.php"]').addEventListener('submit', function(e) {
    const btn = e.submitter;
    if (btn) {
        btn.innerHTML = "Processing...";
        btn.style.opacity = "0.5";
        btn.style.pointerEvents = "none";
    }
});

function updateAttendanceUI() {
    const status = document.getElementById('statusSelect').value;
    const timeContainer = document.getElementById('timeInContainer');
    const saveStatusBtn = document.getElementById('btnSaveStatus');

    if (status === 'Present') {
        // Show Time buttons, Hide Save Status
        timeContainer.style.display = 'flex';
        saveStatusBtn.style.display = 'none';
    } else if (status === 'SNW Holiday' || status === 'Holiday') {
        // Hide Time buttons, Show Save Status
        timeContainer.style.display = 'flex';
        saveStatusBtn.style.display = 'block';
    } else {
        // Hide Time buttons, Show Save Status
        timeContainer.style.display = 'none';
        saveStatusBtn.style.display = 'block';
    }
}

// Optional: Ensure correct state if the modal is closed and reopened
function toggleModal(show) {
    const modal = document.getElementById('attendanceModal');
    modal.style.display = show ? 'flex' : 'none';
    if(show) {
        // Reset to "Present" when opening modal
        document.getElementById('statusSelect').value = 'Present';
        updateAttendanceUI();
    }
}