<?php
session_start();
include 'navbar.php';


if(!isset($_SESSION['user'])){
    header('Location:login.php');
    exit();
}

try {
    require_once('PHPHost.php');
} catch (Exception $ex) {
    echo "failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage());
    exit();
}

if (!isset($_GET['orders_id'])){
    echo "order ID not specified";
    exit();
}

$orderId = intval($_GET['orders_id']);

try {
    $stmt = $db->prepare("
        SELECT
            orders.orders_id,
            orders.order_date,
            orders.order_price,
            order_status.status,
            product.product_name,
            product.product_image,
            product_item.price,
            order_prod.quantity
        from orders
        INNER JOIN order_status ON orders.order_status_id = order_status.order_status_id
        INNER JOIN order_prod ON orders.orders_id = order_prod.orders_id
        INNER JOIN product_item ON order_prod.product_item_id = product_item.product_item_id
        INNER JOIN product ON product_item.product_id = product.product_id
        WHERE orders.orders_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$orderDetails) {
        echo "order details not found";
        exit();
    }
} catch (PDOException $ex) {
    echo "failed to retrieve order details: " . htmlspecialchars($ex->getMessage());
    exit();
}

$orderInfo = $orderDetails[0];

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta nam="viewport" content="width=device-width", initial-scale="1.0">
        <title>Order Details</title>
        <link rel="stylesheet" href="sty.css">
	<link rel="stylesheet" href="homestyle.css">
    </head>
    <div class="body">
		<h2>Order Details</h2>
        <div class="page">
        
            <section class="order-info">
                <h3>Order <?php echo htmlspecialchars($orderInfo['order_id']); ?></h3>
                <p>Date: <?php echo htmlspecialchars($orderInfo['order_date']); ?></p>
                <p>Total Price: £<?php echo htmlspecialchars($orderInfo['order_price']); ?></p>
                <p>Status: <?php echo htmlspecialchars($orderInfo['status']); ?></p>
            </section> 
            <div class="product-list">
                <h3>Products:</h3>
                
                   <?php foreach ($orderDetails as $product): ?>
                        <div class = "products-cards"> 
                        	
                    		<img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        	<div class="details">
                                <h4> <?php echo htmlspecialchars($product['product_name']); ?></h4>
                                <p>Quantity: <?php echo htmlspecialchars($product['quantity']); ?></p>
                                <p>Price: £<?php echo htmlspecialchars($product['price']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <a href="previous_orders.php" class="back-button">Previous Orders</a> 
		<br><br><be>
        	</div>   
        </div>
    </div>
</html>
<?php include 'footer.php'; ?>
