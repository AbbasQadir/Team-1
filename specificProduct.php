<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<script src = "homescript.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="homestyle.css">
<link rel="stylesheet" href="main.css">
    <title>Product Page</title>
</head>
<body>
<?php 
session_start();
    
    
    
    require_once("PHPHost.php");
    
    $productID = $_GET["id"];

    // Fetch product details
    $item = getDBResult($db, "SELECT * FROM product WHERE product_id=:productID", ":productID", $productID)[0];

    $productConfigs =  getDBResult($db, "SELECT * FROM product_configuration INNER JOIN variation_option ON product_configuration.variation_option_id = variation_option.variation_option_id WHERE product_item_id=:itemID", ":itemID", $productID);
    
    $AvailableConfigs;

    $sizeAvailable = false;
    $colourAvaliable = false;
	$hasAltImages = false;


    foreach($productConfigs as $config){
        
        if(isset($config["variation_value"])){
            //var_dump($config["variation_value"]);
            
            $AvailableConfigs[$config["variation_value"]] = true;
            
           
            $availableSizes = array("extraSmall", "small", "medium", "large", "extraLarge" );
            if(in_array($config["variation_value"], $availableSizes)){
                $sizeAvailable = true;
            }

            $availableColours = array("red", "green", "purple", "yellow");
            if(in_array($config["variation_value"], $availableColours)) {
                $colourAvaliable = true;
            }
        }

    }

    $reviews = getDBResult($db, "SELECT * FROM users_review WHERE order_prod_id=:productID", ":productID", $productID);

    //$variationOptions =  getDBResult($db, "SELECT * FROM variation WHERE product_category_id=:categoryID", ":categoryID", $item["product_category_id"]);

    
    

    //foreach($variationOptions as $option){
        
        //if($option["variation_name"] == "size"){
        //    $sizeAvailable = true;
        //}
        
        //if($option["variation_name"] == "colour"){
        //    $colourAvaliable = true;
        //}

    //}



     
    //var_dump($variations);


  if(!file_exists($item["product_image"])){
  	$item["product_image"] = "images/missingImage.png";
  }

    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_SESSION['uid'])) { 
            $userId = $_SESSION['uid']; 
            $quantity = 1;  

            // Check ifmproduct is already in basket or no
            $checkBasket = $db->prepare("SELECT quantity FROM asad_basket WHERE user_id = :user_id AND product_id = :product_id");
            $checkBasket->execute([':user_id' => $userId, ':product_id' => $productID]);
            $existingBasket = $checkBasket->fetch(PDO::FETCH_ASSOC);

            if ($existingBasket) {
                // fix quantity if the product is already tehre
                $newQuantity = $existingBasket['quantity'] + $quantity;
                $updateBasket = $db->prepare("UPDATE asad_basket SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
                $updateBasket->execute([':quantity' => $newQuantity, ':user_id' => $userId, ':product_id' => $productID]);
            } else {
                // put new product into  basket
                $addToBasket = $db->prepare("INSERT INTO asad_basket (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $addToBasket->execute([':user_id' => $userId, ':product_id' => $productID, ':quantity' => $quantity]);
            }

            header("Location: Basket.php");
            exit;
        } else {
            echo "<p>Please log in to add items to your basket.</p>";
        }
    }

require_once("navbar.php");

    

    ?>

    <div id="mainInfoContainer">
    <div id="imageCarousel">
        <button id="prevBtn">❮</button>

        <div id="imageContainer">
            <?php 
            $imagePaths = array_filter([
                $item["product_image"], 
                $item["product_image_2"] ?? null, 
                $item["product_image_3"] ?? null
            ]);


            foreach ($imagePaths as $index => $img) { ?>
                <img src="<?php echo htmlspecialchars($img); ?>" class="product-image <?php echo $index === 0 ? 'active' : ''; ?>">
            <?php } ?>
        </div>

        <button id="nextBtn">❯</button>
    </div>


        

        <div id="mainInfoDetails"> 
                <h1 id="mainTitle"><?php echo htmlspecialchars($item["product_name"]); ?></h1>
                <h5 id="stockAvailable">
                    Stock Available: 
                    <?php 
                $stock = getProductQuantity($db, $productID);
                echo ($stock > 0) ? htmlspecialchars($stock) : "Out of Stock";
                ?>
            </h5>
            <h5 id="mainPrice">£<?php echo htmlspecialchars(getProductPrice($db, $productID)); ?></h5>

            <?php if($colourAvaliable == true) { ?>
            <div id="colour-options" >
                <p>Colour:</p>
                <table> 
                    <tr>
                        <?php if(isset($AvailableConfigs["red"])){ ?>
                            <td><div class="colour-shape" style="background-color: red;"></div></td>
                        <?php } ?>

                        <?php if(isset($AvailableConfigs["blue"])){ ?>
                            <td><div class="colour-shape" style="background-color: blue;"></div></td>
                        <?php } ?>
                        
                        <?php if(isset($AvailableConfigs["green"])){ ?>
                            <td><div class="colour-shape" style="background-color: green;"></div></td>
                        <?php } ?>

                        <?php if(isset($AvailableConfigs["purple"])){ ?>
                            <td><div class="colour-shape" style="background-color: purple;"></div></td>
                        <?php } ?>

                        <?php if(isset($AvailableConfigs["yellow"])){ ?>
                            <td><div class="colour-shape" style="background-color: yellow;"></div></td>
                        <?php } ?>
                        
                    </tr>
                </table>
         
            </div>

            <?php } ?>


            <?php if($sizeAvailable == true) { ?>
            <div id="size-options">
                <p>Size:</p>
                <table id="size-option-table">

                    <tr>
                    <?php if(isset($AvailableConfigs["extraSmall"])){ ?>
                        <td class="size-option">XS</td>
                    <?php } ?>

                    <?php if(isset($AvailableConfigs["small"])){ ?>
                        <td  class="size-option">S</td>
                    <?php } ?>

                    <?php if(isset($AvailableConfigs["medium"])){ ?>
                        <td class="size-option">M</td>
                    <?php } ?>

                    <?php if(isset($AvailableConfigs["large"])){ ?>
                        <td class="size-option">L</td>
                    <?php } ?>

                    <?php if(isset($AvailableConfigs["extraLarge"])){ ?>
                        <td class="size-option">XL</td>
                    <?php } ?>

                    </tr>
                    
                </table>
            </div>
            <?php } ?>    	

            
            <form method="POST" >
                <label for="quantity" style="display:none;">Quantity:</label>
                <input type="number" name="quantity" id="quantity" min="1" value="1" style="display:none;">
                <button type="submit" id="addToBasket">Add to Basket</button>
                <div id="reviewContainer" >

                    <?php if (isset($_SESSION['uid'])){ ?>
                        <!-- If user is logged in, show the "Write a Review" link -->
                        <a href="reviews.php?id=<?php echo htmlspecialchars($productID); ?>" id="reviewLink">
                           Write a Review
                        </a>

                        <?php } ?>

                </div>  
            </form>

                
        
                <?php

                
                //if($_SESSION['admin'] == "admin"){
                    //echo "logged in as admin";
                //}
                
                ?> 
        </div>
        
             
    </div>

    <div id="detailedInfoContainer">
        <h3 style="font-weight:bold;">Description</h3>
            <ul>
            <?php 
            $description = explode('-', $item["product_discription"]);
			foreach ($description as $description){
            echo "<li>" . htmlspecialchars(trim($description)) . "</li>";
            }

      
            ?>
            </ul>
            <br>
         <h3 style="font-weight:bold;">Category</h3> 
         <p><?php echo htmlspecialchars(getCatagoryFromId($db, $item["product_category_id"])); ?></p>
    </div>

           <?php 
            
            $simmilarResults = searchProducts($db, getCatagoryFromId($db, $item["product_category_id"]));
			//var_dump($simmilarResults);
            
            ?> 
            


<div id="reviewPreviewContainer">
    <h1 class="sectionHeader">Customer Reviews</h1>

    <?php if (count($reviews) == 0) { ?>
        <p id="noReviewsText">No reviews yet. Be the first to leave one!</p>
    <?php } ?>

    <?php $presentedComments = 0; ?>

    <?php foreach ($reviews as $review) { ?>
        <?php if ($presentedComments < 3) { ?>
            <?php $presentedComments++; ?>

            <div class="reviewPreview">
                <div class="reviewHeader">
                    <p class="reviewUser">User #<?php echo htmlspecialchars($review["user_id"]); ?></p>
                    <p class="reviewRating"><?php echo str_repeat("★", $review["rating"]) . str_repeat("☆", 5 - $review["rating"]); ?></p>
                </div>
                <p class="reviewText">"<?php echo htmlspecialchars($review["comment"]); ?>"</p>
                <hr class="reviewSeparator">
            </div>
        <?php } ?>
    <?php } ?>
</div>





    </div>


    <div id="similarProductsContainer">
        <h1 class="sectionHeader">Similar Products</h1>

            
            
         <a href="/specificProduct.php?id=<?php echo $simmilarResults[0]['product_id'] ?>">
            <div class="similarProductItem">
                <img class="similarProductImg" src="<?php echo $simmilarResults[0]['product_image'] ?>">
                <p class="similarProductTitle"><?php echo $simmilarResults[0]["product_name"] ?></p>
                <p class="similarProductPrice">£<?php echo getProductPrice($db, $simmilarResults[0]['product_id']) ?></p>
            </div>
        </a>
        
        <a href="/specificProduct.php?id=<?php echo $simmilarResults[1]['product_id'] ?>">
            <div class="similarProductItem">
                <img class="similarProductImg" src="<?php echo $simmilarResults[1]['product_image'] ?>">
                <p class="similarProductTitle"><?php echo $simmilarResults[1]["product_name"] ?></p>
                <p class="similarProductPrice">£<?php echo getProductPrice($db, $simmilarResults[1]['product_id']) ?></p>
            </div>
        </a>

        <a href="/specificProduct.php?id=<?php echo $simmilarResults[2]['product_id'] ?>">
            <div class="similarProductItem">
                <img class="similarProductImg" src="<?php echo $simmilarResults[2]['product_image'] ?>">
                <p class="similarProductTitle"><?php echo $simmilarResults[2]["product_name"] ?></p>
                <p class="similarProductPrice">£<?php echo getProductPrice($db, $simmilarResults[2]['product_id']) ?></p>
            </div>
        </a>
    </div>
            

            <?php include 'footer.php'; ?>

            <script>
document.addEventListener("DOMContentLoaded", function() {
    let images = document.querySelectorAll(".product-image");
    let currentIndex = 0;
    let totalImages = images.length;

    document.getElementById("nextBtn").addEventListener("click", function() {
        images[currentIndex].classList.remove("active");
        currentIndex = (currentIndex + 1) % totalImages;
        images[currentIndex].classList.add("active");
    });

    document.getElementById("prevBtn").addEventListener("click", function() {
        images[currentIndex].classList.remove("active");
        currentIndex = (currentIndex - 1 + totalImages) % totalImages;
        images[currentIndex].classList.add("active");
    });
});
</script>



    <style>
#imageCarousel {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    max-width: 500px;
    margin: auto;
}

