<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coworker";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);
$eventId = $conn->real_escape_string($data['eventId']);

// Ensure event ID is not empty
if (empty($eventId)) {
    echo json_encode(['success' => false, 'message' => 'Event ID is missing.']);
    exit;
}

// Verify the column name used in the database
$sql = "DELETE FROM bookings WHERE event_id='$eventId'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
}

$conn->close();
?>
