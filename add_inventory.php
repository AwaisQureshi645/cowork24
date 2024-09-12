<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="cowork-logo.PNG">
    <title>Add Inventory</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f0f0;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        label {
            font-size: 16px;
            margin-bottom: 10px;
            display: block;
            color: #333;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        select {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        .success {
            color: green;
            margin-top: 10px;
        }

        .error {
            color: red;
            margin-top: 10px;
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
    </style>
</head>

<body>
    <div class="form-container">
        <a href="logout.php" class="logout-button">Logout</a>
        <h2>Add Inventory Item</h2>
        <?php
        session_start();
        $conn = new mysqli('localhost', 'root', '', 'coworker');
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
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


        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $itemName = $_POST['item_name'];
            $categoryId = $_POST['category_id'];
            $branchId = $_POST['branch_id'];
            $quantity = $_POST['quantity'];
            $itemCondition = $_POST['item_condition'];

            $sql = "INSERT INTO items (item_name, category_id, branch_id, quantity, item_condition) 
                    VALUES ('$itemName', '$categoryId', '$branchId', '$quantity', '$itemCondition')";
            if ($conn->query($sql) === TRUE) {
                echo "<p class='success'>Inventory item added successfully!</p>";
                header("Location: welcome.php");
                exit();
            } else {
                echo "<p class='error'>Error: " . $sql . "<br>" . $conn->error . "</p>";
            }
        }


        $branchQuery = "SELECT branch_id, branch_name FROM branches";
        $branchResult = $conn->query($branchQuery);


        $categoryQuery = "SELECT category_id, category_name FROM categories";
        $categoryResult = $conn->query($categoryQuery);
        ?>

        <form method="post" action="add_inventory.php">
            <label for="item_name">Item Name</label>
            <input type="text" id="item_name" name="item_name" required>

            <label for="category_id">Category Name</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php
                if ($categoryResult->num_rows > 0) {
                    while ($row = $categoryResult->fetch_assoc()) {
                        echo "<option value='" . $row['category_id'] . "'>" . $row['category_name'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No categories available</option>";
                }
                ?>
            </select>

            <label for="branch_id">Branch Name</label>
            <select id="branch_id" name="branch_id" required>
                <option value="">Select Branch</option>
                <?php
                if ($branchResult->num_rows > 0) {
                    while ($row = $branchResult->fetch_assoc()) {
                        echo "<option value='" . $row['branch_id'] . "'>" . $row['branch_name'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No branches available</option>";
                }
                ?>
            </select>

            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" required>

            <label for="item_condition">Item Condition</label>
            <select id="item_condition" name="item_condition" required>
                <option value="good">Good</option>
                <option value="damaged">Damaged</option>
                <option value="need replacement">Need Replacement</option>
            </select>

            <button type="submit">Add Inventory</button>

        </form>
    </div>
</body>

</html>