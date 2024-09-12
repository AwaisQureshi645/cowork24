<?php
if (isset($_GET['branch_id'])) {
    $branch_id = $_GET['branch_id'];
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
    
    $sql = "DELETE FROM branches WHERE branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branch_id);

    if ($stmt->execute()) {
        header("Location: /cowork/branch.php");
        exit;
    } else {
        echo "Error deleting branch: " . $conn->error;
    }
}
?>
