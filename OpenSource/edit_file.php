<?php
session_start(); // Start the session to access $_SESSION

require_once 'config.php'; // Include your database connection configuration

if (!isset($_SESSION['userID'])) {
    // Handle the case where the user is not logged in
    header("Location: login.php");
    exit();
}

$fileID = $_GET['fileID']; // Get the fileID from the URL

// Set the time zone
date_default_timezone_set('Asia/Manila');

// Retrieve the existing document details from the database
$sql = "SELECT * FROM documents WHERE file_id = $fileID";
$result = mysqli_query($mysqli, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $document = mysqli_fetch_assoc($result);
    $currentTitle = $document['title'];
    $currentCategory = $document['category'];
    $currentDescription = $document['description'];
    $currentTags = $document['tags'];
} else {
    // Handle the case where the document does not exist
    header("Location: dashboard.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newTitle = $_POST["title"];
    $newCategory = $_POST["category"];
    $newDescription = $_POST["description"];
    $newTags = $_POST["tags"];
    $userID = $_SESSION['userID'];
    
    // Format tags with # prefix
    $formattedTags = formatTags($newTags);

    // Update file details in the document table
    $sql = "UPDATE documents 
            SET title = '$newTitle', category = '$newCategory', description = '$newDescription', tags = '$formattedTags'
            WHERE file_id = $fileID";

    if (mysqli_query($mysqli, $sql)) {
        // Insert a record in the history table
        $modifiedDate = date('Y-m-d H:i:s');
        $historySql = "INSERT INTO history (user_id, file_id, modifiedDate) VALUES (?, ?, ?)";
        $historyStmt = $mysqli->prepare($historySql);
        $historyStmt->bind_param("iis", $userID, $fileID, $modifiedDate);
        
        if ($historyStmt->execute()) {
            // Success
            header("Location: dashboard.php");
            exit();
        } else {
            // Handle the error if the history record insertion fails
            echo "Error: " . $historyStmt->error;
        }
        
        $historyStmt->close();
    } else {
        // Error
        echo "Error: " . $sql . "<br>" . mysqli_error($mysqli);
    }
}

// Function to format tags, adding # if missing
function formatTags($tags) {
    $tagArray = explode(',', $tags);
    $formattedTags = array_map(function($tag) {
        $formattedTag = trim($tag); // Remove any leading/trailing spaces
        if (substr($formattedTag, 0, 1) !== '#') {
            $formattedTag = '#' . $formattedTag; // Add # if missing
        }
        return $formattedTag;
    }, $tagArray);

    return implode(', ', $formattedTags);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit File</title>
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="dashboard.php">
                <h1>DnD CollaboraDocs</h1>
            </a>
        </div>
    </div>
    <h1>EDIT FILE</h1>
    <form action="edit_file.php?fileID=<?php echo $fileID; ?>" method="POST">
        <input type="text" name="title" placeholder="Title" required value="<?php echo $currentTitle; ?>">
        <select name="category" required>
            <option value="PDF" <?php echo ($currentCategory === 'PDF') ? 'selected' : ''; ?>>PDF</option>
            <option value="Compressed Folder" <?php echo ($currentCategory === 'Compressed Folder') ? 'selected' : ''; ?>>Compressed Folder</option>
            <option value="Other" <?php echo ($currentCategory === 'Other') ? 'selected' : ''; ?>>Other</option>
        </select>
        <textarea name="description" placeholder="Description"><?php echo $currentDescription; ?></textarea>
        <input type="text" name="tags" placeholder="Tags (comma-separated)" value="<?php echo $currentTags; ?>">
        <input type="submit" value="Update">
    </form>
</body>
</html>
