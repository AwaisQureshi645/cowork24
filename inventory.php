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

// Role check
if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'head' && 
     $_SESSION['role'] !== 'financehead' && 
     $_SESSION['role'] !== 'floorHost' && 
     $_SESSION['role'] !== 'manager')) {
    header('Location: access_denied.php');
    exit();
}

// Get branch ID
$branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : 0;

// Debugging - Add JavaScript alert for tracking the branch ID
//echo "<script>alert('Branch ID: " . $branch_id . "');</script>";

$branch_name = "Unknown Branch";

if ($branch_id > 0) {
    $query = "
        SELECT b.branch_name, i.item_id, i.item_name, i.quantity, i.item_condition 
        FROM items i
        JOIN branches b ON i.branch_id = b.branch_id
        WHERE b.branch_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
            $branch_name = htmlspecialchars($row['branch_name']);
        }
    }

    $stmt->close();
} else {
    error_log("Invalid branch ID: " . $branch_id);
    echo "<script>alert('Invalid branch ID: " . $branch_id . "');</script>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="cowork-logo.PNG">
    <title>Branch Inventory</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .edit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }

        .delete-btn {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }

        .edit-btn:hover {
            background-color: #45a049;
        }

        .delete-btn:hover {
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
    <a href="logout.php" class="logout-button">Logout</a>
        <h1>Inventory for <?php echo htmlspecialchars($branch_name); ?></h1>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Condition</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($item['item_condition']); ?></td>
                            <td>
                                <a class="edit-btn" href="edit_item.php?item_id=<?php echo $item['item_id']; ?>&branch_id=<?php echo $branch_id; ?>">Edit</a>
                                <a class="delete-btn" href="delete_item.php?item_id=<?php echo $item['item_id']; ?>&branch_id=<?php echo $branch_id; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="welcome.php">Back to dashboard</a>
    </div>
</body>
</html>
