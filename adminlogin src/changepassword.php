<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="settings.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <div class="header-title">Change Password</div>
        </div>
        
        <div class="dashboard">
            <div class="settings-container">
                <div class="settings-section">
                    <h2 class="settings-section-title">Change Password</h2>
                    
                    <?php if (isset($_SESSION['password_error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['password_error']; unset($_SESSION['password_error']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['password_success'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['password_success']; unset($_SESSION['password_success']); ?></div>
                    <?php endif; ?>
                    
                    <form action="updatepassword.php" method="POST" class="password-form">
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
                            <button type="submit" class="btn btn-primary">Change Password</button>
                            <a href="settings.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>