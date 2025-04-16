<?php
// Get current page name for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Function to check if menu item is active
function isActive($page, $currentPage) {
    // For pages with similar names (e.g., student-view.php and students.php)
    $basePage = str_replace('.php', '', $page);
    return (strpos($currentPage, $basePage) !== false) ? 'active' : '';
}
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="../Assets/eCCC_Logo.png" alt="Calabanga Community College Logo" class="sidebar-logo">
        <h1 class="sidebar-title"><?php echo $firstName . ' ' . $lastName; ?></h1>
        <p class="sidebar-role"><?php echo ucfirst($role); ?></p>
    </div>
    <div class="sidebar-body">
        <nav class="sidebar-menu">
            <p class="menu-category">Main</p>
            <a href="adminlogin.php" class="menu-item <?php echo isActive('adminlogin', $currentPage); ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            
            <p class="menu-category">Management</p>
            <a href="students.php" class="menu-item <?php echo isActive('students', $currentPage); ?>">
                <i class="fas fa-user-graduate"></i>
                <span class="menu-text">Students</span>
            </a>
            <a href="faculty.php" class="menu-item <?php echo isActive('faculty', $currentPage); ?>">
                <i class="fas fa-chalkboard-teacher"></i>
                <span class="menu-text">Faculty</span>
            </a>
            <a href="courses.php" class="menu-item <?php echo isActive('courses', $currentPage); ?>">
                <i class="fas fa-book"></i>
                <span class="menu-text">Courses</span>
            </a>
            <a href="grades.php" class="menu-item <?php echo isActive('grades', $currentPage); ?>">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Grades</span>
            </a>
            <a href="calendar.php" class="menu-item <?php echo isActive('calendar', $currentPage); ?>">
                <i class="fas fa-calendar"></i>
                <span class="menu-text">Calendar</span>
            </a>
            
            <p class="menu-category">System</p>
            <a href="settings.php" class="menu-item <?php echo isActive('settings', $currentPage); ?>">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
            </a>
            <a href="usermanagement.php" class="menu-item <?php echo isActive('usermanagement', $currentPage); ?>">
                <i class="fas fa-users"></i>
                <span class="menu-text">User Management</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Logout</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            &copy; <?php echo date('Y'); ?> Calabanga Community College
        </div>
    </div>
</aside>

<!-- Sidebar Overlay (for mobile) -->
<div class="sidebar-overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.querySelector('.sidebar');
    let overlay = document.querySelector('.sidebar-overlay');
    
    // Create overlay element if it doesn't exist
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }
    
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    if (toggleBtn && sidebar) {
        // Toggle sidebar when button clicked
        toggleBtn.addEventListener('click', toggleSidebar);
        
        // Close sidebar when overlay clicked
        overlay.addEventListener('click', toggleSidebar);
        
        // Close sidebar when clicking a menu item (for mobile)
        document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });
    }
    
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991) {
            // Reset sidebar on larger screens
            if (sidebar) {
                sidebar.classList.remove('active');
            }
            overlay.classList.remove('active');
        }
    });
});
</script>

<script src="../javascript/togglesidebar.js"></script>
<script src="../javascript/togglesidebarclose.js"></script>