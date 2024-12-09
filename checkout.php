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
            p.item_price, 
            p.item_name, 
            p.image
        FROM 
            asad_basket b
        JOIN 
            products p ON b.product_id = p.product_id
        WHERE 
            b.user_id = :user_id
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $basketItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // calculate total amount
    $total_amount = 0;
    foreach ($basketItems as $item) {
        $total_amount += $item['quantity'] * $item['item_price'];
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


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        //clears basket
        $clearQuery = "DELETE FROM asad_basket WHERE user_id = :user_id";
        $stmt = $db->prepare($clearQuery);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        //simulate payment success
        echo "<script>alert('Payment was successful!');</script>";
        echo "<script>window.location.href = 'index.php';</script>";
        exit();
    } catch (PDOException $ex) {
        echo "Error clearing basket: " . htmlspecialchars($ex->getMessage());
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
    <!-- order details -->
    <section class="order-summary">
        <h2>Order Summary</h2>
        <?php foreach ($basketItems as $item): ?>
            <div class="item-card">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                <div class="product-details">
                    <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                    <p>Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                    <p>Price: £<?php echo number_format($item['item_price'], 2); ?></p>
                    <p>Subtotal: £<?php echo number_format($item['quantity'] * $item['item_price'], 2); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <p style="font-size: 18px; font-weight: bold;">Total Amount: £<?php echo number_format($total_amount, 2); ?></p>
    </section>

    <!-- dummy paymet -->
    <section class="payment">
        <h2>Payment</h2>
        <form method="POST" action="">
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

.payment {
    flex: 1;
    background-color: #08429830;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.payment h2 {
    margin-bottom: 20px;
}

.payment form {
    display: flex;
    flex-direction: column;
}

.payment label {
    font-size: 14px;
    margin-bottom: 5px;
    color: #333;
}

.payment input,
.payment select {
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
