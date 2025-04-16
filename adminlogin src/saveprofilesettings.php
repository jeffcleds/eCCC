<?php
// saveprofilesettings.php

// Database connection setup
$host = "localhost";
$user = "root"; // change to your DB username
$password = ""; // change to your DB password
$database = "CCCDB"; // change to your DB name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from POST request
$FirstName = $_POST['FirstName'] ?? '';
$LastName = $_POST['LastName'] ?? '';
$Email = $_POST['Email'] ?? '';
$PhoneNumber = $_POST['PhoneNumber'] ?? '';
$AddressDetails = $_POST['AddressDetails'] ?? '';

// Simple validation
if (empty($email)) {
    die("Email is required.");
}

// Update user profile
$sql = "UPDATE users SET FirstName=?, LastName=?, PhoneNumber=?, AddressDetails=? WHERE Email=?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sssss", $FirstName, $LastName, $PhoneNumber, $AddressDetails, $Email);
    if ($stmt->execute()) {
        echo "Profile updated successfully.";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
?>
