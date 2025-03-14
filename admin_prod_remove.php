<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_log.php');
    exit();
}

include 'sidebar.php';
require_once(__DIR__ . '/PHPHost.php');

$message = "";
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $action = $_POST['action'];

    if ($action === "toggle") {
        $new_status = ($_POST['active'] === 'yes') ? 'no' : 'yes';
        try {
            $query = "UPDATE product SET active = ? WHERE product_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$new_status, $product_id]);
            $message = "Product status updated successfully";
        } catch (PDOException $e) {
            $error = "Error updating product status: " . $e->getMessage();
        }
    } elseif ($action === "remove") {
        $query = "SELECT COUNT(*) AS cnt 
                    FROM order_prod
                    JOIN product_item ON order_prod.product_item_id = product_item.product_item_id
                    WHERE product_item.product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['cnt'] > 0) {
            $error = "Cannot remove product ID {$product_id} because it is part of one or more orders, try disactivating the product to keep it from being displayed on the website.";

        } else {
            try {
                $stmt = $db->prepare("DELETE FROM product_item WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $stmt = $db->prepare("DELETE FROM product WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $message = "Product removed successfully";
            } catch (PDOException $ex) {
                $error = "Error removing product: " . $ex->getMessage();
            }
        }
    }
}

// filter for categories:
$f_category = (isset($_GET['category']) && $_GET['category'] !== "")
    ? intval($_GET['category'])
    : 0;

$query = "
    SELECT
    product.product_id,
    product.product_name,
    product.product_image,
    product.active,
    product_category.category_name,
    COALESCE(
        (SELECT product_item.quantity
        FROM product_item
        WHERE product_item.product_id = product.product_id
        ORDER BY product_item.product_item_id LIMIT 1),
        0
        ) AS quantity,
        COALESCE(
        (SELECT product_item.price
        FROM product_item
        WHERE product_item.product_id = product.product_id
        ORDER BY product_item.product_item_id LIMIT 1),
        0
        ) AS price
         FROM product
         LEFT JOIN product_category ON product.product_category_id = product_category.product_category_id

";
if ($f_category > 0) {
    $query .= " WHERE product.product_category_id = :cat";
    $stmt = $db->prepare($query);
    $stmt->execute([':cat' => $f_category]);

} else {
    $stmt = $db->prepare($query);
    $stmt->execute();
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// get categories for the drop down filter box
$catStmt = $db->prepare("SELECT product_category_id, category_name FROM product_category");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Product Management (Remove Product)</title>
</head>

<body>
    <h2>Product Management</h2>
    <?php if (!empty($message))
        echo "<div class= 'message'>{$message}</div>"; ?>
    <?php if (!empty($error))
        echo "<div class='error'>{$error}</div>"; ?>

    <!-- category filter-->
    <form class="filter-form" method="GET">
        <label for="category">Category:</label>
        <select name="category" id="category">
            <option value="">ALL categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['product_category_id']; ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['product_category_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Apply Filter</button>
    </form>

    <!-- table header-->
    <div class="card-container">
        <div class="card-row header-card">
            <div class="card-cell col-image">Image</div>
            <div class="card-cell col-id">Product ID</div>
            <div class="card-cell col-name">Product Name</div>
            <div class="card-cell col-price">Price</div>
            <div class="card-cell col-category">Category</div>
            <div class="card-cell col-quantity">Quantity</div>
            <div class="card-cell col-status">Status</div>
            <div class="card-cell col-actions">Action</div>
        </div>

        <!-- cards for each product-->
        <?php foreach ($products as $product): ?>
            <div class="card-row">
                <div class="card-cell col-image">
                    <?php if (!empty($product['product_image'])): ?>
                        <img src="<?= htmlspecialchars($product['product_image']) ?>"
                            alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-image">
                    <?php else: ?>
                        <img src="default-image.jpg" alt="Default Image" class="product-image">
                    <?php endif; ?>
                </div>
                <div class="card-cell col-id"><?= $product['product_id'] ?></div>
                <div class="card-cell col-name"><?= htmlspecialchars($product['product_name']) ?></div>
                <div class="card-cell col-price">£<?= number_format($product['price'], 2) ?></div>
                <div class="card-cell col-category"><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></div>
                <div class="card-cell col-quantity"><?= $product['quantity'] ?></div>
                <div class="card-cell col-status"><?= ucfirst($product['active']) ?></div>
                <div class="card-cell col-actions">
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="active" value="<?= $product['active'] ?>">
                        <button type="submit" class="toggle-btn">
                            <?= ($product['active'] === 'yes') ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                    <form method="POST"
                        onsubmit="return confirm('Are you sure you want to permanently delete this product from the database?');">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="remove-btn">Remove</button>
                    </form>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #E0E1DD;
        margin: 0;
        padding: 20px;
        color: #0D1B2A;
    }

    h2 {
        text-align: center;
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 25px;
        color: #1B263B;
    }

    .message,
    .error {
        margin: 0 auto;
        max-width: 1000px;
        text-align: center;
        padding: 12px;
        margin-bottom: 20px;
        background-color: #dda8a8;
        color: #0D1B2A;
        border: 1px solid #dda8a8;
        border-radius: 4px;
    }

    .message {
        margin: 0 auto;
        max-width: 1000px;
        text-align: center;
        padding: 12px;
        margin-bottom: 20px;
        background-color: #b8dda8;
        color: #0D1B2A;
        border: 1px solid #b8dda8;
        border-radius: 4px;
    }

    .error {
        margin: 0 auto;
        max-width: 1000px;
        text-align: center;
        padding: 12px;
        margin-bottom: 20px;
        background-color: #dda8a8;
        color: #0D1B2A;
        border: 1px solid #dda8a8;
        border-radius: 4px;
    }

    .filter-form {
        max-width: 1000px;
        margin: 0 auto 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
    }

    .filter-form select,
    .filter-form button {
        padding: 8px 12px;
        font-size: 1em;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .filter-form button {
        background: #415A77;
        color: #E0E1DD;
        border: none;
        cursor: pointer;
    }

    .filter-form button:hover {
        background: #778DA9;
        color: #0D1B2A;
    }

    .card-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .card-row {
        display: flex;
        align-items: center;
        border: 1px solid #ddd;
        padding: 10px;
        margin-bottom: 10px;
        background: #fff;
        border-radius: 4px;
    }

    .header-card {
        background: #1B263B;
        color: #E0E1DD;
        font-weight: bold;
    }

    .card-cell {
        padding: 5px 10px;
        text-align: center;
        flex: 1;
    }

    .col-image {
        flex: 0 0 120px;
    }

    .col-id {
        flex: 0 0 80px;
    }

    .col-name {
        flex: 2;
    }

    .col-category {
        flex: 1;
    }

    .col-quantity,
    .col-price,
    .col-status {
        flex: 0 0 80px;
    }

    .col-actions {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .product-image {
        width: 100%;
        height: 100px;
        object-fit: contain;
    }

    .toggle-btn,
    .remove-btn {
        width: 100%;
        height: 35px;
        padding: 6px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9em;
        background: #415A77;
        color: #E0E1DD;
        transition: background 0.3s ease, color 0.3s ease;
    }

    .toggle-btn:hover,
    .remove-btn:hover {
        background: #778DA9;
        color: #0D1B2A;
    }
</style>
