<?php
include 'session_init.php';
// Start session if not already started

// Check if user is admin


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

// Get current academic year and semester
function getCurrentAcademicYear() {
    $month = date('n');
    $year = date('Y');
    
    // First semester: June to October, Second semester: November to March
    if ($month >= 6 && $month <= 10) {
        return [
            'year' => $year . '-' . ($year + 1),
            'semester' => '1st Semester'
        ];
    } else if (($month >= 11 && $month <= 12) || ($month >= 1 && $month <= 3)) {
        return [
            'year' => ($month >= 11 ? $year : $year - 1) . '-' . ($month >= 11 ? $year + 1 : $year),
            'semester' => '2nd Semester'
        ];
    } else {
        // Summer term: April to May
        return [
            'year' => $year . '-' . ($year + 1),
            'semester' => 'Summer'
        ];
    }
}

// Get available academic years from database
function getAvailableAcademicYears() {
    $conn = connectDB();
    $years = [];
    
    // In a real application, you would fetch this from the database
    // For now, we'll use sample data
    $years = [
        '2023-2024',
        '2022-2023',
        '2021-2022'
    ];
    
    $conn->close();
    return $years;
}

// Get available semesters
function getAvailableSemesters() {
    return [
        '1st Semester',
        '2nd Semester',
        'Summer'
    ];
}

// Search students by ID or name
function searchStudents($searchTerm) {
    $conn = connectDB();
    $students = [];
    
    // In a real application, you would fetch this from the database using a query like:
    /*
    $searchTerm = '%' . $conn->real_escape_string($searchTerm) . '%';
    $stmt = $conn->prepare("
        SELECT IDNumber, FirstName, MiddleInitial, LastName, Program, YearLevel
        FROM Users
        WHERE (IDNumber LIKE ? OR FirstName LIKE ? OR LastName LIKE ?)
        AND Role = 'student'
        ORDER BY LastName, FirstName
        LIMIT 20
    ");
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    $stmt->close();
    */
    
    // For now, we'll use sample data
    if (stripos('2023-0001', $searchTerm) !== false || 
        stripos('John', $searchTerm) !== false || 
        stripos('Doe', $searchTerm) !== false) {
        $students[] = [
            'IDNumber' => '2023-0001',
            'FirstName' => 'John',
            'MiddleInitial' => 'A',
            'LastName' => 'Doe',
            'Program' => 'Bachelor of Science in Information Technology',
            'YearLevel' => '2nd Year'
        ];
    }
    
    if (stripos('2023-0002', $searchTerm) !== false || 
        stripos('Jane', $searchTerm) !== false || 
        stripos('Smith', $searchTerm) !== false) {
        $students[] = [
            'IDNumber' => '2023-0002',
            'FirstName' => 'Jane',
            'MiddleInitial' => 'B',
            'LastName' => 'Smith',
            'Program' => 'Bachelor of Science in Computer Science',
            'YearLevel' => '1st Year'
        ];
    }
    
    if (stripos('2023-0003', $searchTerm) !== false || 
        stripos('Michael', $searchTerm) !== false || 
        stripos('Johnson', $searchTerm) !== false) {
        $students[] = [
            'IDNumber' => '2023-0003',
            'FirstName' => 'Michael',
            'MiddleInitial' => 'C',
            'LastName' => 'Johnson',
            'Program' => 'Bachelor of Science in Information Systems',
            'YearLevel' => '3rd Year'
        ];
    }
    
    if (stripos('2023-0004', $searchTerm) !== false || 
        stripos('Sarah', $searchTerm) !== false || 
        stripos('Williams', $searchTerm) !== false) {
        $students[] = [
            'IDNumber' => '2023-0004',
            'FirstName' => 'Sarah',
            'MiddleInitial' => 'D',
            'LastName' => 'Williams',
            'Program' => 'Bachelor of Science in Information Technology',
            'YearLevel' => '2nd Year'
        ];
    }
    
    if (stripos('2023-0005', $searchTerm) !== false || 
        stripos('Robert', $searchTerm) !== false || 
        stripos('Brown', $searchTerm) !== false) {
        $students[] = [
            'IDNumber' => '2023-0005',
            'FirstName' => 'Robert',
            'MiddleInitial' => 'E',
            'LastName' => 'Brown',
            'Program' => 'Bachelor of Science in Computer Science',
            'YearLevel' => '4th Year'
        ];
    }
    
    $conn->close();
    return $students;
}

