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


try {
    $query = "SELECT product_category_id, category_name FROM product_category;";
    $result = $db->query($query);
    $categories = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    die("Database query failed: " . htmlspecialchars($ex->getMessage()));
}

try {
    $query = "SELECT product_category_id, category_name FROM product_category;";
    $result = $db->query($query);
    $categories = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    die("Database query failed: " . htmlspecialchars($ex->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_discription'];
    $product_price = $_POST['product_price'];
    $product_quantity = $_POST['product_quantity'];
    $product_category_id = $_POST['product_category_id'];

    $product_image = '';
    $product_image_2 = '';
    $product_image_3 = '';

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

    $product_image = handleImageUpload($_FILES['product_image'], $upload_dir, $allowed_ext);
    $product_image_2 = handleImageUpload($_FILES['product_image_2'], $upload_dir, $allowed_ext);
    $product_image_3 = handleImageUpload($_FILES['product_image_3'], $upload_dir, $allowed_ext);

    try {
        $query = "INSERT INTO product (product_name, product_discription, product_image, product_image_2, product_image_3, product_category_id) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $product_name,
            $product_description,
            $product_image,
            $product_image_2,
            $product_image_3,
            $product_category_id
        ]);

        $product_id = $db->lastInsertId();

        $query = "INSERT INTO product_item (product_id, price, quantity) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id, $product_price, $product_quantity]);

        $message = "The product was added to the database successfully";
    } catch (PDOException $ex) {
        $error = "There was a problem when adding the product to the database: " . htmlspecialchars($ex->getMessage());
    }
}

include 'sidebar.php';
?>
<html>
<title>Product Management</title>
<br>
<h1 class="title">Product Management</h1>
<?php if (!empty($message)): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="signup-container">
    <div class="signup-form-container">
        <form method="POST" enctype="multipart/form-data">

            <label for="product_name">Name of Product</label>
            <input type="text" name="product_name" required>

            <label for="product_discription">Description</label>
            <input type="text" name="product_discription" required>


            <label class="custum-file-upload" for="product_image">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z" />
                    </svg>
                </div>
                <div class="text">
                    <span id="upload-text-1">Click to upload main image</span>
                </div>
            </label>
            <input type="file" name="product_image" id="product_image" required
                onchange="displayFileNameCustom(this, 'upload-text-1')" style="display:none;">

            <br>
            <label class="custum-file-upload" for="product_image_2">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z" />
                    </svg>
                </div>
                <div class="text">
                    <span id="upload-text-2">Click to upload image 2</span>
                </div>
            </label>
            <input type="file" name="product_image_2" id="product_image_2"
                onchange="displayFileNameCustom(this, 'upload-text-2')" style="display:none;">

            <br>
            <label class="custum-file-upload" for="product_image_3">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z" />
                    </svg>
                </div>
                <div class="text">
                    <span id="upload-text-3">Click to upload image 3</span>
                </div>
            </label>
            <input type="file" name="product_image_3" id="product_image_3"
                onchange="displayFileNameCustom(this, 'upload-text-3')" style="display:none;">
            <br>
            <label for="product_price">Price</label>
            <input type="number" name="product_price" required>

            <label for="product_quantity">Quantity</label>
            <input type="number" name="product_quantity" required>

            <label for="product_category_id">Category</label>
            <select name="product_category_id" required>
                <option value="" disabled selected>Select Category</option>
                <?php foreach ($categories as $row) {
                    echo "<option value='{$row['product_category_id']}'>{$row['category_name']}</option>";
                } ?>
            </select>

            <input type="submit" value="Add Product" class="button-add">
        </form>
    </div>
</div>

<!-- JS for image name display -->
<script>
    function displayFileNameCustom(input, elementId) {
        const fileName = input.files.length > 0 ? input.files[0].name : "Click to upload image";
        document.getElementById(elementId).textContent = fileName;
    }
</script>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #E0E1DD;
        color: #0D1B2A;
        line-height: 1.6;
        scroll-behavior: smooth;
    }

    h1.title {
        text-align: center;
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #1B263B;
    }


    .signup-container {
        display: flex;
        max-width: 1000px;
        margin: 40px auto;
        border-radius: 10px;
        background: #f4f4f4;
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

    }

    .signup-form-container input,
    .signup-form-container select {
        margin-bottom: 15px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
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

    .button-add {
        width: 100%;
        padding: 20px;

        background-color: #415A77;
        color: #E0E1DD;
        text-align: center;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
    }

    .button-add:hover {
        background-color: #778DA9;
        color: #0D1B2A;
        box-shadow: 0px 0px 9px 0px rgba(0, 0, 0, 0.1);
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
</style>

</html>
