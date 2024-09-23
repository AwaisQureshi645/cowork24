<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Coworker Data</title>
    <link rel="stylesheet" href="style.css">

    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            /* border: 1px solid black; */
        }
        th, td {
            padding: 7px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .action-icons {
            display: flex;
            gap: 10px;
        }
        .action-icons a {
            text-decoration: none;
            color: black;
        }
        .icbtn {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

<?php
$host = 'localhost';
$dbname = 'coworker';
$username_db = 'root';
$password_db = '';

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete functionality
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM coworkers WHERE coworker_id = $delete_id";
    if ($conn->query($delete_sql) === TRUE) {
        // echo "Record deleted successfully.";
    } else {
        // echo "Error deleting record: " . $conn->error;
    }
}

// Display coworker data
$sql = "
    SELECT 
        c.coworker_id, 
        c.coworker_type, 
        c.name, 
        c.contact_info, 
        c.email, 
        c.office_id, 
        ct.contract_id, 
        c.service_id, 
        c.created_at, 
        c.updated_at
    FROM coworkers c
    LEFT JOIN contracts ct ON c.contract_id = ct.contract_id
    ORDER BY c.created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Coworker Type</th><th>Name</th><th>Contact Info</th><th>Email</th><th>Created At</th><th>Updated At</th><th>Action</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
    
        echo "<td>" . $row['coworker_type'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['contact_info'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
      
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['updated_at'] . "</td>";
        echo "<td class='icbtn'>
                      
                <a href='editCoworker.php?id=" . $row['coworker_id'] . "' title='Edit' ' class='btn btn-primary'>Edit</a>
                <a href='?delete_id=" . $row['coworker_id'] .  "' title='Delete' ' class='btn btn-primary' onclick='return confirm(\"Are you sure you want to delete this coworker?\");'>Delete</a>
              </td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No coworker data found.";
}

$conn->close();
?>

</body>
</html>
