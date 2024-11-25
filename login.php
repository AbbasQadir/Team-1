<?php
session_start();

if (isset($_POST['submitted'])) {
    // Check if username and password fields are set
    if (empty($_POST['username']) || empty($_POST['password'])) {
        exit('<p style="color:red">Please fill both the username and password fields!</p>');
    }

    // Include PHPHost.php for database connection
    try {
        require_once(__DIR__ . '/PHPHost.php');  // Corrected file name to PHPHost.php
    } catch (Exception $ex) {
        echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
        exit;
    }

    try {
        // Query to fetch user data from the database, selecting the 'user_id' and 'password' fields
        $stmt = $db->prepare('SELECT user_id, password FROM users WHERE username = ?');
        $stmt->execute([$_POST['username']]);

        if ($stmt->rowCount() > 0) {  
            $row = $stmt->fetch();

            // Verify the password
            if (password_verify($_POST['password'], $row['password'])) {
                $_SESSION['user'] = htmlspecialchars($_POST['username']); 
                $_SESSION['uid'] = $row['user_id'];  // Storing the 'user_id' in the session as 'uid'
                header("Location: index.php");  // Redirect to the index page after successful login
                exit(); 
            } else {
                echo "<p style='color:red'>Error logging in: Password does not match</p>";
            }
        } else {
            echo "<p style='color:red'>Error logging in: Username not found</p>";
        }
    } catch (PDOException $ex) {
        echo "<p style='color:red'>A database error occurred: " . htmlspecialchars($ex->getMessage()) . "</p>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" size="15" maxlength="25" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" size="15" maxlength="25" required>
            </div>
            <input type="submit" value="Login">
            <input type="hidden" name="submitted" value="TRUE">
            <p>Not a member? <a href="register.php">Register</a></p>
        </form>
    </div>
</body>
</html>
