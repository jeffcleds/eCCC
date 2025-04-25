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

// Handle registrar operations (add, edit, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = connectDB();
    
    // Add new registrar
    if (isset($_POST['add_registrar'])) {
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
        $role = 'registrar'; // Set role as 'registrar'
        
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
                $successMessage = "Registrar added successfully!";
            } else {
                $errorMessage = "Error adding registrar: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Edit registrar
    if (isset($_POST['edit_registrar'])) {
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
                $successMessage = "Registrar updated successfully!";
            } else {
                $errorMessage = "Error updating registrar: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Delete registrar
    if (isset($_POST['delete_registrar'])) {
        $userId = intval($_POST['user_id']);
        
        $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ? AND Role = 'registrar'");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $successMessage = "Registrar deleted successfully!";
        } else {
            $errorMessage = "Error deleting registrar: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    $conn->close();
}

// Get registrars with filtering and pagination
function getRegistrars($search = '', $page = 1, $perPage = 10) {
    $conn = connectDB();
    $registrars = [];
    $totalRegistrars = 0;
    
    $offset = ($page - 1) * $perPage;
    
    // Build the query
    $query = "SELECT UserID, IDNumber, FirstName, MiddleInitial, LastName, 
                     Birthday, Email, AddressDetails, PhoneNumber, Gender, Username, 
                     CASE WHEN Photo IS NOT NULL THEN 1 ELSE 0 END AS HasPhoto 
              FROM Users
              WHERE Role = 'registrar'";
    
    $countQuery = "SELECT COUNT(*) as total 
                   FROM Users
                   WHERE Role = 'registrar'";
    
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
    $totalRegistrars = $countResult->fetch_assoc()['total'];
    $countStmt->close();
    
    // Get registrars
    $stmt = $conn->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $registrars[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return [
        'registrars' => $registrars,
        'total' => $totalRegistrars
    ];
}

// Process filters and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Get registrars
$registrarData = getRegistrars($search, $page, $perPage);
$registrars = $registrarData['registrars'];
$totalRegistrars = $registrarData['total'];
$totalPages = ceil($totalRegistrars / $perPage);

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
    <title>Registrar Management - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
        /* Registrar Management Page Specific Styles */
        .registrar-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .registrar-header {
            margin-bottom: 30px;
        }

        .registrar-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .registrar-header p {
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

        .registrar-table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .registrar-table {
            width: 100%;
            border-collapse: collapse;
        }

        .registrar-table th,
        .registrar-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .registrar-table th {
            background-color: #f1f3f9;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }

        .registrar-table tr:last-child td {
            border-bottom: none;
        }

        .registrar-table tr:hover {
            background-color: #f9f9f9;
        }

        .registrar-actions {
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

        .no-registrar {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        .no-registrar i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .no-registrar p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .no-registrar span {
            font-size: 0.9rem;
        }

        .registrar-details {
            margin-bottom: 20px;
        }

        .registrar-details-section {
            margin-bottom: 20px;
        }

        .registrar-details-section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .registrar-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .registrar-info-item {
            margin-bottom: 10px;
        }

        .registrar-info-label {
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .registrar-info-value {
            color: #333;
            font-size: 1rem;
        }

        /* Photo styles */
        .registrar-photo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .registrar-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #4361ee;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .registrar-photo-placeholder {
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
            
            .registrar-info-grid {
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
        
        <div class="registrar-container">
            <div class="registrar-header">
                <h1>Registrar Management</h1>
                <p>View, add, and manage registrars</p>
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
                <form class="search-form" method="get" action="registrar.php">
                    <input type="text" name="search" class="search-input" placeholder="Search registrars..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline btn-icon">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
                
                <button id="addRegistrarBtn" class="btn btn-primary btn-icon">
                    <i class="fas fa-plus"></i> Add New Registrar
                </button>
            </div>
            
            <div class="registrar-table-container">
                <?php if (empty($registrars)): ?>
                    <div class="no-registrar">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>No registrars found</p>
                        <span>Try adjusting your search criteria or add a new registrar.</span>
                    </div>
                <?php else: ?>
                    <table class="registrar-table">
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
                            <?php foreach ($registrars as $member): ?>
                                <tr>
                                    <td><?php echo $member['IDNumber']; ?></td>
                                    <td><?php echo $member['FirstName'] . ' ' . $member['MiddleInitial'] . '. ' . $member['LastName']; ?></td>
                                    <td><?php echo $member['Email']; ?></td>
                                    <td><?php echo $member['PhoneNumber'] ?: 'Not provided'; ?></td>
                                    <td><?php echo $member['Gender']; ?></td>
                                    <td class="registrar-actions">
                                        <button class="action-btn view-btn view-registrar-btn" 
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
                                        <button class="action-btn edit-btn edit-registrar-btn" 
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
                                        <button class="action-btn delete-btn delete-registrar-btn" 
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
        
        <!-- View Registrar Modal -->
        <div class="modal-overlay" id="viewRegistrarModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Registrar Details</h2>
                    <button class="modal-close" id="closeViewRegistrarModal">&times;</button>
                </div>
                <div class="registrar-details">
                    <!-- Registrar Photo -->
                    <div class="registrar-photo-container">
                        <div id="view-photo-placeholder" class="registrar-photo-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                        <img id="view-photo" class="registrar-photo" style="display: none;" alt="Registrar Photo">
                    </div>
                    
                    <div class="registrar-details-section">
                        <h3>Personal Information</h3>
                        <div class="registrar-info-grid">
                            <div class="registrar-info-item">
                            <div class="registrar-info-label">ID Number</div>
                                <div class="registrar-info-value" id="view-id-number"></div>
                            </div>
                            <div class="registrar-info-item">
                                <div class="registrar-info-label">Full Name</div>
                                <div class="registrar-info-value" id="view-full-name"></div>
                            </div>
                            <div class="registrar-info-item">
                                <div class="registrar-info-label">Birthday</div>
                                <div class="registrar-info-value" id="view-birthday"></div>
                            </div>
                            <div class="registrar-info-item">
                                <div class="registrar-info-label">Gender</div>
                                <div class="registrar-info-value" id="view-gender"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="registrar-details-section">
                        <h3>Contact Information</h3>
                        <div class="registrar-info-grid">
                            <div class="registrar-info-item">
                                <div class="registrar-info-label">Email</div>
                                <div class="registrar-info-value" id="view-email"></div>
                            </div>
                            <div class="registrar-info-item">
                                <div class="registrar-info-label">Phone Number</div>
                                <div class="registrar-info-value" id="view-phone"></div>
                            </div>
                            <div class="registrar-info-item">
                                <div class="registrar-info-label">Address</div>
                                <div class="registrar-info-value" id="view-address"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="registrar-details-section">
                        <h3>Account Information</h3>
                        <div class="registrar-info-grid">
                            <div class="registrar-info-item">
                                <div class="registrar-info-label">Username</div>
                                <div class="registrar-info-value" id="view-username"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="closeViewRegistrarBtn">Close</button>
                    <button type="button" class="btn btn-primary" id="viewToEditBtn">Edit</button>
                </div>
            </div>
        </div>
        
        <!-- Add Registrar Modal -->
        <div class="modal-overlay" id="addRegistrarModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Add New Registrar</h2>
                    <button class="modal-close" id="closeAddRegistrarModal">&times;</button>
                </div>
                <form id="addRegistrarForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="add_registrar" value="1">
                    
                    <!-- Photo Upload -->
                    <div class="photo-upload-container">
                        <div class="form-group">
                            <label for="photo">Profile Photo</label>
                            <input type="file" id="photo" name="photo" accept="image/*" onchange="previewImage(this, 'add-photo-preview')">
                        </div>
                        <img id="add-photo-preview" class="photo-preview" alt="Photo Preview">
                    </div>
                    
                    <div class="registrar-details-section">
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
                    
                    <div class="registrar-details-section">
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
                    
                    <div class="registrar-details-section">
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
                        <button type="button" class="btn btn-outline" id="cancelAddRegistrarBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Registrar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Edit Registrar Modal -->
        <div class="modal-overlay" id="editRegistrarModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Registrar</h2>
                    <button class="modal-close" id="closeEditRegistrarModal">&times;</button>
                </div>
                <form id="editRegistrarForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="edit_registrar" value="1">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <!-- Photo Upload -->
                    <div class="photo-upload-container">
                        <div class="form-group">
                            <label for="edit_photo">Profile Photo</label>
                            <input type="file" id="edit_photo" name="photo" accept="image/*" onchange="previewImage(this, 'edit-photo-preview')">
                        </div>
                        <img id="edit-photo-preview" class="photo-preview" alt="Photo Preview">
                    </div>
                    
                    <div class="registrar-details-section">
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
                    
                    <div class="registrar-details-section">
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
                    
                    <div class="registrar-details-section">
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
                        <button type="button" class="btn btn-outline" id="cancelEditRegistrarBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Registrar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Delete Registrar Modal -->
        <div class="modal-overlay" id="deleteRegistrarModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Registrar</h2>
                    <button class="modal-close" id="closeDeleteRegistrarModal">&times;</button>
                </div>
                <form id="deleteRegistrarForm" method="post">
                    <input type="hidden" name="delete_registrar" value="1">
                    <input type="hidden" id="delete_user_id" name="user_id">
                    <p>Are you sure you want to delete the registrar <strong id="delete_registrar_name"></strong>?</p>
                    <p>This action cannot be undone and will remove all associated records.</p>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelDeleteRegistrarBtn">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View Registrar Modal
            const viewRegistrarBtns = document.querySelectorAll('.view-registrar-btn');
            const viewRegistrarModal = document.getElementById('viewRegistrarModal');
            const closeViewRegistrarModal = document.getElementById('closeViewRegistrarModal');
            const closeViewRegistrarBtn = document.getElementById('closeViewRegistrarBtn');
            const viewToEditBtn = document.getElementById('viewToEditBtn');
            
            viewRegistrarBtns.forEach(btn => {
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
                        photoElement.src = `registrar.php?get_photo=1&user_id=${userId}&t=${new Date().getTime()}`; // Add timestamp to prevent caching
                        photoElement.style.display = 'block';
                        photoPlaceholder.style.display = 'none';
                    } else {
                        photoElement.style.display = 'none';
                        photoPlaceholder.style.display = 'flex';
                    }
                    
                    // Store data for edit button
                    viewToEditBtn.setAttribute('data-userid', userId);
                    
                    viewRegistrarModal.classList.add('active');
                });
            });
            
            if (closeViewRegistrarModal) {
                closeViewRegistrarModal.addEventListener('click', function() {
                    viewRegistrarModal.classList.remove('active');
                });
            }
            
            if (closeViewRegistrarBtn) {
                closeViewRegistrarBtn.addEventListener('click', function() {
                    viewRegistrarModal.classList.remove('active');
                });
            }
            
            // View to Edit
            if (viewToEditBtn) {
                viewToEditBtn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-userid');
                    
                    // Find the edit button with matching data and trigger click
                    const editBtn = document.querySelector(`.edit-registrar-btn[data-userid="${userId}"]`);
                    if (editBtn) {
                        viewRegistrarModal.classList.remove('active');
                        editBtn.click();
                    }
                });
            }
            
            // Add Registrar Modal
            const addRegistrarBtn = document.getElementById('addRegistrarBtn');
            const addRegistrarModal = document.getElementById('addRegistrarModal');
            const closeAddRegistrarModal = document.getElementById('closeAddRegistrarModal');
            const cancelAddRegistrarBtn = document.getElementById('cancelAddRegistrarBtn');
            
            if (addRegistrarBtn) {
                addRegistrarBtn.addEventListener('click', function() {
                    addRegistrarModal.classList.add('active');
                });
            }
            
            if (closeAddRegistrarModal) {
                closeAddRegistrarModal.addEventListener('click', function() {
                    addRegistrarModal.classList.remove('active');
                });
            }
            
            if (cancelAddRegistrarBtn) {
                cancelAddRegistrarBtn.addEventListener('click', function() {
                    addRegistrarModal.classList.remove('active');
                });
            }
            
            // Edit Registrar Modal
            const editRegistrarBtns = document.querySelectorAll('.edit-registrar-btn');
            const editRegistrarModal = document.getElementById('editRegistrarModal');
            const closeEditRegistrarModal = document.getElementById('closeEditRegistrarModal');
            const cancelEditRegistrarBtn = document.getElementById('cancelEditRegistrarBtn');
            
            editRegistrarBtns.forEach(btn => {
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
                        photoPreview.src = `registrar.php?get_photo=1&user_id=${userId}&t=${new Date().getTime()}`; // Add timestamp to prevent caching
                        photoPreview.style.display = 'block';
                    } else {
                        photoPreview.style.display = 'none';
                    }
                    
                    editRegistrarModal.classList.add('active');
                });
            });
            
            if (closeEditRegistrarModal) {
                closeEditRegistrarModal.addEventListener('click', function() {
                    editRegistrarModal.classList.remove('active');
                });
            }
            
            if (cancelEditRegistrarBtn) {
                cancelEditRegistrarBtn.addEventListener('click', function() {
                    editRegistrarModal.classList.remove('active');
                });
            }
            
            // Delete Registrar Modal
            const deleteRegistrarBtns = document.querySelectorAll('.delete-registrar-btn');
            const deleteRegistrarModal = document.getElementById('deleteRegistrarModal');
            const closeDeleteRegistrarModal = document.getElementById('closeDeleteRegistrarModal');
            const cancelDeleteRegistrarBtn = document.getElementById('cancelDeleteRegistrarBtn');
            
            deleteRegistrarBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-userid');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('delete_user_id').value = userId;
                    document.getElementById('delete_registrar_name').textContent = name;
                    
                    deleteRegistrarModal.classList.add('active');
                });
            });
            
            if (closeDeleteRegistrarModal) {
                closeDeleteRegistrarModal.addEventListener('click', function() {
                    deleteRegistrarModal.classList.remove('active');
                });
            }
            
            if (cancelDeleteRegistrarBtn) {
                cancelDeleteRegistrarBtn.addEventListener('click', function() {
                    deleteRegistrarModal.classList.remove('active');
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