<?php
session_start();
include 'navbar.php';
require_once("PHPHost.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$is_logged_in = isset($_SESSION['uid']);
$products = [];
$message = '';

if ($is_logged_in) {
    $user_id = $_SESSION['uid'];

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

        if (empty($products)) {
            $message = "You need to purchase a product before leaving a review.";
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
} else {
    echo "<script>alert('Please log in to submit a review.'); window.location.href = 'login.php';</script>";
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $is_logged_in) {
    $productId = $_POST['Product_id'] ?? null;
    $rating = $_POST['Rating'] ?? null;
    $reviewText = trim($_POST['Review_text'] ?? '');

    if (!$productId || !$rating || empty($reviewText) || $rating < 1 || $rating > 5) {
        echo "<script>alert('Invalid review data.'); window.history.back();</script>";
        exit;
    }

    try {
       
        $checkQuery = "SELECT users_review_id FROM users_review WHERE user_id = :user_id AND order_prod_id = :product_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $checkStmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            echo "<script>alert('You have already submitted a review for this product.'); window.history.back();</script>";
            exit;
        }

     
        $insertQuery = "INSERT INTO users_review (user_id, order_prod_id, rating, comment) 
                        VALUES (:user_id, :product_id, :rating, :review_text)";
        $stmt = $db->prepare($insertQuery);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':review_text', $reviewText, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo "<script>alert('Review Submitted Successfully!'); window.location.href = 'reviews.php';</script>";
        } else {
            die("Database error: " . print_r($stmt->errorInfo(), true)); // Debugging error message
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - Mind and Motion</title>
    <link rel="stylesheet" href="reviews.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
</head>
<body>

<div id="reviewContent" class="container">
    <div class="leftSide">
        <?php if ($is_logged_in && !empty($products)): ?>
        <form id="reviewForm" method="POST">
            <div class="form-group">
                <label for="ProductId">Product Name:</label>
                <select id="ProductId" name="Product_id" required>
                    <?php foreach ($products as $product): ?>
                    <option value="<?= htmlspecialchars($product['product_id']); ?>"
                            data-image="<?= htmlspecialchars($product['product_image']); ?>">
                        <?= htmlspecialchars($product['product_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Rating:</label>
                <div class="star-rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="Rating" value="<?= $i ?>" id="star<?= $i ?>" required>
                        <label for="star<?= $i ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="desc-txtArea">Review:</label>
                <textarea id="desc-txtArea" name="Review_text" placeholder="Write your review here..." rows="3" maxlength="500" required></textarea>
                <p id="charCount">0/500</p>
            </div>
            <button type="submit" class="btn">Submit Review</button>
        </form>
        <?php else: ?>
        <p><?= $message; ?></p>
        <?php endif; ?>
    </div>
    <div class="rightSide">
        <img src="images/placeholder.jpg" alt="Selected Product Image" id="reviewSelectedProduct-image" loading="lazy">
    </div>
</div>

<script>
$(document).ready(function() {
 
    $('#ProductId').select2({
        placeholder: "Type or select an option",
        allowClear: true,
        minimumInputLength: 0 
    });


    $('#ProductId').on('change', function() {
        updateProductImage();
    });

   
    function updateProductImage() {
        var select = document.getElementById('ProductId');
        if (select.selectedIndex !== -1) {  
            var selectedOption = select.options[select.selectedIndex];
            var imageSrc = selectedOption.getAttribute('data-image');
            document.getElementById('reviewSelectedProduct-image').src = imageSrc || 'images/placeholder.jpg';  // Fallback to default image if not available
        }
    }


    const reviewText = document.getElementById("desc-txtArea");
    const charCount = document.getElementById("charCount");

    reviewText.addEventListener("input", function() {
        const maxLength = 500;
        charCount.innerText = `${this.value.length}/${maxLength}`;  
    });

   
    updateProductImage();
});
</script>
</body>
</html>
<?php include 'footer.php'; ?>