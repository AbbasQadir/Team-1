<?php
session_start(); // Start the session to track logged-in users

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - Mind and Motion</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="reviews.css">

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Select2 CSS & JS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

</head>
<body>

<!-- Alert for Not Logged-In Users -->
<?php if (!$is_logged_in): ?>
<div class="alert" id="alertBox">
    <p><strong>Login Required:</strong> You must <a href="login.php">log in</a> to submit a review.</p>
    <button class="close-btn btn" onclick="closeAlert()">✖</button>
</div>
<?php endif; ?>

<!-- Review Form -->
<div id="reviewContent" class="container">
    <div class="leftSide">
        <form id="reviewForm" action="submit_review.php" method="POST">
            <input type="hidden" id="userId" name="userId" value="<?php echo $is_logged_in ? $_SESSION['user_id'] : ''; ?>">

            <!-- Product Selection -->
            <div class="form-group">
                <label for="ProductId">Product Name:</label>
                <select id="ProductId" name="Product_id" onchange="updateProductImage()" required>
                    <option value="1" data-image="placeholder1.jpg">Product 1</option>
                    <option value="2" data-image="placeholder2.jpg">Product 2</option>
                    <option value="3" data-image="placeholder3.jpg">Product 3</option>
                </select>
            </div>

            <!-- Star Rating -->
            <div class="form-group">
                <label>Rating:</label>
                <div class="star-rating">
                    <input type="radio" name="Rating" value="5" id="star5"><label for="star5">★</label>
                    <input type="radio" name="Rating" value="4" id="star4"><label for="star4">★</label>
                    <input type="radio" name="Rating" value="3" id="star3"><label for="star3">★</label>
                    <input type="radio" name="Rating" value="2" id="star2"><label for="star2">★</label>
                    <input type="radio" name="Rating" value="1" id="star1"><label for="star1">★</label>
                </div>
            </div>

            <!-- Review Input -->
            <div class="form-group">
                <label for="desc-txtArea">Review:</label>
                <textarea id="desc-txtArea" placeholder="Write your review here..." rows="3" name="Review_text" maxlength="500" required></textarea>
                <p id="charCount">0/500</p>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn">Submit Review</button>
        </form>
    </div>

    <!-- Product Image Display -->
    <div class="rightSide">
        <img src="images/fitnesss.webp" alt="Selected Product Image" id="reviewSelectedProduct-image" loading="lazy" onclick="enlargeImage()">
    </div>
</div>

<!-- Display Recent Reviews -->
<div id="recentReviews">
    <h2 class="text-center">Recent Reviews</h2>
    <ul id="reviewsList">
        <?php include 'fetch_reviews.php'; ?>
    </ul>
</div>

<!-- JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    loadSavedReview();
    loadRecentReviews();

    // Character counter and enforce max length
    const reviewText = document.getElementById("desc-txtArea");
    const charCount = document.getElementById("charCount");

    reviewText.addEventListener("input", function () {
        if (this.value.length > 500) {
            this.value = this.value.substring(0, 500); // Truncate extra characters
        }
        charCount.innerText = `${this.value.length}/500`;
        localStorage.setItem("savedReview", this.value);
    });

    // Prevent multiple submissions
    document.getElementById("reviewForm").addEventListener("submit", function(event) {
        event.preventDefault();
        const submitButton = document.querySelector('.btn');
        submitButton.disabled = true;
        submitButton.innerText = "Submitting...";

        setTimeout(() => {
            alert("Review Submitted!");
            submitButton.disabled = false;
            submitButton.innerText = "Submit Review";
            localStorage.removeItem("savedReview");
        }, 2000);
    });
});

// Product images stored in an object 
const productImages = {
    "Product 1": "placeholder1.jpg",
    "Product 2": "placeholder2.jpg",
    "Product 3": "placeholder3.jpg"
};

function updateProductImage() {
    const inputElement = document.getElementById('ProductId');
    const inputValue = inputElement.value.trim().toLowerCase(); // Normalize input
    const imageElement = document.getElementById('reviewSelectedProduct-image');

    // Match input with predefined product images
    const matchedProduct = Object.keys(productImages).find(product =>
        product.toLowerCase().includes(inputValue) // Matches even partial input
    );

    if (matchedProduct) {
        imageElement.src = productImages[matchedProduct]; // Update image source
    } else {
        imageElement.src = "placeholder.jpg"; // Default image if no match found
    }
}

// Close alert
function closeAlert() {
    document.getElementById('alertBox').style.display = 'none';
}

// Enlarge image
function enlargeImage() {
    const image = document.getElementById("reviewSelectedProduct-image");
    window.open(image.src, "_blank");
}

// Load saved review
function loadSavedReview() {
    const savedReview = localStorage.getItem("savedReview");
    if (savedReview) {
        document.getElementById("desc-txtArea").value = savedReview;
        document.getElementById("charCount").innerText = `${savedReview.length}/500`;
    }
}

// Load recent reviews
function loadRecentReviews() {
    document.getElementById("reviewsList").innerHTML = "<li>No reviews yet. Be the first to leave one!</li>";
}

$(document).ready(function() {
    $('#ProductId').select2({
        placeholder: "Select or Type a Product",
        allowClear: true
    });
});
</script>

</body>
</html>
