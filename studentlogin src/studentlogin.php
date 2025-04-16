<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
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
    <title>Student Dashboard - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="studentloginstyles.css">

</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/eCCC%20Logo-z0RgF8WgeU1nmcArUr34k2hzGcCSPC.png" alt="Calabanga Community College Logo" class="sidebar-logo">
            <h1 class="sidebar-title">Student Portal</h1>
            <p class="sidebar-subtitle">Calabanga Community College</p>
        </div>
        
        <nav class="sidebar-menu">
            <p class="menu-category">Main</p>
            <a href="#" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            
            <p class="menu-category">Academics</p>
            <a href="#" class="menu-item">
                <i class="fas fa-book"></i>
                <span class="menu-text">My Courses</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-calendar-alt"></i>
                <span class="menu-text">Class Schedule</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Grades</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-clipboard-list"></i>
                <span class="menu-text">Attendance</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-tasks"></i>
                <span class="menu-text">Assignments</span>
            </a>
            
            <p class="menu-category">Registration</p>
            <a href="#" class="menu-item">
                <i class="fas fa-edit"></i>
                <span class="menu-text">Course Registration</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-file-export"></i>
                <span class="menu-text">Transcript Request</span>
            </a>
            
            <p class="menu-category">Financial</p>
            <a href="#" class="menu-item">
                <i class="fas fa-money-bill-wave"></i>
                <span class="menu-text">Tuition & Fees</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-hand-holding-usd"></i>
                <span class="menu-text">Financial Aid</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-receipt"></i>
                <span class="menu-text">Payment History</span>
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
            
            <h1 class="header-title">Student Dashboard</h1>
            
            <div class="header-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">4</span>
                </button>
                
                <div class="user-profile">
                    <div class="user-avatar">
                        <span>J</span>
                    </div>
                    <div class="user-info">
                        <p class="user-name">Juan Dela Cruz</p>
                        <p class="user-role">BS Computer Science</p>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Dashboard Content -->
        <div class="dashboard">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2 class="welcome-title">Welcome back, Juan!</h2>
                <p class="welcome-subtitle">You have 3 upcoming assignments and 1 exam this week.</p>
                <div class="welcome-actions">
                    <button class="welcome-btn">
                        <i class="fas fa-tasks"></i> View Assignments
                    </button>
                    <button class="welcome-btn outline">
                        <i class="fas fa-calendar-alt"></i> Class Schedule
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon courses">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3>5</h3>
                        <p>Current Courses</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon gpa">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3>3.75</h3>
                        <p>Current GPA</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon credits">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-info">
                        <h3>45/120</h3>
                        <p>Credits Completed</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon attendance">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>95%</h3>
                        <p>Attendance Rate</p>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Current Courses -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Current Courses</h3>
                        <a href="#" class="card-action">View All</a>
                    </div>
                    
                    <table class="course-table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Schedule</th>
                                <th>Instructor</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="course-badge cs">CS</span>
                                    Data Structures
                                </td>
                                <td>MWF 9:00-10:30 AM</td>
                                <td>Prof. Santos</td>
                                <td><span class="grade-badge a">A</span></td>
                                <td>
                                    <button class="action-btn view">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="course-badge math">MATH</span>
                                    Discrete Mathematics
                                </td>
                                <td>TTh 11:00-12:30 PM</td>
                                <td>Prof. Reyes</td>
                                <td><span class="grade-badge b">B+</span></td>
                                <td>
                                    <button class="action-btn view">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="course-badge cs">CS</span>
                                    Database Systems
                                </td>
                                <td>MWF 1:00-2:30 PM</td>
                                <td>Prof. Garcia</td>
                                <td><span class="grade-badge a">A-</span></td>
                                <td>
                                    <button class="action-btn view">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="course-badge eng">ENG</span>
                                    Technical Writing
                                </td>
                                <td>TTh 2:00-3:30 PM</td>
                                <td>Prof. Cruz</td>
                                <td><span class="grade-badge b">B</span></td>
                                <td>
                                    <button class="action-btn view">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="course-badge sci">SCI</span>
                                    Physics for Computing
                                </td>
                                <td>MWF 3:00-4:30 PM</td>
                                <td>Prof. Mendoza</td>
                                <td><span class="grade-badge c">C+</span></td>
                                <td>
                                    <button class="action-btn view">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Announcements -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Announcements</h3>
                        <a href="#" class="card-action">View All</a>
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
                            <p class="announcement-title">Library Hours Extended for Finals Week</p>
                            <p class="announcement-time">Yesterday, 3:30 PM</p>
                        </div>
                    </div>
                    
                    <div class="announcement-item">
                        <div class="announcement-icon event">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="announcement-content">
                            <p class="announcement-title">Campus Career Fair Next Week</p>
                            <p class="announcement-time">Apr 12, 2025, 10:45 AM</p>
                        </div>
                    </div>
                    
                    <div class="announcement-item">
                        <div class="announcement-icon info">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="announcement-content">
                            <p class="announcement-title">Registration for Next Semester Opens Soon</p>
                            <p class="announcement-time">Apr 10, 2025, 2:15 PM</p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div>
                    <!-- Upcoming Assignments -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Upcoming Assignments</h3>
                            <a href="#" class="card-action">View All</a>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task1">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Database Project <span class="task-priority high">High</span></p>
                                <p class="task-due">Due: Tomorrow, 11:59 PM</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task2">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Math Problem Set <span class="task-priority medium">Medium</span></p>
                                <p class="task-due">Due: Apr 15, 2025</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task3">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Technical Writing Essay <span class="task-priority high">High</span></p>
                                <p class="task-due">Due: Apr 16, 2025</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task4">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Physics Lab Report <span class="task-priority medium">Medium</span></p>
                                <p class="task-due">Due: Apr 18, 2025</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Class Schedule -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Today's Schedule</h3>
                            <a href="#" class="card-action">Full Schedule</a>
                        </div>
                        
                        <div style="padding: 10px 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding: 10px; background-color: rgba(42, 157, 143, 0.1); border-radius: 8px;">
                                <div>
                                    <p style="font-size: 14px; font-weight: 500; color: var(--success-color);">Data Structures</p>
                                    <p style="font-size: 12px; color: var(--light-text);">9:00 - 10:30 AM</p>
                                </div>
                                <div style="align-self: center; font-size: 12px;">
                                    Room 201
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding: 10px; background-color: rgba(244, 162, 97, 0.1); border-radius: 8px;">
                                <div>
                                    <p style="font-size: 14px; font-weight: 500; color: var(--warning-color);">Lunch Break</p>
                                    <p style="font-size: 12px; color: var(--light-text);">12:00 - 1:00 PM</p>
                                </div>
                                <div style="align-self: center; font-size: 12px;">
                                    Cafeteria
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px; padding: 10px; background-color: rgba(21, 62, 111, 0.1); border-radius: 8px;">
                                <div>
                                    <p style="font-size: 14px; font-weight: 500; color: var(--secondary-color);">Database Systems</p>
                                    <p style="font-size: 12px; color: var(--light-text);">1:00 - 2:30 PM</p>
                                </div>
                                <div style="align-self: center; font-size: 12px;">
                                    Computer Lab 2
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; padding: 10px; background-color: rgba(230, 57, 70, 0.1); border-radius: 8px;">
                                <div>
                                    <p style="font-size: 14px; font-weight: 500; color: var(--danger-color);">Physics for Computing</p>
                                    <p style="font-size: 12px; color: var(--light-text);">3:00 - 4:30 PM</p>
                                </div>
                                <div style="align-self: center; font-size: 12px;">
                                    Science Lab
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Academic Progress -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Academic Progress</h3>
                        <a href="#" class="card-action">View Details</a>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Degree Completion</span>
                            <span>37.5%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill success" style="width: 37.5%;"></div>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Current Semester</span>
                            <span>65%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill info" style="width: 65%;"></div>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>GPA Trend</span>
                            <span>3.75/4.0</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill warning" style="width: 93.75%;"></div>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Attendance</span>
                            <span>95%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill success" style="width: 95%;"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Financial Information -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Financial Information</h3>
                        <a href="#" class="card-action">Payment Portal</a>
                    </div>
                    
                    <div class="financial-summary">
                        <div class="financial-item">
                            <p class="financial-label">Tuition Paid</p>
                            <p class="financial-value paid">₱45,000</p>
                        </div>
                        
                        <div class="financial-item">
                            <p class="financial-label">Amount Due</p>
                            <p class="financial-value due">₱15,000</p>
                        </div>
                        
                        <div class="financial-item">
                            <p class="financial-label">Financial Aid</p>
                            <p class="financial-value aid">₱20,000</p>
                        </div>
                        
                        <div class="financial-item">
                            <p class="financial-label">Account Balance</p>
                            <p class="financial-value balance">₱15,000</p>
                        </div>
                    </div>
                    
                    <div style="margin-top: 10px;">
                        <p style="font-size: 14px; margin-bottom: 10px;"><strong>Payment Due Date:</strong> April 30, 2025</p>
                        <button class="welcome-btn" style="width: 100%; justify-content: center;">
                            <i class="fas fa-credit-card"></i> Make a Payment
                        </button>
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