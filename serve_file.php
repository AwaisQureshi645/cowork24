<?php
if (!isset($_GET['contract_id']) || !isset($_GET['file_ext'])) {
    die('Invalid request');
}

$contract_id = intval($_GET['contract_id']);
$file_ext = strtolower($_GET['file_ext']);

$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

if (!in_array($file_ext, $allowed_extensions)) {
    die('Unsupported file type');
}

$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT contract_copy FROM contracts WHERE contract_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $contract_id);
$stmt->execute();
$stmt->bind_result($file_path);
$stmt->fetch();
$stmt->close();
$conn->close();

if ($file_path && file_exists($file_path)) {
    header('Content-Type: ' . ($file_ext === 'pdf' ? 'application/pdf' : 'image/' . $file_ext));
    readfile($file_path);
} else {
    die('File not found');
}
?>
