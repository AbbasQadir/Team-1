<?php
session_start();
require_once("PHPHost.php");

$productID = $_GET["id"];

if (isset($_SESSION['uid'])) {
    $userId = $_SESSION['uid'];
    $purchaseQuery = "SELECT COUNT(*) FROM orders o 
                      JOIN order_prod op ON o.orders_id = op.orders_id 
                      WHERE o.user_id = :user_id AND op.product_item_id = :productID";
    $stmt = $db->prepare($purchaseQuery);
    $stmt->execute([':user_id' => $userId, ':productID' => $productID]);
    $purchaseCount = $stmt->fetchColumn();

    $reviewQuery = "SELECT COUNT(*) FROM users_review 
                    WHERE user_id = :user_id AND order_prod_id = :productID";
    $stmt2 = $db->prepare($reviewQuery);
    $stmt2->execute([':user_id' => $userId, ':productID' => $productID]);
    $reviewCount = $stmt2->fetchColumn();

    $canReview = ($purchaseCount > 0);
    $alreadyReviewed = ($reviewCount > 0);
} else {
    $canReview = false;
    $alreadyReviewed = false;
}
?>
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



    function sortVariationSizes($availableSizes){
        sort($availableSizes);
        $temp = array();

        $extraSmallPosition = array_search("extraSmall", $availableSizes);
        if ($extraSmallPosition !== false) {
            $temp[] = $availableSizes[$extraSmallPosition];
            array_splice($availableSizes,$extraSmallPosition, 1 );
        }

        $smallPosition = array_search("small", $availableSizes);
        if ($smallPosition !== false) {
            $temp[] = $availableSizes[$smallPosition];
            array_splice($availableSizes,$smallPosition, 1 );
        }

        $mediumPosition = array_search("medium", $availableSizes);
        if ($mediumPosition !== false) {
            $temp[] = $availableSizes[$mediumPosition];
            array_splice($availableSizes,$mediumPosition, 1 );
        }

        $largePosition = array_search("large", $availableSizes);
        if ($largePosition !== false) {
            $temp[] = $availableSizes[$largePosition];
            array_splice($availableSizes,$largePosition, 1 );
        }

        $extraLargePosition = array_search("extraLarge", $availableSizes);
        if ($extraLargePosition !== false) {
            $temp[] = $availableSizes[$extraLargePosition];
            array_splice($availableSizes,$extraLargePosition, 1 );
        }

        $output = array_merge($temp, $availableSizes);
        return $output;
    }
         
    
    $productID = $_GET["id"];

    // Fetch product details
    $item = getDBResult($db, "SELECT * FROM product WHERE product_id=:productID", ":productID", $productID)[0];

    //get list of variations
    $productConfigs =  getDBResult($db, "SELECT * FROM product_configuration INNER JOIN variation_option ON product_configuration.variation_option_id = variation_option.variation_option_id WHERE product_item_id=:itemID", ":itemID", $productID);
    
    $AvailableConfigs;

    $sizeAvailable = false;
    $colourAvaliable = false;
	$hasAltImages = false;

    $availableColours = array();
    $availableSizes = array();



    foreach($productConfigs as $config){

        $AvailableConfigs[$config["variation_value"]] = true;

        if($config["variation_type"] == "Size" ){
            $availableSizes[] = $config["variation_value"];
            $sizeAvailable = true;
        }



        if($config["variation_type"] == "Colour"){
            $availableColours[] = $config["variation_value"];
            $colourAvaliable = true;
        }

    }

    $availableSizes = sortVariationSizes($availableSizes);

    $reviews = getDBResult($db, "SELECT * FROM users_review WHERE order_prod_id=:productID", ":productID", $productID);



     session_start();
    

