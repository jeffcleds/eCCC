<?php
// Start session if not already started
include 'session_init.php';

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

// Initialize variables
$successMessage = "";
$errorMessage = "";
$userId = $_SESSION['user_id'] ?? 0;
$isAdmin = ($_SESSION['role'] === 'admin');
$isFaculty = ($_SESSION['role'] === 'faculty');

// Get current year and month
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$eventType = isset($_GET['event_type']) ? $_GET['event_type'] : '';

// Validate year and month
if ($year < 1970 || $year > 2100) {
    $year = intval(date('Y'));
}
if ($month < 1 || $month > 12) {
    $month = intval(date('m'));
}

// Get first day of the month
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$numberDays = date('t', $firstDayOfMonth);
$dateComponents = getdate($firstDayOfMonth);
$monthName = $dateComponents['month'];
$dayOfWeek = $dateComponents['wday']; // 0 for Sunday, 6 for Saturday

// Get previous and next month
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Handle event operations (add, edit, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = connectDB();
    
    // Add new event
    if (isset($_POST['add_event'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $startDate = $conn->real_escape_string($_POST['start_date']);
        $startTime = $conn->real_escape_string($_POST['start_time']);
        $endDate = $conn->real_escape_string($_POST['end_date']);
        $endTime = $conn->real_escape_string($_POST['end_time']);
        $location = $conn->real_escape_string($_POST['location']);
        $eventType = $conn->real_escape_string($_POST['event_type']);
        $color = $conn->real_escape_string($_POST['color']);
        $isAllDay = isset($_POST['is_all_day']) ? 1 : 0;
        
        $startDateTime = $startDate . ' ' . ($isAllDay ? '00:00:00' : $startTime . ':00');
        $endDateTime = $endDate . ' ' . ($isAllDay ? '23:59:59' : $endTime . ':00');
        
        $stmt = $conn->prepare("INSERT INTO Events (Title, Description, StartDate, EndDate, Location, EventType, Color, IsAllDay, CreatedBy) VALUES (?, ?, ?,?,?,?,?,?,?)");       
        $stmt->close();
    }
    
    // Edit event
    if (isset($_POST['edit_event'])) {
        $eventId = intval($_POST['event_id']);
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $startDate = $conn->real_escape_string($_POST['start_date']);
        $startTime = $conn->real_escape_string($_POST['start_time']);
        $endDate = $conn->real_escape_string($_POST['end_date']);
        $endTime = $conn->real_escape_string($_POST['end_time']);
        $location = $conn->real_escape_string($_POST['location']);
        $eventType = $conn->real_escape_string($_POST['event_type']);
        $color = $conn->real_escape_string($_POST['color']);
        $isAllDay = isset($_POST['is_all_day']) ? 1 : 0;
        
        $startDateTime = $startDate . ' ' . ($isAllDay ? '00:00:00' : $startTime . ':00');
        $endDateTime = $endDate . ' ' . ($isAllDay ? '23:59:59' : $endTime . ':00');
        
        // Check if user has permission to edit this event
        $canEdit = false;
        if ($isAdmin) {
            $canEdit = true;
        } else {
            $checkStmt = $conn->prepare("SELECT CreatedBy FROM Events WHERE EventID = ?");
            $checkStmt->bind_param("i", $eventId);
            $checkStmt->execute();
            $checkStmt->bind_result($createdBy);
            $checkStmt->fetch();
            $checkStmt->close();
            
            if ($createdBy == $userId) {
                $canEdit = true;
            }
        }
        
        if ($canEdit) {
            $stmt = $conn->prepare("UPDATE Events SET Title = ?, Description = ?, StartDate = ?, EndDate = ?, Location = ?, EventType = ?, Color = ?, IsAllDay = ? WHERE EventID = ?");
            $stmt->bind_param("sssssssii", $title, $description, $startDateTime, $endDateTime, $location, $eventType, $color, $isAllDay, $eventId);
            
            if ($stmt->execute()) {
                $successMessage = "Event updated successfully!";
            } else {
                $errorMessage = "Error updating event: " . $conn->error;
            }
            
            $stmt->close();
        } else {
            $errorMessage = "You don't have permission to edit this event.";
        }
    }
    
    // Delete event
    if (isset($_POST['delete_event'])) {
        $eventId = intval($_POST['event_id']);
        
        // Check if user has permission to delete this event
        $canDelete = false;
        if ($isAdmin) {
            $canDelete = true;
        } else {
            $checkStmt = $conn->prepare("SELECT CreatedBy FROM Events WHERE EventID = ?");
            $checkStmt->bind_param("i", $eventId);
            $checkStmt->execute();
            $checkStmt->bind_result($createdBy);
            $checkStmt->fetch();
            $checkStmt->close();
            
            if ($createdBy == $userId) {
                $canDelete = true;
            }
        }
        
        if ($canDelete) {
            $stmt = $conn->prepare("DELETE FROM Events WHERE EventID = ?");
            $stmt->bind_param("i", $eventId);
            
            if ($stmt->execute()) {
                $successMessage = "Event deleted successfully!";
            } else {
                $errorMessage = "Error deleting event: " . $conn->error;
            }
            
            $stmt->close();
        } else {
            $errorMessage = "You don't have permission to delete this event.";
        }
    }
    
    $conn->close();
}

// Get events for the current month
function getMonthEvents($year, $month, $eventType = '') {
    $conn = connectDB();
    $events = [];
    
    // Calculate start and end dates for the month
    $startDate = sprintf("%04d-%02d-01 00:00:00", $year, $month);
    $endDate = sprintf("%04d-%02d-%02d 23:59:59", $year, $month, date('t', mktime(0, 0, 0, $month, 1, $year)));
    
    // Build the query
    $query = "SELECT * FROM Events WHERE StartDate <= ? AND EndDate >= ? ";
    $params = [$endDate, $startDate];
    $types = "ss";
    
    if (!empty($eventType)) {
        $query .= "AND EventType = ? ";
        $params[] = $eventType;
        $types .= "s";
    }
    
    $query .= "ORDER BY StartDate";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $events;
}

// Get event types
function getEventTypes() {
    $conn = connectDB();
    $types = [];
    
    $stmt = $conn->prepare("SELECT DISTINCT EventType FROM Events ORDER BY EventType");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $types[] = $row['EventType'];
    }
    
    $stmt->close();
    $conn->close();
    
    return $types;
}

// Get events for a specific date
function getDateEvents($date, $events) {
    $dateEvents = [];
    
    foreach ($events as $event) {
        $startDate = new DateTime($event['StartDate']);
        $endDate = new DateTime($event['EndDate']);
        $checkDate = new DateTime($date);
        
        // Check if the event occurs on this date
        if ($checkDate >= $startDate && $checkDate <= $endDate) {
            $dateEvents[] = $event;
        }
    }
    
    return $dateEvents;
}

// Get month events
$monthEvents = getMonthEvents($year, $month, $eventType);

// Get event types for filter
$eventTypes = getEventTypes();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
        /* Calendar Page Specific Styles */
        .calendar-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .calendar-header {
            margin-bottom: 30px;
        }

        .calendar-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .calendar-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #e6f7ed;
            color: #0d8a53;
            border: 1px solid #0d8a53;
        }

        .alert-danger {
            background-color: #feeae9;
            color: #d63d62;
            border: 1px solid #d63d62;
        }

        .calendar-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .month-nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .month-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            min-width: 180px;
            text-align: center;
        }

        .nav-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f1f3f9;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background-color: #e2e6f0;
        }

        .filter-container {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            background-color: white;
        }

        .filter-select:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #4361ee;
            color: white;
        }

        .btn-primary:hover {
            background-color: #3a56d4;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid #ddd;
            color: #666;
        }

        .btn-outline:hover {
            background-color: #f8f9fa;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .calendar-grid {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 9px;
            margin-bottom: 10px;
        }

        .weekday {
            text-align: center;
            font-weight: 600;
            color: #333;
            padding: 10px;
        }

        .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .day {
            width: 150px;
            min-height: 120px;
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 10px;
            position: relative;
        }

        .day:hover {
            background-color: #f9f9f9;
        }

        .day-number {
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }

        .day.today {
            background-color: #f1f3f9;
            border-color: #4361ee;
        }

        .day.other-month {
            background-color: #f8f9fa;
            color: #aaa;
        }

        .day.other-month .day-number {
            color: #aaa;
        }

        .event {
            margin-bottom: 5px;
            padding: 5px;
            border-radius: 3px;
            font-size: 0.8rem;
            color: white;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .event-more {
            font-size: 0.8rem;
            color: #666;
            cursor: pointer;
            text-align: center;
            margin-top: 5px;
        }

        .event-more:hover {
            text-decoration: underline;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }

        .modal-overlay.active .modal {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #888;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .form-check input {
            width: auto;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .color-picker {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .color-option {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .color-option.selected {
            border-color: #333;
        }

        .event-details {
            margin-bottom: 20px;
        }

        .event-details-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .event-color {
            width: 16px;
            height: 16px;
            border-radius: 50%;
        }

        .event-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .event-info {
            margin-bottom: 10px;
        }

        .event-info-label {
            font-weight: 500;
            color: #555;
            margin-right: 5px;
        }

        .event-description {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .event-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .day-events-modal .modal {
            max-width: 500px;
        }

        .day-events-list {
            margin-top: 15px;
        }

        .day-event-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .day-event-item:hover {
            background-color: #f8f9fa;
        }

        .day-event-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .day-event-time {
            font-size: 0.8rem;
            color: #666;
            width: 100px;
        }

        .day-event-title {
            font-weight: 500;
            color: #333;
            flex: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .calendar-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }
            
            .calendar-nav {
                justify-content: space-between;
            }
            
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .weekdays, .days {
                gap: 5px;
            }
            
            .day {
                min-height: 80px;
                padding: 5px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="calendar-container">
            <div class="calendar-header">
                <h1>Calendar</h1>
                <p>View and manage events and activities</p>
            </div>
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="calendar-actions">
                <div class="calendar-nav">
                    <div class="month-nav">
                        <a href="?year=<?php echo $prevYear; ?>&month=<?php echo $prevMonth; ?>&event_type=<?php echo urlencode($eventType); ?>" class="nav-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <div class="month-title"><?php echo $monthName . ' ' . $year; ?></div>
                        <a href="?year=<?php echo $nextYear; ?>&month=<?php echo $nextMonth; ?>&event_type=<?php echo urlencode($eventType); ?>" class="nav-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <a href="?year=<?php echo date('Y'); ?>&month=<?php echo date('m'); ?>&event_type=<?php echo urlencode($eventType); ?>" class="btn btn-outline">Today</a>
                </div>
                
                <div class="filter-container">
                    <form method="get" action="calendar.php" class="filter-form">
                        <input type="hidden" name="year" value="<?php echo $year; ?>">
                        <input type="hidden" name="month" value="<?php echo $month; ?>">
                        <select name="event_type" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Event Types</option>
                            <?php foreach ($eventTypes as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo $eventType == $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    
                    <button id="addEventBtn" class="btn btn-primary btn-icon">
                        <i class="fas fa-plus"></i> Add Event
                    </button>
                </div>
            </div>
            
            <div class="calendar-grid">
                <div class="weekdays">
                    <div class="weekday">Sunday</div>
                    <div class="weekday">Monday</div>
                    <div class="weekday">Tuesday</div>
                    <div class="weekday">Wednesday</div>
                    <div class="weekday">Thursday</div>
                    <div class="weekday">Friday</div>
                    <div class="weekday">Saturday</div>
                </div>
                
                <div class="days">
                    <?php
                    // Previous month's days
                    $prevMonthDays = date('t', mktime(0, 0, 0, $month - 1, 1, $year));
                    for ($i = 0; $i < $dayOfWeek; $i++) {
                        $day = $prevMonthDays - $dayOfWeek + $i + 1;
                        $date = sprintf("%04d-%02d-%02d", $prevYear, $prevMonth, $day);
                        echo '<div class="day other-month">';
                        echo '<div class="day-number">' . $day . '</div>';
                        echo '</div>';
                    }
                    
                    // Current month's days
                    $currentDay = date('j');
                    $currentMonth = date('n');
                    $currentYear = date('Y');
                    
                    for ($i = 1; $i <= $numberDays; $i++) {
                        $date = sprintf("%04d-%02d-%02d", $year, $month, $i);
                        $isToday = ($i == $currentDay && $month == $currentMonth && $year == $currentYear);
                        
                        echo '<div class="day' . ($isToday ? ' today' : '') . '" data-date="' . $date . '">';
                        echo '<div class="day-number">' . $i . '</div>';
                        
                        // Get events for this day
                        $dateEvents = getDateEvents($date, $monthEvents);
                        $eventCount = count($dateEvents);
                        $maxDisplay = 3;
                        
                        // Display events (limited to 3)
                        for ($j = 0; $j < min($eventCount, $maxDisplay); $j++) {
                            $event = $dateEvents[$j];
                            echo '<div class="event" style="background-color: ' . $event['Color'] . ';" data-event-id="' . $event['EventID'] . '">';
                            if (!$event['IsAllDay']) {
                                $startTime = date('g:i A', strtotime($event['StartDate']));
                                echo $startTime . ' - ';
                            }
                            echo htmlspecialchars($event['Title']) . '</div>';
                        }
                        
                        // Show "more" indicator if there are more events
                        if ($eventCount > $maxDisplay) {
                            echo '<div class="event-more" data-date="' . $date . '">+' . ($eventCount - $maxDisplay) . ' more</div>';
                        }
                        
                        echo '</div>';
                    }
                    
                    // Next month's days
                    $daysAfter = 42 - ($dayOfWeek + $numberDays); // 42 = 6 rows of 7 days
                    for ($i = 1; $i <= $daysAfter; $i++) {
                        $date = sprintf("%04d-%02d-%02d", $nextYear, $nextMonth, $i);
                        echo '<div class="day other-month">';
                        echo '<div class="day-number">' . $i . '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Add Event Modal -->
        <div class="modal-overlay" id="addEventModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Add New Event</h2>
                    <button class="modal-close" id="closeAddEventModal">&times;</button>
                </div>
                <form id="addEventForm" method="post">
                    <input type="hidden" name="add_event" value="1">
                    <div class="form-group">
                        <label for="title">Event Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="start_time">Start Time</label>
                            <input type="time" id="start_time" name="start_time" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_time">End Time</label>
                            <input type="time" id="end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="is_all_day" name="is_all_day">
                        <label for="is_all_day">All Day Event</label>
                    </div>
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location">
                    </div>
                    <div class="form-group">
                        <label for="event_type">Event Type</label>
                        <select id="event_type" name="event_type" required>
                            <option value="Meeting">Meeting</option>
                            <option value="Academic">Academic</option>
                            <option value="Event">Event</option>
                            <option value="Training">Training</option>
                            <option value="Holiday">Holiday</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Event Color</label>
                        <input type="hidden" id="color" name="color" value="#4361ee">
                        <div class="color-picker">
                            <div class="color-option selected" style="background-color: #4361ee;" data-color="#4361ee"></div>
                            <div class="color-option" style="background-color: #0d8a53;" data-color="#0d8a53"></div>
                            <div class="color-option" style="background-color: #d63d62;" data-color="#d63d62"></div>
                            <div class="color-option" style="background-color: #f9a826;" data-color="#f9a826"></div>
                            <div class="color-option" style="background-color: #6c757d;" data-color="#6c757d"></div>
                            <div class="color-option" style="background-color: #9c27b0;" data-color="#9c27b0"></div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelAddEventBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Event</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Event Details Modal -->
        <div class="modal-overlay" id="eventDetailsModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Event Details</h2>
                    <button class="modal-close" id="closeEventDetailsModal">&times;</button>
                </div>
                <div class="event-details">
                    <div class="event-details-header">
                        <div class="event-color" id="event-details-color"></div>
                        <h3 class="event-title" id="event-details-title"></h3>
                    </div>
                    <div class="event-info">
                        <span class="event-info-label">Date:</span>
                        <span id="event-details-date"></span>
                    </div>
                    <div class="event-info">
                        <span class="event-info-label">Time:</span>
                        <span id="event-details-time"></span>
                    </div>
                    <div class="event-info">
                        <span class="event-info-label">Location:</span>
                        <span id="event-details-location"></span>
                    </div>
                    <div class="event-info">
                        <span class="event-info-label">Event Type:</span>
                        <span id="event-details-type"></span>
                    </div>
                    <div class="event-description" id="event-details-description"></div>
                </div>
                <div class="event-actions">
                    <button type="button" class="btn btn-outline" id="editEventBtn">Edit</button>
                    <button type="button" class="btn btn-danger" id="deleteEventBtn">Delete</button>
                </div>
                <form id="deleteEventForm" method="post" style="display: none;">
                    <input type="hidden" name="delete_event" value="1">
                    <input type="hidden" id="delete_event_id" name="event_id">
                </form>
            </div>
        </div>
        
        <!-- Edit Event Modal -->
        <div class="modal-overlay" id="editEventModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Event</h2>
                    <button class="modal-close" id="closeEditEventModal">&times;</button>
                </div>
                <form id="editEventForm" method="post">
                    <input type="hidden" name="edit_event" value="1">
                    <input type="hidden" id="edit_event_id" name="event_id">
                    <div class="form-group">
                        <label for="edit_title">Event Title</label>
                        <input type="text" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_start_date">Start Date</label>
                            <input type="date" id="edit_start_date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_start_time">Start Time</label>
                            <input type="time" id="edit_start_time" name="start_time" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_end_date">End Date</label>
                            <input type="date" id="edit_end_date" name="end_date" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_end_time">End Time</label>
                            <input type="time" id="edit_end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="edit_is_all_day" name="is_all_day">
                        <label for="edit_is_all_day">All Day Event</label>
                    </div>
                    <div class="form-group">
                        <label for="edit_location">Location</label>
                        <input type="text" id="edit_location" name="location">
                    </div>
                    <div class="form-group">
                        <label for="edit_event_type">Event Type</label>
                        <select id="edit_event_type" name="event_type" required>
                            <option value="Meeting">Meeting</option>
                            <option value="Academic">Academic</option>
                            <option value="Event">Event</option>
                            <option value="Training">Training</option>
                            <option value="Holiday">Holiday</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Event Color</label>
                        <input type="hidden" id="edit_color" name="color" value="#4361ee">
                        <div class="color-picker" id="edit_color_picker">
                            <div class="color-option" style="background-color: #4361ee;" data-color="#4361ee"></div>
                            <div class="color-option" style="background-color: #0d8a53;" data-color="#0d8a53"></div>
                            <div class="color-option" style="background-color: #d63d62;" data-color="#d63d62"></div>
                            <div class="color-option" style="background-color: #f9a826;" data-color="#f9a826"></div>
                            <div class="color-option" style="background-color: #6c757d;" data-color="#6c757d"></div>
                            <div class="color-option" style="background-color: #9c27b0;" data-color="#9c27b0"></div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelEditEventBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Event</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Day Events Modal -->
        <div class="modal-overlay" id="dayEventsModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Events for <span id="day-events-date"></span></h2>
                    <button class="modal-close" id="closeDayEventsModal">&times;</button>
                </div>
                <div class="day-events-list" id="day-events-list">
                    <!-- Events will be populated here -->
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add Event Modal
            const addEventBtn = document.getElementById('addEventBtn');
            const addEventModal = document.getElementById('addEventModal');
            const closeAddEventModal = document.getElementById('closeAddEventModal');
            const cancelAddEventBtn = document.getElementById('cancelAddEventBtn');
            const isAllDayCheckbox = document.getElementById('is_all_day');
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            
            // Set default dates
            const today = new Date();
            const todayFormatted = today.toISOString().split('T')[0];
            document.getElementById('start_date').value = todayFormatted;
            document.getElementById('end_date').value = todayFormatted;
            
            // Set default times
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const currentTime = `${hours}:${minutes}`;
            startTimeInput.value = currentTime;
            
            // Set end time to 1 hour later
            const endDate = new Date(now);
            endDate.setHours(now.getHours() + 1);
            const endHours = endDate.getHours().toString().padStart(2, '0');
            const endMinutes = endDate.getMinutes().toString().padStart(2, '0');
            const endTime = `${endHours}:${endMinutes}`;
            endTimeInput.value = endTime;
            
            addEventBtn.addEventListener('click', function() {
                addEventModal.classList.add('active');
            });
            
            closeAddEventModal.addEventListener('click', function() {
                addEventModal.classList.remove('active');
            });
            
            cancelAddEventBtn.addEventListener('click', function() {
                addEventModal.classList.remove('active');
            });
            
            // All Day Event Checkbox
            isAllDayCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    startTimeInput.disabled = true;
                    endTimeInput.disabled = true;
                } else {
                    startTimeInput.disabled = false;
                    endTimeInput.disabled = false;
                }
            });
            
            // Color Picker
            const colorOptions = document.querySelectorAll('.color-picker .color-option');
            const colorInput = document.getElementById('color');
            
            colorOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const color = this.getAttribute('data-color');
                    colorInput.value = color;
                    
                    // Remove selected class from all options
                    colorOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                });
            });
            
            // Event Details Modal
            const eventDetailsModal = document.getElementById('eventDetailsModal');
            const closeEventDetailsModal = document.getElementById('closeEventDetailsModal');
            const events = document.querySelectorAll('.event');
            const editEventBtn = document.getElementById('editEventBtn');
            const deleteEventBtn = document.getElementById('deleteEventBtn');
            const deleteEventForm = document.getElementById('deleteEventForm');
            const deleteEventId = document.getElementById('delete_event_id');
            
            events.forEach(event => {
                event.addEventListener('click', function() {
                    const eventId = this.getAttribute('data-event-id');
                    
                    // Fetch event details via AJAX
                    fetch(`get_event.php?id=${eventId}`)
                        .then(response => response.json())
                        .then(data => {
                            // For demonstration, we'll use hardcoded data
                            // In a real application, you would use the data from the AJAX response
                            const eventData = {
                                id: eventId,
                                title: this.textContent.includes('-') ? this.textContent.split('-')[1].trim() : this.textContent,
                                description: 'This is a sample event description. In a real application, this would be fetched from the database.',
                                startDate: '2025-04-20',
                                startTime: '09:00',
                                endDate: '2025-04-20',
                                endTime: '11:00',
                                isAllDay: false,
                                location: 'Conference Room A',
                                type: 'Meeting',
                                color: this.style.backgroundColor
                            };
                            
                            // Populate event details
                            document.getElementById('event-details-color').style.backgroundColor = eventData.color;
                            document.getElementById('event-details-title').textContent = eventData.title;
                            document.getElementById('event-details-date').textContent = formatDate(eventData.startDate, eventData.endDate);
                            document.getElementById('event-details-time').textContent = eventData.isAllDay ? 'All Day' : `${formatTime(eventData.startTime)} - ${formatTime(eventData.endTime)}`;
                            document.getElementById('event-details-location').textContent = eventData.location || 'Not specified';
                            document.getElementById('event-details-type').textContent = eventData.type;
                            document.getElementById('event-details-description').textContent = eventData.description || 'No description provided.';
                            
                            // Set event ID for edit and delete
                            deleteEventId.value = eventData.id;
                            
                            // Show modal
                            eventDetailsModal.classList.add('active');
                        })
                        .catch(error => {
                            console.error('Error fetching event details:', error);
                            
                            // For demonstration, we'll use hardcoded data
                            const eventData = {
                                id: eventId,
                                title: this.textContent.includes('-') ? this.textContent.split('-')[1].trim() : this.textContent,
                                description: 'This is a sample event description. In a real application, this would be fetched from the database.',
                                startDate: '2025-04-20',
                                startTime: '09:00',
                                endDate: '2025-04-20',
                                endTime: '11:00',
                                isAllDay: false,
                                location: 'Conference Room A',
                                type: 'Meeting',
                                color: this.style.backgroundColor
                            };
                            
                            // Populate event details
                            document.getElementById('event-details-color').style.backgroundColor = eventData.color;
                            document.getElementById('event-details-title').textContent = eventData.title;
                            document.getElementById('event-details-date').textContent = formatDate(eventData.startDate, eventData.endDate);
                            document.getElementById('event-details-time').textContent = eventData.isAllDay ? 'All Day' : `${formatTime(eventData.startTime)} - ${formatTime(eventData.endTime)}`;
                            document.getElementById('event-details-location').textContent = eventData.location || 'Not specified';
                            document.getElementById('event-details-type').textContent = eventData.type;
                            document.getElementById('event-details-description').textContent = eventData.description || 'No description provided.';
                            
                            // Set event ID for edit and delete
                            deleteEventId.value = eventData.id;
                            
                            // Show modal
                            eventDetailsModal.classList.add('active');
                        });
                });
            });
            
            closeEventDetailsModal.addEventListener('click', function() {
                eventDetailsModal.classList.remove('active');
            });
            
            // Edit Event
            const editEventModal = document.getElementById('editEventModal');
            const closeEditEventModal = document.getElementById('closeEditEventModal');
            const cancelEditEventBtn = document.getElementById('cancelEditEventBtn');
            const editEventId = document.getElementById('edit_event_id');
            const editTitle = document.getElementById('edit_title');
            const editDescription = document.getElementById('edit_description');
            const editStartDate = document.getElementById('edit_start_date');
            const editStartTime = document.getElementById('edit_start_time');
            const editEndDate = document.getElementById('edit_end_date');
            const editEndTime = document.getElementById('edit_end_time');
            const editIsAllDay = document.getElementById('edit_is_all_day');
            const editLocation = document.getElementById('edit_location');
            const editEventType = document.getElementById('edit_event_type');
            const editColor = document.getElementById('edit_color');
            const editColorOptions = document.querySelectorAll('#edit_color_picker .color-option');
            
            editEventBtn.addEventListener('click', function() {
                const eventId = deleteEventId.value;
                
                // Fetch event details via AJAX
                fetch(`get_event.php?id=${eventId}`)
                    .then(response => response.json())
                    .then(data => {
                        // For demonstration, we'll use hardcoded data
                        // In a real application, you would use the data from the AJAX response
                        const eventData = {
                            id: eventId,
                            title: document.getElementById('event-details-title').textContent,
                            description: document.getElementById('event-details-description').textContent,
                            startDate: '2025-04-20',
                            startTime: '09:00',
                            endDate: '2025-04-20',
                            endTime: '11:00',
                            isAllDay: false,
                            location: document.getElementById('event-details-location').textContent,
                            type: document.getElementById('event-details-type').textContent,
                            color: document.getElementById('event-details-color').style.backgroundColor
                        };
                        
                        // Populate edit form
                        editEventId.value = eventData.id;
                        editTitle.value = eventData.title;
                        editDescription.value = eventData.description === 'No description provided.' ? '' : eventData.description;
                        editStartDate.value = eventData.startDate;
                        editStartTime.value = eventData.startTime;
                        editEndDate.value = eventData.endDate;
                        editEndTime.value = eventData.endTime;
                        editIsAllDay.checked = eventData.isAllDay;
                        editLocation.value = eventData.location === 'Not specified' ? '' : eventData.location;
                        editEventType.value = eventData.type;
                        
                        // Set color
                        const colorHex = rgbToHex(eventData.color);
                        editColor.value = colorHex;
                        
                        // Update color picker
                        editColorOptions.forEach(option => {
                            option.classList.remove('selected');
                            if (option.getAttribute('data-color') === colorHex) {
                                option.classList.add('selected');
                            }
                        });
                        
                        // Handle all day event
                        if (eventData.isAllDay) {
                            editStartTime.disabled = true;
                            editEndTime.disabled = true;
                        } else {
                            editStartTime.disabled = false;
                            editEndTime.disabled = false;
                        }
                        
                        // Hide event details modal and show edit modal
                        eventDetailsModal.classList.remove('active');
                        editEventModal.classList.add('active');
                    })
                    .catch(error => {
                        console.error('Error fetching event details for edit:', error);
                        
                        // For demonstration, we'll use hardcoded data
                        const eventData = {
                            id: eventId,
                            title: document.getElementById('event-details-title').textContent,
                            description: document.getElementById('event-details-description').textContent,
                            startDate: '2025-04-20',
                            startTime: '09:00',
                            endDate: '2025-04-20',
                            endTime: '11:00',
                            isAllDay: false,
                            location: document.getElementById('event-details-location').textContent,
                            type: document.getElementById('event-details-type').textContent,
                            color: document.getElementById('event-details-color').style.backgroundColor
                        };
                        
                        // Populate edit form
                        editEventId.value = eventData.id;
                        editTitle.value = eventData.title;
                        editDescription.value = eventData.description === 'No description provided.' ? '' : eventData.description;
                        editStartDate.value = eventData.startDate;
                        editStartTime.value = eventData.startTime;
                        editEndDate.value = eventData.endDate;
                        editEndTime.value = eventData.endTime;
                        editIsAllDay.checked = eventData.isAllDay;
                        editLocation.value = eventData.location === 'Not specified' ? '' : eventData.location;
                        editEventType.value = eventData.type;
                        
                        // Set color
                        const colorHex = rgbToHex(eventData.color);
                        editColor.value = colorHex;
                        
                        // Update color picker
                        editColorOptions.forEach(option => {
                            option.classList.remove('selected');
                            if (option.getAttribute('data-color') === colorHex) {
                                option.classList.add('selected');
                            }
                        });
                        
                        // Handle all day event
                        if (eventData.isAllDay) {
                            editStartTime.disabled = true;
                            editEndTime.disabled = true;
                        } else {
                            editStartTime.disabled = false;
                            editEndTime.disabled = false;
                        }
                        
                        // Hide event details modal and show edit modal
                        eventDetailsModal.classList.remove('active');
                        editEventModal.classList.add('active');
                    });
            });
            
            closeEditEventModal.addEventListener('click', function() {
                editEventModal.classList.remove('active');
            });
            
            cancelEditEventBtn.addEventListener('click', function() {
                editEventModal.classList.remove('active');
            });
            
            // Edit All Day Event Checkbox
            editIsAllDay.addEventListener('change', function() {
                if (this.checked) {
                    editStartTime.disabled = true;
                    editEndTime.disabled = true;
                } else {
                    editStartTime.disabled = false;
                    editEndTime.disabled = false;
                }
            });
            
            // Edit Color Picker
            editColorOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const color = this.getAttribute('data-color');
                    editColor.value = color;
                    
                    // Remove selected class from all options
                    editColorOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                });
            });
            
            // Delete Event
            deleteEventBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this event?')) {
                    deleteEventForm.submit();
                }
            });
            
            // Day Events Modal
            const dayEventsModal = document.getElementById('dayEventsModal');
            const closeDayEventsModal = document.getElementById('closeDayEventsModal');
            const dayEventsList = document.getElementById('day-events-list');
            const dayEventsDate = document.getElementById('day-events-date');
            const eventMoreLinks = document.querySelectorAll('.event-more');
            
            eventMoreLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const date = this.getAttribute('data-date');
                    const formattedDate = new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                    dayEventsDate.textContent = formattedDate;
                    
                    // Clear previous events
                    dayEventsList.innerHTML = '';
                    
                    // Fetch events for this date via AJAX
                    fetch(`get_day_events.php?date=${date}`)
                        .then(response => response.json())
                        .then(data => {
                            // For demonstration, we'll use hardcoded data
                            // In a real application, you would use the data from the AJAX response
                            const events = [
                                {
                                    id: 1,
                                    title: 'Faculty Meeting',
                                    startTime: '09:00',
                                    endTime: '11:00',
                                    isAllDay: false,
                                    color: '#4361ee'
                                },
                                {
                                    id: 2,
                                    title: 'Department Meeting',
                                    startTime: '13:00',
                                    endTime: '14:30',
                                    isAllDay: false,
                                    color: '#4361ee'
                                },
                                {
                                    id: 3,
                                    title: 'Student Council Meeting',
                                    startTime: '15:00',
                                    endTime: '16:30',
                                    isAllDay: false,
                                    color: '#f9a826'
                                },
                                {
                                    id: 4,
                                    title: 'Enrollment Deadline',
                                    isAllDay: true,
                                    color: '#d63d62'
                                }
                            ];
                            
                            // Populate events list
                            events.forEach(event => {
                                const eventItem = document.createElement('div');
                                eventItem.className = 'day-event-item';
                                eventItem.setAttribute('data-event-id', event.id);
                                
                                const eventColor = document.createElement('div');
                                eventColor.className = 'day-event-color';
                                eventColor.style.backgroundColor = event.color;
                                
                                const eventTime = document.createElement('div');
                                eventTime.className = 'day-event-time';
                                eventTime.textContent = event.isAllDay ? 'All Day' : `${formatTime(event.startTime)} - ${formatTime(event.endTime)}`;
                                
                                const eventTitle = document.createElement('div');
                                eventTitle.className = 'day-event-title';
                                eventTitle.textContent = event.title;
                                
                                eventItem.appendChild(eventColor);
                                eventItem.appendChild(eventTime);
                                eventItem.appendChild(eventTitle);
                                
                                dayEventsList.appendChild(eventItem);
                                
                                // Add click event to show event details
                                eventItem.addEventListener('click', function() {
                                    const eventId = this.getAttribute('data-event-id');
                                    
                                    // Hide day events modal
                                    dayEventsModal.classList.remove('active');
                                    
                                    // Simulate click on event to show details
                                    const eventElement = document.querySelector(`.event[data-event-id="${eventId}"]`);
                                    if (eventElement) {
                                        eventElement.click();
                                    } else {
                                        // Fetch event details via AJAX and show details modal
                                        // For demonstration, we'll use the event data we already have
                                        const eventData = events.find(e => e.id == eventId);
                                        
                                        // Populate event details
                                        document.getElementById('event-details-color').style.backgroundColor = eventData.color;
                                        document.getElementById('event-details-title').textContent = eventData.title;
                                        document.getElementById('event-details-date').textContent = formattedDate;
                                        document.getElementById('event-details-time').textContent = eventData.isAllDay ? 'All Day' : `${formatTime(eventData.startTime)} - ${formatTime(eventData.endTime)}`;
                                        document.getElementById('event-details-location').textContent = 'Not specified';
                                        document.getElementById('event-details-type').textContent = 'Meeting';
                                        document.getElementById('event-details-description').textContent = 'No description provided.';
                                        
                                        // Set event ID for edit and delete
                                        deleteEventId.value = eventData.id;
                                        
                                        // Show modal
                                        eventDetailsModal.classList.add('active');
                                    }
                                });
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching day events:', error);
                            
                            // For demonstration, we'll use hardcoded data
                            const events = [
                                {
                                    id: 1,
                                    title: 'Faculty Meeting',
                                    startTime: '09:00',
                                    endTime: '11:00',
                                    isAllDay: false,
                                    color: '#4361ee'
                                },
                                {
                                    id: 2,
                                    title: 'Department Meeting',
                                    startTime: '13:00',
                                    endTime: '14:30',
                                    isAllDay: false,
                                    color: '#4361ee'
                                },
                                {
                                    id: 3,
                                    title: 'Student Council Meeting',
                                    startTime: '15:00',
                                    endTime: '16:30',
                                    isAllDay: false,
                                    color: '#f9a826'
                                },
                                {
                                    id: 4,
                                    title: 'Enrollment Deadline',
                                    isAllDay: true,
                                    color: '#d63d62'
                                }
                            ];
                            
                            // Populate events list
                            events.forEach(event => {
                                const eventItem = document.createElement('div');
                                eventItem.className = 'day-event-item';
                                eventItem.setAttribute('data-event-id', event.id);
                                
                                const eventColor = document.createElement('div');
                                eventColor.className = 'day-event-color';
                                eventColor.style.backgroundColor = event.color;
                                
                                const eventTime = document.createElement('div');
                                eventTime.className = 'day-event-time';
                                eventTime.textContent = event.isAllDay ? 'All Day' : `${formatTime(event.startTime)} - ${formatTime(event.endTime)}`;
                                
                                const eventTitle = document.createElement('div');
                                eventTitle.className = 'day-event-title';
                                eventTitle.textContent = event.title;
                                
                                eventItem.appendChild(eventColor);
                                eventItem.appendChild(eventTime);
                                eventItem.appendChild(eventTitle);
                                
                                dayEventsList.appendChild(eventItem);
                                
                                // Add click event to show event details
                                eventItem.addEventListener('click', function() {
                                    const eventId = this.getAttribute('data-event-id');
                                    
                                    // Hide day events modal
                                    dayEventsModal.classList.remove('active');
                                    
                                    // Simulate click on event to show details
                                    const eventElement = document.querySelector(`.event[data-event-id="${eventId}"]`);
                                    if (eventElement) {
                                        eventElement.click();
                                    } else {
                                        // Fetch event details via AJAX and show details modal
                                        // For demonstration, we'll use the event data we already have
                                        const eventData = events.find(e => e.id == eventId);
                                        
                                        // Populate event details
                                        document.getElementById('event-details-color').style.backgroundColor = eventData.color;
                                        document.getElementById('event-details-title').textContent = eventData.title;
                                        document.getElementById('event-details-date').textContent = formattedDate;
                                        document.getElementById('event-details-time').textContent = eventData.isAllDay ? 'All Day' : `${formatTime(eventData.startTime)} - ${formatTime(eventData.endTime)}`;
                                        document.getElementById('event-details-location').textContent = 'Not specified';
                                        document.getElementById('event-details-type').textContent = 'Meeting';
                                        document.getElementById('event-details-description').textContent = 'No description provided.';
                                        
                                        // Set event ID for edit and delete
                                        deleteEventId.value = eventData.id;
                                        
                                        // Show modal
                                        eventDetailsModal.classList.add('active');
                                    }
                                });
                            });
                        });
                    
                    // Show modal
                    dayEventsModal.classList.add('active');
                });
            });
            
            closeDayEventsModal.addEventListener('click', function() {
                dayEventsModal.classList.remove('active');
            });
            
            // Day click event to add event on that day
            const days = document.querySelectorAll('.day:not(.other-month)');
            
            days.forEach(day => {
                day.addEventListener('dblclick', function() {
                    const date = this.getAttribute('data-date');
                    
                    // Set date in add event form
                    document.getElementById('start_date').value = date;
                    document.getElementById('end_date').value = date;
                    
                    // Show add event modal
                    addEventModal.classList.add('active');
                });
            });
            
            // Helper functions
            function formatDate(startDate, endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                
                if (startDate === endDate) {
                    return start.toLocaleDateString('en-US', options);
                } else {
                    return `${start.toLocaleDateString('en-US', options)} - ${end.toLocaleDateString('en-US', options)}`;
                }
            }
            
            function formatTime(time) {
                const [hours, minutes] = time.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const formattedHour = hour % 12 || 12;
                
                return `${formattedHour}:${minutes} ${ampm}`;
            }
            
            function rgbToHex(rgb) {
                // Check if already in hex format
                if (rgb.startsWith('#')) {
                    return rgb;
                }
                
                // Extract RGB values
                const rgbValues = rgb.match(/\d+/g);
                if (!rgbValues || rgbValues.length !== 3) {
                    return '#4361ee'; // Default color
                }
                
                // Convert to hex
                const r = parseInt(rgbValues[0]);
                const g = parseInt(rgbValues[1]);
                const b = parseInt(rgbValues[2]);
                
                return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
            }
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(function() {
                    alerts.forEach(function(alert) {
                        alert.style.display = 'none';
                    });
                }, 5000);
            }
        });
    </script>
</body>
</html>