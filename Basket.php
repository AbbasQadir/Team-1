<?php
session_start();

try {
    require_once(__DIR__ . '/PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . $ex->getMessage();
    exit;
}

// check if log in is there

if (!isset($_SESSION['uid'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Required</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
		<link rel="stylesheet" href="homestyle.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    	
    </head>
    <body>

        
        <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="color: black;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">Login Required</h5>
                        <button type="button" class="btn-close" id="closeBtn" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>You need to log in to view your basket.</p>
                    </div>
                    <div class="modal-footer">
                        <a href="login.php" class="btn btn-primary">Log In</a>
                        <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var loginModal = new bootstrap.Modal(document.getElementById("loginModal"), { backdrop: "static" });
                loginModal.show();

                document.getElementById("cancelBtn").addEventListener("click", function () {
                    window.history.back(); 
                });
            
                document.getElementById("closeBtn").addEventListener("click", function () {
                    window.history.back();
                });
            
            
            
            });
        </script>

    </body>
    </html>
    <?php
    exit;
}

$user_id = $_SESSION['uid'];

// get basket from user that is logged in 
$query = "
    SELECT 
        p.product_name, 
        p.product_image, 
        pi.price, 
        b.quantity, 
        (b.quantity * pi.price) AS total_price,
        b.product_id,
        b.basket_id,
        b.Colour,
        b.Size
    FROM 
        asad_basket b 
    JOIN 
        product p 
    ON 
        b.product_id = p.product_id 
    JOIN
        product_item pi
    ON
        b.product_id = pi.product_id
    WHERE 
        b.user_id = :user_id
";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$basketItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// basket item removal
if (isset($_GET['remove'])) {
    $basketID = $_GET['remove'];
    $removeQuery = "DELETE FROM asad_basket WHERE basket_id=:basketID";
    $removeStmt = $db->prepare($removeQuery);
    $removeStmt->execute([':basketID' => $basketID]);
    header("Location: Basket.php");
    exit;
}

// quantity updater
if (isset($_POST['update_quantity']) && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $basketID => $newQuantity) { // Use basket_id as key
        if ($newQuantity > 0) {
            $updateQuery = "UPDATE asad_basket SET quantity = :quantity WHERE basket_id = :basketID";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([
                ':quantity' => $newQuantity,
                ':basketID' => $basketID,
            ]);
        }
    }
    header("Location: Basket.php");
    exit;
}

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basket</title>
    <link rel="stylesheet" href="homestyle.css">
</head>
<body>

<h1 class="basket-title">Your Basket</h1>

<?php if (count($basketItems) > 0): ?>
    <form method="POST" action="Basket.php" class="basket-form">
        <table class="basket-table">
            <thead>
                <tr>
                    <th class="basket-header">Product Name</th>
                    <th class="basket-header">Product Picture</th>
                    <th class="basket-header">Quantity</th>
                    <th class="basket-header">Price per Item</th>
                    <th class="basket-header">Total Price</th>
                    <th class="basket-header">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($basketItems as $item): ?>
                    <tr class="basket-row">
                        <td class="basket-cell">
                 			<?php echo htmlspecialchars($item['product_name'])  ?>
                            <br>
                            <div id='productVariationsContainer'>

                                <?php if(isset($item["Colour"]) ) { ?>
                                    <div id="productVariationsColourIcon" style="background-color: <?php echo htmlspecialchars(getNameFromVariationOptionID($db, $item["Colour"])); ?>;"></div>
                                    <?php echo htmlspecialchars(getNameFromVariationOptionID($db, $item["Colour"]))."<br>"  ?>
                                <?php } ?>

                               

                                <?php if(isset($item["Size"])) { ?>
                                    <!-- <div id="productVariationsSizeIcon"> <?php echo getSymbolLetterForSize(getNameFromVariationOptionID($db, $item["Size"])); ?> </div> -->
                                    <?php echo "Size: ".htmlspecialchars(getShortNameFromVariationOptionID($db, $item["Size"]))  ?>
                                <?php } ?>

                                
                            </div>
               	 		</td>
                        <td class="basket-cell">
                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="basket-image">
                        </td>
                        <td class="basket-cell">
                            <input type="number" name="quantities[<?php echo $item['basket_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" required class="basket-quantity">
                        </td>
                        <td class="basket-cell">&pound;<?php echo number_format($item['price'], 2); ?></td>
                        <td class="basket-cell">&pound;<?php echo number_format($item['total_price'], 2); ?></td>
                        <td class="basket-cell">
                            <a href="Basket.php?remove=<?php echo $item['basket_id']; ?>" class="basket-remove-btn">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="basket-actions">
            <button type="submit" name="update_quantity" class="basket-update-btn">Update Basket</button>
        </div>
    </form>

    <div class="basket-checkout">
        <a href="checkout.php" class="basket-checkout-btn">Checkout</a>
    </div>

<?php else: ?>
    <p class="basket-empty">Your basket is empty.</p>
<?php endif; ?>

<div class="basket-previous-orders">
    <a href="previous_orders.php" class="basket-previous-orders-btn">Previous Orders</a>
</div>

<style>
    body {
    margin: 0;
    background-color: var(--bg-color);
    color: var(--text-color);
    font-family: 'Merriweather', serif;
}

#productVariationsColourIcon{
        display: inline-block;
        background-Color:red;
        width: 15px;
        height:15px;
        border-radius: 7.5px;

    }

    #productVariationsSizeIcon{
        display: inline-block;
        width: 15px;
        height:15px;
        border-width: 2px;
        border-color: gray;
        color: gray;
        border-style: solid;

		margin:0;
		padding:0;

        font-size: 6px;


    }

.basket-title {
    margin-top: 20px;
    text-align: center;
    color: var(--text-color);
}

.basket-table {
    width: 97%;
    border-collapse: collapse;
    margin: 20px auto;
    background: var(--card-bg);
    color: var(--text-color);
}

.basket-table, .basket-header, .basket-cell {
    border: 1px solid var(--border-color);
}

.basket-header {
    padding: 12px;
    background-color: var(--accent-color);
    color: var(--bg-color);
    text-align: center;
    font-family: 'Merriweather', serif;
}

.basket-cell {
    text-align: center;
    padding: 12px;
}

.basket-image {
    width: 150px;
    height: auto;
    object-fit: cover;
}

.basket-remove-btn, 
.basket-checkout-btn, 
.basket-previous-orders-btn {
    font-family: 'Merriweather', serif;
    background-color: var(--accent-color);
    color: var(--bg-color);
    border: none;
    padding: 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    width: 200px;
    text-decoration: none;
    margin: 10px;
    display: inline-block;
    text-align: center;
    box-shadow: 0px 0px 9px 0px var(--shadow);
}

.basket-remove-btn:hover, 
.basket-checkout-btn:hover, 
.basket-update-btn:hover, 
.basket-previous-orders-btn:hover {
    background-color: var(--accent-hover);
    color: var(--text-color);
    box-shadow: 0px 0px 9px 0px var(--shadow);
    text-decoration: none;
}

.basket-update-btn {
    font-family: 'Merriweather', serif;
    background-color: var(--accent-color);
    color: var(--bg-color);
    border: none;
    padding: 18px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    width: 200px;
    text-decoration: none;
    margin-left: 10px;
    display: inline-block;
    text-align: center;
    margin-bottom: 10px;
    box-shadow: 0px 0px 9px 0px var(--shadow);
}
</style>

</html>
<?php include 'footer.php'; ?>
