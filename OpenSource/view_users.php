<?php
session_start();
require_once 'config.php'; // Include your database connection configuration

// Query to retrieve user information, excluding the admin user
$sql = "SELECT user_id, firstName, lastName, email, roles FROM users WHERE user_id != 0";

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
            <a href="admin_dashboard.php">
                <h1>Admin Dashboard</h1>
            </a>
        </div>
    </div>

    <div id="body">
        <h1>View Users</h1>
        <input type="text" id="search" class="search-bar" placeholder="Search by name or email">
        <button onclick="searchUsers()">Search</button>
        <button onclick="clearSearch()">Clear</button>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
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

                            // Add the "View Uploaded Files" and "Delete" buttons
                            echo '<td>';
                            echo '<a href="uploaded_files.php?user_id=' . $user['user_id'] . '">View Uploaded Files</a>';
                            echo ' | ';
                            echo '<a href="delete_user.php?user_id=' . $user['user_id'] . '">Delete</a>';
                            echo '</td>';

                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function searchUsers() {
            var searchText = document.getElementById('search').value.toLowerCase();

            var rows = document.querySelectorAll('table tbody tr');
            rows.forEach(function (row) {
                var name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                var email = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                if (name.includes(searchText) || email.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function clearSearch() {
            document.getElementById('search').value = '';
            searchUsers(); // Clear the search results
        }
    </script>
</body>
</html>
