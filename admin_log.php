<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE){
	session_start();
}

 try {
        require_once(__DIR__ . '/PHPHost.php'); 
    } catch (Exception $ex) {
        echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
        exit;
    }

if ($_SERVER['REQUEST_METHOD']=='POST'){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $admin_username = 'admin';
    $admin_password = 'password';

    if ($username === $admin_username && $password === $admin_password){
        $_SESSION['admin'] = $username;
        header('Location: admin_dash.php');
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Admin Login</title>
        <link rel="stylesheet" href="styles1.css">
    </head>
    <body>
        <h2>Admin Login</h2>
        <form method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <br><br>
            <label for="password">Password:</lable>
            <input type="password" id="password" name="password" required>
            <br><br>
            <button type="submit">Login</button>
        </form>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </body>
</html>