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
    <title>Category Results</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div id="header">
    <div id="title">
        <a href="index.php">
            <h1>DnD Libraries</h1>
        </a>
    </div>
</div>

<div id="body">
    <h1>Category Results - <?php echo ucfirst($categoryFilter); ?></h1>
    <div id="documents">
        <?php
        require_once 'config.php';

        $sql = "SELECT d.file_id, d.title, d.category, d.description, d.tags, IFNULL(CONCAT(u.firstName, ' ', u.lastName), 'Deleted User') AS author, d.size, IFNULL(d.infoViews, 0) AS infoViews, IFNULL(d.favorites, 0) AS favorites, IFNULL(d.status, 'active') AS status, d.file, d.visibility
                FROM documents d
                LEFT JOIN users u ON d.author = u.user_id
                WHERE d.status != 'rejected' $categoryFilterSQL AND (d.visibility = 1 OR d.author = $userID)";

        $result = mysqli_query($mysqli, $sql);

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

            if ($status === 'inactive') {
                continue;
            }

            echo '<div class="document" data-category="' . $category . '">';
            echo "<h2>$title</h2>";
            echo '<a href="document_details.php?fileID=' . $fileID . '&file=' . $file . '"><img src="img/document.jpg" alt="DocumentImage"></a>';
            echo '<form method="post" action="index.php">';
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
        ?>
    </div>
</div>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
