<?php
session_start();


if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once('PHPHost.php');
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage());
    exit();
}

//get login details
try {
    $username = $_SESSION['user'];
    $stmt = $db->prepare("SELECT user_id, first_name FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo "User not found!";
        exit();
    }
} catch (PDOException $ex) {
    echo "Database error: " . htmlspecialchars($ex->getMessage());
    exit();
}


if (!isset($_GET['orders_id']) || !isset($_GET['order_prod_id'])) {
    echo "Invalid request. Order details are missing.";
    exit();
}

$orders_id    = $_GET['orders_id'];
$order_prod_id = $_GET['order_prod_id'];

//gets the order items 
try {
    $verify_sql = "
        SELECT 
            op.*,
            o.user_id,
            op.quantity AS ordered_quantity,
            pi.product_item_id,
            pi.quantity AS current_stock,
            p.product_name,
            p.product_image,
            pc.category_name
        FROM order_prod op
        INNER JOIN orders o ON op.orders_id = o.orders_id
        INNER JOIN product_item pi ON op.product_item_id = pi.product_item_id
        INNER JOIN product p ON pi.product_id = p.product_id
        LEFT JOIN product_category pc ON p.product_category_id = pc.product_category_id
        WHERE op.order_prod_id = ? AND o.user_id = ?
    ";
    $stmt = $db->prepare($verify_sql);
    $stmt->execute([$order_prod_id, $user['user_id']]);
    $order_item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order_item) {
        echo "Error: The item is not found in your order history.";
        exit();
    }
} catch (PDOException $ex) {
    echo "Database error: " . htmlspecialchars($ex->getMessage());
    exit();
}

//calculate how many of the same item have been returned
try {
    $stmtReturned = $db->prepare("SELECT SUM(quantity_returned) AS returned_total FROM returns WHERE order_prod_id = ?");
    $stmtReturned->execute([$order_prod_id]);
    $returnedData = $stmtReturned->fetch(PDO::FETCH_ASSOC);
    $returned_total = $returnedData['returned_total'] ? $returnedData['returned_total'] : 0;
} catch (PDOException $ex) {
    echo "Database error: " . htmlspecialchars($ex->getMessage());
    exit();
}

//total order quantity
$ordered_quantity   = $order_item['ordered_quantity'];
$remaining_quantity = $ordered_quantity - $returned_total;


$product_item_id = $order_item['product_item_id'];


$step = isset($_POST['step']) ? intval($_POST['step']) : 1;


