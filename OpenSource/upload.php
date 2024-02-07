<?php
session_start();

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errorMessages = array();
    $uploadSuccessful = true;
    $duplicateFound = false;

    $maxFiles = 1;
    if (count($_FILES['file']['name']) > $maxFiles) {
        $uploadSuccessful = false;
        $errorMessages[] = "You can only upload up to $maxFiles files. Please select only one file.";
    }

    if (!empty($_FILES['file']['name'][0]) && $uploadSuccessful) {
        // Arrays to store details for duplicate removal, if needed
        $filesToRemove = array();
        $titlesToRemove = array();

        foreach ($_FILES['file']['name'] as $key => $filename) {
            $title = $_POST["title"][$key];
            $category = $_POST["category"][$key];
            $description = $_POST["description"][$key];
            $tags = $_POST["tags"][$key];

            // Check for duplicate title
            if (in_array($title, $titlesToRemove)) {
                $uploadSuccessful = false;
                $duplicateFound = true;
                $errorMessages[] = "Error: Title '$title' is already in the system.";
                continue;
            }

            $size = $_FILES['file']['size'][$key] / (1024 * 1024);
            $maxFileSize = 5;
            if ($size > $maxFileSize) {
                $uploadSuccessful = false;
                $errorMessages[] = "File '$filename' exceeds the maximum allowed size of $maxFileSize MB.";
                continue;
            }

            $allowedFileTypes = array('pdf', 'zip', 'rar', 'jpg', 'png', 'mp4', 'mp3', 'gif', 'jpeg');
            $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($fileExtension, $allowedFileTypes)) {
                $uploadSuccessful = false;
                $errorMessages[] = "File '$filename' has an invalid file type. File/s must be: pdf, zip, rar, jpg, png, mp4, mp3, gif, jpeg";
                continue;
            }

            // Check for duplicate title in the database
            $checkDuplicateTitleSQL = "SELECT COUNT(*) FROM documents WHERE title = '$title'";
            $duplicateTitleResult = mysqli_query($mysqli, $checkDuplicateTitleSQL);
            $duplicateTitleCount = mysqli_fetch_row($duplicateTitleResult)[0];
            if ($duplicateTitleCount > 0) {
                $uploadSuccessful = false;
                $duplicateFound = true;
                $errorMessages[] = "Error: Title '$title' is already in the system.";
                continue;
            }

            $targetFileInDB = "uploadedDocs/" . basename($_FILES['file']['name'][$key]);

            // Check for duplicate file
            $checkDuplicateFileSQL = "SELECT COUNT(*) FROM documents WHERE file = '$targetFileInDB'";
            $duplicateFileResult = mysqli_query($mysqli, $checkDuplicateFileSQL);
            $duplicateFileCount = mysqli_fetch_row($duplicateFileResult)[0];
            if ($duplicateFileCount > 0) {
                $uploadSuccessful = false;
                $duplicateFound = true;
                // Store details for potential removal
                $filesToRemove[] = $targetFileInDB;
                $titlesToRemove[] = $title;
                $errorMessages[] = "Error: File '$filename' is already in the system.";
                continue;
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

            if (!isset($allowedCategories[$fileExtension]) || $category !== $allowedCategories[$fileExtension]) {
                $uploadSuccessful = false;
                $errorMessages[] = "Wrong category for file '$filename'. Please choose the correct category.";
                continue;
            }

            $formattedTags = formatTags($tags);

            if (isset($_SESSION["userID"])) {
                $author = $_SESSION["userID"];
            } else {
                header("Location: login.php");
                exit();
            }

            $isFavorite = isset($_POST["favorite"][$key]) ? 1 : 0;
            $visibility = isset($_POST["visibility"][$key]) ? $_POST["visibility"][$key] : 1;

            $targetDirectory = "uploadedDocs/";
            $targetFile = $targetDirectory . basename($_FILES['file']['name'][$key]);

            if (move_uploaded_file($_FILES['file']['tmp_name'][$key], $targetFile)) {
                $sqlDocuments = "INSERT INTO documents (file, title, category, description, tags, author, size, favorites, dateCreated, status, visibility)
                        VALUES ('$targetFile', '$title', '$category', '$description', '$formattedTags', $author, $size, $isFavorite, NOW(), 'inactive', $visibility)";

                if (!mysqli_query($mysqli, $sqlDocuments)) {
                    $uploadSuccessful = false;
                    $errorMessages[] = "Error inserting into the documents table: " . mysqli_error($mysqli);
                    echo "Document insert error. File upload failed.";
                    unlink($targetFile);
                    exit();
                }

                $fileID = mysqli_insert_id($mysqli);

                $sqlAuthorization = "INSERT INTO authorization (user_id, file_id, status) VALUES ($author, $fileID, 'pending')";

                if (!mysqli_query($mysqli, $sqlAuthorization)) {
                    $uploadSuccessful = false;
                    $errorMessages[] = "Error inserting into the authorization table: " . mysqli_error($mysqli);
                    $deleteDocumentSQL = "DELETE FROM documents WHERE file_id = $fileID";
                    mysqli_query($mysqli, $deleteDocumentSQL);
                    unlink($targetFile);
                    echo "Authorization error. File upload failed.";
                    exit();
                }
            } else {
                $uploadSuccessful = false;
                $errorMessages[] = "Sorry, there was an error uploading your file.";
                echo "File upload error. File upload failed.";
            }
        }

        if ($duplicateFound) {
            echo '<h1>Error in File Upload:</h1>';
            echo '<p>Please fix the following issues:</p>';
            echo '<ul>';
            foreach ($errorMessages as $errorMessage) {
                echo '<li>' . $errorMessage . '</li>';
            }
            echo '</ul>';
            echo '<p><a href="' . $_SERVER['HTTP_REFERER'] . '">Go back</a></p>';
            exit();
        }

        if (!$uploadSuccessful) {
            echo '<h1>Error in File Upload:</h1>';
            echo '<p>Please fix the following issues:</p>';
            echo '<ul>';
            foreach ($errorMessages as $errorMessage) {
                echo '<li>' . $errorMessage . '</li>';
            }
            echo '</ul>';
            echo '<p><a href="' . $_SERVER['HTTP_REFERER'] . '">Go back</a></p>';
            exit();
        }

        header("Location: needs_approval.php");
        exit();
    } else {
        echo "Please select one file to upload.";
    }
}