// Get student grades
function getStudentGrades($studentId, $academicYear, $semester, $quarter = null) {
    $conn = connectDB();
    $grades = [];
    
    // In a real application, you would fetch this from the database using a query like:
    /*
    $stmt = $conn->prepare("
        SELECT c.CourseCode, c.CourseTitle, c.Units, g.Prelim, g.Midterm, g.Prefinal, g.Final, g.FinalGrade, g.Remarks
        FROM Grades g
        JOIN Courses c ON g.CourseID = c.CourseID
        WHERE g.StudentID = ? AND g.AcademicYear = ? AND g.Semester = ?
        ORDER BY c.CourseCode
    ");
    $stmt->bind_param("sss", $studentId, $academicYear, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    
    $stmt->close();
    */
    
    // For now, we'll use sample data
    if ($studentId == '2023-0001' && $academicYear == '2023-2024' && $semester == '1st Semester') {
        $grades = [
            [
                'CourseCode' => 'COMP101',
                'CourseTitle' => 'Introduction to Computing',
                'Units' => 3,
                'Prelim' => 92,
                'Midterm' => 88,
                'Prefinal' => 90,
                'Final' => 94,
                'FinalGrade' => 91,
                'Remarks' => 'Passed'
            ],
            [
                'CourseCode' => 'MATH101',
                'CourseTitle' => 'College Algebra',
                'Units' => 3,
                'Prelim' => 85,
                'Midterm' => 82,
                'Prefinal' => 88,
                'Final' => 90,
                'FinalGrade' => 86.25,
                'Remarks' => 'Passed'
            ],
            [
                'CourseCode' => 'ENG101',
                'CourseTitle' => 'English Communication Skills',
                'Units' => 3,
                'Prelim' => 90,
                'Midterm' => 92,
                'Prefinal' => 88,
                'Final' => 91,
                'FinalGrade' => 90.25,
                'Remarks' => 'Passed'
            ],
            [
                'CourseCode' => 'FIL101',
                'CourseTitle' => 'Komunikasyon sa Akademikong Filipino',
                'Units' => 3,
                'Prelim' => 88,
                'Midterm' => 85,
                'Prefinal' => 87,
                'Final' => 89,
                'FinalGrade' => 87.25,
                'Remarks' => 'Passed'
            ],
            [
                'CourseCode' => 'NSTP1',
                'CourseTitle' => 'National Service Training Program 1',
                'Units' => 3,
                'Prelim' => 95,
                'Midterm' => 92,
                'Prefinal' => 94,
                'Final' => 96,
                'FinalGrade' => 94.25,
                'Remarks' => 'Passed'
            ]
        ];
    } else if ($studentId == '2023-0002' && $academicYear == '2023-2024' && $semester == '1st Semester') {
        $grades = [
            [
                'CourseCode' => 'COMP101',
                'CourseTitle' => 'Introduction to Computing',
                'Units' => 3,
                'Prelim' => 88,
                'Midterm' => 85,
                'Prefinal' => 82,
                'Final' => 87,
                'FinalGrade' => 85.5,
                'Remarks' => 'Passed'
            ],
            [
                'CourseCode' => 'MATH101',
                'CourseTitle' => 'College Algebra',
                'Units' => 3,
                'Prelim' => 90,
                'Midterm' => 92,
                'Prefinal' => 88,
                'Final' => 91,
                'FinalGrade' => 90.25,
                'Remarks' => 'Passed'
            ],
            [
                'CourseCode' => 'ENG101',
                'CourseTitle' => 'English Communication Skills',
                'Units' => 3,
                'Prelim' => 85,
                'Midterm' => 82,
                'Prefinal' => 80,
                'Final' => 84,
                'FinalGrade' => 82.75,
                'Remarks' => 'Passed'
            ]
        ];
    } else if ($studentId == '2023-0003' && $academicYear == '2023-2024' && $semester == '1st Semester') {
        $grades = [
            [
                'CourseCode' => 'COMP301',
                'CourseTitle' => 'Data Structures and Algorithms',
                'Units' => 3,
                'Prelim' => 78,
                'Midterm' => 82,
                'Prefinal' => 80,
                'Final' => 85,
                'FinalGrade' => 81.25,
                'Remarks' => 'Passed'
            ],
            [
                'CourseCode' => 'MATH301',
                'CourseTitle' => 'Discrete Mathematics',
                'Units' => 3,
                'Prelim' => 75,
                'Midterm' => 72,
                'Prefinal' => 70,
                'Final' => 74,
                'FinalGrade' => 72.75,
                'Remarks' => 'Failed'
            ]
        ];
    }
    
    $conn->close();
    return $grades;
}

