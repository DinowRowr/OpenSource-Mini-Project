<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
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
        <?php
        session_start();

        // Check if the user is not logged in
        if (!isset($_SESSION['email'])) {
            // Redirect to the login page
            header("Location: login.php");
            exit();
        }

        $userID = $_SESSION['userID'];

        // Include your database connection code here
        require_once 'config.php';

        // Check if the search parameter is set
        if (isset($_GET['search'])) {
            // Sanitize the search input to prevent SQL injection
            $searchTerm = mysqli_real_escape_string($mysqli, $_GET['search']);

            // Display the user's search term
            echo "<h1>Search Results for: $searchTerm</h1>";

            // Query to retrieve documents with their respective authors based on the search term
            $sql = "SELECT d.file_id, d.title, d.category, d.description, d.tags, IFNULL(CONCAT(u.firstName, ' ', u.lastName), 'Deleted User') AS author, d.size, IFNULL(d.infoViews, 0) AS infoViews, IFNULL(d.favorites, 0) AS favorites, IFNULL(d.status, 'active') AS status, d.file, d.visibility
                    FROM documents d
                    LEFT JOIN users u ON d.author = u.user_id
                    WHERE d.status != 'rejected' AND (d.title LIKE '%$searchTerm%' OR d.tags LIKE '%$searchTerm%' OR CONCAT(u.firstName, ' ', u.lastName) LIKE '%$searchTerm%') AND (d.visibility = 1 OR d.author = $userID)";

            $result = mysqli_query($mysqli, $sql);

            // Display search results
            echo '<div id="documents">';
            while ($row = mysqli_fetch_assoc($result)) {
                // Output each document with the data-category attribute
                echo '<div class="document" data-category="' . $row['category'] . '">';
                echo "<h2>{$row['title']}</h2>";
                echo '<a href="document_details.php?fileID=' . $row['file_id'] . '&file=' . $row['file'] . '"><img src="img/document.jpg" alt="DocumentImage"></a>';
                echo '<form method="post" action="index.php">';
                echo '<input type="hidden" name="fileID" value="' . $row['file_id'] . '">';
                echo "<p>Category: {$row['category']}</p>";
                echo "<p class='author'>Author: {$row['author']}</p>";
                echo "<p class='tags'>Tags: {$row['tags']}</p>";
                echo '<p><i class="far fa-eye"></i> ' . $row['infoViews'] . '</p>';
                echo '<div class="favorite-container">';
                echo '<button class="favorite-button" name="favorite"><i class="fa-regular fa-bookmark"></i></button>' . $row['favorites'];
                echo '</div>';
                echo '</form>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            // Redirect back to the dashboard if no search term is provided
            header("Location: index.php");
            exit();
        }
        ?>
    </div>
</body>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</html>
