<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['query'])) {
    $search = '%' . $_GET['query'] . '%';
    
    try {
        // Составляем SQL запрос
        $sql = "
            SELECT 
                i.idItem, 
                i.ItemName, 
                i.Price, 
                i.img, 
                c.nameCategory 
            FROM Item i 
            LEFT JOIN Category c ON i.idCategory = c.idCategory
            WHERE i.ItemName LIKE :search 
            OR c.nameCategory LIKE :search
            LIMIT 10
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['search' => $search]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Преобразуем изображения в base64
        foreach ($results as &$item) {
            if (isset($item['img']) && $item['img'] !== null) {
                $item['img'] = base64_encode($item['img']);
            } else {
                $item['img'] = ''; // Если изображения нет, устанавливаем пустую строку
            }
        }
        
        echo json_encode([
            'success' => true,
            'results' => $results
        ]);
    } catch(PDOException $e) {
        // Добавляем больше информации об ошибке
        error_log("Search error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при поиске: ' . $e->getMessage(),
            'error_code' => $e->getCode()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Не указан поисковый запрос'
    ]);
}
?> 