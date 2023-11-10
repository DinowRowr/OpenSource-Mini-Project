<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check if the user ID is provided as a parameter
if (isset($_GET['user_id'])) {
    $userID = $_GET['user_id'];

    // Query to retrieve the user's uploaded documents with additional information
    $sql = "SELECT d.file_id, d.title, d.category, d.description, d.author, d.tags, d.infoViews, d.favorites, d.dateCreated, d.status, u.firstName, u.lastName
            FROM documents d
            LEFT JOIN users u ON d.author = u.user_id
            WHERE d.author = $userID";
    $result = mysqli_query($mysqli, $sql);

    if ($result) {
        $uploadedDocuments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        die("Error: " . mysqli_error($mysqli));
    }
} else {
    // Handle the case where the user ID parameter is missing
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="#">
    <title>Uploaded Files</title>
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
        <h1>Uploaded Files</h1>
        <div>
            <table>
            <thead>
                <tr>
                    <th>File ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Author</th>
                    <th>Tags</th>
                    <th>Views</th>
                    <th>Favorites</th>
                    <th>Date Created</th>
                    <th>Status</th>
                </tr>
            </thead>
                <tbody>
                    <?php
                    if (!empty($uploadedDocuments)) {
                        foreach ($uploadedDocuments as $document) {
                            echo "<tr>";
                            echo "<td>" . $document['file_id'] . "</td>";
                            echo "<td>" . $document['title'] . "</td>";
                            echo "<td>" . $document['category'] . "</td>";
                            echo "<td>" . $document['description'] . "</td>";
                            echo "<td>" . $document['firstName'] . ' ' . $document['lastName'] . "</td>";
                            echo "<td>" . $document['tags'] . "</td>";
                            echo "<td>" . $document['infoViews'] . "</td>";
                            echo "<td>" . $document['favorites'] . "</td>";
                            echo "<td>" . $document['dateCreated'] . "</td>";
                            echo "<td>" . $document['status'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>No uploaded files found for this user.</td></tr>";
                    }                    
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
