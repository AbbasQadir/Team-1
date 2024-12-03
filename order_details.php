<?php
$host='localhost';
$dbname = 'mind_and_motion';
$username = 'root';
$password =''; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die('connection failed:' . $conn->connect_error);
}
$order_id = isset($_GET['order_id'])? intval($_GET['order_id']) : 0;

if ($order_id === 0){
    die("invalid order id");
}

$sql = "
        SELECT 
            order.order_date,
            order_prod.quantity, 
            order_prod.order_prod_price, 
            product.product_image, 
            product.product_name,
            prod.product_id
        FROM 
            order_prod 
        JOIN 
            product_item ON order_prod.product_item_id = product_item.product_item_id
        JOIN 
            product ON product_item.product_id = product.product_id 
        JOIN 
            orders ON order_prod.orders_id = orders.orders_id 
        WHERE 
            order_prod.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$order_id);
$stmt->execute();
$result = $stmt-> get_result();
?>
<?php
session_start();

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
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <main>
        <h2>Order Details</h2>
            <section>
                <h3>Order <?php echo htmlspecialchars($orderInfo['order_id']); ?></h3>
                <p>Date: <?php echo htmlspecialchars($orderInfo['order_date']); ?></p>
                <p>Total Price: £<?php echo htmlspecialchars($orderInfo['order_price']); ?></p>
                <p>Status: <?php echo htmlspecialchars($orderInfo['status']); ?></p>
                    <h3>Products:</h3>
                    <card>
                        <?php foreach ($orderDetails as $product): ?>
                            <li> 
                                <p> <?php echo htmlspecialchars($product['product_name']); ?></p>
                                <p>Quantity: <?php echo htmlspecialchars($product['quantity']); ?></p>
                                <p>Price: £<?php echo htmlspecialchars($product['price']); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </card>
                    <a href="previous_orders.php" class="button">Previous Orders</a>
            </section>    
        </main>
    </body>
</html>