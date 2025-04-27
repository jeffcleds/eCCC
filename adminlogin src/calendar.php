<?php
// Start session if not already started
include 'session_init.php';

// Global database connection
$GLOBALS['db_connection'] = null;

// Database connection function
function connectDB() {
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbname = "CCCDB";

    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Function to close database connection
function closeDB() {
    $conn = connectDB();
    if ($conn) {
        $conn->close();
    }
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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_event'])) {
    $conn = null;
    $success = false;
    $error = '';
    
    try {
        // Debug: Print all POST data
        error_log("=== START EVENT ADDITION DEBUG ===");
        error_log("POST Data: " . print_r($_POST, true));
        error_log("Session Data: " . print_r($_SESSION, true));
        
        $conn = connectDB();
        error_log("Database connection established");
        
        // Validate required fields
        if (empty($_POST['title'])) {
            throw new Exception("Event title is required");
        }
        if (empty($_POST['start_date'])) {
            throw new Exception("Start date is required");
        }
        if (empty($_POST['end_date'])) {
            throw new Exception("End date is required");
        }
        
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description'] ?? '');
        $startDate = $conn->real_escape_string($_POST['start_date']);
        $endDate = $conn->real_escape_string($_POST['end_date']);
        $location = $conn->real_escape_string($_POST['location'] ?? '');
        $eventType = $conn->real_escape_string($_POST['event_type'] ?? 'Meeting');
        $color = $conn->real_escape_string($_POST['color'] ?? '#4361ee');
        $isAllDay = isset($_POST['is_all_day']) ? 1 : 0;
        
        // Handle time fields
        if ($isAllDay) {
            $startTime = '00:00:00';
            $endTime = '23:59:59';
        } else {
            $startTime = isset($_POST['start_time']) ? $_POST['start_time'] . ':00' : '00:00:00';
            $endTime = isset($_POST['end_time']) ? $_POST['end_time'] . ':00' : '00:00:00';
        }
        
        $startDateTime = $startDate . ' ' . $startTime;
        $endDateTime = $endDate . ' ' . $endTime;
        
        // Debug: Print all processed values
        error_log("Processed Values:");
        error_log("Title: " . $title);
        error_log("Description: " . $description);
        error_log("Start DateTime: " . $startDateTime);
        error_log("End DateTime: " . $endDateTime);
        error_log("Location: " . $location);
        error_log("Event Type: " . $eventType);
        error_log("Color: " . $color);
        error_log("Is All Day: " . $isAllDay);
        
        // Get the current user's ID from session
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        error_log("User ID from session: " . $userId);
        
        // Check if user exists
        $checkUser = $conn->prepare("SELECT UserID FROM Users WHERE UserID = ?");
        if (!$checkUser) {
            throw new Exception("User check prepare failed: " . $conn->error);
        }
        
        $checkUser->bind_param("i", $userId);
        $checkUser->execute();
        $checkUser->store_result();
        
        error_log("User check result: " . $checkUser->num_rows . " rows found");
        
        if ($checkUser->num_rows > 0) {
            // Debug: Print the SQL query
            $sql = "INSERT INTO Events (Title, Description, StartDate, EndDate, Location, EventType, Color, IsAllDay, CreatedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            error_log("SQL Query: " . $sql);
            error_log("SQL Parameters: " . print_r([$title, $description, $startDateTime, $endDateTime, $location, $eventType, $color, $isAllDay, $userId], true));
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Event insert prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssssssii", $title, $description, $startDateTime, $endDateTime, $location, $eventType, $color, $isAllDay, $userId);
            
            if ($stmt->execute()) {
                $success = true;
                $successMessage = "Event added successfully!";
                error_log("Event added successfully");
                error_log("Last Insert ID: " . $conn->insert_id);
            } else {
                $error = "Error adding event: " . $stmt->error;
                error_log("Error adding event: " . $stmt->error);
            }
            
            $stmt->close();
        } else {
            $error = "Error: Invalid user ID. Please log in again.";
            error_log("Invalid user ID: " . $userId);
        }
        
        $checkUser->close();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        error_log("Exception: " . $e->getMessage());
    } finally {
        if ($conn) {
    $conn->close();
            error_log("Database connection closed");
        }
    }
    
    error_log("=== END EVENT ADDITION DEBUG ===");
    
    if ($success) {
        // Redirect only after all database operations are complete
        header("Location: calendar.php?year=" . $year . "&month=" . $month);
        exit();
    } else {
        $errorMessage = $error;
    }
}

// Get events for the current month
function getMonthEvents($year, $month, $eventType = '') {
    $events = [];
    $conn = null;
    
    try {
        $conn = connectDB();
    
    // Calculate start and end dates for the month
    $startDate = sprintf("%04d-%02d-01 00:00:00", $year, $month);
    $endDate = sprintf("%04d-%02d-%02d 23:59:59", $year, $month, date('t', mktime(0, 0, 0, $month, 1, $year)));
    
    // Build the query
        $query = "SELECT * FROM Events WHERE 
            (StartDate BETWEEN ? AND ?) OR 
            (EndDate BETWEEN ? AND ?) OR 
            (StartDate <= ? AND EndDate >= ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $startDate, $endDate, $startDate, $endDate, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error getting month events: " . $e->getMessage());
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
    
    return $events;
}

// Get upcoming events
function getUpcomingEvents($limit = 5) {
    $events = [];
    $conn = null;
    
    try {
        $conn = connectDB();
        
        $currentDateTime = date('Y-m-d H:i:s');
        $query = "SELECT * FROM Events WHERE EndDate >= ? ORDER BY StartDate LIMIT ?";
    
    $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $currentDateTime, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    
    $stmt->close();
    } catch (Exception $e) {
        error_log("Error getting upcoming events: " . $e->getMessage());
    } finally {
        if ($conn) {
    $conn->close();
        }
    }
    
    return $events;
}

// Get event types
function getEventTypes() {
    $types = [];
    $conn = null;
    
    try {
        $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT DISTINCT EventType FROM Events ORDER BY EventType");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $types[] = $row['EventType'];
    }
    
    $stmt->close();
    } catch (Exception $e) {
        error_log("Error getting event types: " . $e->getMessage());
    } finally {
        if ($conn) {
    $conn->close();
        }
    }
    
    return $types;
}

// Get events for a specific date
function getDateEvents($date, $events) {
    $dateEvents = [];
    $checkDate = new DateTime($date);
    
    foreach ($events as $event) {
        $startDate = new DateTime($event['StartDate']);
        $endDate = new DateTime($event['EndDate']);
        
        // Check if the event occurs on this date
        if ($checkDate >= $startDate && $checkDate <= $endDate) {
            $dateEvents[] = $event;
        }
    }
    
    return $dateEvents;
}

// Get month events
$monthEvents = getMonthEvents($year, $month, $eventType);

// Get upcoming events
$upcomingEvents = getUpcomingEvents();

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
        .calendar-container {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .calendar-main {
            background: var(--bg-white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-btn {
            background: var(--bg-light);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .weekday {
            text-align: center;
            font-weight: 500;
            color: var(--light-text);
            padding: 10px;
            font-size: 0.9rem;
        }

        .day {
            aspect-ratio: 1;
            background: var(--bg-light);
            border-radius: 10px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .day:hover {
            background: var(--bg-white);
            box-shadow: var(--shadow);
        }

        .day.today {
            background: var(--primary-color);
            color: white;
        }

        .day.other-month {
            opacity: 0.5;
        }

        .day-number {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .day-events {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .event-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .upcoming-events {
            background: var(--bg-white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .upcoming-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .event-card {
            padding: 15px;
            background: var(--bg-light);
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .event-card-title {
            font-weight: 500;
            margin-bottom: 5px;
            color: var(--text-color);
        }

        .event-card-time {
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .add-event-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .add-event-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--bg-white);
            border-radius: 10px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--light-text);
            padding: 5px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: var(--bg-light);
            color: var(--primary-color);
        }

        .event-info {
            padding: 20px;
            background: var(--bg-light);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .event-info h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .event-info p {
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: var(--danger-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--bg-light);
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
            transform: translateY(-1px);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .color-picker {
            display: flex;
            gap: 10px;
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
            border-color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .calendar-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="calendar-container">
            <div class="calendar-main">
            <div class="calendar-header">
                    <h1 class="calendar-title"><?php echo $monthName . ' ' . $year; ?></h1>
                <div class="calendar-nav">
                        <a href="?year=<?php echo $prevYear; ?>&month=<?php echo $prevMonth; ?>" class="nav-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="?year=<?php echo date('Y'); ?>&month=<?php echo date('m'); ?>" class="nav-btn">
                            <i class="fas fa-calendar-day"></i>
                        </a>
                        <a href="?year=<?php echo $nextYear; ?>&month=<?php echo $nextMonth; ?>" class="nav-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                </div>
            </div>
            
            <div class="calendar-grid">
                    <div class="weekday">Sun</div>
                    <div class="weekday">Mon</div>
                    <div class="weekday">Tue</div>
                    <div class="weekday">Wed</div>
                    <div class="weekday">Thu</div>
                    <div class="weekday">Fri</div>
                    <div class="weekday">Sat</div>
                    
                    <?php
                    // Previous month's days
                    $prevMonthDays = date('t', mktime(0, 0, 0, $month - 1, 1, $year));
                    for ($i = 0; $i < $dayOfWeek; $i++) {
                        $day = $prevMonthDays - $dayOfWeek + $i + 1;
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
                        if (!empty($dateEvents)) {
                            echo '<div class="day-events">';
                            foreach ($dateEvents as $event) {
                                echo '<div class="event-dot" style="background-color: ' . $event['Color'] . '"></div>';
                            }
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }
                    
                    // Next month's days
                    $daysAfter = 42 - ($dayOfWeek + $numberDays);
                    for ($i = 1; $i <= $daysAfter; $i++) {
                        echo '<div class="day other-month">';
                        echo '<div class="day-number">' . $i . '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="upcoming-events">
                <h2 class="upcoming-title">Upcoming Events</h2>
                <?php foreach ($upcomingEvents as $event): ?>
                    <div class="event-card" data-event-id="<?php echo $event['EventID']; ?>">
                        <div class="event-dot" style="background-color: <?php echo $event['Color']; ?>"></div>
                        <div class="event-card-title"><?php echo htmlspecialchars($event['Title']); ?></div>
                        <div class="event-card-time">
                            <?php
                            $startDate = new DateTime($event['StartDate']);
                            echo $event['IsAllDay'] ? 'All Day' : $startDate->format('g:i A');
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button class="add-event-btn" id="addEventBtn">
            <i class="fas fa-plus"></i>
        </button>
        
        <!-- Add Event Modal -->
        <div class="modal" id="addEventModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Add New Event</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <form id="addEventForm" method="post" action="calendar.php">
                    <input type="hidden" name="add_event" value="1">
                    <input type="hidden" name="year" value="<?php echo $year; ?>">
                    <input type="hidden" name="month" value="<?php echo $month; ?>">
                    <div class="form-group">
                        <label for="title">Event Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">Start Date *</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="start_time">Start Time</label>
                            <input type="time" id="start_time" name="start_time" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="end_date">End Date *</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="end_time">End Time</label>
                            <input type="time" id="end_time" name="end_time" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_all_day" id="is_all_day">
                            All Day Event
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="event_type">Event Type</label>
                        <select id="event_type" name="event_type" class="form-control">
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
                            <div class="color-option selected" style="background-color: #4361ee" data-color="#4361ee"></div>
                            <div class="color-option" style="background-color: #2a9d8f" data-color="#2a9d8f"></div>
                            <div class="color-option" style="background-color: #e63946" data-color="#e63946"></div>
                            <div class="color-option" style="background-color: #f4a261" data-color="#f4a261"></div>
                            <div class="color-option" style="background-color: #2a9d8f" data-color="#2a9d8f"></div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Add Event</button>
                        <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- View/Edit Event Modal -->
        <div class="modal" id="viewEventModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Event Details</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div id="eventDetails"></div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-primary" id="editEventBtn">Edit</button>
                    <button type="button" class="btn btn-danger" id="deleteEventBtn">Delete</button>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal handling
            const addEventModal = document.getElementById('addEventModal');
            const closeButtons = document.querySelectorAll('.close-modal');
            
            function openModal(modal) {
                modal.classList.add('active');
            }
            
            function closeModal(modal) {
                modal.classList.remove('active');
            }
            
            // Calendar Day Click
            const calendarDays = document.querySelectorAll('.day:not(.other-month)');
            calendarDays.forEach(day => {
                day.addEventListener('click', function() {
                    const date = this.dataset.date;
                    document.getElementById('start_date').value = date;
                    document.getElementById('end_date').value = date;
                    openModal(addEventModal);
                });
            });
            
            // Close modal buttons
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    closeModal(this.closest('.modal'));
                });
            });
            
            // Close modal when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal')) {
                    closeModal(e.target);
                }
            });
            
            // All Day Event Toggle
            const isAllDayCheckbox = document.getElementById('is_all_day');
            const timeInputs = document.querySelectorAll('input[type="time"]');
            
            isAllDayCheckbox.addEventListener('change', function() {
                timeInputs.forEach(input => {
                    input.disabled = this.checked;
                if (this.checked) {
                        input.value = '';
                    }
                });
            });
            
            // Color Picker
            const colorOptions = document.querySelectorAll('.color-option');
            const colorInput = document.getElementById('color');
            
            colorOptions.forEach(option => {
                option.addEventListener('click', function() {
                    colorOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    colorInput.value = this.dataset.color;
                });
            });
            
            // Form Submission
            const addEventForm = document.getElementById('addEventForm');
            addEventForm.addEventListener('submit', function(e) {
                // Basic validation
                const title = document.getElementById('title').value;
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                
                if (!title || !startDate || !endDate) {
                    alert('Please fill in all required fields');
                    e.preventDefault();
                    return;
                }
                
                // If all day event is checked, clear time values
                if (document.getElementById('is_all_day').checked) {
                    document.getElementById('start_time').value = '';
                    document.getElementById('end_time').value = '';
                }
                
                // Let the form submit normally
            });
        });
    </script>
</body>
</html>