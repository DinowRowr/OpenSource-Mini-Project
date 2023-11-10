<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['view_approvals']) && $_POST['action'] === 'Approved') {
        $action = 'Approved';
    } elseif (isset($_POST['view_rejections']) && $_POST['action'] === 'Rejected') {
        $action = 'Rejected';
    } else {
        // Handle any other actions if needed
        $action = '';
    }
} else {
    // Handle the case when no action is provided
    $action = '';
}

function getNotificationsByAction($mysqli, $action) {
    $sql = "SELECT n.action, n.document_title, n.timestamp, n.message, u.firstName, u.lastName
            FROM notifications n
            LEFT JOIN users u ON n.user_id = u.user_id
            WHERE n.action = '$action'
            ORDER BY n.timestamp DESC";
    $result = mysqli_query($mysqli, $sql);
    $notifications = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
    }

    return $notifications;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Notifications</title>
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="admin_dashboard.php">
                <h1>Admin Dashboard</h1>
            </a>
        </div>
    </div>
    <div>
        <h1>Notifications for <?php echo $action; ?></h1>
        <?php
            if (!empty($action)) {
                $notifications = getNotificationsByAction($mysqli, $action);

                if (!empty($notifications)) {
                    echo "<table>";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th>Action</th>";
                    echo "<th>Document Title</th>";
                    echo "<th>Timestamp</th>";
                    echo "<th>Author</th>";
                    echo "<th>Message</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";

                    foreach ($notifications as $notification) {
                        echo "<tr>";
                        echo "<td>{$notification['action']}</td>";
                        echo "<td>{$notification['document_title']}</td>";
                        echo "<td>{$notification['timestamp']}</td>";
                        echo "<td>{$notification['firstName']} {$notification['lastName']}</td>"; // Remove HTML-style comment
                        echo "<td>{$notification['message']}</td>";
                        echo "</tr>";
                    }

                    echo "</tbody>";
                    echo "</table>";
                } else {
                    echo "No notifications found for $action.";
                }
            } else {
                echo "No action provided.";
            }
        ?>
    </div>

</body>
</html>
