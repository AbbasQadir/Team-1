<?php
include 'navbar.php';
require_once("PHPHost.php");
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
<?php

    if(isset($_GET["query"])){
        $items = searchProducts($db, $_GET["query"]);
    }else{
       echo "<script>window.location.href='productpage2.php?category=fitness';</script>";

    }

?>

<h1 id="fitnesstitle"> <?php echo "search results for ".htmlspecialchars($_GET["query"]) ?> </h1>

<!--<form action="/search.php">
<p>Search</p>
<input name="query" type="text" value=<?php echo htmlspecialchars($_GET["query"]); ?> >
<button>Search</button>
</form>-->

<div class="products">

<?php foreach($items as $item  ) {   ?>
<a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>">
    <div class="search-card">
       <img  src=<?php echo $item["image"]; ?> alt="product">
       <span class="favorite-icon">♡</span>
       <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>" class="product-name"><?php echo $item["item_name"]; ?></a>
       <br><br>
       <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>" class="product-price">£<?php echo number_format($item["item_price"],2); ?></a>
    </div>
</a>

<?php } ?>

</div>

</body>
</html>
<?php include 'footer.php'; ?>