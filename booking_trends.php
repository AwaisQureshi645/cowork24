<?php
session_start();
header('Content-Type: application/json');


$conn = new mysqli('localhost', 'root', '', 'coworker');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'head' && 
     $_SESSION['role'] !== 'financehead' && 
     $_SESSION['role'] !== 'floorHost' && 
     $_SESSION['role'] !== 'manager')) {
    header('Location: access_denied.php');
    exit();
}


$sql = "SELECT DATE_FORMAT(start_time, '%Y-%m') AS month, COUNT(*) AS count 
        FROM bookings 
        GROUP BY month 
        ORDER BY month ASC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$conn->close();

echo json_encode($data);
?>
