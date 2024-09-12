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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update contract details
    $coworker_name = $_POST['coworker_name'];
    $contract_details = $_POST['contract_details'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $update_sql = "UPDATE contracts SET 
        coworker_id = (SELECT coworker_id FROM coworkers WHERE name = ?),
        contract_details = ?, 
        start_date = ?, 
        end_date = ? 
        WHERE contract_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $coworker_name, $contract_details, $start_date, $end_date, $contract_id);

    if ($stmt->execute()) {
        header("Location: view_contracts.php");
        exit();
    } else {
        echo "Error updating contract: " . $conn->error;
    }
}

// Fetch contract data
$sql = "SELECT * FROM contracts WHERE contract_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $contract_id);
$stmt->execute();
$result = $stmt->get_result();
$contract = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Contract</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:linear-gradient(to right, #007bff, #00d4ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            width: 400px;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            border: 1px solid #ddd;
            max-height: 90vh;
            overflow-y: auto;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            color: #555;
            font-weight: 600;
        }
        input[type="text"],
        textarea,
        input[type="date"] {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        input[type="submit"] {
            padding: 12px;
            color: #fff;
            background: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        input[type="submit"]:hover {
            background: #0056b3;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
        }
        .message.error {
            color: #d9534f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Contract</h1>
        <?php if ($contract): ?>
            <form method="POST">
                
                <label for="contract_details">Contract Details:</label>
                <textarea id="contract_details" name="contract_details" required><?php echo htmlspecialchars($contract['contract_details']); ?></textarea>
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($contract['start_date']); ?>" required>
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($contract['end_date']); ?>" required>
                <input type="submit" value="Update">
            </form>
        <?php else: ?>
            <p class="message error">Contract not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
