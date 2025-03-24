<?php

try {
    require_once(__DIR__ . '/PHPHost.php');  
} catch (Exception $ex) {
    echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact = htmlspecialchars(string: $_POST['contact']);
    $name = htmlspecialchars(string: $_POST['firstname']);
    $phone = htmlspecialchars(string: $_POST['phone']);
    $email = htmlspecialchars(string: $_POST['email1']);
    $date = htmlspecialchars(string: $_POST['date']);
    $message = htmlspecialchars(string: $_POST['description']);

 
try {
    require_once(__DIR__ . '/PHPHost.php'); 
} catch (Exception $ex) {
    echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact = htmlspecialchars($_POST['contact'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($_POST['firstname'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email1'], ENT_QUOTES, 'UTF-8');
    $date = htmlspecialchars($_POST['date'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');

	echo "Name: " . htmlspecialchars($name) . "<br>";
    echo "Email: " . htmlspecialchars($email) . "<br>";
    echo "Message: " . htmlspecialchars($message) . "<br>";
	echo "Number: " . htmlspecialchars($phone) . "<br>";
	echo "date: " . htmlspecialchars($date) . "<br>";
	echo "contact: " . htmlspecialchars($contact) . "<br>";
	var_dump($name, $email, $phone, $message, $contact, $date);

    $sql = "INSERT INTO Email (`name`, `email`, `number`, `message`, `contact`, `date`)
            VALUES (:name, :email, :number, :message, :contact, :date)";
	$stmt = $db->prepare($sql);

	$data = [
    'name' => $name,
    'email' => $email,
    'number' => $phone,
    'message' => $message,
    'contact' => $email,
    'date' => $date,
	];

	error_log("Query: " . $sql);
   
    try {
    $stmt->execute($data);
    echo "Record inserted successfully.";
	} catch (PDOException $e) {
    die("Error inserting record: " . $e->getMessage());
	}
}

?>