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
    $filterConditions[] = "p.product_category_id = ?";
    $filterParams[] = $categoryId;
}

$query = "SELECT p.product_id, p.product_name, pi.product_item_id, pi.quantity, pi.price
          FROM product p 
          JOIN product_item pi ON p.product_id = pi.product_id";

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
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #E0E1DD;
            margin: 0;
            padding: 20px;
            color: #0D1B2A;

        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #0D1B2A;
        }

        .message {
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            background-color: #E0E1DD;
            color: #0D1B2A;
            border: 1px solid #c1e2b3;
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
            min-width: 100px;
            min-height: 40px;
        }

        .filter-form button:hover {
            background-color: #778DA9;
            color: #0D1B2A;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #E0E1DD;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: left;
            color: #0D1B2A;

        }

        th {
            background-color: #1B263B;

            color: #E0E1DD;

        }

        tr:nth-child(even) {
            background-color: #E0E1DD;
        }

        .low-stock {
            color: orange;
            font-weight: bold;
        }

        .out-of-stock {
            color: red;
            font-weight: bold;
        }

        .update-form input[type="number"] {
            width: 180px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
            margin-right: 10px;
        }

        .update-form button {
            padding: 8px 15px;
            background-color: #415A77;
            color: #E0E1DD;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            min-width: 100px;
            min-height: 40px;
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

        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Manage Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $prod): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($prod['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($prod['price']); ?></td>
                        <td><?php echo htmlspecialchars($prod['quantity']); ?></td>
                        <td>
                            <?php
                            if ($prod['quantity'] == 0) {
                                echo '<span class="out-of-stock">Out of Stock</span>';
                            } elseif ($prod['quantity'] <= 10) {
                                echo '<span class="low-stock">Low Stock</span>';
                            } else {
                                echo 'In Stock';
                            }
                            ?>
                        </td>
                        <td>
                            <form class="update-form" method="POST" action="">
                                <input type="hidden" name="product_item_id" value="<?php echo $prod['product_item_id']; ?>">
                                <input type="number" name="add_quantity" placeholder="Add Quantity" min="1" required>
                                <button type="submit" name="update_stock">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>