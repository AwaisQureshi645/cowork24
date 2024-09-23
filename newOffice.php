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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $RoomNo = $_POST['RoomNo'];
    $capacity = $_POST['capacity'];
    $Price = $_POST['Price'];
    $branch_id = !empty($_POST['branch_id']) ? $_POST['branch_id'] : null; // Allow NULL for branch
    $status = $_POST['status']; // Capture the status from the dropdown

    // Prepare and execute the statement
    $stmt = $conn->prepare("INSERT INTO office (RoomNo, capacity, Price, branch_id, status) 
                            VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("siiss", $RoomNo, $capacity, $Price, $branch_id, $status);

    if ($stmt->execute()) {
        echo "Record inserted successfully.";
        $stmt->close();
        $conn->close();
        header("Location: /cowork/office.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="cowork-logo.PNG">
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            background: linear-gradient(to top, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0) 50%), url(bgc.jpg);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            width: 500px;
            max-height: 90vh;
            padding: 20px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            border: 5px solid #ddd;
            border-style: double;
            overflow-y: auto;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            padding-top: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
            color: #555;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"],
        select {
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            width: 100%;
        }

        button {
            padding: 12px;
            color: #fff;
            background: #007bff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #0056b3;
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

        <h2>Add New Office</h2>
        <form action="" method="post">
            <label for="RoomNo">Room Number:</label>
            <input type="text" id="RoomNo" name="RoomNo" required>

            <label for="capacity">Capacity:</label>
            <input type="number" id="capacity" name="capacity" required>

            <label for="Price">Price:</label>
            <input type="number" id="Price" name="Price" required>

            <label for="branch_id">Select a branch (optional):</label>
            <select id="branch_id" name="branch_id">
                <option value="">Select a branch (optional)</option>
                <?php
                foreach ($branches as $branch) {
                    $selected = ($branch['branch_id'] == $row['branch_id']) ? 'selected' : ''; // Pre-select if editing an office
                    echo "<option value=\"{$branch['branch_id']}\" $selected>{$branch['branch_name']}</option>";
                }
                ?>
            </select>


            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="Available">Available</option>
                <option value="Not Available">Not Available</option>
            </select>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>

</html>