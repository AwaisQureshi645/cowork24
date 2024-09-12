<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database_name";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);

$eventId = $conn->real_escape_string($data['eventId']);
$summary = $conn->real_escape_string($data['summary']);
$location = $conn->real_escape_string($data['location']);
$description = $conn->real_escape_string($data['description']);
$start = $conn->real_escape_string($data['start']);
$end = $conn->real_escape_string($data['end']);

$sql = "UPDATE booking SET summary='$summary', location='$location', description='$description', start_time='$start', end_time='$end' WHERE id='$eventId'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
}

$conn->close();
?>
