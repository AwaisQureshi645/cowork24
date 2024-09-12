<?php
session_start();
if(isset($_GET['TeamID']))
{
$TeamID=$_GET['TeamID'];
$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'head' && 
     $_SESSION['role'] !== 'financehead' && 
     $_SESSION['role'] !== 'floorHost' && 
     $_SESSION['role'] !== 'manager')) {
    header('Location: access_denied.php');
    exit();
}

$sql="Delete  from team where TeamID='$TeamID'";
$conn->query($sql);
}
header("location:/cowork/team.php");
exit;
?>