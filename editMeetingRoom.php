<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if the user is not logged in
    header('Location: index.php');
    exit();
}
$meetingRoomID = "";
$name = "";
$capacity= "";
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
    if (!isset($_GET["meetingRoomID"])) {
        header("location:/cowork/meetingRoom.php");
        exit;
    }
    $meetingRoomID= $conn->real_escape_string($_GET["meetingRoomID"]);
    $result = "SELECT * FROM meetingroom WHERE meetingRoomID=$meetingRoomID";
    $sql = $conn->query($result);
    $row = $sql->fetch_assoc();
    if (!$row) {
        header("location:/cowork/meetingRoom.php");
        exit;
    }
    $name  = $row["name"];
    $capacity = $row["capacity"];
    
    
} else {
    $meetingRoomID = $conn->real_escape_string($_POST["meetingRoomID"]);
    $name = $conn->real_escape_string($_POST["name"]);
    $capacity= $conn->real_escape_string($_POST["capacity"]);
    
   

    if (empty($name ) || empty($capacity)) {
        $errormessage = "All fields are required";
    } else {
        $sql = "UPDATE meetingroom SET name  = '$name', capacity= '$capacity'where meetingRoomID='$meetingRoomID'";
        $result = $conn->query($sql);
        if (!$result) {
            $errormessage = "Invalid query: " . $conn->error;
        } else {
            $successmessage = "meeting room data updated successfully";
            header("location:/cowork/meetingRoom.php");
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
    <title>Edit MeetingRoom Data</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('bgc.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
        
            margin-top:-1rem ;
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
  
        <h2>Edit MeetingRoom  Data</h2>
        <?php if (!empty($errormessage)): ?>
            <p class="message"><?= htmlspecialchars($errormessage) ?></p>
        <?php endif; ?>
        <?php if (!empty($successmessage)): ?>
            <p class="message success"><?= htmlspecialchars($successmessage) ?></p>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="meetingRoomID" value="<?= htmlspecialchars($meetingRoomID) ?>">
            <label for="name">name :</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            <label for="capacity">capacity:</label>
            <input type="text" id="capacity" name="capacity" value="<?= htmlspecialchars($capacity) ?>" required>
            
            
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
