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
// Fetch offices and services
$offices = $conn->query("
    SELECT office.OfficeID, branches.branch_name 
    FROM office 
    JOIN branches ON office.branch_id = branches.branch_id
");

$services = $conn->query("SELECT service_id, service_name FROM add_on_services");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact_info = $_POST['contact_info'];
    $email = $_POST['email'];
    $office_id = $_POST['OfficeID'];
    $service_id = $_POST['service_id'];

    $conn->begin_transaction();

    try {
        // Prepare contract details
        $contract_details = "Contract for $name";
        
        // Check if contract already exists
        $stmt = $conn->prepare("SELECT contract_id FROM contracts WHERE contract_details = ?");
        $stmt->bind_param("s", $contract_details);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Contract exists, fetch contract ID
            $stmt->bind_result($contract_id);
            $stmt->fetch();
        } else {
            // No contract exists, create new contract
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO contracts (contract_details) VALUES (?)");
            $stmt->bind_param("s", $contract_details);
            if (!$stmt->execute()) {
                throw new Exception("Error creating contract: " . $stmt->error);
            }
            $contract_id = $stmt->insert_id;
        }
        $stmt->close();

        // Insert the coworker record with the contract ID
        $stmt = $conn->prepare("INSERT INTO coworkers (coworker_type, name, contact_info, email, office_id, service_id, contract_id)
                                VALUES ('individual', ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiii", $name, $contact_info, $email, $office_id, $service_id, $contract_id);
        if (!$stmt->execute()) {
            throw new Exception("Error creating coworker: " . $stmt->error);
        }
        $last_id = $stmt->insert_id;
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Redirect to contract page
        header("Location: contract.php?coworker_id=$last_id");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log($e->getMessage()); // Log error for debugging
        echo "An error occurred. Please try again later.";
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Individual Coworker</title>
    <style>
       body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #007bff, #00d4ff);
            padding: 20px;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        h2 {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
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
    <div class="form-container">
        <h2>Add Individual Coworker</h2>
        <a href="logout.php" class="logout-button">Logout</a>
        <form action="" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="contact_info">Contact Info:</label>
            <input type="text" id="contact_info" name="contact_info" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="OfficeID">Select office:</label>
            <select id="OfficeID" name="OfficeID" required>
                <?php while ($row = $offices->fetch_assoc()) { ?>
                    <option value="<?= $row['OfficeID'] ?>">
                        <?= $row['OfficeID'] . ' - ' . $row['branch_name'] ?>
                    </option>
                <?php } ?>
            </select>

            <label for="service_id">Select Service:</label>
            <select id="service_id" name="service_id" required>
                <?php while ($row = $services->fetch_assoc()) { ?>
                    <option value="<?= $row['service_id'] ?>"><?= $row['service_name'] ?></option>
                <?php } ?>
            </select>

            <button type="submit">Proceed to Contract</button>
        </form>
    </div>
</body>
</html>
