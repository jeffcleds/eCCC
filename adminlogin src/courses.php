<?php
   include 'session_init.php';
// Start session if not already started


// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Check if user is admin
$isAdmin = ($_SESSION['role'] === 'admin');
$isFaculty = ($_SESSION['role'] === 'faculty');

// If not admin or faculty, redirect to dashboard
if (!$isAdmin && !$isFaculty) {
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

// Handle course operations (add, edit, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = connectDB();
    
    // Add new course
    if (isset($_POST['add_course'])) {
        $courseCode = $conn->real_escape_string($_POST['course_code']);
        $courseTitle = $conn->real_escape_string($_POST['course_title']);
        $units = intval($_POST['units']);
        $description = $conn->real_escape_string($_POST['description']);
        $department = $conn->real_escape_string($_POST['department']);
        $prerequisite = $conn->real_escape_string($_POST['prerequisite']);
        
        // Check if course code already exists
        $checkStmt = $conn->prepare("SELECT CourseID FROM Courses WHERE CourseCode = ?");
        $checkStmt->bind_param("s", $courseCode);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Course code already exists. Please use a different code.";
        } else {
            // In a real application, you would insert the course into the database
            /*
            $stmt = $conn->prepare("INSERT INTO Courses (CourseCode, CourseTitle, Units, Description, Department, Prerequisite) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisss", $courseCode, $courseTitle, $units, $description, $department, $prerequisite);
            
            if ($stmt->execute()) {
                $successMessage = "Course added successfully!";
            } else {
                $errorMessage = "Error adding course: " . $conn->error;
            }
            
            $stmt->close();
            */
            
            // For demonstration purposes
            $successMessage = "Course added successfully!";
        }
        
        $checkStmt->close();
    }
    
    // Edit course
    if (isset($_POST['edit_course'])) {
        $courseId = intval($_POST['course_id']);
        $courseCode = $conn->real_escape_string($_POST['course_code']);
        $courseTitle = $conn->real_escape_string($_POST['course_title']);
        $units = intval($_POST['units']);
        $description = $conn->real_escape_string($_POST['description']);
        $department = $conn->real_escape_string($_POST['department']);
        $prerequisite = $conn->real_escape_string($_POST['prerequisite']);
        
        // Check if course code already exists for other courses
        $checkStmt = $conn->prepare("SELECT CourseID FROM Courses WHERE CourseCode = ? AND CourseID != ?");
        $checkStmt->bind_param("si", $courseCode, $courseId);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Course code already exists. Please use a different code.";
        } else {
            // In a real application, you would update the course in the database
            /*
            $stmt = $conn->prepare("UPDATE Courses SET CourseCode = ?, CourseTitle = ?, Units = ?, Description = ?, Department = ?, Prerequisite = ? WHERE CourseID = ?");
            $stmt->bind_param("ssisssi", $courseCode, $courseTitle, $units, $description, $department, $prerequisite, $courseId);
            
            if ($stmt->execute()) {
                $successMessage = "Course updated successfully!";
            } else {
                $errorMessage = "Error updating course: " . $conn->error;
            }
            
            $stmt->close();
            */
            
            // For demonstration purposes
            $successMessage = "Course updated successfully!";
        }
        
        $checkStmt->close();
    }
    
    // Delete course
    if (isset($_POST['delete_course'])) {
        $courseId = intval($_POST['course_id']);
        
        // In a real application, you would delete the course from the database
        /*
        $stmt = $conn->prepare("DELETE FROM Courses WHERE CourseID = ?");
        $stmt->bind_param("i", $courseId);
        
        if ($stmt->execute()) {
            $successMessage = "Course deleted successfully!";
        } else {
            $errorMessage = "Error deleting course: " . $conn->error;
        }
        
        $stmt->close();
        */
        
        // For demonstration purposes
        $successMessage = "Course deleted successfully!";
    }
    
    $conn->close();
}

