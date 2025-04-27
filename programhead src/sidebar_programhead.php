<?php
if(!isset($_SESSION)) {
    include 'session_init.php'; // or session_start();
}

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
        <h1 class="sidebar-title"><?php echo $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; ?></h1>
        <p class="sidebar-role" style="color: white !important; font-size: 15px !important;"><?php echo ucfirst($_SESSION['role']); ?></p>
    </div>
    <div class="sidebar-body">
        <nav class="sidebar-menu">
            <p class="menu-category">Main</p>
            <a href="programheadlogin.php" class="menu-item <?php echo isActive('programheadlogin', $currentPage); ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
                <a href="programhead_profile.php" class="menu-item <?php echo isActive('programhead_profile', $currentPage); ?>">
                <i class="fa-solid fa-user"></i>
                <span class="menu-text">Profile</span>
            </a>
            <p class="menu-category">Program Head Tools</p>
            <a href="programhead_mysubjects.php" class="menu-item <?php echo isActive('programhead_mysubjects', $currentPage); ?>">
                <i class="fas fa-user-tie"></i>
                <span class="menu-text">My Subjects</span>
            </a>
            <a href="programhead_submitgrades.php" class="menu-item <?php echo isActive('programhead_submitgrades', $currentPage); ?>">
                <i class="fa-solid fa-scroll"></i>
                <span class="menu-text">Submit Grades</span>
            </a>
            <a href="programhead_grades.php" class="menu-item <?php echo isActive('programhead_grades', $currentPage); ?>">
                <i class="fa-solid fa-print"></i>
                <span class="menu-text">Grades</span>
            </a>
            <p class="menu-category">Program Head</p>
            <a href="programhead_facultymanagement.php" class="menu-item <?php echo isActive('programhead_facultymanagement', $currentPage); ?>">
                <i class="fa-solid fa-users"></i>
                <span class="menu-text">Faculty Management</span>
            </a>
            <a href="programhead_curriculum.php" class="menu-item <?php echo isActive('programhead_curriculum', $currentPage); ?>">
                <i class="fa-solid fa-book"></i>
                <span class="menu-text">Curriculum</span>
            </a>
            <p class="menu-category">System</p>
            <a href="programhead_settings.php" class="menu-item <?php echo isActive('programhead_settings', $currentPage); ?>">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
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