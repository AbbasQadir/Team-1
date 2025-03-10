<?php

session_start();



if (isset($_POST['submitted'])) {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        exit('<p style="color:red">Please fill both the username and password fields!</p>');
    }

    try {
        require_once(__DIR__ . '/PHPHost.php');  
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
   
   :root {
  --bg-color: #0d1b2a;
  --text-color: #e0e1dd;
  --secondary-text: #778da9;
  --card-bg: #1b263b;
  --icon-bg: #415a77;
  --border-color: #415a77;
  --shadow: rgba(0, 0, 0, 0.3);
  --accent-color: #778da9;
  --accent-hover: #a8b2c8;
}

[data-theme="light"] {
  --bg-color: #e0e1dd;
  --text-color: #1b263b;
  --secondary-text: #415a77;
  --card-bg: #f1f3f5;
  --icon-bg: #a8b2c8;
  --border-color: #a8b2c8;
  --shadow: rgba(0, 0, 0, 0.1);
  --accent-color: #415a77;
  --accent-hover: #778da9;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

body {
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background-color: var(--bg-color);
  color: var(--text-color);
  font-size: 18px;
  line-height: 1.6;
  transition: background 0.3s ease, color 0.3s ease;
}

h2 {
  flex-grow: 1;
  text-align: center;
  margin: 0;
  font-size: 2em;
  font-weight: bold;
  color: var(--text-color);
  text-shadow: 2px 2px 5px var(--shadow);
}

.signup-container {
  display: flex;
  justify-content: center;
  align-items: center;
  max-width: 500px;
  margin: 40px auto;
  padding: 30px;
  background-color: var(--card-bg);
  border-radius: 10px;
  box-shadow: 0 4px 15px var(--shadow);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.signup-container:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px var(--shadow);
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
  font-size: 16px;
  color: var(--text-color);
}

.signup-form-container input {
  margin-bottom: 20px;
  padding: 12px;
  font-size: 16px;
  border: 1px solid var(--border-color);
  border-radius: 5px;
  width: 100%;
  background-color: var(--bg-color);
  color: var(--text-color);
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.signup-form-container input:focus {
  outline: none;
  border-color: var(--accent-color);
  box-shadow: 0 0 8px var(--shadow);
}

.signup-form-container input[type="submit"] {
  background: var(--icon-bg);
  color: var(--text-color);
  border: none;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
  padding: 12px;
  transition: background 0.3s ease, transform 0.2s ease;
  border-radius: 5px;
}

.signup-form-container input[type="submit"]:hover {
  background: var(--accent-hover);
  transform: translateY(-2px);
  color: var(--bg-color);
}

.signup-form-container p {
  text-align: center;
  font-size: 14px;
  margin-bottom: 10px;
  color: var(--secondary-text);
}

.signup-form-container p a {
  color: var(--accent-color);
  text-decoration: none;
  font-weight: bold;
  transition: color 0.3s ease;
}

.signup-form-container p a:hover {
  color: var(--accent-hover);
  text-decoration: underline;
}

.admin-login {
  text-align: center;
  margin-top: 20px;
}
span{
    color: var(--text-color);
    
}
.admin-login a {
  display: inline-block;
  padding: 10px 20px;
  font-size: 14px;
  background: var(--icon-bg);
  color: var(--text-color);
  text-decoration: none;
  border-radius: 5px;
  font-weight: bold;
  transition: background 0.3s ease, transform 0.2s ease;
}

.admin-login a:hover {
  background: var(--accent-hover);
  transform: translateY(-2px);
  color: var(--bg-color);
}

   </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-form-container">
            <form action="login.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" size="15" maxlength="25" required placeholder="Enter your username">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" size="15" maxlength="25" required placeholder="Enter your password">
                <input type="submit" value="Login">
                <input type="hidden" name="submitted" value="TRUE">
                <p>Not a member? <a href="register.php"><span>Register</span></a></p>
            </form>
            <div class="admin-login">
                <a href="admin_log.php">Admin Login</a>
            </div>
        </div>
    </div>
</body>
                <?php include 'footer.php'; ?>

</html>
