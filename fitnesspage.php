<?php
include 'navbar.php';
require_once "PHPHost.php";
?>
<link rel="stylesheet" href="homestyle.css">

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

        $category = "Fitness Equipment";

        require_once "itemList.php";

    ?>

</body> 
</html>
<?php include 'footer.php'; ?>