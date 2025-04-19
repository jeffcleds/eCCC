<?php

function connectDB() {
    $host = "localhost";
    $dbname = "CCCDB";
    $username = "root";
    $password = "";

    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = connectDB();
$conn->set_charset("utf8");
?>