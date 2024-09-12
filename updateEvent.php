<?php
// updateEvent.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

if ($requestMethod === 'POST') {
  $eventId = $data['id'];
  $roomId = $data['room_id'];
  $roomType = $data['room_type'];
  $startTime = $data['start_time'];
  $endTime = $data['end_time'];

  $stmt = $conn->prepare("INSERT INTO bookings (id, room_id, room_type, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sisss", $eventId, $roomId, $roomType, $startTime, $endTime);
  $stmt->execute();
  echo json_encode(['status' => 'success', 'message' => 'Event saved.']);
} elseif ($requestMethod === 'DELETE') {
  $eventId = $data['id'];
  
  $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
  $stmt->bind_param("s", $eventId);
  $stmt->execute();
  echo json_encode(['status' => 'success', 'message' => 'Event deleted.']);
}

$conn->close();
?>
