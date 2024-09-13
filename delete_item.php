<?php
session_start();

// Database connection
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


// Validate that both 'item_id' and 'branch_id' are provided
if (isset($_GET['item_id']) && isset($_GET['branch_id'])) {
    $itemId = intval($_GET['item_id']);
    $branchId = intval($_GET['branch_id']);

    // Prepare and execute the delete query
    $sql = "DELETE FROM items WHERE item_id = ? AND branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $itemId, $branchId);
    
    if ($stmt->execute()) {
        // Redirect back to the same page after successful deletion
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    
    $stmt->close();
} else {
    echo "Invalid request.";
}

// Close the database connection
$conn->close();
?>
