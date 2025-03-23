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

try {
    require_once(__DIR__ . '/PHPHost.php');
} catch (Exception $ex) {
    echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
    exit;
}

$categoryQuery = "SELECT product_category_id, category_name FROM product_category ORDER BY category_name ASC";
$catStmt = $db->prepare($categoryQuery);
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

$query = "SELECT product.product_id, product.product_name, product.product_discription, 
                 product.product_image, product_category.category_name, 
                 product_item.price, product_item.quantity 
          FROM product
          LEFT JOIN product_category ON product.product_category_id = product_category.product_category_id
          LEFT JOIN product_item ON product.product_id = product_item.product_id";

if (count($filterConditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $filterConditions);
}

$stmt = $db->prepare($query);
$stmt->execute($filterParams);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'sidebar.php';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Product Management</title>
    <link rel="stylesheet" href="admin-dashboard.css">
</head>

<body>
    <h2>Product Management</h2>

    <!--filter-->
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


    <div class="card-container">
        <div class="card-row header-card">
            <div class="card-cell col-image">Image</div>
            <div class="card-cell col-name">Product Name</div>
            <div class="card-cell col-description">Description</div>
            <div class="card-cell col-price">Price</div>
            <div class="card-cell col-quantity">Quantity</div>
            <div class="card-cell col-actions">Actions</div>
        </div>

        <?php foreach ($products as $product): ?>
            <div class="card-row">
                <div class="card-cell col-image" data-label="Image">
                    <img src="<?= htmlspecialchars($product['product_image']) ?>" class="product-image">
                </div>
                <div class="card-cell col-name" data-label="Product Name">
                    <?= htmlspecialchars($product['product_name']) ?>
                </div>
                <div class="card-cell col-description" data-label="Product Description">
                    <?= htmlspecialchars($product['product_discription']) ?>
                </div>
                <div class="card-cell col-price" data-label="Price">
                    £<?= number_format($product['price'], 2) ?>
                </div>
                <div class="card-cell col-quantity" data-label="Quantity">
                    <?= $product['quantity'] ?>
                </div>
                <div class="card-cell col-actions" data-label="Action">
                    <a href="edit_product.php?product_id=<?= $product['product_id'] ?>" class="edit-btn">Edit Product</a>
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
        padding: 10px;
        margin-bottom: 10px;
        background: var(--card-bg);
        border-radius: 4px;
        align-items: center;
    }

    .header-card {
        background: #1B263B;
        color: #E0E1DD;
        font-weight: bold;
    }

    .card-cell {
        padding: 8px;
        text-align: center;
    }


    .col-image {
        flex: 0 0 120px;
    }

    .col-name {
        flex: 2;
    }

    .col-description {
        flex: 2;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    .col-price {
        flex: 1;
    }

    .col-quantity {
        flex: 1;
    }

    .col-actions {
        flex: 2;
    }

    .product-image {
        width: 100px;
        height: 100px;
        object-fit: contain;
    }


    .edit-btn {
        width: 100%;
        height: 35px;
        padding: 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9em;
        background: #415A77;
        color: #E0E1DD;
        transition: background 0.3s ease, color 0.3s ease;
    }

    .edit-btn:hover {
        background: #778DA9;
        color: #0D1B2A;
    }


    @media all and (max-width: 767px) {
        .card-container {
            margin: auto 10px;
        }

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
            align-items: center;
        }

        .card-cell:before {
            content: attr(data-label);
            flex-basis: 40%;
            font-weight: bold;
            text-align: right;
            padding-right: 10px;
        }

        .col-image {
            justify-content: center;
        }

        .col-image:before {
            display: none;
        }


        .col-actions {
            justify-content: center;
        }

        .col-actions:before {
            display: none;
        }

        .edit-btn {
            width: 80%;
        }

        .message {
            margin: 0 auto 20px;
            max-width: 90%;
        }
    }
</style>
