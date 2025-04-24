<?php
include '../config.php'; 
include 'session_init.php'; 


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
    <link rel="stylesheet" href="sidebarstyles_registar.css">
    <link rel="stylesheet" href="headerstyles_registrar.css">

</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar_registrar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <?php include 'header_registrar.php'; ?>
        
    <!-- Dashboard Content -->
    <div class="dashboard">
            <h2 class="dashboard-title">Welcome, <?php echo $firstName . ' ' . $lastName; ?>!</h2>

            <!-- Stats Cards -->
            <!--
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
            -->


</body>
</html>