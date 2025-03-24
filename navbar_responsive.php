<?php
session_start();
?>
<!-- Blue Top Strip -->
<div class="top-strip"></div>
<link rel="stylesheet" href="homestyle_responsive.css">

<div class="nav-container">
    <div class="logo">
        <a href="index.php">
         
            <img id="logo" src="images/Mind and Motion Logo.png" alt="Mind & Motion Logo">
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
        <a href="ContactUs.php">Contact</a>
        <?php if (isset($_SESSION['user'])): ?>
            <div class="dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <img id="userIcon" src="images/user.png" width="20" height="20" alt="User Icon">
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="profile.php">Profile</a>
                    <a class="dropdown-item" href="logout.php">Sign Out</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>

    <div class="nav-controls">
        <!-- Dark Mode Toggle for Desktop -->
        <button id="theme-toggle" class="theme-btn">🌙</button>
        <div class="basket">
            <a href="Basket.php">
                <img src="images/basket.png" alt="Basket Icon">
            </a>
        </div>
        <form action="/search.php" class="search-bar">
            <input name="query" type="text" placeholder="Search" aria-label="Search">
            <img src="images/search.png" alt="Search Icon" class="search-icon">
        </form>
    </div>

    <!-- Hamburger Menu Icon (visible on mobile) -->
    <div class="hamburger-menu" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>

<!-- Mobile Menu (overlaying content) -->
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
    <a href="ContactUs.php">Contact</a>
    <?php if (isset($_SESSION['user'])): ?>
        <div class="dropdown">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <img id="userIconMobile" src="images/user.png" width="20" height="20" alt="User Icon">
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="profile.php">Profile</a>
                <a class="dropdown-item" href="logout.php">Sign Out</a>
            </div>
        </div>
    <?php else: ?>
        <a href="login.php">Login</a>
    <?php endif; ?>
    <div class="basket">
        <a href="Basket.php">
            <img src="images/basket.png" alt="Basket Icon">
        </a>
    </div>
    <form action="/search.php" class="search-bar">
        <input name="query" type="text" placeholder="Search" aria-label="Search">
        <img src="images/search.png" alt="Search Icon" class="search-icon">
    </form>

    <button id="theme-toggle-mobile" class="theme-btn">🌙</button>
</div>

<script>
function toggleMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('active');
}

window.addEventListener('resize', function() {
    const menu = document.getElementById('mobileMenu');
    if (window.innerWidth > 809) {
        menu.classList.remove('active');
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const themeToggle = document.getElementById("theme-toggle");
    const themeToggleMobile = document.getElementById("theme-toggle-mobile");
    const currentTheme = localStorage.getItem("theme") || "dark";
    document.documentElement.setAttribute("data-theme", currentTheme);
    updateTheme(currentTheme);

    themeToggle.addEventListener("click", () => {
        let newTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
        document.documentElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);
        updateTheme(newTheme);
    });
    themeToggleMobile.addEventListener("click", () => {
        let newTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
        document.documentElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);
        updateTheme(newTheme);
    });

    function updateTheme(theme) {
        if (theme === "dark") {
            document.getElementById("logo").src = "images/dark-logo.png";
            if (document.getElementById("userIcon")) {
                document.getElementById("userIcon").src = "images/userDarkMode.png";
            }
            if (document.getElementById("userIconMobile")) {
                document.getElementById("userIconMobile").src = "images/userDarkMode.png";
            }
            themeToggle.innerHTML = "☀️";
            themeToggleMobile.innerHTML = "☀️";
            themeToggle.style.filter = "none";
            themeToggleMobile.style.filter = "none";
        } else {
            document.getElementById("logo").src = "images/Mind and Motion Logo.png";
            if (document.getElementById("userIcon")) {
                document.getElementById("userIcon").src = "images/user.png";
            }
            if (document.getElementById("userIconMobile")) {
                document.getElementById("userIconMobile").src = "images/user.png";
            }
            themeToggle.innerHTML = "🌙";
            themeToggleMobile.innerHTML = "🌙";
            themeToggle.style.filter = "invert(50%) sepia(10%) saturate(300%) hue-rotate(220deg) brightness(90%) contrast(90%)";
            themeToggleMobile.style.filter = "invert(50%) sepia(10%) saturate(300%) hue-rotate(220deg) brightness(90%) contrast(90%)";
        }
    }
});
</script>
