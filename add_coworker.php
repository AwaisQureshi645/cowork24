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

// Handle form submission for individual coworker
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
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
    <title>Add a Coworker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eaeaea;
            color: black;
            padding: 20px;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 800px;
            margin: 20px auto;
        }

        select,
        input,
        button {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            max-width: 400px;
        }

        .hidden {
            display: none;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        #selectForm {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        #coworker_type {
            width: 100% !important;
        }

        #indendividualForm {
            display: flex;
            flex-direction: column;
            justify-content: left;
            align-items: baseline;
            max-width: 60%;
            margin: 0 auto;
        }

        #indendividualForm button {

            background-color: #ff8802 !important;
            color: white;
            border: none;
            cursor: pointer;

        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Select Members Type</h2>
        <form id="selectForm">
            <label for="coworker_type">Are you adding an individual or a team?</label>
            <select id="coworker_type" name="coworker_type" required onchange="toggleForm()">
                <option value="">Choose an option</option>
                <option value="individual">Individual</option>
                <option value="team">Team</option>
            </select>
        </form>
    </div>

    <!-- Hidden form for adding individual coworker -->
    <div id="individualForm" class="form-container hidden">
        <h2>Add Individual Coworker</h2>
        <form id="indendividualForm" action="" method="post">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="contact_info">Contact Info:</label>
            <input type="text" id="contact_info" name="contact_info" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="service_id">Select Service:</label>
            <select id="service_id" name="service_id" required>
                <?php while ($row = $services->fetch_assoc()) { ?>
                    <option value="<?= $row['service_id'] ?>"><?= $row['service_name'] ?></option>
                <?php } ?>
            </select>

            <label for="seat_type">Seat Type:</label>
            <select id="seat_type" name="seat_type" onchange="toggleFields()" required>
                <option value="">Select Seat Type</option>
                <option value="dedicated_seat">Dedicated Seat</option>
                <option value="private_office">Private Office</option>
            </select>

            <label for="private_office_size">Private Office Size:</label>
            <select id="private_office_size" name="private_office_size" disabled>
                <option value="">Select Private Office Size</option>
                <option value="8_person">8 Person Office</option>
                <option value="16_person">16 Person Office</option>
                <option value="22_person">22 Person Office</option>
                <option value="40_person">40 Person Office</option>
            </select>

            <label for="no_of_seats">Number of Seats:</label>
            <input type="number" id="no_of_seats" name="no_of_seats" min="1" disabled>

            <button type="submit">Proceed to Contract</button>
        </form>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seatTypeSelect = document.getElementById('seat_type');
            const privateOfficeSizeSelect = document.getElementById('private_office_size');
            const noOfSeatsInput = document.getElementById('no_of_seats');

            toggleFields();

            function toggleFields() {
                if (seatTypeSelect.value === 'dedicated_seat') {
                    privateOfficeSizeSelect.disabled = true;
                    noOfSeatsInput.required = true;
                    noOfSeatsInput.disabled = false;
                } else if (seatTypeSelect.value === 'private_office') {
                    privateOfficeSizeSelect.disabled = false;
                    noOfSeatsInput.required = false;
                    noOfSeatsInput.disabled = true;
                } else {
                    privateOfficeSizeSelect.disabled = true;
                    noOfSeatsInput.required = false;
                    noOfSeatsInput.disabled = true;
                }
            }

            seatTypeSelect.addEventListener('change', toggleFields);
        });
    </script>
    <script>
        function toggleForm() {
            var coworkerType = document.getElementById("coworker_type").value;
            var individualForm = document.getElementById("individualForm");

            if (coworkerType === "individual") {
                individualForm.classList.remove("hidden");
            } 
            
            else if (coworkerType === "team") {
                // Redirect to addTeam.php if "Team" is selected
                window.location.href = "addTeam.php";
            } 
            else {
                individualForm.classList.add("hidden");
            }
        }
    </script>
</body>

</html>