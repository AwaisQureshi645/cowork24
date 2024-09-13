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
     $_SESSION['role'] !== 'manager' &&
     $_SESSION['role'] !== 'floorHost')) {
    header('Location: access_denied.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $branch_id = isset($_POST['branch_id']) ? intval($_POST['branch_id']) : 0;
    $item_name = isset($_POST['item_name']) ? trim($_POST['item_name']) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $item_condition = isset($_POST['item_condition']) ? trim($_POST['item_condition']) : '';

    // Debugging
    error_log("Item ID: " . $item_id);
    error_log("Branch ID: " . $branch_id);
    error_log("Item Name: " . $item_name);
    error_log("Quantity: " . $quantity);
    error_log("Item Condition: " . $item_condition);

    if ($item_id <= 0 || $branch_id <= 0 || empty($item_name) || empty($item_condition)) {
        die("Invalid input data. Item ID: $item_id, Branch ID: $branch_id, Item Name: $item_name, Quantity: $quantity, Condition: $item_condition");
    }

    $query = "UPDATE items SET item_name = ?, quantity = ?, item_condition = ? WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Failed to prepare the SQL statement. Error: " . $conn->error);
    }

    $stmt->bind_param("sisi", $item_name, $quantity, $item_condition, $item_id);

    if ($stmt->execute()) {
        echo "Update successful.";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();


header("Location: inventory.php?branch_id=" . $branch_id);

exit;

?>
