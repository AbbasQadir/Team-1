<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Ensure only logged-in admins can see this page
if (!isset($_SESSION['admin_id'])) {
    die("Admin login required.");
}

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once('PHPHost.php'); // Adjust path if needed
    } catch (Exception $ex) {
        die("Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()));
    }
    global $db;

    // Delete a return
    if (isset($_POST['deleteReturn'])) {
        $returnId = (int)$_POST['return_id'];
        $stmt = $db->prepare("DELETE FROM returns WHERE return_id = ?");
        $stmt->execute([$returnId]);
        header("Location: returns.php");
        exit();
    }
    // Edit a return
    elseif (isset($_POST['editReturn'])) {
        $returnId  = (int)$_POST['return_id'];
        $newQty    = (int)$_POST['quantity_returned'];
        $newReason = trim($_POST['return_reason'] ?? '');

        $stmt = $db->prepare("UPDATE returns SET quantity_returned = ?, return_reason = ? WHERE return_id = ?");
        $stmt->execute([$newQty, $newReason, $returnId]);
        header("Location: returns.php");
        exit();
    }
}


try {
    require_once('PHPHost.php'); // Adjust path if needed
} catch (Exception $ex) {
    die("Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()));
}

global $db;


$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;


$search    = isset($_GET['search']) ? trim($_GET['search']) : '';
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate   = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';


$whereParts = [];
$params = [];


if ($orderId > 0) {
    $whereParts[] = "op.orders_id = :orderId";
    $params[':orderId'] = $orderId;
}


if ($search !== '') {
    $whereParts[] = "(
        r.return_id LIKE :search1
        OR r.return_reason LIKE :search2
        OR op.product_item_id LIKE :search3
    )";
    $params[':search1'] = '%' . $search . '%';
    $params[':search2'] = '%' . $search . '%';
    $params[':search3'] = '%' . $search . '%';
}


if ($startDate !== '') {
    $whereParts[] = "r.return_date >= :startDate";
    $params[':startDate'] = $startDate . " 00:00:00";
}
if ($endDate !== '') {
    $whereParts[] = "r.return_date <= :endDate";
    $params[':endDate'] = $endDate . " 23:59:59";
}

$whereSQL = '';
if (!empty($whereParts)) {
    $whereSQL = "WHERE " . implode(' AND ', $whereParts);
}


$sql = "
    SELECT
        r.return_id,
        r.quantity_returned,
        r.return_reason,
        r.return_date,
        op.product_item_id,
        op.quantity AS original_qty,
        op.orders_id
    FROM returns r
    JOIN order_prod op ON r.order_prod_id = op.order_prod_id
    $whereSQL
    ORDER BY r.return_date DESC