// Calculate GPA
function calculateGPA($grades) {
    if (empty($grades)) {
        return 0;
    }
    
    $totalPoints = 0;
    $totalUnits = 0;
    
    foreach ($grades as $grade) {
        $totalPoints += $grade['FinalGrade'] * $grade['Units'];
        $totalUnits += $grade['Units'];
    }
    
    return $totalUnits > 0 ? round($totalPoints / $totalUnits, 2) : 0;
}

// Get student information
function getStudentInfo($studentId) {
    $conn = connectDB();
    
    // In a real application, you would fetch this from the database
    // For now, we'll use sample data
    $studentInfo = null;
    
    if ($studentId == '2023-0001') {
        $studentInfo = [
            'StudentID' => '2023-0001',
            'Name' => 'John A. Doe',
            'Program' => 'Bachelor of Science in Information Technology',
            'YearLevel' => '2nd Year'
        ];
    } else if ($studentId == '2023-0002') {
        $studentInfo = [
            'StudentID' => '2023-0002',
            'Name' => 'Jane B. Smith',
            'Program' => 'Bachelor of Science in Computer Science',
            'YearLevel' => '1st Year'
        ];
    } else if ($studentId == '2023-0003') {
        $studentInfo = [
            'StudentID' => '2023-0003',
            'Name' => 'Michael C. Johnson',
            'Program' => 'Bachelor of Science in Information Systems',
            'YearLevel' => '3rd Year'
        ];
    } else if ($studentId == '2023-0004') {
        $studentInfo = [
            'StudentID' => '2023-0004',
            'Name' => 'Sarah D. Williams',
            'Program' => 'Bachelor of Science in Information Technology',
            'YearLevel' => '2nd Year'
        ];
    } else if ($studentId == '2023-0005') {
        $studentInfo = [
            'StudentID' => '2023-0005',
            'Name' => 'Robert E. Brown',
            'Program' => 'Bachelor of Science in Computer Science',
            'YearLevel' => '4th Year'
        ];
    }
    
    $conn->close();
    return $studentInfo;
}

// Process form submission
$currentAcademic = getCurrentAcademicYear();
$academicYear = isset($_GET['academic_year']) ? $_GET['academic_year'] : $currentAcademic['year'];
$semester = isset($_GET['semester']) ? $_GET['semester'] : $currentAcademic['semester'];
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : 'all';

// Search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$searchResults = [];
if (!empty($searchTerm)) {
    $searchResults = searchStudents($searchTerm);
}

// Get student ID
$studentId = isset($_GET['student_id']) ? $_GET['student_id'] : '';
$studentInfo = null;
$grades = [];
$gpa = 0;

// If student ID is provided, get student info and grades
if (!empty($studentId)) {
    $studentInfo = getStudentInfo($studentId);
    if ($studentInfo) {
        $grades = getStudentGrades($studentId, $academicYear, $semester);
        $gpa = calculateGPA($grades);
    }
}

