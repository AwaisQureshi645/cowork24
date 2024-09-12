<?php
session_start();

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Database connection details
    $host = 'localhost';
    $dbname = 'coworker';
    $username_db = 'root';
    $password_db = '';

    // Create a connection
    $conn = new mysqli($host, $username_db, $password_db, $dbname);

    // Check if the connection is successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the user is logged in and has one of the allowed roles
    if (!isset($_SESSION['role']) || 
        ($_SESSION['role'] !== 'head' && 
         $_SESSION['role'] !== 'financehead' && 
         $_SESSION['role'] !== 'floorHost' && 
         $_SESSION['role'] !== 'manager')) {
        // If the user does not have the allowed role, redirect to access denied page
        header('Location: access_denied.php');
        exit();
    }

    // If the user has the right role, proceed with the deletion
    $sql = "DELETE FROM coworkusers WHERE id='$id'";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        // If successful, redirect to employeeData page
        header("Location: /cowork/employeeData.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    
    // Close the database connection
    $conn->close();
} else {
    // If no 'id' is provided, redirect to employeeData page
    header("Location: /cowork/employeeData.php");
    exit();
}
?>