";
$stmt = $db->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$returns = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Returns Management</title>
	<link rel="stylesheet" href="admin-dashboard.css">
    <style>
  

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: var(--bg-color);
        margin: 0;
        padding: 20px;
        font-weight: bold;
        color: var(--text-color);
        position: relative;
    }
   
    .header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        position: relative;
    }
    .header-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .page-title {
        font-size: 2rem;
        color: var(--text-color);
        margin: 0;
    }
    a.back-link {
        background: #415A77;
        color: #fff;
        padding: 12px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 1.1rem;
        box-shadow: 2px 2px 6px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    a.back-link:hover {
        background: #778DA9;
        color: #000;
        transform: scale(1.05);
    }

   
    .filter-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        justify-content: center;
    }
    .filter-input,
    .filter-date {
        padding: 10px 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: bold;
    }
    .filter-input {
        width: 280px;
    }
    .filter-date {
        width: 180px;
    }
    .filter-input::placeholder,
    .filter-date::placeholder {
        font-weight: normal;
        color: #999;
    }
    .search-button,
    .filter-button {
        padding: 10px 16px;
        border: none;
        background: #415A77;
        color: white;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: bold;
        box-shadow: 2px 2px 6px rgba(0,0,0,0.2);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .search-button:hover,
    .filter-button:hover {
        background: #778DA9;
        color: #000;
        transform: scale(1.05);
    }

    /* Container for returns table */
    .returns-container {
        width: 98%;
        margin: 0 auto;
        background: var(--card-bg);
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .returns-row {
        display: flex;
        align-items: center;
        padding: 18px 20px;
        border-bottom: 1px solid #e1e1e1;
        transition: background 0.3s;
    }
    .returns-row:hover {
        background: var(--nth-color);
    }
    .returns-row.header {
        background-color: #0D1B2A;
        color: #ffffff;
        font-size: 1.1rem;
    }
    .returns-cell {
        flex: 1;
        text-align: center;
        padding: 8px 12px;
        position: relative;
        word-wrap: break-word;
        font-size: 1rem;
    }
    .returns-cell:not(:last-child)::after {
        content: "";
        position: absolute;
        top: 0; bottom: 0; right: 0;
        width: 1px;
        background-color: #ddd;
    }
    .button {
        background: #415A77;
        color: #fff;
        border: none;
        padding: 12px 16px;
        border-radius: 6px;
        cursor: pointer;
        margin: 5px;
        transition: 0.3s;
        font-size: 1rem;
        font-weight: bold;
        box-shadow: 2px 2px 6px rgba(0,0,0,0.2);
    }
    .button:hover {
        background: #778DA9;
        color: #000;
        transform: scale(1.05);
        box-shadow: 3px 3px 9px rgba(0,0,0,0.3);
    }
    .edit-form {
        display: none;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        background: #f9f9f9;
        border: 1px solid #ccc;
        padding: 12px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .edit-form label {
        font-weight: normal;
        color: #333;
        margin-bottom: 4px;
        font-size: 0.9rem;
    }
    .edit-form input[type="number"],
    .edit-form input[type="text"] {
        width: 220px;
        padding: 8px;
        font-weight: normal;
        margin-bottom: 8px;
        font-size: 1rem;
    }
    .edit-form .button {
        width: 120px;
        align-self: center;
    }
    </style>
</head>
<body>

    <!-- Top bar with "Back to Orders" on left, "Returns Management" in center -->
    <div class="header-bar">
        <div class="header-left">
            <a href="ordermanagement.php" class="back-link">Back to Orders</a>
        </div>
        <h2 class="page-title">Returns Management</h2>
     
        <div></div>
    </div>


    <form method="GET" action="returns.php" class="filter-row">
        <!-- Keep order_id if set -->
        <?php if ($orderId > 0): ?>
            <input type="hidden" name="order_id" value="<?php echo (int)$orderId; ?>">
        <?php endif; ?>

        <input type="text" name="search"
               class="filter-input"
               placeholder="Search (Return ID, Reason, Product ID)"
               value="<?php echo htmlspecialchars($search); ?>">

        <button type="submit" class="search-button">Search</button>

        <input type="date" name="start_date"
               class="filter-date"
               value="<?php echo htmlspecialchars($startDate); ?>">
        <input type="date" name="end_date"
               class="filter-date"
               value="<?php echo htmlspecialchars($endDate); ?>">

        <button type="submit" class="filter-button">Filter</button>
    </form>

    <div class="returns-container">
        <?php if (empty($returns)): ?>
            <div class="returns-row">
                <div class="returns-cell" style="flex:1;">No returns found.</div>
            </div>
        <?php else: ?>
            <div class="returns-row header">
                <div class="returns-cell">Return ID</div>
                <div class="returns-cell">Order ID</div>
                <div class="returns-cell">Product Item ID</div>
                <div class="returns-cell">Original Qty</div>
                <div class="returns-cell">Qty Returned</div>
                <div class="returns-cell">Reason</div>
                <div class="returns-cell">Return Date</div>
                <div class="returns-cell">Actions</div>
            </div>

            <?php foreach ($returns as $r): ?>
                <?php $returnId = (int)$r['return_id']; ?>
                <div class="returns-row">
                    <div class="returns-cell"><?php echo $returnId; ?></div>
                    <div class="returns-cell"><?php echo (int)$r['orders_id']; ?></div>
                    <div class="returns-cell"><?php echo htmlspecialchars($r['product_item_id']); ?></div>
                    <div class="returns-cell"><?php echo (int)$r['original_qty']; ?></div>
                    <div class="returns-cell"><?php echo (int)$r['quantity_returned']; ?></div>
                    <div class="returns-cell"><?php echo htmlspecialchars($r['return_reason']); ?></div>
                    <div class="returns-cell"><?php echo htmlspecialchars($r['return_date']); ?></div>
                    <div class="returns-cell" style="display:flex; flex-direction:column; align-items:center;">
                     
                        <form method="POST" style="margin-bottom:5px;">
                            <input type="hidden" name="return_id" value="<?php echo $returnId; ?>">
                            <button type="submit" name="deleteReturn" class="button">Delete</button>
                        </form>
                      
                        <button class="button" onclick="toggleEditForm(<?php echo $returnId; ?>)">Edit</button>
                    </div>
                </div>

          
                <div class="edit-form" id="editForm-<?php echo $returnId; ?>">
                    <form method="POST">
                        <input type="hidden" name="return_id" value="<?php echo $returnId; ?>">

                        <label>Qty Returned:</label>
                        <input type="number" name="quantity_returned" value="<?php echo (int)$r['quantity_returned']; ?>">

                        <label>Reason:</label>
                        <input type="text" name="return_reason" value="<?php echo htmlspecialchars($r['return_reason']); ?>">

                        <button type="submit" name="editReturn" class="button">Save</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    function toggleEditForm(returnId) {
        const form = document.getElementById('editForm-' + returnId);
        if (form.style.display === 'block') {
            form.style.display = 'none';
        } else {
            form.style.display = 'block';
        }
    }

	//sets current theme (light mode/ dark mode)
	 const currentTheme = localStorage.getItem("theme") || "dark";
      document.documentElement.setAttribute("data-theme", currentTheme);

    </script>
</body>
</html>

