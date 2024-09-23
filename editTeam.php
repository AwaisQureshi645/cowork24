<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
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

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['head', 'financehead', 'floorHost', 'manager'])) {
    header('Location: access_denied.php');
    exit();
}

$TeamID = "";
$TeamName = "";
$JoiningDate = "";
$EndingDate = "";
$Discount = "";
$SecurityAmount = "";
$PointofContact = "";
$Reference = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET['TeamID'])) {
        header("location:/cowork/team.php");
        exit;
    }
    $TeamID = $conn->real_escape_string($_GET['TeamID']);
    $result = $conn->query("SELECT * FROM team WHERE TeamID=$TeamID");
    $row = $result->fetch_assoc();
    if (!$row) {
        header("location:/cowork/team.php");
        exit;
    }
    $TeamName = $row['TeamName'];
    $JoiningDate = $row['JoiningDate'];
    $EndingDate = $row['EndingDate'];
    // $Discount = $row['Discount'];
    $SecurityAmount = $row['SecurityAmount'];
    $PointofContact = $row['PointofContact'];
    $Reference = $row['Reference'];
} else {
    if (!isset($_POST['TeamID'])) {
        $errormessage = "TeamID is missing";
    } else {
        $TeamID = $conn->real_escape_string($_POST['TeamID']);
        $TeamName = $conn->real_escape_string($_POST['TeamName']);
        $JoiningDate = $conn->real_escape_string($_POST['JoiningDate']);
        $EndingDate = $conn->real_escape_string($_POST['EndingDate']);
        // $Discount = $conn->real_escape_string($_POST['Discount']);
        $SecurityAmount = $conn->real_escape_string($_POST['SecurityAmount']);
        $PointofContact = $conn->real_escape_string($_POST['PointofContact']);
        $Reference = $conn->real_escape_string($_POST['Reference']);

        if (empty($TeamName) || empty($JoiningDate) || empty($EndingDate) || empty($Discount) || empty($SecurityAmount) || empty($PointofContact) || empty($Reference)) {
            $errormessage = "All fields are required";
        } else {
            $sql = "UPDATE team SET TeamName = '$TeamName', JoiningDate = '$JoiningDate', EndingDate = '$EndingDate', Discount = '$Discount', SecurityAmount = '$SecurityAmount', PointofContact = '$PointofContact', Reference = '$Reference' WHERE TeamID = $TeamID";
            $result = $conn->query($sql);
            if (!$result) {
                $errormessage = "Invalid query: " . $conn->error;
            } else {
                $successmessage = "Team data updated successfully";
                header("location:/cowork/team.php");
                exit;
            }
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

    <title>Edit Team Data</title>
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
        input[type="text"] {
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
        .form-group{
            display: flex;
            flex-direction: column;
        }
        .form-group input{
            padding: 9px;
        }
    </style>
</head>
<body>
    <div class="container">
       
        <h2>Edit Team Data</h2>
        <?php if (!empty($errormessage)): ?>
            <p class="message"><?= htmlspecialchars($errormessage) ?></p>
        <?php endif; ?>
        <?php if (!empty($successmessage)): ?>
            <p class="message success"><?= htmlspecialchars($successmessage) ?></p>
        <?php endif; ?>
        <form action="" method="post"  enctype="multipart/form-data">
            <input type="hidden" name="TeamID" value="<?= htmlspecialchars($TeamID) ?>">
            <label for="TeamName">TeamName:</label>
            <input type="text" id="TeamName" name="TeamName" value="<?= htmlspecialchars($TeamName) ?>" required>
     
           
         
            <div class="form-group">
                    <label for="joining_date">Joining Date:</label>
                    <input type="date" id="joining_date" name="joining_date" class="form-control" required onclick="this.showPicker();">
                </div>

                <div class="form-group">
                    <label for="ending_date">Ending Date:</label>
                    <input type="date" id="ending_date" name="ending_date" class="form-control" required onclick="this.showPicker();">
                </div>
             <label for="SecurityAmount">SecurityAmount:</label>
            <input type="text" id="SecurityAmount" name="SecurityAmount" value="<?= htmlspecialchars($SecurityAmount) ?>" required>
            <label for="PointofContact">PointofContact:</label>
            <input type="text" id="PointofContact" name="PointofContact" value="<?= htmlspecialchars($PointofContact) ?>" required>
            <label for="Reference">Reference:</label>
            <input type="text" id="Reference" name="Reference" value="<?= htmlspecialchars($Reference) ?>" required>
             
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
