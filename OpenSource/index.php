<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Process category filter
$categoryFilter = isset($_GET['category']) ? strtolower($_GET['category']) : 'all categories';
$categoryFilterSQL = ($categoryFilter !== 'all categories') ? "AND d.category = '$categoryFilter'" : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome!</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    #pagination a {
        text-decoration: none;
    }
</style>
</head>
<body>

<div id="header">
    <div id="title">
        <a href="index.php">
            <h1>DnD Libraries</h1>
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
        <form id="searchForm" method="get" action="search_results.php">
            <input type="text" id="search" name="search" placeholder="Search for: #tags, Title, or Author">
            <button type="submit" id="searchButton">Search</button>
        </form>
    </p>
    <div id="tabs">
        <div class="tab">
            <select id="categoryDropdown" onchange="filterByCategory(this.value)">
                <option value="All Categories">All Categories</option>
                <option value="PDF">PDF</option>
                <option value="Compressed Folder">Compressed Folder</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="tab">
            <a href="index.php?sort=views">Most Viewed</a>
        </div>
        <div class="tab">
            <a href="index.php?sort=favorites">Most Favorites</a>
        </div>
    </div>

    <div id="documents">
        <?php
        require_once 'config.php';

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['fileID'])) {
            $fileID = $_POST['fileID'];
            $userID = $_SESSION['userID'];

            if (isset($_POST['favorite'])) {
                $checkFavoriteSQL = "SELECT * FROM user_favorites WHERE user_id = $userID AND file_id = $fileID";
                $checkFavoriteResult = mysqli_query($mysqli, $checkFavoriteSQL);

                if (mysqli_num_rows($checkFavoriteResult) === 0) {
                    $insertFavoriteSQL = "INSERT INTO user_favorites (user_id, file_id) VALUES ($userID, $fileID)";
                    if (mysqli_query($mysqli, $insertFavoriteSQL)) {
                        $updateFavoritesSQL = "UPDATE documents SET favorites = favorites + 1 WHERE file_id = $fileID";
                        mysqli_query($mysqli, $updateFavoritesSQL);
                    }
                }
            } elseif (isset($_POST['unfavorite'])) {
                $checkFavoriteSQL = "SELECT * FROM user_favorites WHERE user_id = $userID AND file_id = $fileID";
                $checkFavoriteResult = mysqli_query($mysqli, $checkFavoriteSQL);

                if (mysqli_num_rows($checkFavoriteResult) > 0) {
                    $deleteFavoriteSQL = "DELETE FROM user_favorites WHERE user_id = $userID AND file_id = $fileID";
                    if (mysqli_query($mysqli, $deleteFavoriteSQL)) {
                        $updateFavoritesSQL = "UPDATE documents SET favorites = favorites - 1 WHERE file_id = $fileID";
                        mysqli_query($mysqli, $updateFavoritesSQL);
                    }
                }
            }
        }

        $sql = "SELECT d.file_id, d.title, d.category, d.description, d.tags, IFNULL(CONCAT(u.firstName, ' ', u.lastName), 'Deleted User') AS author, d.size, IFNULL(d.infoViews, 0) AS infoViews, IFNULL(d.favorites, 0) AS favorites, IFNULL(d.status, 'active') AS status, d.file, d.visibility
        FROM documents d
        LEFT JOIN users u ON d.author = u.user_id
        WHERE d.status != 'rejected' AND d.visibility = 1 $categoryFilterSQL";

        if (isset($row['infoViews'])) {
            $infoViews = $row['infoViews'];
        } else {
            $infoViews = 0;
        }

        if (isset($row['favorites'])) {
            $favorites = $row['favorites'];
        } else {
            $favorites = 0;
        }

        if (isset($row['status'])) {
            $status = $row['status'];
        } else {
            $status = 'active';
        }

        if (isset($_GET['sort'])) {
            $sortOption = $_GET['sort'];

            if ($sortOption === 'views') {
                $sql .= " ORDER BY d.infoViews DESC";
            } elseif ($sortOption === 'favorites') {
                $sql .= " ORDER BY d.favorites DESC";
            }
        }

        $documentsPerPage = 3;
        $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $offset = ($current_page - 1) * $documentsPerPage;

        $sql .= " LIMIT $documentsPerPage OFFSET $offset";

        $result = mysqli_query($mysqli, $sql);

        // Calculate the total number of documents
        $totalDocumentsSQL = "SELECT COUNT(*) AS total FROM documents WHERE status != 'rejected'";
        $totalDocumentsResult = mysqli_query($mysqli, $totalDocumentsSQL);
        $totalDocumentsRow = mysqli_fetch_assoc($totalDocumentsResult);
        $totalDocuments = $totalDocumentsRow['total'];

        $totalPages = ceil($totalDocuments / $documentsPerPage);

        

        while ($row = mysqli_fetch_assoc($result)) {
            $fileID = $row['file_id'];
            $title = $row['title'];
            $author = isset($row['author']) ? $row['author'] : '';
            $tags = isset($row['tags']) ? $row['tags'] : '';
            $file = $row['file'];
            $category = $row['category'];
            $views = $row['infoViews'];
            $favorites = $row['favorites'];
            $status = $row['status'];
        
            $isFavorited = false;
            $checkFavoriteSQL = "SELECT * FROM user_favorites WHERE user_id = $userID AND file_id = $fileID";
            $checkFavoriteResult = mysqli_query($mysqli, $checkFavoriteSQL);
            if (mysqli_num_rows($checkFavoriteResult) > 0) {
                $isFavorited = true;
            }
        
            // Skip files with the status "removed" or "inactive"
            if ($status === 'removed' || $status === 'inactive') {
                continue;
            }
        
            echo '<div class="document" data-category="' . $category . '">';
            echo "<h2>$title</h2>";
            echo '<a href="document_details.php?fileID=' . $fileID . '&file=' . $file . '"><img src="img/document.jpg" alt="DocumentImage"></a>';
            echo '<form method="post" action="index.php">';
            echo '<input type="hidden" name="fileID" value="' . $fileID . '">';
            echo "<p>Category: $category</p>";
            echo "<p class='author'>Author: $author</p>";
            echo "<p class='_id'>Tags: $tags</p>";
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
        echo '<div id="pagination">';
        echo '<br>';
        for ($i = 1; $i <= $totalPages; $i++) {
            echo '<a href="index.php?page=' . $i . '">' . $i . '&nbsp;&nbsp;&nbsp;</a>';
        }
        echo '</div>';
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

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<script>
    function filterByCategory(selectedCategory) {
        if (selectedCategory !== 'All Categories') {
            window.location.href = 'categoryResults.php?category=' + encodeURIComponent(selectedCategory);
        } else {
            window.location.href = 'index.php'; // Redirect to index.php for 'All Categories'
        }
    }
</script>
</body>
</html>
