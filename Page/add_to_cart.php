<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Необходимо авторизоваться',
        'redirect' => 'vhod.php'
    ]);
    exit();
}

// Check if item ID was provided
if (!isset($_POST['idItem'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID товара не указан'
    ]);
    exit();
}

$userId = $_SESSION['user_id'];
$itemId = (int)$_POST['idItem'];

try {
    // Check if item exists in cart
    $stmt = $conn->prepare("SELECT quantity FROM Cart WHERE idUser = ? AND idItem = ?");
    $stmt->execute([$userId, $itemId]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        // If item exists, increase quantity by 1
        $newQuantity = $cartItem['quantity'] + 1;
        if ($newQuantity > 99) {
            echo json_encode([
                'success' => false,
                'message' => 'Достигнуто максимальное количество товара'
            ]);
            exit();
        }
        
        $stmt = $conn->prepare("UPDATE Cart SET quantity = ? WHERE idUser = ? AND idItem = ?");
        $stmt->execute([$newQuantity, $userId, $itemId]);
    } else {
        // If item doesn't exist, add it with quantity 1
        $stmt = $conn->prepare("INSERT INTO Cart (idUser, idItem, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$userId, $itemId]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Товар добавлен в корзину'
    ]);
} catch(PDOException $e) {
    error_log("Error in add_to_cart.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Произошла ошибка при добавлении в корзину'
    ]);
}
?> 