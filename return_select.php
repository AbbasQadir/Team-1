<?php
session_start();
include 'navbar.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once('PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage());
    exit();
}

try {
    $username = $_SESSION['user'];
    $stmt = $db->prepare("SELECT user_id, first_name FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo "User not found!";
        exit();
    }
} catch (PDOException $ex) {
    echo "Database error: " . htmlspecialchars($ex->getMessage());
    exit();
}

// Query order items for the logged-in user
$sql = "
    SELECT 
        op.order_prod_id,
        op.orders_id,
        op.quantity AS ordered_quantity,
        o.order_date,
        p.product_name
    FROM order_prod op
    INNER JOIN orders o ON op.orders_id = o.orders_id
    INNER JOIN product_item pi ON op.product_item_id = pi.product_item_id
    INNER JOIN product p ON pi.product_id = p.product_id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
";
$stmt = $db->prepare($sql);
$stmt->execute([$user['user_id']]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Select Item to Return</title>
    <link rel="stylesheet" href="sty.css">
    <style>
        /* Main container style */
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
            margin-left: 45px;
            text-align: left;
        }
        /* Order table styling */
        .order-table {
            list-style-type: none;
            padding: 0;
        }
        .order-table li {
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
        /* Column widths; adjust as needed */
        .col-1, .col-2, .col-3, .col-4, .col-5, .col-6 {
            flex-basis: 16%;
            text-align: center;
        }
        @media all and (max-width: 767px) {
            .table-header {
                display: none;
            }
            .order-table li {
                display: block;
            }
            .col {
                display: flex;
                padding: 10px 0;
            }
            .col:before {
                color: #0d1b2a;
                padding-right: 10px;
                content: attr(data-label);
                flex-basis: 50%;
                text-align: right;
            }
        }
        /* Button styling for the Return Item action */
        .button-details {
            background-color: #415A77;
            color: #E0E1DD;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        .button-details:hover {
            background-color: #1b263b;
            color: #E0E1DD;
            box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="main">
        <h2>Return Items</h2>
        <h3>Select the item you wish to return, <?php echo htmlspecialchars($user['first_name']); ?>:</h3>
        <?php if (count($orderItems) > 0): ?>
            <ul class="order-table">
                <li class="table-header">
                    <div class="col-1" data-label="Order Date">Order Date</div>
                    <div class="col-2" data-label="Order ID">Order ID</div>
                    <div class="col-3" data-label="Product">Product</div>
                    <div class="col-4" data-label="Purchased">Purchased</div>
                    <div class="col-5" data-label="Returned">Returned</div>
                    <div class="col-6" data-label="Action">Action</div>
                </li>
                <?php foreach ($orderItems as $item): ?>
                    <?php 
                        // Calculate how many of the item have been returned so far.
                        $stmtReturned = $db->prepare("SELECT SUM(quantity_returned) as returned_total FROM returns WHERE order_prod_id = ?");
                        $stmtReturned->execute([$item['order_prod_id']]);
                        $returnedData = $stmtReturned->fetch(PDO::FETCH_ASSOC);
                        $returned_total = $returnedData['returned_total'] ? $returnedData['returned_total'] : 0;
                    ?>
                    <li class="table-row">
                        <div class="col-1" data-label="Order Date"><?php echo htmlspecialchars($item['order_date']); ?></div>
                        <div class="col-2" data-label="Order ID"><?php echo htmlspecialchars($item['orders_id']); ?></div>
                        <div class="col-3" data-label="Product"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div class="col-4" data-label="Purchased"><?php echo htmlspecialchars($item['ordered_quantity']); ?></div>
                        <div class="col-5" data-label="Returned"><?php echo htmlspecialchars($returned_total); ?></div>
                        <div class="col-6" data-label="Action">
                            <?php if ($returned_total >= $item['ordered_quantity']): ?>
                                <span>Returned</span>
                            <?php else: ?>
                                <a href="return_product.php?orders_id=<?php echo urlencode($item['orders_id']); ?>&order_prod_id=<?php echo urlencode($item['order_prod_id']); ?>" class="button-details">Return Item</a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No items available for return.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>
