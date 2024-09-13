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

// Check user roles
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

    // Debugging - Log values to track the flow
    error_log("Item ID: " . $item_id);
    error_log("Branch ID: " . $branch_id);
    error_log("Item Name: " . $item_name);
    error_log("Quantity: " . $quantity);
    error_log("Item Condition: " . $item_condition);

    // Check if the form inputs are valid
    if ($item_id <= 0 || $branch_id <= 0 || empty($item_name) || empty($item_condition)) {
        error_log("Invalid input data. Redirecting back to inventory.");
        die("Invalid input data.");
    }

    // Update query
    $query = "UPDATE items SET item_name = ?, quantity = ?, item_condition = ? WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Failed to prepare the SQL statement. Error: " . $conn->error);
        die("Failed to prepare the SQL statement. Error: " . $conn->error);
    }

    // Bind and execute
    $stmt->bind_param("sisi", $item_name, $quantity, $item_condition, $item_id);

    if ($stmt->execute()) {
        // Debugging - Confirm success
        error_log("Item update successful. Redirecting back to inventory page.");
        
        // Redirect back to the inventory page after updating
        header("Location: inventory.php?branch_id=" . $branch_id . "&msg=Item+updated+successfully");
        exit();
    } else {
        error_log("Error updating record: " . $stmt->error);
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
