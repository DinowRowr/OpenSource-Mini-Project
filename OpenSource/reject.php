<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['reject'])) {
        $fileID = $_POST['fileID'];
        $message = mysqli_real_escape_string($mysqli, $_POST['message']);

        // Update the status in the "documents" table to 'rejected'
        $updateDocumentStatusSQL = "UPDATE documents SET status = 'rejected' WHERE file_id = $fileID";
        if (mysqli_query($mysqli, $updateDocumentStatusSQL)) {
            // Remove the file from the "authorization" table
            $deleteAuthorizationSQL = "DELETE FROM authorization WHERE file_id = $fileID";
            if (mysqli_query($mysqli, $deleteAuthorizationSQL)) {
                // File rejected and removed from authorization

                // Notify the user with the rejection message
                $documentTitleSQL = "SELECT title FROM documents WHERE file_id = $fileID";
                $documentTitleResult = mysqli_query($mysqli, $documentTitleSQL);
                if ($documentTitleRow = mysqli_fetch_assoc($documentTitleResult)) {
                    $documentTitle = $documentTitleRow['title'];
                    $authorIDSQL = "SELECT author FROM documents WHERE file_id = $fileID";
                    $authorIDResult = mysqli_query($mysqli, $authorIDSQL);
                    if ($authorIDRow = mysqli_fetch_assoc($authorIDResult)) {
                        $userID = $authorIDRow['author'];
                        $action = 'Rejected';
                        // Insert the message into the notifications table
                        $insertNotificationSQL = "INSERT INTO notifications (user_id, action, document_title, timestamp, message) VALUES ($userID, '$action', '$documentTitle', NOW(), '$message')";
                        mysqli_query($mysqli, $insertNotificationSQL);
                    }
                }
                header("Location: authorization.php");
            } else {
                echo "Error removing file from authorization table: " . mysqli_error($mysqli);
            }
        } else {
            echo "Error updating document status: " . mysqli_error($mysqli);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Add your HTML head content here -->
</head>
<body>
    <div>
        <h1>Rejection Page</h1>
        <form method="post">
            <input type='hidden' name='fileID' value='<?php echo isset($_GET['fileID']) ? $_GET['fileID'] : ''; ?>'>
            <label for="message">Rejection Message:</label>
            <textarea name="message" id="message" rows="4" cols="50"></textarea>
            <br>
            <button type="submit" name="reject">Submit Rejection</button>
        </form>
    </div>
</body>
</html>
