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

//check if product_id is there
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    header('Location: product_management.php');
    exit();
}

$product_id = intval($_GET['product_id']);


try {
    $query = "SELECT product_category_id, category_name FROM product_category;";
    $result = $db->query($query);
    $categories = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    die("Database query failed: " . htmlspecialchars($ex->getMessage()));
}


try {
    $query = "SELECT p.*, pi.price, pi.quantity, pc.category_name, pc.product_category_id
              FROM product p
              LEFT JOIN product_item pi ON p.product_id = pi.product_id
              LEFT JOIN product_category pc ON p.product_category_id = pc.product_category_id
              WHERE p.product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $error = "Product not found";
    }
} catch (PDOException $ex) {
    $error = "Error fetching product details: " . htmlspecialchars($ex->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $product_price = floatval($_POST['product_price']);
    $product_quantity = intval($_POST['product_quantity']);
    $product_category_id = intval($_POST['product_category_id']);


    if ($product_price <= 0) {
        $error = "Price must be a positive number";
    } else if ($product_quantity < 0) {
        $error = "Quantity must be a non-negative number";
    } else {
        try {

            $db->beginTransaction();


            $query = "UPDATE product 
                      SET product_name = ?, product_discription = ?, product_category_id = ?
                      WHERE product_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$product_name, $product_description, $product_category_id, $product_id]);

            $query = "UPDATE product_item 
                      SET price = ?, quantity = ?
                      WHERE product_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$product_price, $product_quantity, $product_id]);


            $allowed_ext = ['jpg', 'png', 'jpeg'];
            $upload_dir = __DIR__ . "/images/";

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }


            function handleImageUpload($file, $upload_dir, $allowed_ext)
            {
                if (isset($file) && $file['error'] == 0) {
                    $image_name = $file['name'];
                    $image_tmp = $file['tmp_name'];
                    $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

                    if (in_array($image_ext, $allowed_ext)) {
                        $image_name_new = uniqid('img_', true) . '.' . $image_ext;
                        $upload_path = $upload_dir . $image_name_new;

                        if (move_uploaded_file($image_tmp, $upload_path)) {
                            return "images/" . $image_name_new;
                        }
                    }
                }
                return '';
            }

            $update_images = [];

            if ($_FILES['product_image']['error'] !== 4) {
                $new_image = handleImageUpload($_FILES['product_image'], $upload_dir, $allowed_ext);
                if (!empty($new_image)) {
                    $update_images['product_image'] = $new_image;
                }
            }

            if ($_FILES['product_image_2']['error'] !== 4) {
                $new_image_2 = handleImageUpload($_FILES['product_image_2'], $upload_dir, $allowed_ext);
                if (!empty($new_image_2)) {
                    $update_images['product_image_2'] = $new_image_2;
                }
            }

            if ($_FILES['product_image_3']['error'] !== 4) {
                $new_image_3 = handleImageUpload($_FILES['product_image_3'], $upload_dir, $allowed_ext);
                if (!empty($new_image_3)) {
                    $update_images['product_image_3'] = $new_image_3;
                }
            }


            if (!empty($update_images)) {
                $set_clauses = [];
                $params = [];

                foreach ($update_images as $field => $value) {
                    $set_clauses[] = "$field = ?";
                    $params[] = $value;
                }

                $params[] = $product_id;

                $query = "UPDATE product SET " . implode(', ', $set_clauses) . " WHERE product_id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute($params);
            }

            $db->commit();

            $message = "Product updated successfully";

            $query = "SELECT p.*, pi.price, pi.quantity, pc.category_name, pc.product_category_id
                      FROM product p
                      LEFT JOIN product_item pi ON p.product_id = pi.product_id
                      LEFT JOIN product_category pc ON p.product_category_id = pc.product_category_id
                      WHERE p.product_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $ex) {
            $db->rollBack();
            $error = "Error updating product: " . htmlspecialchars($ex->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="admin-dashboard.css">
    <title>Edit Product - <?= htmlspecialchars($product['product_name'] ?? '') ?></title>
</head>

<body>
    <h2>Edit Product</h2>

    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($product) && $product): ?>
        <div class="signup-container">
            <div class="signup-form-container">
                <form method="POST" enctype="multipart/form-data">
                    <label for="product_name">Name of Product</label>
                    <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>"
                        required>

                    <label for="product_description">Description</label>
                    <textarea name="product_description" rows="5"
                        required><?= htmlspecialchars($product['product_discription']) ?></textarea>

                    <!--main image -->
                    <div class="current-image">
                        <p>Current Main Image:</p>
                        <?php if (!empty($product['product_image'])): ?>
                            <img src="<?= htmlspecialchars($product['product_image']) ?>" alt="Product Image"
                                style="max-width: 150px; max-height: 150px;">
                        <?php else: ?>
                            <p>No image available</p>
                        <?php endif; ?>
                    </div>

                    <label class="custum-file-upload" for="product_image">
                        <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z" />
                            </svg>
                        </div>
                        <div class="text">
                            <span id="upload-text-1">Click to update main image</span>
                        </div>
                    </label>
                    <input type="file" name="product_image" id="product_image"
                        onchange="displayFileNameCustom(this, 'upload-text-1')" style="display:none;">

                    <!--image 2 -->
                    <div class="current-image">
                        <p>Current Image 2:</p>
                        <?php if (!empty($product['product_image_2'])): ?>
                            <img src="<?= htmlspecialchars($product['product_image_2']) ?>" alt="Product Image 2"
                                style="max-width: 150px; max-height: 150px;">
                        <?php else: ?>
                            <p>No secondary image available</p>
                        <?php endif; ?>
                    </div>

                    <label class="custum-file-upload" for="product_image_2">
                        <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z" />
                            </svg>
                        </div>
                        <div class="text">
                            <span id="upload-text-2">Click to update image 2</span>
                        </div>
                    </label>
                    <input type="file" name="product_image_2" id="product_image_2"
                        onchange="displayFileNameCustom(this, 'upload-text-2')" style="display:none;">

                    <!--image 3 -->
                    <div class="current-image">
                        <p>Current Image 3:</p>
                        <?php if (!empty($product['product_image_3'])): ?>
                            <img src="<?= htmlspecialchars($product['product_image_3']) ?>" alt="Product Image 3"
                                style="max-width: 150px; max-height: 150px;">
                        <?php else: ?>
                            <p>No tertiary image available</p>
                        <?php endif; ?>
                    </div>

                    <label class="custum-file-upload" for="product_image_3">
                        <div class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z" />
                            </svg></div>
                        <div class="text">
                            <span id="upload-text-3">Click to update image 3</span>
                        </div>
                    </label>
                    <input type="file" name="product_image_3" id="product_image_3"
                        onchange="displayFileNameCustom(this, 'upload-text-3')" style="display:none;">

                    <label for="product_price">Price</label>
                    <input type="number" name="product_price" value="<?= number_format($product['price'], 2, '.', '') ?>"
                        step="0.01" min="0.01" required>

                    <label for="product_quantity">Quantity</label>
                    <input type="number" name="product_quantity" value="<?= $product['quantity'] ?>" min="0" required>

                    <label for="product_category_id">Category</label>
                    <select name="product_category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['product_category_id'] ?>"
                                <?= ($product['product_category_id'] == $category['product_category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="hidden" name="update_product" value="1">

                   
                    <div class="button-row">
                        <button type="submit" name="update_product" class="button-add">Update Product</button>
                        <a href="admin_prod_edit.php" class="back-button">Back to Products</a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="error-message">Product not found or an error occurred.</div>
        <div class="button-row">
            <a href="product_management.php" class="back-button">Back to Products</a>
        </div>
    <?php endif; ?>

    <script>
        function displayFileNameCustom(input, elementId) {
            const fileName = input.files.length > 0 ? input.files[0].name : "Click to upload image";
            document.getElementById(elementId).textContent = fileName;
        }
    </script>
</body>


</html>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--bg-color);
        color: var(--text-color);
        line-height: 1.6;
        scroll-behavior: smooth;
        margin: 0;
        padding: 0;
    }

    h2 {
        text-align: center;
        font-size: 36px;
        font-weight: bold;
        margin-top: 30px;
        color: var(--text-color);
    }

    .signup-container {
        display: flex;
        margin: 40px 250px 40px 250px ;
        border-radius: 10px;
        background: var(--card-bg);
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .signup-form-container {
        flex: 1;
    }

    .signup-form-container form {
        display: flex;
        flex-direction: column;
    }

    .signup-form-container label {
        margin-bottom: 5px;
        font-weight: bold;
        color: var(--text-color);
    }

    .signup-form-container input,
    .signup-form-container select,
    .signup-form-container textarea {
        margin-bottom: 15px;
        padding: 10px;
        border: 1px solid var(--border-color);
        border-radius: 5px;
        width: 100%;
        font-size: 16px;
        background-color: #fff;
        color: #0d1b2a;
    }

    .signup-form-container textarea {
        resize: vertical;
        min-height: 100px;
    }

    .custum-file-upload {
        height: 150px;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px dashed #E0E1DD;
        background-color: white;
        padding: 1.5rem;
        border-radius: 10px;
        cursor: pointer;
        text-align: center;
        margin-bottom: 15px;
    }

    .custum-file-upload .icon svg {
        width: 40px;
        height: 40px;
        fill: #0D1B2A;
    }

    .custum-file-upload .text span {
        font-size: 14px;
        color: #0D1B2A;
        font-weight: 500;
        margin-top: 5px;
    }

    .current-image {
        margin: 10px 0;
        padding: 10px;
        border: 1px dashed #ccc;
        border-radius: 4px;
        background-color: #f4f4f4;
        text-align: center;
    }

    .current-image p {
        margin: 5px 0;
        font-weight: bold;
        color:var(--text-color);
    }

    .current-image img {
        display: block;
        margin: 10px auto;
        max-width: 150px;
        max-height: 150px;
        border: 1px solid #eee;
        border-radius: 4px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    .button-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-top: 20px;
    }

    .button-add,
    .back-button {
        flex: 1;
        padding: 20px;
        background-color: #415A77;
        color: #E0E1DD;
        text-align: center;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
        text-decoration: none;
        transition: background-color 0.3s, color 0.3s;
    }

    .button-add:hover,
    .back-button:hover {
        background-color: #778DA9;
        color: #0D1B2A;
        box-shadow: 0 0 9px rgba(0, 0, 0, 0.1);
    }

    .message,
    .error,
    .error-message {
        margin: 0 250px 250px;
        text-align: center;
        padding: 12px;
        margin-bottom: 20px;
        background-color: #dda8a8;
        color: #0D1B2A;
        border: 1px solid #dda8a8;
        border-radius: 4px;
    }

    .message {
        margin: 0 250px 250px;
        max-width: 1000px;
        text-align: center;
        padding: 12px;
        margin-bottom: 20px;
        background-color: #b8dda8;
        color: #0D1B2A;
        border: 1px solid #b8dda8;
        border-radius: 4px;
    }

    .error,
    .error-message {
        margin: 0 250px 250px;
        text-align: center;
        padding: 12px;
        margin-bottom: 20px;
        background-color: #dda8a8;
        color: #0D1B2A;
        border: 1px solid #dda8a8;
        border-radius: 4px;
    }

    @media (max-width: 768px) {
        .signup-container {
            margin: 20px;
            padding: 15px;
        }

        .message,
        .error {
            margin: 20px;
        }
    }
</style>
