<?php
session_start();

require_once 'config.php';

$sql = "SELECT * FROM edit_history";
$result = mysqli_query($mysqli, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit File History</title>
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="admin_index.php">
                <h1>DnD Libraries - Edit History</h1>
            </a>
        </div>
    </div>
    <h1>EDIT FILE HISTORY</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>File ID</th>
            <th>User ID</th>
            <th>Date of Modification</th>
            <th>Status</th>
        </tr>
        <?php
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['file_id']}</td>";
            echo "<td>{$row['user_id']}</td>";
            echo "<td>{$row['date_of_modification']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>
