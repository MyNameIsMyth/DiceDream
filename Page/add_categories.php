<?php
require_once 'db_connect.php';

// Массив категорий
$categories = [
    'Вечериночные',
    'Для взрослых',
    'Для всей семьи',
    'Для детей',
    'Классические',
    'Планы',
    'Стратегические',
    'Хиты продаж'
];

try {
    

    if ($count == 0) {
        // Если категорий нет, добавляем их
        $stmt = $conn->prepare("INSERT INTO Category (nameCategory) VALUES (?)");
        
        foreach ($categories as $category) {
            $stmt->execute([$category]);
        }
        
        echo "Категории успешно добавлены";
    } else {
        echo "Категории уже существуют в базе данных";
    }
} catch(PDOException $e) {
    echo "Ошибка при добавлении категорий: " . $e->getMessage();
}
?> 