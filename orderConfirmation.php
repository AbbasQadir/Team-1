<?php
session_start();
include 'navbar.php';

try {
    require_once('PHPHost.php'); 
} catch (Exception $ex) {
    echo "Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage());
    exit();
}

if (!isset($_SESSION['uid'])) {
    echo "<script> location.href='/' </script>";
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <!--<link rel="stylesheet" href="sty.css">-->
</head>

<body>

    <h1 style="text-align: center; margin-top: 20px;">Thank you for your order</h1>
    <h3 style="text-align: center; margin-top: 20px;"> Your order ID is: <?php echo $_GET["orderID"]; ?> </h3>
    <p style="text-align: center; margin-top: 20px;" > Your order is now in our system as pending. You should have received an email with your order details. </p>

    <div id="buttonContainer">
        <button class="btn" onclick="location.href='/previous_orders.php'"> See your other Orders </button>
        <button class="btn" onclick="location.href='/'"> Go home </button>
    </div>
   
    
</body>

</html>
<?php include 'footer.php'; ?>
<style>
   #buttonContainer {
        display: flex; 
        justify-content: center; 
        align-items: center; 
        margin-top: 50px;
        margin-bottom:50px;
        gap: 20px; 
    }

    .btn {
        font-family: 'Merriweather', serif;
        background-color: #084298;
        color: white;
        border: none;
        padding: 18px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 300px;
        text-decoration: none;
        text-align: center;
    }
</style>