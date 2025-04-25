<?php
include("../config.php");
$conn = connectDB();

$sql = "SELECT * FROM Users WHERE Role = 'registrar'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Management - Calabanga Community College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="adminloginstyles.css">
    <style>
    .main-content {
      flex: 1;
      margin-left: var(--sidebar-width);
      padding: 30px;
      background-color: var(--bg-light);
      min-height: 100vh;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .page-header h2 {
      color: var(--primary-color);
      font-size: 24px;
      font-weight: 600;
    }

    .btn {
      background-color: var(--accent-color);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
      transition: 0.3s ease;
      text-decoration: none;
    }

    .btn:hover {
      background-color: #21867a;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: var(--bg-white);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--shadow);
    }

    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
      font-size: 14px;
    }

    th {
      background-color: var(--primary-color);
      color: white;
    }

    tr:hover {
      background-color: var(--bg-light);
    }

    .action-btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      font-size: 12px;
      cursor: pointer;
      margin-right: 5px;
    }

    .edit-btn {
      background-color: var(--warning-color);
      color: white;
    }

    .delete-btn {
      background-color: var(--danger-color);
      color: white;
    }

    .no-data {
      text-align: center;
      padding: 20px;
      font-size: 16px;
      color: var(--light-text);
    }
  </style>
</head>
<body>
  <?php include("sidebar.php"); ?>
  <div class="main-content">
    <div class="page-header">
      <h2>Registrar Management</h2>
      <a href="add-registrar.php" class="btn"><i class="fas fa-user-plus"></i> Add Registrar</a>
    </div>
    <div class="dashboard-card">
      <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
          <thead>
            <tr>
              <th>UserID</th>
              <th>Name</th>
              <th>ID Number</th>
              <th>Username</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?= $row["UserID"] ?></td>
                <td><?= $row["FirstName"] . " " . $row["LastName"] ?></td>
                <td><?= $row["IDNumber"] ?></td>
                <td><?= $row["Username"] ?></td>
                <td>
                  <a href="edit-registrar.php?id=<?= $row['UserID'] ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                  <a href="delete-registrar.php?id=<?= $row['UserID'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this registrar?');"><i class="fas fa-trash"></i></a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="no-data">No registrars found.</div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>