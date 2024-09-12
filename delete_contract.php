<?php
session_start();
if ($_SESSION['role'] !== 'head') {
    header('Location: access_denied.php');
    exit();
}

$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$contract_id = $_GET['contract_id'] ?? '';

if ($contract_id) {
    // Delete contract record
    $sql = "DELETE FROM contracts WHERE contract_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $contract_id);

    if ($stmt->execute()) {
        header("Location: view_contracts.php");
        exit();
    } else {
        echo "Error deleting contract: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
