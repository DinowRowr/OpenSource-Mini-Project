<?php
session_start();
require_once 'config.php';

if (isset($_GET['fileID'])) {
    $fileID = $_GET['fileID'];

    // Query to retrieve document details
    $sql = "SELECT d.file_id, d.title, d.category, d.author, d.description, d.tags, d.infoViews, d.favorites, u.firstName, u.lastName, d.file
            FROM documents d
            LEFT JOIN users u ON d.author = u.user_id
            WHERE d.file_id = $fileID";

    $result = mysqli_query($mysqli, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $document = mysqli_fetch_assoc($result);
        $title = $document['title'];
        $file = $document['file'];
        $authorName = !empty($document['firstName']) ? $document['firstName'] . ' ' . $document['lastName'] : "Deleted User";
        $category = $document['category'];
        $description = $document['description'];
        $tags = $document['tags'];
        $views = $document['infoViews'];
        $favorites = $document['favorites'];
        ?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title>View Document Details</title>
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
                <h1>Document Details</h1>
                <div>
                    <p>File ID: <?php echo $fileID; ?></p>
                    <p>Title: <?php echo $title; ?></p>
                    <p>Author: <?php echo $authorName; ?></p>
                    <p>Category: <?php echo $category; ?></p>
                    <p>Description: <?php echo $description; ?></p>
                    <p>Tags: <?php echo $tags; ?></p>
                    <p>Views: <?php echo $views; ?></p>
                    <p>Favorites: <?php echo $favorites; ?></p>
                    <p><a href="<?php echo $file; ?>" target="_blank">View File</a></p>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "Document not found.";
    }
}
?>
