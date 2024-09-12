<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require 'vendor/autoload.php';
$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password = '';

$conn = new mysqli($host, $username_db, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'head' && 
     $_SESSION['role'] !== 'financehead' && 
     $_SESSION['role'] !== 'floorHost' && 
     $_SESSION['role'] !== 'manager')) {
    header('Location: access_denied.php');
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $email = $conn->real_escape_string($_POST['email']);
    $sql = "SELECT id FROM admin WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        $new_password = bin2hex(random_bytes(5));
        //$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $sql = "UPDATE admin  SET password='$new_password' WHERE email='$email' ";
        if ($conn->query($sql) === TRUE) {

            $mail = new PHPMailer(true);

            try {

                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'Cowork24management@gmail.com'; 
                $mail->Password   = 'qonp ohjf vnqp njll';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;


                $mail->setFrom('your-email@gmail.com', 'cowrkManagement');
                $mail->addAddress($email);


                $mail->isHTML(true);
                $mail->Subject = 'Your New Password';
                $mail->Body    = "Your new password is: <b>$new_password</b>";

                $mail->send();
                header('location:index.php');
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error updating password: " . $conn->error;
        }
    } else {
        echo "We are sorry, but you are not a member.";
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="cowork-logo.PNG">
    <title>forget password</title>
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
            width: 300px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .forget-password h2 {
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

        .form-group input {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
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
    <a href="logout.php" class="logout-button">Logout</a>

        <div class="forget-password">
            <h2>Forget Password</h2>
            <form id="forgetpass" method="post" action="">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required />
                </div>
                <div class="form-group">
                    <button type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>