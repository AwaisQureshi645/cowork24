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

$results_per_page = 3; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

$sql = "SELECT * FROM meetingroom LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$total_results_sql = "SELECT COUNT(*) FROM office";
$total_results_result = $conn->query($total_results_sql);
$total_results_row = $total_results_result->fetch_row();
$total_results = $total_results_row[0];
$total_pages = ceil($total_results / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="cowork-logo.PNG">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .dashboard {
            width: 80%;
            max-width: 1200px;
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
            text-align: left;
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a.btn {
            text-decoration: none;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            margin: 0 2px;
            display: inline-block;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .pagination a {
            color: #007bff;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }

        .pagination a:hover {
            background-color: #ddd;
        }

        .icbtn {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .hidden {
            display: none;
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
    <div class="dashboard">
    <a href="logout.php" class="logout-button">Logout</a>
        <h2>Meeting Room Information</h2>
        <div style="text-align: right;">
            <a class="btn btn-primary" href="/cowork/newmeeting.php" role="button">Add New Meeting Room</a>
        </div>
        <div id="meetingroomTable">
            <table>
                <thead>
                    <tr>
                        
                        <th>Meeting Room ID</th>
                        <th>Name</th>
                        <th>Capacity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                      
                        echo "<tr>
                                
                                  <td>{$row['meetingRoomID']}</td>
                                  <td>{$row['name']}</td>
                                  <td>{$row['capacity']}</td>
                                  
                                <td>
                                    <div class='icbtn'>
                                        <a class='btn btn-primary btn-sm' href='/cowork/editMeetingRoom.php?meetingRoomID={$row['meetingRoomID']}' role='button'>
                                            <i class='fa-solid fa-pen-to-square'></i>
                                        </a>
                                        <a class='btn btn-danger btn-sm' href='/cowork/deleteMeetingRoom.php?meetingRoomID={$row['meetingRoomID']}' role='button'>
                                            <i class='fa-solid fa-trash'></i>
                                        </a>
                                    </div>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <?php
            for ($i = 1; $i <= $total_pages; $i++) {
                echo "<a class='" . ($i == $page ? 'active' : '') . "' href='?page={$i}'>{$i}</a>";
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
