<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CCCDB";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['UserID'];

    $first_name = filter_input(INPUT_POST, 'FirstName', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'LastName', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'Email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'PhoneNumber', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'AddressDetails', FILTER_SANITIZE_STRING);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: settings.php");
        exit();
    }

    $profile_picture_blob = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024;

        if (!in_array($_FILES['profile_picture']['type'], $allowed_types)) {
            $_SESSION['error'] = "Only JPG, PNG, and GIF files are allowed";
            header("Location: settings.php");
            exit();
        }

        if ($_FILES['profile_picture']['size'] > $max_size) {
            $_SESSION['error'] = "File size must be less than 2MB";
            header("Location: settings.php");
            exit();
        }

        $profile_picture_blob = file_get_contents($_FILES['profile_picture']['tmp_name']);
    }

    $sql = "UPDATE Users SET 
            FirstName = ?, 
            LastName = ?, 
            Email = ?, 
            PhoneNumber = ?, 
            AddressDetails = ?";
    
    $params = [$first_name, $last_name, $email, $phone, $address];
    $types = "sssss";

    if ($profile_picture_blob !== null) {
        $sql .= ", Photo = ?";
        $params[] = $profile_picture_blob;
        $types .= "b";
    }

    $sql .= " WHERE UserID = ?";
    $params[] = $user_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $_SESSION['error'] = "SQL prepare failed: " . $conn->error;
        header("Location: settings.php");
        exit();
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully";
    } else {
        $_SESSION['error'] = "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
    header("Location: settings.php");
    exit();
} else {
    header("Location: settings.php");
    exit();
}

$conn->close();
?>
