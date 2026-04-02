document.addEventListener('input', function (e) {
    if (e.target.classList.contains('print-hours-input')) {
        const inputs = document.querySelectorAll('.print-hours-input');
        let total = 0;

        inputs.forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value)) {
                total += value;
            }
        });

        const totalOtCell = document.getElementById('totalOtHours');
        if (totalOtCell) {
            totalOtCell.textContent = total.toFixed(2);
        }
    }
});