<?php
header('Content-Type: application/json');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$input = file_get_contents("php://input");
error_log('Raw input: ' . $input);

$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

error_log('Decoded data: ' . print_r($data, true));

if (!isset($data['eventId']) || !isset($data['roomId']) || !isset($data['startTime']) || !isset($data['endTime']) || !isset($data['roomType'])) {
    http_response_code(400);
    error_log('Error: Missing required fields');
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$eventId = $data['eventId'];
$roomId = intval($data['roomId']);
$startTime = date('Y-m-d H:i:s', strtotime($data['startTime']));
$endTime = date('Y-m-d H:i:s', strtotime($data['endTime']));
$roomType = $data['roomType'];

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
    INSERT INTO bookings (event_id, room_id, room_type, start_time, end_time)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    room_id = VALUES(room_id),
    room_type = VALUES(room_type),
    start_time = VALUES(start_time),
    end_time = VALUES(end_time)
");

if (!$stmt) {
    error_log('Failed to prepare statement: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit();
}

$stmt->bind_param('sisss', $eventId, $roomId, $roomType, $startTime, $endTime);

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
