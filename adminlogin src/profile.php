<?php
include 'session_init.php';

// Don't start a session here since session_init.php will handle it
// Just check if we need to access session data before session_init.php is included


// Database connection function
function connectDB() {
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbname = "CCCDB";

    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Function to calculate age from birthday
function calculateAge($birthdate) {
    $today = new DateTime();
    $birth = new DateTime($birthdate);
    $age = $today->diff($birth);
    return $age->y;
}

// Handle form submissions
$successMessage = "";
$errorMessage = "";

// Handle personal information update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_personal'])) {
    $conn = connectDB();
    
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $middleInitial = $conn->real_escape_string($_POST['middleInitial']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $birthday = $conn->real_escape_string($_POST['birthday']);
    $address = $conn->real_escape_string($_POST['address']);
    
    $stmt = $conn->prepare("UPDATE Users SET FirstName = ?, MiddleInitial = ?, LastName = ?, Gender = ?, Birthday = ?, AddressDetails = ? WHERE Username = ?");
    $stmt->bind_param("sssssss", $firstName, $middleInitial, $lastName, $gender, $birthday, $address, $_SESSION['username']);
    
    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['firstname'] = $firstName;
        $_SESSION['middleinitial'] = $middleInitial;
        $_SESSION['lastname'] = $lastName;
        $_SESSION['gender'] = $gender;
        $_SESSION['birthday'] = $birthday;
        $_SESSION['addressdetails'] = $address;
        
        $successMessage = "Personal information updated successfully!";
    } else {
        $errorMessage = "Error updating personal information: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}

// Handle contact information update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_contact'])) {
    $conn = connectDB();
    
    $email = $conn->real_escape_string($_POST['email']);
    $phoneNumber = $conn->real_escape_string($_POST['phoneNumber']);
    
    $stmt = $conn->prepare("UPDATE Users SET Email = ?, PhoneNumber = ? WHERE Username = ?");
    $stmt->bind_param("sss", $email, $phoneNumber, $_SESSION['username']);
    
    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['email'] = $email;
        $_SESSION['phonenumber'] = $phoneNumber;
        
        $successMessage = "Contact information updated successfully!";
    } else {
        $errorMessage = "Error updating contact information: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}

// Handle account settings update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_account'])) {
    $conn = connectDB();
    
    $username = $conn->real_escape_string($_POST['username']);
    
    // Check if username already exists (if it's different from current username)
    if ($username !== $_SESSION['username']) {
        $checkStmt = $conn->prepare("SELECT Username FROM Users WHERE Username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Username already exists. Please choose a different one.";
            $checkStmt->close();
        } else {
            $checkStmt->close();
            
            // Update username
            $stmt = $conn->prepare("UPDATE Users SET Username = ? WHERE Username = ?");
            $stmt->bind_param("ss", $username, $_SESSION['username']);
            
            if ($stmt->execute()) {
                // Update session variable
                $_SESSION['username'] = $username;
                
                $successMessage = "Account settings updated successfully!";
            } else {
                $errorMessage = "Error updating account settings: " . $conn->error;
            }
            
            $stmt->close();
        }
    } else {
        $successMessage = "No changes were made to your account settings.";
    }
    
    $conn->close();
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $conn = connectDB();
    
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Verify current password
    $stmt = $conn->prepare("SELECT Username FROM Users WHERE Username = ? AND Password = ?");
    $stmt->bind_param("ss", $_SESSION['username'], $currentPassword);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Current password is correct
        if ($newPassword === $confirmPassword) {
            // Update password
            $updateStmt = $conn->prepare("UPDATE Users SET Password = ? WHERE Username = ?");
            $updateStmt->bind_param("ss", $newPassword, $_SESSION['username']);
            
            if ($updateStmt->execute()) {
                $successMessage = "Password updated successfully!";
            } else {
                $errorMessage = "Error updating password: " . $conn->error;
            }
            
            $updateStmt->close();
        } else {
            $errorMessage = "New passwords do not match.";
        }
    } else {
        $errorMessage = "Current password is incorrect.";
    }
    
    $stmt->close();
    $conn->close();
}

