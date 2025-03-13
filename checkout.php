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
    //to gfetch shipping methods from database
    $shippingQuery = "SELECT shipping_method_id, method_name, shipping_price FROM shipping_method";
    $shippingStmt = $db->prepare($shippingQuery);
    $shippingStmt->execute();
    $shippingMethods = $shippingStmt->fetchAll(PDO::FETCH_ASSOC);

    //to fetch all payment types from database
    $paymentTypeQuery = "SELECT type_id, type FROM payment_type";
    $paymentTypeStmt = $db->prepare($paymentTypeQuery);
    $paymentTypeStmt->execute();
    $paymentTypes = $paymentTypeStmt->fetchAll(PDO::FETCH_ASSOC);



} catch (PDOException $ex) {
    echo "Error fetching basket details: " . htmlspecialchars($ex->getMessage());
    exit();
}




//process form submission when user clicks "Proceed to Payment"
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {


        //address
        
        $street = trim($_POST['street']);
        $line1 = trim($_POST['line1']);
        $line2 = trim($_POST['line2']);
    	$postal_code = trim($_POST['postal_code']);
        $city = trim($_POST['city']);
        $country_name = trim($_POST['country']);
        $region = trim($_POST['region']);

        if (!isset($_POST['shipping_method'])) {
            echo "<script>alert('Please select a shipping method.');</script>";
            echo "<script>window.location.href = 'Checkout.php';</script>";
            exit();
        }
        $shipping_method_id = $_POST['shipping_method'];

        if (!isset($_POST['payment_method']) || empty($_POST['payment_method'])) {
            echo "<script>alert('Please select a payment method.');</script>";
            echo "<script>window.location.href = 'Checkout.php';</script>";
            exit();
        }



        //check if ccountry exists in database
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

        //link payment method with user
        $paymentMethodQuery = "SELECT payment_method_id FROM payment_method WHERE user_id = :user_id AND type_id = :type_id";
        $paymentMethodStmt = $db->prepare($paymentMethodQuery);
        $paymentMethodStmt->execute([
            ':user_id' => $user_id,
            ':type_id' => $selected_payment_type_id
        ]);

        $paymentMethodRow = $paymentMethodStmt->fetch(PDO::FETCH_ASSOC);
        if ($paymentMethodRow) {
            $payment_method_id = $paymentMethodRow['payment_method_id'];
        } else {
            //create new payment recordfor user
            $insertPaymentMethodQuery = "INSERT INTO payment_method (type_id, user_id) VALUES (:type_id, :user_id)";
            $insertPaymentMethodStmt = $db->prepare($insertPaymentMethodQuery);
            $insertPaymentMethodStmt->execute([
                ':type_id' => $selected_payment_type_id,
                ':user_id' => $user_id
            ]);
            $payment_method_id = $db->lastInsertId();
        }


        //order status pending
        $statusQuery = "SELECT order_status_id FROM order_status WHERE status = 'pending'"; //get pending status id
        $statusStmt = $db->prepare($statusQuery);
        $statusStmt->execute();
        $statusRow = $statusStmt->fetch(PDO::FETCH_ASSOC);
        $order_status_id = $statusRow ? $statusRow['order_status_id'] : 1;


        $randomNumber = random_int(5000, 5000000);

        // if there is  an order with that id already try again 

        $count = getDBResult($db, "SELECT COUNT(*) FROM orders WHERE orders_id=:ordersID", ":ordersID", $randomNumber)[0];
        var_dump($count["COUNT(*)"]);
        
        //while($count["COUNT(*)"] != 0 ){
        //   $count = getDBResult($db, "SELECT COUNT(*) FROM orders WHERE orders_id=:ordersID", ":ordersID", $randomNumber)[0];
        //}   


        // insert the new order into table 
        $insertOrderQuery = "INSERT INTO orders (orders_id, user_id, address_id, order_status_id, shipping_method_id, payment_method_id, order_price, order_date) 
                             VALUES (:orders_id, :user_id, :address_id, :order_status_id, :shipping_method_id, :payment_method_id, :order_price, NOW())";
        $insertOrderStmt = $db->prepare($insertOrderQuery);
        $insertOrderStmt->execute([
            'orders_id' => $randomNumber, 
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
        
            //update stock
            $updateStockQuery = "UPDATE product_item 
                                 SET quantity = quantity - :quantity 
                                 WHERE product_item_id = :product_item_id";
            $updateStockStmt = $db->prepare($updateStockQuery);
            $updateStockStmt->execute([
                ':quantity'         => $item['quantity'],
                ':product_item_id'  => $item['product_item_id']
            ]);
        }
        

        //clear the basket
        $clearBasketQuery = "DELETE FROM asad_basket WHERE user_id = :user_id";
        $clearBasketStmt = $db->prepare($clearBasketQuery);
        $clearBasketStmt->execute([':user_id' => $user_id]);

        //payment success message
       // echo "<script>alert('Payment was successful and your order has been placed as pending!');</script>";
        //echo "<script>window.location.href = 'index.php';</script>"; //go index page
        echo "<script>window.location.href = '/orderConfirmation.php?orderID=".$order_id."';</script>";
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
                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>"
                        alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    <div class="product-details">
                        <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                        <p>Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                        <p>Price: £<?php echo number_format($item['price'], 2); ?></p>
                        <p>Subtotal: £<?php echo number_format($item['quantity'] * $item['price'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <p id="productTotal" style="font-size: 18px; font-weight: bold;">Products Total:
                £<?php echo number_format($total_amount, 2); ?></p>
            <p id="shippingCost" style="font-size: 18px; font-weight: bold;">Shipping: £0.00</p>
            <br>
            <p id="finalTotal" style="font-size: 20px; font-weight: bold;">Final Total:
                £<?php echo number_format($total_amount, 2); ?></p>
        </section>

        <form method="POST" action="">
            <section class="shipping-method">
                <fieldset>
                    <!--allow the userto choose shipping method-->
                    <h3>Select Shipping Method:</h3>
                    <?php foreach ($shippingMethods as $method): ?>
                        <div>
                            <input type="radio" id="shipping_<?php echo $method['shipping_method_id']; ?>"
                                name="shipping_method" value="<?php echo $method['shipping_method_id']; ?>"
                                data-price="<?php echo $method['shipping_price']; ?>" required>
                            <label for="shipping_<?php echo $method['shipping_method_id']; ?>">
                                <?php echo htmlspecialchars($method['method_name']); ?> -
                                £<?php echo number_format($method['shipping_price'], 2); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            </section>

            <section class="shipping-address">
                <h2>Shipping Address</h2>

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
            </section>
            <section class="payment_card">
                <!-- payment details -->
                <h2>Payment</h2>

                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">Select Payment Method</option>
                    <?php foreach ($paymentTypes as $pt): ?>
                        <option value="<?php echo $pt['type_id']; ?>"><?php echo htmlspecialchars($pt['type']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="card_number">Card Number</label>
                <input type="text" id="card_number" name="card_number" required>
                <label for="card_holder">Card Holder</label>
                <input type="text" id="card_holder" name="card_holder" required>
                <label for="expiration_date">Expiration Date</label>
                <input type="text" id="expiration_date" name="expiration_date" placeholder="MM/YY" required>
                <label for="cvv">CVV</label>
                <input type="text" id="cvv" name="cvv" required>

                <button type="submit" class="btn">Proceed to Payment</button>

            </section>
        </form>
    </div>
    <!--update price adding delivery-->
    <script>

        const productTotal = <?php echo $total_amount; ?>;

        function updateShipping() {
            const selected = document.querySelector('input[name="shipping_method"]:checked');
            if (selected) {
                const shippingPrice = parseFloat(selected.getAttribute('data-price'));
                document.getElementById('shippingCost').textContent = "Shipping: £" + shippingPrice.toFixed(2);
                document.getElementById('finalTotal').textContent = "Final Total: £" + (productTotal + shippingPrice).toFixed(2);
            }
        }

        const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
        shippingRadios.forEach(radio => {
            radio.addEventListener('change', updateShipping);
        });

        updateShipping();
    </script>
</body>

</html>
<?php include 'footer.php'; ?>
<style>
    /*checkout page css*/

    .container {
        background-color: inherit;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: #0D1B2A;
        margin: 0;
        padding: 0;
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
        padding: 20px;
        
        margin: 0 auto;
    }


    .order-summary {
        flex: 1;
        margin-right: 20px;
        padding: 20px;

    }

    .order-summary h2 {

        color: #1B263B;
        padding: 10px;
        margin-bottom: 20px;
        text-align: center;
    }

    .item-card {
        display: flex;
        background-color: #E0E1DD;
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

    }

    .product-details p {
        margin: 8px 0;
        font-size: 14px;
    }


    .shipping-method,
    .shipping-address,
    .payment_card {
        flex: 2;
        background-color: #E0E1DD;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }


    .shipping-method h3,
    .shipping-address h2,
    .payment_card h2 {
        background-color: #E0E1DD;
        color: #1B263B;
        padding: 10px;
        margin-bottom: 20px;
        text-align: center;
    }


    fieldset {
        border: none;
        margin: 0;
        padding: 0;
    }


    label {
        font-size: 14px;
        margin-bottom: 5px;
        color: #0D1B2A;
        display: block;
    }

    input[type="text"],
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }


    .btn {
        background-color: #415A77;
        color: #E0E1DD;
        padding: 10px 20px;
        text-align: center;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
        width: 100%;
    }

    .btn:hover {
        background-color: #778DA9;
        color: #0D1B2A;
        box-shadow: 0px 0px 9px 0px rgba(0, 0, 0, 0.1);
    }

    .shipping-method div {
        margin-bottom: 10px;
    }

    .shipping-method input[type="radio"] {
        margin-right: 5px;
    }
</style>
