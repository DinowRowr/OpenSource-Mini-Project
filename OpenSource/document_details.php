<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Details</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="index.php">
                <h1>DnD Libraries</h1>
            </a>
        </div>        
    </div>

    <?php
    session_start();
    require_once 'config.php';

    if (isset($_GET['fileID'])) {
        $fileID = $_GET['fileID'];

        // Query to retrieve document details based on $fileID
        $documentSQL = "SELECT d.file_id, d.file, d.title, d.category, d.description, d.infoViews, d.favorites, d.tags, d.visibility, u.firstName, u.lastName, d.author
                        FROM documents d
                        LEFT JOIN users u ON d.author = u.user_id
                        WHERE d.file_id = $fileID";

        // Update the infoViews count
        $updateViewsSQL = "UPDATE documents SET infoViews = infoViews + 1 WHERE file_id = $fileID";
        mysqli_query($mysqli, $updateViewsSQL);

        $documentResult = mysqli_query($mysqli, $documentSQL);

        if (mysqli_num_rows($documentResult) > 0) {
            $documentRow = mysqli_fetch_assoc($documentResult);
            $title = $documentRow['title'];
            $category = $documentRow['category'];
            $views = $documentRow['infoViews'];
            $favorites = $documentRow['favorites'];
            $description = $documentRow['description'];
            $tags = $documentRow['tags'];
            $visibility = $documentRow['visibility'];
            $author = isset($documentRow['firstName']) && isset($documentRow['lastName']) ? $documentRow['firstName'] . ' ' . $documentRow['lastName'] : 'Deleted User';
            $file = $documentRow['file'];

            // Check if the current user is the owner
            $isOwner = ($_SESSION['userID'] == $documentRow['author']);

            // Display document details
            echo '<h2>Title: ' . $title . '</h2>';
            echo '<p>Author: ' . $author . '</p>';
            echo '<p>File ID: ' . $fileID . '</p>';
            echo '<p>Category: ' . $category . '</p>';
            echo '<p>Views: ' . $views . '</p>';
            echo '<p>Favorites: ' . $favorites . '</p>';
            echo '<p>Description: ' . $description . '</p>';
            echo '<p>Tags: ' . $tags . '</p>';
            echo '<p>Visibility: ' . ($visibility == 1 ? 'Public' : 'Private') . '</p>'; // Display visibility

            // View button
            echo '<a href="' . $file . '" target="_blank">Download File<br></a>';

            // Display ratings
            echo '<h3>Ratings:</h3>';
            echo '<p>Sort by: <a href="?fileID=' . $fileID . '&sort=asc">Least Ratings</a> | <a href="?fileID=' . $fileID . '&sort=desc">Most Ratings</a></p>';

            // Use the sort order parameter based on the user's choice
            $sortOrder = (isset($_GET['sort']) && $_GET['sort'] == 'asc') ? 'ASC' : 'DESC';
            displayRatings($mysqli, $fileID, $sortOrder);

            // Rating form (only visible to authenticated users who are not the owner)
            if (isset($_SESSION['userID']) && !$isOwner) {
                echo '<h3>Rate this document:</h3>';
                echo '<form method="post" action="submit_rating.php">';
                echo '<input type="hidden" name="fileID" value="' . $fileID . '">';
                
                // Dropdown for rating
                echo '<label for="rating">Rating:</label>';
                echo '<select name="rating">';
                echo '<option value="1">1 star</option>';
                echo '<option value="2">2 stars</option>';
                echo '<option value="3">3 stars</option>';
                echo '<option value="4">4 stars</option>';
                echo '<option value="5">5 stars</option>';
                echo '</select>';

                // Message input
                echo '<label for="message">Message:</label>';
                echo '<textarea name="message"></textarea>';

                echo '<input type="submit" name="submitRating" value="Submit Rating">';
                echo '</form>';
            }

            // Edit and delete buttons (visible to the owner)
            if ($isOwner) {
                echo '<a href="edit_file.php?fileID=' . $fileID . '">Edit File</a>';

                echo '<form method="post" action="delete_file.php">';
                echo '<input type="hidden" name="fileID" value="' . $fileID . '">';
                echo '<input type="submit" name="deleteFile" value="Delete File">';
                echo '</form>'; // Make sure to close the form here

                // Update visibility form
                echo '<form method="post" action="update_visibility.php">';
                echo '<input type="hidden" name="fileID" value="' . $fileID . '">';
                            
                // Dropdown for updating visibility
                echo '<label for="updateVisibility">Update Visibility:</label>';
                echo '<select name="updateVisibility">';
                echo '<option value="1" ' . ($visibility == 1 ? 'selected' : '') . '>Public</option>';
                echo '<option value="0" ' . ($visibility == 0 ? 'selected' : '') . '>Private</option>';
                echo '</select>';
                echo '<input type="submit" name="submitUpdateVisibility" value="Update Visibility">';
                echo '<br>';
                echo '</form>'; // Close the form here
            }
        } else {
            echo "Document not found.";
        }
    }

    function displayRatings($mysqli, $fileID, $sortOrder = 'DESC') {
        $ratingsSQL = "SELECT r.rating, r.message, u.firstName, u.lastName
                       FROM ratings r
                       LEFT JOIN users u ON r.user_id = u.user_id
                       WHERE r.file_id = $fileID
                       ORDER BY r.rating $sortOrder";
    
        $ratingsResult = mysqli_query($mysqli, $ratingsSQL);
    
        if (mysqli_num_rows($ratingsResult) > 0) {
            echo '<table>';
            echo '<tr><th>Rated By:</th><th>Rate:</th><th>Message:</th></tr>';
            while ($ratingRow = mysqli_fetch_assoc($ratingsResult)) {
                $raterName = isset($ratingRow['firstName']) && isset($ratingRow['lastName']) ? $ratingRow['firstName'] . ' ' . $ratingRow['lastName'] : 'Anonymous';
                $rating = $ratingRow['rating'];
                $message = $ratingRow['message'];
    
                echo '<tr>';
                echo '<td>' . $raterName . '</td>';
                echo '<td>' . $rating . '</td>';
                echo '<td>' . $message . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>No ratings yet.</p>';
        }
    }
    ?>
<?php
if ($category === 'PDF' || $category === 'Images') {
    echo '<embed src="' . $file . '" width="500" height="375" type="application/pdf">';
} elseif ($category === 'Compressed Folder') {
    echo 'Compressed folders cannot be embedded.';
} else {
    echo '<embed src="' . $file . '" width="500" height="375" type="application/pdf">';
}
?>


</body>
</html>