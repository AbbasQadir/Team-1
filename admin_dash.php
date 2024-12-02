<?php 

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
    $product_image = $_POST['product_image'];
    $product_price = $_POST['product_price'];
    $product_quantity = $_POST['product_quantity'];
    $product_category_id = $_POST['product_category_id'];

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
        <form method="POST">
        <h3>Add a New Product</h3>
            <label for="product_name">Name of the product:</label>
            <input type="text" name="product_name" id="product_name" placeholder="Name" required>
            <br><br>
            <label for="product_discription">Description of the product:</label>
            <input type="text" name="product_discription" id="product_discription" placeholder="Description" required>
            <br><br>
            <label for="product_image">Image of the product:</label>
            <input type="text" name="product_image" id="product_image" placeholder="Image" required>
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
            <?php
            if (isset($message)) echo "<p style='color:green;'>$message</p>";
            if (isset($error)) echo "<p style='color:red;'>$error</p>";
            ?>
        </form>

    </body>
</html>
