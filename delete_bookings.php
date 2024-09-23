<?php
// Start session
session_start();

// Database connection
$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Check if eventId is set in the POST request
$data = json_decode(file_get_contents("php://input"), true);
echo json_encode($data);
if (isset($data['eventId'])) {
    $delete_id = intval($data['eventId']); // Sanitize the input

    // Prepare the SQL delete query
    $delete_sql = "DELETE FROM bookingsss WHERE event_id = ?";
    $stmt = $conn->prepare($delete_sql);
    
    if ($stmt) {
        // Bind the parameter
        $stmt->bind_param('i', $delete_id);

        // Execute the statement
        if ($stmt->execute()) {
            // Success message
            echo json_encode(['message' => 'Record deleted successfully!']);
        } else {
            // Failure message
            echo json_encode(['error' => 'Error deleting record: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        // Prepare failed
        echo json_encode(['error' => 'Failed to prepare delete statement: ' . $conn->error]);
    }
} else {
    // If eventId is not set
    echo json_encode(['error' => 'Invalid request. No event ID provided.']);
}

// Close the database connection
$conn->close();
?>
