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


$message = "";



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $product_item_id = $_POST['product_item_id'];
    $add_quantity = $_POST['add_quantity'];

    if (is_numeric($add_quantity) && $add_quantity > 0) {
        try {
            $query = "UPDATE product_item SET quantity = quantity + ? WHERE product_item_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$add_quantity, $product_item_id]);
            $message = "Stock updated successfully.";
        } catch (PDOException $ex) {
            $message = "Error updating stock: " . htmlspecialchars($ex->getMessage());
        }
    } else {
        $message = "Invalid quantity entered.";
    }
}


$categoryQuery = "SELECT product_category_id, category_name FROM product_category ORDER BY category_name ASC";
$catStmt = $db->prepare($categoryQuery);
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);


$filterConditions = [];
$filterParams = [];

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    $filterConditions[] = "p.product_name LIKE ?";
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
            product_item.product_item_id, 
            product_item.quantity, 
            product_item.price
                FROM product  
                JOIN product_item  ON product.product_id = product_item.product_id";

if (count($filterConditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $filterConditions);
}

$stmt = $db->prepare($query);
$stmt->execute($filterParams);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Stock Management</title>
	<link rel="stylesheet" href="admin-dashboard.css">

    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            color: var(--text-color);
        }

        .message {
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
            width: 100%;
            margin: 0 auto;
        }

        .card-row,
        .product-row {
            display: grid;
            grid-template-columns: 1fr 2fr 0.75fr 0.75fr 1fr 1.5fr;
            align-items: center;
            text-align: center;
            padding: 15px;
            width: 100%;
        }

        .card-row {
            padding-top: 25px;
            padding-bottom: 25px;
            background: #1B263B;
            color: #E0E1DD;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .product-image {
            width: 100%;
            height: 100px;
            object-fit: contain;
        }

        .product-row {
            background: var(--card-bg);
            margin-bottom: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        }

        .card-cell {

            text-align: center;
        }

        .low-stock {
            color: orange;
            font-weight: bold;
        }

        .out-of-stock {
            color: red;
            font-weight: bold;
        }

        .update-form {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .update-form input[type="number"] {
            width: 120px;
            padding: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            border-radius: 4px;
            text-align: center;
        }

        .update-form button {
            padding: 8px 15px;
            background-color: #415A77;
            color: #E0E1DD;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .update-form button:hover {
            background-color: #778DA9;
            color: #0D1B2A;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <h1>Stock Management</h1>

        <?php if ($message != ""): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

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
            <div class="card-row">
                <div class="card-cell col-image">Product Image</div>
                <div class="card-cell">Product Name</div>
                <div class="card-cell">Price</div>
                <div class="card-cell">Quantity</div>
                <div class="card-cell">Status</div>
                <div class="card-cell">Manage Stock</div>
            </div>
            <?php foreach ($products as $product): ?>
                <div class="product-row">
                    <div class="card-cell col-image">
                        <?php if (!empty($product['product_image'])): ?>
                            <img src="<?= htmlspecialchars($product['product_image']) ?>"
                                alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-image">
                        <?php else: ?>
                            <img src="default-image.jpg" alt="Default Image" class="product-image">
                        <?php endif; ?>
                    </div>
                    <div class="card-cell "><?php echo htmlspecialchars($product['product_name']); ?></div>
                    <div class="card-cell ">£<?php echo htmlspecialchars($product['price']); ?></div>
                    <div class="card-cell "><?php echo htmlspecialchars($product['quantity']); ?></div>
                    <div class="card-cell ">
                        <?php
                        if ($product['quantity'] == 0) {
                            echo '<span class="out-of-stock">Out of Stock</span>';
                        } elseif ($product['quantity'] <= 10) {
                            echo '<span class="low-stock">Low Stock</span>';
                        } else {
                            echo 'In Stock';
                        }
                        ?>
                    </div>
                    <div class="card-cell ">
                        <form class="update-form" method="POST" action="">
                            <input type="hidden" name="product_item_id" value="<?php echo $product['product_item_id']; ?>">
                            <input type="number" name="add_quantity" placeholder="Add Quantity" min="1" step="1" required>
                            <button type="submit" name="update_stock">Update</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
</body>

</html>