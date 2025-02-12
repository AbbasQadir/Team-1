<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE){
    session_start();
}

include 'sidebar.php';

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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    try {
        // First, delete related records in dependent tables
        $query = "DELETE FROM product_item WHERE product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);

        $query = "DELETE FROM cart_item WHERE product_item_id IN (SELECT product_item_id FROM product_item WHERE product_id = ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);

        $query = "DELETE FROM order_prod WHERE product_item_id IN (SELECT product_item_id FROM product_item WHERE product_id = ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);

        // Finally, delete from the product table
        $query = "DELETE FROM product WHERE product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);

        $message = "The product has been successfully deleted.";
    } catch (PDOException $ex) {
        $error = "Error deleting product: " . htmlspecialchars($ex->getMessage());
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Delete Product</title>
        <link rel="stylesheet" href="styles1.css">
    </head>
    <body>
        <h2>Delete a Product</h2>
        <form method="POST">
            <label for="product_id">Enter Product ID to Delete:</label>
            <input type="number" name="product_id" id="product_id" required>
            <br><br>
            <button type="submit">Delete Product</button>
            <br><br>
            <button type="button" onclick="window.location.href='index.php';">Go to Index</button>
            <?php
            if (isset($message)) echo "<p style='color:green;'>$message</p>";
            if (isset($error)) echo "<p style='color:red;'>$error</p>";
            ?>
        </form>
    </body>
</html>
