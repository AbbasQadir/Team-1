<?php

session_start();
include 'navbar.php';

if (!isset($_SESSION['uid'])) {
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

    // add up the price of the things that are in basket
    $total_amount = 0;
    foreach ($basketItems as $item) {
        $total_amount += $item['quantity'] * $item['price'];
    }

    //if basket is empty send them to basket page
    if (empty($basketItems)) {
        echo "<script>window.location.href = 'Basket.php';</script>";
        exit();
    }

    // get shipping methods from database
    $shippingQuery = "SELECT shipping_method_id, method_name, shipping_price FROM shipping_method";
    $shippingStmt = $db->prepare($shippingQuery);
    $shippingStmt->execute();
    $shippingMethods = $shippingStmt->fetchAll(PDO::FETCH_ASSOC);

    // Set credit card as default payment method (with type_id = 1)
    $defaultPaymentTypeId = 1;

} catch (PDOException $ex) {
    //error handling for problem with database
    echo "<script>
            document.addEventListener('DOMContentLoaded', function(){
              showCustomMessage('Error fetching details: " . addslashes($ex->getMessage()) . "', 'index.php');
            });
          </script>";
    exit();
}

$orderSuccess = false;  //changed to true if order is processed correctly
$errorMessage = "";

//process form submission if checkout is pressed
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $shipping_method_id = $_POST['shipping_method'];
        $postal_code = trim($_POST['postal_code']);
        $street = trim($_POST['street']);
        $line1 = trim($_POST['line1']);
        $line2 = trim($_POST['line2']);
        $city = trim($_POST['city']);
        $country_name = trim($_POST['country']);
        $region = trim($_POST['region']);
        $selected_payment_type_id = $defaultPaymentTypeId; // Use the default payment type (credit card)
        $card_number = trim($_POST['card_number']);
        $expiration_date = trim($_POST['expiration_date']);
        $cvv = trim($_POST['cvv']);
        $card_holder = trim($_POST['card_holder']);

        //validate payment details
        if (!preg_match('/^\d{16}$/', $card_number)) {
            throw new Exception("Invalid card number. It must be 16 digits.");
        }
        if (!preg_match('/^\d{2}\/\d{2}$/', $expiration_date)) {
            throw new Exception("Expiration date must be in MM/YY format.");
        }
        if (!preg_match('/^\d{3}$/', $cvv)) {
            throw new Exception("CVV must be 3 digits.");
        }

        // non UK users have to pay international fees
        $selectedShipping = null;
        foreach ($shippingMethods as $method) {
            if ($method['shipping_method_id'] == $shipping_method_id) {
                $selectedShipping = $method;
                break;
            }
        }
        if ($selectedShipping) {
            $isInternational = (stripos($selectedShipping['method_name'], "international") !== false);
            if (stripos($country_name, "uk") === false && stripos($country_name, "united kingdom") === false && !$isInternational) {
                throw new Exception("For addresses outside the UK, please select an international shipping option, (if you are from the UK please check spelling).");
            }
        }

        //verifies if country is in the database if not it will add it in the countries table
        $countryQuery = "SELECT country_id FROM country WHERE country_name = :country_name";
        $countryStmt = $db->prepare($countryQuery);
        $countryStmt->execute([':country_name' => $country_name]);
        $countryRow = $countryStmt->fetch(PDO::FETCH_ASSOC);
        if ($countryRow) {
            $country_id = $countryRow['country_id'];
        } else {
            $insertCountryQuery = "INSERT INTO country (country_name) VALUES (:country_name)";
            $insertCountryStmt = $db->prepare($insertCountryQuery);
            $insertCountryStmt->execute([':country_name' => $country_name]);
            $country_id = $db->lastInsertId();
        }

        //adds address to the user_address table in database
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

        //links address with user
        $insertUserAddressQuery = "INSERT INTO users_address (address_id, user_id) VALUES (:address_id, :user_id)";
        $insertUserAddressStmt = $db->prepare($insertUserAddressQuery);
        $insertUserAddressStmt->execute([
            ':address_id' => $address_id,
            ':user_id' => $user_id
        ]);

        //checks payment method for user and adds a new one
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
            $insertPaymentMethodQuery = "INSERT INTO payment_method (type_id, user_id) VALUES (:type_id, :user_id)";
            $insertPaymentMethodStmt = $db->prepare($insertPaymentMethodQuery);
            $insertPaymentMethodStmt->execute([
                ':type_id' => $selected_payment_type_id,
                ':user_id' => $user_id
            ]);
            $payment_method_id = $db->lastInsertId();
        }

        //order status pending
        $statusQuery = "SELECT order_status_id FROM order_status WHERE status = 'pending'";
        $statusStmt = $db->prepare($statusQuery);
        $statusStmt->execute();
        $statusRow = $statusStmt->fetch(PDO::FETCH_ASSOC);
        $order_status_id = $statusRow ? $statusRow['order_status_id'] : 1;

        //adds to orders table in DB
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

        //adds all basket items to order and changes stock level 
        foreach ($basketItems as $item) {
            $insertOrderProdQuery = "INSERT INTO order_prod (orders_id, product_item_id, quantity) 
                                     VALUES (:order_id, :product_item_id, :quantity)";
            $insertOrderProdStmt = $db->prepare($insertOrderProdQuery);
            $insertOrderProdStmt->execute([
                ':order_id' => $order_id,
                ':product_item_id' => $item['product_item_id'],
                ':quantity' => $item['quantity']
            ]);

            $updateStockQuery = "UPDATE product_item 
                                 SET quantity = quantity - :quantity 
                                 WHERE product_item_id = :product_item_id";
            $updateStockStmt = $db->prepare($updateStockQuery);
            $updateStockStmt->execute([
                ':quantity' => $item['quantity'],
                ':product_item_id' => $item['product_item_id']
            ]);
        }

        //clear basket
        $clearBasketQuery = "DELETE FROM asad_basket WHERE user_id = :user_id";
        $clearBasketStmt = $db->prepare($clearBasketQuery);
        $clearBasketStmt->execute([':user_id' => $user_id]);

        //set true for success
        $orderSuccess = true;

    } catch (Exception $ex) {
        //for if there is a problem with order processing
        $errorMessage = $ex->getMessage();
    } catch (PDOException $ex) {
        $errorMessage = $ex->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            ;
            background-color: #E0E1DD;
            margin: 0;
            padding: 0;
            color: #0D1B2A;
            line-height: 1.6;
        }

        h1,
        h2,
        h3 {
            text-align: center;
        }

        h1 {
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .checkout-container {
            max-width: 1300px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .checkout-title {
            font-size: 28px;
            margin: 30px 0;
            font-weight: 500;
        }

        .checkout-layout {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        .checkout-details {
            flex: 1;
            min-width: 300px;
        }

        .order-summary {
            width: 500px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }


        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin: 0 0 8px 15px;
            font-size: 14px;
            font-weight: bold;
        }

        .input-field {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 15px;
        }

        .shipping-options {
            margin: 15px 0;
            vertical-align: center;
        }

        .shipping-option {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid #778DA9;
            border-radius: 4px;
            cursor: pointer;
            transition: border-color 0.3s;
            vertical-align: center;
        }

        .shipping-option:hover {
            border-color: #415A77;
        }

        .shipping-option input {
            margin-right: 10px;

        }

        .shipping-option-details {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .product-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #778DA9;
            padding: 15px 0;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }

        .product-details {
            flex: 1;
        }

        .product-title {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .product-price {
            text-align: right;
            font-weight: 500;
        }

        .product-quantity {
            background-color: #778DA9;
            padding: 3px 8px;
            border-radius: 50%;
            font-size: 13px;
            margin-left: 10px;
            color: #0D1B2A;
            margin-right: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        .summary-total {
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #778DA9;
        }

        .edit-link {
            display: inline-block;
            margin: 10px auto;
            color: #0D1B2A;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
        }

        .edit-link:hover {
            color: #415A77;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }


        .card-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-col-half {
            flex: 1;
            min-width: 120px;
        }

        .card-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .card-number-container {
            position: relative;
        }

        .checkout-btn {
            width: 100%;
            padding: 14px;
            background-color: #415A77;
            color: #E0E1DD;
            text-align: center;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .checkout-btn:hover {
            background-color: #778DA9;
            color: #0D1B2A;
            box-shadow: 0px 0px 9px 0px rgba(0, 0, 0, 0.1);
        }


        .success-message {
            margin: 0 auto;
            max-width: 1000px;
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            margin: 20px auto;
            background-color: #b8dda8;
            color: #0D1B2A;
            border: 1px solid #b8dda8;
            border-radius: 4px;
        }

        .error-message {
            margin: 0 auto;
            max-width: 1000px;
            text-align: center;
            padding: 12px;
            margin: 20px auto;
            margin-bottom: 20px;
            background-color: #dda8a8;
            color: #0D1B2A;
            border: 1px solid #dda8a8;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .checkout-layout {
                flex-direction: column;
            }

            .order-summary {
                width: 100%;
            }
        }

        #step-4 {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .checkout-heading {
            font-size: 1.8rem;
            margin-bottom: 25px;
            color: #0D1B2A;
            border-bottom: 2px solid #778DA9;
            padding-bottom: 10px;
            text-align: center;
        }

        .review-container {
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
        }

        .review-section {
            margin-bottom: 30px;
        }

        .review-section:last-child {
            margin-bottom: 0;
        }

        .review-section h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            border-bottom: 1px solid #778DA9;
            padding-bottom: 8px;
        }

        .review-section p {
            margin: 8px 0;
            font-size: 1rem;
            line-height: 1.5;
        }

        .review-section strong {
            font-weight: 20px;
        }

        .review-section:last-of-type p:last-of-type {
            font-size: 1.2rem;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #778DA9;
        }

        .checkout-actions {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }
    </style>
</head>

<body>
    <?php if ($orderSuccess): ?>
        <div class="success-message">
            <h2>Order Placed Successfully!</h2>
            <p>Your order has been placed and is pending. Thank you for your purchase!</p>
            <p>You will be redirected to the home page shortly.</p>
        </div>
        <script>
            //takes them to index after 10s
            setTimeout(function () {
                window.location.href = 'index.php';
            }, 10000);
        </script>
    <?php elseif (!empty($errorMessage)): ?>
        <div class="error-message">
            <h2>Error Processing Order</h2>
            <p><?php echo htmlspecialchars($errorMessage); ?></p>
            <p>Please review your details and try again.</p>
        </div>
    <?php else: ?>
        <div class="checkout-container">

            <h1>Checkout</h1>

            <div class="checkout-layout">
                <div class="checkout-details">
                    <form id="checkoutForm" method="POST" action="">
                        <!--step 1-->
                        <div class="step active" id="step-1">
                            <h2>SHIPPING DETAILS</h2>

                            <div class="form-group">
                                <label for="line1">Address Line 1</label>
                                <input type="text" id="line1" name="line1" class="input-field" required>
                            </div>

                            <div class="form-group">
                                <label for="line2">Address Line 2 (Optional)</label>
                                <input type="text" id="line2" name="line2" class="input-field">
                            </div>

                            <div class="form-group">
                                <label for="street">Street</label>
                                <input type="text" id="street" name="street" class="input-field" required>
                            </div>

                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" class="input-field" required>
                            </div>

                            <div class="form-group">
                                <label for="postal_code">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" class="input-field" required>
                            </div>

                            <div class="form-group">
                                <label for="region">Region (Optional)</label>
                                <input type="text" id="region" name="region" class="input-field">
                            </div>

                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" class="input-field" required>
                            </div>

                            <button type="button" class="checkout-btn" onclick="validateAddressAndNext()">CONTINUE TO
                                SHIPPING</button>
                        </div>

                        <!--step 2-->
                        <div class="step" id="step-2">
                            <h2>SHIPPING METHOD</h2>

                            <div class="shipping-options">
                                <?php foreach ($shippingMethods as $method): ?>
                                    <div class="shipping-option">
                                        <input type="radio" id="shipping_<?php echo $method['shipping_method_id']; ?>"
                                            name="shipping_method" value="<?php echo $method['shipping_method_id']; ?>"
                                            data-price="<?php echo $method['shipping_price']; ?>" required>
                                        <div class="shipping-option-details">
                                            <label for="shipping_<?php echo $method['shipping_method_id']; ?>">
                                                <?php echo htmlspecialchars($method['method_name']); ?>
                                            </label>
                                            <span>£<?php echo number_format($method['shipping_price'], 2); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" class="checkout-btn" onclick="nextStep(1)">BACK</button>
                            <button type="button" class="checkout-btn" onclick="nextStep(3)">CONTINUE TO PAYMENT</button>
                        </div>

                        <!--step 3-->
                        <div class="step" id="step-3">
                            <h2>PAYMENT DETAILS</h2>

                            <div class="form-group">
                                <label for="card_holder">NAME ON CARD</label>
                                <input type="text" id="card_holder" name="card_holder" class="input-field" required>
                            </div>

                            <div class="form-group">
                                <label for="card_number">CARD NUMBER</label>
                                <div class="card-number-container">
                                    <input type="text" id="card_number" name="card_number" class="input-field" required
                                        pattern="\d{16}" title="Card number must be 16 digits" maxlength="16">
                                    <div class="card-icon">VISA</div>
                                </div>
                            </div>

                            <div class="card-row">
                                <div class="card-col-half">
                                    <div class="form-group">
                                        <label for="expiration_date">VALID THROUGH</label>
                                        <input type="text" id="expiration_date" name="expiration_date" class="input-field"
                                            required pattern="\d{2}/\d{2}" placeholder="MM/YY" maxlength="5">
                                    </div>
                                </div>
                                <div class="card-col-half">
                                    <div class="form-group">
                                        <label for="cvv">CVC CODE</label>
                                        <input type="text" id="cvv" name="cvv" class="input-field" required pattern="\d{3}"
                                            title="CVV must be 3 digits" maxlength="3">
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="checkout-btn" onclick="nextStep(2)">BACK</button>
                            <button type="button" class="checkout-btn"
                                onclick="if(validatePayment()) { nextStep(4); }">REVIEW ORDER</button>
                        </div>

                        <!--step 4-->
                        <div class="step" id="step-4">
                            <h2>REVIEW YOUR ORDER</h2>

                            <div id="review-details">
                                <!--filled by JavaScript-->
                            </div>

                            <button type="button" class="checkout-btn" onclick="nextStep(3)">BACK</button>
                            <button type="submit" class="checkout-btn">PURCHASE</button>
                        </div>
                    </form>
                </div>

                <div class="order-summary">
                    <h2>YOUR ORDER</h2>

                    <!--product item-->
                    <?php foreach ($basketItems as $item):
                        $subtotal = $item['quantity'] * $item['price'];
                        ?>
                        <div class="product-item">
                            <?php if (!empty($item['product_image'])): ?>
                                <img src="<?php echo htmlspecialchars($item['product_image']); ?>"
                                    alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-image">
                            <?php else: ?>
                                <img src="default-image.jpg" alt="Default Image" class="product-image">
                            <?php endif; ?>

                            <div class="product-details">
                                <div class="product-title"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            </div>

                            <div class="product-price">
                                <span class="product-quantity">x<?php echo $item['quantity']; ?></span>
                                £<?php echo number_format($item['price'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <span class="edit-link" onclick="window.location.href='Basket.php'">EDIT SHOPPING BAG</span>
                    <!--summery of order-->
                    <div class="summary-section">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>£<?php echo number_format($total_amount, 2); ?></span>
                        </div>

                        <div class="summary-row" id="shipping-cost-row">
                            <span>Shipping</span>
                            <span id="shipping-cost">£0.00</span>
                        </div>

                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span id="total-with-shipping">£<?php echo number_format($total_amount, 2); ?></span>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <script>
            let currentStep = 1;
            let shippingPrice = 0;
            const productTotal = <?php echo $total_amount; ?>;

            function nextStep(step) {
                //update price after selecting shipping
                if (currentStep === 2 && step === 3) {
                    const selected = document.querySelector('input[name="shipping_method"]:checked');
                    if (selected) {
                        shippingPrice = parseFloat(selected.getAttribute('data-price'));
                        updateOrderSummary();
                    }
                }

                //fills the review form if in step4
                if (step === 4) {
                    populateReview();
                }

                document.getElementById('step-' + currentStep).classList.remove('active');
                document.getElementById('step-' + step).classList.add('active');
                currentStep = step;
            }

            //order details with price updated
            function updateOrderSummary() {
                document.getElementById('shipping-cost').textContent = '£' + shippingPrice.toFixed(2);
                const totalWithShipping = productTotal + shippingPrice;
                document.getElementById('total-with-shipping').textContent = '£' + totalWithShipping.toFixed(2);
            }

            //address validation
            function validateAddressAndNext() {
                const country = document.getElementById('country').value.trim().toLowerCase();

                //what is required
                const requiredFields = ['line1', 'street', 'city', 'postal_code', 'country'];
                for (const field of requiredFields) {
                    if (!document.getElementById(field).value.trim()) {
                        alert('Please fill in all required fields.');
                        return;
                    }
                }

                nextStep(2);
            }

            //payment details parameters
            function validatePayment() {
                const cardNumber = document.getElementById("card_number").value.trim();
                const expiration = document.getElementById("expiration_date").value.trim();
                const cvv = document.getElementById("cvv").value.trim();
                const cardHolder = document.getElementById("card_holder").value.trim();

                const cardNumberRegex = /^\d{16}$/;
                const expirationRegex = /^\d{2}\/\d{2}$/;
                const cvvRegex = /^\d{3}$/;

                if (!cardNumberRegex.test(cardNumber)) {
                    alert("Invalid card number. Please enter 16 digits.");
                    return false;
                }
                if (!expirationRegex.test(expiration)) {
                    alert("Invalid expiration date. Please use MM/YY format.");
                    return false;
                }
                if (!cvvRegex.test(cvv)) {
                    alert("Invalid CVV. Please enter 3 digits.");
                    return false;
                }
                if (cardHolder === "") {
                    alert("Card holder name is required.");
                    return false;
                }
                return true;
            }

            //review info filled with previous steps details
            function populateReview() {
                //shipping
                let shippingMethodName = "";
                let shippingMethodPrice = 0;
                const shippingRadios = document.getElementsByName("shipping_method");
                for (let i = 0; i < shippingRadios.length; i++) {
                    if (shippingRadios[i].checked) {
                        const methodId = shippingRadios[i].value;
                        const methodLabel = document.querySelector(`label[for="shipping_${methodId}"]`).textContent.trim();
                        shippingMethodName = methodLabel;
                        shippingMethodPrice = parseFloat(shippingRadios[i].getAttribute('data-price'));
                        break;
                    }
                }

                //address
                const addressLines = [
                    document.getElementById('line1').value,
                    document.getElementById('line2').value,
                    document.getElementById('street').value,
                    document.getElementById('city').value,
                    document.getElementById('region').value,
                    document.getElementById('postal_code').value,
                    document.getElementById('country').value
                ].filter(line => line.trim() !== '').join(', ');

                //payment with security 
                const cardNumber = document.getElementById('card_number').value;
                const lastFourDigits = cardNumber.slice(-4);

                //currency
                const formatCurrency = (amount) => {
                    return `£${amount.toFixed(2)}`;
                };

                const reviewHtml = `
        <div class="review-section">
            <h3>Shipping Address</h3>
            <p>${addressLines || 'No address provided'}</p>
        </div>
        
        <div class="review-section">
            <h3>Shipping Method</h3>
            <p>${shippingMethodName || 'Not selected'} - ${formatCurrency(shippingMethodPrice)}</p>
        </div>
        
        <div class="review-section">
            <h3>Payment Method</h3>
            <p>Credit Card (ending in ${lastFourDigits})</p>
            <p>Name on Card: ${document.getElementById('card_holder').value}</p>
        </div>
        
        <div class="review-section">
            <h3>Order Summary</h3>
            <p>Subtotal: ${formatCurrency(productTotal)}</p>
            <p>Shipping: ${formatCurrency(shippingPrice)}</p>
            <p><strong>Total: ${formatCurrency(productTotal + shippingPrice)}</strong></p>
        </div>
    `;
                document.getElementById('review-details').innerHTML = reviewHtml;
            }

            //expiration date as MM/YY
            document.getElementById('expiration_date').addEventListener('input', function (e) {
                const input = e.target;
                let value = input.value.replace(/\D/g, '');
                if (value.length > 2) {
                    value = value.slice(0, 2) + '/' + value.slice(2, 4);
                }
                input.value = value;
            });

            //update total after shipping
            const shippingOptions = document.querySelectorAll('input[name="shipping_method"]');
            shippingOptions.forEach(option => {
                option.addEventListener('change', function () {
                    shippingPrice = parseFloat(this.getAttribute('data-price'));
                    updateOrderSummary();
                });
            });

            //always start with first shipping method selected
            window.addEventListener('DOMContentLoaded', function () {
                const firstShippingOption = document.querySelector('input[name="shipping_method"]');
                if (firstShippingOption) {
                    firstShippingOption.checked = true;
                    shippingPrice = parseFloat(firstShippingOption.getAttribute('data-price'));
                    updateOrderSummary();
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>
<?php include 'footer.php'; ?>
