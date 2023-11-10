<?php
session_start(); // Start the session to access $_SESSION

require_once 'config.php'; // Include your database connection configuration

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if the form was submitted with files
    if (!empty($_FILES['file']['name'][0])) {
        // Loop through each uploaded file
        foreach ($_FILES['file']['name'] as $key => $filename) {
            $title = $_POST["title"][$key];
            $category = $_POST["category"][$key];
            $description = $_POST["description"][$key];
            $tags = $_POST["tags"][$key];

            // Format tags with # prefix
            $formattedTags = formatTags($tags);

            // Check if $_SESSION["userID"] is set
            if (isset($_SESSION["userID"])) {
                $author = $_SESSION["userID"]; // Use userID as the author
            } else {
                // Handle the case where userID is not set (e.g., the user is not logged in)
                header("Location: login.php");
                exit();
            }

            // Calculate the file size in megabytes (MB)
            $size = $_FILES['file']['size'][$key] / (1024 * 1024); // Convert bytes to MB

            // Check if the file is a favorite
            $isFavorite = isset($_POST["favorite"][$key]) ? 1 : 0;

            // Handle file upload for each file
            $targetDirectory = "uploadedDocs/";
            $targetFile = $targetDirectory . basename($_FILES['file']['name'][$key]);

            if (move_uploaded_file($_FILES['file']['tmp_name'][$key], $targetFile)) {
                // File upload success
                $sqlDocuments = "INSERT INTO documents (file, title, category, description, tags, author, size, favorites, dateCreated, status)
                    VALUES ('$targetFile', '$title', '$category', '$description', '$formattedTags', $author, $size, $isFavorite, NOW(), 'inactive')";

                if (mysqli_query($mysqli, $sqlDocuments)) {
                    // Insert into the "authorization" table
                    $fileID = mysqli_insert_id($mysqli); // Get the file_id of the last inserted document
                    $sqlAuthorization = "INSERT INTO authorization (user_id, file_id, status) VALUES ($author, $fileID, 'pending')";

                    if (mysqli_query($mysqli, $sqlAuthorization)) {
                        // Success, continue processing other files or redirect to a page indicating that the files need approval
                    } else {
                        echo "Error inserting into the authorization table: " . mysqli_error($mysqli);
                    }
                } else {
                    echo "Error inserting into the documents table: " . mysqli_error($mysqli);
                }
            } else {
                // File upload error
                echo "Sorry, there was an error uploading your file.";
            }
        }

        // Redirect to a page indicating that the files need approval
        header("Location: needs_approval.php");
        exit();
    } else {
        // Handle the case where no files were selected
        echo "Please select one or more files to upload.";
    }
}

// Function to format tags with # prefix for multiple tags
function formatTags($tags) {
    $tagArray = explode(',', $tags);
    $formattedTags = array_map(function ($tag) {
        return '#' . trim($tag); // Add # prefix and remove any leading/trailing spaces
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
            <a href="dashboard.php">
                <h1>DnD CollaboraDocs</h1>
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
                <button type="button" class="deleteRow" onclick="deleteFileRow(this)">Delete Row</button>
            </div>
        </div>
        <button type="button" id="addMore">Add More</button>
        <!-- Other input fields as needed -->
        <input type="submit" value="Upload">
    </form>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const fileInputsContainer = document.getElementById("fileInputs");
    const addMoreButton = document.getElementById("addMore");

    addMoreButton.addEventListener("click", function () {
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
        newFileInputDiv.appendChild(deleteRowButton);

        fileInputsContainer.appendChild(newFileInputDiv);
    });
});

function deleteFileRow(button) {
    const fileInputDiv = button.parentElement;
    const fileInputsContainer = document.getElementById("fileInputs");

    if (fileInputsContainer.children.length > 1) {
        fileInputsContainer.removeChild(fileInputDiv);
    } else {
        alert("You cannot delete the last row.");
    }
}
</script>
</html>