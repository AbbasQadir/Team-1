<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Website</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js" defer></script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">E-Shop</div>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="#">Products</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
            <li>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>

    <!-- Product Section -->
    <div class="products-section">
        <h2>Featured Products</h2>
        <div class="product-grid">
            <div class="product-card">
                <img src="https://via.placeholder.com/150" alt="Product 1">
                <h3>Product 1</h3>
                <p>$25.99</p>
                <button class="add-to-cart">Add to Cart</button>
            </div>
            <div class="product-card">
                <img src="https://via.placeholder.com/150" alt="Product 2">
                <h3>Product 2</h3>
                <p>$30.99</p>
                <button class="add-to-cart">Add to Cart</button>
            </div>
            <div class="product-card">
                <img src="https://via.placeholder.com/150" alt="Product 3">
                <h3>Product 3</h3>
                <p>$15.99</p>
                <button class="add-to-cart">Add to Cart</button>
            </div>
        </div>
    </div>
</body>
</html>

