<?php
include 'session_init.php';


// Check if user has permission (admin, faculty, or registrar)
$allowedRoles = ['admin', 'faculty', 'registrar'];
if (!in_array($_SESSION['role'], $allowedRoles)) {
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
$userId = $_SESSION['user_id'] ?? 0;
$isAdmin = ($_SESSION['role'] === 'admin');
$isRegistrar = ($_SESSION['role'] === 'registrar');

// Handle student operations (add, edit, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = connectDB();
    
    // Add new student
    if (isset($_POST['add_student'])) {
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
        $role = 'student';
        
        // Get student information
        $courseId = intval($_POST['course']);
        $yearLevelId = intval($_POST['year_level']);
        
        // Check if username or ID number already exists
        $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE Username = ? OR IDNumber = ?");
        $checkStmt->bind_param("ss", $username, $idNumber);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Username or ID Number already exists. Please use different credentials.";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert into Users table
                $userStmt = $conn->prepare("INSERT INTO Users (IDNumber, FirstName, MiddleInitial, LastName, Birthday, Email, AddressDetails, PhoneNumber, Gender, Username, Password, Role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $userStmt->bind_param("ssssssssssss", $idNumber, $firstName, $middleInitial, $lastName, $birthday, $email, $address, $phone, $gender, $username, $password, $role);
                $userStmt->execute();
                
                // Get the inserted user ID
                $userId = $conn->insert_id;
                
                // Insert into Students table
                $studentStmt = $conn->prepare("INSERT INTO Students (UserID, YearLevelID, CourseID) VALUES (?, ?, ?)");
                $studentStmt->bind_param("iii", $userId, $yearLevelId, $courseId);
                $studentStmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $successMessage = "Student added successfully!";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $errorMessage = "Error adding student: " . $e->getMessage();
            }
            
            $userStmt->close();
            $studentStmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Edit student
    if (isset($_POST['edit_student'])) {
        $studentId = intval($_POST['student_id']);
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
        
        // Get student information
        $courseId = intval($_POST['course']);
        $yearLevelId = intval($_POST['year_level']);
        
        // Check if username or ID number already exists for other users
        $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE (Username = ? OR IDNumber = ?) AND UserID != ?");
        $checkStmt->bind_param("ssi", $username, $idNumber, $userId);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Username or ID Number already exists. Please use different credentials.";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Update Users table
                if (empty($_POST['password'])) {
                    // Update without changing password
                    $userStmt = $conn->prepare("UPDATE Users SET IDNumber = ?, FirstName = ?, MiddleInitial = ?, LastName = ?, Birthday = ?, Email = ?, AddressDetails = ?, PhoneNumber = ?, Gender = ?, Username = ? WHERE UserID = ?");
                    $userStmt->bind_param("ssssssssssi", $idNumber, $firstName, $middleInitial, $lastName, $birthday, $email, $address, $phone, $gender, $username, $userId);
                } else {
                    // Update with new password
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $userStmt = $conn->prepare("UPDATE Users SET IDNumber = ?, FirstName = ?, MiddleInitial = ?, LastName = ?, Birthday = ?, Email = ?, AddressDetails = ?, PhoneNumber = ?, Gender = ?, Username = ?, Password = ? WHERE UserID = ?");
                    $userStmt->bind_param("sssssssssssi", $idNumber, $firstName, $middleInitial, $lastName, $birthday, $email, $address, $phone, $gender, $username, $password, $userId);
                }
                $userStmt->execute();
                
                // Update Students table
                $studentStmt = $conn->prepare("UPDATE Students SET YearLevelID = ?, CourseID = ? WHERE StudentID = ?");
                $studentStmt->bind_param("iii", $yearLevelId, $courseId, $studentId);
                $studentStmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $successMessage = "Student updated successfully!";
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $errorMessage = "Error updating student: " . $e->getMessage();
            }
            
            $userStmt->close();
            $studentStmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Delete student
    if (isset($_POST['delete_student'])) {
        $userId = intval($_POST['user_id']);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete from Users table (will cascade to Students table)
            $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $successMessage = "Student deleted successfully!";
            $stmt->close();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errorMessage = "Error deleting student: " . $e->getMessage();
        }
    }
    
    $conn->close();
}

