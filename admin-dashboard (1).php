<?php
session_start(); 
include 'PHPHost.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_log.php"); 
    exit();
}
include 'sidebar.php';


$sqlOrders = "SELECT COUNT(*) AS total_orders FROM orders";
$resultOrders = $db->query($sqlOrders);
$totalOrders = $resultOrders->fetch(PDO::FETCH_ASSOC)['total_orders'];

$sqlRevenue = "SELECT SUM(order_price) AS total_revenue FROM orders";
$resultRevenue = $db->query($sqlRevenue);
$totalRevenue = $resultRevenue->fetch(PDO::FETCH_ASSOC)['total_revenue'];


$sqlCustomers = "SELECT COUNT(DISTINCT user_id) AS new_customers FROM orders WHERE order_date >= CURDATE() - INTERVAL 12 MONTH";
$resultCustomers = $db->query($sqlCustomers);
$newCustomers = $resultCustomers->fetch(PDO::FETCH_ASSOC)['new_customers'];


$sqlProductsSold = "SELECT SUM(quantity) AS total_products_sold FROM order_prod";
$resultProductsSold = $db->query($sqlProductsSold);
$totalProductsSold = $resultProductsSold->fetch(PDO::FETCH_ASSOC)['total_products_sold'] ?? 0;


$currentAdminId = $_SESSION['admin_id'] ?? null; 
$currentAdmin = 'Guest'; 

if ($currentAdminId) {
    $sqlAdmin = "SELECT username FROM admins WHERE id = :admin_id"; 
    $stmtAdmin = $db->prepare($sqlAdmin);
    $stmtAdmin->bindParam(':admin_id', $currentAdminId);
    $stmtAdmin->execute();
    $adminData = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
    
    if ($adminData) {
        $currentAdmin = htmlspecialchars($adminData['username']); 
    }
}


$previousTotalOrders = 0;   
$previousTotalRevenue = 0;  
$previousNewCustomers = 0; 
$previousProductsSold = 0; 

$ordersChange = ($previousTotalOrders === 0 && $totalOrders > 0) ? 100 : ($previousTotalOrders ? (($totalOrders - $previousTotalOrders) / $previousTotalOrders) * 100 : 0);
$revenueChange = ($previousTotalRevenue === 0 && $totalRevenue > 0) ? 100 : ($previousTotalRevenue ? (($totalRevenue - $previousTotalRevenue) / $previousTotalRevenue) * 100 : 0);
$customersChange = ($previousNewCustomers === 0 && $newCustomers > 0) ? 100 : ($previousNewCustomers ? (($newCustomers - $previousNewCustomers) / $previousNewCustomers) * 100 : 0);
$productsChange = ($previousProductsSold === 0 && $totalProductsSold > 0) ? 100 : ($previousProductsSold ? (($totalProductsSold - $previousProductsSold) / $previousProductsSold) * 100 : 0);


$sqlRevenueMonthly = "SELECT MONTH(order_date) AS month, SUM(order_price) AS revenue 
                      FROM orders 
                      WHERE YEAR(order_date) = YEAR(CURDATE()) 
                      GROUP BY MONTH(order_date)";
$resultRevenueMonthly = $db->query($sqlRevenueMonthly);
$monthlyRevenue = array_fill(1, 12, 0); // Fill months with 0 as default

while ($row = $resultRevenueMonthly->fetch(PDO::FETCH_ASSOC)) {
    $monthlyRevenue[(int)$row['month']] = (float)$row['revenue'];
}


$sqlSalesByCategory = "SELECT p.item_category, SUM(op.quantity) AS total_sales 
                        FROM order_prod op 
                        JOIN products p ON op.product_item_id = p.product_id 
                        GROUP BY p.item_category";
$resultSalesByCategory = $db->query($sqlSalesByCategory);
$categories = [];
$salesByCategory = [];

while ($row = $resultSalesByCategory->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row['item_category'];
    $salesByCategory[] = (int)$row['total_sales'];
}


