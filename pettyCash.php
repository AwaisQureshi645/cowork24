<?php
session_start();

header('Content-Type: text/html; charset=UTF-8');

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
     $_SESSION['role'] !== 'floorHost')) {
    header('Location: access_denied.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $branch_id = $_POST['branch_id'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $amount = $_POST['amount'];
    $action = $_POST['action']; // 'spend' or 'add'

    // Fetch current data for the branch and month
    $fetch_sql = "SELECT total_given, total_left FROM petty_cash 
                  WHERE branch_id = ? AND month = ? AND year = ?";
    $stmt = $conn->prepare($fetch_sql);
    $stmt->bind_param('iii', $branch_id, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_given = $row['total_given'];
        $total_left = $row['total_left'];

        if ($action === 'spend') {
            // Calculate new total_left
            $new_total_left = $total_left - $amount;
        } elseif ($action === 'add') {
            // Calculate new total_left
            $new_total_left = $total_left + $amount;
            // Also update total_given to reflect the additional amount
            $total_given += $amount;
        }

        // Update the database
        $update_sql = "UPDATE petty_cash 
                       SET total_given = ?, total_left = ? 
                       WHERE branch_id = ? AND month = ? AND year = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ddiii', $total_given, $new_total_left, $branch_id, $month, $year);
    } else {
        // Handle case where there's no existing record
        if ($action === 'add') {
            // Insert new record if adding more petty cash
            $insert_sql = "INSERT INTO petty_cash (branch_id, month, year, total_given, total_left) 
                           VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param('iiidd', $branch_id, $month, $year, $amount, $amount);
        } else {
            echo "No existing record found for the specified branch, month, and year.";
            exit();
        }
    }

    $stmt->execute();
    $stmt->close();
}

// Fetch data for the chart
$sql = "SELECT b.branch_name, p.month, p.year, SUM(p.total_given) AS total_given, SUM(p.total_left) AS total_left
        FROM petty_cash p
        JOIN branches b ON p.branch_id = b.branch_id
        GROUP BY b.branch_name, p.month, p.year
        ORDER BY b.branch_name, p.year, p.month";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = [
    'branches' => [],
    'totalGiven' => [],
    'totalLeft' => [],
    'months' => [],
    'years' => []
];

while ($row = $result->fetch_assoc()) {
    $data['branches'][] = $row['branch_name'] . ' (' . $row['month'] . '/' . $row['year'] . ')';
    $data['totalGiven'][] = (float)$row['total_given'];
    $data['totalLeft'][] = (float)$row['total_left'];
    $data['months'][] = $row['month'];
    $data['years'][] = $row['year'];
}

$stmt->close();

// Fetch distinct branch IDs for the form
$branch_sql = "SELECT branch_id, branch_name FROM branches";
$branch_result = $conn->query($branch_sql);

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Petty Cash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>
        body{
            background-color: white ;
            max-width: 700px !important;
            width: 80% !important;
            margin: auto;
            height: auto !important;

         
        }
        #form-container_pettyCash {
       
            background-color: lightgrey !important;
        
        }
        .container {
           
          
            background-color: white;
        }
        .container h2{
            padding-top: 1rem;
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


<div id="form-container_pettyCash">

</div>
    <div class="container">
       
        <h2 class="text-center ">Update Petty Cash</h2>
        <form method="post" action="">
            <div class="mb-3">
                <label for="branch_id" class="form-label">Branch</label>
                <select id="branch_id" name="branch_id" class="form-select" required>
                    <option value="">Select Branch</option>
                    <?php
                    while ($branch_row = $branch_result->fetch_assoc()) {
                        echo '<option value="'.$branch_row['branch_id'].'">'.$branch_row['branch_name'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="month" class="form-label">Month</label>
                <input type="number" id="month" name="month" class="form-control" min="1" max="12" required>
            </div>
            <div class="mb-3">
                <label for="year" class="form-label">Year</label>
                <input type="number" id="year" name="year" class="form-control" min="2000" max="2100" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" id="amount" name="amount" class="form-control" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="action" class="form-label">Action</label>
                <select id="action" name="action" class="form-select" required>
                    <option value="spend">Spend</option>
                    <option value="add">Add</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <div class="mt-5">
            <canvas id="barChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const data = <?php echo json_encode($data); ?>;

        var ctxBar = document.getElementById('barChart').getContext('2d');

        var barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: data.branches,
                datasets: [
                    {
                        label: 'Total Amount Given',
                        data: data.totalGiven,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)'
                    },
                    {
                        label: 'Total Amount Left',
                        data: data.totalLeft,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
