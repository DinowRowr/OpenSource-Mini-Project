<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Details</title>
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="dashboard.php">
                <h1>DnD CollaboraDocs</h1>
            </a>
        </div>        
    </div>
    

<?php
session_start();
require_once 'config.php';

if (isset($_GET['file'])) {
    $file = $_GET['file'];
} else {
    // Handle the case where the file path is not provided
}

if (isset($_GET['fileID'])) {
    $fileID = $_GET['fileID'];
    // Query to retrieve document details based on $fileID
    $sql = "SELECT d.file_id, d.title, d.category, d.description, d.infoViews, d.favorites, d.tags, u.firstName, u.lastName, d.author
            FROM documents d
            LEFT JOIN users u ON d.author = u.user_id
            WHERE d.file_id = $fileID";
            
    // Update the infoViews count
    $updateViewsSQL = "UPDATE documents SET infoViews = infoViews + 1 WHERE file_id = $fileID";
    if (mysqli_query($mysqli, $updateViewsSQL)) {
        // Views updated successfully
    } else {
        // Handle the error
        echo "Error updating views: " . mysqli_error($mysqli);
    }

    $result = mysqli_query($mysqli, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $title = $row['title'];
        $category = $row['category'];
        $views = $row['infoViews'];
        $favorites = $row['favorites'];
        $description = $row['description'];
        $tags = $row['tags'];
        $author = isset($row['firstName']) && isset($row['lastName']) ? $row['firstName'] . ' ' . $row['lastName'] : 'Deleted User';
        $ownerID = $row['author'];
        

        // Check if the current user is the owner
        $isOwner = ($_SESSION['userID'] == $ownerID);
        
        // Display document details
        echo '<h2>Title: ' . $title . '</h2>';
        echo '<p>Author: ' . $author . '</p>';
        echo '<p>File ID: ' . $fileID . '</p>';
        echo '<p>Category: ' . $category . '</p>';
        echo '<p>Views: ' . $views . '</p>';
        echo '<p>Favorites: ' . $favorites . '</p>';
        echo '<p>Description: ' . $description . '</p>';
        echo '<p>Tags: ' . $tags . '</p>';

        // View button
        echo '<a href="' . $file . '" target="_blank">View File<br></a>';

        // Edit button (visible to the owner)
        if ($isOwner) {
            echo '<a href="edit_file.php?fileID=' . $fileID . '">Edit File</a>';

            // Delete button (visible to the owner)
            echo '<form method="post" action="delete_file.php">';
            echo '<input type="hidden" name="fileID" value="' . $fileID . '">';
            echo '<input type="submit" name="deleteFile" value="Delete File">';
            echo '</form>';
        }
    } else {
        echo "Document not found.";
    }
}
?>
</body>
</html>