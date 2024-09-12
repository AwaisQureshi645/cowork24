
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Coworker Data</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
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
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Coworker ID</th><th>Coworker Type</th><th>Name</th><th>Contact Info</th><th>Email</th><th>Office ID</th><th>Contract ID</th><th>Service ID</th><th>Created At</th><th>Updated At</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['coworker_id'] . "</td>";
        echo "<td>" . $row['coworker_type'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['contact_info'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['office_id'] . "</td>";
        echo "<td>" . $row['contract_id'] . "</td>";
        echo "<td>" . $row['service_id'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['updated_at'] . "</td>";
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
