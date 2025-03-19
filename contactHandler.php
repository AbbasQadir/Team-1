<?php

try {
    if (!isset($db)) {
        require_once(__DIR__ . '/PHPHost.php');
    }
} catch (Exception $ex) {
    echo "<p style='color:red'>Failed to include PHPHost.php: " . htmlspecialchars($ex->getMessage()) . "</p>";
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $contact = htmlspecialchars($_POST['contact'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($_POST['firstname'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email1'], ENT_QUOTES, 'UTF-8');
    $date = htmlspecialchars($_POST['date'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');

    $sql = "INSERT INTO Email (`name`, `email`, `number`, `message`, `contact`, `date`)
            VALUES (:name, :email, :number, :message, :contact, :date)";
    
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'number' => $phone,
            'message' => $message,
            'contact' => $contact,
            'date' => $date,
        ]);
        echo "<script type='text/javascript'>alert('Contact sent successfully');</script>";
    } catch (PDOException $e) {
        echo "Error inserting record: " . ($e->getMessage());
        return;
    }
}
?>