require_once 'session_init.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grades - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
        /* Grades Page Specific Styles */
        .grades-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .grades-header {
            margin-bottom: 30px;
        }

        .grades-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .grades-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .search-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
        }

        .search-input:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
        }

        .search-button {
            padding: 12px 20px;
            background-color: #4361ee;
            color: white;
            border: none;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-button:hover {
            background-color: #3a56d4;
        }

        .search-results {
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 5px;
        }

        .search-results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .search-results-table th,
        .search-results-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .search-results-table th {
            background-color: #f1f3f9;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }

        .search-results-table tr:last-child td {
            border-bottom: none;
        }

        .search-results-table tr:hover {
            background-color: #f9f9f9;
            cursor: pointer;
        }

        .search-results-table .select-btn {
            padding: 6px 12px;
            background-color: #4361ee;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .search-results-table .select-btn:hover {
            background-color: #3a56d4;
        }

        .student-info {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .student-info-grid {
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

        .filter-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }

        .filter-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            background-color: white;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
        }

        .filter-button {
            padding: 10px 20px;
            background-color: #4361ee;
            color: white;
            border: none;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-button:hover {
            background-color: #3a56d4;
        }

        .grades-table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
        }

        .grades-table th,
        .grades-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .grades-table th {
            background-color: #f1f3f9;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }

        .grades-table tr:last-child td {
            border-bottom: none;
        }

        .grades-table tr:hover {
            background-color: #f9f9f9;
        }

        .grade-cell {
            font-weight: 500;
        }

        .grade-passed {
            color: #0d8a53;
        }

        .grade-failed {
            color: #d63d62;
        }

        .grade-incomplete {
            color: #f9a826;
        }

        .summary-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        .summary-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .summary-item {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .summary-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #4361ee;
        }

        .quarter-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .quarter-tab {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid transparent;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            margin-right: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .quarter-tab:hover {
            background-color: #f8f9fa;
        }

        .quarter-tab.active {
            background-color: white;
            border-color: #ddd;
            color: #4361ee;
            position: relative;
        }

        .quarter-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 1px;
            background-color: white;
        }

        .quarter-content {
            display: none;
        }

        .quarter-content.active {
            display: block;
        }

        .no-grades {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        .no-grades i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .no-grades p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .no-grades span {
            font-size: 0.9rem;
        }

        .no-student-selected {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .no-student-selected i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-student-selected h2 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .no-student-selected p {
            color: #666;
            font-size: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .student-info-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .quarter-tabs {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 5px;
            }
            
            .quarter-tab {
                display: inline-block;
            }
            
            .search-form {
                flex-direction: column;
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
        
        <div class="grades-container">
            <div class="grades-header">
                <h1>Student Grades</h1>
                <p>Search for students and view their academic performance</p>
            </div>
            
            <!-- Search Section -->
            <div class="search-container">
                <form class="search-form" method="get" action="grades.php">
                    <input type="text" name="search" class="search-input" placeholder="Search by student ID or name..." value="<?php echo htmlspecialchars($searchTerm); ?>" required>
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
                
                <?php if (!empty($searchResults)): ?>
                    <div class="search-results">
                        <table class="search-results-table">
                            <thead>
                                <tr>
                                    <th>ID Number</th>
                                    <th>Name</th>
                                    <th>Program</th>
                                    <th>Year Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($searchResults as $student): ?>
                                    <tr>
                                        <td><?php echo $student['IDNumber']; ?></td>
                                        <td><?php echo $student['FirstName'] . ' ' . $student['MiddleInitial'] . '. ' . $student['LastName']; ?></td>
                                        <td><?php echo $student['Program']; ?></td>
                                        <td><?php echo $student['YearLevel']; ?></td>
                                        <td>
                                            <a href="grades.php?student_id=<?php echo $student['IDNumber']; ?>&academic_year=<?php echo $academicYear; ?>&semester=<?php echo $semester; ?>" class="select-btn">View Grades</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif (!empty($searchTerm)): ?>
                    <div class="no-grades">
                        <i class="fas fa-search"></i>
                        <p>No students found</p>
                        <span>Try searching with a different ID or name.</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($studentInfo): ?>
                <!-- Student Information -->
                <div class="student-info">
                    <div class="student-info-grid">
                        <div class="info-group">
                            <div class="info-label">Student ID</div>
                            <div class="info-value"><?php echo $studentInfo['StudentID']; ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Name</div>
                            <div class="info-value"><?php echo $studentInfo['Name']; ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Program</div>
                            <div class="info-value"><?php echo $studentInfo['Program']; ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Year Level</div>
                            <div class="info-value"><?php echo $studentInfo['YearLevel']; ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Options -->
                <div class="filter-container">
                    <form class="filter-form" method="get">
                        <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">
                        <div class="filter-group">
                            <label for="academic_year">Academic Year</label>
                            <select id="academic_year" name="academic_year">
                                <?php foreach (getAvailableAcademicYears() as $year): ?>
                                    <option value="<?php echo $year; ?>" <?php echo $academicYear == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="semester">Semester</label>
                            <select id="semester" name="semester">
                                <?php foreach (getAvailableSemesters() as $sem): ?>
                                    <option value="<?php echo $sem; ?>" <?php echo $semester == $sem ? 'selected' : ''; ?>><?php echo $sem; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="quarter">Quarter</label>
                            <select id="quarter" name="quarter">
                                <option value="all" <?php echo $quarter == 'all' ? 'selected' : ''; ?>>All Quarters</option>
                                <option value="prelim" <?php echo $quarter == 'prelim' ? 'selected' : ''; ?>>Prelim</option>
                                <option value="midterm" <?php echo $quarter == 'midterm' ? 'selected' : ''; ?>>Midterm</option>
                                <option value="prefinal" <?php echo $quarter == 'prefinal' ? 'selected' : ''; ?>>Prefinal</option>
                                <option value="final" <?php echo $quarter == 'final' ? 'selected' : ''; ?>>Final</option>
                            </select>
                        </div>
                        <button type="submit" class="filter-button">Apply Filters</button>
                    </form>
                </div>
                
                <?php if (empty($grades)): ?>
                    <div class="grades-table-container">
                        <div class="no-grades">
                            <i class="fas fa-file-alt"></i>
                            <p>No grades found</p>
                            <span>There are no grades available for the selected filters.</span>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if ($quarter == 'all'): ?>
                        <!-- Quarter Tabs -->
                        <div class="quarter-tabs">
                            <div class="quarter-tab active" data-quarter="all">All Quarters</div>
                            <div class="quarter-tab" data-quarter="prelim">Prelim</div>
                            <div class="quarter-tab" data-quarter="midterm">Midterm</div>
                            <div class="quarter-tab" data-quarter="prefinal">Prefinal</div>
                            <div class="quarter-tab" data-quarter="final">Final</div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- All Quarters Table -->
                    <div class="quarter-content active" id="all-quarters">
                        <div class="grades-table-container">
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Title</th>
                                        <th>Units</th>
                                        <th>Prelim</th>
                                        <th>Midterm</th>
                                        <th>Prefinal</th>
                                        <th>Final</th>
                                        <th>Final Grade</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): ?>
                                        <tr>
                                            <td><?php echo $grade['CourseCode']; ?></td>
                                            <td><?php echo $grade['CourseTitle']; ?></td>
                                            <td><?php echo $grade['Units']; ?></td>
                                            <td class="grade-cell"><?php echo $grade['Prelim']; ?></td>
                                            <td class="grade-cell"><?php echo $grade['Midterm']; ?></td>
                                            <td class="grade-cell"><?php echo $grade['Prefinal']; ?></td>
                                            <td class="grade-cell"><?php echo $grade['Final']; ?></td>
                                            <td class="grade-cell <?php echo $grade['FinalGrade'] >= 75 ? 'grade-passed' : 'grade-failed'; ?>">
                                                <?php echo number_format($grade['FinalGrade'], 2); ?>
                                            </td>
                                            <td class="<?php echo $grade['Remarks'] == 'Passed' ? 'grade-passed' : ($grade['Remarks'] == 'Failed' ? 'grade-failed' : 'grade-incomplete'); ?>">
                                                <?php echo $grade['Remarks']; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Prelim Quarter Table -->
                    <div class="quarter-content" id="prelim-quarter">
                        <div class="grades-table-container">
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Title</th>
                                        <th>Units</th>
                                        <th>Prelim Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): ?>
                                        <tr>
                                            <td><?php echo $grade['CourseCode']; ?></td>
                                            <td><?php echo $grade['CourseTitle']; ?></td>
                                            <td><?php echo $grade['Units']; ?></td>
                                            <td class="grade-cell <?php echo $grade['Prelim'] >= 75 ? 'grade-passed' : 'grade-failed'; ?>">
                                                <?php echo $grade['Prelim']; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Midterm Quarter Table -->
                    <div class="quarter-content" id="midterm-quarter">
                        <div class="grades-table-container">
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Title</th>
                                        <th>Units</th>
                                        <th>Midterm Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): ?>
                                        <tr>
                                            <td><?php echo $grade['CourseCode']; ?></td>
                                            <td><?php echo $grade['CourseTitle']; ?></td>
                                            <td><?php echo $grade['Units']; ?></td>
                                            <td class="grade-cell <?php echo $grade['Midterm'] >= 75 ? 'grade-passed' : 'grade-failed'; ?>">
                                                <?php echo $grade['Midterm']; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Prefinal Quarter Table -->
                    <div class="quarter-content" id="prefinal-quarter">
                        <div class="grades-table-container">
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Title</th>
                                        <th>Units</th>
                                        <th>Prefinal Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): ?>
                                        <tr>
                                            <td><?php echo $grade['CourseCode']; ?></td>
                                            <td><?php echo $grade['CourseTitle']; ?></td>
                                            <td><?php echo $grade['Units']; ?></td>
                                            <td class="grade-cell <?php echo $grade['Prefinal'] >= 75 ? 'grade-passed' : 'grade-failed'; ?>">
                                                <?php echo $grade['Prefinal']; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Final Quarter Table -->
                    <div class="quarter-content" id="final-quarter">
                        <div class="grades-table-container">
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Title</th>
                                        <th>Units</th>
                                        <th>Final Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): ?>
                                        <tr>
                                            <td><?php echo $grade['CourseCode']; ?></td>
                                            <td><?php echo $grade['CourseTitle']; ?></td>
                                            <td><?php echo $grade['Units']; ?></td>
                                            <td class="grade-cell <?php echo $grade['Final'] >= 75 ? 'grade-passed' : 'grade-failed'; ?>">
                                                <?php echo $grade['Final']; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Summary Section -->
                    <div class="summary-container">
                        <h3 class="summary-title">Grade Summary</h3>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <div class="summary-label">GPA</div>
                                <div class="summary-value"><?php echo number_format($gpa, 2); ?></div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Total Units</div>
                                <div class="summary-value">
                                    <?php 
                                        $totalUnits = 0;
                                        foreach ($grades as $grade) {
                                            $totalUnits += $grade['Units'];
                                        }
                                        echo $totalUnits;
                                    ?>
                                </div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Courses</div>
                                <div class="summary-value"><?php echo count($grades); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php elseif (empty($searchTerm)): ?>
                <!-- No student selected yet -->
                <div class="no-student-selected">
                    <i class="fas fa-user-graduate"></i>
                    <h2>No Student Selected</h2>
                    <p>Search for a student by ID number or name to view their grades.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Quarter tabs functionality
            const quarterTabs = document.querySelectorAll('.quarter-tab');
            const quarterContents = document.querySelectorAll('.quarter-content');
            
            quarterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const quarter = this.getAttribute('data-quarter');
                    
                    // Remove active class from all tabs and contents
                    quarterTabs.forEach(t => t.classList.remove('active'));
                    quarterContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to selected tab and content
                    this.classList.add('active');
                    
                    if (quarter === 'all') {
                        document.getElementById('all-quarters').classList.add('active');
                    } else if (quarter === 'prelim') {
                        document.getElementById('prelim-quarter').classList.add('active');
                    } else if (quarter === 'midterm') {
                        document.getElementById('midterm-quarter').classList.add('active');
                    } else if (quarter === 'prefinal') {
                        document.getElementById('prefinal-quarter').classList.add('active');
                    } else if (quarter === 'final') {
                        document.getElementById('final-quarter').classList.add('active');
                    }
                });
            });
            
            // Auto-submit form when changing academic year or semester
            const autoSubmitSelects = document.querySelectorAll('#academic_year, #semester');
            autoSubmitSelects.forEach(select => {
                select.addEventListener('change', function() {
                    this.form.submit();
                });
            });
            
            // Make entire row clickable in search results
            const searchRows = document.querySelectorAll('.search-results-table tbody tr');
            searchRows.forEach(row => {
                row.addEventListener('click', function() {
                    const viewBtn = this.querySelector('.select-btn');
                    if (viewBtn) {
                        window.location.href = viewBtn.getAttribute('href');
                    }
                });
            });
        });
    </script>
</body>
</html>