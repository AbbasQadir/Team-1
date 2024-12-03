<?php
session_start();
require_once("PHPHost.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['basket_id'])) {
    $basketId = $_POST['basket_id'];

    $stmt = $db->prepare("DELETE FROM asad_basket WHERE basket_id = :basket_id AND user_id = :user_id");
    $stmt->execute([
        ':basket_id' => $basketId,
        ':user_id' => $_SESSION['uid'] 
    ]);

    header("Location: Basket.php");
    exit;
}
?>
