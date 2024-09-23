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
if (
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'head' &&
        $_SESSION['role'] !== 'financehead' &&
        $_SESSION['role'] !== 'floorHost')
) {
    header('Location: access_denied.php');
    exit();
}


$results_per_page = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

$sql = "
    SELECT * from tickets
    LIMIT $start_from, $results_per_page
";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$total_results_sql = "SELECT COUNT(*) FROM tickets";
$total_results_result = $conn->query($total_results_sql);
$total_results_row = $total_results_result->fetch_row();
$total_results = $total_results_row[0];
$total_pages = ceil($total_results / $results_per_page);


if (isset($_GET['delete_id'])) {
    $ticket_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM tickets WHERE ticket_id = $ticket_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "Ticket deleted successfully.";
        // Optionally, you can redirect to the same page to refresh the table
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="cowork-logo.PNG">
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin-top: -4rem !important;
        }

        .dashboard {
            width: 100%;
            max-width: 1700px;
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
            overflow-x: auto;
            height: 85%;
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

        th,
        td {
            padding: 1px 7px;
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
            background-color: #007bff !important;
        }

        .btn-danger {
            background-color: #dc3545 !important;
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

        .icbtn {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            border: none;
        }

        .btn:hover {
            color: white;
        }
    </style>

</head>

<body>
    <div class="dashboard">

        <h2>Tickets Information</h2>

        <div id="TicketTable">
            <table>
                <thead>
                    <tr>
                       
                        <th>subject</th>

                        <th>created_by</th>
                        <th>created_at</th>
                        <th>priorty</th>
                        <th>closeup date</th>
                        <th>description</th>
                        <th>Status</th>
                        <th>Action</th> <!-- New Action Column -->

                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {

                        echo "<tr>
     
        <td>{$row['subject']}</td>
        <td>{$row['created_by']}</td>
        <td>{$row['created_at']}</td>
        <td>{$row['priority']}</td>
        <td>{$row['closeup_date']}</td>
        <td>{$row['description']}</td>
        <td class='status_cart' style='background-color: " . ($row['status'] === 'Pending' ? '#ffcccc' : 'white') . ";'>
            {$row['status']}
        </td>
        <td class='icbtn'>
            <a href='editTicket.php?id={$row['ticket_id']}' class='btn'>Edit</a>
            <a href='?delete_id={$row['ticket_id']}' class='btn' onclick='return confirm(\"Are you sure you want to delete this ticket?\");'>Delete</a>
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