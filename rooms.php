<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coworker";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    http_response_code(500); 
    echo json_encode(['error' => 'Database connection failed.']);
    exit();
}

if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'head' && 
     $_SESSION['role'] !== 'financehead' && 
     $_SESSION['role'] !== 'floorHost')) {
    http_response_code(403); 
    echo json_encode(['error' => 'Access denied.']);
    exit();
}

if (isset($_GET['type'])) {
    $type = $conn->real_escape_string($_GET['type']); 

    if ($type == 'meeting') {
        $sql = "SELECT meetingRoomID AS id, name FROM meetingroom";
    } elseif ($type == 'huddle') {
        $sql = "SELECT huddleRoomID AS id, name FROM huddleroom";
    } else {
        http_response_code(400); 
        echo json_encode(['error' => 'Invalid room type specified.']);
        exit();
    }

    $result = $conn->query($sql);

    if ($result === false) {
        http_response_code(500); 
        echo json_encode(['error' => 'Query execution failed.']);
        exit();
    }

    $rooms = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rooms[] = $row;
        }
    }

    echo json_encode($rooms);
} else {
    http_response_code(400); 
    echo json_encode(['error' => 'No room type specified.']);
}

$conn->close();
?>