// Get courses with filtering and pagination
function getCourses($search = '', $department = '', $page = 1, $perPage = 10) {
    $conn = connectDB();
    $courses = [];
    $totalCourses = 0;
    
    // In a real application, you would fetch courses from the database
    /*
    $offset = ($page - 1) * $perPage;
    
    // Build the query
    $query = "SELECT * FROM Courses WHERE 1=1";
    $countQuery = "SELECT COUNT(*) as total FROM Courses WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $query .= " AND (CourseCode LIKE ? OR CourseTitle LIKE ?)";
        $countQuery .= " AND (CourseCode LIKE ? OR CourseTitle LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    if (!empty($department)) {
        $query .= " AND Department = ?";
        $countQuery .= " AND Department = ?";
        $params[] = $department;
        $types .= "s";
    }
    
    $query .= " ORDER BY CourseCode LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $perPage;
    $types .= "ii";
    
    // Get total count
    $countStmt = $conn->prepare($countQuery);
    if (!empty($types)) {
        $countStmt->bind_param(substr($types, 0, -2), ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalCourses = $countResult->fetch_assoc()['total'];
    $countStmt->close();
    
    // Get courses
    $stmt = $conn->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    
    $stmt->close();
    */
    
    // For demonstration purposes, use sample data
    $totalCourses = 15;
    
    // Filter by search term
    $allCourses = [
        [
            'CourseID' => 1,
            'CourseCode' => 'COMP101',
            'CourseTitle' => 'Introduction to Computing',
            'Units' => 3,
            'Description' => 'This course introduces the fundamental concepts of computing and programming.',
            'Department' => 'Computer Studies',
            'Prerequisite' => 'None'
        ],
        [
            'CourseID' => 2,
            'CourseCode' => 'COMP102',
            'CourseTitle' => 'Computer Programming 1',
            'Units' => 3,
            'Description' => 'This course covers the basics of programming using a high-level language.',
            'Department' => 'Computer Studies',
            'Prerequisite' => 'COMP101'
        ],
        [
            'CourseID' => 3,
            'CourseCode' => 'MATH101',
            'CourseTitle' => 'College Algebra',
            'Units' => 3,
            'Description' => 'This course covers algebraic expressions, equations, inequalities, and functions.',
            'Department' => 'Mathematics',
            'Prerequisite' => 'None'
        ],
        [
            'CourseID' => 4,
            'CourseCode' => 'MATH102',
            'CourseTitle' => 'Trigonometry',
            'Units' => 3,
            'Description' => 'This course covers trigonometric functions, identities, and equations.',
            'Department' => 'Mathematics',
            'Prerequisite' => 'MATH101'
        ],
        [
            'CourseID' => 5,
            'CourseCode' => 'ENG101',
            'CourseTitle' => 'English Communication Skills',
            'Units' => 3,
            'Description' => 'This course develops effective communication skills in English.',
            'Department' => 'Languages',
            'Prerequisite' => 'None'
        ],
        [
            'CourseID' => 6,
            'CourseCode' => 'ENG102',
            'CourseTitle' => 'Writing in the Discipline',
            'Units' => 3,
            'Description' => 'This course focuses on academic and professional writing.',
            'Department' => 'Languages',
            'Prerequisite' => 'ENG101'
        ],
        [
            'CourseID' => 7,
            'CourseCode' => 'FIL101',
            'CourseTitle' => 'Komunikasyon sa Akademikong Filipino',
            'Units' => 3,
            'Description' => 'This course develops effective communication skills in Filipino.',
            'Department' => 'Languages',
            'Prerequisite' => 'None'
        ],
        [
            'CourseID' => 8,
            'CourseCode' => 'NSTP1',
            'CourseTitle' => 'National Service Training Program 1',
            'Units' => 3,
            'Description' => 'This course focuses on civic consciousness and defense preparedness.',
            'Department' => 'Social Sciences',
            'Prerequisite' => 'None'
        ],
        [
            'CourseID' => 9,
            'CourseCode' => 'NSTP2',
            'CourseTitle' => 'National Service Training Program 2',
            'Units' => 3,
            'Description' => 'This course is a continuation of NSTP1.',
            'Department' => 'Social Sciences',
            'Prerequisite' => 'NSTP1'
        ],
        [
            'CourseID' => 10,
            'CourseCode' => 'COMP201',
            'CourseTitle' => 'Data Structures and Algorithms',
            'Units' => 3,
            'Description' => 'This course covers fundamental data structures and algorithms.',
            'Department' => 'Computer Studies',
            'Prerequisite' => 'COMP102'
        ],
        [
            'CourseID' => 11,
            'CourseCode' => 'COMP202',
            'CourseTitle' => 'Object-Oriented Programming',
            'Units' => 3,
            'Description' => 'This course covers object-oriented programming concepts and techniques.',
            'Department' => 'Computer Studies',
            'Prerequisite' => 'COMP102'
        ],
        [
            'CourseID' => 12,
            'CourseCode' => 'COMP301',
            'CourseTitle' => 'Database Management Systems',
            'Units' => 3,
            'Description' => 'This course covers database design, implementation, and management.',
            'Department' => 'Computer Studies',
            'Prerequisite' => 'COMP201'
        ],
        [
            'CourseID' => 13,
            'CourseCode' => 'COMP302',
            'CourseTitle' => 'Web Development',
            'Units' => 3,
            'Description' => 'This course covers client-side and server-side web development.',
            'Department' => 'Computer Studies',
            'Prerequisite' => 'COMP201'
        ],
        [
            'CourseID' => 14,
            'CourseCode' => 'COMP401',
            'CourseTitle' => 'Software Engineering',
            'Units' => 3,
            'Description' => 'This course covers software development methodologies and practices.',
            'Department' => 'Computer Studies',
            'Prerequisite' => 'COMP301'
        ],
        [
            'CourseID' => 15,
            'CourseCode' => 'COMP402',
            'CourseTitle' => 'Capstone Project',
            'Units' => 6,
            'Description' => 'This course requires students to develop a comprehensive software project.',
            'Department' => 'Computer Studies',
            'Prerequisite' => 'COMP401'
        ]
    ];
    
    // Filter by search term
    if (!empty($search)) {
        $filteredCourses = [];
        foreach ($allCourses as $course) {
            if (stripos($course['CourseCode'], $search) !== false || 
                stripos($course['CourseTitle'], $search) !== false) {
                $filteredCourses[] = $course;
            }
        }
        $allCourses = $filteredCourses;
        $totalCourses = count($allCourses);
    }
    
    // Filter by department
    if (!empty($department)) {
        $filteredCourses = [];
        foreach ($allCourses as $course) {
            if ($course['Department'] === $department) {
                $filteredCourses[] = $course;
            }
        }
        $allCourses = $filteredCourses;
        $totalCourses = count($allCourses);
    }
    
    // Paginate
    $offset = ($page - 1) * $perPage;
    $courses = array_slice($allCourses, $offset, $perPage);
    
    $conn->close();
    
    return [
        'courses' => $courses,
        'total' => $totalCourses
    ];
}