// if cant find picture show placeholder
  if(!file_exists($item["product_image"])){
  	$item["product_image"] = "images/missingImage.png";
  }


    function updateQuantity($db, $quantity, $basketID){
        $newQuantity = $quantity + 1;

        $updateBasket = $db->prepare("UPDATE asad_basket SET quantity = :quantity WHERE basket_id=:basketID");
        $updateBasket->execute([':basketID' => $basketID, ":quantity" => $newQuantity]);
    
    }

    function addToBasket($db, $userId, $productID, $colourOption, $sizeOption){

        $quantity = 1;

        if(isProductInStock($db, htmlspecialchars($productID) )){
           
            if(!isset($colourOption)) { $colourOption = "";};
            if(!isset($sizeOption)) { $sizeOption = "";};
           
            if($colourOption != "" && $sizeOption != ""){
           
                $addToBasket = $db->prepare("INSERT INTO asad_basket (user_id, product_id, colour, size, quantity) VALUES (:user_id, :product_id, :colour_variation_id, :size_variation_id, :quantity)");
                $addToBasket->execute([':user_id' => $userId, ':product_id' => $productID, ":colour_variation_id" =>  $colourOption, ":size_variation_id" => $sizeOption, ':quantity' => $quantity]);
           
            }else if($colourOption != ""){

                $addToBasket = $db->prepare("INSERT INTO asad_basket (user_id, product_id, colour, quantity) VALUES (:user_id, :product_id, :colour_variation_id, :quantity)");
                $addToBasket->execute([':user_id' => $userId, ':product_id' => $productID, ":colour_variation_id" =>  $colourOption,  ':quantity' => $quantity]);
            
            }else if($sizeOption != ""){
            
                $addToBasket = $db->prepare("INSERT INTO asad_basket (user_id, product_id, size, quantity) VALUES (:user_id, :product_id, :size_variation_id, :quantity)");
                $addToBasket->execute([':user_id' => $userId, ':product_id' => $productID, ":size_variation_id" => $sizeOption, ':quantity' => $quantity]);

                
            
            }else{
                $addToBasket = $db->prepare("INSERT INTO asad_basket (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $addToBasket->execute([':user_id' => $userId, ':product_id' => $productID,  ':quantity' => $quantity]);
            }
           
        }

    }

    function addOrUpdateVariation($db, $existingBasket, $productID , $colourOption, $sizeOption){
        //go through the basket 

        $alreadyExists = false;

       foreach($existingBasket as $basketItem){

           if(isset($colourOption)){
               $colourVariationOptionName = getNameFromVariationOptionID($db, $basketItem["Colour"]);
           }

           if(isset($sizeOption)){
               $sizeVariationOptionName = getNameFromVariationOptionID($db, $basketItem["Size"]);
           }

           if($basketItem["product_id"] == $productID){

            
                if($colourVariationOptionName == $colourOption &&  $sizeVariationOptionName == $sizeOption){
                    updateQuantity($db, $basketItem["quantity"] , $basketItem["basket_id"]);
                    $alreadyExists = true;
                    break;
                }

            }

        }

        if(!$alreadyExists){
            $colourOptionID = getVariationIDFromName($db, $colourOption);
            $sizeOptionID = getVariationIDFromName($db, $sizeOption);
            addToBasket($db, $_SESSION['uid'] , $productID, $colourOptionID, $sizeOptionID);
        }

   }
    
    if($_SERVER['REQUEST_METHOD'] === 'POST')  {

        if (isset($_SESSION['uid'])) { // is user logged in

            

            $userId = $_SESSION['uid']; 
            $quantity = 1;  

            
            //get everything from the basket 
            $checkBasket = $db->prepare("SELECT * FROM asad_basket WHERE user_id = :user_id AND product_id = :product_id");
            $checkBasket->execute([':user_id' => $userId, ':product_id' => $productID]);
            $existingBasket = $checkBasket->fetchAll(PDO::FETCH_ASSOC);

            //get the colour and size that the user submitted 
            $colourOption = $_POST["colour"];
            $sizeOption = $_POST["size"];


            //get the corrosponding IDs
            $colourOptionID = getVariationIDFromName($db, $colourOption);
            $sizeOptionID = getVariationIDFromName($db, $sizeOption);

            // if there is no current item in the basket with the current productID just call addToBasket
            if(count($existingBasket) <= 0){
                addToBasket($db, $userId , $productID, $colourOptionID, $sizeOptionID);
            }else{
                addOrUpdateVariation($db, $existingBasket, $productID , $colourOption, $sizeOption);
            }

           
            
                    
           
            
        } 

        header("Location: Basket.php");
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

                    <?php foreach($availableColours as $Colour) { ?>
                        <td>
                            <div onclick="selectColour('<?php echo $Colour ?>')" id="colour-shape-<?php echo $Colour ?>" class="colour-shape" style="background-color: <?php echo $Colour ?>;"></div>
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

                    <?php foreach($availableSizes as $Size) { ?>
                        <?php $sizeVarName = str_replace(".","", $Size); ?>
                            <td onclick="sizeSelect('<?php echo $Size ?>')" class="size-option" id="size-option-<?php echo $sizeVarName ?>"><?php echo getVaraitionShortName($Size, $productConfigs); ?></td>
                    <?php } ?>

                    </tr>
                    
                </table>
            </div>
            <?php } ?>    	

           

            
            <form method="POST" id="mainForm" >
                <label for="quantity" style="display:none;">Quantity:</label>
                <input type="number" name="quantity" id="quantity" min="1" value="1" style="display:none;">

                <input id="colourFormText" type="text" name="colour" id="quantity"   style="display:none;">
                <input id="colourFormSize" type="text" name="size" id="quantity"  style="display:none;">

                <?php if(isProductInStock($db, htmlspecialchars($productID) )){ ?>
                        <button type="button" id="addToBasket">Add to Basket</button>
                <?php }else{ ?>
                    <button type="button" id="addToBasketOutOfStock">Out of Stock</button>
                <?php } ?>
                
                
                <div id="reviewContainer">
    <?php if (isset($_SESSION['uid'])) { ?>
        <a href="#" id="reviewLink">Write a Review</a>


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

    <div class="modal fade" id="variationModal" tabindex="-1" aria-labelledby="varationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="color: black;">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Please Select a product configuration</h5>
                    <button type="button" class="btn-close" id="closeBtn" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="vairationModal-subtitle">Please select a Size and/or colour to continue</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelBtn">Cancel</button>
                </div>
            </div>
        </div>
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

       <div class="modal fade" id="reviewNotificationModal" tabindex="-1" aria-labelledby="reviewNotificationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="color: black;">
      <div class="modal-header">
        <h5 class="modal-title" id="reviewNotificationModalLabel">Review Notification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="reviewNotificationMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



            <?php include 'footer.php'; ?>

            <script>


var isLoggedIn = <?php if(isset($_SESSION["uid"])) { echo 'true'; } else { echo 'false'; } ?>

var ColourRequired = <?php if($colourAvaliable == true) { echo 'true'; } else { echo 'false'; } ?>

var SizeRequired = <?php if($sizeAvailable == true) { echo 'true'; } else { echo 'false'; } ?>


function canSubmitFormVariation(){

    var formSize = document.getElementById("colourFormSize")
    var formColour = document.getElementById("colourFormText")

    
    if(ColourRequired && formColour.value == ""){
        return false
    }

    if(SizeRequired && formSize.value == ""){
        return false
    }

    return true

}


document.getElementById("addToBasket").onclick = (e) => {

    

    var loginModal = new bootstrap.Modal(document.getElementById("loginModal"), { backdrop: "static" });
    var variationModal = new bootstrap.Modal(document.getElementById("variationModal"), { backdrop: "static" });

    if(isLoggedIn){
        
        if(canSubmitFormVariation() == false){

            if(ColourRequired == false && SizeRequired == true){
                document.getElementById("vairationModal-subtitle").innerHTML = "Please Select a size To continue"
            }

            if(ColourRequired == true && SizeRequired == false){
                document.getElementById("vairationModal-subtitle").innerHTML = "Please Select a Colour To continue"
            }

            if(ColourRequired == true && SizeRequired == true){
                document.getElementById("vairationModal-subtitle").innerHTML = "Please Select a size and colour To continue"
            }           

            variationModal.show();
        }else{
            document.getElementById("mainForm").submit()
        }
        
    }else{
        loginModal.show();
    }


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


<?php

    function getVaraitionShortName($variationName ,  $productConfigs){
        foreach($productConfigs as $config){
            if($config["variation_value"] == $variationName && $config["variation_short_name"] != "" ){
                return $config["variation_short_name"];
            }
        }

        return $variationName;
    }

?>


    let selectedSize = "none"
    let selectedColour = "none"



    function sizeSelect(size){


        
        <?php foreach($availableSizes as $size) { ?>
            <?php $sizeVarName = "size".str_replace(".","", $size); ?>
            <?php $sizeIdName = str_replace(".","", $size); ?>
            var <?php echo $sizeVarName ?> = document.getElementById("size-option-<?php echo $sizeIdName ?>")
            if(<?php echo $sizeVarName ?> != null){ <?php echo $sizeVarName ?>.style.backgroundColor = "var(--card-bg)" }
        <?php } ?>

        var formSize = document.getElementById("colourFormSize")


        switch(size){

            <?php foreach($availableSizes as $size) { ?>
                <?php $sizeVarName = "size".str_replace(".","", $size); ?>
                case "<?php echo $size ?>":
                    <?php echo $sizeVarName ?>.style.backgroundColor = "var(--icon-bg)"
                    formSize.value = "<?php echo $size ?>"
                    break;

            <?php } ?>

        }

        //alert(size)
    }

    function selectColour(colour){

        <?php foreach($availableColours as $color) { ?>
            var <?php echo $color ?>Circle = document.getElementById("colour-shape-<?php echo $color ?>")
            if(<?php echo $color ?>Circle != null){ <?php echo $color ?>Circle.style.borderWidth = "0px" }
        <?php } ?>

        var formColour = document.getElementById("colourFormText")

        switch(colour){

            <?php foreach($availableColours as $color) { ?>
                case "<?php echo $color ?>":
                    <?php echo $color ?>Circle.style.borderWidth = "5px"
                    formColour.value = "<?php echo $color ?>"
                    break;
            <?php } ?>
        }
    }
var canReview = <?php echo json_encode($canReview); ?>;
var alreadyReviewed = <?php echo json_encode($alreadyReviewed); ?>;


var reviewNotificationModal = new bootstrap.Modal(document.getElementById("reviewNotificationModal"), { backdrop: "static" });

document.getElementById("reviewLink").addEventListener("click", function(e) {
    e.preventDefault();
    var messageEl = document.getElementById("reviewNotificationMessage");
    if (!canReview) {
        messageEl.innerText = "You haven't purchased this product yet. You can only review products you've bought.";
        reviewNotificationModal.show();
    } else if (alreadyReviewed) {
        messageEl.innerText = "You have already submitted a review for this product.";
        reviewNotificationModal.show();
    } else {
        window.location.href = "reviews.php?id=<?php echo htmlspecialchars($productID); ?>";
    }
});



</script>



    <style>

a{
    text-decoration: none;
}

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


.reviewHeader {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    font-size: 18px; <?php echo $color ?>
    margin-bottom: 10px;
}



.reviewPreview {
    background: linear-gradient(145deg, var(--card-bg), var(--icon-bg));
    border-radius: 15px;
    box-shadow: 0 6px 15px var(--shadow);
    padding: 30px;
    margin: 20px auto;
    width: 90%;
    max-width: 1000px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    color: var(--text-color);
    text-align: center;
}

.reviewPreview:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px var(--shadow);
}


.reviewHeader {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 10px;
    color: var(--text-color);
}

.reviewUser {
    color: var(--text-color);
}

.reviewRating {
    color: var(--text-color);
    font-size: 20px;
}

.reviewText {
    font-size: 18px;
    color: var(--text-color);
    font-style: italic;
    margin-bottom: 15px;
}

.reviewSeparator {
    border: none;
    height: 1px;
    background: #ddd;
    margin-top: 15px;
    width: 100%;
}
#reviewPreviewContainer {
    background-color: var(--bg-color);
    border: 2px solid var(--border-color);
    border-radius: 10px;
    padding: 40px;
    width: 100%;
    max-width: 100%;
    margin: 30px auto;
    box-shadow: 0px 4px 12px var(--shadow);
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
        margin-left: 20px;
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
        margin-left: 2.5%;
        height: 650px;
        vertical-align: top;
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
        
        object-fit:cover;
       
        border-radius: 25px;
        vertical-align: middle;

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

    #addToBasketOutOfStock{

        

        width: 80%;
        margin-left: 10%;

        height: 45px;

}

}

        
        #mainInfoDetails{

            grid-column-start: 2;
            padding-top:20px;

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

        #similarProductsContainer > a:link{
            color: var(--text-color);
        }

        #similarProductsContainer > a:visited {
            color: var(--text-color);

        }

        #similarProductsContainer > a:hover {
            color: var(--text-color);
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

            padding-left:20px;
            padding-right:20px;

            

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
            border-color: var(--border-color);
            padding: 5px;
            cursor: pointer;


        }

     
        #addToBasket {
    background-color: var(--icon-bg);
    color: var(--text-color);
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    font-weight: bold;
    padding: 10px 20px;
    transition: background-color 0.3s ease;
    
}

#addToBasket:hover {
    background-color: var(--secondary-text);
    color: var(--bg-color);
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
    font-size: 1em;
    color: var(--text-color);
    font-weight: bold;
    padding: 10px 20px;
    border-radius: 5px;
    background-color: var(--icon-bg);
    transition: background-color 0.3s ease;
    
}

#reviewLink:hover {
    background-color: var(--secondary-text);
    color: var(--bg-color);
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
            width: 80%;
            margin-bottom: 0;
            font-weight: bold;
            
            
            
        }

        .similarProductTitle:visited {
            color:var(--text-color);
            
        }



    </style>

</body>
</html>
