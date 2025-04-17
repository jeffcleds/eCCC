<?php
// MUST be first line
session_start();

echo "<h2>Final Session Verification</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Direct database verification
$conn = new mysqli("localhost", "root", "", "CCCDB");
$dbGender = $conn->query("SELECT Gender FROM Users WHERE Username = '".$_SESSION['username']."'")->fetch_assoc()['Gender'];
echo "<p>Database Gender: $dbGender</p>";
echo "<p>Session Gender: ".($_SESSION['gender'] ?? 'NOT SET')."</p>";

// Debug session file
echo "<h3>Session File Contents</h3>";
$sessionData = file_get_contents(session_save_path().'/sess_'.session_id());
echo htmlspecialchars($sessionData);
?>