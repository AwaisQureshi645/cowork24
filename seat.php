<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'coworker'); // Update with your DB credentials

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle seat data fetching
if (isset($_GET['action']) && $_GET['action'] == 'fetch_seats') {
    $branch_id = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : 0;
    $query = "SELECT s.seat_id, s.seat_number, s.status, b.branch_name, 
                     c.name AS occupant_name
              FROM seats s
              JOIN branches b ON s.branch_id = b.branch_id
              LEFT JOIN coworkers c ON s.coworker_id = c.coworker_id";
    if ($branch_id) {
        $query .= " WHERE s.branch_id = $branch_id";
    }
    $result = $conn->query($query);
    $seats = $result->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($seats);
    exit();
}

// Handle coworker data fetching
if (isset($_GET['action']) && $_GET['action'] == 'fetch_coworkers') {
    $result = $conn->query("SELECT coworker_id, name FROM coworkers");
    $coworkers = $result->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($coworkers);
    exit();
}

// Handle branch data fetching
if (isset($_GET['action']) && $_GET['action'] == 'fetch_branches') {
    $result = $conn->query("SELECT branch_id, branch_name FROM branches");
    $branches = $result->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($branches);
    exit();
}

// Handle seat update
if (isset($_POST['action']) && $_POST['action'] == 'update_seat') {
    $seat_id = $_POST['seat_id'];
    $new_status = $_POST['status'];

    $coworker_id = isset($_POST['coworker_id']) && $_POST['coworker_id'] !== '' ? $_POST['coworker_id'] : null;

    if ($coworker_id === null) {
        $stmt = $conn->prepare("UPDATE seats SET status = ?, coworker_id = NULL WHERE seat_id = ?");
        $stmt->bind_param("si", $new_status, $seat_id);
    } else {
        $stmt = $conn->prepare("UPDATE seats SET status = ?, coworker_id = ? WHERE seat_id = ?");
        $stmt->bind_param("sii", $new_status, $coworker_id, $seat_id);
    }
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit();
}

// Handle seat addition
if (isset($_POST['action']) && $_POST['action'] == 'add_seat') {
    $seat_number = $_POST['seat_number'];
    $branch_id = $_POST['branch_id'];

    if (empty($seat_number) || empty($branch_id)) {
        echo json_encode(['error' => 'Invalid input']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO seats (seat_number, branch_id, status) VALUES (?, ?, 'available')");
    $stmt->bind_param("si", $seat_number, $branch_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to add seat']);
    }
    exit();
}
// Handle seat deletion
if (isset($_POST['action']) && $_POST['action'] == 'delete_seat') {
    $seat_id = $_POST['seat_id'];

    $stmt = $conn->prepare("DELETE FROM seats WHERE seat_id = ?");
    $stmt->bind_param("i", $seat_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete seat']);
    }
    exit();
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Seating Management</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background-color: #eef1f5;
            color: #333;
            transition: background-color 0.3s;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
            color: #464646;
        }

        .controls {
            text-align: center;
            margin-bottom: 20px;
        }

        .controls label {
            margin-right: 10px;
            font-size: 1.2em;
        }

        .branch-block {
            margin: 20px auto;
            padding: 10px;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 900px;
        }

        .branch-title {
            font-size: 1.5em;
            color: #4a90e2;
            margin-bottom: 10px;
            text-align: center;
        }

        .seating-chart {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .seat {
            width: 70px;
            height: 70px;
            margin: 5px;
            border: 2px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .seat:hover {
            transform: scale(1.05);
            border-color: #4a90e2;
        }

        .available {
            background-color: #28a745;
            /* Green */
            color: white;
        }

        .occupied {
            background-color: #dc3545;
            /* Red */
            color: white;
        }

        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1000;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.5);
            /* Black w/ opacity */
        }

        /* Modal Content */
        .modal-content {
            background-color: #ffffff;
            margin: 15% auto;
            /* 15% from the top and centered */
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            /* Could be more or less depending on screen size */
            max-width: 600px;
            /* Maximum width */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        /* Close Button */
        .close {
            color: #333;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #007bff;
            text-decoration: none;
            cursor: pointer;
        }

        /* Modal Header */
        .modal-content h2 {
            margin-top: 0;
            color: #333;
        }

        /* Modal Body */
        .modal-content p {
            margin: 15px 0;
            color: #555;
        }

        .modal-content label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }

        .modal-content select,
        .modal-content button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .modal-content button {
            background-color: #007bff;
            border: none;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 5px;
        }

        .modal-content button:hover {
            background-color: #0056b3;
        }

        .modal-content .btn-danger {
            background-color: #dc3545;
        }

        .modal-content .btn-danger:hover {
            background-color: #c82333;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                margin: 10% auto;
            }
        }

        .form-container {
            width: 100%;
            max-width: 600px;
            padding-left: 300px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin: 20px;
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .form-container label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }

        .form-container input,
        .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-container button:hover {
            background-color: #0056b3;
        }

        /* Add this to your CSS file */
        .seat {
            position: relative;
            padding: 10px;
            border: 1px solid #ccc;
            margin: 5px;
            display: inline-block;
            width: 100px;
            text-align: center;
            cursor: pointer;
        }

        .delete-btn {
            position: absolute;
            top: 0;
            right: 0;

            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: white;
        }
#fetchSeats{
    font-size: 16px;
}


   
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #ffffff;
    
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    }

    .close {
        color: #333;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: #007bff;
        text-decoration: none;
    }
