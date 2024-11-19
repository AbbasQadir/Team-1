<?php
session_start();

function sanitizeInput($input) {
    return $GLOBALS['db']->quote($input);
}

function sanitizeOutput($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

if (isset($_POST['submitted'])) {
    require_once('PHPHOST.php');

    $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : false;
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : false;

    if (!$username) {
        echo "Username is required!";
        exit;
    }
    if (!$password) {
        exit("Password is required!");
    }

    try {
        $stat = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stat->execute(array($username, $password));
        $_SESSION["user"] = $username;

        header("Location: loggedin.php"); 
        exit(); 
    } catch (PDOException $ex) {
        echo "Sorry, a database error occurred! <br>";
        echo "Error details: <em>" . $ex->getMessage() . "</em>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registration System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #333;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-container {
            max-width: 400px;
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"],
        input[type="reset"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #f5f5f5;
            color: #000;
            cursor: pointer;
        }

        input[type="submit"]:hover,
        input[type="reset"]:hover {
            background-color: #333;
        }

        .form-container p {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 0;
        }

        .form-container p a {
            color: #007bff;
            text-decoration: none;
        }

        .form-container p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Register</h2>
        <form method="post" action="register.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required /><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required /><br><br>

            <input type="submit" value="Register" /> 
            <input type="reset" value="Clear"/>
            <input type="hidden" name="submitted" value="true"/>
            <p>Already a member? <a href="login.php">Log in</a></p>
        </form>  
    </div>
</body>
</html>
