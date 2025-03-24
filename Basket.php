<?php
session_start();

try {
    require_once(__DIR__ . '/PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . $ex->getMessage();
    exit;
}


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
          <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content" style="color: black; border-radius: 12px;">
                  <div class="modal-header border-0">
                      <h5 class="modal-title fw-bold" id="loginModalLabel">Login Required</h5>
                      <button type="button" class="btn-close" id="closeBtn" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body py-4">
                      <p class="mb-0">You need to log in to view your basket.</p>
                  </div>
                  <div class="modal-footer border-0">
                      <a href="login.php" class="btn btn-primary px-4">Log In</a>
                      <button type="button" class="btn btn-outline-secondary" id="cancelBtn">Cancel</button>
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

// Get basket from user that is logged in 
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
        product p ON b.product_id = p.product_id 
    JOIN
        product_item pi ON b.product_id = pi.product_id
    WHERE 
        b.user_id = :user_id
";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$basketItems = $stmt->fetchAll(PDO::FETCH_ASSOC);


$basketTotal = 0;
foreach ($basketItems as $item) {
    $basketTotal += $item['total_price'];
}


if (isset($_GET['remove'])) {
    $basketID = $_GET['remove'];
    $removeQuery = "DELETE FROM asad_basket WHERE basket_id = :basketID";
    $removeStmt = $db->prepare($removeQuery);
    $removeStmt->execute([':basketID' => $basketID]);
    header("Location: Basket.php");
    exit;
}


if (isset($_POST['update_quantity']) && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $basketID => $newQuantity) {
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
    <title>Your Shopping Basket</title>
    <link rel="stylesheet" href="homestyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        .basket-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            color: var(--text-color);
        }
        
        .basket-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .basket-title {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .basket-count {
            color: var(--accent-color);
            font-weight: normal;
        }
        
        /* Item Card Layout */
        .basket-items {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .basket-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1rem;
            padding: 1.5rem;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            position: relative;
        }
        
        .basket-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .basket-item-details {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .basket-item-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .basket-variations {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }
        
        .basket-variation {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .variation-color {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
        }
        
        .variation-size {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        
        .basket-item-price {
            font-weight: 600;
            color: var(--accent-color);
        }
        
        .basket-quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .basket-quantity {
            width: 3.5rem;
            padding: 0.5rem;
            text-align: center;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-color);
            color: var(--text-color);
        }
        
        .basket-quantity:focus {
            outline: 2px solid var(--accent-color);
            border-color: transparent;
        }
        
        .basket-remove {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .basket-remove:hover {
            color: var(--accent-color);
        }
        
      
        .basket-summary {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .basket-summary-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .basket-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .basket-summary-total {
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        
  
        .basket-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
            font-family: inherit;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }
        
        .btn-secondary:hover {
            background-color: var(--bg-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .basket-empty {
            text-align: center;
            padding: 3rem 1rem;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .empty-basket-icon {
            font-size: 3rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        
        .empty-basket-message {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            color: var(--text-muted);
        }
        
  
        @media (max-width: 768px) {
            .basket-item {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .basket-item-image {
                width: 100%;
                height: auto;
                max-width: 200px;
                margin: 0 auto;
            }
            
            .basket-variations {
                justify-content: center;
            }
            
            .basket-quantity-controls {
                justify-content: center;
                margin-top: 1rem;
            }
            
            .basket-remove {
                top: 0.75rem;
                right: 0.75rem;
            }
            
            .basket-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        @media (min-width: 768px) {
            .basket-layout {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 2rem;
                align-items: start;
            }
            
            .basket-summary {
                margin-top: 0;
                position: sticky;
                top: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="basket-container">
    <div class="basket-header">
        <h1 class="basket-title">Your Basket <span class="basket-count">(<?php echo count($basketItems); ?> items)</span></h1>
    </div>
    
    <?php if (count($basketItems) > 0): ?>
    <div class="basket-layout">
        <div class="basket-main">
            <form method="POST" action="Basket.php" id="basket-form">
                <div class="basket-items">
                    <?php foreach ($basketItems as $item): ?>
                    <div class="basket-item">
                        <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="basket-item-image">
                        
                        <div class="basket-item-details">
                            <div>
                                <h3 class="basket-item-name"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                
                                <div class="basket-variations">
                                    <?php if(isset($item["Colour"])): ?>
                                    <div class="basket-variation">
                                        <span class="variation-color" style="background-color: <?php echo htmlspecialchars(getNameFromVariationOptionID($db, $item["Colour"])); ?>;"></span>
                                        <span><?php echo htmlspecialchars(getNameFromVariationOptionID($db, $item["Colour"])); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($item["Size"])): ?>
                                    <div class="basket-variation">
                                        <span class="variation-size"><?php echo htmlspecialchars(getShortNameFromVariationOptionID($db, $item["Size"])); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="basket-item-price">
                                &pound;<?php echo number_format($item['price'], 2); ?> each
                            </div>
                        </div>
                        
                        <div class="basket-item-actions">
                            <div class="basket-quantity-controls">
                                <input type="number" name="quantities[<?php echo $item['basket_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="99" required class="basket-quantity" aria-label="Quantity">
                            </div>
                            <div class="basket-item-total">
                                <strong>Total: &pound;<?php echo number_format($item['total_price'], 2); ?></strong>
                            </div>
                        </div>
                        
                        <a href="Basket.php?remove=<?php echo $item['basket_id']; ?>" class="basket-remove" aria-label="Remove item">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" name="update_quantity" class="btn btn-secondary">
                    <i class="fas fa-sync-alt"></i> Update Quantities
                </button>
            </form>
        </div>
        
        <div class="basket-summary">
            <h2 class="basket-summary-title">Order Summary</h2>
            
            <div class="basket-summary-row">
                <span>Subtotal</span>
                <span>&pound;<?php echo number_format($basketTotal, 2); ?></span>
            </div>
            
            <div class="basket-summary-row">
                <span>Shipping</span>
                <span>Calculated at checkout</span>
            </div>
            
            <div class="basket-summary-row basket-summary-total">
                <span>Total</span>
                <span>&pound;<?php echo number_format($basketTotal, 2); ?></span>
            </div>
            
            <div class="basket-actions">
                <a href="checkout.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Proceed to Checkout
                </a>
                
                <a href="previous_orders.php" class="btn btn-secondary">
                    <i class="fas fa-history"></i> View Previous Orders
                </a>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <div class="basket-empty">
        <div class="empty-basket-icon">
            <i class="fas fa-shopping-basket"></i>
        </div>
        <h2 class="empty-basket-message">Your basket is empty</h2>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Continue Shopping
        </a>
    </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const quantityInputs = document.querySelectorAll('.basket-quantity');
        quantityInputs.forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('basket-form').submit();
                }
            });
        });
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>