<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                // Update quantity if the product is already tehre
                $newQuantity = $existingBasket['quantity'] + $quantity;
                $updateBasket = $db->prepare("UPDATE asad_basket SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
                $updateBasket->execute([':quantity' => $newQuantity, ':user_id' => $userId, ':product_id' => $productID]);
            } else {
                // put new product into the basket
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

        <!-- Add to Basket Form -->
        <form method="POST">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" min="1" value="1" style="display:none;">
            <button type="submit" id="addToBasket">Add to Basket</button>
        </form>
    </div>

    <div id="detailedInfoContainer">
        <h3 style="font-weight:bold;">Description</h3>
        <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Illum obcaecati praesentium cumque harum reiciendis similique maxime suscipit molestiae iure tempora, est ipsam repudiandae hic porro. Ipsum sit quos nobis reiciendis?</p>
        <p style="font-weight:bold;">Category</p>
        <p><?php echo htmlspecialchars($item["item_category"]); ?></p>
    </div>

    <div id="similarProductsContainer">
        <h1 id="similarProductsHeader">Similar Products</h1>

        <a href="">
            <div class="similarProductItem">
                <img class="similarProductImg" src="images/book.jpg">
                <p class="similarProductTitle">Title</p>
                <p class="similarProductPrice">price</p>
            </div>
        </a>
        
        <a href="">
            <div class="similarProductItem">
                <img class="similarProductImg" src="/images/book.jpg">
                <p class="similarProductTitle">Title</p>
                <p class="similarProductPrice">price</p>
            </div>
        </a>

        <a href="">
            <div class="similarProductItem">
                <img class="similarProductImg" src="/images/book.jpg">
                <p class="similarProductTitle">Title</p>
                <p class="similarProductPrice">price</p>
            </div>
        </a>
    </div>

    <style>
        body {
            margin-bottom: 550px;
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
        }

        #mainImage {
            display: inline;
            border-radius: 15px;
            margin-left: 50px;
            margin-top: 50px;
            max-height: 300px;
        }

        #mainTitle {
            display: inline;
            margin-left: 50px;
            position: relative;
            bottom: 45px;
        }

        #mainPrice {
            display: inline;
            position: relative;
            top: 50px;
            right: 135px;
        }

        #addToBasket {
            display: inline;
            background-color: #2a4d69;
            position: relative;
            top: 115px;
            left: -170px;
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
