<?php
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $phone_number = $_POST['phone'];

    $query = "SELECT UserID FROM Users WHERE IDNumber = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$student_id]);

    if ($stmt->rowCount() > 0) {
        $insertQuery = "INSERT INTO ForgotPasswordSubmissions (IDNumber, PhoneNumber) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->execute([$student_id, $phone_number]);

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
