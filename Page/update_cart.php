<?php
session_start();
require_once 'db_connect.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходимо войти в систему']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id']) && isset($_POST['quantity'])) {
    $userId = $_SESSION['user_id'];
    $itemId = $_POST['item_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Проверяем валидность количества
    if ($quantity < 1) $quantity = 1;
    if ($quantity > 99) $quantity = 99;
    
    try {
        $stmt = $conn->prepare("UPDATE Cart SET quantity = ? WHERE idUser = ? AND idItem = ?");
        $stmt->execute([$quantity, $userId, $itemId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Количество обновлено']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Товар не найден в корзине']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении количества: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос']);
}
?> 