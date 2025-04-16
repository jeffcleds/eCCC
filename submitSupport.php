<?php
// Database connection settings
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbname = "CCCDB";

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from POST
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$issue = $_POST['issue'] ?? '';

// Basic validation
if (empty($name) || empty($email) || empty($issue)) {
    echo "All fields are required.";
    exit;
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO ContactSupportSubmissions (Name, Email, Issue) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $issue);

// Execute and respond
if ($stmt->execute()) {
    $ticketNumber = $stmt->insert_id;
    // Redirect with success message in URL
    header("Location: index.php?success=Your Ticket Number is: " . $ticketNumber);
    exit;
} else {
    echo "Error: " . $stmt->error;
}

// Close
$stmt->close();
$conn->close();
?>
