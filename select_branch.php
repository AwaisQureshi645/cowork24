<?php
session_start();

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
     $_SESSION['role'] !== 'floorHost')) {
    header('Location: access_denied.php');
    exit();
}


$query = "SELECT branch_id, branch_name FROM branches";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="cowork-logo.PNG">
    <title>Select Branch</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
        }

        select, button {
            padding: 10px;
            font-size: 16px;
            margin: 10px 0;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #45a049;
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
   
        <h1>Select a Branch</h1>
        <form method="get" action="inventory.php">
            <select name="branch_id" required>
                <option value="">-- Select Branch --</option>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['branch_id']); ?>">
                        <?php echo htmlspecialchars($row['branch_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select><br><br>
            <button type="submit">View Inventory</button>
        </form>
    </div>
</body>
</html>
