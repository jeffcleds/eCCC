// togglesidebarclose.js

// Sidebar toggle with overlay
var toggleSidebarBtn = document.getElementById('toggleSidebar');
var sidebar = document.querySelector('.sidebar');

// Create overlay element if it doesn't exist
var overlay = document.querySelector('.sidebar-overlay');
if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
}

if (toggleSidebarBtn && sidebar) {
    toggleSidebarBtn.addEventListener('click', function () {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function () {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
}

// Handle window resize
window.addEventListener('resize', function () {
    if (window.innerWidth > 991) {
        // Reset sidebar on larger screens
        if (sidebar) {
            sidebar.classList.remove('active');
        }
        overlay.classList.remove('active');
    }
});
