<?php

$db_host = 'localhost';  
$db_name = 'cs2team1_db';
$username = 'cs2team1';
$password = 'SqDC8zgJHEVQBIo';


try {
    // Establish a database connection
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $username, $password);

    // Set PDO attributes for better error handling and performance
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable detailed errors
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Use native prepared statements
} catch (PDOException $ex) {
    // Log the error instead of displaying it to the user
    error_log("Database connection error: " . $ex->getMessage());

    // Provide a generic error message to the user
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
    // If the search query is empty, redirect the user to the fitness category page
    if($query == ""){
        echo "<script>window.location.href='productpage2.php?category=fitness';</script>";
    }

    // Prepare the SQL query to search for the query in three different fields: item name, description, and category
    $sql = "SELECT * FROM products WHERE item_name LIKE ? OR description LIKE ? OR item_category LIKE ?";

    // Prepare the query for execution
    $result = $db->prepare($sql);

    // Add "%" around the search query so we can match partial text (not just exact matches)
    $searchTerm = "%$query%";

    // Execute the query, using the search term for all three fields (name, description, and category)
    $result->execute(array($searchTerm, $searchTerm, $searchTerm));

    // Fetch all matching products and return them
    $items = $result->fetchAll(PDO::FETCH_ASSOC);
    
    return $items;
}



?>
