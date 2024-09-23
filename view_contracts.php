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

$conn = new mysqli($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
    SELECT contracts.contract_id, 
       COALESCE(coworkers.name, team.TeamName) AS coworker_name, 
       contracts.contract_details, 
       contracts.start_date, 
       contracts.end_date, 
       contracts.contract_copy
FROM contracts
LEFT JOIN coworkers ON contracts.coworker_id = coworkers.coworker_id
LEFT JOIN team ON contracts.TeamID = team.TeamID
ORDER BY contracts.contract_id DESC"; 
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>View Contracts</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .icon {
            cursor: pointer;
            width: 24px;
            height: 24px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons a {
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .action-buttons a.edit {
            background-color: #4CAF50;
        }
        .action-buttons a.delete {
            background-color: #f44336;
        }
        .contract-copy {
            display: none;
            max-width: 100%;
            max-height: 500px;
            margin-top: 10px;
        }
        .contract-copy img {
            max-width: 100%;
            max-height: 100%;
        }
        .contract-copy iframe {
            width: 100%;
            height: 500px;
            border: none;
        }
    </style>
    <script>
        function toggleFile(contractId, fileExt) {
            const fileContainer = document.getElementById('file-' + contractId);
            if (fileContainer.style.display === 'none' || fileContainer.style.display === '') {
                fileContainer.style.display = 'block';
                const fileFrame = document.getElementById('file-frame-' + contractId);
                fileFrame.src = 'serve_file.php?contract_id=' + contractId + '&file_ext=' + fileExt;
            } else {
                fileContainer.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="table-container">
        <h2>Contracts</h2>
        <table>
            <thead>
                <tr>
                 
                    <th>Coworker Name</th>
                    <th>Contract Details</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Contract Copy</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc() ) { ?>
                    <tr>
                    
                        <td><?= htmlspecialchars($row['coworker_name']) ?></td>
                        <td><?= htmlspecialchars($row['contract_details']) ?></td>
                        <td><?= htmlspecialchars($row['start_date']) ?></td>
                        <td><?= htmlspecialchars($row['end_date']) ?></td>
                        <td>
                            <?php 
                            if (!empty($row['contract_copy'])) {
                                $file_ext = strtolower(pathinfo($row['contract_copy'], PATHINFO_EXTENSION));
                                
                                if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                                    echo '<span class="icon" onclick="toggleFile(' . htmlspecialchars($row['contract_id']) . ', \'' . $file_ext . '\')">';
                                    echo $file_ext == 'pdf' ? '<i class="fas fa-file-pdf"></i>' : '<i class="fas fa-image"></i>';
                                    echo '</span>';
                                    echo '<div id="file-' . htmlspecialchars($row['contract_id']) . '" class="contract-copy">';
                                    echo '<iframe id="file-frame-' . htmlspecialchars($row['contract_id']) . '"></iframe>';
                                    echo '</div>';
                                } else {
                                    echo 'Unsupported file type';
                                }
                            } else {
                                echo 'No copy uploaded';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_contract.php?contract_id=<?= htmlspecialchars($row['contract_id']) ?>" class="btn">Edit</a>
                                <a href="delete_contract.php?contract_id=<?= htmlspecialchars($row['contract_id']) ?>" class="delete" onclick="return confirm('Are you sure you want to delete this contract?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>