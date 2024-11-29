<?php
session_start();

include 'navbar.php';


try {
    // Include PHPHost.php using an absolute path
    require_once(__DIR__ . '/PHPHost.php'); // Adjust this path if necessary
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . $ex->getMessage();
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basket</title>
</head>
<body>

    <h1>Your Basket</h1>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Product Picture</th>
                <th>Price</th>
                <th>Checkout</th>
            </tr>
        </thead>
        <tbody>
            <!-- Examples -->
            <tr>
                <td>Product 1</td>
                <td><img src="product1.jpg" alt="Product 1"></td>
                <td>####</td>
                <td><button class="checkout-btn">Checkout</button></td>
            </tr>
           
            <tr>
                <td>Product 2</td>
                <td><img src="product2.jpg" alt="Product 2"></td>
                <td>####</td>
                <td><button class="checkout-btn">Checkout</button></td>
            </tr>
            
        </tbody>
    </table>

    <div>
        <button class="previousorder-btn">Previous Orders</button>
    </div>

    <style>
        body {
            font-family: Arial, sans-serif;
            
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        img {
            width: 100px;
            height: auto;
        }
        .checkout-btn {
            background-color: #2a4d69;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border: none;
            cursor: pointer;
        }
        .checkout-btn:hover {
            background-color: #4c7b97;
        }

        .previousorder-btn {
            background-color: #2a4d69;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border: none;
            cursor: pointer;
        }
        .previousorder-btn:hover {
            background-color: #4c7b97;
        }


    </style>

</body>
</html>
