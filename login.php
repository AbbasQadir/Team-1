<?php
session_start();

if (isset($_POST['submitted'])) {
    if (!isset($_POST['username'], $_POST['password'])) {
        exit('Please fill both the username and password fields!');
    }

    require_once("PHPHOST.php");

    try {
        $stmt = $db->prepare('SELECT uid, password FROM users WHERE username = ?');
        $stmt->execute(array($_POST['username']));

        if ($stmt->rowCount() > 0) {  
            $row = $stmt->fetch();

            if (password_verify($_POST['password'], $row['password'])) {
                $_SESSION['user'] = htmlspecialchars($_POST['username']); 
                $_SESSION['uid'] = $row['uid']; 
                header("Location: loggedin.php"); 
                exit(); 
            } else {
                echo "<p style='color:red'>Error logging in, password does not match </p>";
            }
        } else {
            echo "<p style='color:red'>Error logging in, Username not found </p>";
        }
    } catch (PDOException $ex) {
        echo "Failed to connect to the database.<br>";
        echo $ex->getMessage();
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
            <p>View Projects without an account <a href="project.php">Projects</a></p>
        </form>
    </div>
</body>
</html>
