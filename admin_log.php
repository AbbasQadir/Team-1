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

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        //gets admin detaisl from db
        $stmt = $db->prepare("SELECT id, username, password, role FROM admins WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true); 
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['role'] = $admin['role'];

            header('Location: admin-dashboard.php');
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } else {
        $error = "Both fields are required!";
    }
}
include 'navbar.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!--<link rel="stylesheet" href="styles1.css">-->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        /*body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background: #E0E1DD;
        }*/
        .form-container {
            display: flex;
            width: 80vw;
            max-width: 1100px;
            min-height: 600px;
            margin: 40px auto;
            border-radius: 15px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            background: rgba(27, 38, 59, 0.9);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .form-image {
            flex: 1;
            background: url('/images/m_m.png') no-repeat center/cover;
            opacity: 0.9;
            transition: opacity 0.3s ease-in-out;
        }
        .form-image:hover {
            opacity: 1;
        }
        .form-content {
            flex: 1.2;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #E0E1DD;
        }
        .form-content h2{
            text-align: center;
            font-size: 28px;
            margin-bottom: 20px;
            color: #E0E1DD;
            padding: 10px;
            border-radius: 8px;
            text-transform: uppercase;
        }
        .form-content form {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 400px;
        }
        .form-content label{
            font-weight: bold;
            margin-bottom: 5px;
            color: rgba(224, 225, 221, 0.7);
        }
        .form-content input[type="text"],
        .form-content input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.2);
            color: #E0E1DD;
            font-size: 16px;
            outline: none;
        }
        .form-content input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .form-content button {
            background: #415A77;
            color: #E0E1DD;
            border: none;
            padding: 12px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
            width: 100%;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .form-content button:hover {
            background: #778DA9;
            color: #0D1B2A;
            transform: scale(1.05);
        }
        .form-content p {
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
            color: #FF4C29;
        }
        .success {
            color: #28A745;
        }
		.form-error {
            color: #FF4C29;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-image"></div>
        <div class="form-content">
            <h2>Admin Login</h2>
            <form method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <br><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <br><br>
                <button type="submit">Login</button>
            </form>
            <?php if (isset($error)) echo "<p>$error</p>"; ?>
        </div>
    </div>
</body>
            <?php include 'footer.php'?>
</html>