$return_quantity = isset($_POST['return_quantity']) ? intval($_POST['return_quantity']) : 0;
$return_reason   = isset($_POST['return_reason']) ? trim($_POST['return_reason']) : "";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //cancel if user doesnt want to continue
    if (isset($_POST['cancel'])) {
        header("Location: return_select.php");
        exit();
    }

  
    if ($step === 1 && isset($_POST['initiate'])) {
        $step = 2;
    }
 
    elseif ($step === 2 && isset($_POST['return_form_submit'])) {
        $return_quantity = isset($_POST['return_quantity']) ? intval($_POST['return_quantity']) : 0;
        if ($return_quantity < 1 || $return_quantity > $remaining_quantity) {
            $error = "Please enter a valid return quantity (1 to $remaining_quantity).";
        } elseif (empty($_POST['return_reason'])) {
            $error = "Please select a return reason.";
        } else {
            $return_reason = $_POST['return_reason'];
            $step = 3;
        }
    }
 
    elseif ($step === 3 && isset($_POST['next_step'])) {
        
        $step = 4;
    }
   
    elseif ($step === 4 && isset($_POST['finish'])) {
        //mark item as returned in the DB
        try {
            //recorded into the returns table
            $insert_sql = "
                INSERT INTO returns (order_prod_id, user_id, product_item_id, quantity_returned, return_reason)
                VALUES (?, ?, ?, ?, ?)
            ";
            $stmt = $db->prepare($insert_sql);
            $stmt->execute([
                $order_prod_id,
                $user['user_id'],
                $product_item_id,
                $return_quantity,
                $return_reason
            ]);
        } catch (PDOException $ex) {
            echo "Error inserting return record: " . htmlspecialchars($ex->getMessage());
            exit();
        }

        //update stock
        try {
            $update_sql = "UPDATE product_item SET quantity = quantity + ? WHERE product_item_id = ?";
            $stmt = $db->prepare($update_sql);
            $stmt->execute([$return_quantity, $product_item_id]);
        } catch (PDOException $ex) {
            echo "Error updating stock: " . htmlspecialchars($ex->getMessage());
            exit();
        }

        //back to selection page
        header("Location: return_select.php");
        exit();
    }
}
?>
<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Return Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #E0E1DD; 
            color: #0D1B2A;
        }
        .main {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
		h3 {
            text-align: center;
            margin-bottom: 10px;
        }
		p {
            
            margin-bottom: 10px;
        }
        
        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin: 20px 0 40px;
            position: relative;
            align-items: center;
        }
        .progress-bar::before {
            content: '';
            position: absolute;
            top: 27%;
            left: 1%;
            width: 98%;
            height: 4px;
            background-color: #1b263b;
            z-index: 0;
            transform: translateY(-50%);
        }
        .step-circle {
            position: relative;
            z-index: 1;
            background-color: #1b263b;
            color: #E0E1DD;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }
        .step-circle.active {
            background-color: #415A77;
            color: #E0E1DD;
        }
        .step-label {
            margin-top: 8px;
            text-align: center;
            font-size: 14px;
        }

        
        .step-content {
            background-color: #778DA9;
            color: #0D1B2A;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }

        
        .flex-row {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .left-col {
            flex: 0 0 auto;
        }
        .right-col {
            flex: 1;
        }

        
        .product-image {
            display: block;
            max-width: 200px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #0D1B2A;
        }
        select, input[type="number"] {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            font-size: 14px;
            color: #0D1B2A;
        }
        .error {
            color: #FF3131;
            font-weight: bold;
            margin-top: 10px;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn {
            background-color: #415A77;
            color: #E0E1DD;
            width: 180px; 
            padding: 10px;
            text-align: center;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #778DA9;
            color: #0D1B2A;
        }
    </style>
</head>
<body>
    <div class="main">
        <h2>Return Product</h2>
        <!--progress bar-->
        <div class="progress-bar">
            <div>
                <div class="step-circle <?php if($step >= 1) echo 'active'; ?>">1</div>
                <div class="step-label">Step 1</div>
            </div>
            <div>
                <div class="step-circle <?php if($step >= 2) echo 'active'; ?>">2</div>
                <div class="step-label">Step 2</div>
            </div>
            <div>
                <div class="step-circle <?php if($step >= 3) echo 'active'; ?>">3</div>
                <div class="step-label">Step 3</div>
            </div>
            <div>
                <div class="step-circle <?php if($step >= 4) echo 'active'; ?>">4</div>
                <div class="step-label">Step 4</div>
            </div>
        </div>

        <form method="post">
            <input type="hidden" name="step" value="<?php echo $step; ?>">
            <input type="hidden" name="return_quantity" value="<?php echo htmlspecialchars($return_quantity); ?>">
            <input type="hidden" name="return_reason" value="<?php echo htmlspecialchars($return_reason); ?>">

            <?php if ($step === 1): ?>
                <!--STEP 1-->
                <div class="step-content">
                    <div class="flex-row">
                        <!--image-->
                        <div class="left-col">
                            <img 
                                src="<?php echo !empty($order_item['product_image']) 
                                    ? htmlspecialchars($order_item['product_image']) 
                                    : 'https://via.placeholder.com/200x200.png?text=Product+Image'; 
                                ?>" 
                                alt="Product" 
                                class="product-image"
                            >
                        </div>
                        <!--info-->
                        <div class="right-col">
                            <h3>Confirm Return</h3>
                            <p><strong>Product Name:</strong> <?php echo htmlspecialchars($order_item['product_name']); ?></p>
                            <p><strong>Category:</strong> 
                                <?php echo htmlspecialchars($order_item['category_name'] ?? 'N/A'); ?>
                            </p>
                            <p><strong>Quantity Purchased:</strong> <?php echo htmlspecialchars($ordered_quantity); ?></p>
                            <p><strong>Remaining to Return:</strong> <?php echo htmlspecialchars($remaining_quantity); ?></p>
                        </div>
                    </div>

                    <div class="button-container">
                        <button type="submit" name="cancel" class="btn">Cancel</button>
                        <button type="submit" name="initiate" class="btn">Next Step</button>
                    </div>
                </div>

            <?php elseif ($step === 2): ?>
                <!--STEP 2-->
                <div class="step-content">
                    <div class="flex-row">
                        <div class="left-col">
                            <img 
                                src="<?php echo !empty($order_item['product_image']) 
                                    ? htmlspecialchars($order_item['product_image']) 
                                    : 'https://via.placeholder.com/200x200.png?text=Product+Image'; 
                                ?>" 
                                alt="Product" 
                                class="product-image"
                            >
                        </div>
                        <div class="right-col">
                            <h3>Return Form</h3>
                            <?php if (isset($error)): ?>
                                <p class="error"><?php echo htmlspecialchars($error); ?></p>
                            <?php endif; ?>

                            <label for="return_quantity">Quantity to Return (max <?php echo $remaining_quantity; ?>):</label>
                            <input type="number" name="return_quantity" id="return_quantity" min="1" max="<?php echo $remaining_quantity; ?>" value="1" required>

                            <label for="return_reason">Reason for Return:</label>
                            <select name="return_reason" id="return_reason" required>
                                <option value="">-Select a reason-</option>
                                <option value="Product Quality">Product Quality</option>
                                <option value="Wrong Item">Wrong Item</option>
                                <option value="Damaged Product">Damaged Product</option>
                                <option value="Changed Mind">Changed Mind</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="button-container">
                        <button type="submit" name="cancel" class="btn">Cancel</button>
                        <button type="submit" name="return_form_submit" class="btn">Next Step</button>
                    </div>
                </div>

            <?php elseif ($step === 3): ?>
                <!--STEP 3-->
                <div class="step-content">
                    <div class="flex-row">
                        <div class="left-col">
                            <img 
                                src="<?php echo !empty($order_item['product_image']) 
                                    ? htmlspecialchars($order_item['product_image']) 
                                    : 'https://via.placeholder.com/200x200.png?text=Product+Image'; 
                                ?>" 
                                alt="Product" 
                                class="product-image"
                            >
                        </div>
                        <div class="right-col">
                            <h3>Mailing Instructions</h3>
                            <p>Please <strong>mail</strong> the item to the following address. Include <strong>your order number</strong> and a copy of <strong>your return form</strong> in the package.</p>
                            <p>123 Aston St,<br>Birmingham, Postal Code 7UJ 1A<br>United Kingdom</p>
                        </div>
                    </div>

                    <div class="button-container">
                        <button type="submit" name="cancel" class="btn">Cancel</button>
                        
                        <button type="submit" name="next_step" class="btn">Next Step</button>
                    </div>
                </div>

            <?php elseif ($step === 4): ?>
                <!--STEP 4-->
                <div class="step-content">
                    <div class="flex-row">
                        <div class="left-col">
                            <img 
                                src="<?php echo !empty($order_item['product_image']) 
                                    ? htmlspecialchars($order_item['product_image']) 
                                    : 'https://via.placeholder.com/200x200.png?text=Product+Image'; 
                                ?>" 
                                alt="Product" 
                                class="product-image"
                            >
                        </div>
                        <div class="right-col">
                            <h3>Confirmation</h3>
                            <p>
                                Once you click <strong>"Finish,"</strong> your return will be processed. 
                                We will update our stock by adding 
                                <?php echo htmlspecialchars($return_quantity); ?> unit<?php echo ($return_quantity > 1 ? 's' : ''); ?> 
                                and a <strong>refund</strong> will be issued shortly.
                            </p>
                        </div>
                    </div>

                    <div class="button-container">
                        <button type="submit" name="cancel" class="btn">Cancel</button>
                        <!--finalise the return and update the databse-->
                        <button type="submit" name="finish" class="btn">Finish</button>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>
