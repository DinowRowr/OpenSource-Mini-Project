<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$fileID = $_GET['fileID'];

date_default_timezone_set('Asia/Manila');

$sql = "SELECT * FROM documents WHERE file_id = $fileID";
$result = mysqli_query($mysqli, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $document = mysqli_fetch_assoc($result);
    $currentTitle = $document['title'];
    $currentCategory = $document['category'];
    $currentDescription = $document['description'];
    $currentTags = $document['tags'];
} else {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newTitle = $_POST["title"];
    $newCategory = $_POST["category"];
    $newDescription = $_POST["description"];
    $newTags = $_POST["tags"];

    $formattedTags = formatTags($newTags);

    $checkDuplicateTitleSQL = "SELECT COUNT(*) FROM documents WHERE title = '$newTitle' AND file_id != $fileID";
    $duplicateTitleResult = mysqli_query($mysqli, $checkDuplicateTitleSQL);
    $duplicateTitleCount = mysqli_fetch_row($duplicateTitleResult)[0];
    if ($duplicateTitleCount > 0) {
        echo "Error: Title '$newTitle' is already in the system. <a href='javascript:history.back()'>Go back</a>";
        exit();
    }

    $allowedCategories = array(
        'pdf' => 'PDF',
        'zip' => 'Compressed Folder',
        'rar' => 'Compressed Folder',
        'jpg' => 'Other',
        'png' => 'Other',
        'mp4' => 'Other',
        'mp3' => 'Other',
        'gif' => 'Other',
    );

    $fileExtension = strtolower(pathinfo($document['file'], PATHINFO_EXTENSION));
    if (!isset($allowedCategories[$fileExtension]) || $newCategory !== $allowedCategories[$fileExtension]) {
        echo "Error: Wrong category for file. Please choose the correct category. <a href='javascript:history.back()'>Go back</a>";
        exit();
    }

    $sql = "UPDATE documents 
            SET title = '$newTitle', category = '$newCategory', description = '$newDescription', tags = '$formattedTags'
            WHERE file_id = $fileID";

    if (mysqli_query($mysqli, $sql)) {
        // Log edit history
        $user_id = $_SESSION['userID'];
        $date_of_modification = date('Y-m-d H:i:s');
        $status = "Updated";
        $logHistorySQL = "INSERT INTO edit_history (file_id, user_id, date_of_modification, status) 
                          VALUES ($fileID, $user_id, '$date_of_modification', '$status')";
        mysqli_query($mysqli, $logHistorySQL);

        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($mysqli) . " <a href='javascript:history.back()'>Go back</a>";
    }
}

function formatTags($tags) {
    $tagArray = explode(',', $tags);
    $formattedTags = array_map(function($tag) {
        $formattedTag = trim($tag);
        if (substr($formattedTag, 0, 1) !== '#') {
            $formattedTag = '#' . $formattedTag;
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
            <a href="index.php">
                <h1>DnD Libraries</h1>
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
        <a href="index.php"><button type="button">Cancel</button></a>
    </form>
</body>
</html>
