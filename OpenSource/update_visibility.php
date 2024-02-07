<?php
session_start();
require_once 'config.php';

if (isset($_POST['submitUpdateVisibility'])) {
    $fileID = $_POST['fileID'];
    $newVisibility = $_POST['updateVisibility'];

    // Update the visibility in the database
    $updateVisibilitySQL = "UPDATE documents SET visibility = $newVisibility WHERE file_id = $fileID";

    if (mysqli_query($mysqli, $updateVisibilitySQL)) {
        // Visibility updated successfully
        header("Location: document_details.php?fileID=$fileID");
        exit();
    } else {
        // Handle the error
        echo "Error updating visibility: " . mysqli_error($mysqli);
    }
}
?>
