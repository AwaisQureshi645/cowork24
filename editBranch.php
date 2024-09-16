<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if the user is not logged in
    header('Location: index.php');
    exit();
}
$branch_id = "";
$branch_name = "";
$location= "";
$ContactDetails= "";
$manager = "";


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

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["branch_id"])) {
        header("location:/cowork/branch.php");
        exit;
    }
    $branch_id= $conn->real_escape_string($_GET["branch_id"]);
    $result = "SELECT * FROM branches WHERE branch_id=$branch_id";
    $sql = $conn->query($result);
    $row = $sql->fetch_assoc();
    if (!$row) {
        header("location:/cowork/branch.php");
        exit;
    }
    $branch_name  = $row["branch_name"];
    $location = $row["location"];
    $ContactDetails = $row["ContactDetails"];
    $manager = $row["manager"];
    
} else {
    $branch_id = $conn->real_escape_string($_POST["branch_id"]);
    $branch_name = $conn->real_escape_string($_POST["branch_name"]);
    $location = $conn->real_escape_string($_POST["location"]);
    $ContactDetails = $conn->real_escape_string($_POST["ContactDetails"]);
    $manager = $conn->real_escape_string($_POST["manager"]);
   

    if (empty($branch_name ) || empty($location) || empty($ContactDetails) || empty($manager)) {
        $errormessage = "All fields are required";
    } else {
        $sql = "UPDATE branches SET branch_name  = '$branch_name', location= '$location', ContactDetails = '$ContactDetails', manager = '$manager' WHERE branch_id =$branch_id";
        $result = $conn->query($sql);
        if (!$result) {
            $errormessage = "Invalid query: " . $conn->error;
        } else {
            $successmessage = "branch data updated successfully";
            header("location:/cowork/branch.php");
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

    <title>Edit Branch Data</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eaeaea;
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
    </style>
</head>
<body>
    <div class="container">
    <a href="logout.php" class="logout-button">Logout</a>
        <h2>Edit Branch Data</h2>
        <?php if (!empty($errormessage)): ?>
            <p class="message"><?= htmlspecialchars($errormessage) ?></p>
        <?php endif; ?>
        <?php if (!empty($successmessage)): ?>
            <p class="message success"><?= htmlspecialchars($successmessage) ?></p>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="branch_id" value="<?= htmlspecialchars($branch_id) ?>">
            <label for="branch_name">branch_name :</label>
            <input type="text" id="branch_name" name="branch_name" value="<?= htmlspecialchars($branch_name) ?>" required>
            <label for="location">location:</label>
            <input type="text" id="location" name="location" value="<?= htmlspecialchars($location) ?>" required>
            <label for="ContactDetails">ContactDetails:</label>
            <input type="text" id="ContactDetails" name="ContactDetails" value="<?= htmlspecialchars($ContactDetails) ?>" required>
            <label for="manager">manager:</label>
            <input type="text" id="manager" name="manager" value="<?= htmlspecialchars($manager) ?>" required>
            
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
