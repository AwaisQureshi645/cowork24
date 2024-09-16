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

// Role validation
if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'head' && 
     $_SESSION['role'] !== 'financehead' && 
     $_SESSION['role'] !== 'floorHost' && 
     $_SESSION['role'] !== 'manager')) {
    header('Location: access_denied.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $teamName = $_POST['team_name'];
    $joiningDate = $_POST['joining_date'];
    $endingDate = $_POST['ending_date'];
    $discount = $_POST['discount'];
    $securityAmount = $_POST['security_amount'];
    $pointOfContact = $_POST['point_of_contact'];
    $reference = $_POST['reference'];

    $stmt = $conn->prepare("INSERT INTO team (TeamName, JoiningDate, EndingDate, Discount, SecurityAmount, PointofContact, Reference) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiiss", $teamName, $joiningDate, $endingDate, $discount, $securityAmount, $pointOfContact, $reference);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;
        header("Location: contract.php?coworker_id=$last_id");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Team</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="cowork-logo.PNG">
    <link rel="stylesheet" href="style.css">

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
            width: 90%;
            max-width: 900px;
            padding: 30px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: block;
            color: #333;
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
        }

        button {
            padding: 12px;
            color: #fff;
            background: #007bff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
            transition: background 0.3s ease;
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
        <a href="logout.php" class="logout-button">Logout</a>
        <h2>Add New Team</h2>
        
        <form action="" method="post">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="team_name">Team Name:</label>
                        <input type="text" id="team_name" name="team_name" class="form-control" placeholder="Enter Team Name" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="joining_date">Joining Date:</label>
                        <input type="date" id="joining_date" name="joining_date" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ending_date">Ending Date:</label>
                        <input type="date" id="ending_date" name="ending_date" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="discount">Discount (%):</label>
                        <input type="number" id="discount" name="discount" class="form-control" placeholder="Enter Discount" required>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="security_amount">Security Amount:</label>
                        <input type="number" id="security_amount" name="security_amount" class="form-control" placeholder="Enter Security Amount" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="point_of_contact">Point of Contact:</label>
                        <input type="text" id="point_of_contact" name="point_of_contact" class="form-control" placeholder="Enter Point of Contact" required>
                    </div>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="reference">Reference:</label>
                <input type="text" id="reference" name="reference" class="form-control" placeholder="Enter Reference" required>
            </div>
            
            <button type="submit" name="submit">Add Team</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
