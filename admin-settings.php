<?php
session_start(); 


include 'PHPHost.php'; 

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_log.php"); 
    exit();
}


$admin_id = $_SESSION['admin_id'];
$query = "SELECT role FROM admins WHERE id = ?";
$stmt = $db->prepare($query);  // Using `$db` for PDO
$stmt->execute([$admin_id]);
$admin_data = $stmt->fetch(PDO::FETCH_ASSOC);
$admin_role = $admin_data['role'];

include 'sidebar.php';


$edit_mode = false;
$admin_to_edit = null;


if(isset($_GET['edit']) && $admin_role === 'super_admin') {
    $edit_id = $_GET['edit'];
    $query = "SELECT id, username, email, role FROM admins WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$edit_id]);
    $admin_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($admin_to_edit) {
        $edit_mode = true;
    }
}


if(isset($_POST['update_admin']) && $admin_role === 'super_admin') {
    $update_id = $_POST['admin_id'];
    $admin_username = htmlspecialchars($_POST['admin_username']);
    $admin_email = htmlspecialchars($_POST['admin_email']);
    $admin_role_update = $_POST['admin_role'];
    
    
    if(!empty($_POST['admin_password']) && !empty($_POST['admin_confirm_password'])) {
        
        if($_POST['admin_password'] === $_POST['admin_confirm_password']) {
            $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
            $query = "UPDATE admins SET username = ?, email = ?, password = ?, role = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$admin_username, $admin_email, $admin_password, $admin_role_update, $update_id]);
        } else {
            $admin_error_message = "Passwords do not match!";
        }
    } else {
        
        $query = "UPDATE admins SET username = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$admin_username, $admin_email, $admin_role_update, $update_id]);
    }
    
    if(!isset($admin_error_message)) {
        $admin_success_message = "Admin account updated successfully!";
        $edit_mode = false; 
    }
}


if(isset($_POST['create_admin']) && $admin_role === 'super_admin') {
    
    if($_POST['admin_password'] === $_POST['admin_confirm_password']) {
        $admin_username = htmlspecialchars($_POST['admin_username']);
        $admin_email = htmlspecialchars($_POST['admin_email']);
        $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
        $admin_role_new = $_POST['admin_role'];
        
        $query = "INSERT INTO admins (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$admin_username, $admin_email, $admin_password, $admin_role_new]);
        
        $admin_success_message = "New admin account created successfully!";
    } else {
        $admin_error_message = "Passwords do not match!";
    }
}


if(isset($_POST['delete_admin']) && $admin_role === 'super_admin') {
    $admin_id_to_delete = $_POST['admin_id'];
    
    // First check if the admin we're trying to delete is a super_admin
    $query = "SELECT role FROM admins WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$admin_id_to_delete]);
    $admin_to_delete = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Only delete if it's not a super_admin and not id 1
    if($admin_id_to_delete != 1 && $admin_to_delete['role'] !== 'super_admin') {
        $query = "DELETE FROM admins WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$admin_id_to_delete]);
        
        $admin_success_message = "Admin account deleted successfully!";
    } else {
        $admin_error_message = "Super admin accounts cannot be deleted!";
    }
}


