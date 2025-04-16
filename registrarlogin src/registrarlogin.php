<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'registrar') {
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
    <title>Registrar Dashboard - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="registrarloginstyles.css">

</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/eCCC%20Logo-z0RgF8WgeU1nmcArUr34k2hzGcCSPC.png" alt="Calabanga Community College Logo" class="sidebar-logo">
            <h1 class="sidebar-title">Registrar Portal</h1>
            <p class="sidebar-subtitle">Calabanga Community College</p>
        </div>
        
        <nav class="sidebar-menu">
            <p class="menu-category">Main</p>
            <a href="#" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            
            <p class="menu-category">Student Records</p>
            <a href="#" class="menu-item">
                <i class="fas fa-user-graduate"></i>
                <span class="menu-text">Student Directory</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-user-plus"></i>
                <span class="menu-text">New Enrollment</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Academic Records</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-graduation-cap"></i>
                <span class="menu-text">Graduation</span>
            </a>
            
            <p class="menu-category">Course Management</p>
            <a href="#" class="menu-item">
                <i class="fas fa-book"></i>
                <span class="menu-text">Course Catalog</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-calendar-alt"></i>
                <span class="menu-text">Class Scheduling</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-clipboard-list"></i>
                <span class="menu-text">Registration</span>
            </a>
            
            <p class="menu-category">Services</p>
            <a href="#" class="menu-item">
                <i class="fas fa-file-export"></i>
                <span class="menu-text">Transcript Requests</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-certificate"></i>
                <span class="menu-text">Certifications</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reports</span>
            </a>
            
            <p class="menu-category">Account</p>
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
            
            <h1 class="header-title">Registrar Dashboard</h1>
            
            <div class="header-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">7</span>
                </button>
                
                <div class="user-profile">
                    <div class="user-avatar">
                        <span>R</span>
                    </div>
                    <div class="user-info">
                        <p class="user-name">Rosa Mendoza</p>
                        <p class="user-role">Registrar Officer</p>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Dashboard Content -->
        <div class="dashboard">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2 class="welcome-title">Welcome back, Rosa!</h2>
                <p class="welcome-subtitle">You have 7 pending transcript requests and 12 new student applications to process.</p>
                <div class="welcome-actions">
                    <button class="welcome-btn">
                        <i class="fas fa-file-export"></i> Process Transcripts
                    </button>
                    <button class="welcome-btn outline">
                        <i class="fas fa-user-plus"></i> Review Applications
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3>1,245</h3>
                        <p>Active Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon enrollment">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-info">
                        <h3>128</h3>
                        <p>New Enrollments</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon courses">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3>42</h3>
                        <p>Active Courses</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon transcripts">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <div class="stat-info">
                        <h3>18</h3>
                        <p>Pending Requests</p>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Student Records -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Student Records</h3>
                        <a href="#" class="card-action">View All</a>
                    </div>
                    
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="Search by name or ID...">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <table class="student-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2025-0001</td>
                                <td>Juan Dela Cruz</td>
                                <td>BS Computer Science</td>
                                <td><span class="status-badge enrolled">Enrolled</span></td>
                                <td>
                                    <button class="action-btn view">View</button>
                                    <button class="action-btn edit">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2025-0042</td>
                                <td>Maria Garcia</td>
                                <td>BS Business Administration</td>
                                <td><span class="status-badge enrolled">Enrolled</span></td>
                                <td>
                                    <button class="action-btn view">View</button>
                                    <button class="action-btn edit">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2024-0189</td>
                                <td>Carlos Reyes</td>
                                <td>BS Education</td>
                                <td><span class="status-badge pending">Pending</span></td>
                                <td>
                                    <button class="action-btn view">View</button>
                                    <button class="action-btn edit">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2023-0076</td>
                                <td>Sofia Santos</td>
                                <td>BS Nursing</td>
                                <td><span class="status-badge withdrawn">Withdrawn</span></td>
                                <td>
                                    <button class="action-btn view">View</button>
                                    <button class="action-btn edit">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2022-0125</td>
                                <td>Miguel Lopez</td>
                                <td>BS Information Technology</td>
                                <td><span class="status-badge graduated">Graduated</span></td>
                                <td>
                                    <button class="action-btn view">View</button>
                                    <button class="action-btn edit">Edit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pending Requests -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Pending Requests</h3>
                        <a href="#" class="card-action">View All</a>
                    </div>
                    
                    <div class="request-item">
                        <div class="request-icon transcript">
                            <i class="fas fa-file-export"></i>
                        </div>
                        <div class="request-content">
                            <p class="request-title">Transcript Request - Juan Dela Cruz</p>
                            <p class="request-details">Requested on: Apr 12, 2025</p>
                            <div class="request-actions">
                                <button class="action-btn view">Process</button>
                                <button class="action-btn edit">Details</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="request-item">
                        <div class="request-icon enrollment">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="request-content">
                            <p class="request-title">Enrollment Verification - Maria Garcia</p>
                            <p class="request-details">Requested on: Apr 11, 2025</p>
                            <div class="request-actions">
                                <button class="action-btn view">Process</button>
                                <button class="action-btn edit">Details</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="request-item">
                        <div class="request-icon graduation">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="request-content">
                            <p class="request-title">Graduation Application - Miguel Lopez</p>
                            <p class="request-details">Requested on: Apr 10, 2025</p>
                            <div class="request-actions">
                                <button class="action-btn view">Process</button>
                                <button class="action-btn edit">Details</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="request-item">
                        <div class="request-icon transcript">
                            <i class="fas fa-file-export"></i>
                        </div>
                        <div class="request-content">
                            <p class="request-title">Transcript Request - Sofia Santos</p>
                            <p class="request-details">Requested on: Apr 9, 2025</p>
                            <div class="request-actions">
                                <button class="action-btn view">Process</button>
                                <button class="action-btn edit">Details</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div>
                    <!-- Academic Calendar -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Academic Calendar</h3>
                            <a href="#" class="card-action">View Full</a>
                        </div>
                        
                        <div class="calendar-header">
                            <span>Sun</span>
                            <span>Mon</span>
                            <span>Tue</span>
                            <span>Wed</span>
                            <span>Thu</span>
                            <span>Fri</span>
                            <span>Sat</span>
                        </div>
                        
                        <div class="calendar-grid">
                            <div class="calendar-day other-month">28</div>
                            <div class="calendar-day other-month">29</div>
                            <div class="calendar-day other-month">30</div>
                            <div class="calendar-day other-month">31</div>
                            <div class="calendar-day">1</div>
                            <div class="calendar-day">2</div>
                            <div class="calendar-day">3</div>
                            <div class="calendar-day">4</div>
                            <div class="calendar-day">5</div>
                            <div class="calendar-day">6</div>
                            <div class="calendar-day">7</div>
                            <div class="calendar-day">8</div>
                            <div class="calendar-day has-event">9</div>
                            <div class="calendar-day">10</div>
                            <div class="calendar-day">11</div>
                            <div class="calendar-day">12</div>
                            <div class="calendar-day today">13</div>
                            <div class="calendar-day has-event">14</div>
                            <div class="calendar-day">15</div>
                            <div class="calendar-day">16</div>
                            <div class="calendar-day has-event">17</div>
                            <div class="calendar-day">18</div>
                            <div class="calendar-day">19</div>
                            <div class="calendar-day">20</div>
                            <div class="calendar-day">21</div>
                            <div class="calendar-day">22</div>
                            <div class="calendar-day">23</div>
                            <div class="calendar-day">24</div>
                            <div class="calendar-day">25</div>
                            <div class="calendar-day has-event">26</div>
                            <div class="calendar-day">27</div>
                            <div class="calendar-day">28</div>
                            <div class="calendar-day">29</div>
                            <div class="calendar-day">30</div>
                            <div class="calendar-day other-month">1</div>
                        </div>
                        
                        <div style="margin-top: 15px; font-size: 13px;">
                            <p><strong>Apr 9:</strong> Last Day to Drop Classes</p>
                            <p><strong>Apr 14:</strong> Faculty Meeting</p>
                            <p><strong>Apr 17:</strong> Enrollment Deadline</p>
                            <p><strong>Apr 26:</strong> Graduation Rehearsal</p>
                        </div>
                    </div>
                    
                    <!-- Tasks -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Tasks</h3>
                            <a href="#" class="card-action">Add New</a>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task1">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Process transcript requests <span class="task-priority high">High</span></p>
                                <p class="task-due">Due: Today</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task2">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Update course catalog <span class="task-priority medium">Medium</span></p>
                                <p class="task-due">Due: Tomorrow</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task3">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Prepare graduation list <span class="task-priority high">High</span></p>
                                <p class="task-due">Due: Apr 16, 2025</p>
                            </div>
                        </div>
                        
                        <div class="task-item">
                            <div class="task-checkbox">
                                <input type="checkbox" id="task4">
                            </div>
                            <div class="task-content">
                                <p class="task-title">Schedule next semester <span class="task-priority medium">Medium</span></p>
                                <p class="task-due">Due: Apr 20, 2025</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enrollment Statistics -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Enrollment Statistics</h3>
                        <a href="#" class="card-action">View Report</a>
                    </div>
                    
                    <div style="padding: 10px 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <div>
                                <p style="font-size: 14px; font-weight: 500;">Computer Science</p>
                                <p style="font-size: 12px; color: var(--light-text);">245 students</p>
                            </div>
                            <div style="width: 100px; height: 10px; background-color: #eee; border-radius: 5px; align-self: center;">
                                <div style="width: 75%; height: 100%; background-color: var(--success-color); border-radius: 5px;"></div>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <div>
                                <p style="font-size: 14px; font-weight: 500;">Business Administration</p>
                                <p style="font-size: 12px; color: var(--light-text);">312 students</p>
                            </div>
                            <div style="width: 100px; height: 10px; background-color: #eee; border-radius: 5px; align-self: center;">
                                <div style="width: 90%; height: 100%; background-color: var(--info-color); border-radius: 5px;"></div>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <div>
                                <p style="font-size: 14px; font-weight: 500;">Education</p>
                                <p style="font-size: 12px; color: var(--light-text);">187 students</p>
                            </div>
                            <div style="width: 100px; height: 10px; background-color: #eee; border-radius: 5px; align-self: center;">
                                <div style="width: 60%; height: 100%; background-color: var(--warning-color); border-radius: 5px;"></div>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <div>
                                <p style="font-size: 14px; font-weight: 500;">Nursing</p>
                                <p style="font-size: 12px; color: var(--light-text);">203 students</p>
                            </div>
                            <div style="width: 100px; height: 10px; background-color: #eee; border-radius: 5px; align-self: center;">
                                <div style="width: 65%; height: 100%; background-color: var(--secondary-color); border-radius: 5px;"></div>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <p style="font-size: 14px; font-weight: 500;">Engineering</p>
                                <p style="font-size: 12px; color: var(--light-text);">178 students</p>
                            </div>
                            <div style="width: 100px; height: 10px; background-color: #eee; border-radius: 5px; align-self: center;">
                                <div style="width: 55%; height: 100%; background-color: var(--danger-color); border-radius: 5px;"></div>
                            </div>
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