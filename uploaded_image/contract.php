<?php
session_start();
if ($_SESSION['role'] !== 'head') {
    header('Location: access_denied.php');
    exit();
}

if (!isset($_GET['coworker_id'])) {
    header('Location: add_coworker.php');
    exit();
}

$coworker_id = $_GET['coworker_id'];

// Database connection
$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if coworker_id exists in the database
$result = $conn->query("SELECT COUNT(*) as count FROM coworkers WHERE coworker_id = '$coworker_id'");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $coworker_id = NULL; // Set to NULL if not found
}

// If coworker_id is NULL, get team_id from GET parameters
if (!$coworker_id) {
    $team_id = $_GET['team_id'] ?? NULL; // Assuming team_id is also passed via GET
    if ($team_id) {
        $coworker_id = $team_id; // Use team_id if coworker_id is not valid
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contract_details = $_POST['contract_details'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Handle file upload
    $upload_dir = 'uploaded_files/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if not exists
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $image_size = $_FILES['image']['size'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        // Validate file size
        if ($image_size > (50 * 1024 * 1024)) {
            echo "<script>alert('File size is greater than 50MB');</script>";
            exit();
        }

        // Validate file type (allow images and PDFs)
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (!in_array($image_ext, $allowed_ext)) {
            echo "<script>alert('Invalid File Extension');</script>";
            exit();
        }

        $contract_copy = $upload_dir . $coworker_id . "." . $image_ext;
        if (move_uploaded_file($image, $contract_copy)) {
            // Insert contract into database
            $stmt = $conn->prepare("INSERT INTO contracts (coworker_id, contract_details, start_date, end_date, contract_copy) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $coworker_id, $contract_details, $start_date, $end_date, $contract_copy);

            if ($stmt->execute()) {
                echo "<script>alert('Upload Successful');</script>";
                $stmt->close();
                $conn->close();
                header('Location: view_contracts.php');
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "<script>alert('Failed to move uploaded file.');</script>";
        }
    } else {
        echo "<script>alert('No file uploaded or upload error.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Contract</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #007bff, #00d4ff);
            padding: 20px;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        h2 {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add Contract</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <!-- <label for="contract_details">Contract Details:</label>
            <textarea id="contract_details" name="contract_details" rows="5" required></textarea> -->

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <label for="image">Upload Contract File:</label>
            <input type="file" id="image" name="image" class="form-control-file" >

            <button type="submit">Submit Contract</button>
        </form>
    </div>
</body>
</html>
