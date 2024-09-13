<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaveID = $_POST['leaveID'];
    $leave_status = $_POST['leave_status'];

    $query = "UPDATE leave_records SET leave_status = ? WHERE leaveID = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("si", $leave_status, $leaveID);
        $stmt->execute();
        
        
        $email_query = "SELECT e.email, lr.employeeID FROM coworkusers e JOIN leave_records lr ON e.id = lr.employeeID WHERE lr.leaveID = ?";
        $email_stmt = $conn->prepare($email_query);
        if ($email_stmt) {
            $email_stmt->bind_param("i", $leaveID);
            $email_stmt->execute();
            $email_result = $email_stmt->get_result();
            $email_row = $email_result->fetch_assoc();
            $employee_email = $email_row['email'];

            $mail = new PHPMailer(true);

            try {
               
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'Cowork24management@gmail.com'; 
                $mail->Password   = 'qonp ohjf vnqp njll';        // Your Gmail password or app-specific password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

               
                $mail->setFrom('Cowork24management@gmail.com', 'Cowork Management');
                $mail->addAddress($employee_email);

               
                $mail->isHTML(true);
                $mail->Subject = 'Leave Request Status Update';
                $mail->Body    = "Your leave request has been " . $leave_status . ".";

                $mail->send();
                echo "Leave request updated and email sent.";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error preparing email statement: " . $conn->error;
        }
    } else {
        echo "Error preparing update statement: " . $conn->error;
    }
}


$leaveID = $_GET['id'];
$query = "SELECT * FROM leave_records WHERE leaveID = ?";
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param("i", $leaveID);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
} else {
    echo "Error preparing select statement: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Leave Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
        }
        h1 {
            color: white;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            color: #333;
        }
        label, select, button {
            display: block;
            margin-bottom: 15px;
            width: 100%;
        }
        label {
            font-weight: bold;
        }
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .loader {
            display: none;
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
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
    <h1>Update Leave Request</h1>
    
    <div class="loader" id="loader"></div>
    <div class="message" id="message"></div>
    
    <form method="post" id="updateLeaveForm">
        <input type="hidden" name="leaveID" value="<?php echo $record['leaveID']; ?>">
        <label for="leave_status">Leave Status:</label>
        <select id="leave_status" name="leave_status">
            <option value="Approved" <?php echo $record['leave_status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
            <option value="Rejected" <?php echo $record['leave_status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
            <option value="Pending" <?php echo $record['leave_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
        </select>
        <button type="submit">Update</button>
    </form>

    <script>
        document.getElementById('updateLeaveForm').addEventListener('submit', function(e) {
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
                document.getElementById('message').innerHTML = 'Leave status updated and email sent successfully.';
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
