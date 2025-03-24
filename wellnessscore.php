<?php
// If you have PHP logic or database queries, put them here.
// Otherwise, you can remove the PHP tags entirely.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mind & Motion - Wellness Score Dashboard</title>
  <!-- Include Chart.js from CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Link to External CSS -->
  <link rel="stylesheet" href="wellness.css">
</head>
<body>
  <!-- HEADER -->
  <header class="dashboard-header">
    <div class="header-left">
      <!-- Replace 'logo.png' with your actual logo file -->
      <img src="logo.png" alt="Mind & Motion Logo" class="logo">
      <h1>Wellness Score Dashboard</h1>
    </div>
    <div class="header-right">
      <div class="user-profile">
        <!-- Replace 'avatar.png' with your actual avatar -->
        <img src="avatar.png" alt="User Avatar" class="avatar">
        <span class="username">Admin</span>
      </div>
    </div>
  </header>

  <!-- QUICK LINKS -->
  <section class="quick-links">
    <button class="quick-link-btn">Add New Product</button>
    <button class="quick-link-btn">Manage Categories</button>
    <button class="quick-link-btn">View All Reviews</button>
  </section>

  <!-- NOTIFICATIONS -->
  <section id="notifications" class="notifications"></section>

  <!-- MAIN CONTENT -->
  <main class="container">
    <!-- ANALYTICS CARDS -->
    <section class="cards">
      <div class="card" title="Total number of products in the system">
        <img src="icons/products.png" alt="Products Icon" class="card-icon">
        <h3>Total Products</h3>
        <p id="totalProducts">--</p>
        <small>Compared to last 30 days</small>
      </div>
      <div class="card" title="Average wellness score of products">
        <img src="icons/score.png" alt="Score Icon" class="card-icon">
        <h3>Average Score</h3>
        <p id="averageScore">--</p>
        <small>Compared to last 30 days</small>
      </div>
      <div class="card" title="Number of products scoring above the average">
        <img src="icons/trending-up.png" alt="Trending Up Icon" class="card-icon">
        <h3>Trending Up</h3>
        <p id="trendingUp">--</p>
        <small>Compared to last 30 days</small>
      </div>
      <div class="card" title="Number of products scoring at or below the average">
        <img src="icons/trending-down.png" alt="Trending Down Icon" class="card-icon">
        <h3>Trending Down</h3>
        <p id="trendingDown">--</p>
        <small>Compared to last 30 days</small>
      </div>
    </section>

    <!-- CONTROLS: Refresh Button & Toast Notification -->
    <div class="controls">
      <button class="refresh-btn" onclick="refreshScores()">Refresh Scores</button>
    </div>
    <div id="toast" class="toast">Data updated successfully!</div>

    <!-- TABLE CONTROLS (Search Bar) -->
    <div class="table-controls">
      <input type="text" id="searchInput" placeholder="Search products..." onkeyup="searchProducts()">
    </div>

    <!-- PRODUCT TABLE -->
    <section class="section-box">
      <h2>Products & Wellness Scores</h2>
      <table class="product-table" id="productTable">
        <thead>
          <tr>
            <th onclick="sortTable(0)">Product</th>
            <th onclick="sortTable(1)">Wellness Score</th>
            <th>Progress</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="productTableBody">
          <!-- Rows will be populated by wellness.js -->
        </tbody>
      </table>
    </section>

    <!-- POPULARITY PIE CHART (Small) -->
    <section class="section-box">
      <h2>Product Popularity</h2>
      <p class="chart-description">
        Pie chart showing each product’s average user rating (1–5 stars).
      </p>
      <canvas id="popularityChart" width="300" height="150"></canvas>
    </section>

    <!-- HISTORICAL TREND LINE CHART -->
    <section class="section-box">
      <h2>Historical Wellness Trend</h2>
      <p class="chart-description">
        Line chart showing historical changes in average wellness score.
      </p>
      <canvas id="trendChart" width="400" height="200"></canvas>
    </section>
  </main>

  <!-- FOOTER -->
  <footer class="dashboard-footer">
    <p>&copy; 2025 Mind & Motion</p>
  </footer>

  <!-- Link to External JavaScript -->
  <script src="wellness.js"></script>
</body>
</html>
