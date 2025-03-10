<?php
session_start();
?>

<div class="top-strip"></div>
<link rel="stylesheet" href="homestyle.css">

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
                    <img id="userIcon" src="images/user.png" width="20px" height="20px">
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
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const themeToggle = document.getElementById("theme-toggle");
    const logo = document.getElementById("logo");
    var userIcon = document.getElementById("userIcon");
    const currentTheme = localStorage.getItem("theme") || "dark";

    document.documentElement.setAttribute("data-theme", currentTheme);
    updateTheme(currentTheme);

    themeToggle.addEventListener("click", () => {
        let newTheme = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
        document.documentElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);
        updateTheme(newTheme);
    });

    function updateTheme(theme) {
        
        if (theme === "dark") {
            logo.src = "images/dark-logo.png";  
            if( userIcon !== null) { userIcon.src = "images/userDarkMode.png" }
            themeToggle.innerHTML = "☀️";
        } else {
            logo.src = "images/Mind and Motion Logo.png"; 
            if( userIcon !== null) { userIcon.src = "images/user.png" }
            themeToggle.innerHTML = "🌙";
        }
    }
});
</script>
