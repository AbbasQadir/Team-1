<?php

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}


try {
    require_once('PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage());
    exit();
}


try {
    $username = $_SESSION['user'];
    $stmt = $db->prepare("SELECT username, first_name, last_name, number, email FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found!";
        exit();
    }
} catch (PDOException $ex) {
    echo "Database error occurred: " . htmlspecialchars($ex->getMessage());
    exit();
}
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body.profile-page {
            
            background-color: white;
            margin: 0;
            padding: 0;
        }

        .profile-page .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-page h1 {
            text-align: center;
            color: black;
        }

        .profile-page .section {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: white;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

        .profile-page .section h2 {
            margin-top: 0;
            font-size: 18px;
            color: black;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .profile-page .profile-info {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: white;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .profile-page .profile-info p {
            margin: 5px 0;
            font-size: 16px;
            color: black;
        }

        .profile-page .section p {
            font-size: 16px;
            color: black;
            text-align: center;
            margin-bottom: 15px;
        }

        .profile-page .section button {
            display: block;
            margin: 10px auto;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #084298;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        	font-family: merriweather, serif;
        }

        .profile-page .section button:hover {
            background-color: #b8c5d4;
    		color: #084298;
    		box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.1);
        }

        .profile-page .logout-button {
            display: block;
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #084298;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .profile-page .logout-button:hover {
            background-color: #b8c5d4;
   			color: #084298;
    		box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="profile-page">
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
        <div class="profile-info">
            <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['first_name'] ?? 'N/A'); ?></p>
            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['last_name'] ?? 'N/A'); ?></p>
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($user['number'] ?? 'N/A'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
        </div>

        <div class="section">
            <h2>Your Cart</h2>
            <button onclick="window.location.href='Basket.php'">Go to Cart</button>
        </div>



        <div class="section">
            <h2>Orders</h2>
            <button onclick="window.location.href='previous_orders.php'">View your Orders</button>
        </div>

        <a href="logout.php" class="logout-button">Log Out</a>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>