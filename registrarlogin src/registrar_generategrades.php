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
        
</body>
</html>