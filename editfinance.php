<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "coworker");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$booking_id = $_GET['id'] ?? '';
$booking = null;

if ($booking_id) {
    // Fetch booking data
    $sql = "SELECT * FROM office_bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update booking data
    $rent_amount = $_POST['rent_amount'];
    $rent_status = $_POST['rent_status'];
    $rent_payment_date = $_POST['rent_payment_date'];
    $security_deposit_amount = $_POST['security_deposit_amount'];
    $security_deposit_status = $_POST['security_deposit_status'];
    $security_deposit_payment_date = $_POST['security_deposit_payment_date'];

    $update_sql = "UPDATE office_bookings SET rent_amount = ?, rent_status = ?, rent_payment_date = ?, security_deposit_amount = ?, security_deposit_status = ?, security_deposit_payment_date = ? WHERE booking_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("issdssi", $rent_amount, $rent_status, $rent_payment_date, $security_deposit_amount, $security_deposit_status, $security_deposit_payment_date, $booking_id);

    if ($update_stmt->execute()) {
        header("Location: financedisplay.php");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #007bff, #00d4ff);
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .container {
            width: 400px;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            border: 1px solid #ddd;
            max-height: 90vh;
            overflow-y: auto;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            color: #555;
            font-weight: 600;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            padding: 12px;
            color: #fff;
            background: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        button:hover {
            background: #0056b3;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
        }
        .message.error {
            color: #d9534f;
        }
        .message.success {
            color: #5bc0de;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Booking</h1>
        <?php if ($booking): ?>
            <form method="POST">
                <label for="rent_amount">Rent Amount:</label>
                <input type="number" id="rent_amount" name="rent_amount" value="<?php echo htmlspecialchars($booking['rent_amount']); ?>" required>
                
                <label for="rent_status">Rent Status:</label>
                <select id="rent_status" name="rent_status" required>
                    <option value="Pending" <?php echo ($booking['rent_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Paid" <?php echo ($booking['rent_status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                </select>
                
                <label for="rent_payment_date">Rent Payment Date:</label>
                <input type="date" id="rent_payment_date" name="rent_payment_date" onclick="this.showPicker();" value="<?php echo htmlspecialchars($booking['rent_payment_date']); ?>">

                <label for="security_deposit_amount">Security Deposit Amount:</label>
                <input type="number" id="security_deposit_amount" name="security_deposit_amount" value="<?php echo htmlspecialchars($booking['security_deposit_amount']); ?>" required>
                
                <label for="security_deposit_status">Security Deposit Status:</label>
                <select id="security_deposit_status" name="security_deposit_status" required>
                    <option value="Pending" <?php echo ($booking['security_deposit_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Paid" <?php echo ($booking['security_deposit_status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                </select>
                
                <label for="security_deposit_payment_date">Security Deposit Payment Date:</label>
                <input type="date" id="security_deposit_payment_date" onclick="this.showPicker();" name="security_deposit_payment_date" value="<?php echo htmlspecialchars($booking['security_deposit_payment_date']); ?>">
                
                <button type="submit">Update</button>
            </form>
        <?php else: ?>
            <p class="message error">Booking not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
