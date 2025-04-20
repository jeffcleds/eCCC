<?php
include '../config.php';
include 'session_init.php';
include 'getUsersTotal.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">

        <!-- Header -->
        <?php include 'header.php'; ?>

        <!-- Dashboard Content -->
        <div class="dashboard">
            <h2 class="dashboard-title">Welcome, <?php echo $firstName . ' ' . $lastName; ?>!</h2>
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalStudents; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon faculty">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalFaculty; ?></h3>
                        <p>Total Faculty</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon courses">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalRegistrars; ?></h3>
                        <p>Total Registrars</p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">

                <!-- Recent Activity -->
                <div class="dashboard-card recent-activity">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                        <a href="#" class="card-action">View All</a>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon add">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">New student registered: Joshua Gamora</p>
                            <p class="activity-time">Today, 10:30 AM</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon edit">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">Course schedule updated: Computer Science 101</p>
                            <p class="activity-time">Yesterday, 3:45 PM</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon add">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">New faculty member added: Prof. Marbert Plazo</p>
                            <p class="activity-time">Yesterday, 1:20 PM</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon delete">
                            <i class="fas fa-trash"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">Course removed: Advanced Calculus</p>
                            <p class="activity-time">Apr 12, 2025, 9:15 AM</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon edit">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">Student information updated: Chrystian Festin</p>
                            <p class="activity-time">Apr 11, 2025, 2:30 PM</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>

                    <!-- Upcoming Events -->
                    <div class="dashboard-card upcoming-events">
                        <div class="card-header">
                            <h3 class="card-title">Upcoming Events</h3>
                            <a href="#" class="card-action">View Calendar</a>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <span class="event-day">15</span>
                                <span class="event-month">Apr</span>
                            </div>
                            <div class="event-info">
                                <p class="event-title">Faculty Meeting</p>
                                <p class="event-details">9:00 AM - RM204</p>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <span class="event-day">18</span>
                                <span class="event-month">Apr</span>
                            </div>
                            <div class="event-info">
                                <p class="event-title">Enrollment Deadline</p>
                                <p class="event-details">All Day</p>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <span class="event-day">22</span>
                                <span class="event-month">Apr</span>
                            </div>
                            <div class="event-info">
                                <p class="event-title">STI Tagisan ng Talino</p>
                                <p class="event-details">1:00 PM - STI Lobby</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                            <a href="students.php" style="text-decoration: none; display: block;">
                                <button style="width: 100%; padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">
                                    <i class="fas fa-user-plus" style="margin-right: 5px;"></i> Add Student
                                </button>
                            </a>
                            <a href="faculty.php" style="text-decoration: none; display: block;">
                                <button style="width: 100%; padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">
                                    <i class="fas fa-user-tie" style="margin-right: 5px;"></i> Add Faculty
                                </button>
                            </a>
                            <a href="subjects.php" style="text-decoration: none; display: block;">
                                <button style="width: 100%; padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">
                                    <i class="fas fa-book-medical" style="margin-right: 5px;"></i> Add Course
                                </button>
                            </a>
                            <a href="settings.php?tab=system" style="text-decoration: none; display: block;">
                                <button style="width: 100%; padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">
                                    <i class="fas fa-file-export" style="margin-right: 5px;"></i> Export Data
                                </button>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</body>
</html>
