<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar with Bootstrap</title>
    <!-- Add Bootstrap CSS via CDN -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-navy">
        <a class="navbar-brand" href="#">
            <img src="logo.png" alt="Logo">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">  <!-- Align links to the left -->
                <!-- Products Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#">Products</a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="#">Books</a>
                        <a class="dropdown-item" href="#">Fitness Equipment</a>
                        <a class="dropdown-item" href="#">Gym Wear</a>
                        <a class="dropdown-item" href="#">Technology</a>
                        <a class="dropdown-item" href="#">Supplements</a>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Icons Container (profile, cart, search) -->
        <div class="d-flex align-items-center ml-auto">
            <!-- Profile Dropdown -->
            <div class="profile-container nav-item dropdown">
                <a class="nav-link" href="#" id="profileDropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <img src="profile icon.png" alt="Profile Icon" id="profile-icon">
                </a>
                <div class="dropdown-menu" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="#">Login</a>
                    <a class="dropdown-item" href="#">Signup</a>
                </div>
            </div>

            <!-- Cart Container -->
            <div class="cart-container ml-3">
                <a href="#"><img src="cart symbol.png" id="cart" alt="Cart Icon"></a>
            </div>

            <!-- Search Container -->
            <div class="search-container ml-3">
                <a href="#"><img src="search icon.png" id="search" alt="Search Icon"></a>
            </div>
        </div>
    </nav>

<!-- Footer Section -->
<footer class="bg-dark text-center text-lg-start fixed-bottom">
    <div class="container position-relative">
        <div class="row">
            <!-- About Us Section -->
            <div class="col-md-6">
                <a href="#">About us</a>
            </div>

            <!-- Contact Section -->
            <div class="col-md-6">
                <a href="#">Contact</a>
            </div>
        </div>

        <!-- Footer Logo and Text in Bottom Left -->
        <div class="footer-logo-container">
            <img src="logo.png" alt="Footer Logo" class="footer-logo" id="logo">
            <p>© Mind and Motion</p>
        </div>
    </div>
</footer>


    <!-- Add Bootstrap JS and Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zyCU6mrxA6f4EJ5cltvjxjp6nE1MXA9G16p9b3F5" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0vp2VSsV1AsuyHk5tXnxutM3HX8evoO6jC0TDAKoaH6+z/JF" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0vp2VSsV1AsuyHk5tXnxutM3HX8evoO6jC0TDAKoaH6+z/JF" crossorigin="anonymous"></script>

    <!-- Custom Styles -->
    <style>
        .bg-navy {
            background-color: cadetblue !important;
        }

        /* Ensure text is white for visibility */
        .navbar .navbar-nav .nav-link {
            color: white !important; /* Force text to be white */
        }

        .navbar .navbar-nav .nav-link:hover {
            color: #f8f9fa !important; /* Change color on hover */
        }

        .navbar .navbar-brand img {
            height: 60px;
            object-fit: cover;
        }

        .d-flex {
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        /* Spacing between the icons */
        .ml-3 {
            margin-left: 1rem;
        }

        /* Profile Icon */
        #profile-icon {
            height: 30px;
            width: 30px;
            object-fit: contain;
        }

        /* Cart Icon */
        #cart {
            height: 30px;
            object-fit: contain;
        }

        /* Search Icon */
        #search {
            height: 30px;
            object-fit: contain;
        }

        /* Keyframes animation for dropdown */
        @keyframes dropdownFadeIn {
            0% {
                opacity: 0;
                visibility: hidden;
            }
            100% {
                opacity: 1;
                visibility: visible;
            }
        }

        /* Apply the animation to the dropdown */
        .nav-item.dropdown:hover .dropdown-menu {
            animation: dropdownFadeIn 0.5s ease forwards; 
            animation-delay: 0.3s; 
            display: block;
            visibility: visible;  
        }

        /* Ensure dropdown menu starts hidden */
        .dropdown-menu {
            display: none;
            position: absolute;
            visibility: hidden;
            opacity: 0;
        }

        /* Display dropdown when hovered */
        .nav-item.dropdown:hover .dropdown-menu {
            display: block;
        }

        /* Footer Styles */
        footer {
        background-color: #343a40;
        color: white;
        
            
        }

      

        footer a{
            color: white;
        }

        footer a:hover{
            color: white
            ;
        }

        #logo{
            height: 60px;
        }

        
    </style>





</body>
</html>
