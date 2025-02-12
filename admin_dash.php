<?php 

include 'sidebar.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE){
	session_start();
}

 try {
    require_once(__DIR__ . '/PHPHost.php'); 
} catch (Exception $ex) {
    echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
    exit;
}

if (!isset($_SESSION['admin'])){
    header('Location: admin_log.php');
    exit;
}
try {
	$query = "SELECT product_category_id, category_name FROM product_category;";
	$result = $db->query($query);
	$categories = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
	die("database query failed:".htmlspecialchars($ex->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_discription'];
    $product_price = $_POST['product_price'];
    $product_quantity = $_POST['product_quantity'];
    $product_category_id = $_POST['product_category_id'];
    $product_image = '';

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image_name = $_FILES['product_image']['name'];
        $image_tmp = $_FILES['product_image']['tmp_name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'png', 'jpeg'];

        if (in_array($image_ext, $allowed_ext)){
            $upload_dir = __DIR__ . "/images/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            /* $upload_path = $upload_dir . $image_name;
            $file_counter = 1;

            while (file_exists($upload_path)){
                $file_name_without_ext = pathinfo($image_name, PATHINFO_FILENAME);
                $upload_path = $upload_dir . $file_name_without_ext . "_$filecounter." . $image_ext;
                $file_counter++;

            }
            if (move_uploaded_file($image_tmp, $upload_path)){
                $product_image = "images/" . basename($upload_path);
            }*/
            $image_name_new = uniqid('img_', true) . '.' .$image_ext;
            $upload_path = $upload_dir . $image_name_new;

            if (move_uploaded_file($image_tmp, $upload_path)) {
                $product_image = "images/" . $image_name_new;
            } else {
                $error = "failed to move the uploaded file.";
            }
        } else {
            $error= "Invalid file format, only accepts PNG, JPG and JPEG.";
        }
    } else {
        $error = "you did not upload an image";
    }

	try {
    	$query = "INSERT INTO product (product_name, product_discription, product_image, product_category_id) VALUES (?, ?, ?, ?)";
    	$stmt = $db->prepare($query);
    	$stmt->execute([ $product_name, $product_description, $product_image, $product_category_id]);
    	$product_id = $db->lastInsertId();
    	$query = "INSERT INTO product_item (product_id, price, quantity) VAlUES (?, ?, ?)";
    	$stmt = $db->prepare($query);
    	$stmt->execute([$product_id, $product_quantity, $product_price]);
    	$message="The product was added to the database successfully";
	} catch (PDOException $ex) {
    	$error="there was a problem when adding the product to the database".htmlspecialchars($ex->getMessage());
	}
}


?>

<!DOCTYPE html>
<html>
    <head>
        <title>Product Management</title>
        <link rel="stylesheet" href="styles1.css">
		
    </head>
    <body>
        <h2>Product Management</h2>
        <form method="POST" enctype="multipart/form-data">
        <?php
            if (isset($message)) echo "<p style='color:green;'>$message</p>";
            if (isset($error)) echo "<p style='color:red;'>$error</p>";
        ?>
        <h3>Add a New Product</h3>
            <label for="product_name">Name of the product:</label>
            <input type="text" name="product_name" id="product_name" placeholder="Name" required>
            <br><br>
            <label for="product_discription">Description of the product:</label>
            <input type="text" name="product_discription" id="product_discription" placeholder="Description" required>
            <br><br>
            <label for="product_image">Image of the product:</label>
            <input type="file" name="product_image" id="product_image" accept="image/*" required>
            <br><br>
            <label for="product_price">Price of the product:</label>
            <input type="number" name="product_price" id="product_price" placeholder="Price"required>
            <br><br>
            <label for="product_quantity">Quantity of the product:</label>
            <input type="number" name="product_quantity" id="product_quantity" placeholder="Quantity" required>
            <br><br>
            <label for="product_category_id">Category of the product:</label>
            <select name="product_category_id" id="product_category_id" required>
            	<option value="" disabled selected>Select a Category</option>
                <?php
                if (!empty($categories)){
                    foreach($categories as $row){
                        echo "<option value='" . htmlspecialchars($row['product_category_id']) . "'>" . htmlspecialchars($row['category_name'])."</option>";
                    }
                } else {
                    echo "<option value=''> No categories available</option>";
                }
                ?>
            </select> 
            <br><br>
            <button type="submit"> Add the Product </button>
               <br><br> 
            <button type="button" onclick="window.location.href='index.php';">Go to Index</button>
            
        </form>

    </body>
</html>

