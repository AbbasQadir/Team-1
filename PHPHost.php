<?php

$db_host = 'localhost';  
$db_name = 'cs2team1_db';
$username = 'cs2team1';
$password = 'SqDC8zgJHEVQBIo';


try {
    // Establish a database connection
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $username, $password);

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable detailed errors
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Use native prepared statements
} catch (PDOException $ex) {
    error_log("Database connection error: " . $ex->getMessage());

    die("An error occurred while connecting to the database. Please try again later.");
}


function fetchProducts($db, $requestedCategory){
    
    if($requestedCategory == "fitness"){
        $category = "Fitness Equipment";
    }elseif($requestedCategory == "books"){
        $category = "Books";
    } elseif($requestedCategory == "gymWear"){
        $category = "Gym Wear";
    }elseif($requestedCategory == "technology"){
        $category = "Technology";
    }elseif($requestedCategory == "supplements"){
        $category = "Supplements";
    }

   
    $result = $db->prepare("SELECT * FROM products WHERE item_category=:category; ");
    $result->bindParam(":category", $category);
    $result->execute();
    $items = $result->fetchAll(PDO::FETCH_ASSOC);
    return $items;
}

function searchProducts($db, $query){
    if($query == ""){
        echo "<script>window.location.href='productpage2.php?category=fitness';</script>";
    }

    $sql = "SELECT * FROM products WHERE item_name LIKE ? OR description LIKE ? OR item_category LIKE ?";

    $result = $db->prepare($sql);

    $searchTerm = "%$query%";

    $result->execute(array($searchTerm, $searchTerm, $searchTerm));

    $items = $result->fetchAll(PDO::FETCH_ASSOC);
    
    return $items;
}



?>
