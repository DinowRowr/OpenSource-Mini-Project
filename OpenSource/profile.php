<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

$userID = $_SESSION['userID'];

$sql = "SELECT * FROM users WHERE user_id = $userID";
$result = mysqli_query($mysqli, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    $firstName = $row['firstName'];
    $lastName = $row['lastName'];
    $email = $row['email'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="dashboard.php">
                <h1>DnD CollaboraDocs</h1>
            </a>
        </div>
    </div>

    <div id="body">
        <h1>Profile</h1>
        <div>
            <p>First Name: <?php echo $firstName; ?></p>
            <p>Last Name: <?php echo $lastName; ?></p>
            <p>Email: <?php echo $email; ?></p>
            <a href="edit_profile.php">Edit Profile</a>
        </div>
        <div id="tabButtons">
            <button id="uploadsTab">Uploads</button>
            <button id="favoritesTab">Favorites</button>
        </div>
        <div id="uploadsContent">
            <h2>Your Uploaded Documents</h2>
            <?php
            require_once 'config.php';

            function getUploadedDocuments($userID, $mysqli) {
                $query = "SELECT * FROM documents WHERE author = ? AND status = 'active'";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("i", $userID);
            
                $uploadedDocuments = array();
            
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
            
                    while ($row = $result->fetch_assoc()) {
                        $uploadedDocuments[] = $row;
                    }
            
                    $stmt->close();
            
                    return $uploadedDocuments;
                } else {
                    $stmt->close();
            
                    return false;
                }
            }                

            $userID = $_SESSION['userID'];
            $uploadedDocuments = getUploadedDocuments($userID, $mysqli);

            if ($uploadedDocuments) {
                foreach ($uploadedDocuments as $document) {
                    $fileID = $document['file_id'];
                    $title = $document['title'];
                    $category = $document['category'];
                    $infoViews = $document['infoViews'];
                    $file = $document['file'];

                    echo '<div class="document">';
                    echo "<h3>$title</h3>";
                    echo '<a href="document_details.php?fileID=' . $fileID . '&file=' . $file . '"><img src="img/document.jpg" alt="DocumentImage"></a>';
                    echo '</a>';
                    echo "<p>Category: $category</p>";
                    echo '<p><i class="far fa-eye"></i> ' . $infoViews . '</p>';
                    
                    echo '</div>';
                }
            } else {
                echo "No uploaded documents found.";
            }
            ?>
        </div>
        <div id="favoritesContent">
            <h2>Your Favorite Documents</h2>
            <?php
            require_once 'config.php';
            function getFavoriteDocuments($userID, $mysqli) {
                $query = "SELECT d.* FROM documents d
                        INNER JOIN user_favorites uf ON d.file_id = uf.file_id
                        WHERE uf.user_id = ? AND d.status != 'rejected'";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("i", $userID);
                $stmt->execute();
            
                $favoriteDocuments = array();
                $result = $stmt->get_result();
            
                while ($row = $result->fetch_assoc()) {
                    $favoriteDocuments[] = $row;
                }
            
                $stmt->close();
            
                return $favoriteDocuments;
            }            

            $userID = $_SESSION['userID'];
            $favoriteDocuments = getFavoriteDocuments($userID, $mysqli);

            if ($favoriteDocuments) {
                foreach ($favoriteDocuments as $document) {
                    $fileID = $document['file_id'];
                    $title = $document['title'];
                    $category = $document['category'];
                    $views = $document['infoViews'];
                    $file = $document['file'];

                    echo '<div class="document">';
                    echo "<h3>$title</h3>";
                    echo '<a href="document_details.php?fileID=' . $fileID . '&file=' . $file . '"><img src="img/document.jpg" alt="DocumentImage"></a>';
                    echo '</a>';
                    echo "<p>Category: $category</p>";
                    echo '<p><i class="far fa-eye"></i>' . $views . '</p>';
                }
            } else {
                echo "No favorite documents found.";
            }
            ?>
        </div>
    </div>  
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const uploadsTab = document.getElementById("uploadsTab");
            const favoritesTab = document.getElementById("favoritesTab");
            const uploadsContent = document.getElementById("uploadsContent");
            const favoritesContent = document.getElementById("favoritesContent");

            uploadsTab.classList.add("active");
            favoritesContent.style.display = "none";

            uploadsTab.addEventListener("click", function () {
                uploadsTab.classList.add("active");
                favoritesTab.classList.remove("active");
                uploadsContent.style.display = "block";
                favoritesContent.style.display = "none";
            });

            favoritesTab.addEventListener("click", function () {
                favoritesTab.classList.add("active");
                uploadsTab.classList.remove("active");
                favoritesContent.style.display = "block";
                uploadsContent.style.display = "none";
            });
        });
    </script>



</body>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</html>