// Handle profile photo upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_photo'])) {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $conn = connectDB();
        
        // Get file info
        $fileName = $_FILES['photo']['name'];
        $fileTmpName = $_FILES['photo']['tmp_name'];
        $fileSize = $_FILES['photo']['size'];
        $fileType = $_FILES['photo']['type'];
        
        // Check if file is an image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            $errorMessage = "Only JPG, PNG, and GIF files are allowed.";
        } 
        // Check file size (max 2MB)
        else if ($fileSize > 2 * 1024 * 1024) {
            $errorMessage = "File size should not exceed 2MB.";
        } else {
            // Read file content
            $imageData = file_get_contents($fileTmpName);
            
            // Update database
            $stmt = $conn->prepare("UPDATE Users SET Photo = ? WHERE Username = ?");
            $stmt->bind_param("ss", $imageData, $_SESSION['username']);
            
            if ($stmt->execute()) {
                // Update session variable
                $_SESSION['photo'] = base64_encode($imageData);
                
                $successMessage = "Profile photo updated successfully!";
            } else {
                $errorMessage = "Error updating profile photo: " . $conn->error;
            }
            
            $stmt->close();
        }
        
        $conn->close();
    } else if ($_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        // If there was an error uploading the file
        $errorMessage = "Error uploading file: " . $_FILES['photo']['error'];
    }
}

// Get user data from session
$user = [
    'FirstName' => $_SESSION['firstname'],
    'MiddleInitial' => $_SESSION['middleinitial'],
    'LastName' => $_SESSION['lastname'],
    'IDNumber' => $_SESSION['idnumber'],
    'Birthday' => $_SESSION['birthday'],
    'Email' => $_SESSION['email'],
    'AddressDetails' => $_SESSION['addressdetails'],
    'PhoneNumber' => $_SESSION['phonenumber'],
    'Gender' => $_SESSION['gender'],
    'Photo' => $_SESSION['photo'], // Already base64 encoded in login.php
    'Username' => $_SESSION['username'],
    'Role' => $_SESSION['role']
];

