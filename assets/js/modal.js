document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("addEmployeeModal");
    const addBtn = document.querySelector(".add-btn");
    const closeBtn = modal.querySelector(".close");
    const cancelBtn = modal.querySelector(".close-btn");

    // Open modal
    addBtn.addEventListener("click", function(e){
        e.preventDefault(); // prevent navigating
        modal.style.display = "block";
    });

    // Close modal
    closeBtn.addEventListener("click", () => modal.style.display = "none");
    cancelBtn.addEventListener("click", () => modal.style.display = "none");

    // Close when clicking outside modal
    window.addEventListener("click", (e) => {
        if(e.target == modal) modal.style.display = "none";
    });
});

const modal = document.getElementById("addEmployeeModal");
const addBtn = document.querySelector(".add-btn");
const closeBtn = modal.querySelector(".close");
const cancelBtn = modal.querySelector(".close-btn");

addBtn.addEventListener("click", (e) => {
    e.preventDefault();
    modal.style.display = "block";
    modal.classList.add("show"); // add animation
});

closeBtn.addEventListener("click", () => {
    modal.classList.remove("show");
    setTimeout(() => modal.style.display = "none", 300);
});

cancelBtn.addEventListener("click", () => {
    modal.classList.remove("show");
    setTimeout(() => modal.style.display = "none", 300);
});

window.addEventListener("click", (e) => {
    if(e.target == modal){
        modal.classList.remove("show");
        setTimeout(() => modal.style.display = "none", 300);
    }
});