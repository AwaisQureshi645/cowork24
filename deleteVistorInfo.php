<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user has the proper role
if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'head' && 
     $_SESSION['role'] !== 'financehead' && 
     $_SESSION['role'] !== 'manager' && 
     $_SESSION['role'] !== 'floorHost')) {
    header('Location: access_denied.php');
    exit();
}

// Check if 'sno' is set in the URL
if (!isset($_GET['sno'])) {
    die("Invalid Request");
}

$sno = (int)$_GET['sno'];

// Prepare and execute the delete query
$sql = "DELETE FROM visitorsinfo WHERE sno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $sno);

if ($stmt->execute()) {
    echo "<script>alert('Visitor information deleted successfully!'); window.location.href = 'visits.php';</script>";
} else {
    echo "Error deleting record: " . $conn->error;
}

$conn->close();
?>
