<?php
session_start();

require_once 'config.php';

// Check for a successful database connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if a delete action is requested
if (isset($_POST['delete']) && isset($_POST['notification_id'])) {
    $notificationId = $_POST['notification_id'];
    
    // Delete the selected notification
    $deleteSql = "DELETE FROM notifications WHERE notification_id = ?"; // Use the correct column name for notification_id
    $stmt = $mysqli->prepare($deleteSql);
    $stmt->bind_param("i", $notificationId);
    
    if ($stmt->execute()) {
        // Notification deleted successfully
    } else {
        // Handle the error
        echo "Error deleting notification: " . $stmt->error;
    }
}

// Fetch notifications with author names from your database, including notification_id
$sql = "SELECT n.notification_id, n.action, n.document_title, n.timestamp, n.message, CONCAT(u.firstName, ' ', u.lastName) AS author
        FROM notifications n
        INNER JOIN users u ON n.user_id = u.user_id";

$result = $mysqli->query($sql);

// Check if there are notifications
if ($result->num_rows > 0) {
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $notifications = [];
}

// Close the database connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="your_stylesheet.css"> <!-- Add your stylesheet link here -->
    <title>Notification History</title>
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="admin_dashboard.php">
                <h1>Admin Dashboard</h1>
            </a>
        </div>
    </div>

    <div id="body">
        <div>
            <h2>Notification History</h2>
            <table>
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Title</th>
                        <th>Timestamp</th>
                        <th>Message</th>
                        <th>Author</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notifications as $notification) : ?>
                        <tr>
                            <td><?php echo $notification['action']; ?></td>
                            <td><?php echo $notification['document_title']; ?></td>
                            <td><?php echo $notification['timestamp']; ?></td>
                            <td><?php echo $notification['message']; ?></td>
                            <td><?php echo $notification['author']; ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                    <button type="submit" name="delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
