<?php
session_start();

try {
    require_once(__DIR__ . '/PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . $ex->getMessage();
    exit;
}

// check if log in is there
if (!isset($_SESSION['uid'])) {
    echo "<script>alert('Please log in to view your basket.');</script>";
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$user_id = $_SESSION['uid'];

// get basket from user that is logged in 
$query = "
    SELECT 
        p.item_name, 
        p.image, 
        b.quantity, 
        p.item_price, 
        (b.quantity * p.item_price) AS total_price,
        b.product_id
    FROM 
        asad_basket b 
    JOIN 
        products p 
    ON 
        b.product_id = p.product_id 
    WHERE 
        b.user_id = :user_id
";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$basketItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// basket item rmoval
if (isset($_GET['remove'])) {
    $productId = $_GET['remove'];
    $removeQuery = "DELETE FROM asad_basket WHERE user_id = :user_id AND product_id = :product_id";
    $removeStmt = $db->prepare($removeQuery);
    $removeStmt->execute([':user_id' => $user_id, ':product_id' => $productId]);
    header("Location: Basket.php");
    exit;
}

// quanitity uodater
if (isset($_POST['update_quantity']) && isset($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $productId => $newQuantity) {
        if ($newQuantity > 0) {
            $updateQuery = "UPDATE asad_basket SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([
                ':quantity' => $newQuantity,
                ':user_id' => $user_id,
                ':product_id' => $productId
            ]);
        }
    }
    header("Location: Basket.php");
    exit;
}

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basket</title>
	<link rel="stylesheet" href="homestyle.css">
</head>
<body>

<h1 class="basket-title">Your Basket</h1>

<?php if (count($basketItems) > 0): ?>
    <form method="POST" action="Basket.php" class="basket-form">
        <table class="basket-table">
            <thead>
                <tr>
                    <th class="basket-header">Product Name</th>
                    <th class="basket-header">Product Picture</th>
                    <th class="basket-header">Quantity</th>
                    <th class="basket-header">Price per Item</th>
                    <th class="basket-header">Total Price</th>
                    <th class="basket-header">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($basketItems as $item): ?>
                    <tr class="basket-row">
                        <td class="basket-cell"><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td class="basket-cell">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" class="basket-image">
                        </td>
                        <td class="basket-cell">
                            <input type="number" name="quantity[<?php echo $item['product_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" required class="basket-quantity">
                        </td>
                        <td class="basket-cell">&pound;<?php echo number_format($item['item_price'], 2); ?></td>
                        <td class="basket-cell">&pound;<?php echo number_format($item['total_price'], 2); ?></td>
                        <td class="basket-cell">
                            <a href="Basket.php?remove=<?php echo $item['product_id']; ?>" class="basket-remove-btn">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="basket-actions">
            <button type="submit" name="update_quantity" class="basket-update-btn">Update Basket</button>
        </div>
    </form>

    <div class="basket-checkout">
        <a href="checkout.php" class="basket-checkout-btn">Checkout</a>
    </div>

<?php else: ?>
    <p class="basket-empty">Your basket is empty.</p>
<?php endif; ?>

<div class="basket-previous-orders">
    <a href="previous_orders.php" class="basket-previous-orders-btn">Previous Orders</a>
</div>

<style>
    body{
		margin:0;
	}
    .basket-title {
        margin-top: 20px;
        text-align: center;
    }
    .basket-table {
        width: 97%;
        border-collapse: collapse;
        margin: 20px auto;
        background: white;
    }
    .basket-table, .basket-header, .basket-cell {
        border: 1px solid #ddd;
    }
    .basket-header {
        padding: 12px;
        background-color: #f4f4f4;
        text-align: center;
    }
    .basket-cell {
        text-align: center;
        padding: 12px;
    }
    .basket-image {
        width: 150px;
        height: auto;
        object-fit: cover;
    }
    .basket-remove-btn, .basket-checkout-btn, .basket-previous-orders-btn {
        font-family: 'Merriweather', serif;
    	background-color: #084298;
        color: white;
        border: none;
        padding: 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 200px;
        text-decoration: none;
        margin: 10px;
        display: inline-block;
        text-align: center;
    }
    .basket-remove-btn:hover, .basket-checkout-btn:hover, .basket-update-btn:hover, .basket-previous-orders-btn:hover {
        background-color: #b8c5d4;
        color: #084298;
        box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.1);
        text-decoration: none;
    }
	.basket-update-btn{
    	font-family: 'Merriweather', serif;
    	background-color: #084298;
        color: white;
        border: none;
        padding: 18px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 200px;
        text-decoration: none;
        margin-left: 10px;
        display: inline-block;
        text-align: center;
    	margin-bottom:10px;
    }
	

</style>




</body>
</html>
<?php include 'footer.php'; ?>