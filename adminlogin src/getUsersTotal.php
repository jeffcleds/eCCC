<?php
try {
    $conn = new PDO("mysql:host=localhost;dbname=CCCDB", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Function to get count by role
    function getCountByRole($conn, $role) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM Users WHERE Role = :role");
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['total'] : 0;
    }

    $totalStudents = getCountByRole($conn, 'student');
    $totalFaculty = getCountByRole($conn, 'faculty');
    $totalRegistrars = getCountByRole($conn, 'registrar');

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    $totalStudents = $totalFaculty = $totalRegistrars = 0;
}
?>