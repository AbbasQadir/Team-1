<?php

$db_host = 'localhost';  
$db_name = 'cs2team1_db';
$username = 'cs2team1';
$password = 'SqDC8zgJHEVQBIo';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $username, $password);
} catch (PDOException $ex) {
    echo "Failed to connect to the database.<br>";
    echo $ex->getMessage();
    exit;
}

?>
