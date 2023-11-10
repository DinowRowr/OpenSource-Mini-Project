<?php
session_start();
require_once 'config.php';

function getTotalApprovals($mysqli) {
    $sql = "SELECT COUNT(*) as total FROM notifications WHERE action = 'Approved'";
    $result = mysqli_query($mysqli, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total'];
    }
    return 0; // Return 0 if no data found
}

function getTotalRejections($mysqli) {
    $sql = "SELECT COUNT(*) as total FROM notifications WHERE action = 'Rejected'";
    $result = mysqli_query($mysqli, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total'];
    }
    return 0; // Return 0 if no data found
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analytics</title>
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
        <h1>Analytics</h1>
        <p>Total Approvals: <?php echo getTotalApprovals($mysqli); ?></p>
        <p>Total Rejections: <?php echo getTotalRejections($mysqli); ?></p>
        <!-- You can add more analytics here as needed -->
        <form method="post" action="view_notifications.php">
            <input type="hidden" name="action" value="Approved">
            <button type="submit" name="view_approvals">View Notifications for Approvals</button>
        </form>
        <form method="post" action="view_notifications.php">
            <input type="hidden" name="action" value="Rejected">
            <button type="submit" name="view_rejections">View Notifications for Rejections</button>
        </form>
    </div>
</body>
</html>
