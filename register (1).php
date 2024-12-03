<?php
session_start();

include 'navbar.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = isset($_POST['username']) ? trim($_POST['username']) : false;
    $password = isset($_POST['Password']) ? $_POST['Password'] : false;
    $confirm_password = isset($_POST['confirm-password']) ? $_POST['confirm-password'] : false;
    $first_name = isset($_POST['first-name']) ? trim($_POST['first-name']) : false;
    $last_name = isset($_POST['last-name']) ? trim($_POST['last-name']) : false;
    $email = isset($_POST['email']) ? trim($_POST['email']) : false;
    $number = isset($_POST['number']) ? trim($_POST['number']) : false;

    // Validate required fields
    if (!$username || !$password || !$confirm_password || !$first_name || !$last_name || !$email || !$number) {
        echo "All fields are required!";
        exit;
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if the user already exists
        $checkQuery = $db->prepare("SELECT * FROM `users` WHERE username = ?");
        $checkQuery->execute([$username]);
        $existingUser = $checkQuery->fetch();

        if ($existingUser) {
            // If user exists, update their details
            $updateQuery = $db->prepare("UPDATE `users` SET password = ?, first_name = ?, last_name = ?, email = ?, number = ? WHERE username = ?");
            $updateQuery->execute([$hashed_password, $first_name, $last_name, $email, $number, $username]);
            echo "User details updated successfully!";
        } else {
            // If user does not exist, insert a new record
            $insertQuery = $db->prepare("INSERT INTO `users` (username, password, first_name, last_name, email, number) VALUES (?, ?, ?, ?, ?, ?)");
            $insertQuery->execute([$username, $hashed_password, $first_name, $last_name, $email, $number]);
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
            font-family: Arial, sans-serif;
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
            max-width: 1000px;
            margin: 40px auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .signup-image {
            flex: 1;
            background: url('fitness.png') no-repeat center/cover;
            filter: grayscale(30%);
            transition: filter 0.5s;
        }

        .signup-image:hover {
            filter: none;
        }

        .signup-form-container {
            flex: 1;
            padding: 40px;
        }

        .signup-form-container form {
            display: flex;
            flex-direction: column;
        }

        .signup-form-container label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .signup-form-container input,
        .signup-form-container select {
            margin-bottom: 15px;
            padding: 10px;
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
            transition: background 0.3s, transform 0.3s;
        }

        .signup-form-container input[type="submit"]:hover {
            background: #084298;
            transform: scale(1.05);
        }

        .more-details {
            margin: 40px auto;
            max-width: 1000px;
            text-align: center;
        }

        .more-details h3 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #0A369D;
        }

        .benefits, .feedback {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .benefits div, .feedback div {
            flex: 1;
            margin: 10px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #f9f9f9;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .benefits div:hover, .feedback div:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }


    </style>
</head>
<body>
    

    

    <div class="signup-container">
        <div class="signup-image"></div>
        <div class="signup-form-container">
            <p>Join our community and get the latest updates directly in your inbox.</p>
            <form action="#" method="post">
               	<label for="username">Username</label>
    			<input type="text" id="username" name="username" placeholder="Username" required>
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="first-name" placeholder="First Name" required>
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last-name" placeholder="Last Name" required>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Email Address" required>

                <label for="number">Phone Number</label>
				<input type="text" id="number" name="number" placeholder="Phone Number" required>
                <label for="Password">Password</label>
                <input type="password" id="Password" name="Password" placeholder="Password" required>
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm Password" required>
                <input type="submit" value="Sign Up">
            </form>
            <p>Already have an account? <a href="login.php"> Login</a> </p>
        </div>
    </div>

    <div class="more-details">
        <h3>Why Choose Mind & Motion?</h3>
        <div class="benefits">
            <div>
                <h4>Expert Knowledge in Wellness</h4>
                <p>Access a curated selection of self-improvement books, fitness equipment, and tech for wellness from top experts.</p>
            </div>
            <div>
                <h4>Boost Your Mental & Physical Health</h4>
                <p>Explore mental health resources, nutritional supplements, and fitness books designed to enhance your well-being.</p>
            </div>
            <div>
                <h4>Exclusive Products</h4>
                <p>Shop for premium nutritional books, motivation books, and fitness tools that align with your health goals.</p>
            </div>
        </div>

        <h3>Feedback from customers</h3>
        <div class="feedback">
            <div>
                <h4>Jane Doe</h4>
                <p>"The self-improvement books have been life-changing, and the fitness gear really helped me stay on track."</p>
            </div>
            <div>
                <h4>John Smith</h4>
                <p>"The mental health resources helped me understand myself better, and the nutritional supplements gave me a boost."</p>
            </div>
        </div>
    </div>

   
</body>
</html>
