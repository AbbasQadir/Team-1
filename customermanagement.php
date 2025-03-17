<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_log.php'); 
    exit();
}

try {
    require_once(__DIR__ . '/PHPHost.php');
} catch (Exception $ex) {
    echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
    exit;
}
include 'sidebar.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_user'])) {
        $id = $_POST['user_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $number = $_POST['number'];  
        $email = $_POST['email'];

        try {
            $updateStmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, number = ?, email = ? WHERE user_id = ?");
            $updateStmt->execute([$first_name, $last_name, $number, $email, $id]);
            $message = "User updated successfully!";
        } catch (PDOException $ex) {
            $error = "Failed to update user: " . htmlspecialchars($ex->getMessage());
        }
    }

    if (isset($_POST['delete_user'])) {
        $id = $_POST['user_id'];

        try {
            $deleteStmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
            $deleteStmt->execute([$id]);
            $message = "User deleted successfully!";
        } catch (PDOException $ex) {
            $error = "Failed to delete user: " . htmlspecialchars($ex->getMessage());
        }
    }

    if (isset($_POST['add_user'])) {
        $username   = $_POST['username'];
        $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $first_name = $_POST['first_name'];
        $last_name  = $_POST['last_name'];
        $number     = $_POST['number'];
        $email      = $_POST['email'];

        try {
            $addStmt = $db->prepare("INSERT INTO users (username, password, first_name, last_name, number, email) VALUES (?, ?, ?, ?, ?, ?)");
            $addStmt->execute([$username, $password, $first_name, $last_name, $number, $email]);
            $message = "User added successfully!";
        } catch (PDOException $ex) {
            $error = "Failed to add user: " . htmlspecialchars($ex->getMessage());
        }
    }
}


$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page   = isset($_GET['page'])   ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$limit  = 12;
$offset = ($page - 1) * $limit;

if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $stmtCount = $db->prepare("SELECT COUNT(*) FROM users WHERE username LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR number LIKE ? OR email LIKE ?");
    $stmtCount->execute([$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
    $totalRows = $stmtCount->fetchColumn();

    $stmt = $db->prepare("SELECT user_id, username, first_name, last_name, number, email 
                          FROM users 
                          WHERE username LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR number LIKE ? OR email LIKE ?
                          ORDER BY user_id ASC 
                          LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $searchParam, PDO::PARAM_STR);
    $stmt->bindValue(2, $searchParam, PDO::PARAM_STR);
    $stmt->bindValue(3, $searchParam, PDO::PARAM_STR);
    $stmt->bindValue(4, $searchParam, PDO::PARAM_STR);
    $stmt->bindValue(5, $searchParam, PDO::PARAM_STR);
    $stmt->bindValue(6, $limit, PDO::PARAM_INT);
    $stmt->bindValue(7, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmtCount = $db->query("SELECT COUNT(*) FROM users");
    $totalRows = $stmtCount->fetchColumn();

    $stmt = $db->prepare("SELECT user_id, username, first_name, last_name, number, email 
                          FROM users 
                          ORDER BY user_id ASC 
                          LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$totalPages = ceil($totalRows / $limit);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Management</title>
    <link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="admin-dashboard.css">

    <style>
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        
        h2, h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-container input[type="text"] {
            padding: 8px;
            width: 250px;
            border: 1px solid #414A77;
            border-radius: 4px;
        }
        .search-container button {
            background-color: #414A77;
            color: white;
            border: none;
            padding: 8px 16px;
            margin-left: 5px;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-container button:hover {
            background-color: #5a5f9d;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
            background-color: var(--card-bg);
        }
        
        table th, table td {
            border: 1px solid #414A77;
            padding: 10px;
            text-align: center;
        }
        
        form {
            margin: 20px auto;
            max-width: 800px;
        }
        
        label {
            display: block;
            margin: 10px 0 5px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 8px;
            border: 1px solid #414A77;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 10px;
        }
        
        button {
            background-color: #414A77;
            color: white;
            border: none;
            padding: 8px 16px;
            margin: 4px 2px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        button:hover {
            background-color: #778DA9;
        }
        
        p {
            text-align: center;
        }
        
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            text-decoration: none;
            margin: 0 5px;
            padding: 8px 12px;
            background-color: #414A77;
            color: white;
            border-radius: 4px;
        }
        .pagination a:hover {
            background-color: #5a5f9d;
        }
        .pagination .active {
            background-color: #5a5f9d;
        }
        
        @media screen and (max-width: 600px) {
            table {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <h2>Customer Management</h2>
    
    <!-- Display Messages -->
    <?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <!-- Search Bar -->
    <div class="search-container">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>
    
    <!-- User Table -->
    <div class="table-container">
        <h3>Existing Users</h3>
        <table>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php if ($users): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <form method="POST">
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                            </td>
                            <td>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                            </td>
                            <td>
                                <input type="text" name="number" value="<?php echo htmlspecialchars($user['number']); ?>">
                            </td>
                            <td>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                            </td>
                            <td>
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="update_user">Update</button>
                                <button type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No users found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php 
                $queryString = !empty($search) ? "search=" . urlencode($search) . "&" : "";
            ?>
            <?php if ($page > 1): ?>
                <a href="?<?php echo $queryString; ?>page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>
            
            <?php 
            for ($i = 1; $i <= $totalPages; $i++): 
                if ($i == $page):
            ?>
                    <a class="active" href="?<?php echo $queryString; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php else: ?>
                    <a href="?<?php echo $queryString; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?<?php echo $queryString; ?>page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 40px;"></div>
    
    <h3>Add New User</h3>
    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" required>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" required>
        
        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" required>
        
        <label for="number">Phone:</label>
        <input type="text" name="number" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        
        <button type="submit" name="add_user">Add User</button>
    </form>
</body>
</html>
