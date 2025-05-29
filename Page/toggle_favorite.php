<?php
require_once 'db_connect.php';
session_start();

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
    // Проверяем, есть ли уже этот товар в избранном
    $stmt = $conn->prepare("SELECT idFavorite FROM Favorites WHERE idUser = ? AND idItem = ?");
    $stmt->execute([$userId, $itemId]);
    $favorite = $stmt->fetch();

    if ($favorite) {
        // Если товар уже в избранном - удаляем его
        $stmt = $conn->prepare("DELETE FROM Favorites WHERE idUser = ? AND idItem = ?");
        $stmt->execute([$userId, $itemId]);
        echo json_encode(['success' => true, 'isFavorite' => false]);
    } else {
        // Если товара нет в избранном - добавляем его
        $stmt = $conn->prepare("INSERT INTO Favorites (idUser, idItem) VALUES (?, ?)");
        $stmt->execute([$userId, $itemId]);
        echo json_encode(['success' => true, 'isFavorite' => true]);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении избранного']);
}
?> 