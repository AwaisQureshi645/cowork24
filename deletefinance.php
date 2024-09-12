<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "coworker");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$booking_id = $_GET['id'] ?? '';

if ($booking_id) {
    // Delete booking record
    $sql = "DELETE FROM office_bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        header("Location: financedisplay.php");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
