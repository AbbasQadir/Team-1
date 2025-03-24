<?php
require_once "PHPHost.php";
include 'navbar_responsive.php';



function fetchProduct($db, $category, $name) {
    $query = $db->prepare("SELECT * FROM products WHERE item_category=:category AND item_name=:name LIMIT 1");
    $query->bindParam(":category", $category);
    $query->bindParam(":name", $name);
    $query->execute();
    return $query->fetch(PDO::FETCH_ASSOC);
}

// Fetch products
$product1 = fetchProduct($db, "Fitness Equipment", "High Performance Treadmill");
$product2 = fetchProduct($db, "Books", "The Power of Discipline");
$product3 = fetchProduct($db, "Technology", "Smart Scales with 16 Measurement Modes");
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mind & Motion</title>
    <!-- External Stylesheets -->
    <link rel="stylesheet" href="homestyle_responsive.css">
    <link rel="stylesheet" href="css/bootstrap.min.css"
    <!-- External JavaScript -->
    <script src="js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
 
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Mind & Motion</h1>
            <p>Your journey to a better mind and body starts here. Find out more about Mind and Motion.</p>
           <a href="about.php" class="cta-button">Discover More</a>
        </div>
    </section>

  
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

<!-- popular product Section -->
<section class="popular-products">
    <h2 class="section-title">Popular Products</h2>
    <div class="product-container">
        <!-- Product 1 -->
        <div class="product-card">
            <img src="<?php echo htmlspecialchars($product1['image'] ?? 'images/placeholder.png'); ?>" 
                 alt="<?php echo htmlspecialchars($product1['item_name'] ?? 'Product Image'); ?>" 
                 onerror="this.src='images/placeholder.png';">
            <div class="product-details">
                <p><?php echo htmlspecialchars($product1['item_name'] ?? 'Product Name'); ?></p>
                <p>
                    <span class="price-now">£<?php echo htmlspecialchars($product1['item_price'] ?? '0.00'); ?></span>
                </p>
                <a href="specificProduct.php?id=<?php echo htmlspecialchars($product1['product_id'] ?? '#'); ?>">
                    <button class="add-to-basket">View Product</button>
                </a>
            </div>
        </div>

    
        <div class="product-card">
            <img src="<?php echo htmlspecialchars($product2['image'] ?? 'images/placeholder.png'); ?>" 
                 alt="<?php echo htmlspecialchars($product2['item_name'] ?? 'Product Image'); ?>" 
                 onerror="this.src='images/placeholder.png';">
            <div class="product-details">
                <p><?php echo htmlspecialchars($product2['item_name'] ?? 'Product Name'); ?></p>
                <p>
                    <span class="price-now">£<?php echo htmlspecialchars($product2['item_price'] ?? '0.00'); ?></span>
                </p>
                <a href="specificProduct.php?id=<?php echo htmlspecialchars($product2['product_id'] ?? '#'); ?>">
                    <button class="add-to-basket">View Product</button>
                </a>
            </div>
        </div>


        <div class="product-card">
            <img src="<?php echo htmlspecialchars($product3['image'] ?? 'images/placeholder.png'); ?>" 
                 alt="<?php echo htmlspecialchars($product3['item_name'] ?? 'Product Image'); ?>" 
                 onerror="this.src='images/placeholder.png';">
            <div class="product-details">
                <p><?php echo htmlspecialchars($product3['item_name'] ?? 'Product Name'); ?></p>
                <p>
                    <span class="price-now">£<?php echo htmlspecialchars($product3['item_price'] ?? '0.00'); ?></span>
                </p>
                <a href="specificProduct.php?id=<?php echo htmlspecialchars($product3['product_id'] ?? '#'); ?>">
                    <button class="add-to-basket">View Product</button>
                </a>
            </div>
        </div>
    </div>
</section>



    <section class="customer-reviews">
        <h2 class="section-title">What Our Customers Say</h2>
        <div class="review-container">
            <!-- Review 1 -->
            <div class="review-card">
                <img src="images/customer1.jpeg" alt="Customer 1" class="customer-image">
                <div class="review-details">
                    <h3>John Doe</h3>
                    <p>"Mind & Motion has been a game-changer for my fitness routine. The quality of their products is unmatched!"</p>
                </div>
            </div>
    
   
            <div class="review-card">
                <img src="images/customer2.jpeg" alt="Customer 2" class="customer-image">
                <div class="review-details">
                    <h3>Jane Smith</h3>
                    <p>"I love the variety of wellness products available. Their customer service is top-notch as well!"</p>
                </div>
            </div>
    
       
            <div class="review-card">
                <img src="images/customer3.jpeg" alt="Customer 3" class="customer-image">
                <div class="review-details">
                    <h3>Emily Johnson</h3>
                    <p>"Fast delivery, amazing products, and great prices. I’ll definitely be shopping here again!"</p>
                </div>
            </div>
        </div>
    </section>


    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Carousel functionality
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
                autoSlideInterval = setInterval(() => {
                    moveToNextSlide();
                }, 5000);
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

            dropdownToggle.addEventListener("click", (event) => {
                event.preventDefault();
                dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
            });

            document.addEventListener("click", (event) => {
                if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.style.display = "none";
                }
            });
        });

    </script>
     <?php include 'footer.php'; ?>
</body>
</html>
