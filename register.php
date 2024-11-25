<?php
session_start();

// Function to sanitize output for HTML display
function sanitizeOutput($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

try {
    // Include PHPHost.php using an absolute path
    require_once(__DIR__ . '/PHPHost.php'); // Adjust this path if necessary
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . $ex->getMessage();
    exit;
}

// Check if form is submitted
if (isset($_POST['submitted'])) {
    // Sanitize inputs
    $username = isset($_POST['username']) ? trim($_POST['username']) : false;
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : false;
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : false;
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : false;
    $number = isset($_POST['number']) ? trim($_POST['number']) : false;
    $email = isset($_POST['email']) ? trim($_POST['email']) : false;

    if (!$username || !$password || !$first_name || !$last_name || !$email) {
        echo "All fields are required!";
        exit;
    }

    try {
        // Check if the user already exists
        $checkQuery = $db->prepare("SELECT * FROM `users` WHERE username = ?");
        $checkQuery->execute([$username]);
        $existingUser = $checkQuery->fetch();

        if ($existingUser) {
            // If user exists, update their details
            $updateQuery = $db->prepare("UPDATE `users` SET password = ?, first_name = ?, last_name = ?, number = ?, email = ? WHERE username = ?");
            $updateQuery->execute([$password, $first_name, $last_name, $number, $email, $username]);
            echo "User details updated successfully!";
        } else {
            // If user does not exist, insert a new record
            $insertQuery = $db->prepare("INSERT INTO `users` (username, password, first_name, last_name, number, email) VALUES (?, ?, ?, ?, ?, ?)");
            $insertQuery->execute([$username, $password, $first_name, $last_name, $number, $email]);
            echo "New user registered successfully!";
        }

        // Set session and redirect
        $_SESSION["user"] = $username;
        header("Location: index.php");
        exit();
    } catch (PDOException $ex) {
        echo "Sorry, a database error occurred! <br>";
        echo "Error details: <em>" . sanitizeOutput($ex->getMessage()) . "</em>";
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
        input[type="password"],
        input[type="email"],
        input[type="number"] {
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
            color: #fff;
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
            <input type="password" id="password" name="password" required /><br>
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required /><br>
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" /><br>
            <label for="number">Phone Number:</label>
            <input type="number" id="number" name="number" /><br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required /><br><br>

            <input type="submit" value="Register or Update" />
            <input type="reset" value="Clear"/>
            <input type="hidden" name="submitted" value="true"/>
            <p>Already a member? <a href="login.php">Log in</a></p>
        </form>
    </div>
</body>
</html>
