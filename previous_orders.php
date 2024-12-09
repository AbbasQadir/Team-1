<?php 
session_start();
include 'navbar.php';

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
        <link rel="stylesheet" href="sty.css">
		<link rel="stylesheet" href="homestyles.css">

    </head>
    <body>
		
        <div class="main">
            <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
            <h3>Your Previous Orders:</h3>
            <?php if (count($orders) > 0): ?>
                <ul class="order-table">
                    <li class="table-header">
                        <div class="col col-1" data-label="order date">Order Date</div>
                        <div class="col col-2" data-label="order ID">Order Number</div>
                        <div class="col col-3" data-label="total">Total</div>
                        <div class="col col-4" data-label="status">Status</div>
                        <div class="col col-5" data-label="details">Details</div>
                    </li>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <li class="table-row">
                                <div class="col col-1" data-label="order date"><?php echo htmlspecialchars($order['order_date']); ?></div>
                                <div class="col col-2" data-label="order ID"><?php echo htmlspecialchars($order['orders_id']); ?></div>
                                <div class="col col-3" data-label="total">£<?php echo htmlspecialchars($order['order_price']); ?></div>
                                <div class="col col-4" data-label="status"><?php echo htmlspecialchars($order['order_status']); ?></div>
                                <div class="col col-5" data-label="details">
                                    <a href="order_details.php?orders_id=<?php echo htmlspecialchars($order['orders_id']); ?>" class="button-details"> View Details</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </tbody>
                </ul> 
            <?php else: ?>
                <p>You do not have any previous orders with us.</p>
            <?php endif; ?>
        </div>
    </body>
</html>
<?php include 'footer.php'; ?>