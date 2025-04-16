<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

// Fetch the user information from the session
$firstName = $_SESSION['firstname'] ?? 'Unknown'; 
$lastName = $_SESSION['lastname'] ?? 'User'; 
$role = $_SESSION['role'];
$username = $_SESSION['username']; 
$photoData = $_SESSION['photo'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="settings.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
     <div class="sidebar-header">
            <!-- User Profile Picture -->
            <?php if ($photoData): ?>
                <img src="data:image/jpeg;base64,<?php echo $photoData; ?>" alt="User Photo" class="sidebar-logo">
            <?php else: ?>
                <img src="default-photo.png" alt="Default User Photo" class="sidebar-logo"> <!-- Default photo if no user photo -->
            <?php endif; ?>
            <h1 class="sidebar-title"><?php echo $firstName . ' ' . $lastName; ?></h1>
            <p class="sidebar-role"><?php echo ucfirst($role); ?></p> <!-- Display role (admin, faculty, etc.) -->
        </div>
    </div>
        
        <nav class="sidebar-menu">
            <p class="menu-category">Main</p>
            <a href="../adminlogin.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            
            <p class="menu-category">Management</p>
            <a href="#" class="menu-item">
                <i class="fas fa-user-graduate"></i>
                <span class="menu-text">Students</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chalkboard-teacher"></i>
                <span class="menu-text">Faculty</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-book"></i>
                <span class="menu-text">Courses</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Grades</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-calendar"></i>
                <span class="menu-text">Calendar</span>
            </a>
            
            <p class="menu-category">System</p>
            <a href="adminlogin src/settings.php" class="menu-item active">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
            </a>
            <a href="adminlogin src/usermanagement.php" class="menu-item">
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
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <button class="toggle-sidebar" id="toggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <h1 class="header-title">Settings</h1>
            
            <div class="header-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                
                <div class="user-profile">
                    <div class="user-avatar">
                        <span>A</span>
                    </div>
                    <div class="user-info">
                        <p class="user-name">Admin User</p>
                        <p class="user-role">System Administrator</p>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Dashboard Content -->
        <div class="dashboard">
            <h2 class="dashboard-title">Welcome, Admin!</h2>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3>1,245</h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon faculty">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-info">
                        <h3>87</h3>
                        <p>Faculty Members</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon courses">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3>42</h3>
                        <p>Active Courses</p>
                    </div>
                </div>        
            </div>
            
            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Activity -->
                <div class="dashboard-card recent-activity">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                        <a href="#" class="card-action">View All</a>
                    </div>

        </div>
    </main>

    <script>
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.getElementById('toggleSidebar');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggleBtn.contains(event.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>