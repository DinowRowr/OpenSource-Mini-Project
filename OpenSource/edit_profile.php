<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

$userID = $_SESSION['userID'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];

    // Perform input validation and error handling as needed

    // Update the user's profile information in the database
    $updateProfileSQL = "UPDATE users SET firstName = '$firstName', lastName = '$lastName', email = '$email' WHERE user_id = $userID";
    if (mysqli_query($mysqli, $updateProfileSQL)) {
        // Profile updated successfully
        header("Location: profile.php");
        exit();
    } else {
        // Handle the case where the update fails
        $error = "Failed to update profile. Please try again.";
    }
}

// Retrieve the user's current profile information
$profileSQL = "SELECT * FROM users WHERE user_id = $userID";
$profileResult = mysqli_query($mysqli, $profileSQL);
$profile = mysqli_fetch_assoc($profileResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile</title>
</head>
<body>
    <h1>Edit Profile</h1>
    <?php if (isset($error)) { ?>
        <p><?php echo $error; ?></p>
    <?php } ?>

    <form method="post" action="edit_profile.php">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo $profile['firstName']; ?>" required>
        <br>

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo $profile['lastName']; ?>" required>
        <br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $profile['email']; ?>" required>
        <br>

        <input type="submit" name="save" value="Save Changes">
    </form>
</body>
</html>
