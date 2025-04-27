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
            <a href="studentlogin.php" class="menu-item <?php echo isActive('studentlogin', $currentPage); ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
                <a href="student_profile.php" class="menu-item <?php echo isActive('student_profile', $currentPage); ?>">
                <i class="fa-solid fa-user"></i>
                <span class="menu-text">Profile</span>
            </a>
            <p class="menu-category">Student</p>
            <a href="student_mysubjects.php" class="menu-item <?php echo isActive('student_mysubjects', $currentPage); ?>">
                <i class="fa-solid fa-book-open"></i>
                <span class="menu-text">My Subjects</span>
            </a>
            <a href="student_academicprogress.php" class="menu-item <?php echo isActive('student_academicprogress', $currentPage); ?>">
                <i class="fa-solid fa-graduation-cap"></i>
                <span class="menu-text">Academic Progress</span>
            </a>
            <p class="menu-category">Documents</p>
            <a href="student_requestdocuments.php" class="menu-item <?php echo isActive('student_requestdocuments', $currentPage); ?>">
                <i class="fa-solid fa-file-invoice"></i>
                <span class="menu-text">Request Document</span>
            </a>
            <a href="student_trackstatus.php" class="menu-item <?php echo isActive('student_trackstatus', $currentPage); ?>">
                <i class="fa-solid fa-ticket"></i>
                <span class="menu-text">Track Status</span>
            </a>
            <p class="menu-category">System</p>
            <a href="student_settings.php" class="menu-item <?php echo isActive('programhead_settings', $currentPage); ?>">
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