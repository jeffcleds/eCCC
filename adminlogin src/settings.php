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

// Process form submissions
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle different form submissions based on form identifier
    if (isset($_POST['update_profile'])) {
        // Process profile update
        $successMessage = 'Profile settings updated successfully!';
    } elseif (isset($_POST['update_password'])) {
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
    <style>
        /* Settings Page Specific Styles */
        .settings-container {
            padding: 20px;
        }
        
        .settings-header {
            margin-bottom: 30px;
        }
        
        .settings-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .settings-description {
            color: #6c757d;
            font-size: 14px;
        }
        
        .settings-tabs {
            display: flex;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .settings-tab {
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 500;
            color: #495057;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .settings-tab.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .settings-tab:hover:not(.active) {
            color: #212529;
            border-bottom: 2px solid #dee2e6;
        }
        
        .settings-content {
            display: none;
        }
        
        .settings-content.active {
            display: block;
        }
        
        .settings-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .settings-section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-color-rgb), 0.25);
        }
        
        .form-row {
            display: flex;
            margin-left: -10px;
            margin-right: -10px;
        }
        
        .form-col {
            flex: 1;
            padding: 0 10px;
        }
        
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            padding: 10px 20px;
            font-size: 14px;
            line-height: 1.5;
            border-radius: 4px;
            transition: all 0.15s ease-in-out;
            border: none;
        }
        
        .btn-primary {
            color: #fff;
            background-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: rgba(var(--primary-color-rgb), 0.9);
        }
        
        .btn-secondary {
            color: #fff;
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--primary-color);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .toggle-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .toggle-label {
            font-weight: 500;
            font-size: 14px;
        }
        
        .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .color-picker-container {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .color-option {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .color-option.selected {
            border-color: #333;
        }
        
        .avatar-upload {
            position: relative;
            max-width: 150px;
            margin-bottom: 20px;
        }
        
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 10px;
            border: 2px solid #eee;
        }
        
        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-edit {
            position: absolute;
            right: 5px;
            bottom: 30px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .avatar-edit i {
            color: white;
            font-size: 14px;
        }
        
        .avatar-edit input {
            display: none;
        }
    </style>
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
                <!-- Settings Tabs -->
                <div class="mobile-settings-menu">
                    <select class="mobile-settings-select" id="mobile-settings-tabs">
                        <option value="profile">Profile</option>
                        <option value="security">Security</option>
                        <option value="notifications">Notifications</option>
                        <option value="appearance">Appearance</option>
                        <option value="system">System</option>
                    </select>
                </div>

                <div class="settings-tabs">
                    <div class="settings-tab active" data-tab="profile">
                        <i class="fas fa-user"></i> Profile
                    </div>
                    <div class="settings-tab" data-tab="security">
                        <i class="fas fa-lock"></i> Security
                    </div>
                    <div class="settings-tab" data-tab="notifications">
                        <i class="fas fa-bell"></i> Notifications
                    </div>
                    <div class="settings-tab" data-tab="appearance">
                        <i class="fas fa-palette"></i> Appearance
                    </div>
                    <div class="settings-tab" data-tab="system">
                        <i class="fas fa-cogs"></i> System
                    </div>
                </div>
                
                <!-- Profile Settings -->
                <div class="settings-content active" id="profile-settings">
                    <div class="settings-section">
                        <h3 class="settings-section-title">Profile Information</h3>
                        <form action="settings.php" method="POST">
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
                                        <input type="file" id="profile-photo" name="profile_photo" accept="image/*">
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="first-name">First Name</label>
                                        <input type="text" class="form-control" id="first-name" name="first_name" value="<?php echo $firstName; ?>">
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="last-name">Last Name</label>
                                        <input type="text" class="form-control" id="last-name" name="last_name" value="<?php echo $lastName; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="admin@calabangacc.edu.ph">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="+63 912 345 6789">
                            </div>
                            
                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4">Administrator at Calabanga Community College with over 5 years of experience in educational management.</textarea>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="settings-content" id="security-settings">
                    <div class="settings-section">
                        <h3 class="settings-section-title">Change Password</h3>
                        <form action="settings.php" method="POST">
                            <div class="form-group">
                                <label for="current-password">Current Password</label>
                                <input type="password" class="form-control" id="current-password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new-password">New Password</label>
                                <input type="password" class="form-control" id="new-password" name="new_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm-password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="settings-section">
                        <h3 class="settings-section-title">Two-Factor Authentication</h3>
                        <div class="toggle-row">
                            <span class="toggle-label">Enable Two-Factor Authentication</span>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <p style="font-size: 14px; color: #6c757d; margin-top: 10px;">
                            Two-factor authentication adds an extra layer of security to your account by requiring more than just a password to sign in.
                        </p>
                    </div>
                    
                    <div class="settings-section">
                        <h3 class="settings-section-title">Login Sessions</h3>
                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                                <div>
                                    <p style="margin: 0; font-weight: 500;">Chrome on Windows</p>
                                    <p style="margin: 0; font-size: 12px; color: #6c757d;">Manila, Philippines - Current session</p>
                                </div>
                                <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;">This Device</button>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                                <div>
                                    <p style="margin: 0; font-weight: 500;">Safari on iPhone</p>
                                    <p style="margin: 0; font-size: 12px; color: #6c757d;">Naga City, Philippines - April 15, 2025</p>
                                </div>
                                <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;">Logout</button>
                            </div>
                        </div>
                        <button class="btn btn-secondary">Logout from all devices</button>
                    </div>
                </div>
                
                <!-- Notification Settings -->
                <div class="settings-content" id="notifications-settings">
                    <div class="settings-section">
                        <h3 class="settings-section-title">Notification Preferences</h3>
                        <form action="settings.php" method="POST">
                            <div class="toggle-row">
                                <span class="toggle-label">Email Notifications</span>
                                <label class="switch">
                                    <input type="checkbox" name="email_notifications" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="toggle-row">
                                <span class="toggle-label">Browser Notifications</span>
                                <label class="switch">
                                    <input type="checkbox" name="browser_notifications" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="toggle-row">
                                <span class="toggle-label">SMS Notifications</span>
                                <label class="switch">
                                    <input type="checkbox" name="sms_notifications">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <h4 style="margin-top: 20px; font-size: 16px; font-weight: 500;">Notify me about:</h4>
                            
                            <div class="toggle-row">
                                <span class="toggle-label">New student registrations</span>
                                <label class="switch">
                                    <input type="checkbox" name="notify_student_reg" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="toggle-row">
                                <span class="toggle-label">Course updates</span>
                                <label class="switch">
                                    <input type="checkbox" name="notify_course_updates" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="toggle-row">
                                <span class="toggle-label">Faculty changes</span>
                                <label class="switch">
                                    <input type="checkbox" name="notify_faculty_changes" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="toggle-row">
                                <span class="toggle-label">System updates</span>
                                <label class="switch">
                                    <input type="checkbox" name="notify_system_updates" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="toggle-row">
                                <span class="toggle-label">Security alerts</span>
                                <label class="switch">
                                    <input type="checkbox" name="notify_security_alerts" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="form-group" style="margin-top: 20px;">
                                <button type="submit" name="update_notifications" class="btn btn-primary">Save Preferences</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Appearance Settings -->
                <div class="settings-content" id="appearance-settings">
                    <div class="settings-section">
                        <h3 class="settings-section-title">Theme Settings</h3>
                        <div class="toggle-row">
                            <span class="toggle-label">Dark Mode</span>
                            <label class="switch">
                                <input type="checkbox" id="dark-mode-toggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <h4 style="font-size: 16px; font-weight: 500; margin-bottom: 10px;">Primary Color</h4>
                            <div class="color-picker-container">
                                <div class="color-option selected" style="background-color: #3498db;" data-color="#3498db"></div>
                                <div class="color-option" style="background-color: #2ecc71;" data-color="#2ecc71"></div>
                                <div class="color-option" style="background-color: #e74c3c;" data-color="#e74c3c"></div>
                                <div class="color-option" style="background-color: #9b59b6;" data-color="#9b59b6"></div>
                                <div class="color-option" style="background-color: #f39c12;" data-color="#f39c12"></div>
                                <div class="color-option" style="background-color: #1abc9c;" data-color="#1abc9c"></div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <h4 style="font-size: 16px; font-weight: 500; margin-bottom: 10px;">Font Size</h4>
                            <div style="display: flex; align-items: center;">
                                <span style="font-size: 12px; margin-right: 10px;">A</span>
                                <input type="range" min="12" max="20" value="14" class="form-control" id="font-size-slider" style="flex: 1;">
                                <span style="font-size: 20px; margin-left: 10px;">A</span>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 20px;">
                            <button type="button" id="save-appearance" class="btn btn-primary">Save Appearance</button>
                            <button type="button" id="reset-appearance" class="btn btn-secondary" style="margin-left: 10px;">Reset to Default</button>
                        </div>
                    </div>
                </div>
                
                <!-- System Settings -->
                <div class="settings-content" id="system-settings">
                    <div class="settings-section">
                        <h3 class="settings-section-title">System Configuration</h3>
                        <form action="settings.php" method="POST">
                            <div class="form-group">
                                <label for="school-name">School Name</label>
                                <input type="text" class="form-control" id="school-name" name="school_name" value="Calabanga Community College">
                            </div>
                            
                            <div class="form-group">
                                <label for="school-address">School Address</label>
                                <input type="text" class="form-control" id="school-address" name="school_address" value="Calabanga, Camarines Sur, Philippines">
                            </div>
                            
                            <div class="form-group">
                                <label for="school-email">School Email</label>
                                <input type="email" class="form-control" id="school-email" name="school_email" value="info@calabangacc.edu.ph">
                            </div>
                            
                            <div class="form-group">
                                <label for="school-phone">School Phone</label>
                                <input type="tel" class="form-control" id="school-phone" name="school_phone" value="+63 54 123 4567">
                            </div>
                            
                            <div class="form-group">
                                <label for="academic-year">Current Academic Year</label>
                                <input type="text" class="form-control" id="academic-year" name="academic_year" value="2025-2026">
                            </div>
                            
                            <div class="form-group">
                                <label for="semester">Current Semester</label>
                                <select class="form-control" id="semester" name="semester">
                                    <option value="1">First Semester</option>
                                    <option value="2" selected>Second Semester</option>
                                    <option value="summer">Summer</option>
                                </select>
                            </div>
                            
                            <div class="toggle-row">
                                <span class="toggle-label">Enable Online Enrollment</span>
                                <label class="switch">
                                    <input type="checkbox" name="online_enrollment" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="toggle-row">
                                <span class="toggle-label">Enable Online Grade Viewing</span>
                                <label class="switch">
                                    <input type="checkbox" name="online_grades" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="toggle-row">
                                <span class="toggle-label">Enable System Maintenance Mode</span>
                                <label class="switch">
                                    <input type="checkbox" name="maintenance_mode">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="form-group" style="margin-top: 20px;">
                                <button type="submit" name="update_system" class="btn btn-primary">Save System Settings</button>
                            </div>
                        </form>
                    </div>
                    
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
</script>
</body>
</html>
