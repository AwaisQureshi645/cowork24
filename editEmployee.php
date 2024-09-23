<?php
session_start();
$id = "";
$cnic = "";
$name = "";
$email = "";
$password = "";
$phoneNumber = "";
$imagePath = "";
$TeamId = "";
$role = "";
$branch_id = "";
$existingImage = ""; 

$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
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


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["id"])) {
        header("Location: /cowork/employeeData.php");
        exit;
    }
    $id = $conn->real_escape_string($_GET["id"]);
    $result = "SELECT * FROM coworkusers WHERE id='$id'";
    $sql = $conn->query($result);
    $row = $sql->fetch_assoc();
    if (!$row) {
        header("Location: /cowork/employeeData.php");
        exit;
    }
    $id = $row["id"];
    $cnic = $row["CNIC"];
    $name = $row["username"];
    $email = $row["email"];
    $password = $row["password"];
    $phoneNumber = $row["phonenumber"];
    $role = $row["role"];
    $TeamId = $row["TeamId"];
    $branch_id = $row["branch_id"];
    $existingImage = $row["CNICpic"]; 
} else {
    $original_id = $conn->real_escape_string($_POST["original_id"]); 
    $id = $conn->real_escape_string($_POST["id"]);
    $cnic = $conn->real_escape_string($_POST["cnic"]);
    $name = $conn->real_escape_string($_POST["username"]);
    $email = $conn->real_escape_string($_POST["email"]);
    $password = $conn->real_escape_string($_POST["password"]);
    $phoneNumber = $conn->real_escape_string($_POST["phoneNumber"]);
    $TeamId = $conn->real_escape_string($_POST["TeamId"]);
    $role = $conn->real_escape_string($_POST["role"]);
    $branch_id = $conn->real_escape_string($_POST["branch_id"]);
    $existingImage = $conn->real_escape_string($_POST["existing_image"]);

    $imagePath = $existingImage;

    if (empty($id) || empty($cnic) || empty($name) || empty($email) || empty($password) || empty($phoneNumber) || empty($role) || empty($TeamId) || empty($branch_id)) {
        $errormessage = "All fields are required";
    } else {
        if ($original_id !== $id) {
            $check_id_query = "SELECT id FROM coworkusers WHERE id='$id'";
            $check_id_result = $conn->query($check_id_query);
            if ($check_id_result->num_rows > 0) {
                $errormessage = "The new ID already exists. Please choose a different ID.";
            }
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploaded_image/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $image = $_FILES['image']['tmp_name'];
            $image_name = basename($_FILES['image']['name']);
            $imgData = pathinfo($image_name, PATHINFO_EXTENSION);
            $newImagePath = $upload_dir . $name . "." . $imgData;

            if ($_FILES['image']['size'] > (1 * 1024 * 1024)) {
                $errormessage = "Image size is greater than 1MB";
            } elseif (!in_array($imgData, ['jpg', 'jpeg', 'png', 'webp'])) {
                $errormessage = "Invalid Image Extension";
            } else {
                if (move_uploaded_file($image, $newImagePath)) {
                    if ($existingImage && file_exists($existingImage)) {
                        unlink($existingImage);
                    }
                    $imagePath = $newImagePath;
                } else {
                    $errormessage = "Failed to move uploaded file.";
                }
            }
        }

        if (empty($errormessage)) {
            $sql = "UPDATE coworkusers SET id=?, CNIC=?, username=?, email=?, password=?, phonenumber=?, role=?, TeamId=?, branch_id=?, CNICpic=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssssssss', $id, $cnic, $name, $email, $password, $phoneNumber, $role, $TeamId, $branch_id, $imagePath, $original_id);
            $result = $stmt->execute();
            if (!$result) {
                $errormessage = "Invalid query: " . $stmt->error;
            } else {
                $successmessage = "Employee data updated successfully";
                header("Location: /cowork/employeeData.php");
                exit;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="cowork-logo.PNG">
    <title>Edit Employee Data</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            background: #eaeaea;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
        

            margin-top:-4rem ;
        }

        .container {
            width: 500px;
            padding: 30px;
            background: #fff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            border: 1px solid #ddd;
            max-height: 90vh;
            overflow-y: auto;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            padding-top: 10px;
            font-size: 24px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="tel"], input[type="file"] {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="tel"] {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        input[type="file"] {
            padding: 3px;
            margin-bottom: 20px;
        }

        button {
            padding: 12px;
            color: #fff;
            background: #007bff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #0056b3;
        }

        .message {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error {
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
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
 
        <h2>Edit Employee Data</h2>
        <?php if (!empty($errormessage)): ?>
            <p class="error"><?= htmlspecialchars($errormessage) ?></p>
        <?php endif; ?>
        <?php if (!empty($successmessage)): ?>
            <p class="success"><?= htmlspecialchars($successmessage) ?></p>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="original_id" value="<?= htmlspecialchars($id) ?>"> <!-- Hidden field to store the original id -->
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($existingImage) ?>">

            <label for="id">ID</label>
            <input type="text" name="id" value="<?= htmlspecialchars($id) ?>" required>
            <label for="cnic">CNIC</label>
            <input type="text" name="cnic" value="<?= htmlspecialchars($cnic) ?>" required>
            <label for="username">Name</label>
            <input type="text" name="username" value="<?= htmlspecialchars($name) ?>" required>
            <label for="email">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            <label for="password">Password</label>
            <input type="password" name="password" value="<?= htmlspecialchars($password) ?>" required>
            <label for="phoneNumber">Phone Number</label>
            <input type="tel" name="phoneNumber" value="<?= htmlspecialchars($phoneNumber) ?>" required>
            <label for="role">Role</label>
            <input type="text" name="role" value="<?= htmlspecialchars($role) ?>" required>
            <label for="TeamId">Team ID</label>
            <input type="text" name="TeamId" value="<?= htmlspecialchars($TeamId) ?>" required>
            <label for="branch_id">Branch ID</label>
            <input type="text" name="branch_id" value="<?= htmlspecialchars($branch_id) ?>" required>
            <label for="image">CNIC Image</label>
            <input type="file" name="image" accept=".jpg, .jpeg, .png, .webp">
            <button type="submit">Update Employee Data</button>
        </form>
    </div>
</body>
</html>
