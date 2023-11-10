<?php
session_start();
require_once 'config.php';

// Query to retrieve all uploaded documents with additional information, including author status
$sql = "SELECT d.file_id, d.title, d.category, d.author, d.tags, d.infoViews, d.favorites, d.dateCreated, d.status, u.firstName, u.lastName, u.roles
        FROM documents d
        LEFT JOIN users u ON d.author = u.user_id";

$result = mysqli_query($mysqli, $sql);

if ($result) {
    $uploadedDocuments = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    die("Error: " . mysqli_error($mysqli));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="#">
    <title>View Documents</title>
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="admin_dashboard.php">
                <h1>Admin Dashboard</h1>
            </a>
        </div>
    </div>

    <div id="body">
        <h1>View Documents</h1>
        <input type="text" id="search" class="search-bar" placeholder="Search by title, author, or tags">
        <button onclick="searchDocuments()">Search</button>
        <button onclick="clearSearch()">Clear</button>
        <div>
            <table>
            <thead>
                <tr>
                    <th>File ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Tags</th>
                    <th>Views</th>
                    <th>Favorites</th>
                    <th>Date Created</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
                <tbody>
                    <?php
                    if (!empty($uploadedDocuments)) {
                        foreach ($uploadedDocuments as $document) {
                            echo "<tr>";
                            echo "<td>" . $document['file_id'] . "</td>";
                            echo "<td>" . $document['title'] . "</td>";
                            echo "<td>" . $document['category'] . "</td>";
                            $authorName = !empty($document['firstName']) ? $document['firstName'] . ' ' . $document['lastName'] : "Deleted User";
                            echo "<td>" . $authorName . "</td>";
                            echo "<td>" . $document['tags'] . "</td>";
                            echo "<td>" . $document['infoViews'] . "</td>";
                            echo "<td>" . $document['favorites'] . "</td>";
                            echo "<td>" . $document['dateCreated'] . "</td>";
                            echo "<td>" . $document['status'] . "</td>";
                            echo '<td><a href="view_details.php?fileID=' . $document['file_id'] . '">View</a> | <a href="delete_document.php?fileID=' . $document['file_id'] . '">Delete</a></td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>No documents found.</td></tr>";
                    }                    
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function searchDocuments() {
            var searchText = document.getElementById('search').value.toLowerCase();

            var rows = document.querySelectorAll('table tbody tr');
            rows.forEach(function (row) {
                var title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                var author = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                var tags = row.querySelector('td:nth-child(5)').textContent.toLowerCase();

                if (title.includes(searchText) || author.includes(searchText) || tags.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function clearSearch() {
            document.getElementById('search').value = '';
            searchDocuments();
        }
    </script>
</body>
</html>
