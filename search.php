<?php
include 'navbar.php';
require_once("PHPHost.php");

// Check if there is a search query in the URL
if (isset($_GET["query"])) {
    // Call the updated searchProducts function to get search results
    $items = searchProducts($db, $_GET["query"]);
} else {
    // If no search query is provided, redirect to the fitness category page
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
// Check if no products were found
if (empty($items)) {
    // Display a message when no results are found
    echo "<p>No results found for '" . htmlspecialchars($_GET["query"]) . "'. Please try again with a different search term.</p>";
} else {
    // If there are results, display them
    echo "<div class='products'>";
    foreach ($items as $item) {
        ?>
        <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>">
            <div class="search-card">
                <img src="<?php echo $item["image"]; ?>" alt="product">
               
                <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>" class="product-name"><?php echo $item["item_name"]; ?></a>
                <br><br>
                <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>" class="product-price">£<?php echo number_format($item["item_price"], 2); ?></a>
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
