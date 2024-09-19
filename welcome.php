<?php
session_start();
function getTotalSeats()
{
    // Database connection
    $conn = new mysqli("localhost", "root", "", "coworker");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT COUNT(*) as total FROM seats";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    $conn->close();
    return $row['total'];
}

function getSeatsByBranch($branch_id)
{
    // Database connection
    $conn = new mysqli("localhost", "root", "", "coworker");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT COUNT(*) as total FROM seats WHERE branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();










    $conn->close();
    return $row['total'];
}
function getPendingPayments()
{
    // Database connection
    $conn = new mysqli("localhost", "root", "", "coworker");

    // Check connection
    if ($conn->connect_error) {
        echo "<script>alert('Connection failed: " . $conn->connect_error . "');</script>";
        return; // Stop execution if the connection fails
    }

    try {
        $sql = "SELECT b.booking_id, t.TeamName, b.office_id, b.contract_id, b.booking_date, b.rent_amount, b.rent_status, b.rent_payment_date, b.security_deposit_amount, b.security_deposit_status, b.security_deposit_payment_date 
                FROM office_bookings b
                JOIN team t ON b.team_id = t.TeamID";

        $result_pending = $conn->query($sql);

        if (!$result_pending) {
            throw new Exception("Query failed: " . $conn->error);
        }

        $results = [];

        while ($row = $result_pending->fetch_assoc()) {
            $results[] = [
                'Booking ID' => $row['booking_id'],
                'Team Name' => $row['TeamName'],
                'Rent Status' => $row['rent_status'],
                'Security Deposit Status' => $row['security_deposit_status']
            ];
        }

        if (empty($results)) {
            echo "<script>alert('No records found');</script>";
        }

        $conn->close();
        return $results;
    } catch (Exception $e) {
        echo "<script>alert('An error occurred: " . $e->getMessage() . "');</script>";
        return [];
    }
}



function getAvailableSeats()
{
    // Database connection
    $conn = new mysqli("localhost", "root", "", "coworker");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT COUNT(*) as total FROM seats WHERE status = 'available'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    $conn->close();
    return $row['total'];
}