function formatTags($tags)
{
    $tagArray = explode(',', $tags);
    $formattedTags = array_map(function ($tag) {
        return '#' . trim($tag);
    }, $tagArray);

    return implode(', ', $formattedTags);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload</title>
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="index.php">
                <h1>DnD Libraries</h1>
            </a>
        </div>            
    </div>
    <h1>UPLOAD FILE</h1>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <div id="fileInputs">
            <div class="file-input">
                <input type="file" name="file[]" multiple required>
                <input type="text" name="title[]" placeholder="Title" required>
                <select name="category[]" required>
                    <option value="PDF">PDF</option>
                    <option value="Compressed Folder">Compressed Folder</option>
                    <option value="Other">Other</option>
                </select>
                <textarea name="description[]" placeholder="Description" required></textarea>
                <input type="text" name="tags[]" placeholder="Tags (comma-separated)" required>
                <select name="visibility[]">
                    <option value="1">Public</option>
                    <option value="0">Private</option>
                </select>
            </div>
        </div>
        <input type="submit" value="Upload">
        <div id="fileValidation">
            <ul>
                <li>File must be 5 MB and lower</li>
                <li>Maximum of 1 file to upload</li>
                <li>File and Category must match</li>
                <li>File type must be: pdf, zip, rar, jpg, png, mp4, mp3, gif, jpeg</li>
                <li>No Duplicate files or titles</li>
            </ul>
        </div>
    </form>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fileInputsContainer = document.getElementById("fileInputs");
            const addMoreButton = document.getElementById("addMore");
            let addMoreButtonClickCount = 0;

            addMoreButton.addEventListener("click", function () {
                if (addMoreButtonClickCount < 4) {  
                    addMoreButtonClickCount++;

                    const newFileInputDiv = document.createElement("div");
                    newFileInputDiv.className = "file-input";

                    const newFileInput = document.createElement("input");
                    newFileInput.type = "file";
                    newFileInput.name = "file[]";

                    const newTitleInput = document.createElement("input");
                    newTitleInput.type = "text";
                    newTitleInput.name = "title[]";
                    newTitleInput.placeholder = "Title";
                    newTitleInput.required = true;

                    const newCategorySelect = document.createElement("select");
                    newCategorySelect.name = "category[]";
                    const pdfOption = document.createElement("option");
                    pdfOption.value = "PDF";
                    pdfOption.textContent = "PDF";
                    const compressedOption = document.createElement("option");
                    compressedOption.value = "Compressed Folder";
                    compressedOption.textContent = "Compressed Folder";
                    const otherOption = document.createElement("option");
                    otherOption.value = "Other";
                    otherOption.textContent = "Other";
                    newCategorySelect.appendChild(pdfOption);
                    newCategorySelect.appendChild(compressedOption);
                    newCategorySelect.appendChild(otherOption);

                    const newDescriptionTextarea = document.createElement("textarea");
                    newDescriptionTextarea.name = "description[]";
                    newDescriptionTextarea.placeholder = "Description";

                    const newTagsInput = document.createElement("input");
                    newTagsInput.type = "text";
                    newTagsInput.name = "tags[]";
                    newTagsInput.placeholder = "Tags (comma-separated)";

                    const newVisibilitySelect = document.createElement("select");
                    newVisibilitySelect.name = "visibility[]";
                    const publicOption = document.createElement("option");
                    publicOption.value = "1";
                    publicOption.textContent = "Public";
                    const privateOption = document.createElement("option");
                    privateOption.value = "0";
                    privateOption.textContent = "Private";
                    newVisibilitySelect.appendChild(publicOption);
                    newVisibilitySelect.appendChild(privateOption);

                    const deleteRowButton = document.createElement("button");
                    deleteRowButton.type = "button";
                    deleteRowButton.className = "deleteRow";
                    deleteRowButton.textContent = "Delete Row";
                    deleteRowButton.onclick = function () {
                        deleteFileRow(this);
                    };

                    newFileInputDiv.appendChild(newFileInput);
                    newFileInputDiv.appendChild(newTitleInput);
                    newFileInputDiv.appendChild(newCategorySelect);
                    newFileInputDiv.appendChild(newDescriptionTextarea);
                    newFileInputDiv.appendChild(newTagsInput);
                    newFileInputDiv.appendChild(newVisibilitySelect);
                    newFileInputDiv.appendChild(deleteRowButton);

                    fileInputsContainer.appendChild(newFileInputDiv);

                    if (addMoreButtonClickCount === 4) {
                        addMoreButton.style.display = "none";
                    }
                } else {
                    alert("You cannot add more rows.");
                }
            });

            function deleteFileRow(button) {
                const fileInputDiv = button.parentElement;
                const fileInputsContainer = document.getElementById("fileInputs");

                if (fileInputsContainer.children.length > 1) {
                    fileInputsContainer.removeChild(fileInputDiv);
                    addMoreButtonClickCount--;
                    if (addMoreButtonClickCount < 4) {
                        addMoreButton.style.display = "block";  
                    }
                } else {
                    alert("You cannot delete the last row.");
                }
            }
        });
    </script>
</body>
</html>
