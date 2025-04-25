<?php
include '../config.php'; 
include 'session_init.php'; 

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Fetch tickets from DB
$conn = connectDB();
$sql = "SELECT TicketNumber, Name, Email, Issue, SubmittedAt FROM ContactSupportSubmissions ORDER BY SubmittedAt DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
        /* Styling for the table */
        .support-tickets-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .support-tickets-table th, .support-tickets-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .support-tickets-table th {
            background-color: #f4f4f4;
            font-weight: 600;
        }

        .support-tickets-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .support-tickets-table td {
            word-wrap: break-word;
        }

        /* Styling for the section */
        .support-tickets-section {
            padding: 2rem;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .support-tickets-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .support-tickets-table th, .support-tickets-table td {
                padding: 8px;
            }

            .support-tickets-section {
                padding: 1.5rem;
            }

            .support-tickets-table {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .support-tickets-table th, .support-tickets-table td {
                padding: 6px;
                font-size: 12px;
            }

            .support-tickets-section h2 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <?php include 'header.php'; ?>

        <!-- Support Tickets Section -->
        <section style="padding: 2rem;">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Support Tickets</h2>
            <?php if ($result->num_rows > 0): ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; border: 1px solid #ccc;">
                        <thead style="background:#f0f0f0;">
                            <tr>
                                <th style="padding:10px; border:1px solid #ccc;">Ticket #</th>
                                <th style="padding:10px; border:1px solid #ccc;">Name</th>
                                <th style="padding:10px; border:1px solid #ccc;">Email</th>
                                <th style="padding:10px; border:1px solid #ccc;">Issue</th>
                                <th style="padding:10px; border:1px solid #ccc;">Submitted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td style="padding:10px; border:1px solid #ccc;"><?php echo $row["TicketNumber"]; ?></td>
                                    <td style="padding:10px; border:1px solid #ccc;"><?php echo htmlspecialchars($row["Name"]); ?></td>
                                    <td style="padding:10px; border:1px solid #ccc;"><?php echo htmlspecialchars($row["Email"]); ?></td>
                                    <td style="padding:10px; border:1px solid #ccc;"><?php echo nl2br(htmlspecialchars($row["Issue"])); ?></td>
                                    <td style="padding:10px; border:1px solid #ccc;"><?php echo $row["SubmittedAt"]; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No tickets found.</p>
            <?php endif; ?>
        </section>

    </main>

</body>
</html>
