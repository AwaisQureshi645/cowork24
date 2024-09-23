<?php
header('Content-Type: application/json');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$input = file_get_contents("php://input");
error_log('Raw input: ' . $input);

$data = json_decode($input, true);
error_log('Decoded input: ' . print_r($data, true));
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

// if (!isset($data['eventId']) || !isset($data['roomId']) || !isset($data['startTime']) || !isset($data['endTime']) || !isset($data['roomType']) || !isset($data['teamName']) || !isset($data['location']) || !isset($data['description']) || !isset($data['pointOfContact'])) {
//     http_response_code(400);
//     error_log('Error: Missing required fields');
//     echo json_encode(['error' => 'Missing required fields']);
//     exit();
// }

$eventId = $data['eventId'];
$roomId = intval($data['roomId']);
$startTime = date('Y-m-d H:i:s', strtotime($data['startTime']));
$endTime = date('Y-m-d H:i:s', strtotime($data['endTime']));
$roomType = $data['roomType'];
$teamName = $data['teamName'];
$location = $data['location'];
$description = $data['description'];
$pointOfContact = $data['pointOfContact']; // Get from JSON
$summary = $data['summary'];
echo json_encode([
    'Event ID' => $eventId,
    'Room ID' => $roomId,
    'Start Time' => $startTime,
    'End Time' => $endTime,
    'Room Type' => $roomType,
    'Team Name' => $teamName,
    'Location' => $location,
    'Description' => $description,
    'Point of Contact' => $pointOfContact
]);

if (!in_array($roomType, ['meeting', 'huddle'])) {
    http_response_code(400);
    error_log('Error: Invalid room type');
    echo json_encode(['error' => 'Invalid room type']);
    exit();
}

$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    error_log('Database connection error: ' . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error']);
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO bookingsss (event_id, room_id, room_type, start_time, end_time, team_name, location, description, point_of_contact, summary)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    room_id = VALUES(room_id),
    room_type = VALUES(room_type),
    start_time = VALUES(start_time),
    end_time = VALUES(end_time),
    team_name = VALUES(team_name),
    location = VALUES(location),
    description = VALUES(description),
    point_of_contact = VALUES(point_of_contact),
    summary = VALUES(summary)

");

if (!$stmt) {
    error_log('Failed to prepare statement: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit();
}

$stmt->bind_param('sissssssss', $eventId, $roomId, $roomType, $startTime, $endTime, $teamName, $location, $description, $pointOfContact, $summary);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Booking saved successfully']);
} else {
    error_log('Failed to execute statement: ' . $stmt->error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to execute statement']);
}

$stmt->close();
$conn->close();
?>