.product-image {
    display: none;
    width: 100%;
    height: auto;
    border-radius: 10px;
    transition: opacity 0.5s ease-in-out;
}

.product-image.active {
    display: block;
}

#prevBtn, #nextBtn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    font-size: 24px;
    padding: 10px;
    cursor: pointer;
    z-index: 100;
}

#prevBtn { left: 10px; }
#nextBtn { right: 10px; }

#imageContainer {
    position: relative;
    width: 100%;
    text-align: center;
}


/* Review Container */
#reviewPreviewContainer {
    background-color: #f4f4f4;
    border-radius: 10px;
    padding: 40px;
    width: 100%;
    max-width: 100%;
    margin: 30px auto;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
}

/* Section Header */
.sectionHeader {
    font-size: 28px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 20px;
}

/* No Reviews Text */
#noReviewsText {
    color: gray;
    text-align: center;
    font-size: 18px;
}

/* Individual Review Box */
.reviewPreview {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin: 20px auto;
    width: 90%;
    max-width: 1000px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
    position: relative;
}

/* Add hover effect */
.reviewPreview:hover {
    transform: scale(1.02);
}

/* Review Header (User ID + Star Rating) */
.reviewHeader {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 10px;
}

/* User ID */
.reviewUser {
    color: #333;
}

/* Star Rating */
.reviewRating {
    color: gold;
    font-size: 20px;
}

/* Review Text */
.reviewText {
    font-size: 18px;
    color: #555;
    font-style: italic;
    margin-bottom: 15px;
}

/* Separator Line */
.reviewSeparator {
    border: none;
    height: 1px;
    background: #ddd;
    margin-top: 15px;
    width: 100%;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .reviewPreview {
        width: 95%;
    }
}

@media (max-width: 768px) {
    .reviewPreview {
        width: 100%;
    }

    .reviewHeader {
        font-size: 18px;
    }

    .reviewText {
        font-size: 16px;
    }
}
