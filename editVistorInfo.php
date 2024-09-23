<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user has the proper role
if (
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'head' &&
        $_SESSION['role'] !== 'financehead' &&
        $_SESSION['role'] !== 'manager' &&
        $_SESSION['role'] !== 'floorHost')
) {
    header('Location: access_denied.php');
    exit();
}

// Check if 'sno' is set in the URL
if (!isset($_GET['sno'])) {
    die("Invalid Request");
}

$sno = (int)$_GET['sno'];

// Handle the form submission for editing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $businessDetails = $_POST['businessDetails'];
    $phoneNumber = $_POST['phoneNumber'];
    $branch_id = $_POST['branch_id'];
    $comments = $_POST['comments'];
    $assignedTo = $_POST['assignedTo'];
    $appointmentDate = $_POST['appointmentDate'];

    // Update query
    $sql = "UPDATE visitorsinfo 
            SET name = ?, email = ?, businessDetails = ?, phonenumber = ?, branch_id = ?, Comments = ?, assignedTo = ?, appointment_date = ?
            WHERE sno = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssisssi', $name, $email, $businessDetails, $phoneNumber, $branch_id, $comments, $assignedTo, $appointmentDate, $sno);

    if ($stmt->execute()) {
        echo "<script>alert('Visitor information updated successfully!'); window.location.href = 'visits.php';</script>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Fetch the visitor's current data
$sql = "SELECT * FROM visitorsinfo WHERE sno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $sno);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Visitor not found.");
}

$visitor = $result->fetch_assoc();

// Fetch branch information for dropdown
$branches_sql = "SELECT branch_id, branch_name FROM branches";
$branches_result = $conn->query($branches_sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Visitor Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

<style>
 body{
    background-color: white !important;
 }
</style>

</head>

<body>
    <div class="container mt-5">
        <h2>Edit Visitor Information</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($visitor['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($visitor['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="businessDetails" class="form-label">Business Details</label>
                <textarea class="form-control" id="businessDetails" name="businessDetails" required><?= htmlspecialchars($visitor['businessDetails']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="phoneNumber" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="<?= htmlspecialchars($visitor['phonenumber']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="branch_id" class="form-label">Branch</label>
                <select class="form-select" id="branch_id" name="branch_id" required>
                    <?php while ($branch = $branches_result->fetch_assoc()): ?>
                        <option value="<?= $branch['branch_id'] ?>" <?= $branch['branch_id'] == $visitor['branch_id'] ? 'selected' : '' ?>><?= htmlspecialchars($branch['branch_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="comments" class="form-label">Comments</label>
                <textarea class="form-control" id="comments" name="comments"><?= htmlspecialchars($visitor['Comments']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="assignedTo" class="form-label">Assigned To</label>
                <input type="text" class="form-control" id="assignedTo" name="assignedTo" value="<?= htmlspecialchars($visitor['assignedTo']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="appointmentDate" class="form-label">Appointment Date</label>
                <!-- <input type="date" class="form-control" id="appointmentDate" name="appointmentDate" value="<?= htmlspecialchars($visitor['appointment_date']) ?>" required onclick="this.showPicker();"> -->
                <input type="datetime-local" class="form-control" id="appointmentDate" name="appointmentDate" value="<?= htmlspecialchars($visitor['appointment_date']) ?>" required onclick="this.showPicker();">
                <!-- <input type="date" class="form-control" id="appointmentDate" name="appointmentDate" value="<?= htmlspecialchars($visitor['appointment_date']) ?>" onclick="this.showPicker();"> -->
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="visits.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>

</html>

<?php
$conn->close();
?>