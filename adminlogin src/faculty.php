<?php
include '../config.php';
include 'session_init.php';


// Initialize variables
$successMessage = "";
$errorMessage = "";
$userId = $_SESSION['user_id'] ?? 0;
$isAdmin = ($_SESSION['role'] === 'admin');

// Handle faculty operations (add, edit, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = connectDB();
    
    // Add new faculty
    if (isset($_POST['add_faculty'])) {
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
        $role = 'faculty';
        
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
                $successMessage = "Faculty member added successfully!";
            } else {
                $errorMessage = "Error adding faculty member: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Edit faculty
    if (isset($_POST['edit_faculty'])) {
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
                $successMessage = "Faculty member updated successfully!";
            } else {
                $errorMessage = "Error updating faculty member: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Delete faculty
    if (isset($_POST['delete_faculty'])) {
        $userId = intval($_POST['user_id']);
        
        $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ? AND Role = 'faculty'");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $successMessage = "Faculty member deleted successfully!";
        } else {
            $errorMessage = "Error deleting faculty member: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    $conn->close();
}

// Get faculty with filtering and pagination
function getFaculty($search = '', $page = 1, $perPage = 10) {
    $conn = connectDB();
    $faculty = [];
    $totalFaculty = 0;
    
    $offset = ($page - 1) * $perPage;
    
    // Build the query
    $query = "SELECT UserID, IDNumber, FirstName, MiddleInitial, LastName, 
                     Birthday, Email, AddressDetails, PhoneNumber, Gender, Username, 
                     CASE WHEN Photo IS NOT NULL THEN 1 ELSE 0 END AS HasPhoto 
              FROM Users
              WHERE Role = 'faculty'";
    
    $countQuery = "SELECT COUNT(*) as total 
                   FROM Users
                   WHERE Role = 'faculty'";
    
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
    $totalFaculty = $countResult->fetch_assoc()['total'];
    $countStmt->close();
    
    // Get faculty
    $stmt = $conn->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $faculty[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return [
        'faculty' => $faculty,
        'total' => $totalFaculty
    ];
}

// Get faculty photo
function getFacultyPhoto($userId) {
    $conn = connectDB();
    $photo = null;
    
    $stmt = $conn->prepare("SELECT Photo FROM Users WHERE UserID = ? AND Role = 'faculty'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($photoData);
        $stmt->fetch();
        
        if ($photoData !== null) {
            $photo = base64_encode($photoData);
        }
    }
    
    $stmt->close();
    $conn->close();
    
    return $photo;
}

// Process filters and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Get faculty
$facultyData = getFaculty($search, $page, $perPage);
$faculty = $facultyData['faculty'];
$totalFaculty = $facultyData['total'];
$totalPages = ceil($totalFaculty / $perPage);

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
    <title>Faculty Management - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <link rel="stylesheet" href="facultystyles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="faculty-container">
            <div class="faculty-header">
                <h1>Faculty Management</h1>
                <p>View, add, and manage faculty members</p>
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
                <form class="search-form" method="get" action="faculty.php">
                    <input type="text" name="search" class="search-input" placeholder="Search faculty..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline btn-icon">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
                
                <button id="addFacultyBtn" class="btn btn-primary btn-icon">
                    <i class="fas fa-plus"></i> Add New Faculty
                </button>
            </div>
            
            <div class="faculty-table-container">
                <?php if (empty($faculty)): ?>
                    <div class="no-faculty">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>No faculty members found</p>
                        <span>Try adjusting your search criteria or add a new faculty member.</span>
                    </div>
                <?php else: ?>
                    <table class="faculty-table">
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
                            <?php foreach ($faculty as $member): ?>
                                <tr>
                                    <td><?php echo $member['IDNumber']; ?></td>
                                    <td><?php echo $member['FirstName'] . ' ' . $member['MiddleInitial'] . '. ' . $member['LastName']; ?></td>
                                    <td><?php echo $member['Email']; ?></td>
                                    <td><?php echo $member['PhoneNumber'] ?: 'Not provided'; ?></td>
                                    <td><?php echo $member['Gender']; ?></td>
                                    <td class="faculty-actions">
                                        <button class="action-btn view-btn view-faculty-btn" 
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
                                        <button class="action-btn edit-btn edit-faculty-btn" 
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
                                        <button class="action-btn delete-btn delete-faculty-btn" 
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
        
        <!-- View Faculty Modal -->
        <div class="modal-overlay" id="viewFacultyModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Faculty Details</h2>
                    <button class="modal-close" id="closeViewFacultyModal">&times;</button>
                </div>
                <div class="faculty-details">
                    <!-- Faculty Photo -->
                    <div class="faculty-photo-container">
                        <div id="view-photo-placeholder" class="faculty-photo-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                        <img id="view-photo" class="faculty-photo" style="display: none;" alt="Faculty Photo">
                    </div>
                    
                    <div class="faculty-details-section">
                        <h3>Personal Information</h3>
                        <div class="faculty-info-grid">
                            <div class="faculty-info-item">
                                <div class="faculty-info-label">ID Number</div>
                                <div class="faculty-info-value" id="view-id-number"></div>
                            </div>
                            <div class="faculty-info-item">
                                <div class="faculty-info-label">Full Name</div>
                                <div class="faculty-info-value" id="view-full-name"></div>
                            </div>
                            <div class="faculty-info-item">
                                <div class="faculty-info-label">Birthday</div>
                                <div class="faculty-info-value" id="view-birthday"></div>
                            </div>
                            <div class="faculty-info-item">
                                <div class="faculty-info-label">Gender</div>
                                <div class="faculty-info-value" id="view-gender"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faculty-details-section">
                        <h3>Contact Information</h3>
                        <div class="faculty-info-grid">
                            <div class="faculty-info-item">
                                <div class="faculty-info-label">Email</div>
                                <div class="faculty-info-value" id="view-email"></div>
                            </div>
                            <div class="faculty-info-item">
                                <div class="faculty-info-label">Phone Number</div>
                                <div class="faculty-info-value" id="view-phone"></div>
                            </div>
                            <div class="faculty-info-item">
                                <div class="faculty-info-label">Address</div>
                                <div class="faculty-info-value" id="view-address"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faculty-details-section">
                        <h3>Account Information</h3>
                        <div class="faculty-info-grid">
                            <div class="faculty-info-item">
                                <div class="faculty-info-label">Username</div>
                                <div class="faculty-info-value" id="view-username"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="closeViewFacultyBtn">Close</button>
                    <button type="button" class="btn btn-primary" id="viewToEditBtn">Edit</button>
                </div>
            </div>
        </div>
        
        <!-- Add Faculty Modal -->
        <div class="modal-overlay" id="addFacultyModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Add New Faculty</h2>
                    <button class="modal-close" id="closeAddFacultyModal">&times;</button>
                </div>
                <form id="addFacultyForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="add_faculty" value="1">
                    
                    <!-- Photo Upload -->
                    <div class="photo-upload-container">
                        <div class="form-group">
                            <label for="photo">Profile Photo</label>
                            <input type="file" id="photo" name="photo" accept="image/*" onchange="previewImage(this, 'add-photo-preview')">
                        </div>
                        <img id="add-photo-preview" class="photo-preview" alt="Photo Preview">
                    </div>
                    
                    <div class="faculty-details-section">
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
                    
                    <div class="faculty-details-section">
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
                    
                    <div class="faculty-details-section">
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
                        <button type="button" class="btn btn-outline" id="cancelAddFacultyBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Faculty</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Edit Faculty Modal -->
        <div class="modal-overlay" id="editFacultyModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Faculty</h2>
                    <button class="modal-close" id="closeEditFacultyModal">&times;</button>
                </div>
                <form id="editFacultyForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="edit_faculty" value="1">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <!-- Photo Upload -->
                    <div class="photo-upload-container">
                        <div class="form-group">
                            <label for="edit_photo">Profile Photo</label>
                            <input type="file" id="edit_photo" name="photo" accept="image/*" onchange="previewImage(this, 'edit-photo-preview')">
                        </div>
                        <img id="edit-photo-preview" class="photo-preview" alt="Photo Preview">
                    </div>
                    
                    <div class="faculty-details-section">
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
                    
                    <div class="faculty-details-section">
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
                    
                    <div class="faculty-details-section">
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
                        <button type="button" class="btn btn-outline" id="cancelEditFacultyBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Faculty</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Delete Faculty Modal -->
        <div class="modal-overlay" id="deleteFacultyModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Faculty</h2>
                    <button class="modal-close" id="closeDeleteFacultyModal">&times;</button>
                </div>
                <form id="deleteFacultyForm" method="post">
                    <input type="hidden" name="delete_faculty" value="1">
                    <input type="hidden" id="delete_user_id" name="user_id">
                    <p>Are you sure you want to delete the faculty member <strong id="delete_faculty_name"></strong>?</p>
                    <p>This action cannot be undone and will remove all associated records.</p>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelDeleteFacultyBtn">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Faculty</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View Faculty Modal
            const viewFacultyBtns = document.querySelectorAll('.view-faculty-btn');
            const viewFacultyModal = document.getElementById('viewFacultyModal');
            const closeViewFacultyModal = document.getElementById('closeViewFacultyModal');
            const closeViewFacultyBtn = document.getElementById('closeViewFacultyBtn');
            const viewToEditBtn = document.getElementById('viewToEditBtn');
            
            viewFacultyBtns.forEach(btn => {
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
                        photoElement.src = `faculty.php?get_photo=1&user_id=${userId}&t=${new Date().getTime()}`; // Add timestamp to prevent caching
                        photoElement.style.display = 'block';
                        photoPlaceholder.style.display = 'none';
                    } else {
                        photoElement.style.display = 'none';
                        photoPlaceholder.style.display = 'flex';
                    }
                    
                    // Store data for edit button
                    viewToEditBtn.setAttribute('data-userid', userId);
                    
                    viewFacultyModal.classList.add('active');
                });
            });
            
            if (closeViewFacultyModal) {
                closeViewFacultyModal.addEventListener('click', function() {
                    viewFacultyModal.classList.remove('active');
                });
            }
            
            if (closeViewFacultyBtn) {
                closeViewFacultyBtn.addEventListener('click', function() {
                    viewFacultyModal.classList.remove('active');
                });
            }
            
            // View to Edit
            if (viewToEditBtn) {
                viewToEditBtn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-userid');
                    
                    // Find the edit button with matching data and trigger click
                    const editBtn = document.querySelector(`.edit-faculty-btn[data-userid="${userId}"]`);
                    if (editBtn) {
                        viewFacultyModal.classList.remove('active');
                        editBtn.click();
                    }
                });
            }
            
            // Add Faculty Modal
            const addFacultyBtn = document.getElementById('addFacultyBtn');
            const addFacultyModal = document.getElementById('addFacultyModal');
            const closeAddFacultyModal = document.getElementById('closeAddFacultyModal');
            const cancelAddFacultyBtn = document.getElementById('cancelAddFacultyBtn');
            
            if (addFacultyBtn) {
                addFacultyBtn.addEventListener('click', function() {
                    addFacultyModal.classList.add('active');
                });
            }
            
            if (closeAddFacultyModal) {
                closeAddFacultyModal.addEventListener('click', function() {
                    addFacultyModal.classList.remove('active');
                });
            }
            
            if (cancelAddFacultyBtn) {
                cancelAddFacultyBtn.addEventListener('click', function() {
                    addFacultyModal.classList.remove('active');
                });
            }
            
            // Edit Faculty Modal
            const editFacultyBtns = document.querySelectorAll('.edit-faculty-btn');
            const editFacultyModal = document.getElementById('editFacultyModal');
            const closeEditFacultyModal = document.getElementById('closeEditFacultyModal');
            const cancelEditFacultyBtn = document.getElementById('cancelEditFacultyBtn');
            
            editFacultyBtns.forEach(btn => {
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
                        photoPreview.src = `faculty.php?get_photo=1&user_id=${userId}&t=${new Date().getTime()}`; // Add timestamp to prevent caching
                        photoPreview.style.display = 'block';
                    } else {
                        photoPreview.style.display = 'none';
                    }
                    
                    editFacultyModal.classList.add('active');
                });
            });
            
            if (closeEditFacultyModal) {
                closeEditFacultyModal.addEventListener('click', function() {
                    editFacultyModal.classList.remove('active');
                });
            }
            
            if (cancelEditFacultyBtn) {
                cancelEditFacultyBtn.addEventListener('click', function() {
                    editFacultyModal.classList.remove('active');
                });
            }
            
            // Delete Faculty Modal
            const deleteFacultyBtns = document.querySelectorAll('.delete-faculty-btn');
            const deleteFacultyModal = document.getElementById('deleteFacultyModal');
            const closeDeleteFacultyModal = document.getElementById('closeDeleteFacultyModal');
            const cancelDeleteFacultyBtn = document.getElementById('cancelDeleteFacultyBtn');
            
            deleteFacultyBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-userid');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('delete_user_id').value = userId;
                    document.getElementById('delete_faculty_name').textContent = name;
                    
                    deleteFacultyModal.classList.add('active');
                });
            });
            
            if (closeDeleteFacultyModal) {
                closeDeleteFacultyModal.addEventListener('click', function() {
                    deleteFacultyModal.classList.remove('active');
                });
            }
            
            if (cancelDeleteFacultyBtn) {
                cancelDeleteFacultyBtn.addEventListener('click', function() {
                    deleteFacultyModal.classList.remove('active');
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