#seatNumber{
    padding: 8px;
    width: 95%;
}


    </style>
</head>

<body>
    <h1>Office Seating Management</h1>
    <div class="controls">
        <label for="branchSelect">Select Branch:</label>
        <select id="branchSelect">
            <option value="">--All--</option>
        </select>
        <button id="fetchSeats">Load Seats</button>
        <button id="openModal" class="btn btn-primary">Add New Seat</button>
    </div>
    
    <div id="seatingCharts"></div>

    <!-- Modal for Assigning/Removing Coworkers -->
    <div id="seatModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Seat Management</h2>
            <p id="modalSeatInfo"></p>
            <label for="coworkerSelect">Assign Coworker:</label>
            <select id="coworkerSelect"></select>
            <button id="assignSeat">Assign</button>
            <button id="removeCoworker">Make Available</button>
        </div>
    </div>
    <!-- Form for Adding New Seats -->
  <!-- Add New Seat Modal -->
<div id="addSeatModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddSeatModal">&times;</span>
        <h2>Add New Seat</h2>
        <form id="addSeatForm">
            <label for="seatNumber">Seat Number:</label>
            <input type="text" id="seatNumber" name="seat_number" required>

            <label for="branchSelectAdd">Select Branch:</label>
            <select id="branchSelectAdd" name="branch_id" required>
                <!-- Options will be populated dynamically -->
            </select>

            <button type="submit">Add Seat</button>
        </form>
    </div>
</div>
<script>
    // Get the modal, button, and close elements
    const addSeatModal = document.getElementById('addSeatModal');
    const openModalBtn = document.getElementById('openModal');
    const closeAddSeatModal = document.getElementById('closeAddSeatModal');

    // Show the modal when the button is clicked
    openModalBtn.addEventListener('click', function() {
        addSeatModal.style.display = 'block';
    });

    // Hide the modal when the close button is clicked
    closeAddSeatModal.addEventListener('click', function() {
        addSeatModal.style.display = 'none';
    });

    // Hide the modal when the user clicks outside of the modal content
    window.addEventListener('click', function(event) {
        if (event.target == addSeatModal) {
            addSeatModal.style.display = 'none';
        }
    });
