<?php
// Include your database connection logic here

// Function to execute SQL queries
function executeQuery($query)
{
    // Implement your database connection logic here
    $conn = new mysqli("localhost", "root", "", "opensource");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $result = $conn->query($query);

    $conn->close();

    return $result;
}

// Total views across all days
$totalViewsQuery = "SELECT SUM(infoViews) AS TotalViews FROM documents";
$totalViewsResult = executeQuery($totalViewsQuery);

// Favorites
$favoritesQuery = "SELECT COUNT(*) AS TotalFavorites FROM user_favorites";
$favoritesResult = executeQuery($favoritesQuery);

// Ratings distribution
$ratingsDistributionQuery = "SELECT rating, COUNT(*) AS RatingCount FROM ratings GROUP BY rating";
$ratingsDistributionResult = executeQuery($ratingsDistributionQuery);

// Documents per category
$documentsPerCategoryQuery = "SELECT category, COUNT(*) AS DocumentsCount FROM documents GROUP BY category";
$documentsPerCategoryResult = executeQuery($documentsPerCategoryQuery);

// Total Approvals
$totalApprovalsQuery = "SELECT COUNT(*) AS TotalApprovals FROM notifications WHERE action = 'Approved'";
$totalApprovalsResult = executeQuery($totalApprovalsQuery);

// Total Rejects
$totalRejectsQuery = "SELECT COUNT(*) AS TotalRejects FROM notifications WHERE action = 'Rejected'";
$totalRejectsResult = executeQuery($totalRejectsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
</head>
<body>
    <div id="title">
        <a href="admin_index.php">
            <h1>Admin Dashboard</h1>
        </a>
    </div>
    <h1>Admin Analytics</h1>
 
    <!-- Views -->
    <h2>Total Views:</h2>
    <p><?php echo $totalViewsResult->fetch_assoc()['TotalViews']; ?></p>

    <!-- Favorites -->
    <h2>Total Favorites:</h2>
    <p><?php echo $favoritesResult->fetch_assoc()['TotalFavorites']; ?></p>

    <!-- Ratings distribution -->
    <h2>Ratings distribution:</h2>
    <table border="1">
        <tr>
            <th>Rating</th>
            <th>Count</th>
        </tr>
        <?php while ($row = $ratingsDistributionResult->fetch_assoc()) : ?>
            <tr>
                <td><?php echo $row['rating']; ?></td>
                <td><?php echo $row['RatingCount']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Documents per category -->
    <h2>Documents per category:</h2>
    <table border="1">
        <tr>
            <th>Category</th>
            <th>Count</th>
        </tr>
        <?php while ($row = $documentsPerCategoryResult->fetch_assoc()) : ?>
            <tr>
                <td><?php echo $row['category']; ?></td>
                <td><?php echo $row['DocumentsCount']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Total Approvals -->
    <h2>Total Approvals:</h2>
    <p><?php echo $totalApprovalsResult->fetch_assoc()['TotalApprovals']; ?></p>

    <!-- Total Rejects -->
    <h2>Total Rejects:</h2>
    <p><?php echo $totalRejectsResult->fetch_assoc()['TotalRejects']; ?></p>
</body>
</html>


