<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        td {
            background-color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            text-decoration: none;
            color: white;
            border-radius: 4px;
            margin: 2px;
        }

        .edit-btn {
            background-color: #2196F3;
        }

        .delete-btn {
            background-color: #f44336;
        }

        .edit-btn:hover {
            background-color: #0b7dda;
        }

        .delete-btn:hover {
            background-color: #da190b;
        }

        /* Custom CSS for pending payments */
        .pending {
            background-color: #ffcccc; /* Light red background for pending payments */
            color: red; /* Red text for better visibility */
        }
    </style>
</head>
<body>
    <h1>Finance</h1>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Team Name</th>
                <th>Office ID</th>
                <th>Contract ID</th>
                <th>Booking Date</th>
                <th>Rent Amount</th>
                <th>Rent Status</th>
                <th>Rent Payment Date</th>
                <th>Security Deposit Amount</th>
                <th>Security Deposit Status</th>
                <th>Security Deposit Payment Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Database connection
            $conn = new mysqli("localhost", "root", "", "coworker");

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Fetch data
            $sql = "SELECT b.booking_id, t.TeamName, b.office_id, b.contract_id, b.booking_date, b.rent_amount, b.rent_status, b.rent_payment_date, b.security_deposit_amount, b.security_deposit_status, b.security_deposit_payment_date 
                    FROM office_bookings b
                    JOIN team t ON b.team_id = t.TeamID";
            $result = $conn->query($sql);

            // Display data
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Add a 'pending' class if the rent or security deposit status is 'Pending'
                    $rentStatusClass = ($row['rent_status'] === 'pending') ? 'pending' : '';
                    $depositStatusClass = ($row['security_deposit_status'] === 'pending') ? 'pending' : '';

                    echo "<tr>
                        <td>{$row['booking_id']}</td>
                        <td>{$row['TeamName']}</td>
                        <td>{$row['office_id']}</td>
                        <td>{$row['contract_id']}</td>
                        <td>{$row['booking_date']}</td>
                        <td>{$row['rent_amount']}</td>
                        <td class='$rentStatusClass'>{$row['rent_status']}</td>
                        <td>{$row['rent_payment_date']}</td>
                        <td>{$row['security_deposit_amount']}</td>
                        <td class='$depositStatusClass'>{$row['security_deposit_status']}</td>
                        <td>{$row['security_deposit_payment_date']}</td>
                        <td>
                            <a href='editfinance.php?id={$row['booking_id']}' class='btn edit-btn'>Edit</a>
                            <a href='deletefinance.php?id={$row['booking_id']}' class='btn delete-btn' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='12'>No records found</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>
