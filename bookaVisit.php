<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'coworker'; // Change to your database name
$username_db = 'root'; // Change if different
$password_db = ''; // Change if different

// Create connection
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

// Fetch branches
$branch_query = "SELECT branch_id, branch_name FROM branches";
$branch_result = $conn->query($branch_query);
if (!$branch_result) {
    die("Query failed: " . $conn->error);
}

$branches = [];
while ($row = $branch_result->fetch_assoc()) {
    $branches[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $purpose = $_POST['purpose'] ?? '';
    $name = $_POST['name'] ?? '';
    $businessDetails = $_POST['businessDetails'] ?? '';
    $email = $_POST['email'] ?? '';
    $phonenumber = $_POST['phonenumber'] ?? '';
    $branch_id = $_POST['branch_id'] ?? '';
    $assignedTo = $_POST['assignedTo'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';

    if ($purpose && $name  && $phonenumber && $branch_id) {
        $stmt = $conn->prepare("INSERT INTO visitorsinfo (purpose, name, email,businessDetails, phonenumber, assignedTo, branch_id, Comments, appointment_date) VALUES (?, ?, ?, ?, ?, ?, ?,?,?)");
        if ($stmt) {
            $stmt->bind_param("sssssssss", $purpose, $name,$email,$businessDetails,   $phonenumber, $assignedTo, $branch_id, $comment, $appointment_date);

            if ($stmt->execute()) {
                echo "New record created successfully";
                header("Location: visits.php");
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error preparing the SQL statement: " . $conn->error;
        }
    } else {
        echo "All required fields must be filled.";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="cowork-logo.PNG">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #007bff, #00d4ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            width: 700px;
            max-width: 600px;
            background-color: white;
            border: 1px solid #ddd;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 30px;
            overflow-y: auto;
            max-height: 90vh;
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
  
        <h2>Add New Record of Visitor</h2>
        <form action="bookaVisit.php" method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="mb-3">
                <label for="phonenumber" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phonenumber" name="phonenumber" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="businessDetails" class="form-label">BusinessDetails</label>
                <input type="text" class="form-control" id="businessDetails" name="businessDetails" required>
            </div>
            <div class="mb-3">
                <label for="branch_id" class="form-label">Branch</label>
                <select class="form-control" id="branch_id" name="branch_id" required>
                    <option value="">Select a branch</option>
                    <?php
                    foreach ($branches as $branch) {
                        echo "<option value='{$branch['branch_id']}'>{$branch['branch_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="assignedTo" class="form-label">Assigned To</label>
                <textarea class="form-control" id="assignedTo" name="assignedTo" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="comment" class="form-label">Comment</label>
                <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="purpose" class="form-label">Purpose</label>
                <input type="text" class="form-control" id="purpose" name="purpose" required>
            </div>
            <div class="mb-3">
                <label for="appointment_date" class="form-label">Appointment Date:</label>
                <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</body>
</html>
