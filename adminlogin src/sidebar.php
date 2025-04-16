<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="../Assets/eCCC_Logo.png" alt="Calabanga Community College Logo" class="sidebar-logo">
        <h1 class="sidebar-title"><?php echo $firstName . ' ' . $lastName; ?></h1>
        <p class="sidebar-role"><?php echo ucfirst($role); ?></p>
    </div>
    <div class="sidebar-body">
        <nav class="sidebar-menu">
            <?php
            // Get the current page filename
            $currentPage = basename($_SERVER['PHP_SELF']);
            
            // Function to check if menu item is active
            function isActive($page, $currentPage, $exactMatch = true) {
                if ($exactMatch) {
                    return ($page === $currentPage) ? 'active' : '';
                } else {
                    // For partial matches (e.g., student-view.php matches students.php)
                    return strpos($currentPage, str_replace('.php', '', $page)) !== false ? 'active' : '';
                }
            }
            ?>
            
            <p class="menu-category">Main</p>
            <a href="adminlogin.php" class="menu-item <?php echo isActive('adminlogin.php', $currentPage); ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            <p class="menu-category">Management</p>
            <a href="students.php" class="menu-item <?php echo isActive('students.php', $currentPage, false); ?>">
                <i class="fas fa-user-graduate"></i>
                <span class="menu-text">Students</span>
            </a>
            <a href="faculty.php" class="menu-item <?php echo isActive('faculty.php', $currentPage, false); ?>">
                <i class="fas fa-chalkboard-teacher"></i>
                <span class="menu-text">Faculty</span>
            </a>
            <a href="courses.php" class="menu-item <?php echo isActive('courses.php', $currentPage, false); ?>">
                <i class="fas fa-book"></i>
                <span class="menu-text">Courses</span>
            </a>
            <a href="grades.php" class="menu-item <?php echo isActive('grades.php', $currentPage, false); ?>">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Grades</span>
            </a>
            <a href="calendar.php" class="menu-item <?php echo isActive('calendar.php', $currentPage); ?>">
                <i class="fas fa-calendar"></i>
                <span class="menu-text">Calendar</span>
            </a>
            <p class="menu-category">System</p>
            <a href="settings.php" class="menu-item <?php echo isActive('settings.php', $currentPage, false); ?>">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
            </a>
            <a href="usermanagement.php" class="menu-item <?php echo isActive('usermanagement.php', $currentPage, false); ?>">
                <i class="fas fa-users"></i>
                <span class="menu-text">User Management</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Logout</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            &copy; 2025 Calabanga Community College
        </div>
    </div>
</aside>