<?php
// Include your database configuration
require_once 'config.php';

// Query to retrieve file modification history including title and author
$sql = "SELECT h.history_id, d.title, CONCAT(u.firstName, ' ', u.lastName) AS author, h.modifiedDate
        FROM history h
        JOIN documents d ON h.file_id = d.file_id
        JOIN users u ON h.user_id = u.user_id
        ORDER BY h.modifiedDate DESC"; // You can adjust the ORDER BY clause as needed

$result = mysqli_query($mysqli, $sql);

if ($result) {
    $historyRecords = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    die("Error: " . mysqli_error($mysqli));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="#">
    <title>File Modification History</title>
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
        <h1>File Modification History</h1>
        <div>
            <div>
                <input type="text" id="search" class="search-bar" placeholder="Search by author">
                <button onclick="searchUsers()">Search</button>
                <button onclick="clearSearch()">Clear</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>History ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Modified Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($historyRecords)) {
                        foreach ($historyRecords as $record) {
                            echo "<tr>";
                            echo "<td>" . $record['history_id'] . "</td>";
                            echo "<td>" . $record['title'] . "</td>";
                            echo "<td>" . $record['author'] . "</td>";
                            echo "<td>" . $record['modifiedDate'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No file modification history found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
    function searchUsers() {
        var searchValue = document.getElementById("search").value.toLowerCase();
        var rows = document.querySelectorAll("tbody tr");

        rows.forEach(function (row) {
            var authorCell = row.querySelector("td:nth-child(3)");
            var author = authorCell.textContent.toLowerCase();
            if (author.indexOf(searchValue) > -1) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    function clearSearch() {
        document.getElementById("search").value = "";
        searchUsers();
    }
</script>
</body>
</html>
