<?php
session_start();
if ($_SESSION['role'] !== 'head') {
    header('Location: access_denied.php');
    exit();
}

$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['contract_id'])) {
    die("Contract ID is required.");
}

$contract_id = intval($_GET['contract_id']);

// Fetch contract data
$sql = "
    SELECT contracts.*, COALESCE(coworkers.name, team.TeamName) AS coworker_name
    FROM contracts
    LEFT JOIN coworkers ON contracts.coworker_id = coworkers.coworker_id
    LEFT JOIN team ON contracts.TeamID = team.TeamID
    WHERE contracts.contract_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $contract_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $contract = $result->fetch_assoc();
} else {
    die("Contract not found.");
}

// Fetch list of coworkers for the dropdown
$coworkers_sql = "SELECT coworker_id, name FROM coworkers";
$coworkers_result = $conn->query($coworkers_sql);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $coworker_id = $_POST['coworker_id'];
    $contract_details = $_POST['contract_details'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $team_id = isset($_POST['team_id']) ? $_POST['team_id'] : null;

    // If a new file is uploaded, handle the file upload
    $contract_copy = $contract['contract_copy'];
    if (!empty($_FILES['contract_copy']['name'])) {
        $file_tmp = $_FILES['contract_copy']['tmp_name'];
        $file_name = basename($_FILES['contract_copy']['name']);
        $target_dir = "uploads/contracts/";
        $target_file = $target_dir . $file_name;

        // Move the uploaded file to the desired location
        if (move_uploaded_file($file_tmp, $target_file)) {
            $contract_copy = $target_file;
        } else {
            die("File upload failed.");
        }
    }

    // Update the contract in the database
    $update_sql = "
        UPDATE contracts 
        SET coworker_id = ?, contract_details = ?, start_date = ?, end_date = ?, contract_copy = ?, TeamID = ?
        WHERE contract_id = ?
    ";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('issssii', $coworker_id, $contract_details, $start_date, $end_date, $contract_copy, $team_id, $contract_id);

    if ($stmt->execute()) {
        header('Location: view_contracts.php');
        exit();
    } else {
        echo "Error updating contract: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Contract</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Edit Contract</h2>
        <form method="POST" enctype="multipart/form-data">

            <!-- Dropdown for Coworker Name -->
            <div class="mb-3">
    <label for="coworker_name" class="form-label">Coworker Name</label>
    <input type="text" class="form-control" id="coworker_name" name="coworker_name" value="<?php echo htmlspecialchars($contract['coworker_name']); ?>" required>
</div>

            <div class="mb-3">
                <label for="contract_details" class="form-label">Contract Details</label>
                <textarea class="form-control" id="contract_details" name="contract_details" required><?php echo $contract['contract_details']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $contract['start_date']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $contract['end_date']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="contract_copy" class="form-label">Contract Copy (PDF or Image)</label>
                <input type="file" class="form-control" id="contract_copy" name="contract_copy" accept=".pdf,.jpg,.jpeg,.png">
                <?php if (!empty($contract['contract_copy'])) { ?>
                    <p>Current file: <a href="<?php echo $contract['contract_copy']; ?>" target="_blank">View</a></p>
                <?php } ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Contract</button>
        </form>
    </div>
</body>
</html>
