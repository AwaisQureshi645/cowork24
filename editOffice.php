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

if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'head' && 
     $_SESSION['role'] !== 'financehead' && 
     $_SESSION['role'] !== 'floorHost' && 
     $_SESSION['role'] !== 'manager')) {
    header('Location: access_denied.php');
    exit();
}

// Fetch branches for the dropdown
$branch_sql = "SELECT branch_id, branch_name FROM branches";
$branch_result = $conn->query($branch_sql);
if (!$branch_result) {
    die("Query failed: " . $conn->error);
}

$branches = [];
while ($row = $branch_result->fetch_assoc()) {
    $branches[] = $row;
}

$OfficeID = "";
$RoomNo = "";
$capacity = "";
$Price = "";
$branch_id = null;
$status = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["OfficeID"])) {
        header("location:/cowork/office.php");
        exit;
    }
    
    $OfficeID = $conn->real_escape_string($_GET["OfficeID"]);
    $result = "SELECT * FROM office WHERE OfficeID='$OfficeID'";
    $sql = $conn->query($result);
    $row = $sql->fetch_assoc();
    
    if (!$row) {
        header("location:/cowork/office.php");
        exit;
    }
    
    // Fetch data from the selected row
    $RoomNo = $row["RoomNo"];
    $capacity = $row["capacity"];
    $Price = $row["Price"];
    $branch_id = $row["branch_id"];
    $status = $row["status"];
} else {
    // Update logic for POST request
    $OfficeID = $conn->real_escape_string($_POST["OfficeID"]);
    $RoomNo = $conn->real_escape_string($_POST["RoomNo"]);
    $capacity = $conn->real_escape_string($_POST["capacity"]);
    $Price = $conn->real_escape_string($_POST["Price"]);
    $branch_id = !empty($_POST['branch_id']) ? $_POST['branch_id'] : null; // Allow NULL for branch
    $status = $conn->real_escape_string($_POST["status"]);

    if (empty($RoomNo) || empty($capacity) || empty($Price)) {
        $errormessage = "All fields are required";
    } else {
        $sql = "UPDATE office 
                SET RoomNo = '$RoomNo', capacity = '$capacity', Price = '$Price', branch_id = '$branch_id', status = '$status' 
                WHERE OfficeID = '$OfficeID'";
        $result = $conn->query($sql);
        if (!$result) {
            $errormessage = "Invalid query: " . $conn->error;
        } else {
            $successmessage = "Office data updated successfully";
            header("location:/cowork/office.php");
            exit;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="cowork-logo.PNG">
    <link rel="stylesheet" href="style.css">

    <title>Edit Office Data</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('bgc.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            width: 380px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            border: 2px solid #ddd;
            max-height: 90vh;
            overflow-y: auto; 
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"], input[type="file"], input[type="number"], select {
            padding: 12px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        button {
            padding: 12px;
            color: #fff;
            background: #007bff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        button:hover {
            background: #0056b3;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            color: red;
        }
        .message.success {
            color: green;
        }
        .logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .logout-button:hover {
            background: #c82333;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Office Data</h2>
    
        <?php if (!empty($errormessage)): ?>
            <p class="message"><?= htmlspecialchars($errormessage) ?></p>
        <?php endif; ?>
        <?php if (!empty($successmessage)): ?>
            <p class="message success"><?= htmlspecialchars($successmessage) ?></p>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="OfficeID" value="<?= htmlspecialchars($OfficeID) ?>">

            <label for="RoomNo">Room No:</label>
            <input type="text" id="RoomNo" name="RoomNo" value="<?= htmlspecialchars($RoomNo) ?>" required>

            <label for="capacity">Capacity:</label>
            <input type="number" id="capacity" name="capacity" value="<?= htmlspecialchars($capacity) ?>" required>
           
            <label for="Price">Price:</label>
            <input type="number" id="Price" name="Price" value="<?= htmlspecialchars($Price) ?>" required>

            <label for="branch_id">Branch:</label>
            <select id="branch_id" name="branch_id">
                <option value="">Select a branch (optional)</option>
                <?php
                foreach ($branches as $branch) {
                    $selected = ($branch['branch_id'] == $branch_id) ? "selected" : "";
                    echo "<option value=\"{$branch['branch_id']}\" $selected>{$branch['branch_name']}</option>";
                }
                ?>
            </select>

            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="Available" <?= ($status == 'Available') ? "selected" : "" ?>>Available</option>
                <option value="Not Available" <?= ($status == 'Not Available') ? "selected" : "" ?>>Not Available</option>
            </select>
   
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