function getOccupiedSeats()
{
    // Database connection
    $conn = new mysqli("localhost", "root", "", "coworker");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT COUNT(*) as total FROM seats WHERE status = 'occupied'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    $conn->close();
    return $row['total'];
}
function getBranchSeatData()
{
    // Database connection
    $conn = new mysqli("localhost", "root", "", "coworker");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "
        SELECT 
            b.branch_id,
            b.branch_name,
            COUNT(s.seat_id) AS total_seats,
            SUM(CASE WHEN s.status = 'available' THEN 1 ELSE 0 END) AS available_seats,
            SUM(CASE WHEN s.status = 'occupied' THEN 1 ELSE 0 END) AS occupied_seats
        FROM 
            seats s
        JOIN 
            branches b ON s.branch_id = b.branch_id
        GROUP BY 
            b.branch_id, b.branch_name
    ";

    $result = $conn->query($sql);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $conn->close();
    return $data;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Dashboard</title>
    <link rel="stylesheet" href="./style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #01899f 0%, #01abc6 100%);
            color: #333;
            border: none;
        }

        .container {
            display: grid;
    grid-template-columns: 2fr 1fr; /* Two columns: first one is twice the size of the second */
    gap: 20px;
    padding: 20px;
        }

        .widget,
        .announcement,
        .calendar {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            box-sizing: border-box;
        }

        .widget h2,
        .announcement h2,
        .calendar h2 {
            margin-top: 0;
            font-size: 18px;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .widget p,
        .announcement p {
            font-size: 24px;
            margin: 10px 0;
            color: #666;
        }

        .widget .chart {
            width: 100%;
            height: 200px;
        }

        .announcement {
            grid-column: span 2;
        }

        .calendar {
            grid-column: span 2;
        }

        .calendar iframe {
            width: 100%;
            height: 400px;
            border: none;
            border-radius: 8px;
        }

        .widget button {
            width: 100%;
            padding: 15px;
            border: none;
            background-color: #ff8905;
            color: white;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .widget button:hover {
            background-color: #e64a19;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .widget button:focus {
            outline: none;
            box-shadow: 0px 0px 0px 4px rgba(255, 87, 34, 0.5);
        }

        .inventory-options {
            display: none;
            margin-top: 10px;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
            color: #333;
        }

        .inventory-options button {
            display: block;
            margin: 5px 0;
            padding: 10px;
            border: none;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .inventory-options button:hover {
            background-color: #45a049;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .inventory-options button:focus {
            outline: none;
            box-shadow: 0px 0px 0px 4px rgba(76, 175, 80, 0.5);
        }

        .full-screen {
            width: 100%;
            /* height: 100vh; */
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            padding: 20px;
            box-sizing: border-box;
            display: none;
        }

        .stats {
            display: flex;
            justify-content: space-around;
        }

        .stat {
            background-color: #ffffff;
            border: 1px solid #3498db;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 23%;
            text-align: center;
        }

        .stat.available {
            background-color: #d4edda;
        }

        .stat.occupied {
            background-color: #f8d7da;
        }
        th{
    background-color: #464646 !important;
    color: white;
  padding: 3px !important;


}
    
    </style>
</head>

<body>

    <div class="container" id="dashboardContainer">
        <div class="widget">
            <h2>Seat Statistics Per Branch</h2>
            <table>
                <thead>
                    <tr>
                        <th>Branch Name</th>
                        <th>Total Seats</th>
                        <th>Available Seats</th>
                        <th>Occupied Seats</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $branchData = getBranchSeatData();
                    foreach ($branchData as $branch) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($branch['branch_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($branch['total_seats']) . "</td>";
                        echo "<td class='stat available'>" . htmlspecialchars($branch['available_seats']) . "</td>";
                        echo "<td class='stat occupied'>" . htmlspecialchars($branch['occupied_seats']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <!-- <div class="widget">
            <h2>Active Bookings</h2>
            <p><?php echo getActiveBookings(); ?></p>
        </div> -->

        <!-- testing -->


        <div class="widget" >
            <h2>Total Employees</h2>
            <p><?php echo getTotalEmployees(); ?></p>

            <h2>Active Bookings</h2>
            <p><?php echo getActiveBookings(); ?></p>




        </div>




        
        <div class="widget calendar">
            <h2>Upcoming Events</h2>
            <iframe src="https://calendar.google.com/calendar/embed?src=cowork24management%40gmail.com&ctz=Asia/Karachi" frameborder="0"></iframe>
        </div>



        <div class="widget">

            <button onclick="toggleInventoryOptions()">Inventory of cowork-24</button>
            <?php
            // Database connection
            $conn = new mysqli("localhost", "root", "", "coworker");

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Fetch data with filtering for pending statuses
            $sql = "SELECT b.booking_id, t.TeamName, b.office_id, b.contract_id, b.booking_date, b.rent_amount, b.rent_status, b.rent_payment_date, b.security_deposit_amount, b.security_deposit_status, b.security_deposit_payment_date 
        FROM office_bookings b
        JOIN team t ON b.team_id = t.TeamID
        WHERE b.rent_status = 'pending' OR b.security_deposit_status = 'pending'";
            $result = $conn->query($sql);

            // Table structure
            echo "<table border='1' cellpadding='10'>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Team Name</th>
              
                <th>Security Deposit Status</th>
            </tr>
        </thead>
        <tbody>";

            // Display data
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Add a 'pending' class if the rent or security deposit status is 'Pending'
                    $depositStatusClass = ($row['security_deposit_status'] === 'pending') ? 'pending' : '';

                    echo "<tr>
                <td>{$row['booking_id']}</td>
                <td>{$row['TeamName']}</td>
              
                <td class='$depositStatusClass'>{$row['security_deposit_status']}</td>
            </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No records found</td></tr>";
            }

            echo "</tbody></table>";

            // Close connection
            $conn->close();
            ?>








            <!-- show pending results -->








            <div class="inventory-options" id="inventoryOptions">
                <button onclick="loadContent('select_branch.php')">View Inventory</button>
                <button onclick="loadContent('add_inventory.php')">Add Inventory</button>
            </div>
            <div class="inventory-content" id="inventoryContent"></div>
        </div>


        <div class="widget">
            <h2>Booking Trends</h2>
            <canvas id="bookingTrendsChart" class="chart"></canvas>
        </div>
    </div>

    <div class="full-screen" id="fullScreenContent"></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleInventoryOptions() {
            const options = document.getElementById('inventoryOptions');
            options.style.display = options.style.display === 'none' ? 'block' : 'none';
        }

        function loadContent(url) {
            fetch(url)
                .then(response => response.text())
                .then(data => {
                    const dashboardContainer = document.getElementById('dashboardContainer');
                    const fullScreenContent = document.getElementById('fullScreenContent');
                    dashboardContainer.style.display = 'none';
                    fullScreenContent.innerHTML = data;
                    fullScreenContent.style.display = 'flex';
                })
                .catch(error => console.error('Error loading content:', error));
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetch('booking_trends.php')
                .then(response => response.json())
                .then(data => {
                    const labels = data.map(item => item.month);
                    const counts = data.map(item => item.count);

                    const ctx = document.getElementById('bookingTrendsChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Booking Trends',
                                data: counts,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                fill: true,
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    beginAtZero: true
                                },
                                y: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.raw;
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching booking trends data:', error));
        });
    </script>

    <?php

    function getTotalEmployees()
    {
        $conn = new mysqli('localhost', 'root', '', 'coworker');
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
        }

        $sql = "SELECT COUNT(*) AS total FROM coworkusers";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'];
        } else {
            return '0';
        }

        $conn->close();
    }

    function getActiveBookings()
    {
        $conn = new mysqli('localhost', 'root', '', 'coworker');
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
        }

        $sql = "SELECT COUNT(*) AS total FROM bookings WHERE end_time > NOW()";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'];
        } else {
            return '0';
        }

        $conn->close();
    }
    ?>

</body>

</html>