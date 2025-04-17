<?php
// Start session if not already started
if (!isset($_SESSION) && !headers_sent()) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection function
function connectDB() {
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbname = "CCCDB";

    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Get date from request
$date = isset($_GET['date']) ? $_GET['date'] : '';

if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid date format']);
    exit();
}

// Get events for the specified date
$conn = connectDB();
$startDate = $date . ' 00:00:00';
$endDate = $date . ' 23:59:59';

$stmt = $conn->prepare("SELECT * FROM Events WHERE (StartDate <= ? AND EndDate >= ?) ORDER BY IsAllDay DESC, StartDate");
$stmt->bind_param("ss", $endDate, $startDate);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $startDateTime = new DateTime($row['StartDate']);
    $endDateTime = new DateTime($row['EndDate']);
    
    $events[] = [
        'id' => $row['EventID'],
        'title' => $row['Title'],
        'description' => $row['Description'],
        'startDate' => $startDateTime->format('Y-m-d'),
        'startTime' => $startDateTime->format('H:i'),
        'endDate' => $endDateTime->format('Y-m-d'),
        'endTime' => $endDateTime->format('H:i'),
        'isAllDay' => (bool)$row['IsAllDay'],
        'location' => $row['Location'],
        'type' => $row['EventType'],
        'color' => $row['Color']
    ];
}

$stmt->close();
$conn->close();

// Return events as JSON
header('Content-Type: application/json');
echo json_encode($events);