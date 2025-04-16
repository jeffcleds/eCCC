<?php
session_start();

// Redirect if the user is not an the expected role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Fetch user information from the session
$firstName = $_SESSION['firstname'] ?? 'Unknown';
$lastName = $_SESSION['lastname'] ?? 'User';
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$photoData = $_SESSION['photo'] ?? null;
$PhoneNumber = $_SESSION['phoneNumber'] ?? '';
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
    <link rel="stylesheet" href="adminloginstyles.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <button class="toggle-sidebar" id="toggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="header-title">Dashboard</h1>
            <div class="header-actions">
            <button class="notification-btn" onclick="toggleNotificationDropdown()">
    <i class="fas fa-bell"></i>
    <span class="notification-badge">3</span>
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
        <div class="notification-item unread">
            <div class="notification-icon add">
                <i class="fas fa-plus"></i>
            </div>
            <div class="notification-content">
                <p class="notification-title">New faculty member added: Prof. Marbert Plazo</p>
                <p class="notification-time">Yesterday, 1:20 PM</p>
            </div>
        </div>
    </div>
    <div class="notification-footer">
        <a href="#">View all notifications</a>
    </div>
</div>
                <div class="user-profile">
                    <div class="user-avatar" onclick="toggleDropdown()">
                        <?php if ($photoData): ?>
                            <img src="data:image/jpeg;base64,<?php echo $photoData; ?>" alt="User Photo" class="profile-logo">
                        <?php else: ?>
                            <img src="../Pictures/default-photo.png" alt="Default User Photo" class="profile-logo">
                        <?php endif; ?>
                    </div>
                    <div id="dropdown-menu" class="dropdown-content">
                        <b class="rightsidepicname"><?php echo $firstName . ' ' . $lastName; ?></b>
                        <p class="rightsidepicrole"><?php echo ucfirst($role); ?></p>
                        <a href="profile.php"><i class="fa-solid fa-universal-access"></i>Profile</a>
                        <a href="settings.php"><i class="fas fa-cog"></i>Settings</a>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
                    </div>
                    <script>
                        function toggleDropdown() {
                            var menu = document.getElementById("dropdown-menu");
                            menu.style.display = menu.style.display === "block" ? "none" : "block";
                        }
                        window.onclick = function(event) {
                            if (!event.target.closest('.user-avatar')) {
                                var menu = document.getElementById("dropdown-menu");
                                if (menu) menu.style.display = "none";
                            }
                        }
                    </script>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard">
            <h2 class="dashboard-title">Welcome, <?php echo $firstName . ' ' . $lastName; ?>!</h2>
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
                        <p>Registrar Members</p>
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
                    <div class="activity-item">
                        <div class="activity-icon add">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">New student registered: Joshua Gamora</p>
                            <p class="activity-time">Today, 10:30 AM</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon edit">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">Course schedule updated: Computer Science 101</p>
                            <p class="activity-time">Yesterday, 3:45 PM</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon add">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">New faculty member added: Prof. Marbert Plazo</p>
                            <p class="activity-time">Yesterday, 1:20 PM</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon delete">
                            <i class="fas fa-trash"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">Course removed: Advanced Calculus</p>
                            <p class="activity-time">Apr 12, 2025, 9:15 AM</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon edit">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">Student information updated: Chrystian Festin</p>
                            <p class="activity-time">Apr 11, 2025, 2:30 PM</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <!-- Upcoming Events -->
                    <div class="dashboard-card upcoming-events">
                        <div class="card-header">
                            <h3 class="card-title">Upcoming Events</h3>
                            <a href="#" class="card-action">View Calendar</a>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <span class="event-day">15</span>
                                <span class="event-month">Apr</span>
                            </div>
                            <div class="event-info">
                                <p class="event-title">Faculty Meeting</p>
                                <p class="event-details">9:00 AM - RM204</p>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <span class="event-day">18</span>
                                <span class="event-month">Apr</span>
                            </div>
                            <div class="event-info">
                                <p class="event-title">Enrollment Deadline</p>
                                <p class="event-details">All Day</p>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <span class="event-day">22</span>
                                <span class="event-month">Apr</span>
                            </div>
                            <div class="event-info">
                                <p class="event-title">STI Tagisan ng Talino</p>
                                <p class="event-details">1:00 PM - STI Lobby</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <button style="padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">
                                <i class="fas fa-user-plus" style="margin-right: 5px;"></i> Add Student
                            </button>
                            <button style="padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">
                                <i class="fas fa-user-tie" style="margin-right: 5px;"></i> Add Faculty
                            </button>
                            <button style="padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">
                                <i class="fas fa-book-medical" style="margin-right: 5px;"></i> Add Course
                            </button>
                            <button style="padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">
                                <i class="fas fa-file-export" style="margin-right: 5px;"></i> Export Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="../javascript/togglesidebar.js"></script>
    <script>
    // Function to toggle notification dropdown
    function toggleNotificationDropdown() {
        var dropdown = document.getElementById("notification-dropdown");
        var isVisible = dropdown.style.display === "block";
        
        // Hide all dropdowns first
        var allDropdowns = document.querySelectorAll(".dropdown-content, .notification-dropdown");
        allDropdowns.forEach(function(dropdown) {
            dropdown.style.display = "none";
        });
        
        // Toggle this dropdown
        if (!isVisible) {
            dropdown.style.display = "block";
        }
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener("click", function(event) {
        // If click is not on notification button and not inside notification dropdown
        if (!event.target.closest('.notification-btn') && !event.target.closest('.notification-dropdown')) {
            var notificationDropdown = document.getElementById("notification-dropdown");
            if (notificationDropdown) {
                notificationDropdown.style.display = "none";
            }
        }
        
        // If click is not on user avatar and not inside user dropdown
        if (!event.target.closest('.user-avatar') && !event.target.closest('.dropdown-content')) {
            var userDropdown = document.getElementById("dropdown-menu");
            if (userDropdown) {
                userDropdown.style.display = "none";
            }
        }
    });
    
    // Mark notification as read when clicked
    document.addEventListener("DOMContentLoaded", function() {
        var notificationItems = document.querySelectorAll(".notification-item");
        notificationItems.forEach(function(item) {
            item.addEventListener("click", function() {
                this.classList.remove("unread");
                
                // Update badge count
                var unreadCount = document.querySelectorAll(".notification-item.unread").length;
                var badge = document.querySelector(".notification-badge");
                if (badge) {
                    badge.textContent = unreadCount;
                    if (unreadCount === 0) {
                        badge.style.display = "none";
                    }
                }
            });
        });
    });
</script>
</body>
</html>
