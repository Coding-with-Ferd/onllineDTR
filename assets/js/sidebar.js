document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector('#sidebar');
    const toggleBtn = document.querySelector('#toggleBtn');

    // Sidebar Toggle Logic
    if (localStorage.getItem('sidebarState') === 'collapsed') {
        sidebar.classList.add('collapsed');
    }

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
    });

    // Active Link Logic
    const sidebarLinks = document.querySelectorAll('.sidebar nav a');
    const currentPage = window.location.pathname.split("/").pop();

    sidebarLinks.forEach(link => {
        const linkHref = link.getAttribute('href').split("/").pop();
        if (linkHref === currentPage) {
            link.classList.add('active');
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {

    const sidebarLinks = document.querySelectorAll('.sidebar nav a');

    const currentPage = window.location.pathname.split("/").pop();

    sidebarLinks.forEach(link => {
        const linkHref = link.getAttribute('href').split("/").pop(); 

        if (linkHref === currentPage) {
            link.classList.add('active');
        }
    });

});


// Live date & time
function updateDateTime() {
    const now = new Date();
    const options = { 
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    };
    document.getElementById('datetime').textContent = now.toLocaleString('en-US', options);
}
updateDateTime();
setInterval(updateDateTime, 1000);