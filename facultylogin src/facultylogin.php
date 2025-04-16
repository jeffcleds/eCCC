<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index.php");
    exit();
}
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href = facultyloginstyles.css>

</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/eCCC%20Logo-z0RgF8WgeU1nmcArUr34k2hzGcCSPC.png" alt="Calabanga Community College Logo" class="sidebar-logo">
            <h1 class="sidebar-title">Faculty Portal</h1>
            <p class="sidebar-subtitle">Calabanga Community College</p>
        </div>
        
        <nav class="sidebar-menu">
            <p class="menu-category">Main</p>
            <a href="#" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            
            <p class="menu-category">Teaching</p>
            <a href="#" class="menu-item">
                <i class="fas fa-calendar-alt"></i>
                <span class="menu-text">Class Schedule</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-user-graduate"></i>
                <span class="menu-text">My Students</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-clipboard-list"></i>
                <span class="menu-text">Attendance</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Grade Book</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-tasks"></i>
                <span class="menu-text">Assignments</span>
            </a>
            
            <p class="menu-category">Content</p>
            <a href="#" class="menu-item">
                <i class="fas fa-book"></i>
                <span class="menu-text">Course Materials</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-file-upload"></i>
                <span class="menu-text">Upload Resources</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-bullhorn"></i>
                <span class="menu-text">Announcements</span>
            </a>
            
            <p class="menu-category">Account</p>
            <a href="#" class="menu-item">
                <i class="fas fa-user-circle"></i>
                <span class="menu-text">My Profile</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Logout</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            &copy; 2025 Calabanga Community College
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <button class="toggle-sidebar" id="toggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <h1 class="header-title">Faculty Dashboard</h1>
            
            <div class="header-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">5</span>
                </button>
                
                <div class="user-profile">
                    <div class="user-avatar">
                        <span>P</span>
                    </div>
                    <div class="user-info">
                        <p class="user-name">Prof. Allan Aboga-A</p>
                        <p class="user-role">Information Technology Department</p>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Dashboard Content -->
        <div class="dashboard">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2 class="welcome-title">Welcome back, Prof. Aboga-A!</h2>
                <p class="welcome-subtitle">You have 3 classes scheduled for today and 5 pending assignments to grade.</p>
                <div class="welcome-actions">
                    <button class="welcome-btn">
                        <i class="fas fa-clipboard-list"></i> Take Attendance
                    </button>
                    <button class="welcome-btn outline">
                        <i class="fas fa-file-alt"></i> Grade Assignments
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon classes">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <div class="stat-info">
                        <h3>5</h3>
                        <p>Active Classes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3>142</h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon assignments">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-info">
                        <h3>8</h3>
                        <p>Pending Assignments</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon attendance">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>92%</h3>
                        <p>Avg. Attendance</p>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Today's Schedule -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Today's Schedule</h3>
                        <a href="#" class="card-action">View Full Schedule</a>
                    </div>
                    
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Course</th>
                                <th>Room</th>
                                <th>Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>8:00 AM - 9:30 AM</td>
                                <td>
                                    <span class="course-badge cs">CS</span>
                                    Introduction to Programming
                                </td>
                                <td>Room 201</td>
                                <td>32</td>
                            </tr>
                            <tr>
                                <td>10:00 AM - 11:30 AM</td>
                                <td>
                                    <span class="course-badge cs">CS</span>
                                    Data Structures
                                </td>
                                <td>Computer Lab 1</td>
                                <td>28</td>
                            </tr>
                            <tr>
                                <td>1:00 PM - 2:30 PM</td>
                                <td>
                                    <span class="course-badge math">MATH</span>
                                    Discrete Mathematics
                                </td>
                                <td>Room 105</td>
                                <td>35</td>
                            </tr>
                            <tr>
                                <td>3:00 PM - 4:30 PM</td>
                                <td>
                                    <span class="course-badge cs">CS</span>
                                    Database Systems
                                </td>
                                <td>Computer Lab 2</td>
                                <td>30</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Announcements -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Announcements</h3>
                        <a href="#" class="card-action">Create New</a>
                    </div>
                    
                    <div class="announcement-item">
                        <div class="announcement-icon alert">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="announcement-content">
                            <p class="announcement-title">Midterm Exam Schedule Posted</p>
                            <p class="announcement-time">Today, 9:15 AM</p>
                        </div>
                    </div>
                    
                    <div class="announcement-item">
                        <div class="announcement-icon info">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="announcement-content">
                            <p class="announcement-title">Faculty Meeting - Friday, 2:00 PM</p>
                            <p class="announcement-time">Yesterday, 3:30 PM</p>
                        </div>
                    </div>
                    
                    <div class="announcement-item">
                        <div class="announcement-icon event">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="announcement-content">
                            <p class="announcement-title">IT Department Seminar Next Week</p>
                            <p class="announcement-time">Apr 12, 2025, 10:45 AM</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div>
                    <!-- Assignments to Grade -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Assignments to Grade</h3>
                            <a href="#" class="card-action">View All</a>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task1">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Programming Project 1 <span class="task-priority high">High</span></p>
                                <p class="task-due">Due: Today</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task2">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Database Quiz <span class="task-priority medium">Medium</span></p>
                                <p class="task-due">Due: Tomorrow</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task3">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Math Problem Set <span class="task-priority low">Low</span></p>
                                <p class="task-due">Due: Apr 16, 2025</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task4">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Algorithm Analysis <span class="task-priority medium">Medium</span></p>
                                <p class="task-due">Due: Apr 18, 2025</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Student Requests -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Student Requests</h3>
                            <a href="#" class="card-action">View All</a>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-content">
                                <p class="task-title">Juan Dela Cruz</p>
                                <p class="task-due">Extension request for Programming Project</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-content">
                                <p class="task-title">Maria Garcia</p>
                                <p class="task-due">Grade inquiry for Midterm Exam</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-content">
                                <p class="task-title">Carlos Reyes</p>
                                <p class="task-due">Request for recommendation letter</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming Deadlines -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Upcoming Deadlines</h3>
                        <a href="#" class="card-action">Add New</a>
                    </div>
                    
                    <div class="task-item">
                        <div class="task-content">
                            <p class="task-title">Submit Midterm Grades</p>
                            <p class="task-due">Due: Apr 18, 2025</p>
                        </div>
                    </div>
                    
                    <div class="task-item">
                        <div class="task-content">
                            <p class="task-title">Course Syllabus Update</p>
                            <p class="task-due">Due: Apr 20, 2025</p>
                        </div>
                    </div>
                    
                    <div class="task-item">
                        <div class="task-content">
                            <p class="task-title">Research Proposal Submission</p>
                            <p class="task-due">Due: Apr 25, 2025</p>
                        </div>
                    </div>
                    
                    <div class="task-item">
                        <div class="task-content">
                            <p class="task-title">Department Progress Report</p>
                            <p class="task-due">Due: Apr 30, 2025</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.getElementById('toggleSidebar');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggleBtn.contains(event.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
        
        // Task checkbox functionality
        const checkboxes = document.querySelectorAll('.task-checkbox input');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const taskItem = this.closest('.task-item');
                if (this.checked) {
                    taskItem.style.opacity = '0.6';
                    taskItem.querySelector('.task-title').style.textDecoration = 'line-through';
                } else {
                    taskItem.style.opacity = '1';
                    taskItem.querySelector('.task-title').style.textDecoration = 'none';
                }
            });
        });
    </script>
</body>
</html>