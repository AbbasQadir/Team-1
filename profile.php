<?php

session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}


// Include database connection
try {
    require_once('PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage());
    exit();
}

// Fetch user details
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        /* Profile Page Styles */
        body.profile-page {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .profile-page .container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-page h1 {
            text-align: center;
            color: #333;
        }

        .profile-page .section {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #fafafa;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

        .profile-page .section h2 {
            margin-top: 0;
            font-size: 18px;
            color: #555;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .profile-page .profile-info {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #fafafa;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .profile-page .profile-info p {
            margin: 5px 0;
            font-size: 16px;
            color: #444;
        }

        .profile-page .section p {
            font-size: 16px;
            color: #666;
            text-align: center;
            margin-bottom: 15px;
        }

        .profile-page .section button {
            display: block;
            margin: 0 auto;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .profile-page .section button:hover {
            background-color: #0056b3;
        }

        .profile-page .logout-button {
            display: block;
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #d9534f;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .profile-page .logout-button:hover {
            background-color: #c9302c;
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

        <!-- Cart Section -->
        <div class="section">
            <h2>Your Cart</h2>
            <p>Cart functionality coming soon!</p>
            <!-- Button with a dead link for cart -->
            <button onclick="window.location.href='Basket.php'">Go to Cart</button>
        </div>

        <!-- Current Orders Section -->
        <div class="section">
            <h2>Current Orders</h2>
            <p>Current orders functionality coming soon!</p>
            <!-- Button with a dead link for current orders -->
            <button onclick="window.location.href='#'">View Current Orders</button>
        </div>

        <!-- Previous Orders Section -->
        <div class="section">
            <h2>Previous Orders</h2>
            <p>Previous orders functionality coming soon!</p>
            <!-- Button with a dead link for previous orders -->
            <button onclick="window.location.href='previous_orders.php'">View Previous Orders</button>
        </div>

        <a href="logout.php" class="logout-button">Log Out</a>
    </div>
</body>
</html>

