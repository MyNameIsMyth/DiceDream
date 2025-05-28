<?php
session_start();
require_once 'db_connect.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходимо войти в систему']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $userId = $_SESSION['user_id'];
    $itemId = $_POST['item_id'];
    
    try {
        // Проверяем, есть ли уже такой товар в корзине
        $stmt = $conn->prepare("SELECT * FROM Cart WHERE idUser = ? AND idItem = ?");
        $stmt->execute([$userId, $itemId]);
        
        if ($stmt->rowCount() > 0) {
            // Если товар уже есть, увеличиваем количество
            $stmt = $conn->prepare("UPDATE Cart SET quantity = quantity + 1 WHERE idUser = ? AND idItem = ?");
            $stmt->execute([$userId, $itemId]);
        } else {
            // Если товара нет, добавляем новую запись
            $stmt = $conn->prepare("INSERT INTO Cart (idUser, idItem, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$userId, $itemId]);
        }
        
        // Получаем общее количество товаров в корзине
        $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM Cart WHERE idUser = ?");
        $stmt->execute([$userId]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode(['success' => true, 'message' => 'Товар добавлен в корзину', 'total' => $total]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении товара: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос']);
}
?> 