<?php
include 'navbar.php'
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="fitnessstyle.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Equipment</title>
    
</head>
<body>
  
   <h1 id="fitnesstitle"> Fitness Equipment</h1>
   <label for="filter" class="filter">Filter results:</label>
   <select name="filters" id="filters"> 
    <option value="Popularity">Popularity</option>
    <option value="ascending">Price:low to high</option>
    <option value="descending">Price:high to low</option>
   </select>
   <div class="products">
    <div class="item1">
        <img src="images/weights.jpg" alt="product1">
        <a href="#">
        <div class="product-name">Adjustable Dumbbell Weight Set</div>
        <div class="product-price"> £25.99 </div>
    </a> 
    </div>
    <div class="item2">
        <img src="images/jumprope.jpg" alt="product2">
        <a href="#">
        <div class="product-name">Vector Jumping Rope - Black</div>
        <div class="product-price"> £12.00 </div>
    </a>
    </div>
    <div class="item3">
        <img src="images/treadmill.jpg" alt="product3">
        <a href="#">
        <div class="product-name">High Performance Treadmill</div>
        <div class="product-price"> £899.99 </div>
        </a>
    </div>
    <div class="item4">
        <img src="images/mat.jpg" alt="product4">
        <a href="#">
        <div class="product-name">Fitness Yoga Mat - Extra Thick</div>
        <div class="product-price"> £17.95 </div>
        </a>
    </div>
   
    <div class="item5">
        <a href="#">
        <img src="images/bench.jpg" alt="product5">
        <div class="product-name">Home Fitness Bench</div>
        <div class="product-price"> £259.99 </div>
        </a>
    </div>
   </div>

  
</body>
</html>