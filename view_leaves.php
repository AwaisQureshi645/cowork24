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

// Create connection
$conn = new mysqli($host, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch leave records with employee names
$query = "
    SELECT lr.*, e.username 
    FROM leave_records lr
    JOIN coworkusers e ON lr.employeeID = e.id
";
$result = $conn->query($query);

if (!$result) {
    die("Error retrieving leave records: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Leave Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298); /* Blue gradient background */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            position: relative;
        }

        h1 {
            text-align: center;
            color: #fff;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            margin: 0 auto;
            border-collapse: collapse;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            color: #333;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #007bff; /* Blue header background */
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        a:hover {
            color: #0056b3;
        }

        .actions a {
            margin: 0 5px;
        }

        .actions a.edit {
            color: #17a2b8; /* Light blue */
        }

        .actions a.delete {
            color: #dc3545; /* Red */
        }

        .add-leave-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        @media screen and (max-width: 768px) {
            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="add-leave-btn">
            <a class="btn" href="/cowork/add_leave.php" role="button">Apply for Leave</a>
        </div>
        <h1>View Leave Requests</h1>
        <table>
            <tr>
                <th>Employee Name</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Days</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                <td><?php echo htmlspecialchars($row['leave_start_date']); ?></td>
                <td><?php echo htmlspecialchars($row['leave_end_date']); ?></td>
                <td><?php echo htmlspecialchars($row['total_days']); ?></td>
                <td><?php echo htmlspecialchars($row['leave_status']); ?></td>
                <td class="actions">
                    <a href="update_leave.php?id=<?php echo $row['leaveID']; ?>" class="edit">Edit</a>
                    <a href="delete_leave.php?id=<?php echo $row['leaveID']; ?>" class="delete">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
