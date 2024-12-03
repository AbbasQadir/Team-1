<?php
include 'navbar.php';
require_once "PHPHost.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="productstyle.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mind & Motion</title>
    
</head>
<body>
  

    <?php 

    $category = "Supplements";

		if(!isset($_GET["category"])){
        	echo "<script>window.location.href='supplementspage.php?category=supplements';</script>";
    	}



        $result = $db->prepare("SELECT * FROM products WHERE item_category=:category ");
        $result->bindParam(":category", $category);
        $result->execute();
        $items = $result->fetchAll(PDO::FETCH_ASSOC);

    
    ?>

   <h1 id="title"> <?php echo $category ?> </h1>
   <label for="filter" class="filter">Filter results:</label>
   <select name="filters" id="filters"> 
    <option value="Popularity">Popularity</option>
    <option value="ascending">Price:low to high</option>
    <option value="descending">Price:high to low</option>
   </select>
  
   <div class="products">

   <?php foreach($items as $item  ) { ?>

    <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>">
        <div class="item">
            <img src=<?php echo $item["image"]; ?> alt="product">
            <div class="product-name"><?php echo $item["item_name"]; ?></div>
            <div class="product-price">£<?php echo $item["item_price"]; ?> </div>
        </div>
    </a>
    
    <?php } ?> 
  
 

</div>
    

  
</body>
</html>