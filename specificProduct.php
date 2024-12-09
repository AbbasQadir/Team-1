<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<script>src = "homescript.js"</script>
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
    $result = $db->prepare("SELECT * FROM products WHERE product_id=:id");
    $result->bindParam(":id", $productID);
    $result->execute();
    $item = $result->fetch(PDO::FETCH_ASSOC);

    
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
        <img src="<?php echo htmlspecialchars($item["image"]); ?>" id="mainImage" width="250px">
        
        <h1 id="mainTitle"><?php echo htmlspecialchars($item["item_name"]); ?></h1>
        <h5 id="mainPrice">£<?php echo htmlspecialchars($item["item_price"]); ?></h5>

        
        <form method="POST">
            <label for="quantity" style="display:none;">Quantity:</label>
            <input type="number" name="quantity" id="quantity" min="1" value="1" style="display:none;">
            <button type="submit" id="addToBasket">Add to Basket</button>
        </form>
    </div>

    <div id="detailedInfoContainer">
        <h3 style="font-weight:bold;">Description</h3>
            <ul>
            <?php 
            $description = explode('-', $item["description"]);
			foreach ($description as $description){
            echo "<li>" . htmlspecialchars(trim($description)) . "</li>";
            }

      
            ?>
            </ul>
            <br>
         <h3 style="font-weight:bold;">Category</h3> 
         <p><?php echo htmlspecialchars($item["item_category"]); ?></p>
    </div>

           <?php 
            
            $simmilarResults = searchProducts($db, $item["item_category"]);
			//var_dump($simmilarResults);
            
            ?> 
            
    <div id="similarProductsContainer">
        <h1 id="similarProductsHeader">Similar Products</h1>

            
            
        <a href="/specificProduct.php?id=<?php echo $simmilarResults[0]['product_id'] ?>">
            <div class="similarProductItem">
                <img class="similarProductImg" src="<?php echo $simmilarResults[0]['image'] ?>">
                <p class="similarProductTitle"><?php echo $simmilarResults[0]["item_name"] ?></p>
                <p class="similarProductPrice">£<?php echo $simmilarResults[0]['item_price'] ?></p>
            </div>
        </a>
        
        <a href="/specificProduct.php?id=<?php echo $simmilarResults[1]['product_id'] ?>">
            <div class="similarProductItem">
                <img class="similarProductImg" src="<?php echo $simmilarResults[1]['image'] ?>">
                <p class="similarProductTitle"><?php echo $simmilarResults[1]["item_name"] ?></p>
                <p class="similarProductPrice">£<?php echo $simmilarResults[1]['item_price'] ?></p>
            </div>
        </a>

        <a href="/specificProduct.php?id=<?php echo $simmilarResults[2]['product_id'] ?>">
            <div class="similarProductItem">
                <img class="similarProductImg" src="<?php echo $simmilarResults[2]['image'] ?>">
                <p class="similarProductTitle"><?php echo $simmilarResults[2]["item_name"] ?></p>
                <p class="similarProductPrice">£<?php echo $simmilarResults[2]['item_price'] ?></p>
            </div>
        </a>
    </div>
            
            <?php include 'footer.php'; ?>

    <style>
        body {
            margin-bottom: 0px;
        }

        a {
            color: inherit;
        }

        a:visited {
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
            background-color: rgb(216, 216, 216);
            height: 400px;
            display: grid;
            grid-template-columns: 350px auto;
            grid-template-rows: 150px 40px 20px ;
            gap: 20px;
        }

        #mainImage {
            display: inline;
			grid-column-start: 1;
            grid-column-end: 2;
            grid-row-start: 1;
            grid-row-end: 4;

            border-radius: 15px;
            margin-left: 50px;
            margin-top: 50px;
            max-height: 300px;
        }

        #mainTitle {
            grid-column-start: 2;
            grid-column-end: 4;
			margin-top: 50px;

			display: -webkit-box;
            -webkit-line-clamp: 2; /* maximum number of lines  */
            line-clamp: 2; 
            -webkit-box-orient: vertical;
        }

        #mainPrice {
			font-size: 25px;
            display: inline;
            grid-column-start: 2;
            grid-column-end: 4;
        }

        #addToBasket {
            display: inline;
            background-color: #084298;
            grid-column-start: 2;
            grid-column-end: 4;
			cursor: pointer;

            color: white;
            border: none;
            border-radius: 15px;
            padding: 10px;
        }

        #similarProductsContainer {
            width: 100%;
            display: block;
            background-color: rgb(216, 216, 216);
            padding-top: 25px;
            padding-bottom: 25px;
        }

        #detailedInfoContainer > p {
            margin-bottom: 25px;
        }

        .similarProductItem {
            background-color: white;
            width: 30%;
            margin-left: 2%;
            padding-bottom: 25px;
            display: inline-block;
            border-radius: 25px;
        	
        }

        .similarProductImg {
            border-radius: 5px;
            margin-top: 20px;
            margin-left: 5%;
            width: 90%;
        	height:550px;
        }

        .similarProductTitle {
            margin-top: 25px;
            margin-left: 25px;
            margin-bottom: 0;
            font-weight: bold;
        }

        .similarProductPrice {
            margin-top: 5px;
            margin-left: 25px;
            margin-bottom: 0;
            font-size: large;
        }

        #similarProductsHeader {
            margin-top: 50px;
            margin-bottom: 50px;
            text-align: center;
            display: block;
            width: 100%;
        }
    </style>

</body>
</html>
