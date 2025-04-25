<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbname = "CCCDB";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputUsername = $_POST['Username'];
    $inputPassword = $_POST['Password'];

    $stmt = $conn->prepare("SELECT FirstName, MiddleInitial, LastName, IDNumber, Birthday, AddressDetails, PhoneNumber, Gender, Email, Role, Photo FROM Users WHERE Username = ? AND Password = ?");
    $stmt->bind_param("ss", $inputUsername, $inputPassword);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows > 0) {
        // Bind all user data
        $stmt->bind_result($firstName, $middleInitial, $lastName, $idNumber, $birthday, 
        $addressDetails, $phoneNumber, $gender, $email, $role, $photo);
        $stmt->fetch();

        // Save user data to session
        $_SESSION['username'] = $inputUsername;
        $_SESSION['firstname'] = $firstName;
        $_SESSION['middleinitial'] = $middleInitial;
        $_SESSION['lastname'] = $lastName;
        $_SESSION['idnumber'] = $idNumber;
        $_SESSION['birthday'] = $birthday;
        $_SESSION['gender'] = $gender;
        $_SESSION['email'] = $email;
        $_SESSION['phonenumber'] = $phoneNumber;
        $_SESSION['addressdetails'] = $addressDetails;
        $_SESSION['role'] = $role;

        // Handle profile photo
        if ($photo) {
            $_SESSION['photo'] = base64_encode($photo);
        } else {
            // If no photo is set, make sure the session value is empty or use a default.
            $_SESSION['photo'] = null;
        }

        // Redirect based on role
        switch ($role) {
            case "admin":
                header("Location: adminlogin src/adminlogin.php");
                break;
            case "program head":
                header("Location: programhead src/programheadlogin.php");
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

        exit();
    } else {
        header("Location: index.php?error=1");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
