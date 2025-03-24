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

// get categories for the drop down filter box
$catStmt = $db->prepare("SELECT product_category_id, category_name FROM product_category");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$filterConditions = [];
$filterParams = [];

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    $filterConditions[] = "product.product_name LIKE ?";
    $filterParams[] = "%" . $search . "%";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $categoryId = $_GET['category'];
    $filterConditions[] = "product.product_category_id = ?";
    $filterParams[] = $categoryId;
}

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

if (count($filterConditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $filterConditions);
}

$stmt = $db->prepare($query);
$stmt->execute($filterParams);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Product Management (Remove Product)</title>

    <link rel="stylesheet" href="admin-dashboard.css">
</head>

<body>
    <h2>Product Management</h2>
    <?php if (!empty($message))
        echo "<div class= 'message'>{$message}</div>"; ?>
    <?php if (!empty($error))
        echo "<div class='error'>{$error}</div>"; ?>

    <!-- category filter-->
    <form class="filter-form" method="GET" action="">
        <input type="text" name="search" placeholder="Search product name"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <select name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['product_category_id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['product_category_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
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

                <div class="card-cell col-id" data-label="Product ID"><?= $product['product_id'] ?></div>
                <div class="card-cell col-name" data-label="Product Name"><?= htmlspecialchars($product['product_name']) ?>
                </div>
                <div class="card-cell col-price" data-label="Price" data-label="Price">
                    £<?= number_format($product['price'], 2) ?></div>
                <div class="card-cell col-category" data-label="Category">
                    <?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></div>
                <div class="card-cell col-quantity" data-label="Quantity"><?= $product['quantity'] ?></div>
                <div class="card-cell col-status" data-label="Status"><?= ucfirst($product['active']) ?></div>
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
        background: var(--bg-color);
        margin: 0;
        padding: 20px;

    }

    h2 {
        text-align: center;
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 25px;
        color: var(--text-color);
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
        margin-bottom: 20px;
        text-align: center;
    }

    .filter-form input[type="text"],
    .filter-form select {
        padding: 8px;
        width: 200px;
        border: 1px solid #ccc;
        border-radius: 3px;
        margin-right: 10px;
    }

    .filter-form button {
        padding: 8px 15px;
        background-color: #415A77;
        color: #E0E1DD;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }

    .filter-form button:hover {
        background-color: #778DA9;
        color: #0D1B2A;
    }

    .card-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .card-row {
        display: flex;
        align-items: center;
        /*border: 1px solid var(--border-color);*/
        padding: 10px;
        margin-bottom: 10px;
        background: var(--card-bg);
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

    @media all and (max-width: 767px) {
        .card-row {
            flex-direction: column;
            align-items: stretch;
        }

        .header-card {
            display: none;
        }

        .card-cell {
            display: flex;
            padding: 10px 0;
            text-align: left;
            width: 100%;
        }

        .card-cell:before {
            content: attr(data-label);
            flex-basis: 40%;
            font-weight: bold;
            text-align: right;
            padding-right: 10px;
        }

        .col-image {
            flex: 1;
        }

        .col-id,
        .col-name,
        .col-price,
        .col-category,
        .col-quantity,
        .col-status {
            flex: 1;
        }

        .col-actions {
            flex: 1;
            flex-direction: row;
            justify-content: space-between;
        }

        .product-image {
            width: 60%;
            margin: 0 auto;
        }
    }
</style>