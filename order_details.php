<?php
session_start();
include 'navbar.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

try {
    require_once('PHPHost.php');
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage());
    exit();
}

if (!isset($_GET['orders_id'])) {
    echo "Order ID not specified";
    exit();
}

$orderId = intval($_GET['orders_id']);

try {
    $stmt = $db->prepare("
        SELECT
            orders.orders_id,
            orders.order_date,
            orders.order_price,
            shipping_method.shipping_price,
            order_status.status,
            product.product_name,
            product.product_image,
            product.product_id,
            product_item.price,
            order_prod.quantity,
            order_prod.order_prod_id,
            order_prod.Colour,
            order_prod.Size
        FROM orders
        INNER JOIN order_status ON orders.order_status_id = order_status.order_status_id
        INNER JOIN shipping_method ON orders.shipping_method_id = shipping_method.shipping_method_id
        INNER JOIN order_prod ON orders.orders_id = order_prod.orders_id
        INNER JOIN product_item ON order_prod.product_item_id = product_item.product_item_id
        INNER JOIN product ON product_item.product_id = product.product_id
        WHERE orders.orders_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$orderDetails) {
        echo "Order details not found";
        exit();
    }
} catch (PDOException $ex) {
    echo "Failed to retrieve order details: " . htmlspecialchars($ex->getMessage());
    exit();
}

$orderInfo = $orderDetails[0];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="sty.css">
    <link rel="stylesheet" href="homestyle.css">
    <style>
        /* Styling from your original updated code */
        .main {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 10px;
            padding-right: 10px;
        }
        h2 {
            font-size: 30px;
            margin: 20px 0;
            text-align: center;
        }
        h3 {
            font-size: 26px;
            margin: 20px;
            text-align: center;
        }
        .order-info {
            text-align: center;
            background-color: var(--card-bg);
            color: var(--text-color);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin: 40px auto;
            max-width: 1400px;
        }
        .order-info h3 {
            text-align: center;
            color: var(--text-color);
            font-size: 22px;
        }
        .order-info p {
            text-align: center;
            font-size: 16px;
        }
        .products-cards {
            display: flex;
            align-items: center;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 40px auto;
            max-width: 1400px;
            text-decoration: none;
            color: var(--text-color);
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .products-cards:hover {
            transform: scale(1.02);
        }
        .products-cards img {
            width: 170px;
            height: 170px;
            object-fit: cover;
            border-radius: 4px 0 0 4px;
            margin-right: 20px;
        }
        .details {
            padding: 15px;
            flex: 1;
        }
        .details h4 {
            margin: 0 0 10px;
            font-size: 18px;
            color: var(--text-color);
        }
        .details p {
            margin: 5px 0;
            font-size: 16px;
            color: var(--text-color);
        }
        .back-button {
            background-color: #415A77;
            color: #E0E1DD;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 40px 60px 40px 60px;
            max-width: 1400px;
            text-align: center;
        }
        .back-button:hover {
            background-color: #778DA9;
            color: #0d1b2a;
        }
        @media all and (max-width: 767px) {
            .order-info,
            .products-cards,
            .back-button {
                width: 100%;
                margin-left: 0;
                margin-right: 0;
            }
            h2, h3 {
                text-align: center;
            }
        }
        /* Additional styles for product variations */
        #productVariationsColourIcon {
            display: inline-block;
            background-color: red;
            width: 15px;
            height: 15px;
            border-radius: 7.5px;
        }
        #productVariationsContainer {
            color: var(--text-color);
        }
    </style>
</head>
<body>
    <h2>Order Details</h2>
    <div class="page">
        <section class="order-info">
            <h3>Order <?php echo htmlspecialchars($orderInfo['orders_id']); ?></h3>
            <p>Date: <?php echo htmlspecialchars($orderInfo['order_date']); ?></p>
            <p>Total Price: £<?php echo htmlspecialchars($orderInfo['order_price']); ?></p>
            <p>Shipping Price: £<?php echo htmlspecialchars($orderInfo['shipping_price']); ?></p>
            <p>Total Price (include shipping): £<?php echo htmlspecialchars($orderInfo['order_price'] + $orderInfo['shipping_price']); ?></p>
            <p>Status: <?php echo htmlspecialchars($orderInfo['status']); ?></p>
        </section>
        <div class="product-list">
            <h3>Products:</h3>
            <?php foreach ($orderDetails as $product): ?>
                <a href="specificProduct.php?id=<?php echo urlencode($product['product_id']); ?>" class="products-cards">
                    <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <div class="details">
                        <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                        <div id="productVariationsContainer">
                            <?php if (isset($product["Colour"]) && $product["Colour"] != 0) { ?>
                                <div id="productVariationsColourIcon" style="background-color: <?php echo htmlspecialchars(getNameFromVariationOptionID($db, $product["Colour"])); ?>;"></div>
                                <?php echo htmlspecialchars(getNameFromVariationOptionID($db, $product["Colour"])); ?>
                            <?php } ?>
                            <?php if (isset($product["Size"]) && $product["Size"] != 0) { ?>
                                <?php echo " Size: " . htmlspecialchars(getShortNameFromVariationOptionID($db, $product["Size"])); ?>
                            <?php } ?>
                        </div>
                        <p>Quantity: <?php echo htmlspecialchars($product['quantity']); ?></p>
                        <p>Price: £<?php echo htmlspecialchars($product['price']); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
            <a href="previous_orders.php" class="back-button">Previous Orders</a>
            <br><br><br>
        </div>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>
