<?php
session_start();



if (isset($_POST['submitted'])) {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        exit('<p style="color:red">Please fill both the username and password fields!</p>');
    }

    try {
        require_once(__DIR__ . '/PHPHost.php');  // Corrected file name to PHPHost.php
    } catch (Exception $ex) {
        echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
        exit;
    }

    try {
        $stmt = $db->prepare('SELECT user_id, password FROM users WHERE username = ?');
        $stmt->execute([$_POST['username']]);

        if ($stmt->rowCount() > 0) {  
            $row = $stmt->fetch();

            if (password_verify($_POST['password'], $row['password'])) {
                $_SESSION['user'] = htmlspecialchars($_POST['username']); 
                $_SESSION['uid'] = $row['user_id'];  
                header("Location: index.php");  
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
include 'navbar.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mind & Motion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Merriweather', serif;
            line-height: 1.6;
            scroll-behavior: smooth;
        }

        h2 {
            flex-grow: 1;
            text-align: center;
            margin: 0;
        }

        .signup-container {
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 500px;
            margin: 40px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .signup-form-container {
            width: 350px;
        }

        .signup-form-container form {
            display: flex;
            flex-direction: column;
        }

        .signup-form-container label {
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .signup-form-container input {
            margin-bottom: 20px;
            padding: 12px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        .signup-form-container input[type="submit"] {
            background: #0A369D;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 12px;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .signup-form-container input[type="submit"]:hover {
            background: #084298;
            transform: translateY(-2px);
        }

        .signup-form-container p {
            text-align: center;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .signup-form-container p a {
            color: #0A369D;
            text-decoration: none;
            font-weight: bold;
        }

        .admin-login {
            text-align: center;
            margin-top: 10px;
        }

        .admin-login a {
            display: inline-block;
            padding: 10px 20px;
            font-size: 14px;
            background: #084298;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .admin-login a:hover {
            background: #0A369D;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-form-container">
            <form action="login.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" size="15" maxlength="25" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" size="15" maxlength="25" required>
                <input type="submit" value="Login">
                <input type="hidden" name="submitted" value="TRUE">
                <p>Not a member? <a href="register.php">Register</a></p>
            </form>
            <div class="admin-login">
                <a href="admin_log.php">Admin Login</a>
            </div>
        </div>
    </div>
</body>
                <?php include 'footer.php'; ?>

</html>
