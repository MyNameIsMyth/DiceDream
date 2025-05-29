<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться']);
    exit();
}

// Проверяем наличие ID товара
if (!isset($_POST['idItem'])) {
    echo json_encode(['success' => false, 'message' => 'ID товара не указан']);
    exit();
}

$userId = $_SESSION['user_id'];
$itemId = (int)$_POST['idItem'];

try {
    // Проверяем наличие товара в корзине
    $stmt = $conn->prepare("SELECT quantity FROM Cart WHERE idUser = ? AND idItem = ?");
    $stmt->execute([$userId, $itemId]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        // Если товар уже есть в корзине, увеличиваем количество на 1
        $newQuantity = $cartItem['quantity'] + 1;
        if ($newQuantity > 99) {
            echo json_encode(['success' => false, 'message' => 'Достигнуто максимальное количество товара']);
            exit();
        }
        
        $stmt = $conn->prepare("UPDATE Cart SET quantity = ? WHERE idUser = ? AND idItem = ?");
        $stmt->execute([$newQuantity, $userId, $itemId]);
    } else {
        // Если товара нет в корзине, добавляем его с количеством 1
        $stmt = $conn->prepare("INSERT INTO Cart (idUser, idItem, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$userId, $itemId]);
    }

    echo json_encode(['success' => true, 'message' => 'Товар добавлен в корзину']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении в корзину']);
}
?> 