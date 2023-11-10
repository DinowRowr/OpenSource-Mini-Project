<?php
session_start(); // Start the session

require_once 'config.php'; // Include your database connection configuration

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['approve'])) {
        // Admin approved a file
        $fileID = $_POST['fileID'];
        
        // Update the status in the "documents" table to 'active'
        $updateDocumentStatusSQL = "UPDATE documents SET status = 'active' WHERE file_id = $fileID";
        if (mysqli_query($mysqli, $updateDocumentStatusSQL)) {
            // Remove the file from the "authorization" table
            $deleteAuthorizationSQL = "DELETE FROM authorization WHERE file_id = $fileID";
            if (mysqli_query($mysqli, $deleteAuthorizationSQL)) {
                // File approved and removed from authorization
    
                // Get the document title
                $documentTitleSQL = "SELECT title FROM documents WHERE file_id = $fileID";
                $documentTitleResult = mysqli_query($mysqli, $documentTitleSQL);
    
                if ($documentTitleRow = mysqli_fetch_assoc($documentTitleResult)) {
                    $documentTitle = $documentTitleRow['title'];
    
                    // Notify the user with the approval
                    $authorIDSQL = "SELECT author FROM documents WHERE file_id = $fileID";
                    $authorIDResult = mysqli_query($mysqli, $authorIDSQL);
                    if ($authorIDRow = mysqli_fetch_assoc($authorIDResult)) {
                        $userID = $authorIDRow['author'];
                        $action = 'Approved';
    
                        // Insert a notification into the notifications table
                        $insertNotificationSQL = "INSERT INTO notifications (user_id, action, document_title, timestamp) VALUES ($userID, '$action', '$documentTitle', NOW())";
                        mysqli_query($mysqli, $insertNotificationSQL);
                    }
    
                    header("Location: authorization.php");
                } else {
                    echo "Error getting document title: " . mysqli_error($mysqli);
                }
            } else {
                echo "Error removing file from authorization table: " . mysqli_error($mysqli);
            }
        } else {
            echo "Error updating document status: " . mysqli_error($mysqli);
        }
    }   elseif (isset($_POST['reject'])) {
        // Admin rejected a file
        $fileID = $_POST['fileID'];
    
        header("Location: reject.php?fileID=$fileID");
    }    
}


// Retrieve files with 'pending' status from the "authorization" table
$authorizationSQL = "SELECT a.authorization_id, a.user_id, a.file_id, d.title, d.category, d.description, d.tags, IFNULL(CONCAT(u.firstName, ' ', u.lastName), 'Deleted User') AS author, d.size, d.file, a.status
                    FROM authorization a
                    JOIN documents d ON a.file_id = d.file_id
                    LEFT JOIN users u ON d.author = u.user_id
                    WHERE a.status = 'pending'";

$result = mysqli_query($mysqli, $authorizationSQL);

if (!$result) {
    die("Error: " . mysqli_error($mysqli));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Authorization</title>
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="admin_dashboard.php">
                <h1>Admin Dashboard</h1>
            </a>
        </div>
    </div>
    <h1>Admin Authorization</h1>
    <?php
    if (mysqli_num_rows($result) > 0) {
    ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Description</th>
                <th>Tags</th>
                <th>Author</th>
                <th>File Size</th>
                <th>Action</th>
                <th>View</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>{$row['title']}</td>";
                echo "<td>{$row['category']}</td>";
                echo "<td>{$row['description']}</td>";
                echo "<td>{$row['tags']}</td>";
                echo "<td>{$row['author']}</td>";
                echo "<td>{$row['size']} MB</td>";
                echo "<td>";
                echo "<form method='post'>";
                echo "<input type='hidden' name='fileID' value='{$row['file_id']}'>";
                echo "<button type='submit' name='approve'>Approve</button>";
                echo "<form method='post' action='reject.php'>";
                echo "<input type='hidden' name='fileID' value='{$row['file_id']}'>";
                echo "<button type='submit' name='reject'>Reject</button>";
                echo "</form>";
                echo "</form>";
                echo "</td>";
                echo "<td><a href='{$row['file']}' target='_blank'>View File</a></td>";
                echo "</tr>";    
            }
            ?>
        </tbody>
    </table>
    <?php
    } else {
        echo "There are no files to approve yet.";
    }
    ?>
</body>
</html>
