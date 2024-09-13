<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Handle employee form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $id = $_POST['id'];
    $cnic = $_POST['cnic'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phoneNumber = $_POST['phonenumber'];
    $TeamId = $_POST['TeamId'];
    $role = $_POST['role'];
    $branch_id = $_POST['branch_id'];

    // Check if the employee ID already exists in the database
    $check_query = "SELECT id FROM coworkusers WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // If employee ID exists, show alert and redirect back to newemployee.php
        echo "<script>alert('Employee ID already exists. Please use a different ID.');
              window.location.href = 'newemployee.php';</script>";
        exit();
    }

    // Proceed with adding the employee if the ID does not exist
    $upload_dir = 'uploaded_image/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if not exists
    }

    $image = $_FILES['image']['tmp_name'];
    $image_name = basename($_FILES['image']['name']);
    $imgData = strtolower(pathinfo($image_name, PATHINFO_EXTENSION)); // Convert extension to lowercase
    $img_desc = $upload_dir . $username . "." . $imgData;

    // Validate file size
    if ($_FILES['image']['size'] > (1 * 1024 * 1024)) {
        echo "<script>alert('Image size is greater than 1MB');</script>";
        exit();
    }

    // Validate file type (case-insensitive)
    if (!in_array($imgData, ['jpg', 'jpeg', 'png', 'webp'])) {
        echo "<script>alert('Invalid Image Extension');</script>";
        exit();
    }

    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        if (move_uploaded_file($image, $img_desc)) {
            $stmt = $conn->prepare("INSERT INTO coworkusers (id, CNIC, username, email, password, phonenumber, role, CNICpic, branch_id, TeamID) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $id, $cnic, $username, $email, $password, $phoneNumber, $role, $img_desc, $branch_id, $TeamId);

            if ($stmt->execute()) {
                echo "<script>alert('Upload Successful');</script>";
                $stmt->close();
                $conn->close();
                header("Location: /cowork/employeeData.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "<script>alert('Failed to move uploaded file.');</script>";
        }
    } else {
        echo "<script>alert('File upload error: " . $_FILES['image']['error'] . "');</script>";
    }
}

// Fetch branches and teams
$branches = $conn->query("SELECT branch_id, branch_name FROM branches");
$teams = $conn->query("SELECT TeamID, TeamName FROM team");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="cowork-logo.PNG">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #007bff, #00d4ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 85%;
            max-width: 900px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border-radius: 15px;
        }

        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: block;
            color: #333;
        }

        .form-control, .form-select, .form-control-file {
            padding: 12px;
            margin-bottom: 1rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            width: 100%;
        }

        button {
            padding: 15px;
            color: #fff;
            background: #007bff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #0056b3;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .form-col {
            flex: 1;
            min-width: 45%;
        }

        .logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
            display: inline-block;
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
    <script>
        function handleTeamChange(selectElement) {
            if (selectElement.value === 'add_new_team') {
                window.location.href = 'newTeam.php';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout-button">Logout</a>
        <h2>Add New Employee</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-col">
                    <label for="id">ID:</label>
                    <input type="text" id="id" name="id" class="form-control" required>
                </div>
                <div class="form-col">
                    <label for="cnic">CNIC:</label>
                    <input type="text" id="cnic" name="cnic" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-col">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-col">
                    <label for="phonenumber">Phone Number:</label>
                    <input type="tel" id="phonenumber" name="phonenumber" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="TeamId">Team:</label>
                    <select id="TeamId" name="TeamId" class="form-select" onchange="handleTeamChange(this)" required>
                        <option value="">Select a team</option>
                        <option value="add_new_team">Add New Team</option>
                        <?php while ($team = $teams->fetch_assoc()) : ?>
                            <option value="<?php echo $team['TeamID']; ?>"><?php echo $team['TeamName']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-col">
                    <label for="role">Role:</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="floorHost">Floor Host</option>
                        <option value="manager">Manager</option>
                        <option value="head">Head</option>
                        <option value="financehead">Finance Head</option>
                        <option value="employee">Employee</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="branch_id">Branch:</label>
                    <select id="branch_id" name="branch_id" class="form-select" required>
                        <option value="">Select a branch</option>
                        <?php while ($branch = $branches->fetch_assoc()) : ?>
                            <option value="<?php echo $branch['branch_id']; ?>"><?php echo $branch['branch_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-col">
                    <label for="image">Upload Image:</label>
                    <input type="file" id="image" name="image" class="form-control-file" required>
                </div>
            </div>

            <button type="submit" name="submit">Add Employee</button>
        </form>
    </div>
</body>
</html>
