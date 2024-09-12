<?php
$conn = new mysqli('localhost', 'root', '', 'coworker');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'head' && 
     $_SESSION['role'] !== 'financehead' && 
     $_SESSION['role'] !== 'floorHost' && 
     $_SESSION['role'] !== 'manager')) {
    header('Location: access_denied.php');
    exit();
}


if (isset($_GET['item_id']) && isset($_GET['branch_id'])) {
    $itemId = $_GET['item_id'];
    $branchId = $_GET['branch_id'];

    $sql = "DELETE FROM items WHERE item_id = '$itemId' AND branch_id = '$branchId'";

    if ($conn->query($sql) === TRUE) {
        header("Location: inventory.php?msg=Item+deleted+successfully");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
$conn->close();
?>
