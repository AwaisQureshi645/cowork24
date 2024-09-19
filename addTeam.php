<?php
session_start();
header("Cache-Control: no-cache, must-revalidate");

// Database connection
$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for user role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['head', 'financehead', 'floorHost', 'manager'])) {
    header('Location: access_denied.php');
    exit();
}

// Initialize or reset session data
if (!isset($_SESSION['step']) || isset($_GET['reset'])) {
    $_SESSION['step'] = 1;
    unset($_SESSION['team_id'], $_SESSION['contract_id'], $_SESSION['branch_id']);
}

$step = $_SESSION['step']; // Read the step from session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            // Step 1: Team Information
            $teamName = $_POST['team_name'];
            $joiningDate = $_POST['joining_date'];
            $endingDate = $_POST['ending_date'];
            $securityAmount = $_POST['security_amount'];
            $pointOfContact = $_POST['point_of_contact'];
            $reference = $_POST['reference'];
            $numMembers = $_POST['num_members'];

            $insertTeam = $conn->prepare("INSERT INTO team (TeamName, JoiningDate, EndingDate, SecurityAmount, PointofContact, Reference, no_of_members) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertTeam->bind_param("sssissi", $teamName, $joiningDate, $endingDate, $securityAmount, $pointOfContact, $reference, $numMembers);

            if ($insertTeam->execute()) {
                $_SESSION['team_id'] = $conn->insert_id;
                $_SESSION['step'] = 2; // Proceed to next step
            } else {
                echo "Error: " . $insertTeam->error;
            }
            break;

        case 2:
            // Step 2: Contract Details
            $contractDetails = $_POST['contract_details'];
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];

            // Handle file upload
            $upload_dir = 'uploaded_files/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create directory if not exists
            }

            $contract_copy = NULL; // Default to null, as the image is not required
            if (isset($_FILES['contract_copy']) && $_FILES['contract_copy']['error'] === UPLOAD_ERR_OK) {
                $image = $_FILES['contract_copy']['tmp_name'];
                $image_name = basename($_FILES['contract_copy']['name']);
                $image_size = $_FILES['contract_copy']['size'];
                $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

                // Validate file size
                if ($image_size > (50 * 1024 * 1024)) {
                    echo "<script>alert('File size is greater than 50MB');</script>";
                    exit();
                }

                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
                if (!in_array($image_ext, $allowed_ext)) {
                    echo "<script>alert('Invalid File Extension');</script>";
                    exit();
                }

                $contract_copy = $upload_dir . time() . "." . $image_ext; // Unique filename
                move_uploaded_file($image, $contract_copy);
            }

            // Insert into database
            $insertContract = $conn->prepare("INSERT INTO contracts (contract_details, start_date, end_date, contract_copy, TeamID) VALUES (?, ?, ?, ?, ?)");
            $insertContract->bind_param("ssssi", $contractDetails, $startDate, $endDate, $contract_copy, $_SESSION['team_id']);
            if ($insertContract->execute()) {
                $_SESSION['contract_id'] = $conn->insert_id;
                $_SESSION['step'] = 3; // Proceed to next step
            } else {
                echo "Error: " . $insertContract->error;
            }
            break;

        case 3:
            // Step 3: Branch Selection
            if (isset($_POST['branch_id'])) {
                $_SESSION['branch_id'] = $_POST['branch_id'];
                $_SESSION['step'] = 4;  // Proceed to booking after branch selection
            } else {
                echo "<script>alert('Branch selection is required');</script>";
                exit();
            }
            break;

        case 4:
            // Step 4: Book Team directly
            $teamId = $_SESSION['team_id'];
            $branchId = $_SESSION['branch_id'];
            $contractId = $_SESSION['contract_id'];

            // Insert booking record
            $insertBooking = $conn->prepare("INSERT INTO office_bookings (team_id, branch_id, contract_id) VALUES (?, ?, ?)");
            $insertBooking->bind_param("iii", $teamId, $branchId, $contractId);

            if ($insertBooking->execute()) {
                header('Location: team.php');
                exit();
            } else {
                echo "Error: " . $insertBooking->error;
            }
            break;
    }

    // Redirect after successful submission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch branch data outside form processing
$branches = $conn->query("SELECT branch_id, branch_name FROM branches");

// Function to get step title
function getStepTitle($step)
{
    switch ($step) {
        case 1:
            return "Step 1: Enter Team Details";
        case 2:
            return "Step 2: Enter Contract Details";
        case 3:
            return "Step 3: Select Branch";
        case 4:
            return "Step 4: Confirm Booking";
        default:
            return "Office Booking System";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Office Booking System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eaeaea;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            margin-top: -2rem !important;
        }

        .form_container {
            font-family: Arial, sans-serif;
            background: #eaeaea;
            width: 100%;
            max-width: 600px;
            height: 90vh;
            overflow-y: auto;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        .form-control,
        .form-control select,
        .form-control textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
            margin-bottom: 0.5rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus,
        .form-control select:focus,
        .form-control textarea:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
            outline: none;
        }

        textarea {
            resize: vertical;
        }

        button {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="form_container">
        <h1><?php echo getStepTitle($step); ?></h1>
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?reset=1" style="display: inline-block; margin-bottom: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">Start New Team</a>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($step == 1): ?>
                <div class="form-group">
                    <label for="team_name">Team Name:</label>
                    <input type="text" id="team_name" name="team_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="joining_date">Joining Date:</label>
                    <input type="date" id="joining_date" name="joining_date" class="form-control" required onclick="this.showPicker();">
                </div>

                <div class="form-group">
                    <label for="ending_date">Ending Date:</label>
                    <input type="date" id="ending_date" name="ending_date" class="form-control" required onclick="this.showPicker();">
                </div>

                <div class="form-group">
                    <label for="security_amount">Security Amount:</label>
                    <input type="number" id="security_amount" name="security_amount" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="point_of_contact">Point of Contact:</label>
                    <textarea id="point_of_contact" name="point_of_contact" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label for="reference">Reference:</label>
                    <input type="text" id="reference" name="reference" class="form-control">
                </div>

                <div class="form-group">
                    <label for="num_members">Number of Members:</label>
                    <input type="number" id="num_members" name="num_members" class="form-control" required>
                </div>

            <?php elseif ($step == 2): ?>
                <div class="form-group">
                    <label for="contract_details">Contract Details:</label>
                    <textarea id="contract_details" name="contract_details" class="form-control" required></textarea>
                </div>

                <div class="inline-group">
                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required onclick="this.showPicker();">
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" required onclick="this.showPicker();">
                    </div>
                </div>

                <div class="form-group">
                    <label for="contract_copy">Contract Copy (optional):</label>
                    <input type="file" name="contract_copy" accept=".jpg,.jpeg,.png,.webp,.pdf"><br>
                </div>

            <?php elseif ($step == 3): ?>
                <div class="form-group">
                    <label for="branch_id">Select Branch:</label>
                    <select id="branch_id" name="branch_id" class="form-control" required>
                        <?php while ($branch = $branches->fetch_assoc()): ?>
                            <option value="<?php echo $branch['branch_id']; ?>"><?php echo $branch['branch_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

            <?php elseif ($step == 4): ?>
                <h3>Booking Confirmation</h3>
                <p>The team has been booked successfully in the selected branch.</p>
                <p onclick="redirectToAddTeam()">Show Information</p>
            <?php endif; ?>
            <button type="submit">Next</button>

        </form>
    </div>

    <script>
        function redirectToAddTeam() {
            window.location.href = "team.php"; // Redirect to addTeam.php
        }
    </script>

</body>

</html>