// Get students with filtering and pagination
function getStudents($search = '', $course = '', $yearLevel = '', $page = 1, $perPage = 10) {
    $conn = connectDB();
    $students = [];
    $totalStudents = 0;
    
    $offset = ($page - 1) * $perPage;
    
    // Build the query
    $query = "SELECT s.StudentID, u.UserID, u.IDNumber, u.FirstName, u.MiddleInitial, u.LastName, 
                     u.Birthday, u.Email, u.AddressDetails, u.PhoneNumber, u.Gender, u.Username, 
                     s.YearLevelID, s.CourseID, y.YearLevelName, c.CourseName 
              FROM Students s
              JOIN Users u ON s.UserID = u.UserID
              JOIN YearLevel y ON s.YearLevelID = y.YearLevelID
              JOIN Course c ON s.CourseID = c.CourseID
              WHERE 1=1";
    
    $countQuery = "SELECT COUNT(*) as total 
                   FROM Students s
                   JOIN Users u ON s.UserID = u.UserID
                   JOIN YearLevel y ON s.YearLevelID = y.YearLevelID
                   JOIN Course c ON s.CourseID = c.CourseID
                   WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $query .= " AND (u.IDNumber LIKE ? OR u.FirstName LIKE ? OR u.LastName LIKE ? OR u.Email LIKE ? OR u.Username LIKE ?)";
        $countQuery .= " AND (u.IDNumber LIKE ? OR u.FirstName LIKE ? OR u.LastName LIKE ? OR u.Email LIKE ? OR u.Username LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sssss";
    }
    
    if (!empty($course)) {
        $query .= " AND s.CourseID = ?";
        $countQuery .= " AND s.CourseID = ?";
        $params[] = $course;
        $types .= "i";
    }
    
    if (!empty($yearLevel)) {
        $query .= " AND s.YearLevelID = ?";
        $countQuery .= " AND s.YearLevelID = ?";
        $params[] = $yearLevel;
        $types .= "i";
    }
    
    $query .= " ORDER BY u.LastName, u.FirstName LIMIT ?, ?";
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
    $totalStudents = $countResult->fetch_assoc()['total'];
    $countStmt->close();
    
    // Get students
    $stmt = $conn->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return [
        'students' => $students,
        'total' => $totalStudents
    ];
}

// Get courses
function getCourses() {
    $conn = connectDB();
    $courses = [];
    
    $stmt = $conn->prepare("SELECT CourseID, CourseName FROM Course ORDER BY CourseName");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $courses;
}

