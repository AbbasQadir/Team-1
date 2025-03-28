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
            shipping_method.shipping_price,
            order_status.status,
            product.product_name,
            product.product_image,
            product_item.price,
            order_prod.quantity,
            order_prod.order_prod_id,
            order_prod.Colour,
            order_prod.Size
        from orders
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
		
        <style> 

    #productVariationsColourIcon{
        display: inline-block;
        background-Color:red;
        width: 15px;
        height:15px;
        border-radius: 7.5px;

    }

    #productVariationsContainer{
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
                <p>Total Price (include shipping):
                     £<?php echo htmlspecialchars($orderInfo['order_price'] + $orderInfo['shipping_price']); ?></p>
                <p>Status: <?php echo htmlspecialchars($orderInfo['status']); ?></p>
            </section> 
            <div class="product-list">
                <h3>Products:</h3>
                
                   <?php foreach ($orderDetails as $product): ?>
                        <div class = "products-cards"> 
                        	
                    		<img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        	<div class="details">
                                <h4> <?php echo htmlspecialchars($product['product_name']); ?></h4>
                               
                                <div id='productVariationsContainer'>

                                    <?php if(isset($product["Colour"]) &&  $product["Size"] != 0 ) { ?>
                                         <div id="productVariationsColourIcon" style="background-color: <?php echo htmlspecialchars(getNameFromVariationOptionID($db, $product["Colour"])); ?>;"></div>
                                        <?php echo htmlspecialchars(getNameFromVariationOptionID($db, $product["Colour"])) ?>
                                    <?php } ?>


                                    <?php if(isset($product["Size"]) &&  $product["Size"] != 0) { ?>
                                        <!-- <div id="productVariationsSizeIcon"> <?php echo getSymbolLetterForSize(getNameFromVariationOptionID($db, $product["Size"])); ?> </div> -->
                                        <?php echo "Size: ".htmlspecialchars(getShortNameFromVariationOptionID($db, $product["Size"]))  ?>
                                    <?php } ?>


                                </div>
                                
                                <p>Quantity: <?php echo htmlspecialchars($product['quantity']); ?></p>
                                <p>Price: £<?php echo htmlspecialchars($product['price']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <a href="previous_orders.php" class="back-button">Previous Orders</a> 
                <br><br><br>
        	</div>   
        </div>
    </body>
</html>
<?php include 'footer.php'; ?>