<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Records</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f4f7f9;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 20px auto;
}

h1 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
    font-size: 2rem;
}

.booking-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.booking-table th, .booking-table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

.booking-table thead {
    background-color: #007bff;
    color: #fff;
}

.booking-table tbody tr:nth-child(even) {
    background-color: #f2f2f2;
}

.booking-table tbody tr:hover {
    background-color: #e6f7ff;
}

.btn {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 4px;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
    border-radius: 4px;
    color: #fff;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn.edit {
    background-color: #28a745;
}

.btn.edit:hover {
    background-color: #218838;
}

.btn.copy {
    background-color: #ffc107;
}

.btn.copy:hover {
    background-color: #e0a800;
}

.btn.delete {
    background-color: #dc3545;
}

.btn.delete:hover {
    background-color: #c82333;
}

        </style>
</head>
<body>
    <div class="container">
        <h1>Booking Records</h1>
        <table class="booking-table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Team Name</th>
                    <th>Office (Branch)</th>
                    <th>Contract ID</th>
                    <th>Booking Date</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php
                // Database connection
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "coworker"; // Replace with your actual database name

                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // SQL query to fetch data from office_bookings and join with teams and branches
                $sql = "SELECT ob.booking_id, t.TeamName, o.OfficeID, b.branch_name, ob.contract_id, ob.booking_date 
                        FROM office_bookings ob
                        JOIN team t ON ob.team_id = t.TeamID
                        JOIN office o ON ob.office_id = o.OfficeID
                        JOIN branches b ON o.branch_id = b.branch_id";

                $result = $conn->query($sql);

                // Check if any rows are returned
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['booking_id']}</td>
                                <td>{$row['TeamName']}</td>
                                <td>{$row['OfficeID']} ({$row['branch_name']})</td>
                                <td>{$row['contract_id']}</td>
                                <td>{$row['booking_date']}</td>
                                
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No records found</td></tr>";
                }

                // Close connection
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
