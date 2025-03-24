<?php
session_start();
require_once "PHPHost.php"; 
include 'navbar.php';

function fetchProduct($db, $category, $name) {
    $query = $db->prepare("SELECT * FROM products WHERE item_category = :category AND item_name = :name LIMIT 1");
    $query->bindParam(":category", $category);
    $query->bindParam(":name", $name);
    $query->execute();
    return $query->fetch(PDO::FETCH_ASSOC);
}

$product1 = fetchProduct($db, "Fitness Equipment", "High Performance Treadmill");
$product2 = fetchProduct($db, "Books", "The Power of Discipline");
$product3 = fetchProduct($db, "Technology", "Smart Scales with 16 Measurement Modes");


$query = "
    SELECT r.review_text, r.created_at, r.rating,
           u.first_name, u.last_name
      FROM web_review r
      JOIN users u ON r.user_id = u.user_id
  ORDER BY r.created_at DESC
     LIMIT 6
";
$stmt = $db->prepare($query);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mind & Motion</title>
    <!-- External Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="homestyle.css">
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Mind & Motion</h1>
            <p>Your journey to a better mind and body starts here. Find out more about Mind and Motion.</p>
            <a href="about.php" class="cta-button">Discover More</a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <h2>Why Choose Us?</h2>
        <div class="features-container">
            <div class="feature">
                <img src="images/free-delivery-icon.png" alt="Free Shipping Icon">
                <p>Free Shipping</p>
            </div>
            <div class="feature">
                <img src="images/Trusted-product-icon.png" alt="Trusted Products Icon">
                <p>Trusted Wellness Products</p>
            </div>
            <div class="feature">
                <img src="images/secure-payment.png" alt="Secure Payments Icon">
                <p>Secure Payments</p>
            </div>
        </div>
    </section>

    <!-- Categories Carousel Section -->
    <div class="carousel">
        <div class="carousel-title">Explore Our Categories</div>
        <button class="carousel-arrow left" aria-label="Previous Slide">❮</button>
        <div class="carousel-container">
            <div class="carousel-track">
                <div class="carousel-item"><img src="images/fitness.webp" alt="Fitness Equipment"></div>
                <div class="carousel-item"><img src="images/Supplemments1.png" alt="Nutritional Supplements"></div>
                <div class="carousel-item"><img src="images/Untitled design (2).png" alt="Mindfulness Tools"></div>
                <div class="carousel-item"><img src="images/Gym-wear.jpg" alt="Gym Wear"></div>
                <div class="carousel-item"><img src="images/Tech-for-wellness.png" alt="Tech for Wellness"></div>
            </div>
        </div>
        <button class="carousel-arrow right" aria-label="Next Slide">❯</button>
        <div class="pagination-dots">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </div>

    <!-- Popular Products Section -->
    <section class="popular-products">
        <h2 class="section-title">Popular Products</h2>
        <div class="product-container">
            <!-- Product 1 -->
            <div class="product-card">
                <img 
                    src="<?php echo htmlspecialchars($product1['image'] ?? 'images/placeholder.png'); ?>" 
                    alt="<?php echo htmlspecialchars($product1['item_name'] ?? 'Product Image'); ?>"
                    onerror="this.src='images/placeholder.png';"
                >
                <div class="product-details">
                    <p><?php echo htmlspecialchars($product1['item_name'] ?? 'Product Name'); ?></p>
                    <p><span class="price-now">£<?php echo htmlspecialchars($product1['item_price'] ?? '0.00'); ?></span></p>
                    <a href="specificProduct.php?id=<?php echo htmlspecialchars($product1['product_id'] ?? '#'); ?>">
                        <button class="add-to-basket">View Product</button>
                    </a>
                </div>
            </div>

            <!-- Product 2 -->
            <div class="product-card">
                <img 
                    src="<?php echo htmlspecialchars($product2['image'] ?? 'images/placeholder.png'); ?>" 
                    alt="<?php echo htmlspecialchars($product2['item_name'] ?? 'Product Image'); ?>"
                    onerror="this.src='images/placeholder.png';"
                >
                <div class="product-details">
                    <p><?php echo htmlspecialchars($product2['item_name'] ?? 'Product Name'); ?></p>
                    <p><span class="price-now">£<?php echo htmlspecialchars($product2['item_price'] ?? '0.00'); ?></span></p>
                    <a href="specificProduct.php?id=<?php echo htmlspecialchars($product2['product_id'] ?? '#'); ?>">
                        <button class="add-to-basket">View Product</button>
                    </a>
                </div>
            </div>

            <!-- Product 3 -->
            <div class="product-card">
                <img 
                    src="<?php echo htmlspecialchars($product3['image'] ?? 'images/placeholder.png'); ?>" 
                    alt="<?php echo htmlspecialchars($product3['item_name'] ?? 'Product Image'); ?>"
                    onerror="this.src='images/placeholder.png';"
                >
                <div class="product-details">
                    <p><?php echo htmlspecialchars($product3['item_name'] ?? 'Product Name'); ?></p>
                    <p><span class="price-now">£<?php echo htmlspecialchars($product3['item_price'] ?? '0.00'); ?></span></p>
                    <a href="specificProduct.php?id=<?php echo htmlspecialchars($product3['product_id'] ?? '#'); ?>">
                        <button class="add-to-basket">View Product</button>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Customer Reviews Section -->
    <section class="customer-reviews">
        <h2 class="section-title">What Our Customers Say</h2>
        <div class="review-carousel">
            <button class="review-carousel-arrow left" aria-label="Previous Reviews">❮</button>
            <div class="review-carousel-container">
                <div class="review-carousel-track">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-details">
                                    <h3>
                                        <?php
                                         if (!empty($review['first_name']) || !empty($review['last_name'])) {
      
                                        $fullName = trim($review['first_name'] . ' ' . $review['last_name']);
                                        echo htmlspecialchars($fullName);
                                 } else {
                                            echo "Customer";
                                  }
                                        ?>
                                    </h3>
                                    <p>"<?php echo htmlspecialchars($review['review_text']); ?>"</p>
                                    <p class="star-rating">
                                        <?php
                                            $rating = (int)$review['rating'];
                                            echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                                        ?>
                                    </p>
                                    <small>Reviewed on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No reviews available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
            <button class="review-carousel-arrow right" aria-label="Next Reviews">❯</button>
                     <!-- "View More" Link Added Below the Review Carousel -->
        <div class="view-more">
            <a href="all_reviews.php">View More Reviews</a>
        </div>
    </section>
       

   
    <script src="js/bootstrap.bundle.min.js" defer></script>
    <script>
        // Category Carousel
        document.addEventListener("DOMContentLoaded", () => {
            const track = document.querySelector(".carousel-track");
            const dots = document.querySelectorAll(".dot");
            const leftArrow = document.querySelector(".carousel-arrow.left");
            const rightArrow = document.querySelector(".carousel-arrow.right");
            const items = document.querySelectorAll(".carousel-item");
            const totalSlides = items.length;
            let currentIndex = 0;
            let isTransitioning = false;
            const transitionDuration = 500;
            let autoSlideInterval;

            const updateCarousel = (manual = false) => {
                if (!manual) {
                    track.style.transition = `transform ${transitionDuration}ms ease-in-out`;
                }
                track.style.transform = `translateX(-${currentIndex * 100}%)`;
                dots.forEach((dot, i) => {
                    dot.classList.toggle("active", i === currentIndex);
                });
                if (!manual) {
                    isTransitioning = true;
                    setTimeout(() => {
                        isTransitioning = false;
                    }, transitionDuration);
                }
            };

            const moveToNextSlide = () => {
                if (!isTransitioning) {
                    currentIndex = (currentIndex + 1) % totalSlides;
                    updateCarousel();
                }
            };

            const moveToPreviousSlide = () => {
                if (!isTransitioning) {
                    currentIndex = currentIndex > 0 ? currentIndex - 1 : totalSlides - 1;
                    updateCarousel();
                }
            };

            const startAutoSlide = () => {
                clearInterval(autoSlideInterval);
                autoSlideInterval = setInterval(moveToNextSlide, 5000);
            };

            const stopAutoSlide = () => {
                clearInterval(autoSlideInterval);
            };

            leftArrow.addEventListener("click", () => {
                stopAutoSlide();
                moveToPreviousSlide();
                startAutoSlide();
            });

            rightArrow.addEventListener("click", () => {
                stopAutoSlide();
                moveToNextSlide();
                startAutoSlide();
            });

            dots.forEach((dot, i) => {
                dot.addEventListener("click", () => {
                    if (isTransitioning || currentIndex === i) return;
                    stopAutoSlide();
                    currentIndex = i;
                    updateCarousel(true);
                    startAutoSlide();
                });
            });

            const carousel = document.querySelector(".carousel");
            carousel.addEventListener("mouseenter", stopAutoSlide);
            carousel.addEventListener("mouseleave", startAutoSlide);

            updateCarousel(true);
            startAutoSlide();
        });

      
        document.addEventListener("DOMContentLoaded", () => {
            const basketCount = document.querySelector(".basket span");
            const buttons = document.querySelectorAll(".add-to-basket");
            let itemCount = 0;

            buttons.forEach((button) => {
                button.addEventListener("click", () => {
                    itemCount += 1;
                    basketCount.textContent = `(${itemCount})`;
                });
            });
        });

   
        document.addEventListener("DOMContentLoaded", () => {
            const dropdownToggle = document.querySelector(".dropdown-toggle");
            const dropdownMenu = document.querySelector(".dropdown-menu");

            if (dropdownToggle && dropdownMenu) {
                dropdownToggle.addEventListener("click", (event) => {
                    event.preventDefault();
                    dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
                });

                document.addEventListener("click", (event) => {
                    if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                        dropdownMenu.style.display = "none";
                    }
                });
            }
        });

       
        document.addEventListener("DOMContentLoaded", () => {
            const reviewTrack = document.querySelector(".review-carousel-track");
            const reviewLeftArrow = document.querySelector(".review-carousel-arrow.left");
            const reviewRightArrow = document.querySelector(".review-carousel-arrow.right");

            const totalReviews = document.querySelectorAll(".review-carousel-track .review-card").length;
           
            const reviewsPerPage = 3;
            const totalPages = Math.ceil(totalReviews / reviewsPerPage);
            let currentPage = 0;

            reviewRightArrow.addEventListener("click", () => {
                if (currentPage < totalPages - 1) {
                    currentPage++;
                    reviewTrack.style.transform = `translateX(-${currentPage * 100}%)`;
                }
            });

            reviewLeftArrow.addEventListener("click", () => {
                if (currentPage > 0) {
                    currentPage--;
                    reviewTrack.style.transform = `translateX(-${currentPage * 100}%)`;
                }
            });
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>

