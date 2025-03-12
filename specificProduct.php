<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<script src = "homescript.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="homestyle.css">
<link rel="stylesheet" href="main.css">
    <title>Product Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<?php 
//session_start();
    
    
    
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



     session_start();
    


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


            if(isProductInStock($db, htmlspecialchars($productID) )){
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
            }else{
                echo "item not in stock";
            }
            
            
            
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
                            
                            <td>
                                <div onclick="selectColour('red')" id="colour-shape-red" class="colour-shape" style="background-color: red;"></div>
                            </td>
                        <?php } ?>

                        <?php if(isset($AvailableConfigs["blue"])){ ?>
                            <td>
                                <div onclick="selectColour('blue')" class="colour-shape" id="colour-shape-blue" style="background-color: blue;"></div>
                            </td>
                        <?php } ?>
                        
                        <?php if(isset($AvailableConfigs["green"])){ ?>
                            <td>
                                <div onclick="selectColour('green')" class="colour-shape" id="colour-shape-green" style="background-color: green;"></div>
                            </td>
                        <?php } ?>

                        <?php if(isset($AvailableConfigs["purple"])){ ?>
                            <td>
                                <div onclick="selectColour('purple')" class="colour-shape" id="colour-shape-purple" style="background-color: purple;"></div>
                            </td>
                        <?php } ?>

                        <?php if(isset($AvailableConfigs["yellow"])){ ?>
                            <td>
                                <div onclick="selectColour('yellow')"  class="colour-shape" id="colour-shape-yellow" style="background-color: yellow;"></div>
                            </td>
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
                        <td class="size-option">
                            <div onclick="sizeSelect('XS')"  id="size-option-XS">XS</div>
                        </td>
                    <?php } ?>

                    <?php if(isset($AvailableConfigs["small"])){ ?>
                        <td onclick="sizeSelect('S')" class="size-option" id="size-option-S">S</td>
                    <?php } ?>

                    <?php if(isset($AvailableConfigs["medium"])){ ?>
                        <td onclick="sizeSelect('M')" class="size-option" id="size-option-M">M</td>
                    <?php } ?>

                    <?php if(isset($AvailableConfigs["large"])){ ?>
                        <td onclick="sizeSelect('L')" class="size-option" id="size-option-L">L</td>
                    <?php } ?>

                    <?php if(isset($AvailableConfigs["extraLarge"])){ ?>
                        <td onclick="sizeSelect('XL')" class="size-option" id="size-option-XL">XL</td>
                    <?php } ?>

                    </tr>
                    
                </table>
            </div>
            <?php } ?>    	

            
            <form method="POST" >
                <label for="quantity" style="display:none;">Quantity:</label>
                <input type="number" name="quantity" id="quantity" min="1" value="1" style="display:none;">

                <?php if(isProductInStock($db, htmlspecialchars($productID) )){ ?>
                    
                    <?php if(isset($_SESSION["uid"])){ ?>
                        <button type="submit" id="addToBasket">Add to Basket</button>
                    <?php }else{ ?>
                        <button type="button" id="addToBasket">Add to Basket</button>
                    <?php } ?>

                <?php }else{ ?>
                    <button type="button" id="addToBasketOutOfStock">Out of Stock</button>
                <?php } ?>
                
                
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
    
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="color: black;">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login Required</h5>
                    <button type="button" class="btn-close" id="closeBtn" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>You need to log in to add items to your basket.</p>
                </div>
                <div class="modal-footer">
                    <a href="login.php" class="btn btn-primary">Log In</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

       


            <?php include 'footer.php'; ?>

            <script>



