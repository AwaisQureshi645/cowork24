<?php
session_start();
$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("Ticket ID is required.");
}

$ticket_id = intval($_GET['id']);

// Fetch ticket data
$sql = "SELECT * FROM tickets WHERE ticket_id = $ticket_id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $ticket = $result->fetch_assoc();
} else {
    die("Ticket not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $priority = $_POST['priority'];
    $description = $_POST['description'];
    $closeup_date = $_POST['closeup_date'];
    $status = $_POST['status']; // Get the selected status from the form

    $update_sql = "
        UPDATE tickets 
        SET subject = ?, priority = ?, description = ?, closeup_date = ?, status = ? 
        WHERE ticket_id = ?
    ";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssi", $subject, $priority, $description, $closeup_date, $status, $ticket_id);
    
    if ($stmt->execute()) {
        echo "Ticket updated successfully.";
        // Optionally redirect after success
        header('Location: viewticket.php');
        exit();
    } else {
        echo "Error updating ticket: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ticket</title>
  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.btn{
    background-color: #ff8802;
    color: white;
}
.btn:hover{
    background-color: #ff8802;
    color: white;
}
.container{
    width: 100%;
    max-width: 600px;
    background-color: #ffffff;
}


</style>

</head>

<body>
    <div class="container">
        <h2>Edit Ticket</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo $ticket['subject']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="priority" class="form-label">Priority</label>
                <input type="text" class="form-control" id="priority" name="priority" value="<?php echo $ticket['priority']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" required><?php echo $ticket['description']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="closeup_date" class="form-label">Close-up Date</label>
                <input type="date" class="form-control" id="closeup_date" name="closeup_date" value="<?php echo $ticket['closeup_date']; ?>" required>
            </div>

            <!-- Add the status dropdown -->
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="Pending" <?php echo ($ticket['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Completed" <?php echo ($ticket['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>

            <button type="submit" class="btn ">Update Ticket</button>
        </form>
    </div>
</body>
</html>
