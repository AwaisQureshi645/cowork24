<?php

if(isset($_GET['meetingRoomID']))
{
    $meetingRoomID=$_GET['meetingRoomID'];
    $host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql="Delete  from meetingroom where meetingRoomID='$meetingRoomID'";
$conn->query($sql);
}
header("location:/cowork/meetingRoom.php");
exit;
?>