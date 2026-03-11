document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("addEmployeeModal");
    const addBtn = document.querySelector(".add-btn");
    const closeBtn = modal.querySelector(".close");
    const cancelBtn = modal.querySelector(".close-btn");

    flatpickr("#modernDatePicker", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        defaultDate: "today",
        disableMobile: "true",
        static: true, 
        onReady: function(selectedDates, dateStr, instance) {
            const calendar = instance.calendarContainer;
            calendar.style.borderRadius = "12px";
            calendar.style.boxShadow = "0 10px 25px rgba(0,0,0,0.1)";
            calendar.style.zIndex = "9999"; 
        }
    });

    // Modal Logic
    const closeModal = () => {
        modal.classList.remove("show");
        setTimeout(() => {
            modal.style.display = "none";
        }, 300);
    };

    // Open modal
    addBtn.addEventListener("click", function(e){
        e.preventDefault(); 
        modal.style.display = "block";
        setTimeout(() => {
            modal.classList.add("show");
        }, 10);
    });

    // Close modal events
    closeBtn.addEventListener("click", closeModal);
    cancelBtn.addEventListener("click", closeModal);

    // Close when clicking outside modal
    window.addEventListener("click", (e) => {
        if(e.target === modal) closeModal();
    });
});