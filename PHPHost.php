<?php


$db_host = 'localhost';  
$db_name = 'cs2team1_db';
$username = 'cs2team1';
$password = 'SqDC8zgJHEVQBIo';

try {
    // Establish a database connection
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $username, $password);

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable detailed errors
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Use native prepared statements
} catch (PDOException $ex) {
    error_log("Database connection error: " . $ex->getMessage());
    die("An error occurred while connecting to the database. Please try again later.");
}

function getNameFromVariationOptionID($db, $id){
    $result = getDBResult($db, "SELECT * FROM variation_option WHERE variation_option_id=:value", ":value", $id);

    if(isset($result[0]["variation_value"])){
        return $result[0]["variation_value"];
    }else{
        return "";
    }

}

function isProductInStock($db, $productID){

    $productQuantity = getDBResult($db, "SELECT quantity FROM product_item WHERE product_id=:productID", ":productID", $productID);
    
    if($productQuantity[0]["quantity"] > 0){
        return true;
    }else{
        return false;
    }

}

function getDBResult($db, $query, $tag, $tagValue) {
    $result = $db->prepare($query);
    $result->bindParam($tag, $tagValue);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function getProductPrice($db, $productId) {
    $rawPrice = getDBResult($db, "SELECT price FROM product_item WHERE product_item_id=:itemId", ":itemId", $productId);
    return $rawPrice[0]["price"] ?? null;
}

function getProductQuantity($db, $productID) {
    $result = getDBResult($db, "SELECT quantity FROM product_item WHERE product_item_id = :itemId", ":itemId", $productID);
    
   
    return ($result && isset($result[0]['quantity'])) ? $result[0]['quantity'] : 0;
}

function getCatagoryFromId($db, $catagoryId) {
    $categoryObject = getDBResult($db, "SELECT * FROM product_category WHERE product_category_id=:categoryId", ":categoryId", $catagoryId);
    return $categoryObject[0]["category_name"] ?? null;
}

function fetchProducts($db, $requestedCategory) {
    $categories = [
        "fitness" => "Fitness Equipment",
        "books" => "Books",
        "gymWear" => "Gym Wear",
        "technology" => "Technology",
        "supplements" => "Supplements"
    ];

    if (!isset($categories[$requestedCategory])) {
        return [];
    }

    $category = $categories[$requestedCategory];

    $result = $db->prepare("SELECT * FROM product WHERE item_category=:category");
    $result->bindParam(":category", $category);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function searchProducts($db, $query) {
    if ($query == "") {
        echo "<script>window.location.href='fitnesspage.php';</script>";
        exit;
    }

    $sql = "SELECT * FROM product INNER JOIN product_category ON product.product_category_id=product_category.product_category_id WHERE product_name LIKE ? OR product_discription LIKE ? OR product_category.category_name  LIKE ? " ;
    $result = $db->prepare($sql);
    $searchTerm = "%$query%";
    $result->execute([$searchTerm, $searchTerm, $searchTerm]);

    return $result->fetchAll(PDO::FETCH_ASSOC);
}

?>
