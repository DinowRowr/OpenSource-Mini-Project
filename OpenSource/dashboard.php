<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['email'])) {
    // Redirect to the login page
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome!</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const categoryDropdown = document.getElementById("categoryDropdown");
            const documentItems = document.querySelectorAll(".document");

            categoryDropdown.addEventListener("change", filterDocuments);

            // Function to filter documents based on the selected category
            function filterDocuments() {
                const selectedCategory = categoryDropdown.value.toLowerCase();

                documentItems.forEach(function (documentItem) {
                    const category = documentItem.getAttribute("data-category").toLowerCase();

                    if (selectedCategory === "all categories" || category === selectedCategory) {
                        documentItem.style.display = "block";
                    } else {
                        documentItem.style.display = "none";
                    }
                });
            }
        });
    </script>
</head>
<body>

    <div id="header">
        <div id="title">
            <a href="dashboard.php">
                <h1>DnD CollaboraDocs</h1>
            </a>
        </div>
     
        <div id="navigation">
            <ul>
                <li><a href="profile.php">PROFILE</a></li>
                <li><a href="notifications.php">NOTIFICATIONS</a></li>
                <li><a href="upload.php">UPLOAD</a></li>
                <li><a href="logout.php">LOGOUT</a></li>
            </ul>
        </div>        
    </div>

    <div id="body">
        <h1>WELCOME!</h1>
        <p>
            <input type="text" id="search" placeholder="Search for: #tags, Title, or Author">
            <button id="clearButton">Clear</button>
        </p>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const searchInput = document.getElementById("search");
                const clearButton = document.getElementById("clearButton");
                const documentItems = document.querySelectorAll(".document");

                searchInput.addEventListener("input", performSearch);
                clearButton.addEventListener("click", clearSearch);

                function performSearch() {
                    const searchTerm = searchInput.value.trim().toLowerCase();
                    documentItems.forEach(function (documentItem) {
                        const title = documentItem.querySelector("h2").textContent.toLowerCase();
                        const author = documentItem.querySelector(".author").textContent.toLowerCase();
                        const tagsContainer = documentItem.querySelector(".tags").textContent.toLowerCase();

                        if (title.includes(searchTerm) || author.includes(searchTerm) || tagsContainer.includes(searchTerm)) {
                            documentItem.style.display = "block";
                        } else {
                            documentItem.style.display = "none";
                        }
                    });
                }

                function clearSearch() {
                    searchInput.value = ""; // Clear the search input
                    documentItems.forEach(function (documentItem) {
                        documentItem.style.display = "block"; // Reset display for all items
                    });
                }
            });
        </script>
        <div id="tabs">
        <div class="tab">
            <select id="categoryDropdown">
                <option value="All Categories">All Categories</option>
                <option value="PDF">PDF</option>
                <option value="Compressed Folder">Compressed Folder</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="tab">
            <a href="dashboard.php?sort=views">Most Viewed</a>
        </div>
        <div class="tab">
            <a href="dashboard.php?sort=favorites">Most Favorites</a>
        </div>
        </div>

        <!--Dito Ididisplay yung Documents-->
        <div id="documents">
            <?php     
            // Include your database connection code here
            require_once 'config.php';

            if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['fileID'])) {
                $fileID = $_POST['fileID'];
                $userID = $_SESSION['userID'];

                if (isset($_POST['favorite'])) {
                    // The user clicked the "Favorite" button, process the request
                    // Check if the document is already favorited by the user
                    $checkFavoriteSQL = "SELECT * FROM user_favorites WHERE user_id = $userID AND file_id = $fileID";
                    $checkFavoriteResult = mysqli_query($mysqli, $checkFavoriteSQL);

                    if (mysqli_num_rows($checkFavoriteResult) === 0) {
                        // The document is not favorited yet, insert it into user_favorites
                        $insertFavoriteSQL = "INSERT INTO user_favorites (user_id, file_id) VALUES ($userID, $fileID)";
                        if (mysqli_query($mysqli, $insertFavoriteSQL)) {
                            // Document added to favorites, update the 'favorites' count in the 'documents' table
                            $updateFavoritesSQL = "UPDATE documents SET favorites = favorites + 1 WHERE file_id = $fileID";
                            mysqli_query($mysqli, $updateFavoritesSQL);
                        }
                    }
                } elseif (isset($_POST['unfavorite'])) {
                    // The user clicked the "Unfavorite" button, process the request
                    // Check if the document is favorited by the user
                    $checkFavoriteSQL = "SELECT * FROM user_favorites WHERE user_id = $userID AND file_id = $fileID";
                    $checkFavoriteResult = mysqli_query($mysqli, $checkFavoriteSQL);

                    if (mysqli_num_rows($checkFavoriteResult) > 0) {
                        // The document is favorited, delete it from user_favorites
                        $deleteFavoriteSQL = "DELETE FROM user_favorites WHERE user_id = $userID AND file_id = $fileID";
                        if (mysqli_query($mysqli, $deleteFavoriteSQL)) {
                            // Document removed from favorites, update the 'favorites' count in the 'documents' table
                            $updateFavoritesSQL = "UPDATE documents SET favorites = favorites - 1 WHERE file_id = $fileID";
                            mysqli_query($mysqli, $updateFavoritesSQL);
                        }
                    }
                }
            }

            // Query to retrieve documents with their respective authors
            $sql = "SELECT d.file_id, d.title, d.category, d.description, d.tags, IFNULL(CONCAT(u.firstName, ' ', u.lastName), 'Deleted User') AS author, d.size, IFNULL(d.infoViews, 0) AS infoViews, IFNULL(d.favorites, 0) AS favorites, IFNULL(d.status, 'active') AS status, d.file
                    FROM documents d
                    LEFT JOIN users u ON d.author = u.user_id
                    WHERE d.status != 'rejected'";

                    if (isset($row['infoViews'])) {
                        $infoViews = $row['infoViews'];
                    } else {
                        $infoViews = 0; // Default value when infoViews is not set
                    }

                    if (isset($row['favorites'])) {
                        $favorites = $row['favorites'];
                    } else {
                        $favorites = 0; // Default value when favorites is not set
                    }

                    if (isset($row['status'])) {
                        $status = $row['status'];
                    } else {
                        $status = 'active'; // Default value when status is not set
                    }

                    // Check if the 'sort' parameter is set
                    if (isset($_GET['sort'])) {
                        $sortOption = $_GET['sort'];

                        // Sort by Views
                        if ($sortOption === 'views') {
                            $sql .= " ORDER BY d.infoViews DESC";
                        }
                        // Sort by Favorites
                        elseif ($sortOption === 'favorites') {
                            $sql .= " ORDER BY d.favorites DESC";
                        }
                    }
            $result = mysqli_query($mysqli, $sql);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $fileID = $row['file_id'];
                    $title = $row['title'];
                    $author = isset($row['author']) ? $row['author'] : ''; // Full name of the author
                    $tags = isset($row['tags']) ? $row['tags'] : '';
                    $file = $row['file'];
                    $category = $row['category']; // Make sure 'category' is fetched from the database
                    $views = $row['infoViews'];
                    $favorites = $row['favorites'];
                    $status = $row['status']; // Add status field
                
                    // Check if the document is favorited by the user
                    $isFavorited = false; // Default to not favorited
                    $checkFavoriteSQL = "SELECT * FROM user_favorites WHERE user_id = $userID AND file_id = $fileID";
                    $checkFavoriteResult = mysqli_query($mysqli, $checkFavoriteSQL);
                    if (mysqli_num_rows($checkFavoriteResult) > 0) {
                        $isFavorited = true; // Document is favorited
                    }

                    // Skip rendering documents with an "inactive" status
                    if ($status === 'inactive') {
                        continue;
                    }
                
                    // Output each document with the data-category attribute
                    echo '<div class="document" data-category="' . $category . '">';
                    echo "<h2>$title</h2>";
                    echo '<a href="document_details.php?fileID=' . $fileID . '&file=' . $file . '"><img src="img/document.jpg" alt="DocumentImage"></a>';
                    echo '</a>';
                    echo '<form method="post" action="dashboard.php">';
                    echo '<input type="hidden" name="fileID" value="' . $fileID . '">';
                    echo "<p>Category: $category</p>";
                    echo "<p class='author'>Author: $author</p>";
                    echo "<p class='tags'>Tags: $tags</p>";
                    echo '<p><i class="far fa-eye"></i> ' . $views . '</p>';
                    echo '<div class="favorite-container">';
                    if ($isFavorited) {
                        echo '<button class="unfavorite-button" name="unfavorite"><i class="fa-solid fa-bookmark"></i></button>' . $favorites;
                    } else {
                        echo '<button class="favorite-button" name="favorite"><i class="fa-regular fa-bookmark"></i></button>' . $favorites;
                    }
                    echo '</div>';
                    echo '</form>';
                    echo '</div>';
                }
            } else {
                echo "No documents found.";
            }
            ?>
        </div>
        
    </div>

    <div id="footer">
        <h3>Contact Us:</h3>
            <p>admin@gmail.com</p>
            <p>0912-345-6789</p>

        <h3>
            <a href="#">About Us</a>
            <a href="#">F.A.Q.</a>
        </h3>
        <a href="#"><ion-icon name="logo-github"></ion-icon></a>
        <a href="#"><ion-icon name="logo-linkedin"></ion-icon></a>
        <a href="#"><ion-icon name="logo-facebook"></ion-icon></a>
        <a href="#"><ion-icon name="logo-instagram"></ion-icon></a>
        <a href="#"><ion-icon name="logo-twitter"></ion-icon></a>
    </div>

</body>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</html>