<?php
session_start();
require_once 'config.php';

// Query to retrieve user information, excluding the admin user
$sql = "SELECT user_id, firstName, lastName, email, roles, created_at FROM users WHERE user_id != 0";

$result = mysqli_query($mysqli, $sql);

if ($result) {
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    die("Error: " . mysqli_error($mysqli));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="#">
    <title>View Users</title>
</head>
<body>
    <div id="header">
        <div id="title">
            <a href="admin_index.php">
                <h1>Admin Dashboard</h1>
            </a>
        </div>
    </div>
    <h1>View Users</h1>

    <!-- Add a search bar for author names -->
    <form id="authorSearchForm">
        <label for="authorSearch">Search by Author:</label>
        <input type="text" id="authorSearch" placeholder="Enter author's name">
        <button type="button" onclick="searchByAuthor()">Search</button>
        <button type="button" onclick="clearAuthorSearch()">Clear</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Account Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($users)) {
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td>" . $user['user_id'] . "</td>";
                    echo "<td>" . $user['firstName'] . "</td>";
                    echo "<td>" . $user['lastName'] . "</td>";
                    echo "<td>" . $user['email'] . "</td>";
                    echo "<td>" . $user['created_at'] . "</td>"; // Display the created_at value
                    echo '<td>';
                    echo '<a href="uploaded_files.php?user_id=' . $user['user_id'] . '">View Uploaded Files</a>';
                    echo ' | ';
                    echo '<a href="delete_user.php?user_id=' . $user['user_id'] . '">Delete</a>';
                    echo '</td>';
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No users found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Add JavaScript for author search -->
    <script>
        function searchByAuthor() {
            var authorSearchText = document.getElementById('authorSearch').value.toLowerCase();

            var rows = document.querySelectorAll('table tbody tr');
            rows.forEach(function (row) {
                var authorName = (row.querySelector('td:nth-child(2)').textContent + ' ' + row.querySelector('td:nth-child(3)').textContent).toLowerCase();

                if (authorName.includes(authorSearchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function clearAuthorSearch() {
            document.getElementById('authorSearch').value = '';
            searchByAuthor(); // Clear the search results
        }
    </script>
</body>
</html>
