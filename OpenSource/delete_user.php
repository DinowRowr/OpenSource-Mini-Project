<?php
session_start();
require_once 'config.php'; // Include your database connection configuration

if (isset($_GET['user_id'])) {
    $userID = $_GET['user_id'];

    // Define a function to delete user records
    function deleteUserRecords($userID, $mysqli) {
        // Delete user favorites first
        $stmt = $mysqli->prepare("DELETE FROM user_favorites WHERE user_id = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->close();
    
        // Now you can safely delete the user
        $stmt = $mysqli->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->close();
    }    

    // Call the function to delete user records
    deleteUserRecords($userID, $mysqli);

    // Redirect to the admin dashboard after deletion
    header("Location: admin_dashboard.php");
    exit();
} else {
    // Handle the case where user_id is not provided
    echo "User ID not specified.";
}
?>
