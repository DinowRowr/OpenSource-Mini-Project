<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submitRating'])) {
        $fileID = $_POST['fileID'];
        $userID = $_SESSION['userID']; // Assuming you have stored the user ID in the session
        $rating = $_POST['rating'];
        $message = $_POST['message'];

        // Validate the input if necessary

        // Insert the rating into the database
        $insertRatingSQL = "INSERT INTO ratings (file_id, user_id, rating, message) 
                            VALUES ('$fileID', '$userID', '$rating', '$message')";

        if (mysqli_query($mysqli, $insertRatingSQL)) {
            echo "Rating submitted successfully. <a href='document_details.php?fileID=$fileID'>Go back to Document Details</a>";
        } else {
            echo "Error submitting rating: " . mysqli_error($mysqli);
        }
    }
} else {
    echo "Invalid request method.";
}
?>
