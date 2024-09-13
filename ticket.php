<?php
session_start();

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

if (
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'head' &&
        $_SESSION['role'] !== 'financehead' &&
        $_SESSION['role'] !== 'floorHost')
) {
    header('Location: access_denied.php');
    exit();
}

$alert_message = "";
$redirect_url = "viewticket.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = $conn->real_escape_string($_POST['subject']);
    $description = $conn->real_escape_string($_POST['description']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $closeup_date = $conn->real_escape_string($_POST['closeup_date']);
    
    $branch_id = (int)$_POST['branch_id'];
    $created_by = $_SESSION['username'];

    // Fetch the logged-in user's email from session or set a default
    $user_email = isset($_SESSION['email']) ? $_SESSION['email'] : 'default@example.com'; // Fallback email if session email is not set

    $branch_check = $conn->prepare("SELECT branch_id FROM branches WHERE branch_id = ?");
    $branch_check->bind_param("i", $branch_id);
    $branch_check->execute();
    $branch_check->store_result();

    if ($branch_check->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO tickets (subject, description, created_by, branch_id, priority, closeup_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $subject, $description, $created_by, $branch_id, $priority, $closeup_date);

        if ($stmt->execute()) {
            $ticket_id = $stmt->insert_id;

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'Cowork24management@gmail.com'; 
                $mail->Password   = 'qonp ohjf vnqp njll'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Set the email from the logged-in user or default
                $mail->setFrom($user_email, $created_by);
                $mail->addAddress('Cowork24management@gmail.com'); 

                $mail->isHTML(true);
                $mail->Subject = "New Ticket Created: $subject";
                $mail->Body = "A new ticket has been created by $created_by.<br><br>Subject: <b>$subject</b><br>Description: <b>$description</b><br>Priority: <b>$priority</b><br>Close-up Date: <b>$closeup_date</b><br>Ticket ID: <b>$ticket_id</b>";

                $mail->send();
                $alert_message = "Ticket created and an email notification has been sent.";
            } catch (Exception $e) {
                $alert_message = "Ticket created but the email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $alert_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $alert_message = "Invalid branch ID.";
    }

    $conn->close();

    echo "<script>
        alert('$alert_message');
        window.location.href = '$redirect_url';
    </script>";
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="cowork-logo.PNG">
    <title>Create Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            background: linear-gradient(to top, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0) 50%), url(bgc.jpg);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            width: 400px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .create-ticket h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #4e7eee;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #7b0404;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            color: #333;
        }

        .form-group textarea {
            resize: vertical;
            height: 100px;
        }

        .form-group button {
            background-color: #3c66cb;
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
    <div class="container">
      
        <div class="create-ticket">
            <h2>Create Ticket</h2>
            <form method="post" action="ticket.php">
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="branch_id">Branch</label>
                    <select id="branch_id" name="branch_id" required>
                        <option value="">Select Branch</option>
                        <option value="1">Executive Branch</option>
                        <option value="2">Premium Branch</option>
                        <option value="3">I/10</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" required>
                        <option value="">Select priority</option>
                        <option value="1">Urgent</option>
                        <option value="2">Can be Delayed</option>
                        <option value="3">Normal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="closeup_date">closeup_date: </label>
                    <input type="date" id="closeup_date" name="closeup_date" class="form-control">
                </div>
                <div class="form-group">
                    <button type="submit">Create Ticket</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
