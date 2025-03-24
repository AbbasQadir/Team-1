<?php
session_start();
include 'navbar.php';
require_once("PHPHost.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$is_logged_in = isset($_SESSION['uid']);
$products = [];
$message = '';
$selectedProduct = null;

if ($is_logged_in) {
    $user_id = $_SESSION['uid'];
    $requestedProductID = $_GET['id'] ?? null;
    $productExists = false;

    try {
       
        $query = "SELECT DISTINCT p.product_id, p.product_name, p.product_image 
                  FROM product p
                  JOIN order_prod op ON p.product_id = op.product_item_id
                  JOIN orders o ON op.orders_id = o.orders_id
                  WHERE o.user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

      
        foreach ($products as $product) {
            if ($requestedProductID == $product['product_id']) {
                $productExists = true;
                $selectedProduct = $product;
                break;
            }
        }

        if (empty($products)) {
            $message = "You need to purchase a product before leaving a review.";
        } elseif ($requestedProductID && !$productExists) {
            $message = "You haven't purchased this product yet. You can only review products you've bought.";
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
} else {
    $message = "Please log in to submit a review.";
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $is_logged_in) {
    $productId = $_POST['Product_id'] ?? null;
    $rating = $_POST['Rating'] ?? null;
    $reviewText = trim($_POST['Review_text'] ?? '');

    if (!$productId || !$rating || empty($reviewText) || $rating < 1 || $rating > 5) {
        $message = "Invalid review data.";
    } else {
        try {
           
            $checkQuery = "SELECT users_review_id FROM users_review WHERE user_id = :user_id AND order_prod_id = :product_id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $checkStmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                $message = "You have already submitted a review for this product.";
            } else {
            
                $insertQuery = "INSERT INTO users_review (user_id, order_prod_id, rating, comment) 
                                VALUES (:user_id, :product_id, :rating, :review_text)";
                $stmt = $db->prepare($insertQuery);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
                $stmt->bindParam(':review_text', $reviewText, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $message = "Review Submitted Successfully!";
                } else {
                    die("Database error: " . print_r($stmt->errorInfo(), true));
                }
            }
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Reviews - Mind and Motion</title>
  <link rel="stylesheet" href="reviews.css">

  <!-- If you want to keep select2 CSS/JS links -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>

  <!-- Consolidated CSS -->
  <style>
    /* Global box-sizing reset for better layout control */
    * {
      box-sizing: border-box;
    }

    /* Basic page reset */
    body, html {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
    }

    /* Modal Styling */
    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      width: 400px;
      max-width: 90%;
      background-color: #fff;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      font-family: Arial, sans-serif;
    }
    .modal-header, .modal-body, .modal-footer {
      padding: 15px;
    }
    .modal-header {
      border-bottom: 1px solid #ccc;
      position: relative;
    }
    .modal-header h2 {
      margin: 0;
      font-size: 18px;
    }
    .close {
      position: absolute;
      right: 15px;
      top: 15px;
      cursor: pointer;
      font-size: 18px;
      font-weight: bold;
      border: none;
      background: none;
    }
    .modal-body {
      margin-top: 10px;
    }
    .modal-footer {
      border-top: 1px solid #ccc;
      text-align: right;
    }
    .modal-footer button {
      background-color: #007bff;
      color: #fff;
      border: none;
      padding: 8px 16px;
      cursor: pointer;
      border-radius: 4px;
    }
    .modal-footer button:hover {
      background-color: #0056b3;
    }


  </style>
</head>
<body>

<?php if (!empty($message)): ?>
 
  <div id="reviewModal" class="modal">
    <div class="modal-header">
      <h2>Review Notification</h2>
      <button type="button" class="close" id="modalClose">&times;</button>
    </div>
    <div class="modal-body">
      <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <div class="modal-footer">
      <button type="button" id="modalCloseButton">Close</button>
    </div>
  </div>
  <script>
    var reviewModal = document.getElementById('reviewModal');
    reviewModal.style.display = 'block';

    var modalClose = document.getElementById('modalClose');
    var modalCloseButton = document.getElementById('modalCloseButton');

    function closeModalAndRedirect() {
      window.location.href = 'index.php';
    }
    if (modalClose) {
      modalClose.addEventListener('click', closeModalAndRedirect);
    }
    if (modalCloseButton) {
      modalCloseButton.addEventListener('click', closeModalAndRedirect);
    }
  </script>
<?php endif; ?>

<div id="reviewContent" class="container">
  <div class="leftSide">
    <?php if ($is_logged_in && $selectedProduct): ?>
      <form id="reviewForm" method="POST">
        <!-- Display "Product Name" label and read-only text field -->
        <div class="form-group">
          <label for="productName">Product Name:</label>
          <input 
            type="text" 
            id="productName" 
            value="<?= htmlspecialchars($selectedProduct['product_name']); ?>" 
            readonly
          >
        </div>

   
        <input 
          type="hidden" 
          name="Product_id" 
          value="<?= htmlspecialchars($selectedProduct['product_id']); ?>"
        >

        <div class="form-group">
          <label>Rating:</label>
          <div class="star-rating">
            <!-- 1 to 5, left-to-right, but with direction:rtl so the hover color flows leftward -->
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" name="Rating" value="<?= $i ?>" id="star<?= $i ?>" required>
              <label for="star<?= $i ?>">★</label>
            <?php endfor; ?>
          </div>
        </div>

        <div class="form-group">
          <label for="desc-txtArea">Review:</label>
          <textarea 
            id="desc-txtArea" 
            name="Review_text" 
            placeholder="Write your review here..." 
            rows="3" 
            maxlength="500" 
            required
          ></textarea>
          <p id="charCount">0/500</p>
        </div>

        <button type="submit" class="btn">Submit Review</button>
      </form>
    <?php elseif ($is_logged_in && empty($selectedProduct)): ?>
      <p>No valid product selected. Please navigate to a product from your purchase history.</p>
    <?php else: ?>
      <p>Please log in or purchase a product to leave a review.</p>
    <?php endif; ?>
  </div>

  <div class="rightSide">
    <?php if ($selectedProduct): ?>
      <img 
        src="<?= htmlspecialchars($selectedProduct['product_image']); ?>" 
        alt="<?= htmlspecialchars($selectedProduct['product_name']); ?>" 
        loading="lazy"
      >
    <?php else: ?>
      <img 
        src="images/placeholder.jpg" 
        alt="Selected Product Image" 
        id="reviewSelectedProduct-image" 
        loading="lazy"
      >
    <?php endif; ?>
  </div>
</div>

<script>

  document.getElementById("desc-txtArea").addEventListener("input", function() {
    const maxLength = 500;
    document.getElementById("charCount").innerText = `${this.value.length}/${maxLength}`;
  });
</script>

</body>
</html>

<?php include 'footer.php'; ?>