require_once 'session_init.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
        /* Profile Page Specific Styles */
        .profile-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .profile-header {
            margin-bottom: 30px;
        }

        .profile-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #e6f7ed;
            color: #0d8a53;
            border: 1px solid #0d8a53;
        }

        .alert-danger {
            background-color: #feeae9;
            color: #d63d62;
            border: 1px solid #d63d62;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        .profile-sidebar {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            height: fit-content;
        }

        .profile-photo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f8f9fa;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .photo-upload-btn {
            position: absolute;
            bottom: 20px;
            right: 75px;
            background-color: #4361ee;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .photo-upload-btn:hover {
            background-color: #3a56d4;
            transform: scale(1.05);
        }

        .profile-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
            text-align: center;
        }

        .profile-role {
            display: inline-block;
            padding: 5px 15px;
            background-color: #e6f7ed;
            color: #0d8a53;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 15px;
            text-transform: capitalize;
        }

        .profile-id {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 20px;
            text-align: center;
        }

        .profile-quick-info {
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .info-item i {
            width: 20px;
            margin-right: 10px;
            color: #4361ee;
        }

        .info-item span {
            font-size: 0.9rem;
            color: #555;
        }

        .profile-main {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .profile-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .section-action {
            background-color: transparent;
            border: none;
            color: #4361ee;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .section-action:hover {
            color: #3a56d4;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-label {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 0.95rem;
            color: #333;
            font-weight: 500;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #4361ee;
            color: white;
        }

        .btn-primary:hover {
            background-color: #3a56d4;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #ddd;
            color: #666;
        }

        .btn-outline:hover {
            background-color: #f8f9fa;
        }

        .hidden {
            display: none;
        }

        .password-change-link {
            color: #4361ee;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .password-change-link:hover {
            color: #3a56d4;
            text-decoration: underline;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 768px) {
            .info-grid, .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* File input styling */
        .file-input {
            display: none;
        }

        /* Password change modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }

        .modal-overlay.active .modal {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #888;
        }

        .error-message {
            color: #ef476f;
            font-size: 0.8rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="profile-container">
            <div class="profile-header">
                <h1>My Profile</h1>
                <p>Manage your personal information and account settings</p>
            </div>
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-content">
                <!-- Profile Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-photo-container">
                        <img src="<?php echo $user['Photo'] ? 'data:image/jpeg;base64,' . $user['Photo'] : 'https://via.placeholder.com/150' ?>" alt="Profile Photo" class="profile-photo" id="profilePhoto">
                        <label for="photoUpload" class="photo-upload-btn" title="Upload new photo">
                            <i class="fas fa-camera"></i>
                        </label>
                        <form id="photoForm" method="post" enctype="multipart/form-data" style="display: none;">
                            <input type="file" id="photoUpload" name="photo" class="file-input" accept="image/*" onchange="document.getElementById('photoForm').submit();">
                            <input type="hidden" name="update_photo" value="1">
                        </form>
                    </div>
                    
                    <h2 class="profile-name"><?php echo $user['FirstName'] . ' ' . $user['MiddleInitial'] . '. ' . $user['LastName']; ?></h2>
                    <div style="text-align: center;">
                        <span class="profile-role"><?php echo $user['Role']; ?></span>
                    </div>
                    <p class="profile-id">ID: <?php echo $user['IDNumber']; ?></p>
                    
                    <div class="profile-quick-info">
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo $user['Email']; ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo $user['PhoneNumber']; ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo $user['AddressDetails']; ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-birthday-cake"></i>
                            <span><?php echo date('F j, Y', strtotime($user['Birthday'])); ?> (<?php echo calculateAge($user['Birthday']); ?> years)</span>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Main Content -->
                <div class="profile-main">
                    <!-- Personal Information Section -->
                    <div class="profile-section" id="personalInfoSection">
                        <div class="section-header">
                            <h3 class="section-title">Personal Information</h3>
                            <button class="section-action" id="editPersonalInfoBtn">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                        
                        <!-- View Mode -->
                        <div class="info-grid" id="personalInfoView">
                            <div class="info-group">
                                <div class="info-label">First Name</div>
                                <div class="info-value"><?php echo $user['FirstName']; ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Middle Initial</div>
                                <div class="info-value"><?php echo $user['MiddleInitial']; ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Last Name</div>
                                <div class="info-value"><?php echo $user['LastName']; ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Gender</div>
                                <div class="info-value"><?php echo $user['Gender']; ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Birthday</div>
                                <div class="info-value"><?php echo date('F j, Y', strtotime($user['Birthday'])); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">ID Number</div>
                                <div class="info-value"><?php echo $user['IDNumber']; ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Address</div>
                                <div class="info-value"><?php echo $user['AddressDetails']; ?></div>
                            </div>
                        </div>
                        
                        <!-- Edit Mode -->
                        <form id="personalInfoForm" class="hidden" method="post">
                            <input type="hidden" name="update_personal" value="1">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="firstName" value="<?php echo $user['FirstName']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="middleInitial">Middle Initial</label>
                                    <input type="text" id="middleInitial" name="middleInitial" value="<?php echo $user['MiddleInitial']; ?>" maxlength="1">
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" value="<?php echo $user['LastName']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select id="gender" name="gender" required>
                                        <option value="Male" <?php echo $user['Gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $user['Gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="birthday">Birthday</label>
                                    <input type="date" id="birthday" name="birthday" value="<?php echo $user['Birthday']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="idNumber">ID Number</label>
                                    <input type="text" id="idNumber" name="idNumber" value="<?php echo $user['IDNumber']; ?>" readonly>
                                    <small style="color: #888;">ID Number cannot be changed</small>
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label for="address">Address</label>
                                    <input type="text" id="address" name="address" value="<?php echo $user['AddressDetails']; ?>">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" id="cancelPersonalInfoBtn">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Contact Information Section -->
                    <div class="profile-section" id="contactInfoSection">
                        <div class="section-header">
                            <h3 class="section-title">Contact Information</h3>
                            <button class="section-action" id="editContactInfoBtn">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                        
                        <!-- View Mode -->
                        <div class="info-grid" id="contactInfoView">
                            <div class="info-group">
                                <div class="info-label">Email Address</div>
                                <div class="info-value"><?php echo $user['Email']; ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Phone Number</div>
                                <div class="info-value"><?php echo $user['PhoneNumber']; ?></div>
                            </div>
                        </div>
                        
                        <!-- Edit Mode -->
                        <form id="contactInfoForm" class="hidden" method="post">
                            <input type="hidden" name="update_contact" value="1">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" value="<?php echo $user['Email']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phoneNumber">Phone Number</label>
                                    <input type="tel" id="phoneNumber" name="phoneNumber" value="<?php echo $user['PhoneNumber']; ?>" pattern="[0-9]{11}" maxlength="11"> 
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" id="cancelContactInfoBtn">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Account Settings Section -->
                    <div class="profile-section">
                        <div class="section-header">
                            <h3 class="section-title">Account Settings</h3>
                            <button class="section-action" id="editAccountSettingsBtn">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                        
                        <!-- View Mode -->
                        <div class="info-grid" id="accountSettingsView">
                            <div class="info-group">
                                <div class="info-label">Username</div>
                                <div class="info-value"><?php echo $user['Username']; ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Password</div>
                                <div class="info-value">
                                    ••••••••••
                                    <a href="settings.php" class="password-change-link" id="changePasswordBtn">
                                        <i class="fas fa-key"></i> Change Password
                                    </a>
                                </div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Role</div>
                                <div class="info-value"><?php echo ucfirst($user['Role']); ?></div>
                            </div>
                        </div>
                        
                        <!-- Edit Mode -->
                        <form id="accountSettingsForm" class="hidden" method="post">
                            <input type="hidden" name="update_account" value="1">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" value="<?php echo $user['Username']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <input type="text" id="role" name="role" value="<?php echo ucfirst($user['Role']); ?>" readonly>
                                    <small style="color: #888;">Role cannot be changed</small>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" id="cancelAccountSettingsBtn">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Password Change Modal -->
        <div class="modal-overlay" id="passwordModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Change Password</h2>
                    <button class="modal-close" id="closePasswordModal">&times;</button>
                </div>
                <form id="passwordForm" method="post">
                    <input type="hidden" name="update_password" value="1">
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" id="currentPassword" name="currentPassword" required>
                        <div class="error-message" id="currentPasswordError"></div>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="newPassword" maxlength="11"required>
                        <div class="error-message" id="newPasswordError"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" maxlength="11"required>
                        <div class="error-message" id="confirmPasswordError"></div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelPasswordBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Personal Information Section
            const editPersonalInfoBtn = document.getElementById('editPersonalInfoBtn');
            const personalInfoView = document.getElementById('personalInfoView');
            const personalInfoForm = document.getElementById('personalInfoForm');
            const cancelPersonalInfoBtn = document.getElementById('cancelPersonalInfoBtn');
            
            editPersonalInfoBtn.addEventListener('click', function() {
                personalInfoView.classList.add('hidden');
                personalInfoForm.classList.remove('hidden');
            });
            
            cancelPersonalInfoBtn.addEventListener('click', function() {
                personalInfoView.classList.remove('hidden');
                personalInfoForm.classList.add('hidden');
            });
            
            // Contact Information Section
            const editContactInfoBtn = document.getElementById('editContactInfoBtn');
            const contactInfoView = document.getElementById('contactInfoView');
            const contactInfoForm = document.getElementById('contactInfoForm');
            const cancelContactInfoBtn = document.getElementById('cancelContactInfoBtn');
            
            editContactInfoBtn.addEventListener('click', function() {
                contactInfoView.classList.add('hidden');
                contactInfoForm.classList.remove('hidden');
            });
            
            cancelContactInfoBtn.addEventListener('click', function() {
                contactInfoView.classList.remove('hidden');
                contactInfoForm.classList.add('hidden');
            });
            
            // Account Settings Section
            const editAccountSettingsBtn = document.getElementById('editAccountSettingsBtn');
            const accountSettingsView = document.getElementById('accountSettingsView');
            const accountSettingsForm = document.getElementById('accountSettingsForm');
            const cancelAccountSettingsBtn = document.getElementById('cancelAccountSettingsBtn');
            
            editAccountSettingsBtn.addEventListener('click', function() {
                accountSettingsView.classList.add('hidden');
                accountSettingsForm.classList.remove('hidden');
            });
            
            cancelAccountSettingsBtn.addEventListener('click', function() {
                accountSettingsView.classList.remove('hidden');
                accountSettingsForm.classList.add('hidden');
            });
            
            // Password Change Modal
            const changePasswordBtn = document.getElementById('changePasswordBtn');
            const passwordModal = document.getElementById('passwordModal');
            const closePasswordModal = document.getElementById('closePasswordModal');
            const cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
            
            changePasswordBtn.addEventListener('click', function(e) {
                e.preventDefault();
                passwordModal.classList.add('active');
            });
            
            closePasswordModal.addEventListener('click', function() {
                passwordModal.classList.remove('active');
            });
            
            cancelPasswordBtn.addEventListener('click', function() {
                passwordModal.classList.remove('active');
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(function() {
                    alerts.forEach(function(alert) {
                        alert.style.display = 'none';
                    });
                }, 5000);
            }
            
            // Helper function to calculate age
            function calculateAge(birthdate) {
                const today = new Date();
                const birthDate = new Date(birthdate);
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                return age;
            }
        });
    </script>
</body>
</html>