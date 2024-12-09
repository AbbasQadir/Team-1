<?php
include 'navbar.php';
require_once "PHPHost.php";
?>
<link rel="stylesheet" href="homestyle.css">

<!DOCTYPE html>
<html lang="en">
    <head>
        
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mind & Motion</title>
        <link rel="stylesheet" href="styless.css">
    </head>
    <body>
        <?php 
            $category = "Technology";

                if(!isset($_GET["category"])){
                    echo "<script>window.location.href='technologypage.php?category=techforwellness';</script>";
                }

                $result = $db->prepare("SELECT * FROM products WHERE item_category=:category ");
                $result->bindParam(":category", $category);
                $result->execute();
                $items = $result->fetchAll(PDO::FETCH_ASSOC);
        ?>

    <h1 id="title"> <?php echo $category ?> </h1>
    <div class="dropdown filter-dropdown">
        <div class="dropdown-button">Sort By:</div>
        <div class="drop-content">
                <a href="#Popularity">Popularity</a>
                <a href="#ascending">Price: low to high</a>
                <a href="#descending">Price: high to low</a>
        </div>
    </div>
    <!--<form  action="/search.php">
        <p>Search</p>
        <input name="query" type="text">
        <button>Search</button>
    </form>-->
 
    <div class="products">

    <?php foreach($items as $item  ) { ?>

        <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>">
            <div class="item">
    			<div class="tech">
					<img src=<?php echo $item["image"]; ?> alt="product">
				</div>
               
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