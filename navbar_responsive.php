
<?php
session_start();
?>
<!-- Blue Strip at the Top -->
<div class="top-strip"></div>
<link rel="stylesheet" href="homestyle_responsive.css">
<!-- Navigation Section -->
<div class="nav-container">
    <div class="logo">
        <a href="index.php">
            <img src="images/Mind and Motion Logo.png" alt="Mind & Motion Logo">
        </a>
    </div>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <div class="dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Products</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="bookspage.php">Books</a>
                <a class="dropdown-item" href="fitnesspage.php">Fitness Equipment</a>
                <a class="dropdown-item" href="gymwearpage.php">Gym Wear</a>
                <a class="dropdown-item" href="technologypage.php">Technology</a>
                <a class="dropdown-item" href="supplementspage.php">Supplements</a>
            </div>
        </div>
        <a href="about.php">About</a>
        <a href="ContactUs.php">Contact Us</a>
 
    <?php if (isset($_SESSION['user'])): ?>
        <div class="dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="images/user.png" width="20px" height="20px">
            </a>
            <div class="dropdown-menu">    
                <a class="dropdown-item" href="profile.php">Profile</a>
                <a class="dropdown-item" href="logout.php">Sign Out</a> 
            </div>
        </div>
        <?php else: ?>
            <a href="login.php">Log In</a>
        <?php endif; ?>
    </div>
        
    <!-- Navigation Controls -->
    <div class="nav-controls">
        <div class="basket">
            <a href="Basket.php">
                <img src="images/basket.png" alt="Basket Icon">
            </a>
            <span>(0)</span>
        </div>
        <form action="/search.php" class="search-bar">
            <input name="query" type="text" placeholder="Search" aria-label="Search">
            <img src="images/search.png" alt="Search Icon" class="search-icon">
        </form>
    </div>

    <!-- Hamburger Menu Icon -->
    <div class="hamburger-menu" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <a href="index.php">Home</a>
    <div class="dropdown">
        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Products</a>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="bookspage.php">Books</a>
            <a class="dropdown-item" href="fitnesspage.php">Fitness Equipment</a>
            <a class="dropdown-item" href="gymwearpage.php">Gym Wear</a>
            <a class="dropdown-item" href="technologypage.php">Technology</a>
            <a class="dropdown-item" href="supplementspage.php">Supplements</a>
        </div>
    </div>
    <a href="about.php">About</a>
    <a href="ContactUs.php">Contact Us</a>
    <?php if (isset($_SESSION['user'])): ?>
        <div class="dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="images/user.png" width="20px" height="20px">
            </a>
            <div class="dropdown-menu">    
                <a class="dropdown-item" href="profile.php">Profile</a>
                <a class="dropdown-item" href="logout.php">Sign Out</a> 
            </div>
        </div>
    <?php else: ?>
        <a href="login.php">Log In</a>
    <?php endif; ?>
    <div class="basket">
        <a href="Basket.php">
            <img src="images/basket.png" alt="Basket Icon">
        </a>
        <span>(0)</span>
    </div>
    <form action="/search.php" class="search-bar">
        <input name="query" type="text" placeholder="Search" aria-label="Search">
        <img src="images/search.png" alt="Search Icon" class="search-icon">
    </form>
</div>

<script>
function toggleMenu() {
    var menu = document.getElementById('mobileMenu');
    menu.classList.toggle('active');
}

window.addEventListener('resize', function() {
    var menu = document.getElementById('mobileMenu');
    if (window.innerWidth > 809) {
        menu.classList.remove('active');
    }
});
</script>
