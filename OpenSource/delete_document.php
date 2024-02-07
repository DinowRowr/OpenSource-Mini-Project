<?php
session_start();
require_once 'config.php'; 

if (isset($_GET['fileID'])) {
    $fileID = $_GET['fileID'];

    // Check if the user is authorized to delete the document (you can add your authorization logic here)
    // For example, you may want to check if the user is the owner or has admin privileges

    // First, delete user favorites
    $deleteFavoritesQuery = "DELETE FROM user_favorites WHERE file_id = ?";
    $stmtFavorites = $mysqli->prepare($deleteFavoritesQuery);
    $stmtFavorites->bind_param("i", $fileID);

    // Then, delete the document
    $deleteDocumentQuery = "DELETE FROM documents WHERE file_id = ?";
    $stmt = $mysqli->prepare($deleteDocumentQuery);
    $stmt->bind_param("i", $fileID);

    // First, delete related records from the ratings table
    $deleteRatingsQuery = "DELETE FROM ratings WHERE file_id = ?";
    $stmtRatings = $mysqli->prepare($deleteRatingsQuery);
    $stmtRatings->bind_param("i", $fileID);

    if ($stmtRatings->execute()) {
        // Once related records are deleted, proceed to delete user favorites and the document
        if ($stmtFavorites->execute() && $stmt->execute()) {
            // Commit the transaction if all queries are successful
            mysqli_commit($mysqli);
            header("Location: view_documents.php"); // Redirect back to the document list
            exit();
        } else {
            // Rollback the transaction if there is an error in any of the queries
            mysqli_rollback($mysqli);
            echo "Error: " . $stmtFavorites->error;
        }
    } else {
        // Handle the error if deletion from the ratings table fails
        echo "Error: " . $stmtRatings->error;
    }

    $stmtFavorites->close();
    $stmt->close();
    $stmtRatings->close();

}
?>
