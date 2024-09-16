<?php
session_start();
if ($_SESSION['role'] !== 'head') {
    header('Location: access_denied.php');
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';


$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeID = $_POST['employeeID'];
    $leave_type = $_POST['leave_type'];
    $leave_start_date = $_POST['leave_start_date'];
    $leave_end_date = $_POST['leave_end_date'];
    $total_days = $_POST['total_days'];
    $leave_status = 'Pending';

    $check_query = "SELECT id FROM coworkusers WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $employeeID);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        
        $query = "INSERT INTO leave_records (employeeID, leave_type, leave_start_date, leave_end_date, total_days, leave_status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssis", $employeeID, $leave_type, $leave_start_date, $leave_end_date, $total_days, $leave_status);
        
        if ($stmt->execute()) {
            
            $email_query = "SELECT email FROM coworkusers WHERE id = ?";
            $email_stmt = $conn->prepare($email_query);
            $email_stmt->bind_param("s", $employeeID);
            $email_stmt->execute();
            $email_result = $email_stmt->get_result();
            $email_row = $email_result->fetch_assoc();
            $employee_email = $email_row['email'];

        
            $mail = new PHPMailer(true);
            try {
                
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cowork24management@gmail.com'; 
                $mail->Password   = 'qonp ohjf vnqp njll'; // Your Gmail app-specific password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

        
                $mail->setFrom('cowork24management@gmail.com', 'Cowork Management');
                $mail->addAddress($employee_email); 

                
                $mail->isHTML(true);
                $mail->Subject = 'Leave Request Submitted';
                $mail->Body    = 'Your leave request has been submitted and is awaiting approval.';

                $mail->send();
                echo 'Leave request added and email sent.';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error inserting leave request: " . $conn->error;
        }
    } else {
        echo "Employee not found.";
    }
}


$employees_query = "SELECT id, username FROM coworkusers";
$employees_result = $conn->query($employees_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Leave Request</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eaeaea;
            color: #fff;
            padding: 50px;
        }

        h1 {
            text-align: center;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
        }

        .form-row div {
            width: 48%;
        }

        button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .loader {
            display: none;
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 2s  infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .message {
            display: none;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }

        .success {
            color: white;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Add Leave Request</h1>
    <div class="loader" id="loader"></div>
    <div class="message" id="message"></div>
    <form method="post" action="" id="leaveForm">
        <div class="form-row">
            <div>
                <label for="employeeID">Employee:</label>
                <select id="employeeID" name="employeeID" required>
                    <option value="">Select Employee</option>
                    <?php while ($row = $employees_result->fetch_assoc()) { ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo $row['username'] . ' (ID: ' . $row['id'] . ')'; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label for="leave_type">Leave Type:</label>
                <select id="leave_type" name="leave_type" required>
                    <option value="Sick Leave">Sick Leave</option>
                    <option value="Casual Leave">Casual Leave</option>
                    <option value="Paid Leave">Paid Leave</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="leave_start_date">Start Date:</label>
                <input type="date" id="leave_start_date" name="leave_start_date" required>
            </div>
            <div>
                <label for="leave_end_date">End Date:</label>
                <input type="date" id="leave_end_date" name="leave_end_date" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="total_days">Total Days:</label>
                <input type="number" id="total_days" name="total_days" required>
            </div>
        </div>

        <button type="submit">Submit</button>
    </form>

    <script>
        document.getElementById('leaveForm').addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('loader').style.display = 'block';
            document.getElementById('message').style.display = 'none';

            var formData = new FormData(this);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('loader').style.display = 'none';
                document.getElementById('message').innerHTML = 'Email has been sent successfully.';
                document.getElementById('message').classList.add('success');
                document.getElementById('message').style.display = 'block';

                setTimeout(() => {
                    window.location.href = 'view_leaves.php';
                }, 1000); // Redirect after 3 seconds
            })
            .catch(error => {
                document.getElementById('loader').style.display = 'none';
                document.getElementById('message').innerHTML = 'There was an error: ' + error;
                document.getElementById('message').classList.add('error');
                document.getElementById('message').style.display = 'block';
            });
        });
    </script>
</body>
</html>
