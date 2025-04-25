<?php
// Start session if not already started
if (!isset($_SESSION) && !headers_sent()) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Check if user has permission (admin or registrar)
$allowedRoles = ['admin', 'registrar'];
if (!in_array($_SESSION['role'], $allowedRoles)) {
    header("Location: dashboard.php");
    exit();
}

// Database connection function
include '../config.php';

// Initialize variables
$successMessage = "";
$errorMessage = "";
$userId = $_SESSION['user_id'] ?? 0;
$isAdmin = ($_SESSION['role'] === 'admin');

// Handle program head operations (add, edit, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = connectDB();
    
    // Add new program head
    if (isset($_POST['add_program_head'])) {
        // Get user information
        $idNumber = $conn->real_escape_string($_POST['id_number']);
        $firstName = $conn->real_escape_string($_POST['first_name']);
        $middleInitial = $conn->real_escape_string($_POST['middle_initial']);
        $lastName = $conn->real_escape_string($_POST['last_name']);
        $birthday = $conn->real_escape_string($_POST['birthday']);
        $email = $conn->real_escape_string($_POST['email']);
        $address = $conn->real_escape_string($_POST['address']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        $role = 'program head'; // Set role as 'program head'
        
        // Handle photo upload
        $photoData = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        }
        
        // Check if username or ID number already exists
        $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE Username = ? OR IDNumber = ?");
        $checkStmt->bind_param("ss", $username, $idNumber);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Username or ID Number already exists. Please use different credentials.";
        } else {
            // Insert into Users table
            $stmt = $conn->prepare("INSERT INTO Users (IDNumber, FirstName, MiddleInitial, LastName, Birthday, Email, AddressDetails, PhoneNumber, Gender, Photo, Username, Password, Role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssssss", $idNumber, $firstName, $middleInitial, $lastName, $birthday, $email, $address, $phone, $gender, $photoData, $username, $password, $role);
            
            if ($stmt->execute()) {
                $successMessage = "Program head added successfully!";
            } else {
                $errorMessage = "Error adding program head: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Edit program head
    if (isset($_POST['edit_program_head'])) {
        $userId = intval($_POST['user_id']);
        
        // Get user information
        $idNumber = $conn->real_escape_string($_POST['id_number']);
        $firstName = $conn->real_escape_string($_POST['first_name']);
        $middleInitial = $conn->real_escape_string($_POST['middle_initial']);
        $lastName = $conn->real_escape_string($_POST['last_name']);
        $birthday = $conn->real_escape_string($_POST['birthday']);
        $email = $conn->real_escape_string($_POST['email']);
        $address = $conn->real_escape_string($_POST['address']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $username = $conn->real_escape_string($_POST['username']);
        
        // Handle photo upload
        $photoData = null;
        $photoUpdated = false;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
            $photoUpdated = true;
        }
        
        // Check if username or ID number already exists for other users
        $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE (Username = ? OR IDNumber = ?) AND UserID != ?");
        $checkStmt->bind_param("ssi", $username, $idNumber, $userId);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Username or ID Number already exists. Please use different credentials.";
        } else {
            // Update Users table
            if (empty($_POST['password'])) {
                if ($photoUpdated) {
                    // Update with new photo but without changing password
                    $stmt = $conn->prepare("UPDATE Users SET IDNumber = ?, FirstName = ?, MiddleInitial = ?, LastName = ?, Birthday = ?, Email = ?, AddressDetails = ?, PhoneNumber = ?, Gender = ?, Photo = ?, Username = ? WHERE UserID = ?");
                    $stmt->bind_param("sssssssssssi", $idNumber, $firstName, $middleInitial, $lastName, $birthday, $email, $address, $phone, $gender, $photoData, $username, $userId);
                } else {
                    // Update without changing photo or password
                    $stmt = $conn->prepare("UPDATE Users SET IDNumber = ?, FirstName = ?, MiddleInitial = ?, LastName = ?, Birthday = ?, Email = ?, AddressDetails = ?, PhoneNumber = ?, Gender = ?, Username = ? WHERE UserID = ?");
                    $stmt->bind_param("ssssssssssi", $idNumber, $firstName, $middleInitial, $lastName, $birthday, $email, $address, $phone, $gender, $username, $userId);
                }
            } else {
                // Update with new password
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                if ($photoUpdated) {
                    // Update with new photo and password
                    $stmt = $conn->prepare("UPDATE Users SET IDNumber = ?, FirstName = ?, MiddleInitial = ?, LastName = ?, Birthday = ?, Email = ?, AddressDetails = ?, PhoneNumber = ?, Gender = ?, Photo = ?, Username = ?, Password = ? WHERE UserID = ?");
                    $stmt->bind_param("ssssssssssssi", $idNumber, $firstName, $middleInitial, $lastName, $birthday, $email, $address, $phone, $gender, $photoData, $username, $password, $userId);
                } else {
                    // Update with new password but without changing photo
                    $stmt = $conn->prepare("UPDATE Users SET IDNumber = ?, FirstName = ?, MiddleInitial = ?, LastName = ?, Birthday = ?, Email = ?, AddressDetails = ?, PhoneNumber = ?, Gender = ?, Username = ?, Password = ? WHERE UserID = ?");
                    $stmt->bind_param("sssssssssssi", $idNumber, $firstName, $middleInitial, $lastName, $birthday, $email, $address, $phone, $gender, $username, $password, $userId);
                }
            }
            
            if ($stmt->execute()) {
                $successMessage = "Program head updated successfully!";
            } else {
                $errorMessage = "Error updating program head: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Delete program head
    if (isset($_POST['delete_program_head'])) {
        $userId = intval($_POST['user_id']);
        
        $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ? AND Role = 'program head'");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $successMessage = "Program head deleted successfully!";
        } else {
            $errorMessage = "Error deleting program head: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    $conn->close();
}

// Get program heads with filtering and pagination
function getProgramHeads($search = '', $page = 1, $perPage = 10) {
    $conn = connectDB();
    $programHeads = [];
    $totalProgramHeads = 0;
    
    $offset = ($page - 1) * $perPage;
    
    // Build the query
    $query = "SELECT UserID, IDNumber, FirstName, MiddleInitial, LastName, 
                     Birthday, Email, AddressDetails, PhoneNumber, Gender, Username, 
                     CASE WHEN Photo IS NOT NULL THEN 1 ELSE 0 END AS HasPhoto 
              FROM Users
              WHERE Role = 'program head'";
    
    $countQuery = "SELECT COUNT(*) as total 
                   FROM Users
                   WHERE Role = 'program head'";
    
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $query .= " AND (IDNumber LIKE ? OR FirstName LIKE ? OR LastName LIKE ? OR Email LIKE ? OR Username LIKE ?)";
        $countQuery .= " AND (IDNumber LIKE ? OR FirstName LIKE ? OR LastName LIKE ? OR Email LIKE ? OR Username LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sssss";
    }
    
    $query .= " ORDER BY LastName, FirstName LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $perPage;
    $types .= "ii";
    
    // Get total count
    $countStmt = $conn->prepare($countQuery);
    if (!empty($types)) {
        $countBindTypes = substr($types, 0, -2); // Remove the 'ii' for LIMIT parameters
        if (!empty($countBindTypes)) {
            $countStmt->bind_param($countBindTypes, ...array_slice($params, 0, -2));
        }
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalProgramHeads = $countResult->fetch_assoc()['total'];
    $countStmt->close();
    
    // Get program heads
    $stmt = $conn->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $programHeads[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return [
        'program_heads' => $programHeads,
        'total' => $totalProgramHeads
    ];
}

// Process filters and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Get program heads
$programHeadData = getProgramHeads($search, $page, $perPage);
$programHeads = $programHeadData['program_heads'];
$totalProgramHeads = $programHeadData['total'];
$totalPages = ceil($totalProgramHeads / $perPage);

// Handle photo request
if (isset($_GET['get_photo']) && isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    $photo = getFacultyPhoto($userId);
    
    if ($photo !== null) {
        header('Content-Type: image/jpeg');
        echo base64_decode($photo);
        exit();
    } else {
        // Return a 404 if no photo is found
        header("HTTP/1.0 404 Not Found");
        exit();
    }
}

require_once 'session_init.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Head Management - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
        /* Program Head Management Page Specific Styles */
        .program-head-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .program-head-header {
            margin-bottom: 30px;
        }

        .program-head-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .program-head-header p {
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

        .actions-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            min-width: 250px;
        }

        .search-input:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
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
            background-color: #0a2342;
            color: white;
        }

        .btn-primary:hover {
            background-color: #153e6f;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #ddd;
            color: #666;
        }

        .btn-outline:hover {
            background-color: #f8f9fa;
        }

        .btn-danger {
            background-color: #d63d62;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0365a;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .program-head-table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .program-head-table {
            width: 100%;
            border-collapse: collapse;
        }

        .program-head-table th,
        .program-head-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .program-head-table th {
            background-color: #f1f3f9;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }

        .program-head-table tr:last-child td {
            border-bottom: none;
        }

        .program-head-table tr:hover {
            background-color: #f9f9f9;
        }

        .program-head-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .edit-btn {
            background-color: #0a2342;
            color: white;
        }

        .edit-btn:hover {
            background-color: #153e6f;
        }

        .delete-btn {
            background-color: #d63d62;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c0365a;
        }

        .view-btn {
            background-color: #0d8a53;
            color: white;
        }

        .view-btn:hover {
            background-color: #0b7a49;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .pagination-item {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination-item:hover {
            background-color: #f8f9fa;
        }

        .pagination-item.active {
            background-color: #4361ee;
            color: white;
            border-color: #4361ee;
        }

        .pagination-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

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
            max-width: 700px;
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

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .error-message {
            color: #ef476f;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .no-program-head {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        .no-program-head i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .no-program-head p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .no-program-head span {
            font-size: 0.9rem;
        }

        .program-head-details {
            margin-bottom: 20px;
        }

        .program-head-details-section {
            margin-bottom: 20px;
        }

        .program-head-details-section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .program-head-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .program-head-info-item {
            margin-bottom: 10px;
        }

        .program-head-info-label {
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .program-head-info-value {
            color: #333;
            font-size: 1rem;
        }

        /* Photo styles */
        .program-head-photo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .program-head-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #4361ee;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .program-head-photo-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #f1f3f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #aaa;
            border: 3px solid #ddd;
        }

        .photo-upload-container {
            margin-bottom: 20px;
        }

        .photo-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 10px;
            border: 2px solid #4361ee;
            display: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .actions-container {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }
            
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-input {
                min-width: auto;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .program-head-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="program-head-container">
            <div class="program-head-header">
                <h1>Program Head Management</h1>
                <p>View, add, and manage program heads</p>
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
            
            <div class="actions-container">
                <form class="search-form" method="get" action="programhead.php">
                    <input type="text" name="search" class="search-input" placeholder="Search program heads..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline btn-icon">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
                
                <button id="addProgramHeadBtn" class="btn btn-primary btn-icon">
                    <i class="fas fa-plus"></i> Add New Program Head
                </button>
            </div>
            
            <div class="program-head-table-container">
                <?php if (empty($programHeads)): ?>
                    <div class="no-program-head">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>No program heads found</p>
                        <span>Try adjusting your search criteria or add a new program head.</span>
                    </div>
                <?php else: ?>
                    <table class="program-head-table">
                        <thead>
                            <tr>
                                <th>ID Number</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($programHeads as $member): ?>
                                <tr>
                                    <td><?php echo $member['IDNumber']; ?></td>
                                    <td><?php echo $member['FirstName'] . ' ' . $member['MiddleInitial'] . '. ' . $member['LastName']; ?></td>
                                    <td><?php echo $member['Email']; ?></td>
                                    <td><?php echo $member['PhoneNumber'] ?: 'Not provided'; ?></td>
                                    <td><?php echo $member['Gender']; ?></td>
                                    <td class="program-head-actions">
                                        <button class="action-btn view-btn view-program-head-btn" 
                                            data-userid="<?php echo $member['UserID']; ?>"
                                            data-idnumber="<?php echo $member['IDNumber']; ?>"
                                            data-firstname="<?php echo $member['FirstName']; ?>"
                                            data-middleinitial="<?php echo $member['MiddleInitial']; ?>"
                                            data-lastname="<?php echo $member['LastName']; ?>"
                                            data-birthday="<?php echo $member['Birthday']; ?>"
                                            data-email="<?php echo $member['Email']; ?>"
                                            data-address="<?php echo $member['AddressDetails']; ?>"
                                            data-phone="<?php echo $member['PhoneNumber']; ?>"
                                            data-gender="<?php echo $member['Gender']; ?>"
                                            data-username="<?php echo $member['Username']; ?>"
                                            data-hasphoto="<?php echo $member['HasPhoto']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn edit-btn edit-program-head-btn" 
                                            data-userid="<?php echo $member['UserID']; ?>"
                                            data-idnumber="<?php echo $member['IDNumber']; ?>"
                                            data-firstname="<?php echo $member['FirstName']; ?>"
                                            data-middleinitial="<?php echo $member['MiddleInitial']; ?>"
                                            data-lastname="<?php echo $member['LastName']; ?>"
                                            data-birthday="<?php echo $member['Birthday']; ?>"
                                            data-email="<?php echo $member['Email']; ?>"
                                            data-address="<?php echo $member['AddressDetails']; ?>"
                                            data-phone="<?php echo $member['PhoneNumber']; ?>"
                                            data-gender="<?php echo $member['Gender']; ?>"
                                            data-username="<?php echo $member['Username']; ?>"
                                            data-hasphoto="<?php echo $member['HasPhoto']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn delete-program-head-btn" 
                                            data-userid="<?php echo $member['UserID']; ?>"
                                            data-name="<?php echo $member['FirstName'] . ' ' . $member['LastName']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <a href="?search=<?php echo urlencode($search); ?>&page=1" class="pagination-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" class="pagination-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>" class="pagination-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" class="pagination-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $totalPages; ?>" class="pagination-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- View Program Head Modal -->
        <div class="modal-overlay" id="viewProgramHeadModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Program Head Details</h2>
                    <button class="modal-close" id="closeViewProgramHeadModal">&times;</button>
                </div>
                <div class="program-head-details">
                    <!-- Program Head Photo -->
                    <div class="program-head-photo-container">
                        <div id="view-photo-placeholder" class="program-head-photo-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                        <img id="view-photo" class="program-head-photo" style="display: none;" alt="Program Head Photo">
                    </div>
                    
                    <div class="program-head-details-section">
                        <h3>Personal Information</h3>
                        <div class="program-head-info-grid">
                            <div class="program-head-info-item">
                                <div class="program-head-info-label">ID Number</div>
                                <div class="program-head-info-value" id="view-id-number"></div>
                            </div>
                            <div class="program-head-info-item">
                                <div class="program-head-info-label">Full Name</div>
                                <div class="program-head-info-value" id="view-full-name"></div>
                            </div>
                            <div class="program-head-info-item">
                                <div class="program-head-info-label">Birthday</div>
                                <div class="program-head-info-value" id="view-birthday"></div>
                            </div>
                            <div class="program-head-info-item">
                                <div class="program-head-info-label">Gender</div>
                                <div class="program-head-info-value" id="view-gender"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="program-head-details-section">
                        <h3>Contact Information</h3>
                        <div class="program-head-info-grid">
                            <div class="program-head-info-item">
                                <div class="program-head-info-label">Email</div>
                                <div class="program-head-info-value" id="view-email"></div>
                            </div>
                            <div class="program-head-info-item">
                                <div class="program-head-info-label">Phone Number</div>
                                <div class="program-head-info-value" id="view-phone"></div>
                            </div>
                            <div class="program-head-info-item">
                                <div class="program-head-info-label">Address</div>
                                <div class="program-head-info-value" id="view-address"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="program-head-details-section">
                        <h3>Account Information</h3>
                        <div class="program-head-info-grid">
                            <div class="program-head-info-item">
                                <div class="program-head-info-label">Username</div>
                                <div class="program-head-info-value" id="view-username"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="closeViewProgramHeadBtn">Close</button>
                    <button type="button" class="btn btn-primary" id="viewToEditBtn">Edit</button>
                </div>
            </div>
        </div>
        
        <!-- Add Program Head Modal -->
        <div class="modal-overlay" id="addProgramHeadModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Add New Program Head</h2>
                    <button class="modal-close" id="closeAddProgramHeadModal">&times;</button>
                </div>
                <form id="addProgramHeadForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="add_program_head" value="1">
                    
                    <!-- Photo Upload -->
                    <div class="photo-upload-container">
                        <div class="form-group">
                            <label for="photo">Profile Photo</label>
                            <input type="file" id="photo" name="photo" accept="image/*" onchange="previewImage(this, 'add-photo-preview')">
                        </div>
                        <img id="add-photo-preview" class="photo-preview" alt="Photo Preview">
                    </div>
                    
                    <div class="program-head-details-section">
                        <h3>Personal Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="id_number">ID Number</label>
                                <input type="text" id="id_number" name="id_number" required>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="middle_initial">Middle Initial</label>
                                <input type="text" id="middle_initial" name="middle_initial" maxlength="1">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="birthday">Birthday</label>
                            <input type="date" id="birthday" name="birthday" required>
                        </div>
                    </div>
                    
                    <div class="program-head-details-section">
                        <h3>Contact Information</h3>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" maxlength="11">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address">
                        </div>
                    </div>
                    
                    <div class="program-head-details-section">
                        <h3>Account Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelAddProgramHeadBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Program Head</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Edit Program Head Modal -->
        <div class="modal-overlay" id="editProgramHeadModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Program Head</h2>
                    <button class="modal-close" id="closeEditProgramHeadModal">&times;</button>
                </div>
                <form id="editProgramHeadForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="edit_program_head" value="1">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <!-- Photo Upload -->
                    <div class="photo-upload-container">
                        <div class="form-group">
                            <label for="edit_photo">Profile Photo</label>
                            <input type="file" id="edit_photo" name="photo" accept="image/*" onchange="previewImage(this, 'edit-photo-preview')">
                        </div>
                        <img id="edit-photo-preview" class="photo-preview" alt="Photo Preview">
                    </div>
                    
                    <div class="program-head-details-section">
                        <h3>Personal Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_id_number">ID Number</label>
                                <input type="text" id="edit_id_number" name="id_number" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_gender">Gender</label>
                                <select id="edit_gender" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_first_name">First Name</label>
                                <input type="text" id="edit_first_name" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_middle_initial">Middle Initial</label>
                                <input type="text" id="edit_middle_initial" name="middle_initial" maxlength="1">
                            </div>
                            <div class="form-group">
                                <label for="edit_last_name">Last Name</label>
                                <input type="text" id="edit_last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_birthday">Birthday</label>
                            <input type="date" id="edit_birthday" name="birthday" required>
                        </div>
                    </div>
                    
                    <div class="program-head-details-section">
                        <h3>Contact Information</h3>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" id="edit_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_phone">Phone Number</label>
                            <input type="text" id="edit_phone" name="phone" maxlength="11">
                        </div>
                        <div class="form-group">
                            <label for="edit_address">Address</label>
                            <input type="text" id="edit_address" name="address">
                        </div>
                    </div>
                    
                    <div class="program-head-details-section">
                        <h3>Account Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_username">Username</label>
                                <input type="text" id="edit_username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_password">Password (leave blank to keep current)</label>
                                <input type="password" id="edit_password" name="password">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelEditProgramHeadBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Program Head</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Delete Program Head Modal -->
        <div class="modal-overlay" id="deleteProgramHeadModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Program Head</h2>
                    <button class="modal-close" id="closeDeleteProgramHeadModal">&times;</button>
                </div>
                <form id="deleteProgramHeadForm" method="post">
                    <input type="hidden" name="delete_program_head" value="1">
                    <input type="hidden" id="delete_user_id" name="user_id">
                    <p>Are you sure you want to delete the program head <strong id="delete_program_head_name"></strong>?</p>
                    <p>This action cannot be undone and will remove all associated records.</p>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelDeleteProgramHeadBtn">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Program Head</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View Program Head Modal
            const viewProgramHeadBtns = document.querySelectorAll('.view-program-head-btn');
            const viewProgramHeadModal = document.getElementById('viewProgramHeadModal');
            const closeViewProgramHeadModal = document.getElementById('closeViewProgramHeadModal');
            const closeViewProgramHeadBtn = document.getElementById('closeViewProgramHeadBtn');
            const viewToEditBtn = document.getElementById('viewToEditBtn');
            
            viewProgramHeadBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-userid');
                    const idNumber = this.getAttribute('data-idnumber');
                    const firstName = this.getAttribute('data-firstname');
                    const middleInitial = this.getAttribute('data-middleinitial');
                    const lastName = this.getAttribute('data-lastname');
                    const birthday = this.getAttribute('data-birthday');
                    const email = this.getAttribute('data-email');
                    const address = this.getAttribute('data-address');
                    const phone = this.getAttribute('data-phone');
                    const gender = this.getAttribute('data-gender');
                    const username = this.getAttribute('data-username');
                    const hasPhoto = this.getAttribute('data-hasphoto') === '1';
                    
                    // Format birthday
                    const formattedBirthday = new Date(birthday).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    
                    // Populate view modal
                    document.getElementById('view-id-number').textContent = idNumber;
                    document.getElementById('view-full-name').textContent = `${firstName} ${middleInitial}. ${lastName}`;
                    document.getElementById('view-birthday').textContent = formattedBirthday;
                    document.getElementById('view-gender').textContent = gender;
                    document.getElementById('view-email').textContent = email || 'Not provided';
                    document.getElementById('view-phone').textContent = phone || 'Not provided';
                    document.getElementById('view-address').textContent = address || 'Not provided';
                    document.getElementById('view-username').textContent = username;
                    
                    // Handle photo display
                    const photoPlaceholder = document.getElementById('view-photo-placeholder');
                    const photoElement = document.getElementById('view-photo');
                    
                    if (hasPhoto) {
                        photoElement.src = `programhead.php?get_photo=1&user_id=${userId}&t=${new Date().getTime()}`; // Add timestamp to prevent caching
                        photoElement.style.display = 'block';
                        photoPlaceholder.style.display = 'none';
                    } else {
                        photoElement.style.display = 'none';
                        photoPlaceholder.style.display = 'flex';
                    }
                    
                    // Store data for edit button
                    viewToEditBtn.setAttribute('data-userid', userId);
                    
                    viewProgramHeadModal.classList.add('active');
                });
            });
            
            if (closeViewProgramHeadModal) {
                closeViewProgramHeadModal.addEventListener('click', function() {
                    viewProgramHeadModal.classList.remove('active');
                });
            }
            
            if (closeViewProgramHeadBtn) {
                closeViewProgramHeadBtn.addEventListener('click', function() {
                    viewProgramHeadModal.classList.remove('active');
                });
            }
            
            // View to Edit
            if (viewToEditBtn) {
                viewToEditBtn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-userid');
                    
                    // Find the edit button with matching data and trigger click
                    const editBtn = document.querySelector(`.edit-program-head-btn[data-userid="${userId}"]`);
                    if (editBtn) {
                        viewProgramHeadModal.classList.remove('active');
                        editBtn.click();
                    }
                });
            }
            
            // Add Program Head Modal
            const addProgramHeadBtn = document.getElementById('addProgramHeadBtn');
            const addProgramHeadModal = document.getElementById('addProgramHeadModal');
            const closeAddProgramHeadModal = document.getElementById('closeAddProgramHeadModal');
            const cancelAddProgramHeadBtn = document.getElementById('cancelAddProgramHeadBtn');
            
            if (addProgramHeadBtn) {
                addProgramHeadBtn.addEventListener('click', function() {
                    addProgramHeadModal.classList.add('active');
                });
            }
            
            if (closeAddProgramHeadModal) {
                closeAddProgramHeadModal.addEventListener('click', function() {
                    addProgramHeadModal.classList.remove('active');
                });
            }
            
            if (cancelAddProgramHeadBtn) {
                cancelAddProgramHeadBtn.addEventListener('click', function() {
                    addProgramHeadModal.classList.remove('active');
                });
            }
            
            // Edit Program Head Modal
            const editProgramHeadBtns = document.querySelectorAll('.edit-program-head-btn');
            const editProgramHeadModal = document.getElementById('editProgramHeadModal');
            const closeEditProgramHeadModal = document.getElementById('closeEditProgramHeadModal');
            const cancelEditProgramHeadBtn = document.getElementById('cancelEditProgramHeadBtn');
            
            editProgramHeadBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-userid');
                    const idNumber = this.getAttribute('data-idnumber');
                    const firstName = this.getAttribute('data-firstname');
                    const middleInitial = this.getAttribute('data-middleinitial');
                    const lastName = this.getAttribute('data-lastname');
                    const birthday = this.getAttribute('data-birthday');
                    const email = this.getAttribute('data-email');
                    const address = this.getAttribute('data-address');
                    const phone = this.getAttribute('data-phone');
                    const gender = this.getAttribute('data-gender');
                    const username = this.getAttribute('data-username');
                    const hasPhoto = this.getAttribute('data-hasphoto') === '1';
                    
                    // Populate edit form
                    document.getElementById('edit_user_id').value = userId;
                    document.getElementById('edit_id_number').value = idNumber;
                    document.getElementById('edit_first_name').value = firstName;
                    document.getElementById('edit_middle_initial').value = middleInitial;
                    document.getElementById('edit_last_name').value = lastName;
                    document.getElementById('edit_birthday').value = birthday;
                    document.getElementById('edit_email').value = email;
                    document.getElementById('edit_address').value = address;
                    document.getElementById('edit_phone').value = phone;
                    document.getElementById('edit_gender').value = gender;
                    document.getElementById('edit_username').value = username;
                    
                    // Show current photo if exists
                    const photoPreview = document.getElementById('edit-photo-preview');
                    if (hasPhoto) {
                        photoPreview.src = `programhead.php?get_photo=1&user_id=${userId}&t=${new Date().getTime()}`; // Add timestamp to prevent caching
                        photoPreview.style.display = 'block';
                    } else {
                        photoPreview.style.display = 'none';
                    }
                    
                    editProgramHeadModal.classList.add('active');
                });
            });
            
            if (closeEditProgramHeadModal) {
                closeEditProgramHeadModal.addEventListener('click', function() {
                    editProgramHeadModal.classList.remove('active');
                });
            }
            
            if (cancelEditProgramHeadBtn) {
                cancelEditProgramHeadBtn.addEventListener('click', function() {
                    editProgramHeadModal.classList.remove('active');
                });
            }
            
            // Delete Program Head Modal
            const deleteProgramHeadBtns = document.querySelectorAll('.delete-program-head-btn');
            const deleteProgramHeadModal = document.getElementById('deleteProgramHeadModal');
            const closeDeleteProgramHeadModal = document.getElementById('closeDeleteProgramHeadModal');
            const cancelDeleteProgramHeadBtn = document.getElementById('cancelDeleteProgramHeadBtn');
            
            deleteProgramHeadBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-userid');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('delete_user_id').value = userId;
                    document.getElementById('delete_program_head_name').textContent = name;
                    
                    deleteProgramHeadModal.classList.add('active');
                });
            });
            
            if (closeDeleteProgramHeadModal) {
                closeDeleteProgramHeadModal.addEventListener('click', function() {
                    deleteProgramHeadModal.classList.remove('active');
                });
            }
            
            if (cancelDeleteProgramHeadBtn) {
                cancelDeleteProgramHeadBtn.addEventListener('click', function() {
                    deleteProgramHeadModal.classList.remove('active');
                });
            }
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(function() {
                    alerts.forEach(function(alert) {
                        alert.style.display = 'none';
                    });
                }, 5000);
            }
        });
        
        // Function to preview uploaded images
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>