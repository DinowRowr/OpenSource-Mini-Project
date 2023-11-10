<?php
session_start();
require_once 'config.php'; // Include your database connection configuration

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

    if ($stmtFavorites->execute() && $stmt->execute()) {
        // Commit the transaction if both queries are successful
        mysqli_commit($mysqli);
        header("Location: view_documents.php"); // Redirect back to the document list
        exit();
    } else {
        // Rollback the transaction if there is an error in any of the queries
        mysqli_rollback($mysqli);
        echo "Error: " . $stmtFavorites->error;
    }

    $stmtFavorites->close();
    $stmt->close();
}
?>