// Get year levels
function getYearLevels() {
    $conn = connectDB();
    $yearLevels = [];
    
    $stmt = $conn->prepare("SELECT YearLevelID, YearLevelName FROM YearLevel ORDER BY YearLevelID");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $yearLevels[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $yearLevels;
}

// Process filters and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$course = isset($_GET['course']) ? $_GET['course'] : '';
$yearLevel = isset($_GET['year_level']) ? $_GET['year_level'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Get students
$studentsData = getStudents($search, $course, $yearLevel, $page, $perPage);
$students = $studentsData['students'];
$totalStudents = $studentsData['total'];
$totalPages = ceil($totalStudents / $perPage);

// Get courses and year levels for filters and forms
$courses = getCourses();
$yearLevels = getYearLevels();

require_once 'session_init.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
        /* Student Management Page Specific Styles */
        .students-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .students-header {
            margin-bottom: 30px;
        }

        .students-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .students-header p {
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

        .students-table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
        }

        .students-table th,
        .students-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .students-table th {
            background-color: #f1f3f9;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }

        .students-table tr:last-child td {
            border-bottom: none;
        }

        .students-table tr:hover {
            background-color: #f9f9f9;
        }

        .student-actions {
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

        .no-students {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        .no-students i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .no-students p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .no-students span {
            font-size: 0.9rem;
        }

        .student-details {
            margin-bottom: 20px;
        }

        .student-details-section {
            margin-bottom: 20px;
        }

        .student-details-section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .student-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .student-info-item {
            margin-bottom: 10px;
        }

        .student-info-label {
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .student-info-value {
            color: #333;
            font-size: 1rem;
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
            
            .student-info-grid {
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
        
        <div class="students-container">
            <div class="students-header">
                <h1>Student Management</h1>
                <p>View, add, and manage student records</p>
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
                    <form class="search-form" method="get" action="students.php">
                        <input type="text" name="search" class="search-input" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                        <select name="course" class="filter-select">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo $c['CourseID']; ?>" <?php echo $course == $c['CourseID'] ? 'selected' : ''; ?>><?php echo $c['CourseName']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="year_level" class="filter-select">
                            <option value="">All Year Levels</option>
                            <?php foreach ($yearLevels as $yl): ?>
                                <option value="<?php echo $yl['YearLevelID']; ?>" <?php echo $yearLevel == $yl['YearLevelID'] ? 'selected' : ''; ?>><?php echo $yl['YearLevelName']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline btn-icon">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
                
                <?php if ($isAdmin || $isRegistrar): ?>
                <button id="addStudentBtn" class="btn btn-primary btn-icon">
                    <i class="fas fa-plus"></i> Add New Student
                </button>
                <?php endif; ?>
            </div>
            
            <div class="students-table-container">
                <?php if (empty($students)): ?>
                    <div class="no-students">
                        <i class="fas fa-user-graduate"></i>
                        <p>No students found</p>
                        <span>Try adjusting your search or filter criteria.</span>
                    </div>
                <?php else: ?>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>ID Number</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Year Level</th>
                                <th>Email</th>
                                <th>Gender</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo $student['IDNumber']; ?></td>
                                    <td><?php echo $student['FirstName'] . ' ' . $student['MiddleInitial'] . '. ' . $student['LastName']; ?></td>
                                    <td><?php echo $student['CourseName']; ?></td>
                                    <td><?php echo $student['YearLevelName']; ?></td>
                                    <td><?php echo $student['Email']; ?></td>
                                    <td><?php echo $student['Gender']; ?></td>
                                    <td class="student-actions">
                                        <button class="action-btn view-btn view-student-btn" 
                                            data-id="<?php echo $student['StudentID']; ?>"
                                            data-userid="<?php echo $student['UserID']; ?>"
                                            data-idnumber="<?php echo $student['IDNumber']; ?>"
                                            data-firstname="<?php echo $student['FirstName']; ?>"
                                            data-middleinitial="<?php echo $student['MiddleInitial']; ?>"
                                            data-lastname="<?php echo $student['LastName']; ?>"
                                            data-birthday="<?php echo $student['Birthday']; ?>"
                                            data-email="<?php echo $student['Email']; ?>"
                                            data-address="<?php echo $student['AddressDetails']; ?>"
                                            data-phone="<?php echo $student['PhoneNumber']; ?>"
                                            data-gender="<?php echo $student['Gender']; ?>"
                                            data-username="<?php echo $student['Username']; ?>"
                                            data-yearleveid="<?php echo $student['YearLevelID']; ?>"
                                            data-yearlevel="<?php echo $student['YearLevelName']; ?>"
                                            data-courseid="<?php echo $student['CourseID']; ?>"
                                            data-course="<?php echo $student['CourseName']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($isAdmin || $isRegistrar): ?>
                                        <button class="action-btn edit-btn edit-student-btn" 
                                            data-id="<?php echo $student['StudentID']; ?>"
                                            data-userid="<?php echo $student['UserID']; ?>"
                                            data-idnumber="<?php echo $student['IDNumber']; ?>"
                                            data-firstname="<?php echo $student['FirstName']; ?>"
                                            data-middleinitial="<?php echo $student['MiddleInitial']; ?>"
                                            data-lastname="<?php echo $student['LastName']; ?>"
                                            data-birthday="<?php echo $student['Birthday']; ?>"
                                            data-email="<?php echo $student['Email']; ?>"
                                            data-address="<?php echo $student['AddressDetails']; ?>"
                                            data-phone="<?php echo $student['PhoneNumber']; ?>"
                                            data-gender="<?php echo $student['Gender']; ?>"
                                            data-username="<?php echo $student['Username']; ?>"
                                            data-yearleveid="<?php echo $student['YearLevelID']; ?>"
                                            data-courseid="<?php echo $student['CourseID']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn delete-student-btn" 
                                            data-id="<?php echo $student['StudentID']; ?>"
                                            data-userid="<?php echo $student['UserID']; ?>"
                                            data-name="<?php echo $student['FirstName'] . ' ' . $student['LastName']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
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
                    <a href="?search=<?php echo urlencode($search); ?>&course=<?php echo urlencode($course); ?>&year_level=<?php echo urlencode($yearLevel); ?>&page=1" class="pagination-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?search=<?php echo urlencode($search); ?>&course=<?php echo urlencode($course); ?>&year_level=<?php echo urlencode($yearLevel); ?>&page=<?php echo $page - 1; ?>" class="pagination-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <a href="?search=<?php echo urlencode($search); ?>&course=<?php echo urlencode($course); ?>&year_level=<?php echo urlencode($yearLevel); ?>&page=<?php echo $i; ?>" class="pagination-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <a href="?search=<?php echo urlencode($search); ?>&course=<?php echo urlencode($course); ?>&year_level=<?php echo urlencode($yearLevel); ?>&page=<?php echo $page + 1; ?>" class="pagination-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?search=<?php echo urlencode($search); ?>&course=<?php echo urlencode($course); ?>&year_level=<?php echo urlencode($yearLevel); ?>&page=<?php echo $totalPages; ?>" class="pagination-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- View Student Modal -->
        <div class="modal-overlay" id="viewStudentModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Student Details</h2>
                    <button class="modal-close" id="closeViewStudentModal">&times;</button>
                </div>
                <div class="student-details">
                    <div class="student-details-section">
                        <h3>Personal Information</h3>
                        <div class="student-info-grid">
                            <div class="student-info-item">
                                <div class="student-info-label">ID Number</div>
                                <div class="student-info-value" id="view-id-number"></div>
                            </div>
                            <div class="student-info-item">
                                <div class="student-info-label">Full Name</div>
                                <div class="student-info-value" id="view-full-name"></div>
                            </div>
                            <div class="student-info-item">
                                <div class="student-info-label">Birthday</div>
                                <div class="student-info-value" id="view-birthday"></div>
                            </div>
                            <div class="student-info-item">
                                <div class="student-info-label">Gender</div>
                                <div class="student-info-value" id="view-gender"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="student-details-section">
                        <h3>Contact Information</h3>
                        <div class="student-info-grid">
                            <div class="student-info-item">
                                <div class="student-info-label">Email</div>
                                <div class="student-info-value" id="view-email"></div>
                            </div>
                            <div class="student-info-item">
                                <div class="student-info-label">Phone Number</div>
                                <div class="student-info-value" id="view-phone"></div>
                            </div>
                            <div class="student-info-item">
                                <div class="student-info-label">Address</div>
                                <div class="student-info-value" id="view-address"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="student-details-section">
                        <h3>Academic Information</h3>
                        <div class="student-info-grid">
                            <div class="student-info-item">
                                <div class="student-info-label">Course</div>
                                <div class="student-info-value" id="view-course"></div>
                            </div>
                            <div class="student-info-item">
                                <div class="student-info-label">Year Level</div>
                                <div class="student-info-value" id="view-year-level"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="student-details-section">
                        <h3>Account Information</h3>
                        <div class="student-info-grid">
                            <div class="student-info-item">
                                <div class="student-info-label">Username</div>
                                <div class="student-info-value" id="view-username"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="closeViewStudentBtn">Close</button>
                    <?php if ($isAdmin || $isRegistrar): ?>
                    <button type="button" class="btn btn-primary" id="viewToEditBtn">Edit</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Add Student Modal -->
        <?php if ($isAdmin || $isRegistrar): ?>
        <div class="modal-overlay" id="addStudentModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Add New Student</h2>
                    <button class="modal-close" id="closeAddStudentModal">&times;</button>
                </div>
                <form id="addStudentForm" method="post">
                    <input type="hidden" name="add_student" value="1">
                    
                    <div class="student-details-section">
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
                    
                    <div class="student-details-section">
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
                    
                    <div class="student-details-section">
                        <h3>Academic Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="course">Course</label>
                                <select id="course" name="course" required>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?php echo $c['CourseID']; ?>"><?php echo $c['CourseName']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="year_level">Year Level</label>
                                <select id="year_level" name="year_level" required>
                                    <?php foreach ($yearLevels as $yl): ?>
                                        <option value="<?php echo $yl['YearLevelID']; ?>"><?php echo $yl['YearLevelName']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="student-details-section">
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
                        <button type="button" class="btn btn-outline" id="cancelAddStudentBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Student</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Edit Student Modal -->
        <div class="modal-overlay" id="editStudentModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Student</h2>
                    <button class="modal-close" id="closeEditStudentModal">&times;</button>
                </div>
                <form id="editStudentForm" method="post">
                    <input type="hidden" name="edit_student" value="1">
                    <input type="hidden" id="edit_student_id" name="student_id">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <div class="student-details-section">
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
                    
                    <div class="student-details-section">
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
                    
                    <div class="student-details-section">
                        <h3>Academic Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_course">Course</label>
                                <select id="edit_course" name="course" required>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?php echo $c['CourseID']; ?>"><?php echo $c['CourseName']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_year_level">Year Level</label>
                                <select id="edit_year_level" name="year_level" required>
                                    <?php foreach ($yearLevels as $yl): ?>
                                        <option value="<?php echo $yl['YearLevelID']; ?>"><?php echo $yl['YearLevelName']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="student-details-section">
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
                        <button type="button" class="btn btn-outline" id="cancelEditStudentBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Student</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Delete Student Modal -->
        <div class="modal-overlay" id="deleteStudentModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Student</h2>
                    <button class="modal-close" id="closeDeleteStudentModal">&times;</button>
                </div>
                <form id="deleteStudentForm" method="post">
                    <input type="hidden" name="delete_student" value="1">
                    <input type="hidden" id="delete_user_id" name="user_id">
                    <p>Are you sure you want to delete the student <strong id="delete_student_name"></strong>?</p>
                    <p>This action cannot be undone and will remove all associated records.</p>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelDeleteStudentBtn">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Student</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View Student Modal
            const viewStudentBtns = document.querySelectorAll('.view-student-btn');
            const viewStudentModal = document.getElementById('viewStudentModal');
            const closeViewStudentModal = document.getElementById('closeViewStudentModal');
            const closeViewStudentBtn = document.getElementById('closeViewStudentBtn');
            const viewToEditBtn = document.getElementById('viewToEditBtn');
            
            viewStudentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
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
                    const yearLevel = this.getAttribute('data-yearlevel');
                    const course = this.getAttribute('data-course');
                    
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
                    document.getElementById('view-course').textContent = course;
                    document.getElementById('view-year-level').textContent = yearLevel;
                    document.getElementById('view-username').textContent = username;
                    
                    // Store data for edit button
                    if (viewToEditBtn) {
                        viewToEditBtn.setAttribute('data-id', this.getAttribute('data-id'));
                        viewToEditBtn.setAttribute('data-userid', this.getAttribute('data-userid'));
                    }
                    
                    viewStudentModal.classList.add('active');
                });
            });
            
            if (closeViewStudentModal) {
                closeViewStudentModal.addEventListener('click', function() {
                    viewStudentModal.classList.remove('active');
                });
            }
            
            if (closeViewStudentBtn) {
                closeViewStudentBtn.addEventListener('click', function() {
                    viewStudentModal.classList.remove('active');
                });
            }
            
            // View to Edit
            if (viewToEditBtn) {
                viewToEditBtn.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-id');
                    const userId = this.getAttribute('data-userid');
                    
                    // Find the edit button with matching data and trigger click
                    const editBtn = document.querySelector(`.edit-student-btn[data-id="${studentId}"][data-userid="${userId}"]`);
                    if (editBtn) {
                        viewStudentModal.classList.remove('active');
                        editBtn.click();
                    }
                });
            }
            
            // Add Student Modal
            const addStudentBtn = document.getElementById('addStudentBtn');
            const addStudentModal = document.getElementById('addStudentModal');
            const closeAddStudentModal = document.getElementById('closeAddStudentModal');
            const cancelAddStudentBtn = document.getElementById('cancelAddStudentBtn');
            
            if (addStudentBtn) {
                addStudentBtn.addEventListener('click', function() {
                    addStudentModal.classList.add('active');
                });
            }
            
            if (closeAddStudentModal) {
                closeAddStudentModal.addEventListener('click', function() {
                    addStudentModal.classList.remove('active');
                });
            }
            
            if (cancelAddStudentBtn) {
                cancelAddStudentBtn.addEventListener('click', function() {
                    addStudentModal.classList.remove('active');
                });
            }
            
            // Edit Student Modal
            const editStudentBtns = document.querySelectorAll('.edit-student-btn');
            const editStudentModal = document.getElementById('editStudentModal');
            const closeEditStudentModal = document.getElementById('closeEditStudentModal');
            const cancelEditStudentBtn = document.getElementById('cancelEditStudentBtn');
            
            editStudentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-id');
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
                    const yearLevelId = this.getAttribute('data-yearleveid');
                    const courseId = this.getAttribute('data-courseid');
                    
                    // Populate edit form
                    document.getElementById('edit_student_id').value = studentId;
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
                    document.getElementById('edit_year_level').value = yearLevelId;
                    document.getElementById('edit_course').value = courseId;
                    
                    editStudentModal.classList.add('active');
                });
            });
            
            if (closeEditStudentModal) {
                closeEditStudentModal.addEventListener('click', function() {
                    editStudentModal.classList.remove('active');
                });
            }
            
            if (cancelEditStudentBtn) {
                cancelEditStudentBtn.addEventListener('click', function() {
                    editStudentModal.classList.remove('active');
                });
            }
            
            // Delete Student Modal
            const deleteStudentBtns = document.querySelectorAll('.delete-student-btn');
            const deleteStudentModal = document.getElementById('deleteStudentModal');
            const closeDeleteStudentModal = document.getElementById('closeDeleteStudentModal');
            const cancelDeleteStudentBtn = document.getElementById('cancelDeleteStudentBtn');
            
            deleteStudentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-userid');
                    const name = this.getAttribute('data-name');
                    
                    document.getElementById('delete_user_id').value = userId;
                    document.getElementById('delete_student_name').textContent = name;
                    
                    deleteStudentModal.classList.add('active');
                });
            });
            
            if (closeDeleteStudentModal) {
                closeDeleteStudentModal.addEventListener('click', function() {
                    deleteStudentModal.classList.remove('active');
                });
            }
            
            if (cancelDeleteStudentBtn) {
                cancelDeleteStudentBtn.addEventListener('click', function() {
                    deleteStudentModal.classList.remove('active');
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
    </script>
</body>
</html>