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

// Process success/error messages
$successMessage = '';
$errorMessage = '';

if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch user information from the session
$firstName = $_SESSION['firstname'] ?? 'Unknown';
$lastName = $_SESSION['lastname'] ?? 'User';
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$photoData = $_SESSION['photo'] ?? null;
$email = $_SESSION['email'] ?? '';
$PhoneNumber = $_SESSION['phonenumber'] ?? '';
$AddressDetails = $_SESSION['addressdetails'] ?? '';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Process form submissions for other settings sections
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle different form submissions based on form identifier
    if (isset($_POST['update_password'])) {
        // Process password update
        $successMessage = 'Password updated successfully!';
    } elseif (isset($_POST['update_notifications'])) {
        // Process notification settings
        $successMessage = 'Notification preferences saved!';
    } elseif (isset($_POST['update_system'])) {
        // Process system settings
        $successMessage = 'System settings updated successfully!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Calabanga Community College</title>
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
            <img src="../Assets/eCCC_Logo.png" alt="Calabanga Community College Logo" class="sidebar-logo">
            <h1 class="sidebar-title"><?php echo $firstName . ' ' . $lastName; ?></h1>
            <p class="sidebar-role"><?php echo ucfirst($role); ?></p>
        </div>
        <div class="sidebar-body">
            <nav class="sidebar-menu">
                <p class="menu-category">Main</p>
                <a href="adminlogin.php" class="menu-item">
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
                <a href="settings.php" class="menu-item active">
                    <i class="fas fa-cog"></i>
                    <span class="menu-text">Settings</span>
                </a>
                <a href="usermanagement.php" class="menu-item">
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
                    <div class="user-avatar">
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
                </div>
            </div>
        </header>

        <!-- Settings Content -->
        <div class="dashboard">
            <div class="settings-container">
                <!-- Success/Error Messages -->
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Settings Header -->
                <div class="settings-header">
                    <h2 class="settings-title">System Settings</h2>
                    <p class="settings-description">Manage your account settings and preferences</p>
                </div>
              

                <div class="settings-tabs">
                    <div class="settings-tab active" data-tab="profile">
                        <i class="fas fa-user"></i> Profile
                    </div>
                    <div class="settings-tab" data-tab="security">
                        <i class="fas fa-lock"></i> Security
                    </div>
                    <div class="settings-tab" data-tab="system">
                        <i class="fas fa-cogs"></i> System
                    </div>
                </div>
                
                <!-- Profile Settings -->
                <div class="settings-content active" id="profile-settings">
                    <div class="settings-section">
                        <h3 class="settings-section-title">Profile Information</h3>
                        <form action="saveprofilesettings.php" method="POST" enctype="multipart/form-data">
                            <div class="avatar-upload">
                                <div class="avatar-preview">
                                    <?php if ($photoData): ?>
                                        <img src="data:image/jpeg;base64,<?php echo $photoData; ?>" alt="User Photo">
                                    <?php else: ?>
                                        <img src="../Pictures/default-photo.png" alt="Default User Photo">
                                    <?php endif; ?>
                                </div>
                                <div class="avatar-edit">
                                    <label for="profile-photo">
                                        <i class="fas fa-camera"></i>
                                        <input type="file" id="profile-photo" name="profile_picture" accept="image/*">
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="first-name">First Name</label>
                                        <input type="text" class="form-control" id="first-name" name="FirstName" value="<?php echo htmlspecialchars($firstName); ?>">
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="last-name">Last Name</label>
                                        <input type="text" class="form-control" id="last-name" name="LastName" value="<?php echo htmlspecialchars($lastName); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="Email" value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="PhoneNumber" 
                                value="<?php echo htmlspecialchars($PhoneNumber); ?>" 
                                maxlength="11" pattern="\d{11}" title="Phone Number must be 11 digits">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" id="address" name="AddressDetails" value="<?php echo htmlspecialchars($AddressDetails); ?>">
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="settings-content" id="security-settings">
                    <div class="settings-section">
                        <h2 class="settings-section-title">Change Password</h2>
                        
                        <?php if (isset($_SESSION['password_error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['password_error']; unset($_SESSION['password_error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['password_success'])): ?>
                            <div class="alert alert-success">
                                <?php echo $_SESSION['password_success']; unset($_SESSION['password_success']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="updatepassword.php" method="POST">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                                <small class="form-text">Password must be at least 6 characters long</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- System Settings -->
                <div class="settings-content" id="system-settings">
                    <div class="settings-section">
                        <h3 class="settings-section-title">Backup & Restore</h3>
                        <p style="font-size: 14px; margin-bottom: 15px;">Create a backup of your system data or restore from a previous backup.</p>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i> Create Backup
                            </button>
                            <button class="btn btn-secondary">
                                <i class="fas fa-upload"></i> Restore Backup
                            </button>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <h4 style="font-size: 16px; font-weight: 500; margin-bottom: 10px;">Automatic Backups</h4>
                            <div class="toggle-row">
                                <span class="toggle-label">Enable Automatic Backups</span>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="form-group" style="margin-top: 10px;">
                                <label for="backup-frequency">Backup Frequency</label>
                                <select class="form-control" id="backup-frequency">
                                    <option>Daily</option>
                                    <option selected>Weekly</option>
                                    <option>Monthly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="../javascript/togglesidebar.js"></script>
    <script>
    // Wait for the DOM to be fully loaded
    document.addEventListener("DOMContentLoaded", function() {
        console.log("DOM fully loaded");
        
        // Get references to all elements we need
        var userAvatar = document.querySelector('.user-avatar');
        var userDropdown = document.getElementById('dropdown-menu');
        var notificationBtn = document.querySelector('.notification-btn');
        var notificationDropdown = document.getElementById('notification-dropdown');
        
        // User avatar click handler
        if (userAvatar && userDropdown) {
            userAvatar.addEventListener('click', function(event) {
                console.log("User avatar clicked");
                event.stopPropagation(); // Prevent the click from bubbling up
                
                // Toggle user dropdown
                var isVisible = userDropdown.style.display === "block";
                
                // Hide all dropdowns first
                hideAllDropdowns();
                
                // Show this dropdown if it was hidden
                if (!isVisible) {
                    userDropdown.style.display = "block";
                    // Adjust position after showing
                    adjustDropdownPosition();
                }
            });
        }
        
        // Notification button click handler
        if (notificationBtn && notificationDropdown) {
            notificationBtn.addEventListener('click', function(event) {
                console.log("Notification button clicked");
                event.stopPropagation(); // Prevent the click from bubbling up
                
                // Toggle notification dropdown
                var isVisible = notificationDropdown.style.display === "block";
                
                // Hide all dropdowns first
                hideAllDropdowns();
                
                // Show this dropdown if it was hidden
                if (!isVisible) {
                    notificationDropdown.style.display = "block";
                    // Adjust position after showing
                    adjustDropdownPosition();
                }
            });
        }
        
        // Function to hide all dropdowns
        function hideAllDropdowns() {
            console.log("Hiding all dropdowns");
            var allDropdowns = document.querySelectorAll('.dropdown-content, .notification-dropdown');
            allDropdowns.forEach(function(dropdown) {
                dropdown.style.display = "none";
            });
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            console.log("Document clicked");
            hideAllDropdowns();
        });
        
        // Prevent clicks inside dropdowns from closing them
        var dropdownContents = document.querySelectorAll('.dropdown-content, .notification-dropdown');
        dropdownContents.forEach(function(dropdown) {
            dropdown.addEventListener('click', function(event) {
                console.log("Click inside dropdown");
                event.stopPropagation(); // Prevent the click from bubbling up
            });
        });
        
        // Mark notification as read when clicked
        var notificationItems = document.querySelectorAll('.notification-item');
        notificationItems.forEach(function(item) {
            item.addEventListener('click', function() {
                console.log("Notification item clicked");
                this.classList.remove('unread');
                
                // Update badge count
                var unreadCount = document.querySelectorAll('.notification-item.unread').length;
                var badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.textContent = unreadCount;
                    if (unreadCount === 0) {
                        badge.style.display = 'none';
                    }
                }
            });
        });
        
        // Settings tabs functionality
        var tabs = document.querySelectorAll('.settings-tab');
        var contents = document.querySelectorAll('.settings-content');
        
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                console.log("Tab clicked:", this.getAttribute('data-tab'));
                
                // Remove active class from all tabs
                tabs.forEach(function(t) {
                    t.classList.remove('active');
                });
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all content sections
                contents.forEach(function(content) {
                    content.classList.remove('active');
                });
                
                // Show the corresponding content section
                var tabId = this.getAttribute('data-tab');
                document.getElementById(tabId + '-settings').classList.add('active');
            });
        });
        
        // Color picker functionality
        var colorOptions = document.querySelectorAll('.color-option');
        colorOptions.forEach(function(option) {
            option.addEventListener('click', function() {
                console.log("Color option clicked:", this.getAttribute('data-color'));
                
                // Remove selected class from all options
                colorOptions.forEach(function(o) {
                    o.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Apply the selected color
                var color = this.getAttribute('data-color');
                document.documentElement.style.setProperty('--primary-color', color);
            });
        });
        
        // Profile photo preview
        var photoInput = document.getElementById('profile-photo');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                console.log("Photo input changed");
                if (e.target.files && e.target.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var preview = document.querySelector('.avatar-preview img');
                        if (preview) {
                            preview.src = e.target.result;
                            console.log("Preview updated");
                        }
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
        
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
            toggleSidebarBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });
            
            // Close sidebar when clicking overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
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
            
            // Also adjust dropdown positions
            adjustDropdownPosition();
        });
        
        // Improve dropdown positioning in small viewports
        function adjustDropdownPosition() {
            var dropdowns = document.querySelectorAll('.dropdown-content, .notification-dropdown');
            var viewportWidth = window.innerWidth;
            
            dropdowns.forEach(function(dropdown) {
                if (dropdown.style.display === 'block') {
                    var rect = dropdown.getBoundingClientRect();
                    
                    // If dropdown extends beyond right edge
                    if (rect.right > viewportWidth) {
                        dropdown.style.right = '10px';
                        dropdown.style.left = 'auto';
                    }
                }
            });
        }
        
        console.log("All event listeners attached");
    });

    document.addEventListener("DOMContentLoaded", function() {
    // Get a reference to the toggle button
    var toggleBtn = document.getElementById('toggleSidebar');
    
    // Check if it exists
    if (toggleBtn) {
        console.log("Toggle sidebar button found");
        
        // Remove any existing event listeners (this is a trick that might help)
        toggleBtn.outerHTML = toggleBtn.outerHTML;
        
        // Get the new reference after replacing the element
        toggleBtn = document.getElementById('toggleSidebar');
        
        // Add our event listener
        toggleBtn.addEventListener('click', function(event) {
            console.log("Toggle sidebar button clicked");
            event.preventDefault();
            event.stopPropagation();
            
            // Toggle the sidebar
            var sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
                console.log("Sidebar toggled");
                
                // Toggle the overlay
                var overlay = document.querySelector('.sidebar-overlay');
                if (overlay) {
                    overlay.classList.toggle('active');
                }
            }
        });
    } else {
        console.error("Toggle sidebar button not found");
    }
});
</script>
</body>
</html>