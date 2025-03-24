<?php
include 'navbar.php';
require_once("PHPHost.php");


if (isset($_GET["query"])) {
    
    $items = searchProducts($db, $_GET["query"]);
} else {
   
    echo "<script>window.location.href='productpage2.php?category=fitness';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styless.css">
    <link rel="stylesheet" href="homestyle.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mind & Motion</title>
</head>
<body>

<h1 id="fitnesstitle"> <?php echo "Search results for " . htmlspecialchars($_GET["query"]); ?> </h1>

<?php
if (empty($items)) {
    echo "<p>No results found for '" . htmlspecialchars($_GET["query"]) . "'. Please try again with a different search term.</p>";
} else {
     echo "<div class='products'>";
    foreach ($items as $item) {
        
        if(!file_exists($item["product_image"])){
            $item["product_image"] = "images/missingImage.png";
        }
        
        ?>
        <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>">
            <div class="search-card">
                <img src="<?php echo $item["product_image"]; ?>" alt="product">
               
                <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>" class="product-name"><?php echo $item["product_name"]; ?></a>
                <br><br>
                <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>" class="product-price">£<?php echo getProductPrice($db, $item["product_id"]); ?></a>
            <h5 id="stockAvailable">
                    <?php 
                        $stock = getProductQuantity($db, $item["product_id"]);

                        if ($stock > 10) {
                            echo '<span style="color: green;">In Stock</span>';
                        } elseif ($stock > 0) {
                            echo '<span style="color: orange;">Low Stock</span>';
                        } else {
                            echo '<span style="color: red;">Out of Stock</span>';
                        }
                    ?>
                </h5>
        
        </div>
        </a>
        <?php
    }
    echo "</div>";
}
?>

</body>
</html>

<?php include 'footer.php'; ?>
