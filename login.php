<?php
session_start();

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connect to database
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbname = "CCCDB";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputUsername = $_POST['Username'];
    $inputPassword = $_POST['Password'];

    // Prepare and bind statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT FirstName, MiddleInitial, LastName, IDNumber, Birthday, Email, AddressDetails, Role, Photo FROM Users WHERE Username = ? AND Password = ?");
    $stmt->bind_param("ss", $inputUsername, $inputPassword);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($firstName, $middleInitial, $lastName, $idNumber, $birthday, $email, $address, $role, $photo);
        $stmt->fetch();

        // ✅ Save session data
        $_SESSION['username'] = $inputUsername;
        $_SESSION['firstname'] = $firstName;
        $_SESSION['middleinitial'] = $middleInitial;
        $_SESSION['lastname'] = $lastName;
        $_SESSION['idnumber'] = $idNumber;
        $_SESSION['birthday'] = $birthday;
        $_SESSION['email'] = $email;
        $_SESSION['address'] = $address;
        $_SESSION['role'] = $role;

        // Check if the photo exists (handle the case where no photo is uploaded)
        if ($photo) {
            // Convert the binary data to base64 for displaying in an img tag
            $_SESSION['photo'] = base64_encode($photo);
        } else {
            // Set a default image if no photo is available
            $_SESSION['photo'] = null; // or use a default image path
        }

        // Redirect based on role
        switch ($role) {
            case "admin":
                header("Location: adminlogin src/adminlogin.php");
                break;
            case "faculty":
                header("Location: facultylogin src/facultylogin.php");
                break;
            case "registrar":
                header("Location: registrarlogin src/registrarlogin.php");
                break;
            case "student":
                header("Location: studentlogin src/studentlogin.php");
                break;
            default:
                echo "<script>alert('Unknown role.'); window.location.href='index.php';</script>";
        }
        exit;
    } else {
        header("Location: index.php?error=1");
        exit;
    }

    $stmt->close();
}

$conn->close();
?>
