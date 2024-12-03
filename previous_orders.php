<?php 
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(); 
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once('PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage());
    exit();
}

try {

    $username = $_SESSION['user'];
    $stmt = $db->prepare("SELECT user_id, first_name, last_name FROM users WHERE username = ?");
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

try {

    $sql = "
        SELECT 
            orders.orders_id, 
            orders.order_date, 
            orders.order_price, 
            order_status.status AS order_status 
        FROM orders
        INNER JOIN order_status ON orders.order_status_id = order_status.order_status_id
        WHERE orders.user_id = ?
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$user['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo "Database error occurred: " . htmlspecialchars($ex->getMessage());
    exit();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Previous Orders</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <main>
            <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
            <h3>Your Previous Orders:</h3>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order_card">
                        <h2>Order <?php echo htmlspecialchars($order['orders_id']); ?></h2>
                        <p>Date: <?php echo htmlspecialchars($order['order_date']); ?></p>
                        <p>Total: £<?php echo htmlspecialchars($order['order_price']); ?></p>
                        <p>Status: <?php echo htmlspecialchars($order['order_status']); ?></p>
                        <a href="order_details.php?orders_id=<?php echo htmlspecialchars($order['orders_id']); ?>">View Details</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You do not have any previous orders with us.</p>
            <?php endif; ?>
        </main>
    </body>
</html>