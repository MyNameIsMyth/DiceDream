<?php
session_start();
require_once 'db_connect.php';
require_once 'utils.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Необходимо авторизоваться'
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
    // Check if the item exists
    $stmt = $conn->prepare("SELECT idItem FROM Item WHERE idItem = ?");
    $stmt->execute([$itemId]);
    if (!$stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Товар не найден'
        ]);
        exit();
    }

    // Check if the item is already in favorites
    $stmt = $conn->prepare("SELECT idFavorite FROM Favorites WHERE idUser = ? AND idItem = ?");
    $stmt->execute([$userId, $itemId]);
    $favorite = $stmt->fetch();

    if ($favorite) {
        // Remove from favorites
        $stmt = $conn->prepare("DELETE FROM Favorites WHERE idUser = ? AND idItem = ?");
        $stmt->execute([$userId, $itemId]);
        echo json_encode([
            'success' => true,
            'isFavorite' => false,
            'message' => 'Товар удален из избранного'
        ]);
    } else {
        // Add to favorites
        $stmt = $conn->prepare("INSERT INTO Favorites (idUser, idItem) VALUES (?, ?)");
        $stmt->execute([$userId, $itemId]);
        echo json_encode([
            'success' => true,
            'isFavorite' => true,
            'message' => 'Товар добавлен в избранное'
        ]);
    }
} catch (PDOException $e) {
    error_log("Error in toggle_favorite.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Произошла ошибка при обновлении избранного'
    ]);
}
?> 