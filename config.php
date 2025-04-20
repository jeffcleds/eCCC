<?php
function connectDB() {
    $host = "localhost";
    $dbname = "CCCDB";
    $username = "root";
    $password = "";

    // Enable proper error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // Create connection
        $conn = new mysqli($host, $username, $password, $dbname);
        
        // Set charset to UTF-8
        $conn->set_charset("utf8");
        
        return $conn;
    } catch (mysqli_sql_exception $e) {
        // Handle connection error
        die("Database connection failed: " . $e->getMessage());
    }
}
?>