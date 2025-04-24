<?php
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $phone_number = $_POST['phone'];

    $conn = connectDB(); // ✅ Call the function to get the connection

    $query = "SELECT UserID FROM Users WHERE IDNumber = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->store_result(); // ✅ Required for rowCount-like behavior in MySQLi

    if ($stmt->num_rows > 0) {
        $insertQuery = "INSERT INTO ForgotPasswordSubmissions (IDNumber, PhoneNumber) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ss", $student_id, $phone_number);
        $insertStmt->execute();

        // Redirect with success message
        $successMsg = urlencode("Password reset request has been submitted successfully.");
        header("Location: index.php?success=$successMsg");
        exit;
    } else {
        // Student ID not found
        $errorMsg = urlencode("Student ID not found. Please check the ID and try again.");
        header("Location: index.php?error=$errorMsg");
        exit;
    }
}
?>
