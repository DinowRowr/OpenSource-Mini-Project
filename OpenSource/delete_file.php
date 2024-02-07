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
        // The current user is the owner of the file, proceed with deleting the file

        // Delete the ratings for the document from the ratings table
        $deleteRatingsSQL = "DELETE FROM ratings WHERE file_id = $fileID";
        mysqli_query($mysqli, $deleteRatingsSQL);

        // Delete the document from the documents table
        $deleteFileSQL = "DELETE FROM documents WHERE file_id = $fileID";
        if (mysqli_query($mysqli, $deleteFileSQL)) {
            // File and associated ratings deleted successfully
            echo "Document is deleted. <a href='index.php'>Back to Dashboard</a>";
            // Add the header redirection after successful deletion
        } else {
            // Handle the error
            echo "Error deleting document: " . mysqli_error($mysqli);
        }
    }
}
?>
