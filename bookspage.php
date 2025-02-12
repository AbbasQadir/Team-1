<?php
include 'navbar.php';
require_once "PHPHost.php";
?>
<link rel="stylesheet" href="homestyle.css">
<link rel="stylesheet" href="main.css">


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mind & Motion</title>
        <link rel="stylesheet" href="styless.css">
    </head>
    <body>

        <?php 

            $category = "Books";

            require_once "itemList.php";

        ?>

        
    </body>

    <?php include 'footer.php'; ?>
</html>
