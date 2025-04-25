<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect if the user is not an the expected role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'program head') {
    header("Location: ../index.php");
    exit();
}

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Fetch user information from the session
$firstName = $_SESSION['firstname'] ?? 'Unknown';
$lastName = $_SESSION['lastname'] ?? 'User';
$MiddleInitial = $_SESSION['middleinitial'] ?? '';
$role = $_SESSION['role'];
$IDNumber = $_SESSION['idnumber'] ?? '';
$Birthday = $_SESSION['birthday'] ?? '';
$username = $_SESSION['username'];
$photoData = $_SESSION['photo'] ?? null;
$email = $_SESSION['email'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$PhoneNumber = $_SESSION['phonenumber'] ?? '';
$AddressDetails = $_SESSION['addressdetails'] ?? '';
?>

