<?php
$servername = "localhost";
$username = "root";
$password = "Und_ofg";
$dbname = "shop_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Функция для обновления путей к изображениям
function updateImagePaths() {
    global $conn;
    try {
        // Получаем все товары
        $stmt = $conn->query("SELECT idItem, ItemName FROM Item");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Обновляем пути к изображениям
        $updateStmt = $conn->prepare("UPDATE Item SET img_path = ? WHERE idItem = ?");
        
        foreach ($items as $item) {
            $imageName = str_replace(['"', "'", ' '], '', $item['ItemName']) . '.png';
            $newPath = "../Media/" . $imageName;
            $updateStmt->execute([$newPath, $item['idItem']]);
        }
        
        return true;
    } catch(PDOException $e) {
        error_log("Error updating image paths: " . $e->getMessage());
        return false;
    }
}

// Запускаем обновление путей при необходимости
// updateImagePaths();