$sqlRecentOrders = "SELECT o.orders_id, u.username, p.item_name, o.order_price, os.status 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.user_id 
                    JOIN order_prod op ON o.orders_id = op.orders_id 
                    JOIN products p ON op.product_item_id = p.product_id 
                    JOIN order_status os ON o.order_status_id = os.order_status_id 
                    ORDER BY o.order_date DESC
                    LIMIT 5";
$resultRecentOrders = $db->query($sqlRecentOrders);
$recentOrders = $resultRecentOrders->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar-space"></div>

        <main class="main-content">
            <div class="welcome-section">
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <?php echo $currentAdmin; ?></p>
            </div>
            <button id="theme-toggle" class="theme-toggle">Switch to Light Mode</button>

                <div class="dashboard-stats">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="far fa-shopping-cart"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Total Orders</h3>
                        <p class="metric-value"><?php echo number_format($totalOrders); ?></p>
                        <span class="trend <?php echo $ordersChange > 0 ? 'positive' : ($ordersChange < 0 ? 'negative' : 'neutral'); ?>">
                            <?php echo number_format($ordersChange, 1) . '% <i class="fas fa-chevron-' . ($ordersChange > 0 ? 'up' : ($ordersChange < 0 ? 'down' : '')) . '"></i>'; ?>
                        </span>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="far fa-chart-line"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Revenue</h3>
                        <p class="metric-value">£<?php echo number_format($totalRevenue); ?></p>
                        <span class="trend <?php echo $revenueChange > 0 ? 'positive' : ($revenueChange < 0 ? 'negative' : 'neutral'); ?>">
                            <?php echo number_format($revenueChange, 1) . '% <i class="fas fa-chevron-' . ($revenueChange > 0 ? 'up' : ($revenueChange < 0 ? 'down' : '')) . '"></i>'; ?>
                        </span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="far fa-users"></i>
                    </div>
                    <div class="metric-info">
                        <h3>New Customers</h3>
                        <p class="metric-value"><?php echo number_format($newCustomers); ?></p>
                        <span class="trend <?php echo $customersChange > 0 ? 'positive' : ($customersChange < 0 ? 'negative' : 'neutral'); ?>">
                            <?php echo number_format($customersChange, 1) . '% <i class="fas fa-chevron-' . ($customersChange > 0 ? 'up' : ($customersChange < 0 ? 'down' : '')) . '"></i>'; ?>
                        </span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="far fa-box"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Products Sold</h3>
                        <p class="metric-value"><?php echo number_format($totalProductsSold); ?></p>
                        <span class="trend <?php echo $productsChange > 0 ? 'positive' : ($productsChange < 0 ? 'negative' : 'neutral'); ?>">
                            <?php echo number_format($productsChange, 1) . '% <i class="fas fa-chevron-' . ($productsChange > 0 ? 'up' : ($productsChange < 0 ? 'down' : '')) . '"></i>'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="dashboard-container">
            <div class="charts-container">
                <div class="chart-card">
                    <h3>Revenue Overview</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
                
                <div class="chart-card">
                    <h3>Sales by Category</h3>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <div class="recent-activity">
                <h3>Recent Orders</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['orders_id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td><?php echo htmlspecialchars($order['item_name']); ?></td>
                                    <td>£<?php echo number_format($order['order_price'], 2); ?></td>
                                    <td><span class="status <?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>

    <script>
        var revenueData = <?php echo json_encode(array_values($monthlyRevenue)); ?>;
        var categoryLabels = <?php echo json_encode($categories); ?>;
        var categorySales = <?php echo json_encode($salesByCategory); ?>;

    
        var revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: revenueData,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        var categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categorySales,
                    backgroundColor: ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'right' } }
            }
        });
        
        document.addEventListener("DOMContentLoaded", function () {
  const themeToggle = document.getElementById("theme-toggle");
  const currentTheme = localStorage.getItem("theme") || "dark";

  document.documentElement.setAttribute("data-theme", currentTheme);
  themeToggle.textContent = currentTheme === "dark" ? "Light Mode" : "Dark Mode";

  themeToggle.addEventListener("click", () => {
    let theme = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", theme);
    localStorage.setItem("theme", theme);
    themeToggle.textContent = theme === "dark" ? "Light Mode" : "Dark Mode";
  });
});

    </script>
    

</body>
</html>
