<?php

if(isset($_GET['huddleroomID']))
{
    $huddleroomID=$_GET['huddleroomID'];
    $host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql="Delete  from huddleroom where huddleroomID='$huddleroomID'";
$conn->query($sql);
}
header("location:/cowork/huddleRoom.php");
exit;
?>