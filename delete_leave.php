<?php
session_start();
if ($_SESSION['role'] !== 'head') {
    header('Location: access_denied.php');
    exit();
}
$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



$leaveID = $_GET['id'];
$query = "DELETE FROM leave_records WHERE leaveID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $leaveID);
$stmt->execute();

header('location: view_leaves.php');
?>
