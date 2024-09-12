<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coworker";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['roomId'], $data['startTime'], $data['endTime'], $data['roomType'])) {
    $roomId = $data['roomId'];
    $startTime = date('Y-m-d H:i:s', strtotime($data['startTime']));
    $endTime = date('Y-m-d H:i:s', strtotime($data['endTime']));
    $roomType = $data['roomType'];

    $checkRoomQuery = "";
    $roomQuery = "";

    if ($roomType == 'meeting') {
        $checkRoomQuery = "SELECT meetingRoomID FROM meetingroom WHERE meetingRoomID = $roomId AND meetingRoomID NOT IN (
            SELECT room_id FROM bookings WHERE room_type = 'meeting' AND (
                (start_time < '$endTime' AND end_time > '$startTime')
            )
        )";
        $roomQuery = "SELECT meetingRoomID as id, name FROM meetingroom WHERE meetingRoomID = $roomId";
    } elseif ($roomType == 'huddle') {
        $checkRoomQuery = "SELECT huddleRoomID FROM huddleroom WHERE huddleRoomID = $roomId AND huddleRoomID NOT IN (
            SELECT room_id FROM bookings WHERE room_type = 'huddle' AND (
                (start_time < '$endTime' AND end_time > '$startTime')
            )
        )";
        $roomQuery = "SELECT huddleRoomID as id, name FROM huddleroom WHERE huddleRoomID = $roomId";
    } else {
        echo json_encode(['error' => 'Invalid room type']);
        exit;
    }

    $result = $conn->query($checkRoomQuery);
    if ($result === false) {
        error_log("SQL Error: " . $conn->error);
        echo json_encode(['error' => 'SQL Error']);
        exit;
    }

    if ($result->num_rows > 0) {
        $roomResult = $conn->query($roomQuery);
        if ($roomResult === false) {
            error_log("SQL Error: " . $conn->error);
            echo json_encode(['error' => 'SQL Error']);
            exit;
        }
        
        $roomData = $roomResult->fetch_assoc();
        echo json_encode(['available' => true, 'room' => $roomData]);
    } else {
        echo json_encode(['available' => false, 'alternatives' => []]);
    }
} else {
    echo json_encode(['error' => 'Invalid input']);
}

$conn->close();
?>
