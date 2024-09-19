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
     $_SESSION['role'] !== 'floorHost' && 
     $_SESSION['role'] !== 'manager')) {
    header('Location: access_denied.php');
    exit();
}

if (isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);
    $query = "SELECT * FROM items WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if (!$item) {
        die("Item not found.");
    }
} else {
    die("Item ID not provided.");
}

// Retrieve the branch_id from GET parameters
$branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : 0;
if ($branch_id === 0) {
    die("Branch ID is invalid or not provided.");
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

    <title>Edit Item</title>
    <style>
           body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            background-color: white !important;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #007BFF;
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

    <h1>Edit Item</h1>
    
    <form action="update_item.php" method="post">
        <!-- Hidden inputs to carry item_id and branch_id -->
        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
        <input type="hidden" name="branch_id" value="<?php echo htmlspecialchars($branch_id); ?>">

        <label for="item_name">Item Name:</label>
        <input type="text" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
        <br>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" required>
        <br>

        <label for="item_condition">Condition:</label>
        <select id="item_condition" name="item_condition" required>
            <option value="good" <?php echo $item['item_condition'] === 'good' ? 'selected' : ''; ?>>Good</option>
            <option value="damaged" <?php echo $item['item_condition'] === 'damaged' ? 'selected' : ''; ?>>Damaged</option>
            <option value="need replacement" <?php echo $item['item_condition'] === 'need replacement' ? 'selected' : ''; ?>>Need Replacement</option>
        </select>
        <br>

        <button type="submit">Update Item</button>
    </form>
</body>
</html>
