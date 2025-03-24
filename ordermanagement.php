<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_log.php"); 
    exit();
}


// Handle AJAX POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action'])) {
        header('Content-Type: application/json');

        try {
            require_once('PHPHost.php'); // Adjust path as needed
        } catch (Exception $ex) {
            echo json_encode(['success' => false, 'message' => "Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage())]);
            exit();
        }

        global $db;

        // 1) Update Status
        if ($input['action'] === 'updateStatus') {
            $orders_id = isset($input['orders_id']) ? intval($input['orders_id']) : 0;
            $newStatus = isset($input['newStatus']) ? $input['newStatus'] : '';

            try {
                // Validate the new status exists
                $checkStmt = $db->prepare("SELECT COUNT(*) FROM order_status WHERE status = ?");
                $checkStmt->execute([$newStatus]);
                if (!$checkStmt->fetchColumn()) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status']);
                    exit();
                }
                
                // Update the orders table
                $stmt = $db->prepare("
                    UPDATE orders
                    SET order_status_id = (
                        SELECT order_status_id
                        FROM order_status
                        WHERE status = ?
                    )
                    WHERE orders_id = ?
                ");
                $stmt->execute([$newStatus, $orders_id]);

                echo json_encode(['success' => true]);
            } catch (PDOException $ex) {
                echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
            }
            exit();
        }

        // 2) Delete Order
          elseif ($input['action'] === 'deleteOrder') {
            $orders_id = isset($input['orders_id']) ? intval($input['orders_id']) : 0;
            try {
                
                $stmtDependent = $db->prepare("DELETE FROM order_prod WHERE orders_id = ?");
                $stmtDependent->execute([$orders_id]); 

              
                $stmt = $db->prepare("DELETE FROM orders WHERE orders_id = ?");
                $stmt->execute([$orders_id]);  

                echo json_encode(['success' => true]);
            } catch (PDOException $ex) {
                echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
            }
            exit();
        }

        // Unknown action
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        exit();
    }
}


try {
    require_once('PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage());
    exit();
}

include 'sidebar.php'; 

global $db;


$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';


$sql = "
    SELECT
        orders.orders_id,
        orders.user_id,
        orders.order_date,
        orders.order_price,
        order_status.status
    FROM orders
    INNER JOIN order_status
        ON orders.order_status_id = order_status.order_status_id
";
$conditions = [];
$params = [];


if ($search !== '') {
   
    $conditions[] = "(orders.orders_id LIKE :search1 OR orders.user_id LIKE :search2)";
    $params[':search1'] = '%' . $search . '%';
    $params[':search2'] = '%' . $search . '%';
}


if ($statusFilter !== '') {
    $conditions[] = "order_status.status = :statusFilter";
    $params[':statusFilter'] = $statusFilter;
}


if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}


$sql .= " ORDER BY orders.order_date DESC";


try {
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $ex) {
    echo "Failed to retrieve orders: " . htmlspecialchars($ex->getMessage());
    exit();
}


