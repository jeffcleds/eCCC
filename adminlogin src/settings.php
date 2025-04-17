<?php
require_once 'session_init.php';


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
    <link rel="stylesheet" href="adminloginstyles.css">

</head>
<body>
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <?php include 'header.php'; ?>

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
              
                <!-- Tabs -->
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


    <script>
   document.addEventListener("DOMContentLoaded", function () {
    var tabs = document.querySelectorAll('.settings-tab');
    var contents = document.querySelectorAll('.settings-content');

    function activateTab(tabId) {
      tabs.forEach(t => t.classList.remove('active'));
      contents.forEach(c => c.classList.remove('active'));

      var tabToActivate = document.querySelector('.settings-tab[data-tab="' + tabId + '"]');
      var contentToActivate = document.getElementById(tabId + '-settings');

      if (tabToActivate && contentToActivate) {
        tabToActivate.classList.add('active');
        contentToActivate.classList.add('active');
      }
    }

    var params = new URLSearchParams(window.location.search);
    var initialTab = params.get('tab');
    if (initialTab) {
      activateTab(initialTab);
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var tabId = this.getAttribute('data-tab');
        history.pushState(null, '', '?tab=' + tabId);
        activateTab(tabId);
      });
    });

    window.addEventListener('popstate', function () {
      var params = new URLSearchParams(window.location.search);
      var tabId = params.get('tab') || 'profile';
      activateTab(tabId);
    });


        
        // Profile photo preview
        var photoInput = document.getElementById('profile-photo');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var preview = document.querySelector('.avatar-preview img');
                        if (preview) {
                            preview.src = e.target.result;
                        }
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
        

    });
    </script>

</body>
</html>