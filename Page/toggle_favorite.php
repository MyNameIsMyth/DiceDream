<?php
session_start();
require_once 'db_connect.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $userId = $_SESSION['user_id'];
    $itemId = $_POST['item_id'];
    
    try {
        // Проверяем, есть ли уже товар в избранном
        $stmt = $conn->prepare("SELECT * FROM Favorites WHERE idUser = ? AND idItem = ?");
        $stmt->execute([$userId, $itemId]);
        
        if ($stmt->rowCount() > 0) {
            // Если товар уже в избранном - удаляем его
            $stmt = $conn->prepare("DELETE FROM Favorites WHERE idUser = ? AND idItem = ?");
            $stmt->execute([$userId, $itemId]);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            // Если товара нет в избранном - добавляем его
            $stmt = $conn->prepare("INSERT INTO Favorites (idUser, idItem) VALUES (?, ?)");
            $stmt->execute([$userId, $itemId]);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос']);
}
?> 