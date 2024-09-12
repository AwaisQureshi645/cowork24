<?php
session_start();
header("Cache-Control: no-cache, must-revalidate"); 
$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for user role
if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'head' && 
     $_SESSION['role'] !== 'financehead' && 
     $_SESSION['role'] !== 'floorHost')) {
    header('Location: access_denied.php');
    exit();
}

// Initialize or reset the session data
if (!isset($_SESSION['step']) || isset($_GET['reset'])) {
    $_SESSION['step'] = 1;
    unset($_SESSION['team_id'], $_SESSION['num_coworkers'], $_SESSION['contract_id'], $_SESSION['branch_id'], $_SESSION['offices']);
}

$step = $_SESSION['step'];  // Read the step from session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            // Step 1: Team Information
            $teamName = $_POST['team_name'];
            $joiningDate = $_POST['joining_date'];
            $endingDate = $_POST['ending_date'];
            $discount = $_POST['discount'];
            $securityAmount = $_POST['security_amount'];
            $pointOfContact = $_POST['point_of_contact'];
            $reference = $_POST['reference'];
            $numMembers = $_POST['num_members'];

            $insertTeam = $conn->prepare("INSERT INTO team (TeamName, JoiningDate, EndingDate, Discount, SecurityAmount, PointofContact, Reference, no_of_members) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insertTeam->bind_param("sssiiisi", $teamName, $joiningDate, $endingDate, $discount, $securityAmount, $pointOfContact, $reference, $numMembers);
            if ($insertTeam->execute()) {
                $_SESSION['team_id'] = $conn->insert_id;
                $_SESSION['step'] = 2; // Proceed to next step
            } else {
                echo "Error: " . $insertTeam->error;
            }
            break;

        case 2:
            // Step 2: Number of Coworkers
            $_SESSION['num_coworkers'] = $_POST['num_coworkers'];
            $_SESSION['step'] = 3; // Proceed to next step
            break;

        case 3:
            // Step 3: Coworker Details
            $numCoworkers = $_SESSION['num_coworkers'];
            for ($i = 1; $i <= $numCoworkers; $i++) {
                $name = $_POST['name_' . $i];
                $contact = $_POST['contact_' . $i];
                $email = $_POST['email_' . $i];
                $coworkerType = $_POST['coworker_type_' . $i];

                $insertCoworker = $conn->prepare("INSERT INTO coworkers (name, contact_info, email, coworker_type, TeamID) VALUES (?, ?, ?, ?, ?)");
                $insertCoworker->bind_param("ssssi", $name, $contact, $email, $coworkerType, $_SESSION['team_id']);
                $insertCoworker->execute();
            }
            $_SESSION['step'] = 4; // Proceed to next step
            break;

        case 4:
            // Step 4: Contract Details
            $contractDetails = $_POST['contract_details'];
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            $upload_dir = 'uploaded_files/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create directory if not exists
            }
        
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image = $_FILES['image']['tmp_name'];
                $image_name = basename($_FILES['image']['name']);
                $image_size = $_FILES['image']['size'];
                $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        
                // Validate file size
                if ($image_size > (50 * 1024 * 1024)) {
                    echo "<script>alert('File size is greater than 50MB');</script>";
                    exit();
                }
        
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'pdf','PNG','JPG'];
                if (!in_array($image_ext, $allowed_ext)) {
                    echo "<script>alert('Invalid File Extension');</script>";
                    exit();
                }
        
                $contract_copy = $upload_dir . $coworker_id . "." . $image_ext;
                    
                    // Insert into database
                    $insertContract = $conn->prepare("INSERT INTO contracts (contract_details, start_date, end_date, contract_copy, TeamID) VALUES (?, ?, ?, ?, ?)");
                    $insertContract->bind_param("ssssi", $contractDetails, $startDate, $endDate, $contractCopy, $_SESSION['team_id']);
                    $insertContract->execute();
                  
                    echo "Contract successfully added.";
                    $_SESSION['contract_id'] = $conn->insert_id;
                    $_SESSION['step'] = 5; // Proceed to next step
                    break;
        
                } else {
                    echo "File upload error.";
                }
           
        case 5:
          
           
                $_SESSION['branch_id'] = $_POST['branch_id'];
                $branchId = $_SESSION['branch_id'];
               
                $_SESSION['step'] = 5;
           
            break;

         
            

        case 6:
            $_SESSION['officeID'] = $_POST['officeID'];
            $officeId = $_POST['officeID'];
            $teamId = $_SESSION['team_id'];
            $contractId = $_SESSION['contract_id'];
            $rentAmount = $_POST['rent_amount'];
            $rentStatus = $_POST['rent_status'];
            $rentPaymentDate = $_POST['rent_payment_date'];
            $securityDepositAmount = $_POST['security_deposit_amount'];
            $securityDepositStatus = $_POST['security_deposit_status'];
            $securityDepositPaymentDate = $_POST['security_deposit_payment_date'];

            // Update office status
            $updateOfficeStatus = $conn->prepare("UPDATE office SET status = 'booked' WHERE OfficeID = ?");
            $updateOfficeStatus->bind_param("i", $officeId);
            $updateOfficeStatus->execute();

            // Insert booking record with additional fields
            $insertBooking = $conn->prepare("
                INSERT INTO office_bookings 
                (team_id, office_id, contract_id, rent_amount, rent_status, rent_payment_date, 
                 security_deposit_amount, security_deposit_status, security_deposit_payment_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertBooking->bind_param(
                "iiiisssss",
                $teamId,
                $officeId,
                $contractId,
                $rentAmount,
                $rentStatus,
                $rentPaymentDate,
                $securityDepositAmount,
                $securityDepositStatus,
                $securityDepositPaymentDate
            );
            $insertBooking->execute();
            
            // Final step reached; redirect to welcome page
            header('Location: welcome.php');
            exit;
    }

    if ($_SESSION['step'] < 7) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch branch data outside form processing
$branches = $conn->query("SELECT branch_id, branch_name FROM branches");

// Function to get step title
function getStepTitle($step) {
    switch ($step) {
        case 1:
            return "Step 1: Enter Team Details";
        case 2:
            return "Step 2: Specify Number of Coworkers";
        case 3:
            return "Step 3: Enter Coworker Details";
        case 4:
            return "Step 4: Enter Contract Details";
        case 5:
            return "Step 5: Select Branch";
        case 6:
            return "Step 6: Confirm Office Booking";
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
    <style>
         body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #007bff, #00d4ff);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            width: 90%;
            max-width: 600px;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: auto;
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

        .form-control, .form-control select, .form-control textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
            margin-bottom: 0.5rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus, .form-control select:focus, .form-control textarea:focus {
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

        .inline-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .inline-group > div {
            flex: 1;
            min-width: 200px;
        }

        .full-width {
            width: 100%;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 0.5rem;
        }

        .success {
            color: green;
            font-size: 16px;
            margin-top: 0.5rem;
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
        <h1><?php echo getStepTitle($step); ?></h1>
        <!-- Reset Button to Start a New Team Addition -->
        <a href="logout.php" class="logout-button">Logout</a>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?reset=1" style="display: inline-block; margin-bottom: 10px; padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">Start New Team</a>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($step == 1): ?>
                <div class="form-group">
                    <label for="team_name">Team Name:</label>
                    <input type="text" id="team_name" name="team_name" class="form-control" required>
                </div>

                <div class="inline-group">
                    <div class="form-group">
                        <label for="joining_date">Joining Date:</label>
                        <input type="date" id="joining_date" name="joining_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="ending_date">Ending Date:</label>
                        <input type="date" id="ending_date" name="ending_date" class="form-control" required>
                    </div>
                </div>

                <div class="inline-group">
                    <div class="form-group">
                        <label for="discount">Discount:</label>
                        <input type="number" id="discount" name="discount" class="form-control" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="security_amount">Security Amount:</label>
                        <input type="number" id="security_amount" name="security_amount" class="form-control" min="0" required>
                    </div>
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
                    <input type="number" id="num_members" name="num_members" class="form-control" min="1" required>
                </div>

            <?php elseif ($step == 2): ?>
                <div class="form-group">
                    <label for="num_coworkers">Number of Coworkers:</label>
                    <input type="number" id="num_coworkers" name="num_coworkers" class="form-control" min="1" required>
                </div>

            <?php elseif ($step == 3): ?>
                <?php for ($i = 1; $i <= $_SESSION['num_coworkers']; $i++): ?>
                    <h3>Coworker <?php echo $i; ?></h3>
                    <div class="inline-group">
                        <div class="form-group">
                            <label for="name_<?php echo $i; ?>">Name:</label>
                            <input type="text" id="name_<?php echo $i; ?>" name="name_<?php echo $i; ?>" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_<?php echo $i; ?>">Contact Info:</label>
                            <input type="text" id="contact_<?php echo $i; ?>" name="contact_<?php echo $i; ?>" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="email_<?php echo $i; ?>">Email:</label>
                            <input type="email" id="email_<?php echo $i; ?>" name="email_<?php echo $i; ?>" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="coworker_type_<?php echo $i; ?>">Coworker Type:</label>
                            
                    <select id="coworker_type_<?php echo $i; ?>" name="coworker_type_<?php echo $i; ?>">
                        <option value="individual">Individual</option>
                        <option value="team">Team</option>
                    </select>
                        </div>
                    </div>
                <?php endfor; ?>

            <?php elseif ($step == 4): ?>
                <div class="form-group">
                    <label for="contract_details">Contract Details:</label>
                    <textarea id="contract_details" name="contract_details" class="form-control" required></textarea>
                </div>

                <div class="inline-group">
                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                <label for="contract_copy">Contract Copy:</label>
                <input type="file" name="contract_copy" accept=".jpg,.jpeg,.png,.webp,.pdf" required><br>

            <?php elseif ($step == 5): ?>
                <div class="form-group">
                    <label for="branch_id">Select Branch:</label>
                    <select id="branch_id" name="branch_id" class="form-control" required>
                        <?php while ($branch = $branches->fetch_assoc()): ?>
                            <option value="<?php echo $branch['branch_id']; ?>"><?php echo $branch['branch_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="error"><?php echo $_SESSION['error_message']; ?></div>
                <?php endif; ?>
            <?php elseif ($step == 6): ?>
                <div class="form-group">
    <label for="office_id">Select Office:</label>
    <?php if (isset($availableOffices) && count($availableOffices) > 0): ?>
        <select id="office_id" name="office_id" required>
            <?php foreach ($availableOffices as $office): ?>
                <option value="<?php echo htmlspecialchars($office['OfficeID']); ?>">
                    Room No: <?php echo htmlspecialchars($office['RoomNo']); ?> - Capacity: <?php echo htmlspecialchars($office['capacity']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php else: ?>
        <script>
            alert("No office is available in the selected branch. Redirecting to step 5...");
            $_SESSION['step'] = 5;
            
        </script>
    <?php endif; ?>
</div>

        </div>
                <div class="inline-group">
    <div class="form-group">
        <label for="rent_amount">Rent Amount:</label>
        <input type="number" id="rent_amount" name="rent_amount" class="form-control" min="0">
    </div>

    <div class="form-group">
        <label for="rent_status">Rent Status:</label>
        <select id="rent_status" name="rent_status" class="form-control">
            <option value="paid">Paid</option>
            <option value="pending">Pending</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="rent_payment_date">Rent Payment Date:</label>
        <input type="date" id="rent_payment_date" name="rent_payment_date" class="form-control">
    </div>
    
    <div class="form-group">
        <label for="security_deposit_amount">Security Deposit Amount:</label>
        <input type="number" id="security_deposit_amount" name="security_deposit_amount" class="form-control" min="0">
    </div>

    <div class="form-group">
        <label for="security_deposit_status">Security Deposit Status:</label>
        <select id="security_deposit_status" name="security_deposit_status" class="form-control">
            <option value="paid">Paid</option>
            <option value="pending">Pending</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="security_deposit_payment_date">Security Deposit Payment Date:</label>
        <input type="date" id="security_deposit_payment_date" name="security_deposit_payment_date" class="form-control">
    </div>
</div>
            <?php endif; ?>
            <button type="submit">Next</button>
        </form>
    </div>
</body>
</html>