// Get available departments
function getDepartments() {
    $conn = connectDB();
    $departments = [];
    
    // In a real application, you would fetch departments from the database
    /*
    $stmt = $conn->prepare("SELECT DISTINCT Department FROM Courses ORDER BY Department");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['Department'];
    }
    
    $stmt->close();
    */
    
    // For demonstration purposes
    $departments = [
        'Computer Studies',
        'Mathematics',
        'Languages',
        'Social Sciences',
        'Natural Sciences',
        'Business'
    ];
    
    $conn->close();
    return $departments;
}

// Process filters and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Get courses
$coursesData = getCourses($search, $department, $page, $perPage);
$courses = $coursesData['courses'];
$totalCourses = $coursesData['total'];
$totalPages = ceil($totalCourses / $perPage);

// Get departments
$departments = getDepartments();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
        /* Courses Page Specific Styles */
        .courses-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .courses-header {
            margin-bottom: 30px;
        }

        .courses-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .courses-header p {
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

        .courses-table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .courses-table {
            width: 100%;
            border-collapse: collapse;
        }

        .courses-table th,
        .courses-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .courses-table th {
            background-color: #f1f3f9;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }

        .courses-table tr:last-child td {
            border-bottom: none;
        }

        .courses-table tr:hover {
            background-color: #f9f9f9;
        }

        .course-actions {
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
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

        .error-message {
            color: #ef476f;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .no-courses {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        .no-courses i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .no-courses p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .no-courses span {
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
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="courses-container">
            <div class="courses-header">
                <h1>Course Management</h1>
                <p>Add, edit, and manage courses offered by the college</p>
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
                    <form class="search-form" method="get" action="courses.php">
                        <input type="text" name="search" class="search-input" placeholder="Search courses..." value="<?php echo htmlspecialchars($search); ?>">
                        <select name="department" class="filter-select">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept; ?>" <?php echo $department == $dept ? 'selected' : ''; ?>><?php echo $dept; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline btn-icon">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
                
                <?php if ($isAdmin): ?>
                <button id="addCourseBtn" class="btn btn-primary btn-icon">
                    <i class="fas fa-plus"></i> Add New Course
                </button>
                <?php endif; ?>
            </div>
            
            <div class="courses-table-container">
                <?php if (empty($courses)): ?>
                    <div class="no-courses">
                        <i class="fas fa-book"></i>
                        <p>No courses found</p>
                        <span>Try adjusting your search or filter criteria.</span>
                    </div>
                <?php else: ?> 
                    <table class="courses-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th>Units</th>
                                <th>Department</th>
                                <th>Prerequisite</th>
                                <?php if ($isAdmin): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                              
                          
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo $course['CourseCode']; ?></td>
                                    <td><?php echo $course['CourseTitle']; ?></td>
                                    <td><?php echo $course['Units']; ?></td>
                                    <td><?php echo $course['Department']; ?></td>
                                    <td><?php echo $course['Prerequisite']; ?></td>
                                    <?php if ($isAdmin): ?>
                                    <td class="course-actions">
                                        <button class="action-btn edit-btn edit-course-btn" data-id="<?php echo $course['CourseID']; ?>" data-code="<?php echo $course['CourseCode']; ?>" data-title="<?php echo $course['CourseTitle']; ?>" data-units="<?php echo $course['Units']; ?>" data-description="<?php echo htmlspecialchars($course['Description']); ?>" data-department="<?php echo $course['Department']; ?>" data-prerequisite="<?php echo $course['Prerequisite']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn delete-course-btn" data-id="<?php echo $course['CourseID']; ?>" data-code="<?php echo $course['CourseCode']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <a href="?search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department); ?>&page=1" class="pagination-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department); ?>&page=<?php echo $page - 1; ?>" class="pagination-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <a href="?search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department); ?>&page=<?php echo $i; ?>" class="pagination-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <a href="?search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department); ?>&page=<?php echo $page + 1; ?>" class="pagination-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department); ?>&page=<?php echo $totalPages; ?>" class="pagination-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Add Course Modal -->
        <div class="modal-overlay" id="addCourseModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Add New Subject</h2>
                    <button class="modal-close" id="closeAddCourseModal">&times;</button>
                </div>
                <form id="addCourseForm" method="post">
                    <input type="hidden" name="add_course" value="1">
                    <div class="form-group">
                        <label for="course_code">Subject Code</label>
                        <input type="text" id="course_code" name="course_code" required>
                    </div>
                    <div class="form-group">
                        <label for="course_title">Course Title</label>
                        <input type="text" id="course_title" name="course_title" required>
                    </div>
                    <div class="form-group">
                        <label for="units">Units</label>
                        <input type="number" id="units" name="units" min="1" max="6" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="prerequisite">Prerequisite</label>
                        <input type="text" id="prerequisite" name="prerequisite" placeholder="None">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelAddCourseBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Edit Course Modal -->
        <div class="modal-overlay" id="editCourseModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Course</h2>
                    <button class="modal-close" id="closeEditCourseModal">&times;</button>
                </div>
                <form id="editCourseForm" method="post">
                    <input type="hidden" name="edit_course" value="1">
                    <input type="hidden" id="edit_course_id" name="course_id">
                    <div class="form-group">
                        <label for="edit_course_code">Course Code</label>
                        <input type="text" id="edit_course_code" name="course_code" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_course_title">Course Title</label>
                        <input type="text" id="edit_course_title" name="course_title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_units">Units</label>
                        <input type="number" id="edit_units" name="units" min="1" max="6" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_department">Department</label>
                        <select id="edit_department" name="department" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_prerequisite">Prerequisite</label>
                        <input type="text" id="edit_prerequisite" name="prerequisite" placeholder="None">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelEditCourseBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Course</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Delete Course Modal -->
        <div class="modal-overlay" id="deleteCourseModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Course</h2>
                    <button class="modal-close" id="closeDeleteCourseModal">&times;</button>
                </div>
                <form id="deleteCourseForm" method="post">
                    <input type="hidden" name="delete_course" value="1">
                    <input type="hidden" id="delete_course_id" name="course_id">
                    <p>Are you sure you want to delete the course <strong id="delete_course_code"></strong>?</p>
                    <p>This action cannot be undone.</p>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelDeleteCourseBtn">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Course</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add Course Modal
            const addCourseBtn = document.getElementById('addCourseBtn');
            const addCourseModal = document.getElementById('addCourseModal');
            const closeAddCourseModal = document.getElementById('closeAddCourseModal');
            const cancelAddCourseBtn = document.getElementById('cancelAddCourseBtn');
            
            if (addCourseBtn) {
                addCourseBtn.addEventListener('click', function() {
                    addCourseModal.classList.add('active');
                });
            }
            
            if (closeAddCourseModal) {
                closeAddCourseModal.addEventListener('click', function() {
                    addCourseModal.classList.remove('active');
                });
            }
            
            if (cancelAddCourseBtn) {
                cancelAddCourseBtn.addEventListener('click', function() {
                    addCourseModal.classList.remove('active');
                });
            }
            
            // Edit Course Modal
            const editCourseBtns = document.querySelectorAll('.edit-course-btn');
            const editCourseModal = document.getElementById('editCourseModal');
            const closeEditCourseModal = document.getElementById('closeEditCourseModal');
            const cancelEditCourseBtn = document.getElementById('cancelEditCourseBtn');
            const editCourseId = document.getElementById('edit_course_id');
            const editCourseCode = document.getElementById('edit_course_code');
            const editCourseTitle = document.getElementById('edit_course_title');
            const editUnits = document.getElementById('edit_units');
            const editDescription = document.getElementById('edit_description');
            const editDepartment = document.getElementById('edit_department');
            const editPrerequisite = document.getElementById('edit_prerequisite');
            
            editCourseBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const code = this.getAttribute('data-code');
                    const title = this.getAttribute('data-title');
                    const units = this.getAttribute('data-units');
                    const description = this.getAttribute('data-description');
                    const department = this.getAttribute('data-department');
                    const prerequisite = this.getAttribute('data-prerequisite');
                    
                    editCourseId.value = id;
                    editCourseCode.value = code;
                    editCourseTitle.value = title;
                    editUnits.value = units;
                    editDescription.value = description;
                    editDepartment.value = department;
                    editPrerequisite.value = prerequisite;
                    
                    editCourseModal.classList.add('active');
                });
            });
            
            if (closeEditCourseModal) {
                closeEditCourseModal.addEventListener('click', function() {
                    editCourseModal.classList.remove('active');
                });
            }
            
            if (cancelEditCourseBtn) {
                cancelEditCourseBtn.addEventListener('click', function() {
                    editCourseModal.classList.remove('active');
                });
            }
            
            // Delete Course Modal
            const deleteCourseBtns = document.querySelectorAll('.delete-course-btn');
            const deleteCourseModal = document.getElementById('deleteCourseModal');
            const closeDeleteCourseModal = document.getElementById('closeDeleteCourseModal');
            const cancelDeleteCourseBtn = document.getElementById('cancelDeleteCourseBtn');
            const deleteCourseId = document.getElementById('delete_course_id');
            const deleteCourseCode = document.getElementById('delete_course_code');
            
            deleteCourseBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const code = this.getAttribute('data-code');
                    
                    deleteCourseId.value = id;
                    deleteCourseCode.textContent = code;
                    
                    deleteCourseModal.classList.add('active');
                });
            });
            
            if (closeDeleteCourseModal) {
                closeDeleteCourseModal.addEventListener('click', function() {
                    deleteCourseModal.classList.remove('active');
                });
            }
            
            if (cancelDeleteCourseBtn) {
                cancelDeleteCourseBtn.addEventListener('click', function() {
                    deleteCourseModal.classList.remove('active');
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