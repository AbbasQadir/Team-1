<?php
session_start();



if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_log.php"); 
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
            address.line_1,
            address.line_2,
            address.city,
            address.region,
            address.country_id,
            shipping_method.method_name,
            shipping_method.shipping_price,
            country.country_name,
            orders.orders_id,
            users.email,
            users.number,
            orders.user_id,
            orders.order_date,
            orders.order_price,
            order_status.status,
            product.product_name,
            product.product_image,
            product_item.price,
            order_prod.quantity,
            order_prod.Colour,
            order_prod.Size
        from orders
        INNER JOIN shipping_method ON orders.shipping_method_id = shipping_method.shipping_method_id
        INNER JOIN address ON orders.address_id = address.address_id
        INNER JOIN users ON orders.user_id = users.user_id
        INNER JOIN country ON address.country_id = country.country_id
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
$orderUsername = getDBResult($db, "SELECT username FROM users WHERE user_id=:userID", ":userID", htmlspecialchars($orderInfo['user_id']))[0]["username"];

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta nam="viewport" content="width=device-width", initial-scale="1.0">
        <title>Order Details</title>



		<link rel="stylesheet" href="admin-dashboard.css">

        <style>

            .main {
    max-width: 1000px;
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

    text-align: left;
}

.order-table {
    li {
        border-radius: 3px;
        padding: 25px 30px;
        display: flex;
        justify-content: space-between;
        margin-bottom: 25px;
    }
    .table-header {
        background-color: #1b263b;
        color: #E0E1DD;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .table-row {
        background-color: #778da9;
        box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.1);
    }
    .col-1 {
        flex-basis: 20%;
        text-align: center;
    }
   

    @media all and (max-width: 767px) {
        .table-header {
            display: none;
        }
        .table-row{

        }
        li {
            display: block;
        }
        .col {

            flex-basis: 100%;

        }
        .col {
            display: flex;
            padding: 10px 0;
            &:before {
                color: #0d1b2a;
                padding-right: 10px;
                content: attr(data-label);
                flex-basis: 50%;
                text-align: right;
            }
        }
    }
}













/* order details css*/
.order-info {
	text-align:center;
    background-color: var(--card-bg);
    color: var(--text-color);
    border-radius: 10px;    
    padding: 20px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
	margin:40px;
}

.order-info h3 {
	text-align:center;
    color: var(--text-color);
    font-size: 22px;
}

.order-info p {
	text-align:center;
    font-size: 16px;
}

.products-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.products-cards {
    display: flex;
    align-items: center;
    background-color: var(--card-bg);
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin: 40px;
}

.products-cards img {
    width: 150px;
    height: auto;
    object-fit: cover;
}

.details {
    padding: 15px;
    flex: 1;
}

.details h4 {
    margin: 0 0 5px;
    font-size: 18px;
    color: var(--text-color);
}

.details p {
    margin: 5px 0;
    font-size: 16px;
    color: var(--secondary-text);
}


.header-left {
    position: fixed;
    align-items: center;
    gap: 15px;

    left:25px;
    top:25px;


}

a.back-link {
        background: #415A77;
        color: #fff;
        padding: 12px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 1.1rem;
        box-shadow: 2px 2px 6px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    a.back-link:hover {
        background: #778DA9;
        color: #000;
        transform: scale(1.05);
    }

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

    
        <div class="header-left">
            
            <a href="ordermanagement.php" class="back-link">Back </a>
        </div>

        <h2>Order Details</h2>

		
        
        <div class="page">

        
        
            <section class="order-info">

                <h3>Order <?php echo htmlspecialchars($orderInfo['orders_id']); ?></h3>
                <h3>user: <?php echo $orderUsername ?> </h3>
                <p>user ID: <?php echo htmlspecialchars($orderInfo['user_id']); ?></p>
                <p>Date: <?php echo htmlspecialchars($orderInfo['order_date']); ?></p>
                <p>Total Price: £<?php echo htmlspecialchars($orderInfo['order_price']); ?></p>
                <p>Status: <?php echo htmlspecialchars($orderInfo['status']); ?></p>
            </section> 

            <h3>Customer Info:</h3>

            <section class="order-info">
            
                <h3>SHIP TO:</h3>
                <p><?php echo $orderInfo["line_1"] ?> </p>
                <p><?php echo $orderInfo["line_2"] ?> </p>
                <p><?php echo $orderInfo["city"] ?></p>
                <p><?php echo $orderInfo["region"] ?></p>
                <p><?php echo $orderInfo["country_name"] ?></p>
                <h3>SHIPPING METHOD:</h3>
                <p><?php echo $orderInfo["method_name"] ?> ( £<?php echo $orderInfo["shipping_price"] ?> )</p>
                <h3>CONTACT DETAILS:</h3>
                <p><?php echo $orderInfo["number"] ?></p>
                <p><?php echo $orderInfo["email"] ?></p>
                
            </section> 



            <div class="product-list">
                <h3>Order Contents:</h3>
                
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
                <br><br><br>
        	</div>   
        </div>
    </body>



</html>
