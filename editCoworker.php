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

// Fetch offices and services for the form dropdowns
$offices = $conn->query("
    SELECT office.OfficeID, branches.branch_name 
    FROM office 
    JOIN branches ON office.branch_id = branches.branch_id
");

$services = $conn->query("SELECT service_id, service_name FROM add_on_services");

$coworker_id = null;
$existing_data = null;

if (isset($_GET['id'])) {
    // Fetch existing coworker data for editing
    $coworker_id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM coworkers WHERE coworker_id = $coworker_id");

    if ($result->num_rows > 0) {
        $existing_data = $result->fetch_assoc();
    } else {
        echo "No coworker found with this ID.";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact_info = $_POST['contact_info'];
    $email = $_POST['email'];
    $office_id = $_POST['OfficeID'];
    $service_id = $_POST['service_id'];

    $conn->begin_transaction();

    try {
        // Check if it's an update (edit) or new record insertion
        if (isset($coworker_id)) {
            // Update the coworker record
            $stmt = $conn->prepare("
                UPDATE coworkers 
                SET name = ?, contact_info = ?, email = ?, office_id = ?, service_id = ? 
                WHERE coworker_id = ?
            ");
            $stmt->bind_param("sssiii", $name, $contact_info, $email, $office_id, $service_id, $coworker_id);
        } else {
            // Insert a new coworker (if no ID is passed, it's a new entry)
            $stmt = $conn->prepare("
                INSERT INTO coworkers (coworker_type, name, contact_info, email, office_id, service_id) 
                VALUES ('individual', ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssii", $name, $contact_info, $email, $office_id, $service_id);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error saving coworker data: " . $stmt->error);
        }

        // Commit transaction
        $conn->commit();

        // Redirect after saving
        header("Location: view_coworker.php");
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
    <title>Edit Coworker</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eaeaea;
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
        <h2><?php echo isset($coworker_id) ? "Edit" : "Add"; ?> Coworker</h2>

        <form action="" method="post" class="form-container">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo isset($existing_data) ? $existing_data['name'] : ''; ?>" required>

            <label for="contact_info">Contact Info:</label>
            <input type="text" id="contact_info" name="contact_info" value="<?php echo isset($existing_data) ? $existing_data['contact_info'] : ''; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($existing_data) ? $existing_data['email'] : ''; ?>" required>

            <label for="OfficeID">Select office:</label>
            <select id="OfficeID" name="OfficeID" required>
                <?php while ($row = $offices->fetch_assoc()) { ?>
                    <option value="<?= $row['OfficeID'] ?>" <?= isset($existing_data) && $existing_data['office_id'] == $row['OfficeID'] ? 'selected' : ''; ?>>
                        <?= $row['OfficeID'] . ' - ' . $row['branch_name'] ?>
                    </option>
                <?php } ?>
            </select>

            <label for="service_id">Select Service:</label>
            <select id="service_id" name="service_id" required>
                <?php while ($row = $services->fetch_assoc()) { ?>
                    <option value="<?= $row['service_id'] ?>" <?= isset($existing_data) && $existing_data['service_id'] == $row['service_id'] ? 'selected' : ''; ?>>
                        <?= $row['service_name'] ?>
                    </option>
                <?php } ?>
            </select>

            <button type="submit"><?php echo isset($coworker_id) ? "Update" : "Add"; ?> Coworker</button>
        </form>
    </div>
</body>
</html>
