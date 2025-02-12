<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin-dashboard-dark.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Left space for sidebar component -->
        <div class="sidebar-space"></div>

        <main class="main-content">
            <div class="welcome-section">
                <h1>Dashboard Overview</h1>
                <p>Welcome back, Admin</p>
            </div>

            <div class="dashboard-stats">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="far fa-shopping-cart"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Total Orders</h3>
                        <p class="metric-value">34,567</p>
                        <span class="trend positive">+12.5% <i class="fas fa-chevron-up"></i></span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="far fa-chart-line"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Revenue</h3>
                        <p class="metric-value">$74,567</p>
                        <span class="trend positive">+8.2% <i class="fas fa-chevron-up"></i></span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="far fa-users"></i>
                    </div>
                    <div class="metric-info">
                        <h3>New Customers</h3>
                        <p class="metric-value">1,234</p>
                        <span class="trend positive">+5.7% <i class="fas fa-chevron-up"></i></span>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="far fa-box"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Products Sold</h3>
                        <p class="metric-value">45,678</p>
                        <span class="trend negative">-2.3% <i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>
            </div>

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
                            <tr>
                                <td>#ORD-2024</td>
                                <td>John Doe</td>
                                <td>Gaming Laptop</td>
                                <td>$1,299</td>
                                <td><span class="status delivered">Delivered</span></td>
                            </tr>
                            <tr>
                                <td>#ORD-2023</td>
                                <td>Jane Smith</td>
                                <td>Smartphone</td>
                                <td>$899</td>
                                <td><span class="status pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>#ORD-2022</td>
                                <td>Mike Johnson</td>
                                <td>Headphones</td>
                                <td>$199</td>
                                <td><span class="status processing">Processing</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Revenue Chart
        var revenueCtx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: [5000, 7000, 8000, 6000, 9000, 12000, 15000, 13000, 16000, 18000, 20000, 22000],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: { 
                        grid: { 
                            display: false,
                            color: '#666'
                        },
                        ticks: { color: '#fff' }
                    },
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: {
                            color: '#fff',
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Category Chart
        var categoryCtx = document.getElementById('categoryChart').getContext('2d');
        var categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports'],
                datasets: [{
                    data: [30, 25, 15, 20, 10],
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#f1c40f',
                        '#e74c3c',
                        '#9b59b6'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#fff'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
