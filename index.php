<!--
__        ______      _    _   _  ____ _     _____ ____  ____  
\ \      / /  _ \    / \  | \ | |/ ___| |   | ____|  _ \/ ___| 
 \ \ /\ / /| |_) |  / _ \ |  \| | |  _| |   |  _| | |_) \___ \ 
  \ V  V / |  _ <  / ___ \| |\  | |_| | |___| |___|  _ < ___) |
   \_/\_/  |_| \_\/_/   \_\_| \_|\____|_____|_____|_| \_\____/ 
                Aboga-A, Cledera, Festin, Gamora 
-->

<?php
$successMessage = isset($_GET['success']) ? $_GET['success'] : '';
$errorMessage = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calabanga Community College - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <img src="Assets/eCCC_Logo.png" alt="eCCC Logo" class="logo">
        <h1>Calabanga Community College</h1>
        <p class="subtitle">Enter your credentials to access your account</p>

        <!-- Main Login Form -->
        <form action="login.php" method="post">
            <div class="input-group">
                <label for="Username">Username</label>
                <input type="text" id="Username" name="Username" placeholder="Enter your student ID" required>
            </div>

            <div class="input-group">
                <label for="Password">Password</label>
                <input type="password" id="Password" name="Password" placeholder="Enter your password" required>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="error-message">Invalid username or password. Please try again.</div>
            <?php endif; ?>

            <button type="submit" class="login-btn">Log In</button>
            <a href="#" class="forgot-password" onclick="showForgotPasswordForm()">Forgot password?</a>


            <div class="help-text">
                Need help? <a href="#" onclick="showContactForm()">Contact support</a>
            </div>
        </form>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordFormContainer" class="contact-form-container">
        <div class="contact-form">
            <h2>Forgot Password</h2>
            <form action="submitForgotPass.php" method="post">
                <input type="text" name="student_id" placeholder="Enter your Student ID" required>
                <input type="tel" name="phone" placeholder="Enter your 11-digit phone number" 
                       pattern="\d{11}" maxlength="11" minlength="11" required 
                       oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                
                <div class="button-row">
                    <button type="submit" class="submit-btn">Submit</button>
                    <button type="button" class="close-btn" onclick="closeForgotPasswordForm()">Close</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Contact Support Modal -->
    <div id="contactFormContainer" class="contact-form-container">
        <div class="contact-form">
            <h2>Contact Support</h2>
            <form action="submitSupport.php" method="POST">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <textarea name="issue" placeholder="Describe your issue" rows="4" required></textarea>
                <div class="button-row">
                    <button type="submit" class="submit-btn">Submit</button>
                    <button type="button" class="close-btn" onclick="closeContactForm()">Close</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup for success message -->
    <?php if ($successMessage): ?>
        <div id="successPopup" class="popup">
            <div class="popup-message">
                <strong>Success!</strong><br>
                <?php echo $successMessage; ?>
            </div>
            <button class="close-btn" onclick="closePopup()">Close</button>
        </div>
    <?php endif; ?>

    <!-- Popup for error message -->
    <?php if ($errorMessage && $errorMessage != "1"): ?>
    <div id="errorPopup" class="popup error">
        <div class="popup-message">
            <strong>Error!</strong><br>
            <?php echo $errorMessage; ?>
        </div>
        <button class="close-btn" onclick="closePopup()">Close</button>
    </div>
<?php endif; ?>

    <!-- JavaScript -->
    <script>
        function showContactForm() {
            document.getElementById('contactFormContainer').style.display = 'flex';
        }

        function closeContactForm() {
            document.getElementById('contactFormContainer').style.display = 'none';
        }

        function showForgotPasswordForm() {
            document.getElementById('forgotPasswordFormContainer').style.display = 'flex';
        }

        function closeForgotPasswordForm() {
            document.getElementById('forgotPasswordFormContainer').style.display = 'none';
        }

        function closePopup() {
            var successPopup = document.getElementById('successPopup');
            var errorPopup = document.getElementById('errorPopup');

            if (successPopup) {
                successPopup.style.display = 'none';
            }

            if (errorPopup) {
                errorPopup.style.display = 'none';
            }
        }

        window.onload = function() {
            document.getElementById('contactFormContainer').style.display = 'none';
            document.getElementById('forgotPasswordFormContainer').style.display = 'none';
            
            <?php if ($successMessage): ?>
                document.getElementById('successPopup').style.display = 'block';
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                document.getElementById('errorPopup').style.display = 'block';
            <?php endif; ?>
        };
    </script>
</body>
</html>
