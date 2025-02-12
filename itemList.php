<?php 

            //set catagory in the main file 

            $catagoryObject = getDBResult($db, "SELECT * FROM product_category WHERE category_name=:category", ":category", $category);
            $catagoryID = $catagoryObject[0]["product_category_id"];

            $items = getDBResult($db, "SELECT * FROM product WHERE product_category_id=:categoryid", ":categoryid", $catagoryID);

            //get the price of each item and then add the price to array that has the rest of the item data
            for($i = 0; $i < count($items); $i++){
              $items[$i]["product_price"] = getProductPrice($db, $items[$i]["product_id"]); 
            }


            $filterName = "popularity";
            //sort based on filter 
            if(isset($_GET["filter"]) && $_GET["filter"] == "asc"){
                array_multisort(array_column($items, 'product_price'), SORT_ASC, $items);
                $filterName = "low to high";
            }else if(isset($_GET["filter"]) && $_GET["filter"] == "desc"){
                array_multisort(array_column($items, 'product_price'), SORT_DESC, $items);
                $filterName = "high to low";
            }
            
    


?>

        <h1 id="title"> <?php echo $category ?> </h1>
        <div class="dropdown filter-dropdown">
            <div class="dropdown-button">Sort By: <?php echo $filterName ?> </div>
            <div class="drop-content">
                    <a href="?filter=popularity">Popularity</a>
                    <a href="?filter=asc">Price: low to high</a>
                    <a href="?filter=desc">Price: high to low</a>
            </div>
        </div>
      
        <div class="products">

        <?php foreach($items as $item  ) { ?>

            <?php   
            
            if(!file_exists($item["product_image"])){
                $item["product_image"] = "images/missingImage.png";
            }
            

            ?>

            <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>"> 
                <div class="item">
                    <img src=<?php echo $item["product_image"]; ?>  alt="product">
                    
                    <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>" class="product-name"><?php echo $item["product_name"]; ?></a>
                    <br><br>
                    <a href="specificProduct.php?id=<?php echo $item["product_id"]; ?>" class="product-price">£<?php echo getProductPrice($db, $item["product_id"]); ?></a>
                </div>
            </a>
    
        <?php } ?> 

        </div>