<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="#">
    <title>Login</title>
</head>
<body>
    <section>
        <div class="formBox">
            <div class="formValue">

                <?php
                require_once 'config.php';
                session_start();

                // Check if the form is submitted
                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    // Retrieve the submitted email and password
                    $email = $_POST["email"];
                    $password = $_POST["password"];

                    // Retrieve the stored hashed password and user_id from the database
                    $sql = "SELECT password, user_id, firstName, roles FROM users WHERE BINARY email = '$email'";
                    $result = mysqli_query($mysqli, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $storedHashedPassword = $row['password'];
                        $userID = $row['user_id'];
                        $firstName = $row['firstName']; // Retrieve the first name
                        $role = $row['roles']; // Retrieve the user's role (e.g., admin)

                        // Verify the submitted password against the stored hashed password
                        if (password_verify($password, $storedHashedPassword)) {
                            // Password is correct, proceed with login
                            // Store the user's email, userID, and role in the session for later use
                            $_SESSION["email"] = $email;
                            $_SESSION["userID"] = $userID;
                            $_SESSION["firstName"] = $firstName;
                            $_SESSION["role"] = $role;

                            // Redirect the user based on their role
                            if ($role === 'admin') {
                                // Redirect the admin to the admin dashboard
                                header("Location: admin_index.php");
                            } else {
                                // Redirect regular users to the main dashboard page
                                header("Location: index.php");
                            }
                            exit();
                        } else {
                            // Incorrect password
                            echo '<div class="box">
                                    <h1>Invalid Email or Password!</h1>
                                    <a class="button" href="login.php">Try Again</a>
                                </div>';
                            exit();
                        }
                    } else {
                        // User not found
                        echo '<div class="box">
                                <h1>Invalid Email or Password!</h1>
                                <a class="button" href="login.php">Try Again</a>
                            </div>';
                        exit();
                    }
                }

                // Display the error message if login is not successful
                if (isset($errorMessage)) {
                    echo $errorMessage;
                }
                ?>


                <form action="login.php" method="post" class="form">
                    <h2>Login</h2>
                    <div class="inputbox">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="email" name="email" required>
                        <label for="">Email</label>
                    </div>
                    <div class="inputbox">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" name="password" required>
                        <label for="">Password</label>
                    </div>
                    <button type="submit">Log in</button>
                    <div class="register">
                        <p>Don't Have an account? <a href="register.php">Register</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
