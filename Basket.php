<?php
session_start();




try {
    require_once(__DIR__ . '/PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . $ex->getMessage();
    exit;
}

if (!isset($_SESSION['uid'])) {
    echo "Please log in to view your basket.";
    exit;
}

$user_id = $_SESSION['uid'];  

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

// removing things from basket
if (isset($_GET['remove'])) {
    $productId = $_GET['remove'];
    $removeQuery = "DELETE FROM asad_basket WHERE user_id = :user_id AND product_id = :product_id";
    $removeStmt = $db->prepare($removeQuery);
    $removeStmt->execute([':user_id' => $user_id, ':product_id' => $productId]);
    header("Location: Basket.php");
    exit;
}

// updating bsket
if (isset($_POST['update_quantity']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $productId = $_POST['product_id'];
    $newQuantity = $_POST['quantity'];

    if ($newQuantity > 0) {
        $updateQuery = "UPDATE asad_basket SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([':quantity' => $newQuantity, ':user_id' => $user_id, ':product_id' => $productId]);
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
</head>
<body>

    <h1>Your Basket</h1>

    <?php if (count($basketItems) > 0): ?>
        <form method="POST" action="Basket.php">
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Product Picture</th>
                        <th>Quantity</th>
                        <th>Price per Item</th>
                        <th>Total Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($basketItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>"></td>
                            <td>
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" required>
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            </td>
                            <td>£<?php echo number_format($item['item_price'], 2); ?></td>
                            <td>£<?php echo number_format($item['total_price'], 2); ?></td>
                            <td>
                                <a href="Basket.php?remove=<?php echo $item['product_id']; ?>" class="remove-btn">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div>
                <button type="submit" name="update_quantity" class="checkout-btn">Update Basket</button>
            </div>
        </form>

        <div>
            <a href="checkout.php" class="checkout-btn">Checkout</a>
        </div>

    <?php else: ?>
        <p>Your basket is empty.</p>
    <?php endif; ?>

    <div>
        <a href="previous_orders.php" class="previousorder-btn">Previous Orders</a>
    </div>

    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        img {
            width: 100px;
            height: auto;
        }
        .checkout-btn, .remove-btn, .previousorder-btn {
            background-color: #2a4d69;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .checkout-btn:hover, .remove-btn:hover, .previousorder-btn:hover {
            background-color: #4c7b97;
        }
    </style>

</body>
</html>
