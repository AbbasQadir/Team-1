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

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $number = $_POST['number'] ?? '';
        $email = $_POST['email'] ?? '';

        $updateStmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, number = ?, email = ? WHERE username = ?");
        $updateStmt->execute([$first_name, $last_name, $number, $email, $username]);

        header("Location: profile.php"); 
        exit();
    }
} catch (PDOException $ex) {
    echo "Database error: " . htmlspecialchars($ex->getMessage());
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

body.profile-page {
    background-color: var(--bg-color);
    color: var(--text-color);
    margin: 0;
    padding: 0;
}

.profile-page .container {
    max-width: 800px;
    margin: 50px auto;
    background: var(--card-bg);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px var(--shadow);
}

.profile-page h1 {
    text-align: center;
    color: var(--text-color);
}

.profile-info {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 15px;
    background-color: var(--card-bg);
    box-shadow: 0 1px 5px var(--shadow);
    margin-bottom: 20px;
}

.profile-info label {
    font-weight: bold;
    color: var(--secondary-text);
}

.profile-info input {
    width: 100%;
    padding: 8px;
    margin: 5px 0 10px 0;
    border: 1px solid var(--border-color);
    border-radius: 5px;
    background-color: var(--bg-color);
    color: var(--text-color);
    cursor: not-allowed;
}

.profile-info input.editable {
    background-color: var(--card-bg);
    cursor: text;
}

.profile-info button {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 10px;
}

.edit-button {
    background-color: var(--accent-color);
    color: var(--text-color);
}

.save-button {
    background-color: var(--accent-color);
    color: var(--text-color);
    display: none;
}

.edit-button:hover{
    background-color: var(--accent-hover);
    color: var(--bg-color);
}
.save-button:hover{
    background-color: var(--accent-hover);
    color: var(--bg-color);
}

.logout-button {
    display: block;
    text-align: center;
    padding: 10px;
    background-color: var(--icon-bg);
    color: var(--text-color);
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    margin-top: 20px;
    box-shadow: 0 2px 5px var(--shadow);
}

.logout-button:hover {
    background-color: var(--secondary-text);
    color: var(--bg-color);
}

.btn {
    background-color: var(--accent-color);
    color: var(--text-color);
    padding: 8px 18px;
    border-radius: 3px;
    border: none;  
    font-weight: bold;
    width: 150px; 
    text-align: center;
}


.btn:hover{
    background-color: var(--accent-hover);
    color: var(--bg-color);
    cursor: pointer;
    font-weight: bold
}

[data-theme="light"] .btn {
    background-color: var(--icon-bg);
    color: var(--text-color);
}

[data-theme="light"] .btn:hover {
    background-color: var(--secondary-text);
    color: var(--bg-color);
}

[data-theme="light"] .edit-button {
    background-color: var(--icon-bg);
    color: var(--text-color);
}

[data-theme="light"] .edit-button:hover {
    background-color: var(--secondary-text);
    color: var(--bg-color);
}

[data-theme="light"] .save-button {
    background-color: var(--icon-bg);
    color: var(--text-color);
}

[data-theme="light"] .save-button:hover {
    background-color: var(--secondary-text);
    color: var(--bg-color);
}


    </style>

    <script>
        function toggleEditMode() {
            let inputs = document.querySelectorAll('.profile-info input');
            let editButton = document.getElementById('edit-button');
            let saveButton = document.getElementById('save-button');

            inputs.forEach(input => {
                if (input.hasAttribute('readonly')) {
                    input.removeAttribute('readonly');
                    input.classList.add('editable');
                } else {
                    input.setAttribute('readonly', 'true');
                    input.classList.remove('editable');
                }
            });

            editButton.style.display = 'none';
            saveButton.style.display = 'block';
        }
    </script>
</head>
<body class="profile-page">
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>

        <form method="post" action="profile.php">
            <div class="profile-info">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" readonly>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" readonly>

                <label for="number">Phone Number:</label>
                <input type="text" id="number" name="number" value="<?php echo htmlspecialchars($user['number'] ?? ''); ?>" readonly>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>

                <button type="button" class="edit-button" id="edit-button" onclick="toggleEditMode()">Edit Profile</button>
                <button type="submit" class="save-button" id="save-button" name="save">Save Changes</button>
            </div>
        </form>

        <div class="section">
            <h2>Your Cart</h2>
            <button class="btn" onclick="window.location.href='Basket.php'">Go to Cart</button>
        </div>

        <div class="section">
            <h2>Orders</h2>
            <button class="btn" onclick="window.location.href='previous_orders.php'">View your Orders</button>
        </div>

        <a href="logout.php" class="logout-button">Log Out</a>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>
