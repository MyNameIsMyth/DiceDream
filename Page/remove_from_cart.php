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
        $stmt = $conn->prepare("DELETE FROM Cart WHERE idUser = ? AND idItem = ?");
        $stmt->execute([$userId, $itemId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Товар удален из корзины']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Товар не найден в корзине']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при удалении товара: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос']);
}
?> 