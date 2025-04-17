<?php
// Start session if not already started
include 'session_init.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Check if user is admin
$isAdmin = ($_SESSION['role'] === 'admin');
if (!$isAdmin) {
    header("Location: dashboard.php");
    exit();
}

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

// Initialize variables
$successMessage = "";
$errorMessage = "";

// Handle user operations (add, edit, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = connectDB();
    
    // Add new user
    if (isset($_POST['add_user'])) {
        $idNumber = $conn->real_escape_string($_POST['id_number']);
        $firstName = $conn->real_escape_string($_POST['first_name']);
        $middleInitial = $conn->real_escape_string($_POST['middle_initial']);
        $lastName = $conn->real_escape_string($_POST['last_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = $conn->real_escape_string($_POST['password']);
        $role = $conn->real_escape_string($_POST['role']);
        
        
        // Check if username or ID number already exists
        $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE Username = ? OR IDNumber = ?");
        $checkStmt->bind_param("ss", $username, $idNumber);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Username or ID Number already exists. Please use different credentials.";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO Users (IDNumber, FirstName, MiddleInitial, LastName, Email, Username, Password, Role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $idNumber, $firstName, $middleInitial, $lastName, $email, $username, $password, $role);
            
            if ($stmt->execute()) {
                $successMessage = "User added successfully!";
            } else {
                $errorMessage = "Error adding user: " . $conn->error;
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Edit user
    if (isset($_POST['edit_user'])) {
        $userId = intval($_POST['user_id']);
        $idNumber = $conn->real_escape_string($_POST['id_number']);
        $firstName = $conn->real_escape_string($_POST['first_name']);
        $middleInitial = $conn->real_escape_string($_POST['middle_initial']);
        $lastName = $conn->real_escape_string($_POST['last_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $username = $conn->real_escape_string($_POST['username']);
        $role = $conn->real_escape_string($_POST['role']);
     
        
        // Check if username or ID number already exists for other users
        $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE (Username = ? OR IDNumber = ?) AND UserID != ?");
        $checkStmt->bind_param("ssi", $username, $idNumber, $userId);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Username or ID Number already exists. Please use different credentials.";
        } else {
            // Update user without changing password
            if (empty($_POST['password'])) {
                $stmt = $conn->prepare("UPDATE Users SET IDNumber = ?, FirstName = ?, MiddleInitial = ?, LastName = ?, Email = ?, Username = ?, Role = ? WHERE UserID = ?");
                $stmt->bind_param("sssssssi", $idNumber, $firstName, $middleInitial, $lastName, $email, $username, $role, $userId);
            } else {
                // Update user with new password
                $conn->real_escape_string($_POST['password']);
                $stmt = $conn->prepare("UPDATE Users SET IDNumber = ?, FirstName = ?, MiddleInitial = ?, LastName = ?, Email = ?, Username = ?, Password = ?, Role = ? WHERE UserID = ?");
                $stmt->bind_param("sssssssssi", $idNumber, $firstName, $middleInitial, $lastName, $email, $username, $password, $role, $userId);
            }
            
            if ($stmt->execute()) {
                $successMessage = "User updated successfully!";
            } else {
                $errorMessage = "Error updating user: " . $conn->error;
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Delete user
    if (isset($_POST['delete_user'])) {
        $userId = intval($_POST['user_id']);
        
        $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $successMessage = "User deleted successfully!";
        } else {
            $errorMessage = "Error deleting user: " . $conn->error;
        }
        
        $stmt->close();
    }
    
    $conn->close();
}

// Get users with filtering and pagination
function getUsers($search = '', $role = '', $status = '', $page = 1, $perPage = 10) {
    $conn = connectDB();
    $users = [];
    $totalUsers = 0;
    
    $offset = ($page - 1) * $perPage;
    
    // Build the query
    $query = "SELECT * FROM Users WHERE 1=1";
    $countQuery = "SELECT COUNT(*) as total FROM Users WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $query .= " AND (IDNumber LIKE ? OR FirstName LIKE ? OR LastName LIKE ? OR Username LIKE ? OR Email LIKE ?)";
        $countQuery .= " AND (IDNumber LIKE ? OR FirstName LIKE ? OR LastName LIKE ? OR Username LIKE ? OR Email LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sssss";
    }
    
    if (!empty($role)) {
        $query .= " AND Role = ?";
        $countQuery .= " AND Role = ?";
        $params[] = $role;
        $types .= "s";
    }
    
    if (!empty($status)) {
        $query .= " AND Status = ?";
        $countQuery .= " AND Status = ?";
        $params[] = $status;
        $types .= "s";
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
    $totalUsers = $countResult->fetch_assoc()['total'];
    $countStmt->close();
    
    // Get users
    $stmt = $conn->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return [
        'users' => $users,
        'total' => $totalUsers
    ];
}

// Get available roles
function getRoles() {
    $conn = connectDB();
    $roles = [];
    
    $stmt = $conn->prepare("SELECT DISTINCT Role FROM Users ORDER BY Role");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row['Role'];
    }
    
    $stmt->close();
    $conn->close();
    
    return $roles;
}

// Process filters and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Get users
$usersData = getUsers($search, $role, $status, $page, $perPage);
$users = $usersData['users'];
$totalUsers = $usersData['total'];
$totalPages = ceil($totalUsers / $perPage);

// Get roles
$roles = getRoles();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
        /* User Management Page Specific Styles */
        .users-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .users-header {
            margin-bottom: 30px;
        }

        .users-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .users-header p {
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

        .filter-container {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
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

        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            background-color: white;
        }

        .filter-select:focus {
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

        .users-table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th,
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .users-table th {
            background-color: #f1f3f9;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        .users-table tr:hover {
            background-color: #f9f9f9;
        }

        .user-actions {
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
            background-color: #4361ee;
            color: white;
        }

        .edit-btn:hover {
            background-color: #3a56d4;
        }

        .delete-btn {
            background-color: #d63d62;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c0365a;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background-color: #e6f7ed;
            color: #0d8a53;
        }

        .status-inactive {
            background-color: #feeae9;
            color: #d63d62;
        }

        .status-pending {
            background-color: #fff8e6;
            color: #f9a826;
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
            max-width: 600px;
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

        .no-users {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        .no-users i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .no-users p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .no-users span {
            font-size: 0.9rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .actions-container {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }
            
            .filter-container {
                flex-direction: column;
                align-items: stretch;
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
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="users-container">
            <div class="users-header">
                <h1>User Management</h1>
                <p>Add, edit, and manage user accounts</p>
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
                <div class="filter-container">
                    <form class="search-form" method="get" action="usermanagement.php">
                        <input type="text" name="search" class="search-input" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                        <select name="role" class="filter-select">
                            <option value="">All Roles</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?php echo $r; ?>" <?php echo $role == $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline btn-icon">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
                
                <button id="addUserBtn" class="btn btn-primary btn-icon">
                    <i class="fas fa-plus"></i> Add New User
                </button>
            </div>
            
            <div class="users-table-container">
                <?php if (empty($users)): ?>
                    <div class="no-users">
                        <i class="fas fa-users"></i>
                        <p>No users found</p>
                        <span>Try adjusting your search or filter criteria.</span>
                    </div>
                <?php else: ?>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID Number</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                             
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['IDNumber']; ?></td>
                                    <td><?php echo $user['FirstName'] . ' ' . $user['MiddleInitial'] . '. ' . $user['LastName']; ?></td>
                                    <td><?php echo $user['Username']; ?></td>
                                    <td><?php echo $user['Email']; ?></td>
                                    <td><?php echo ucfirst($user['Role']); ?></td>
                                   
                                    <td class="user-actions">
                                        <button class="action-btn edit-btn edit-user-btn" 
                                            data-id="<?php echo htmlspecialchars($user['UserID']); ?>"
                                            data-idnumber="<?php echo htmlspecialchars($user['IDNumber']); ?>"
                                            data-firstname="<?php echo htmlspecialchars($user['FirstName']); ?>"
                                            data-middleinitial="<?php echo htmlspecialchars($user['MiddleInitial']); ?>"
                                            data-lastname="<?php echo htmlspecialchars($user['LastName']); ?>"
                                            data-email="<?php echo htmlspecialchars($user['Email']); ?>"
                                            data-username="<?php echo htmlspecialchars($user['Username']); ?>"
                                            data-role="<?php echo htmlspecialchars($user['Role']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn delete-user-btn" 
                                            data-id="<?php echo $user['UserID']; ?>"
                                            data-name="<?php echo htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']); ?>">
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
                    <a href="?search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&page=1" class="pagination-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&page=<?php echo $page - 1; ?>" class="pagination-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <a href="?search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&page=<?php echo $i; ?>" class="pagination-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <a href="?search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&page=<?php echo $page + 1; ?>" class="pagination-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>&page=<?php echo $totalPages; ?>" class="pagination-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Add User Modal -->
        <div class="modal-overlay" id="addUserModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Add New User</h2>
                    <button class="modal-close" id="closeAddUserModal">&times;</button>
                </div>
                <form id="addUserForm" method="post">
                    <input type="hidden" name="add_user" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_number">ID Number</label>
                            <input type="text" id="id_number" name="id_number" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="student">Student</option>
                                <option value="faculty">Faculty</option>
                                <option value="admin">Admin</option>
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
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
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
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelAddUserBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Edit User Modal -->
        <div class="modal-overlay" id="editUserModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Edit User</h2>
                    <button class="modal-close" id="closeEditUserModal">&times;</button>
                </div>
                <form id="editUserForm" method="post">
                    <input type="hidden" name="edit_user" value="1">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_id_number">ID Number</label>
                            <input type="text" id="edit_id_number" name="id_number" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_role">Role</label>
                            <select id="edit_role" name="role" required>
                                <option value="student">Student</option>
                                <option value="faculty">Faculty</option>
                                <option value="admin">Admin</option>
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
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
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
                        
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelEditUserBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Delete User Modal -->
        <div class="modal-overlay" id="deleteUserModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Delete User</h2>
                    <button class="modal-close" id="closeDeleteUserModal">&times;</button>
                </div>
                <form id="deleteUserForm" method="post">
                    <input type="hidden" name="delete_user" value="1">
                    <input type="hidden" id="delete_user_id" name="user_id">
                    <p>Are you sure you want to delete the user <strong id="delete_user_name"></strong>?</p>
                    <p>This action cannot be undone.</p>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelDeleteUserBtn">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add User Modal
            const addUserBtn = document.getElementById('addUserBtn');
            const addUserModal = document.getElementById('addUserModal');
            const closeAddUserModal = document.getElementById('closeAddUserModal');
            const cancelAddUserBtn = document.getElementById('cancelAddUserBtn');
            
            addUserBtn.addEventListener('click', function() {
                addUserModal.classList.add('active');
            });
            
            closeAddUserModal.addEventListener('click', function() {
                addUserModal.classList.remove('active');
            });
            
            cancelAddUserBtn.addEventListener('click', function() {
                addUserModal.classList.remove('active');
            });
            
            // Edit User Modal
            const editUserBtns = document.querySelectorAll('.edit-user-btn');
            const editUserModal = document.getElementById('editUserModal');
            const closeEditUserModal = document.getElementById('closeEditUserModal');
            const cancelEditUserBtn = document.getElementById('cancelEditUserBtn');
            const editUserId = document.getElementById('edit_user_id');
            const editIdNumber = document.getElementById('edit_id_number');
            const editFirstName = document.getElementById('edit_first_name');
            const editMiddleInitial = document.getElementById('edit_middle_initial');
            const editLastName = document.getElementById('edit_last_name');
            const editEmail = document.getElementById('edit_email');
            const editUsername = document.getElementById('edit_username');
            const editRole = document.getElementById('edit_role');
           
            
            editUserBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const idNumber = this.getAttribute('data-idnumber');
                    const firstName = this.getAttribute('data-firstname');
                    const middleInitial = this.getAttribute('data-middleinitial');
                    const lastName = this.getAttribute('data-lastname');
                    const email = this.getAttribute('data-email');
                    const username = this.getAttribute('data-username');
                    const role = this.getAttribute('data-role');
                   
                    
                    editUserId.value = id;
                    editIdNumber.value = idNumber;
                    editFirstName.value = firstName;
                    editMiddleInitial.value = middleInitial;
                    editLastName.value = lastName;
                    editEmail.value = email;
                    editUsername.value = username;
                    editRole.value = role;
                   
                    
                    editUserModal.classList.add('active');
                });
            });
            
            closeEditUserModal.addEventListener('click', function() {
                editUserModal.classList.remove('active');
            });
            
            cancelEditUserBtn.addEventListener('click', function() {
                editUserModal.classList.remove('active');
            });
            
            // Delete User Modal
            const deleteUserBtns = document.querySelectorAll('.delete-user-btn');
            const deleteUserModal = document.getElementById('deleteUserModal');
            const closeDeleteUserModal = document.getElementById('closeDeleteUserModal');
            const cancelDeleteUserBtn = document.getElementById('cancelDeleteUserBtn');
            const deleteUserId = document.getElementById('delete_user_id');
            const deleteUserName = document.getElementById('delete_user_name');
            
            deleteUserBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    
                    deleteUserId.value = id;
                    deleteUserName.textContent = name;
                    
                    deleteUserModal.classList.add('active');
                });
            });
            
            closeDeleteUserModal.addEventListener('click', function() {
                deleteUserModal.classList.remove('active');
            });
            
            cancelDeleteUserBtn.addEventListener('click', function() {
                deleteUserModal.classList.remove('active');
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
        });
    </script>
</body>
</html>