</script>


    <script>
        document.getElementById('fetchSeats').addEventListener('click', fetchSeats);
        const modal = document.getElementById('seatModal');
        const closeModal = document.getElementById('closeModal');
        const coworkerSelect = document.getElementById('coworkerSelect');
        const assignSeatBtn = document.getElementById('assignSeat');
        const removeCoworkerBtn = document.getElementById('removeCoworker');
        const branchSelect = document.getElementById('branchSelect');
        let currentSeatId;

        function fetchSeats() {
            const branchId = branchSelect.value;
            fetch(`seat.php?action=fetch_seats${branchId ? `&branch_id=${branchId}` : ''}`)
                .then(response => response.json())
                .then(seats => {
                    const seatingCharts = document.getElementById('seatingCharts');
                    seatingCharts.innerHTML = ''; // Clear existing content

                    let currentBranch = '';

                    seats.forEach(seat => {
                        if (seat.branch_name !== currentBranch) {
                            currentBranch = seat.branch_name;
                            const branchBlock = document.createElement('div');
                            branchBlock.className = 'branch-block';

                            const branchTitle = document.createElement('div');
                            branchTitle.className = 'branch-title';
                            branchTitle.textContent = currentBranch;
                            branchBlock.appendChild(branchTitle);

                            const seatingChart = document.createElement('div');
                            seatingChart.className = 'seating-chart';

                            seats.forEach(s => {
                                if (s.branch_name === currentBranch) {
                                    const seatDiv = document.createElement('div');
                                    seatDiv.className = `seat ${s.status}`;
                                    seatDiv.textContent = s.seat_number;

                                    // Create the delete button
                                    const deleteBtn = document.createElement('button');
                                    deleteBtn.textContent = '❌'; // Unicode cross mark for delete
                                    deleteBtn.className = 'delete-btn';
                                    deleteBtn.title = 'Delete Seat'; // Tooltip for the delete button
                                    deleteBtn.addEventListener('click', () => {
                                        deleteSeat(s.seat_id);
                                    });

                                    seatDiv.appendChild(deleteBtn);
                                    seatDiv.addEventListener('click', () => {
                                        currentSeatId = s.seat_id;
                                        document.getElementById('modalSeatInfo').textContent =
                                            `Seat: ${s.seat_number} - Current Occupant: ${s.occupant_name || 'None'}`;
                                        modal.style.display = 'block';
                                    });
                                    seatingChart.appendChild(seatDiv);
                                }
                            });

                            branchBlock.appendChild(seatingChart);
                            seatingCharts.appendChild(branchBlock);
                        }
                    });
                    loadCoworkers(); // Ensure coworker list is updated
                })
                .catch(error => {
                    console.error('Error fetching seats:', error);
                    alert('Failed to fetch seats. Please try again.');
                });
        }


        function deleteSeat(seatId) {
            if (!confirm('Are you sure you want to delete this seat?')) {
                return;
            }

            fetch('seat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=delete_seat&seat_id=${seatId}`
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        fetchSeats(); // Refresh the seats to reflect deletion
                    } else {
                        alert('Error deleting seat: ' + result.error);
                    }
                })
                .catch(error => {
                    console.error('Error deleting seat:', error);
                    alert('Failed to delete seat. Please try again.');
                });
        }


        function loadCoworkers() {
            fetch('seat.php?action=fetch_coworkers')
                .then(response => response.json())
                .then(coworkers => {
                    coworkerSelect.innerHTML = '';
                    coworkers.forEach(coworker => {
                        const option = document.createElement('option');
                        option.value = coworker.coworker_id;
                        option.textContent = coworker.name;
                        coworkerSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching coworkers:', error);
                    alert('Failed to fetch coworkers. Please try again.');
                });
        }

        assignSeatBtn.addEventListener('click', () => {
            const coworkerId = coworkerSelect.value;
            updateSeatStatus('occupied', coworkerId);
        });

        removeCoworkerBtn.addEventListener('click', () => {
            updateSeatStatus('available', '');
        });

        function updateSeatStatus(status, coworkerId) {
            fetch('seat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=update_seat&seat_id=${currentSeatId}&status=${status}&coworker_id=${coworkerId}`
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        fetchSeats(); // Refresh the seats to reflect changes
                        modal.style.display = 'none';
                    } else {
                        alert('Error updating seat: ' + result.error);
                    }
                })
                .catch(error => {
                    console.error('Error updating seat:', error);
                    alert('Failed to update seat. Please try again.');
                });
        }


        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        document.addEventListener('DOMContentLoaded', fetchSeats);

        function loadBranches() {
            fetch('seat.php?action=fetch_branches')
                .then(response => response.json())
                .then(branches => {
                    branchSelect.innerHTML = '<option value="">All Branches</option>';
                    branches.forEach(branch => {
                        const option = document.createElement('option');
                        option.value = branch.branch_id;
                        option.textContent = branch.branch_name;
                        branchSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching branches:', error));
        }
        document.getElementById('addSeatForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const seatNumber = document.getElementById('seatNumber').value;
            const branchId = document.getElementById('branchSelectAdd').value;

            console.log('Adding seat:', {
                seatNumber,
                branchId
            });

            fetch('seat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=add_seat&seat_number=${encodeURIComponent(seatNumber)}&branch_id=${encodeURIComponent(branchId)}`
                })
                .then(response => response.json())
                .then(result => {
                    console.log('Add seat response:', result);
                    if (result.success) {
                        alert('Seat added successfully');
                        fetchSeats(); // Refresh the seats to show the new addition
                        document.getElementById('addSeatForm').reset(); // Clear the form
                    } else {
                        alert('Error adding seat: ' + result.error);
                    }
                })
                .catch(error => console.error('Error adding seat:', error));
        });



        // Populate branch select options for adding new seats
        function loadBranchesForAdd() {
            fetch('seat.php?action=fetch_branches')
                .then(response => response.json())
                .then(branches => {
                    const branchSelectAdd = document.getElementById('branchSelectAdd');
                    branchSelectAdd.innerHTML = '<option value="">--Select Branch--</option>'; // Clear and add default option
                    branches.forEach(branch => {
                        const option = document.createElement('option');
                        option.value = branch.branch_id;
                        option.textContent = branch.branch_name;
                        branchSelectAdd.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching branches for add:', error));
        }

        window.onload = () => {
            loadBranches();
            loadBranchesForAdd(); // Load branches for the add seat form
            fetchSeats(); // Initial fetch of seats
        };
    </script>
</body>

</html>