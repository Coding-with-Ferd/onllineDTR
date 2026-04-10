function toggleModal(show) {
    const modal = document.getElementById('attendanceModal');
    modal.style.display = show ? 'flex' : 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('attendanceModal');
    if (event.target == modal) {
        toggleModal(false);
    }
}

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
        timeContainer.style.display = 'flex';
        saveStatusBtn.style.display = 'none';
    } else if (status === 'SNW Holiday' || status === 'Holiday') {
        timeContainer.style.display = 'flex';
        saveStatusBtn.style.display = 'block';
    } else {
        timeContainer.style.display = 'none';
        saveStatusBtn.style.display = 'block';
    }
}

function toggleModal(show) {
    const modal = document.getElementById('attendanceModal');
    modal.style.display = show ? 'flex' : 'none';
    if(show) {
        document.getElementById('statusSelect').value = 'Present';
        updateAttendanceUI();
    }
}

function openPrintPreview() {
    const url = "../components/print_attendance.php?id=<?= $id ?>&start_date=<?= urlencode($_GET['start_date'] ?? '') ?>&end_date=<?= urlencode($_GET['end_date'] ?? '') ?>";

    window.location.href = url;
}