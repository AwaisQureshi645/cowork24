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

// Check if reset is triggered
if (isset($_GET['reset']) && $_GET['reset'] == 1) {
    // Reset session or any necessary data
    unset($_SESSION['team_data']); // Example: Clear team data from the session
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $coworkerType = $_POST['coworker_type'];
    
    if ($coworkerType == "individual") {
        header("Location: add_individual.php");
    } elseif ($coworkerType == "team") {
        // Check if the user wants to start a new team
        if (isset($_POST['reset']) && $_POST['reset'] == '1') {
            unset($_SESSION['team_data']); // Clear session data related to team
        }
        header("Location: addTeam.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add a Coworker</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eaeaea; 
            color:black;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2); 
            text-align: center;
        }
        
        h2 {
            margin-bottom: 20px;
        }
        
        label {
            font-weight: bold;
            margin-bottom: 10px;
            display: inline-block;
        }
        
        input[type="radio"] {
            margin: 10px;
        }
        
        button {
            background-color: #008CBA; 
            color:white; 
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        
        button:hover {
            background-color: #005f73; 
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
    <script>
        // JavaScript function to detect "Team" selection and update form action
        function handleSelection() {
            const teamOption = document.getElementById('team');
            const form = document.getElementById('addCoworkerForm');

            if (teamOption.checked) {
                form.action = "<?php echo $_SERVER['PHP_SELF']; ?>?reset=1";
            } else {
                form.action = "<?php echo $_SERVER['PHP_SELF']; ?>";
            }
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Add a New Coworker</h2>
       

        
        <form id="addCoworkerForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="coworker_type">Are you adding an individual or a team?</label><br>
            
            <input type="radio" id="individual" name="coworker_type" value="individual" required onchange="handleSelection()">
            <label for="individual">Individual</label><br>
            
            <input type="radio" id="team" name="coworker_type" value="team" required onchange="handleSelection()">
            <label for="team">Team</label><br><br>
            
            <button type="submit">Next</button>
        </form>
    </div>
</body>
</html>
