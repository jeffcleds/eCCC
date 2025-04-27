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

// Handle course operations (add, edit, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = connectDB();
    
    // Add new course
    if (isset($_POST['add_course'])) {
        $courseName = $conn->real_escape_string($_POST['course_name']);
        
        // Check if course name already exists
        $checkStmt = $conn->prepare("SELECT CourseID FROM Course WHERE CourseName = ?");
        $checkStmt->bind_param("s", $courseName);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Course name already exists. Please use a different name.";
        } else {
            // Insert into Course table - only CourseName
            $stmt = $conn->prepare("INSERT INTO Course (CourseName) VALUES (?)");
            $stmt->bind_param("s", $courseName);
            
            if ($stmt->execute()) {
                $successMessage = "Course added successfully!";
            } else {
                $errorMessage = "Error adding course: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Edit course
    if (isset($_POST['edit_course'])) {
        $courseId = intval($_POST['course_id']);
        $courseName = $conn->real_escape_string($_POST['course_name']);
        
        // Check if course name already exists for other courses
        $checkStmt = $conn->prepare("SELECT CourseID FROM Course WHERE CourseName = ? AND CourseID != ?");
        $checkStmt->bind_param("si", $courseName, $courseId);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMessage = "Course name already exists. Please use a different name.";
        } else {
            // Update Course table - only CourseName
            $stmt = $conn->prepare("UPDATE Course SET CourseName = ? WHERE CourseID = ?");
            $stmt->bind_param("si", $courseName, $courseId);
            
            if ($stmt->execute()) {
                $successMessage = "Course updated successfully!";
            } else {
                $errorMessage = "Error updating course: " . $stmt->error;
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Delete course
    if (isset($_POST['delete_course'])) {
        $courseId = intval($_POST['course_id']);
        
        // Check if course is being used by students
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM Students WHERE CourseID = ?");
        $checkStmt->bind_param("i", $courseId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $studentCount = $result->fetch_assoc()['count'];
        $checkStmt->close();
        
        // Check if course is being used by subjects
        $checkSubjectsStmt = $conn->prepare("SELECT COUNT(*) as count FROM Subjects WHERE CourseID = ?");
        $checkSubjectsStmt->bind_param("i", $courseId);
        $checkSubjectsStmt->execute();
        $result = $checkSubjectsStmt->get_result();
        $subjectCount = $result->fetch_assoc()['count'];
        $checkSubjectsStmt->close();
        
        if ($studentCount > 0) {
            $errorMessage = "Cannot delete course. It is currently assigned to {$studentCount} student(s).";
        } elseif ($subjectCount > 0) {
            $errorMessage = "Cannot delete course. It is currently used by {$subjectCount} subject(s).";
        } else {
            $stmt = $conn->prepare("DELETE FROM Course WHERE CourseID = ?");
            $stmt->bind_param("i", $courseId);
            
            if ($stmt->execute()) {
                $successMessage = "Course deleted successfully!";
            } else {
                $errorMessage = "Error deleting course: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
    
    $conn->close();
}

// Get courses with filtering and pagination
function getCourses($search = '', $page = 1, $perPage = 10) {
    $conn = connectDB();
    $courses = [];
    $totalCourses = 0;
    
    $offset = ($page - 1) * $perPage;
    
    // Build the query - adjusted to match your actual database schema
    // Only select columns that exist in the Course table
    $query = "SELECT c.CourseID, c.CourseName, 
                     (SELECT COUNT(*) FROM Students WHERE Students.CourseID = c.CourseID) as StudentCount,
                     (SELECT COUNT(*) FROM Subjects WHERE Subjects.CourseID = c.CourseID) as SubjectCount
              FROM Course c";
    
    $countQuery = "SELECT COUNT(*) as total FROM Course";
    
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $query .= " WHERE c.CourseName LIKE ?";
        $countQuery .= " WHERE CourseName LIKE ?";
        $params[] = $searchTerm;
        $types .= "s";
    }
    
    $query .= " ORDER BY c.CourseName LIMIT ?, ?";
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
    $conn->close();
    
    return [
        'courses' => $courses,
        'total' => $totalCourses
    ];
}

// Process filters and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Get courses
$coursesData = getCourses($search, $page, $perPage);
$courses = $coursesData['courses'];
$totalCourses = $coursesData['total'];
$totalPages = ceil($totalCourses / $perPage);

require_once 'session_init.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
        /* Course Management Page Specific Styles */
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

        .course-details {
            margin-bottom: 20px;
        }

        .course-details-section {
            margin-bottom: 20px;
        }

        .course-details-section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .course-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .course-info-item {
            margin-bottom: 10px;
        }

        .course-info-label {
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .course-info-value {
            color: #333;
            font-size: 1rem;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-primary {
            background-color: #e6effd;
            color: #4361ee;
        }

        .badge-secondary {
            background-color: #e6f7ed;
            color: #0d8a53;
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
            
            .course-info-grid {
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
        
        <div class="courses-container">
            <div class="courses-header">
                <h1>Course Management</h1>
                <p>View, add, and manage courses</p>
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
                <form class="search-form" method="get" action="courses.php">
                    <input type="text" name="search" class="search-input" placeholder="Search courses..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline btn-icon">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
                
                <button id="addCourseBtn" class="btn btn-primary btn-icon">
                    <i class="fas fa-plus"></i> Add New Course
                </button>
            </div>
            
            <div class="courses-table-container">
                <?php if (empty($courses)): ?>
                    <div class="no-courses">
                        <i class="fas fa-book"></i>
                        <p>No courses found</p>
                        <span>Try adjusting your search criteria or add a new course.</span>
                    </div>
                <?php else: ?>
                    <table class="courses-table">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Students</th>
                                <th>Subjects</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo $course['CourseName']; ?></td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?php echo $course['StudentCount']; ?> student<?php echo $course['StudentCount'] != 1 ? 's' : ''; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?php echo $course['SubjectCount']; ?> subject<?php echo $course['SubjectCount'] != 1 ? 's' : ''; ?>
                                        </span>
                                    </td>
                                    <td class="course-actions">
                                        <button class="action-btn view-btn view-course-btn" 
                                            data-id="<?php echo $course['CourseID']; ?>"
                                            data-name="<?php echo $course['CourseName']; ?>"
                                            data-students="<?php echo $course['StudentCount']; ?>"
                                            data-subjects="<?php echo $course['SubjectCount']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn edit-btn edit-course-btn" 
                                            data-id="<?php echo $course['CourseID']; ?>"
                                            data-name="<?php echo $course['CourseName']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn delete-course-btn" 
                                            data-id="<?php echo $course['CourseID']; ?>"
                                            data-name="<?php echo $course['CourseName']; ?>"
                                            data-students="<?php echo $course['StudentCount']; ?>"
                                            data-subjects="<?php echo $course['SubjectCount']; ?>">
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
        
        <!-- View Course Modal -->
        <div class="modal-overlay" id="viewCourseModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Course Details</h2>
                    <button class="modal-close" id="closeViewCourseModal">&times;</button>
                </div>
                <div class="course-details">
                    <div class="course-details-section">
                        <h3>Course Information</h3>
                        <div class="course-info-grid">
                            <div class="course-info-item">
                                <div class="course-info-label">Course Name</div>
                                <div class="course-info-value" id="view-course-name"></div>
                            </div>
                            <div class="course-info-item">
                                <div class="course-info-label">Enrolled Students</div>
                                <div class="course-info-value" id="view-students"></div>
                            </div>
                            <div class="course-info-item">
                                <div class="course-info-label">Subjects</div>
                                <div class="course-info-value" id="view-subjects"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="closeViewCourseBtn">Close</button>
                    <button type="button" class="btn btn-primary" id="viewToEditBtn">Edit</button>
                </div>
            </div>
        </div>
        
        <!-- Add Course Modal -->
        <div class="modal-overlay" id="addCourseModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Add New Course</h2>
                    <button class="modal-close" id="closeAddCourseModal">&times;</button>
                </div>
                <form id="addCourseForm" method="post">
                    <input type="hidden" name="add_course" value="1">
                    
                    <div class="course-details-section">
                        <h3>Course Information</h3>
                        <div class="form-group">
                            <label for="course_name">Course Name</label>
                            <input type="text" id="course_name" name="course_name" required>
                        </div>
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
                    
                    <div class="course-details-section">
                        <h3>Course Information</h3>
                        <div class="form-group">
                            <label for="edit_course_name">Course Name</label>
                            <input type="text" id="edit_course_name" name="course_name" required>
                        </div>
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
                    <p>Are you sure you want to delete the course <strong id="delete_course_name"></strong>?</p>
                    <div id="delete_warning" style="display: none;">
                        <p class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <span id="delete_warning_message"></span>
                        </p>
                    </div>
                    <div id="delete_confirm" style="display: block;">
                        <p>This action cannot be undone.</p>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" id="cancelDeleteCourseBtn">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete Course</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View Course Modal
            const viewCourseBtns = document.querySelectorAll('.view-course-btn');
            const viewCourseModal = document.getElementById('viewCourseModal');
            const closeViewCourseModal = document.getElementById('closeViewCourseModal');
            const closeViewCourseBtn = document.getElementById('closeViewCourseBtn');
            const viewToEditBtn = document.getElementById('viewToEditBtn');
            
            viewCourseBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const courseId = this.getAttribute('data-id');
                    const courseName = this.getAttribute('data-name');
                    const students = this.getAttribute('data-students');
                    const subjects = this.getAttribute('data-subjects');
                    
                    // Populate view modal
                    document.getElementById('view-course-name').textContent = courseName;
                    document.getElementById('view-students').textContent = students + ' student' + (students != 1 ? 's' : '');
                    document.getElementById('view-subjects').textContent = subjects + ' subject' + (subjects != 1 ? 's' : '');
                    
                    // Store data for edit button
                    viewToEditBtn.setAttribute('data-id', courseId);
                    
                    viewCourseModal.classList.add('active');
                });
            });
            
            if (closeViewCourseModal) {
                closeViewCourseModal.addEventListener('click', function() {
                    viewCourseModal.classList.remove('active');
                });
            }
            
            if (closeViewCourseBtn) {
                closeViewCourseBtn.addEventListener('click', function() {
                    viewCourseModal.classList.remove('active');
                });
            }
            
            // View to Edit
            if (viewToEditBtn) {
                viewToEditBtn.addEventListener('click', function() {
                    const courseId = this.getAttribute('data-id');
                    
                    // Find the edit button with matching data and trigger click
                    const editBtn = document.querySelector(`.edit-course-btn[data-id="${courseId}"]`);
                    if (editBtn) {
                        viewCourseModal.classList.remove('active');
                        editBtn.click();
                    }
                });
            }
            
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
            
            editCourseBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const courseId = this.getAttribute('data-id');
                    const courseName = this.getAttribute('data-name');
                    
                    // Populate edit form
                    document.getElementById('edit_course_id').value = courseId;
                    document.getElementById('edit_course_name').value = courseName;
                    
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
            
            deleteCourseBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const courseId = this.getAttribute('data-id');
                    const courseName = this.getAttribute('data-name');
                    const students = parseInt(this.getAttribute('data-students'));
                    const subjects = parseInt(this.getAttribute('data-subjects'));
                    
                    document.getElementById('delete_course_id').value = courseId;
                    document.getElementById('delete_course_name').textContent = courseName;
                    
                    // Check if course has students or subjects
                    if (students > 0 || subjects > 0) {
                        let warningMessage = '';
                        if (students > 0) {
                            warningMessage = `This course has ${students} student${students != 1 ? 's' : ''} enrolled. You cannot delete a course with enrolled students.`;
                        } else if (subjects > 0) {
                            warningMessage = `This course has ${subjects} subject${subjects != 1 ? 's' : ''} assigned. You cannot delete a course with assigned subjects.`;
                        }
                        document.getElementById('delete_warning_message').textContent = warningMessage;
                        document.getElementById('delete_warning').style.display = 'block';
                        document.getElementById('delete_confirm').style.display = 'none';
                    } else {
                        document.getElementById('delete_warning').style.display = 'none';
                        document.getElementById('delete_confirm').style.display = 'block';
                    }
                    
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