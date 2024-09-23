<?php
// Database connection

$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Delete functionality
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    echo "<script>alert('Deleting record with Event ID: " . $delete_id . "');</script>";  // Debugging
    $delete_sql = "DELETE FROM bookingsss WHERE booking_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $delete_id);
    if ($stmt->execute()) {
        echo "<script>alert('Record deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting record: " . $stmt->error . "');</script>";
    }
    $stmt->close();
    header('Location: cal.php');
    exit();
}


// Edit functionality - Fetch data for the record to be edited
$edit_id = null;
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_sql = "SELECT * FROM bookingsss WHERE room_id = ?";
    $stmt = $conn->prepare($edit_sql);
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}




// Handle update (editing)
if (isset($_POST['update'])) {
    $room_id = intval($_POST['room_id']);
    $room_type = $_POST['room_type'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $team_name = $_POST['team_name'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $point_of_contact = $_POST['point_of_contact'];
    $summary = $_POST['summary'];

    $update_sql = "UPDATE bookingsss SET room_type = ?, start_time = ?, end_time = ?, team_name = ?, location = ?, description = ?, point_of_contact = ?, summary = ? WHERE room_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('ssssssssi', $room_type, $start_time, $end_time, $team_name, $location, $description, $point_of_contact, $summary, $room_id);
    if ($stmt->execute()) {
        echo "<script>alert('Record updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating record!');</script>";
    }
    $stmt->close();
    header('Location: show_table.php');
    exit();
}

// Fetch all data from bookingsss table for display
$sql = "SELECT * FROM bookingsss";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Table</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Upcoming Events</h2>

<table>
    <tr>
        <th>Room ID</th>
        <th>Room Type</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Team Name</th>
        <th>Location</th>
        <th>Description</th>
        <th>Point of Contact</th>
        <th>Summary</th>
        <th>Actions</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['room_id']}</td>
                    <td>{$row['room_type']}</td>
                    <td>{$row['start_time']}</td>
                    <td>{$row['end_time']}</td>
                    <td>{$row['team_name']}</td>
                    <td>{$row['location']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['point_of_contact']}</td>
                    <td>{$row['summary']}</td>
                    <td>
                    <a href='view_booking.php?edit_id={$row['room_id']}'>Edit</a> | 
                        <a href='view_booking.php?delete_id={$row['booking_id']}' onclick='return confirm(\"Are you sure you want to delete this record?\");'>Delete</a>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='10'>No records found</td></tr>";
    }
    ?>
</table>

<!-- Edit form (only shown if editing a record) -->
<?php if ($edit_data): ?>
    <h2>Edit Booking</h2>
    <form method="POST" action="show_table.php">
        <input type="hidden" name="room_id" value="<?php echo $edit_data['room_id']; ?>">
        <label for="room_type">Room Type:</label>
        <input type="text" name="room_type" value="<?php echo $edit_data['room_type']; ?>" required><br>
        <label for="start_time">Start Time:</label>
        <input type="datetime-local" name="start_time" value="<?php echo date('Y-m-d\TH:i', strtotime($edit_data['start_time'])); ?>" required><br>
        <label for="end_time">End Time:</label>
        <input type="datetime-local" name="end_time" value="<?php echo date('Y-m-d\TH:i', strtotime($edit_data['end_time'])); ?>" required><br>
        <label for="team_name">Team Name:</label>
        <input type="text" name="team_name" value="<?php echo $edit_data['team_name']; ?>" required><br>
        <label for="location">Location:</label>
        <input type="text" name="location" value="<?php echo $edit_data['location']; ?>" required><br>
        <label for="description">Description:</label>
        <textarea name="description" required><?php echo $edit_data['description']; ?></textarea><br>
        <label for="point_of_contact">Point of Contact:</label>
        <input type="text" name="point_of_contact" value="<?php echo $edit_data['point_of_contact']; ?>" required><br>
        <label for="summary">Summary:</label>
        <input type="text" name="summary" value="<?php echo $edit_data['summary']; ?>" required><br>
        <button type="submit" name="update">Update Booking</button>
    </form>
<?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
