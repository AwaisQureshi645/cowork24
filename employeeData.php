<?php
session_start();

//echo "Session ID: " . session_id() . "<br>";
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
        $_SESSION['role'] !== 'floorHost' &&
        $_SESSION['role'] !== 'manager')
) {
    header('Location: access_denied.php');
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$results_per_page = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

$sql = "
    SELECT 
        coworkusers.*, 
        team.TeamName, 
        branches.branch_name 
    FROM coworkusers
    LEFT JOIN team ON coworkusers.TeamId = team.TeamID
    LEFT JOIN branches ON coworkusers.branch_id = branches.branch_id
    LIMIT $start_from, $results_per_page
";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$total_results_sql = "SELECT COUNT(*) FROM coworkusers";
$total_results_result = $conn->query($total_results_sql);
$total_results_row = $total_results_result->fetch_row();
$total_results = $total_results_row[0];
$total_pages = ceil($total_results / $results_per_page);
?>
<script>
    function filterTable() {
        var teamFilter = document.getElementById('teamFilter').value.toLowerCase();
        var branchFilter = document.getElementById('branchFilter').value.toLowerCase();
        var table = document.querySelector('table tbody');
        var rows = table.querySelectorAll('tr');

        rows.forEach(row => {
            var teamName = row.cells[7].textContent.toLowerCase();
            var branchName = row.cells[8].textContent.toLowerCase();
            var display = true;

            if (teamFilter && teamName.indexOf(teamFilter) === -1) {
                display = false;
            }
            if (branchFilter && branchName.indexOf(branchFilter) === -1) {
                display = false;
            }

            row.style.display = display ? '' : 'none';
        });
    }


    function toggleImage(element) {
        var img = element.nextElementSibling.querySelector('img');
        img.style.display = img.style.display === 'none' ? 'block' : 'none';
    }
</script>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">


    <link rel="icon" href="cowork-logo.PNG">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e8f0fe;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .dashboard {
            width: 100%;
            max-width: 1600px;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            padding: 30px;
            position: relative;
        }

        h2 {
            margin-bottom: 30px;
            color: #333;
            text-align: center;
            font-weight: bold;
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
            background-color: #c82333;
        }

        .table-container {
            overflow-x: auto;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
        }

        th,
        td {
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }

        .pagination a {
            color: #007bff;
            padding: 10px 20px;
            text-decoration: none;
            border: 1px solid #dee2e6;
            margin: 0 4px;
            border-radius: 5px;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }

        .pagination a:hover {
            background-color: #e9ecef;
        }

        .image-preview img {
            display: none;
            max-width: 500px;
            max-height: 300px;
            width: auto;
            height: auto;
        }

        .image-icon {
            cursor: pointer;
            font-size: 24px;
            color: #007bff;
        }

        .table-filters {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
            gap: 15px;
        }

        select {
            padding: 6px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #f8f9fa;
        }

        .add-employee-btn {
            text-align: right;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="dashboard">
    
        <h2>Employees Information</h2>
        <div class="add-employee-btn">
            <a class="btn btn-primary" href="/cowork/newemployee.php" role="button">Add New Employee</a>
        </div>
        <div class="table-filters">
            <select id="teamFilter" onchange="filterTable()">
                <option value="">All Teams</option>
                <?php
                $distinct_team_result = $conn->query("SELECT DISTINCT TeamID, TeamName FROM team");
                while ($row = $distinct_team_result->fetch_assoc()):
                ?>
                    <option value="<?php echo htmlspecialchars($row['TeamName']); ?>">
                        <?php echo htmlspecialchars($row['TeamName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select id="branchFilter" onchange="filterTable()">
                <option value="">All Branches</option>
                <?php
                $distinct_branch_result = $conn->query("SELECT DISTINCT branch_id, branch_name FROM branches");
                while ($row = $distinct_branch_result->fetch_assoc()):
                ?>
                    <option value="<?php echo htmlspecialchars($row['branch_name']); ?>">
                        <?php echo htmlspecialchars($row['branch_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>CNIC</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Phone Number</th>
                        <th>CNIC Picture</th>
                        <th>Team Name</th>
                        <th>Branch Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['CNIC'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['username'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['password'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['phonenumber'] ?? ''); ?></td>
                            <td>

                                <span class="image-icon" onclick="toggleImage(this)"><i class="fas fa-image"></i></span>
                                <div class="image-preview">
                                    <img src="<?php echo htmlspecialchars($row['CNICpic'] ?? ''); ?>" alt="CNIC Picture">
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['TeamName'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['branch_name'] ?? ''); ?></td>
                            <td>

                                <a class="btn btn-primary" href="/cowork/editEmployee.php?id=<?php echo htmlspecialchars($row['id']); ?>" role="button">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a class="btn btn-danger" href="/cowork/delete_employee.php?id=<?php echo htmlspecialchars($row['id']); ?>" role="button">
                                    <i class="fas fa-trash-alt"></i>
                                </a>


                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>