document.getElementById("addToBasket").onclick = () => {
    var loginModal = new bootstrap.Modal(document.getElementById("loginModal"), { backdrop: "static" });
            loginModal.show();

            document.getElementById("cancelBtn").addEventListener("click", function () {
                //window.history.back(); 
            });
        
            document.getElementById("closeBtn").addEventListener("click", function () {
                //window.history.back();
            });
}

      

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


    function sizeSelect(size){

        var XS = document.getElementById("size-option-XS")
        var S = document.getElementById("size-option-S")
        var M = document.getElementById("size-option-M")
        var L = document.getElementById("size-option-L")
        var XL = document.getElementById("size-option-XL")

        if(XS =! null) {XS.style.backgroundColor = "var(--card-bg)" }
        if(S =! null) { S.style.backgroundColor = "var(--card-bg)" }
        if(M =! null) { M.style.backgroundColor = "var(--card-bg)" }
        if(L =! null) { L.style.backgroundColor = "var(--card-bg)" }
        if(XL =! null) { XL.style.backgroundColor = "var(--card-bg)" }


        switch(size){
            case "XS":
                XS.style.backgroundColor = "var(--accent-color)"
                break;
            case "S":
                S.style.backgroundColor = "var(--accent-color)"
                break;
            case "M":
                M.style.backgroundColor = "var(--accent-color)"
                break;
            case "L":
                L.style.backgroundColor = "var(--accent-color)"
                break;
            case "XL":
               XL.style.backgroundColor = "var(--accent-color)"
                break;
        }

        //alert(size)
    }

    function selectColour(colour){

        var redCircle = document.getElementById("colour-shape-red")
        var purpleCircle = document.getElementById("colour-shape-purple")
        var greenCircle = document.getElementById("colour-shape-green")
        var blueCircle = document.getElementById("colour-shape-blue")
        var yellowCircle = document.getElementById("colour-shape-yellow")

        if(redCircle != null){ redCircle.style.borderWidth = "0px" }
        if(purpleCircle != null){ purpleCircle.style.borderWidth = "0px" }
        if(greenCircle != null){ greenCircle.style.borderWidth = "0px" }
        if(blueCircle != null){ blueCircle.style.borderWidth = "0px" }
        if(yellowCircle != null){ yellowCircle.style.borderWidth = "0px" }
        
        switch(colour){
            case "red":
                redCircle.style.borderWidth = "5px"
                break;
            case "purple":
                purpleCircle.style.borderWidth = "5px"
                break;
            case "green":
                greenCircle.style.borderWidth = "5px"
                break;
            case "blue":
                blueCircle.style.borderWidth = "5px"
                break;
            case "yellow":
                yellowCircle.style.borderWidth = "5px"
                break;
        }
    }

</script>



    <style>
#imageCarousel {
    display: block;
    align-items: center;
    justify-content: center;
    position: relative;
    max-width: 500px;
    margin: auto;
    padding-top:50px;
    padding-bottom:50px;
}

.product-image {
    display: none;
    width: 100%;
    height: 400px;
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

}


/* Review Container */
#reviewPreviewContainer {
    background-color: var(--card-bg);
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
@media (min-width: 1000px) {
    .reviewPreview {
        width: 95%;
    }

    form{        
        width:250px;
        height:50px;
    }


    #mainInfoDetails{
        padding-left: 20px;
    }

    #addToBasket{
        padding: 10px;
        margin-top: 20px;
        width: 200px;

    }

    #reviewLink{

        width: 200px;
    }

    

    .similarProductItem{
        width: 30%;
        margin-left: 3%;
        height: 650px;
    }

    #imageCarousel {
        margin: auto;
        padding-top:50px;
        padding-bottom:50px;
    }

    #mainTitle {
        margin-top: 50px;
    }

    .product-image{
        min-height: 500px;
        margin-left: 20px;

    }
    
}



