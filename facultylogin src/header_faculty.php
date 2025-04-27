<?php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Define page titles
$pageTitles = [
    'facultylogin.php' => 'Dashboard',
    'faculty_profile.php' => 'Profile',
    'faculty_mysubjects.php' => 'My Subjects',
    'faculty_submitgrades.php' => 'Submit Grades',
    'faculty_grades.php' => 'Grades',
    'faculty_settings.php' => 'Settings',
];

// Get current page name and set title
$currentPage = basename($_SERVER['PHP_SELF']);
$currentTitle = $pageTitles[$currentPage] ?? 'Administration Panel';
?>

<header class="header">
    <button class="toggle-sidebar" id="toggleSidebar">
        <i class="fas fa-bars"></i>
    </button>
    <h1 class="header-title"><?php echo $currentTitle; ?></h1>
    <div class="header-actions">
        <button class="notification-btn" id="notificationButton">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">2</span>
        </button>
        <div id="notification-dropdown" class="notification-dropdown">
            <div class="notification-header">
                <h3>Notifications</h3>
                <a href="#" class="notification-action">Mark all as read</a>
            </div>
            <div class="notification-list">
                <div class="notification-item unread">
                    <div class="notification-icon add">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="notification-content">
                        <p class="notification-title">New student registered: Joshua Gamora</p>
                        <p class="notification-time">Today, 10:30 AM</p>
                    </div>
                </div>
                <div class="notification-item unread">
                    <div class="notification-icon edit">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="notification-content">
                        <p class="notification-title">Course schedule updated: Computer Science 101</p>
                        <p class="notification-time">Yesterday, 3:45 PM</p>
                    </div>
                </div>
            </div>
            <div class="notification-footer">
                <a href="#">View all notifications</a>
            </div>
        </div>
        <div class="user-profile" id="userProfile">
            <div class="user-avatar">
                <?php if ($photoData): ?>
                    <img src="data:image/jpeg;base64,<?php echo $photoData; ?>" alt="User Photo" class="profile-logo">
                <?php else: ?>
                    <img src="../Pictures/default-photo.png" alt="Default User Photo" class="profile-logo">
                <?php endif; ?>
            </div>
            <div id="dropdown-menu" class="dropdown-content">
                <b class="rightsidepicname"><?php echo $firstName . ' ' . $lastName; ?></b>
                <p class="rightsidepicrole" style="color: grey !important; font-size: 12px !important;"><?php echo ucfirst($role); ?></p>
                <a href="faculty_profile.php"><i class="fa-solid fa-user"></i> Profile</a>
                <a href="faculty_settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Notification dropdown functionality
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notification-dropdown');
    
    if (notificationButton && notificationDropdown) {
        notificationButton.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = notificationDropdown.style.display === 'block';
            
            // Hide all dropdowns first
            document.querySelectorAll('.dropdown-content, .notification-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
            
            // Toggle this dropdown
            notificationDropdown.style.display = isVisible ? 'none' : 'block';
        });
    }

    // User profile dropdown functionality
    const userProfile = document.getElementById('userProfile');
    const userDropdown = document.getElementById('dropdown-menu');
    
    if (userProfile && userDropdown) {
        userProfile.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = userDropdown.style.display === 'block';
            
            // Hide all dropdowns first
            document.querySelectorAll('.dropdown-content, .notification-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
            
            // Toggle this dropdown
            userDropdown.style.display = isVisible ? 'none' : 'block';
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (notificationDropdown) notificationDropdown.style.display = 'none';
        if (userDropdown) userDropdown.style.display = 'none';
    });

    // Mark notifications as read
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            this.classList.remove('unread');
            updateNotificationBadge();
        });
    });

    function updateNotificationBadge() {
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = unreadCount;
            badge.style.display = unreadCount > 0 ? 'flex' : 'none';
        }
    }
});
</script>