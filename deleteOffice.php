<?php

if(isset($_GET['OfficeID']))
{
    $OfficeID=$_GET['OfficeID'];
    $host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql="Delete  from office where OfficeID='$OfficeID'";
$conn->query($sql);
}
header("location:/cowork/office.php");
exit;
?>