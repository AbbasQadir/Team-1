<?php
session_start(); 

if (!isset($_SESSION["user"])) {
 
    header("location: login.php");
    exit(); 
}


$username = $_SESSION["user"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 100px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            margin-top: 0;
        }

        p {
            margin: 10px 0;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>You are now logged in.</p>
        <p><a href="index.php">View Projects</a></p>
        <p>Click <a href="login.php">here</a> to logout.</p>
    </div>
</body>
</html>
