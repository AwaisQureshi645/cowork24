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
     $_SESSION['role'] !== 'manager' && 
     $_SESSION['role'] !== 'floorHost')) {
    header('Location: access_denied.php');
    exit();
}

$results_per_page = 4; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

$sql = "
     SELECT o.OfficeID, o.RoomNo, o.capacity, 
           o.Price, o.branch_id, o.status
    FROM office o
    ORDER BY o.OfficeID DESC
    LIMIT $start_from, $results_per_page
";


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
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
         display: flex;
         justify-content: center;
            margin-top: -4rem;
        }

        .dashboard {
            width: 90%;
            max-width: 1700px;
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
            overflow-x: auto;
           
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
    
        <h2>Office Information</h2>
        <div style="text-align: right;margin-top: -4rem">
            <a class="btn btn-primary" href="/cowork/newOffice.php" role="button">Add New Office</a>
        </div>
        <div id="employeeTable">
            <table>
                <thead>
                    <tr>
                      
                        <th>RoomNo</th>
                        <th>Capacity</th>
                        <th>Price</th>
                       
                        <th>Branch ID</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // while ($row = $result->fetch_assoc()) {


                        while ($row = $result->fetch_assoc()) {
                            // Determine the branch name based on branch_id
                            $branch_name = ($row['branch_id'] == 1) ? 'Executive' : 
                                           (($row['branch_id'] == 2) ? 'Premium' : 
                                           (($row['branch_id'] == 3) ? 'I-10' : 'Unknown'));


                        echo "<tr>
                        <td>{$row['RoomNo']}</td>
                        <td>{$row['capacity']}</td>
                        <td>{$row['Price']}</td>
                        <td>{$row['branch_id']}</td>
                        <td style='color: " . (empty($row['status']) ? 'red' : 'black') . ";'>" . (!empty($row['status']) ? $row['status'] : 'Not Available') . "</td>
                        <td>
                            <div class='icbtn'>
                                <a class='btn btn-primary btn-sm' href='editOffice.php?OfficeID={$row['OfficeID']}' role='button'>
                                    <i class='fa-solid fa-pen-to-square'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' href='deleteOffice.php?OfficeID={$row['OfficeID']}' role='button'>
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