@media (max-width: 1000px) {

    .similarProductItem{
        width: 30%;
        margin-left: 2%;
    }

    #colour-options{
        text-align: center;
        margin: auto;

    }

    #colour-options > table{
        margin-left: auto;
        margin-right: auto;
    }

    #size-options{
        height: auto;
        text-align: center;
        
    }

    

    #size-option-table{
        text-align: center;
        margin-left: auto;
        margin-right: auto;
    }

    form{
        width: 100%;
    }

    #addToBasket {
        width: 80%;
        margin-left: 10%;

        height: 45px;

    }

    #mainInfoContainer{
        grid-template-rows: auto ;
        gap: 10px;
        padding-bottom: 100px;
    }

    

    .similarProductItem{
        width: 90%;
        margin-left: 5%;
        margin-bottom: 25px;

    }

    #mainInfoDetails{
        grid-column-start: 1;
        grid-column-end: 1;
    }

    .product-image{
        display: none;
        padding: 0;

    }

    #mainTitle{
        width: 100%;
        
        text-align: center;

        padding: 20px;
        font-size: 6vw;

        margin-top: 25px;

        height: auto;


    }

    #stockAvailable{
        width: 100%;
        margin: auto;
        text-align: center;
  
    }

    #imageCarousel {
        padding-bottom: 0px;
    }


    #mainPrice{
        width: 100%;
        margin: auto;
        text-align: center;
    }


    

    #reviewLink{

        width: 80%;
        margin-left: 10%;
        
    }

    #mainInfoContainer{
        background-color: rgb(70, 30, 30);
        
        grid-template-columns: 100%;
        grid-template-rows: 50% auto  ;
        display: grid;

    }

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

        
        #mainInfoDetails{

            grid-column-start: 2;
            grid-column-start: 3;
        }

        #mainInfoDetails > * {
            margin-top: 20px;
            grid-column-start: 3;
            grid-column-end: 4;
        }

        .colour-shape{
            width: 25px;
            height: 25px;
            border-radius: 12.5px;
            margin-right: 10px;

            border-width: 0;
            border-style: solid;

            cursor: pointer;
        }
       

        .size-option{
            background-color: rgb(218, 218, 218);
            border-style: solid;
            border-width: 1px;
            border-color: azure;
            padding: 5px;
            
        }

        #altImageContainer{
            grid-column-start: 1;
            grid-column-end: 2;
          


            
            margin: auto;
            justify-content: center;
            
        }

        #altImageContainer > img {
            width: 80%;
            margin-left: 10%;

        }

        #similarProductsContainer > a:visited {
            color: inherit;
        }

        a:hover {
            color: inherit;
        }


        .similarProductItem:hover {
            background-color: gray;
        }

        #detailedInfoContainer {
            padding: 50px 25px 50px 25px;
        }

        #mainInfoContainer {
            background-color: var(--card-bg);
            height: auto;
            display: grid;

            

            

        }

        #mainImage {
            display: inline;
			grid-column-start: 2;
            grid-column-end: 3;
            grid-row-start: 1;
            grid-row-end: 7;
            height:80%;

            object-fit: contain;

            border-radius: 15px;
            justify-content: center;
            margin: auto;
            margin-top: 10%;
            max-height: 600px;

            width: 80%;
        }

        

		#stockAvailable{
			
            font-size: 25px;
            display: block;
           
 
        }


        #mainPrice {

            height: 50px;

			font-size: 25px;
            display: block;
            
        }


        #colour-options{

            

            height: 50px;
 
            margin-bottom:10px;
        }

        

        #size-options{
            

            height:80px;


        }

        .size-option{
            background-color: var(--card-bg);
            border-style: solid;
            border-width: 1px;
            border-color: azure;
            padding: 5px;
            cursor: pointer;


        }

     
        #addToBasket {
            
            background-color: #084298;
            
            

			cursor: pointer;

            color: white;
            border: none;
            border-radius: 15px;
            

            

        }
        

        #addToBasketOutOfStock{

            background-color: lightgray;
            
            margin-top: 20px;

			cursor:not-allowed;

            color: black;
            border: none;
            border-radius: 15px;
            padding: 10px;

        }

    #reviewContainer {
        display:block;
        text-align: center;

        margin-top: 20px;

        
    }

    #reviewLink {

        display: block;
        text-decoration: none;
        font-size: 18px;
        color: #084298;
        font-weight: bold;
        padding: 10px;
        border-radius: 8px;
        background-color: #e6f0ff;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    #reviewLink:hover {
        background-color: #084298;
        color: white;
    }

        #similarProductsContainer {
            width: 100%;
            display: block;
            
            padding-top: 25px;
            padding-bottom: 25px;
        }

        #detailedInfoContainer > p {
            margin-bottom: 25px;
        }

        .similarProductItem {
            background-color: var(--card-bg);
 
       		
            
            padding-bottom: 25px;
            display: inline-block;
            border-radius: 25px;
        	
        }

        .similarProductImg {
            border-radius: 5px;
            margin-top: 20px;
            margin-left: 5%;
            width: 90%;
        	height:500px;
        	object-fit: contain;
        }


        .similarProductPrice {
            margin-top: 5px;
            margin-left: 25px;
            margin-bottom: 0;
            font-size: large;
        }

        .sectionHeader {
            padding-top: 25px;
            margin-bottom: 50px;
            text-align: center;
            display: block;
            width: 100%;

            font-weight: bold;
        }

        .similarProductTitle {
            margin-top: 25px;
            margin-left: 25px;
            margin-bottom: 0;
            font-weight: bold;
        }
    </style>

</body>
</html>
