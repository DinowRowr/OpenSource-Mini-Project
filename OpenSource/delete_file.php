<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['deleteFile'])) {
    $fileID = $_POST['fileID'];

    // Ensure that the current user is the owner of the file
    $userID = $_SESSION['userID'];
    $checkOwnerSQL = "SELECT * FROM documents WHERE file_id = $fileID AND author = $userID";
    $checkOwnerResult = mysqli_query($mysqli, $checkOwnerSQL);

    if (mysqli_num_rows($checkOwnerResult) > 0) {
        // The current user is the owner of the file, proceed with deletion

        // Delete records in the history table related to the file
        $deleteHistorySQL = "DELETE FROM history WHERE file_id = $fileID";
        if (mysqli_query($mysqli, $deleteHistorySQL)) {
            // History records deleted successfully

            // Next, delete records in the user_favorites table
            $deleteFavoritesSQL = "DELETE FROM user_favorites WHERE file_id = $fileID";
            if (mysqli_query($mysqli, $deleteFavoritesSQL)) {
                // Favorites records deleted successfully

                // Finally, delete the file record from the documents table
                $deleteDocumentSQL = "DELETE FROM documents WHERE file_id = $fileID";
                if (mysqli_query($mysqli, $deleteDocumentSQL)) {
                    // File deleted successfully
                    // You may also want to delete the physical file from your server
                    // Add the code to delete the file from your server here
                    echo "File and related records deleted successfully. <a href='dashboard.php'>Back to Dashboard</a>";
                } else {
                    echo "Error deleting file: " . mysqli_error($mysqli);
                }
            } else {
                echo "Error deleting favorites records: " . mysqli_error($mysqli);
            }
        } else {
            echo "Error deleting history records: " . mysqli_error($mysqli);
        }
    }
}
?>