try {
    $statusStmt = $db->query("SELECT status FROM order_status");
    $allStatuses = $statusStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $ex) {
    echo "Failed to retrieve statuses: " . htmlspecialchars($ex->getMessage());
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>Order Management</title>
	
    <link rel="stylesheet" href="ordermanagement.css">
<link rel="stylesheet" href="admin-dashboard.css">

</head>
<body>
    <h2>Order Management</h2>

    
    <form method="GET" action="ordermanagement.php" class="filter-form">
        <!-- Search Input + Search Button -->
        <input type="text" name="search" 
               placeholder="Search (Orders ID or User ID)" 
               value="<?php echo htmlspecialchars($search); ?>" 
               class="filter-input" />

        <button type="submit" name="searchBtn" class="search-button">
            Search
        </button>

    
        <select name="status_filter" class="filter-select">
            <option value="">All Statuses</option>
            <?php foreach ($allStatuses as $st): ?>
                <option value="<?php echo htmlspecialchars($st); ?>"
                    <?php if ($statusFilter === $st) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($st); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="filterBtn" class="filter-button">
            Filter
        </button>
    </form>
 

    <!-- Success message container (for AJAX updates/deletes) -->
    <p id="ajaxMessage" style="color:green; text-align:center; display:none;"></p>


    <div class="order-container">
     
        <div class="order-row header-order">
            <div class="order-cell">Orders ID</div>
            <div class="order-cell">User ID</div>
            <div class="order-cell">Order Date</div>
            <div class="order-cell">Total Amount</div>
            <div class="order-cell">Status</div>
            <div class="order-cell">Actions</div>
        </div>


        <?php if (empty($orders)): ?>
            <div class="order-row">
                <div class="order-cell" style="width:100%;">
                    No orders found.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-row" id="orders-<?php echo (int)$order['orders_id']; ?>">
                  
                        <div class="order-cell"><?php echo htmlspecialchars($order['orders_id']); ?></div>
                    </a>
                    <div class="order-cell"><?php echo htmlspecialchars($order['user_id']); ?></div>
                    <div class="order-cell"><?php echo htmlspecialchars($order['order_date']); ?></div>
                    <div class="order-cell">£<?php echo htmlspecialchars($order['order_price']); ?></div>

                  
                    <div class="order-cell">
                        <select id="status-select-<?php echo (int)$order['orders_id']; ?>">
                            <?php foreach ($allStatuses as $singleStatus): ?>
                                <option value="<?php echo htmlspecialchars($singleStatus); ?>"
                                    <?php if ($order['status'] === $singleStatus) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($singleStatus); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="dropdown-arrow"></span>
                    </div>

                  
                    <div class="order-cell actions">
                        <button class="button" onclick="updateOrder(<?php echo (int)$order['orders_id']; ?>)">Update</button>
                       <button class="button" onclick="deleteOrder(<?php echo (int)$order['orders_id']; ?>)">Delete</button>
                        <button class="button" onclick="location.href='adminOrderDetails.php?orders_id=<?php echo (int)$order['orders_id']; ?>'">Details</button>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        
         
        
        
    
        function updateOrder(orders_id) {
            const select = document.getElementById(`status-select-${orders_id}`);
            const newStatus = select.value;
            updateStatus(newStatus, orders_id);
        }

        function updateStatus(newStatus, orders_id) {
            fetch('ordermanagement.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'updateStatus',
                    orders_id: orders_id,
                    newStatus: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const ajaxMessage = document.getElementById('ajaxMessage');
                    ajaxMessage.textContent = 'Status updated successfully!';
                    ajaxMessage.style.display = 'block';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    setTimeout(() => {
                        ajaxMessage.style.display = 'none';
                    }, 3000);
                } else {
                    alert("Error updating status: " + data.message);
                }
            })
            .catch(error => {
                alert("AJAX error: " + error);
            });
        }


        function deleteOrder(orders_id) {
            if (!confirm("Are you sure you want to delete this order?")) {
                return;
            }
            fetch('ordermanagement.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'deleteOrder',
                    orders_id: orders_id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                 
                    const row = document.getElementById('orders-' + orders_id);
                    if (row) row.remove();
                    const ajaxMessage = document.getElementById('ajaxMessage');
                    ajaxMessage.textContent = 'Order deleted successfully!';
                    ajaxMessage.style.display = 'block';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    setTimeout(() => {
                        ajaxMessage.style.display = 'none';
                    }, 3000);
                } else {
                    alert("Error deleting order: " + data.message);
                }
            })
            .catch(error => {
                alert("AJAX error: " + error);
            });
   
var modal = document.getElementById("deleteModal");


var deleteBtns = document.querySelectorAll(".delete-button"); 


var span = document.getElementsByClassName("close")[0];

deleteBtns.forEach(btn => {
    btn.onclick = function() {
        modal.style.display = "block";
    }
});

span.onclick = function() {
    modal.style.display = "none";
}


var confirmDelete = document.getElementById("confirmDelete");
var cancelDelete = document.getElementById("cancelDelete");


confirmDelete.onclick = function() {
  
    modal.style.display = "none";
}


cancelDelete.onclick = function() {
    modal.style.display = "none";
}


window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

        }
    </script>
    <script>
  
    

<script>
    function deleteOrder(orders_id) {
        if (confirm("Are you sure you want to delete this order?")) {
            fetch('ordermanagement.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'deleteOrder',
                    orders_id: orders_id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.getElementById('orders-' + orders_id);
                    if (row) row.remove();  
                    alert("Order deleted successfully!");
                } else {
                    alert("Error deleting order: " + data.message);
                }
            })
            .catch(error => {
                alert("AJAX error: " + error);
            });
        }
    }
</script>


</body>
</html>
