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
?>
