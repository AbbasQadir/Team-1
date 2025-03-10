<?php
session_start();



function sanitizeOutput($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

try {
    require_once(__DIR__ . '/PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . $ex->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : false;
    $password = isset($_POST['Password']) ? $_POST['Password'] : false;
    $confirm_password = isset($_POST['confirm-password']) ? $_POST['confirm-password'] : false;
    $first_name = isset($_POST['first-name']) ? trim($_POST['first-name']) : false;
    $last_name = isset($_POST['last-name']) ? trim($_POST['last-name']) : false;
    $email = isset($_POST['email']) ? trim($_POST['email']) : false;
    $number = isset($_POST['number']) ? trim($_POST['number']) : false;

    if (!$username || !$password || !$confirm_password || !$first_name || !$last_name || !$email || !$number) {
        echo "All fields are required!";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $checkQuery = $db->prepare("SELECT * FROM `users` WHERE username = ?");
        $checkQuery->execute([$username]);
        $existingUser = $checkQuery->fetch();

        if ($existingUser) {
            $updateQuery = $db->prepare("UPDATE `users` SET password = ?, first_name = ?, last_name = ?, email = ?, number = ? WHERE username = ?");
            $updateQuery->execute([$hashed_password, $first_name, $last_name, $email, $number, $username]);
            echo "User details updated successfully!";
        } else {
            $insertQuery = $db->prepare("INSERT INTO `users` (username, password, first_name, last_name, email, number) VALUES (?, ?, ?, ?, ?, ?)");
            $insertQuery->execute([$username, $hashed_password, $first_name, $last_name, $email, $number]);
            echo "New user registered successfully!";
        }

        $_SESSION["user"] = $username;
        header("Location: index.php");
        exit();
    } catch (PDOException $ex) {
        echo "Sorry, a database error occurred! <br>";
        echo "Error details: <em>" . sanitizeOutput($ex->getMessage()) . "</em>";
    }
}
include 'navbar.php';
?>
<link rel="stylesheet" href="homestyle.css">


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

h2, h3, h4 {
 color: var(--text-color);
 text-shadow: 2px 2px 5px var(--shadow);
}

h2 {
 flex-grow: 1;
 text-align: center;
 margin: 0;
 font-size: 2em;
 font-weight: bold;
}

h3 {
 text-align: center;
 margin: 30px 0 20px;
 font-size: 1.8em;
}

h4 {
 font-size: 1.4em;
 margin-bottom: 10px;
}


.signup-container {
 display: flex;
 max-width: 1000px;
 margin: 40px auto;
 border-radius: 12px;
 overflow: hidden;
 box-shadow: 0 4px 16px var(--shadow);
 background: var(--card-bg);
}


.signup-image {
 flex: 1;
 background: url('fitness.png') no-repeat center/cover;
 filter: grayscale(40%);
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

.signup-form-container p {
 margin-bottom: 20px;
 color: var(--secondary-text);
}

.signup-form-container label {
 margin-bottom: 5px;
 font-weight: bold;
 color: var(--text-color);
}

.signup-form-container input {
 margin-bottom: 15px;
 padding: 12px;
 font-size: 14px;
 border: 1px solid var(--border-color);
 border-radius: 5px;
 background-color: var(--bg-color);
 color: var(--text-color);
 width: 100%;
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
 color: var(--bg-color);
 transform: translateY(-2px);
}

.signup-form-container p:last-child {
 text-align: center;
 margin-top: 15px;
}

.signup-form-container p a {
 color: var(--text-color);
 text-decoration: none;
 font-weight: bold;
 transition: color 0.3s ease;
}

.signup-form-container p a:hover {
text-decoration: underline;
}


.more-details {
 margin: 40px auto;
 max-width: 1000px;
 padding: 30px;
 background-color: var(--card-bg);
 border-radius: 10px;
 box-shadow: 0 4px 15px var(--shadow);
}

.benefits, .feedback {
 display: flex;
 flex-wrap: wrap;
 justify-content: space-between;
 margin-top: 20px;
}

.benefits div, .feedback div {
 flex: 1;
 margin: 10px;
 padding: 20px;
 border-radius: 8px;
 background: var(--bg-color);
 box-shadow: 0 2px 8px var(--shadow);
 transition: transform 0.3s ease;
}

.benefits div:hover, .feedback div:hover {
 transform: translateY(-5px);
 box-shadow: 0 6px 20px var(--shadow);
}

@media (max-width: 768px) {
 .signup-container {
  flex-direction: column;
 }
 
 .signup-image {
  height: 250px;
 }
 
 .signup-form-container {
  padding: 20px;
 }
 
 .benefits, .feedback {
  flex-direction: column;
 }
 
 .benefits div, .feedback div {
  margin: 10px 0;
 }
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
<?php include 'footer.php'; ?>

   
</body>
</html>
