<?php
session_start();
include 'navbar.php';

if (!isset($_SESSION['uid'])) {
    echo "<script>alert('Please login to proceed to checkout.');</script>";
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['uid'];

try {
    require_once(__DIR__ . '/PHPHost.php'); 

    
    $query = "
        SELECT 
            b.product_id, 
            b.quantity, 
            pi.price, 
            pi.product_item_id,  
            p.product_name,     
            p.product_image      
        FROM 
            asad_basket b
        JOIN 
            product p ON b.product_id = p.product_id  
        JOIN
            product_item pi ON b.product_id = pi.product_id 
        WHERE 
            b.user_id = :user_id
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $basketItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //calculat total amount from basket items
    $total_amount = 0;
    foreach ($basketItems as $item) {
        $total_amount += $item['quantity'] * $item['price'];
    }

    if (empty($basketItems)) {
        echo "<script>alert('Your basket is empty. Please add items to proceed to checkout.');</script>";
        echo "<script>window.location.href = 'Basket.php';</script>";
        exit();
    }
} catch (PDOException $ex) {
    echo "Error fetching basket details: " . htmlspecialchars($ex->getMessage());
    exit();
}

//process form submission when user clicks "Proceed to Payment"
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        

        //address
        $postal_code = trim($_POST['postal_code']);
        $street = trim($_POST['street']);
        $line1 = trim($_POST['line1']);
        $line2 = trim($_POST['line2']);
        $city = trim($_POST['city']);
        $country_name = trim($_POST['country']);
        $region = trim($_POST['region']);

        
        $countryQuery = "SELECT country_id FROM country WHERE country_name = :country_name"; //check if country is in database
        $countryStmt = $db->prepare($countryQuery); 
        $countryStmt->execute([':country_name' => $country_name]);
        $countryRow = $countryStmt->fetch(PDO::FETCH_ASSOC);
        if ($countryRow) {
            $country_id = $countryRow['country_id'];
        } else {
            //add new country if it does not exist to database
            $insertCountryQuery = "INSERT INTO country (country_name) VALUES (:country_name)";
            $insertCountryStmt = $db->prepare($insertCountryQuery); 
            $insertCountryStmt->execute([':country_name' => $country_name]);
            $country_id = $db->lastInsertId(); 
        }

        //add address intotable
        $insertAddressQuery = "INSERT INTO address (postal_code, street, line_1, line_2, city, country_id, region) 
                               VALUES (:postal_code, :street, :line1, :line2, :city, :country_id, :region)";
        $insertAddressStmt = $db->prepare($insertAddressQuery);
        $insertAddressStmt->execute([
            ':postal_code' => $postal_code,
            ':street' => $street,
            ':line1' => $line1,
            ':line2' => $line2,
            ':city' => $city,
            ':country_id' => $country_id,
            ':region' => $region
        ]);
        $address_id = $db->lastInsertId();

        //link address with user
        $insertUserAddressQuery = "INSERT INTO users_address (address_id, user_id) VALUES (:address_id, :user_id)";
        $insertUserAddressStmt = $db->prepare($insertUserAddressQuery);
        $insertUserAddressStmt->execute([
            ':address_id' => $address_id, 
            ':user_id' => $user_id
        ]);


        //order status pending
        $statusQuery = "SELECT order_status_id FROM order_status WHERE status = 'pending'"; //get pending status id
        $statusStmt = $db->prepare($statusQuery);
        $statusStmt->execute();
        $statusRow = $statusStmt->fetch(PDO::FETCH_ASSOC);
        $order_status_id = $statusRow ? $statusRow['order_status_id'] : 1;

        //default shipping and payment method!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $shipping_method_id = 1; //
        $payment_method_id = 1;

        // insert the new order intotable 
        $insertOrderQuery = "INSERT INTO orders (user_id, address_id, order_status_id, shipping_method_id, payment_method_id, order_price, order_date) 
                             VALUES (:user_id, :address_id, :order_status_id, :shipping_method_id, :payment_method_id, :order_price, NOW())";
        $insertOrderStmt = $db->prepare($insertOrderQuery); 
        $insertOrderStmt->execute([
            ':user_id' => $user_id,
            ':address_id' => $address_id,
            ':order_status_id' => $order_status_id,
            ':shipping_method_id' => $shipping_method_id,
            ':payment_method_id' => $payment_method_id,
            ':order_price' => $total_amount
        ]);
        $order_id = $db->lastInsertId();

        
        foreach ($basketItems as $item) {
            $insertOrderProdQuery = "INSERT INTO order_prod (orders_id, product_item_id, quantity) 
                                     VALUES (:order_id, :product_item_id, :quantity)"; 
            $insertOrderProdStmt = $db->prepare($insertOrderProdQuery); 
            $insertOrderProdStmt->execute([
                ':order_id' => $order_id,
                ':product_item_id' => $item['product_item_id'],
                ':quantity' => $item['quantity']
            ]);
        }

        //clear the basket
        $clearBasketQuery = "DELETE FROM asad_basket WHERE user_id = :user_id";
        $clearBasketStmt = $db->prepare($clearBasketQuery);
        $clearBasketStmt->execute([':user_id' => $user_id]);

        //payment success message
        echo "<script>alert('Payment was successful and your order has been placed as pending!');</script>";
        echo "<script>window.location.href = 'index.php';</script>"; //go index page
        exit();

    } catch (PDOException $ex) {
        echo "Error processing order: " . htmlspecialchars($ex->getMessage());
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <!--<link rel="stylesheet" href="sty.css">-->
</head>
<body>

<h1 style="text-align: center; margin-top: 20px;">Checkout</h1>

<div class="container">
    <!-- order summary section -->
    <section class="order-summary">
        <h2>Order Summary</h2>
        <?php foreach ($basketItems as $item): ?>
            <div class="item-card">
                <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                <div class="product-details">
                    <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                    <p>Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                    <p>Price: £<?php echo number_format($item['price'], 2); ?></p>
                    <p>Subtotal: £<?php echo number_format($item['quantity'] * $item['price'], 2); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <p style="font-size: 18px; font-weight: bold;">Total Amount: £<?php echo number_format($total_amount, 2); ?></p>
    </section>


    <section class="shipping-address">
        <h2>Shipping Address</h2>
        <form method="POST" action="">
            <label for="postal_code">Postal Code</label>
            <input type="text" id="postal_code" name="postal_code" required>

            <label for="street">Street</label>
            <input type="text" id="street" name="street" required>

            <label for="line1">Address Line 1</label>
            <input type="text" id="line1" name="line1" required>

            <label for="line2">Address Line 2</label>
            <input type="text" id="line2" name="line2">

            <label for="city">City</label>
            <input type="text" id="city" name="city" required>

            <label for="country">Country</label>
            <input type="text" id="country" name="country" required>

            <label for="region">Region</label>
            <input type="text" id="region" name="region"> 

            <!-- payment details -->
            <h2>Payment</h2>
            <label for="card_number">Card Number</label>
            <input type="text" id="card_number" name="card_number" required>

            <label for="card_holder">Card Holder</label>
            <input type="text" id="card_holder" name="card_holder" required> 

            <label for="expiration_date">Expiration Date</label>
            <input type="text" id="expiration_date" name="expiration_date" placeholder="MM/YY" required>
            <label for="cvv">CVV</label>
            <input type="text" id="cvv" name="cvv" required>

            <button type="submit" class="btn">Proceed to Payment</button>
        </form>
    </section>
</div>

</body>
</html>
<?php include 'footer.php'; ?>
<style>
/*checkout page css*/
.container {
    display: flex;
    flex-wrap: nowrap;
    justify-content: space-between;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.order-summary {
    flex: 2;
    margin-right: 20px;
}

.order-summary h2 {
    margin-bottom: 20px;
}

.item-card {
    display: flex;
    background-color: #08429830;
    margin-bottom: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.item-card img {
    width: 150px;
    height: auto;
    object-fit: cover;
}

.product-details {
    padding: 15px;
    flex: 1;
}

.product-details h3 {
    margin: 0;
    font-size: 18px;
    color: black;
}

.product-details p {
    margin: 8px 0;
    font-size: 14px;
    color: black;
}

.shipping-address {
    flex: 1;
    background-color: #08429830;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.shipping-address h2 {
    margin-bottom: 20px;
}

.shipping-address form {
    display: flex;
    flex-direction: column;
}

.shipping-address label {
    font-size: 14px;
    margin-bottom: 5px;
    color: #333;
}

.shipping-address input,
.shipping-address select {
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.btn {
    background-color: #0A369D;
    color: white;
    padding: 10px 20px;
    text-align: center;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
}

.btn:hover {
   background-color: #b8c5d4;
    color: #084298;
    box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.1);
}
</style>
