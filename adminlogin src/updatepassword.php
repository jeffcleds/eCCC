<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Only process if coming from password form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "CCCDB";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        $_SESSION['password_error'] = "Database connection failed";
        header("Location: settings.php");
        exit();
    }

    // Get inputs
    $current_password = $conn->real_escape_string($_POST['current_password']);
    $new_password = $conn->real_escape_string($_POST['new_password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);
    $username = $conn->real_escape_string($_SESSION['username']);

    // Validate
    if (strlen($new_password) < 6) {
        $_SESSION['password_error'] = "Password must be at least 6 characters";
        header("Location: settings.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['password_error'] = "New passwords don't match";
        header("Location: settings.php");
        exit();
    }

    // Verify current password
    $result = $conn->query("SELECT Password FROM Users WHERE Username = '$username'");
    if ($result->num_rows === 0) {
        $_SESSION['password_error'] = "User not found";
        header("Location: settings.php");
        exit();
    }

    $user = $result->fetch_assoc();
    if ($current_password !== $user['Password']) {
        $_SESSION['password_error'] = "Current password is incorrect";
        header("Location: settings.php");
        exit();
    }

    // Update password
    if ($conn->query("UPDATE Users SET Password = '$new_password' WHERE Username = '$username'")) {
        $_SESSION['password_success'] = "Password updated successfully!";
    } else {
        $_SESSION['password_error'] = "Error updating password: " . $conn->error;
    }

    $conn->close();
    header("Location: settings.php");
    exit();
}
?>