$query = "SELECT id, username, email, role FROM admins";
$stmt = $db->query($query);
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Mind & Motion</title>
    <style>
        :root {
            --bg-color: #0d1b2a;
            --text-color: #e0e1dd;
            --secondary-text: #778da9;
            --card-bg: #1b263b;
            --icon-bg: #415a77;
            --border-color: #415a77;
            --shadow: rgba(0, 0, 0, 0.3);
            --nth-color: #131c2d;
        }
        
        [data-theme="light"] {
            --bg-color: #e0e1dd;
            --text-color: #1b263b;
            --secondary-text: #415a77;
            --card-bg: #f1f3f5;
            --icon-bg: #a8b2c8;
            --border-color: #a8b2c8;
            --shadow: rgba(0, 0, 0, 0.1);
            --nth-color: #ececee;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }
        
        .nav-space,
        .footer-space {
            height: 60px;
            width: 100%;
            background-color: var(--card-bg);
        }
        
        .main-content {
            padding: 24px 32px 24px 80px;
            flex: 1;
        }
        
        .welcome-section {
            margin-bottom: 32px;
        }
        
        .welcome-section h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }
        
        .welcome-section p {
            color: var(--secondary-text);
        }
        
        .settings-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 32px;
            margin-bottom: 40px;
        }
        
        .settings-card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 16px var(--shadow);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .settings-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px var(--shadow);
        }
        
        .settings-card h3 {
            margin-bottom: 20px;
            color: var(--text-color);
            font-weight: 600;
            font-size: 1.2rem;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--secondary-text);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: 1rem;
        }
        
        input[type="file"] {
            padding: 10px 0;
        }
        
        button, .button {
            padding: 12px 20px;
            background-color: var(--icon-bg);
            color: var(--text-color);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1rem;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        button:hover, .button:hover {
            opacity: 0.9;
            background: var(--secondary-text);
        }

    
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            border: 1px solid #2ecc71;
            color: #2ecc71;
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            color: #e74c3c;
        }
        
        .admin-list {
            list-style: none;
        }
        
        .admin-item {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-item:nth-child(odd) {
            background-color: var(--nth-color);
        }
        
        .admin-item:last-child {
            border-bottom: none;
        }
        
        .admin-info strong {
            display: block;
            margin-bottom: 4px;
        }
        
        .admin-info p {
            color: var(--secondary-text);
            font-size: 0.9rem;
        }
        
        .admin-actions button, .admin-actions .button {
            margin-left: 10px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }
        
      
        .form-note {
            font-size: 0.9rem;
            color: var(--secondary-text);
            margin-top: 5px;
        }
        
        .back-link {
            margin-bottom: 20px;
            display: inline-block;
            color: var(--secondary-text);
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 1200px) {
            .main-content {
                padding-left: 32px;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 16px;
            }
            
            .settings-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        
        
        <div class="main-content">
            
            <div class="welcome-section">
                <h1>Settings</h1>
                <p>Manage admin accounts and permissions</p>
            </div>
           
            
            <div class="settings-card">
                <h3>Admin Accounts</h3>
                <?php if(isset($admin_success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $admin_success_message; ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($admin_error_message)): ?>
                <div class="alert alert-error">
                    <?php echo $admin_error_message; ?>
                </div>
                <?php endif; ?>
                
                <?php if($edit_mode && $admin_to_edit): ?>
                    
                    <a href="admin-settings.php" class="back-link">← Back to Admin List</a>
                    <h3 style="border-bottom: none; margin-top: 20px;">Edit Admin: <?php echo htmlspecialchars($admin_to_edit['username']); ?></h3>
                    <form action="" method="post">
                        <input type="hidden" name="admin_id" value="<?php echo $admin_to_edit['id']; ?>">
                        
                        <div class="form-group">
                            <label for="admin_username">Username</label>
                            <input type="text" id="admin_username" name="admin_username" value="<?php echo htmlspecialchars($admin_to_edit['username']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_email">Email</label>
                            <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($admin_to_edit['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_password">New Password</label>
                            <input type="password" id="admin_password" name="admin_password">
                            <p class="form-note">Leave blank to keep current password</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_confirm_password">Confirm New Password</label>
                            <input type="password" id="admin_confirm_password" name="admin_confirm_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_role">Role</label>
                            <select id="admin_role" name="admin_role" required>
                                <option value="admin" <?php echo ($admin_to_edit['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="super_admin" <?php echo ($admin_to_edit['role'] === 'super_admin') ? 'selected' : ''; ?>>Super Admin</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="update_admin">Update Admin Account</button>
                    </form>
                
                <?php else: ?>
                    
                    <h3 style="border-bottom: none; margin-top: 20px;">Current Admin Accounts</h3>
                    <ul class="admin-list">
                        <?php foreach($admins as $admin): ?>
                        <li class="admin-item">
                            <div class="admin-info">
                                <strong><?php echo htmlspecialchars($admin['username']); ?></strong>
                                <p><?php echo htmlspecialchars($admin['email']); ?></p>
                                <p>Role: <?php echo htmlspecialchars($admin['role']); ?></p>
                            </div>
                            <?php if($admin_role === 'super_admin'): ?>
                            <div class="admin-actions">
                                <a href="?edit=<?php echo $admin['id']; ?>" class="button">Edit</a>
                                <?php if($admin['id'] != 1 && $admin['role'] !== 'super_admin'): ?>
                                <form action="" method="post" style="display: inline;">
                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                    <button type="submit" name="delete_admin" onclick="return confirm('Are you sure you want to delete this admin account?')">Delete</button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <?php if($admin_role === 'super_admin'): ?>
                    <!-- Create New Admin Form -->
                    <h3 style="margin-top: 30px; border-bottom: none;">Create New Admin Account</h3>
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="admin_username">Username</label>
                            <input type="text" id="admin_username" name="admin_username" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_email">Email</label>
                            <input type="email" id="admin_email" name="admin_email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_password">Password</label>
                            <input type="password" id="admin_password" name="admin_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_confirm_password">Confirm Password</label>
                            <input type="password" id="admin_confirm_password" name="admin_confirm_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_role">Role</label>
                            <select id="admin_role" name="admin_role" required>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="create_admin">Create Admin Account</button>
                    </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>